<?php
// api/reorder.php
// Re-adds all items from a previous order back into the current user's cart
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_helper.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to reorder.']);
    exit;
}

$userId = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$orderId = intval($input['order_id'] ?? 0);

if (!$orderId) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid order ID.']);
    exit;
}

// Validate this order belongs to the logged in user
$stmt = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->execute([$orderId, $userId]);
if (!$stmt->fetch()) {
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit;
}

// Fetch all items from this order
$stmt = $pdo->prepare("SELECT oi.product_id, oi.quantity, p.status, p.stock_qty 
                        FROM order_items oi 
                        JOIN products p ON oi.product_id = p.id 
                        WHERE oi.order_id = ?");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

if (empty($items)) {
    echo json_encode(['status' => 'error', 'message' => 'No items found in this order.']);
    exit;
}

$addedCount = 0;
$sessionId = session_id();

try {
    foreach ($items as $item) {
        // Skip unavailable products
        if ($item['status'] !== 'active' || $item['stock_qty'] < 1) {
            continue;
        }

        // Check if product already in cart for this user
        $checkStmt = $pdo->prepare("SELECT id, quantity FROM carts WHERE user_id = ? AND product_id = ?");
        $checkStmt->execute([$userId, $item['product_id']]);
        $existing = $checkStmt->fetch();

        if ($existing) {
            // Increment quantity
            $newQty = $existing['quantity'] + $item['quantity'];
            $updateStmt = $pdo->prepare("UPDATE carts SET quantity = ? WHERE id = ?");
            $updateStmt->execute([$newQty, $existing['id']]);
        } else {
            // Insert new cart row
            $insertStmt = $pdo->prepare("INSERT INTO carts (user_id, session_id, product_id, quantity) VALUES (?, ?, ?, ?)");
            $insertStmt->execute([$userId, $sessionId, $item['product_id'], $item['quantity']]);
        }
        $addedCount++;
    }

    if ($addedCount === 0) {
        echo json_encode(['status' => 'error', 'message' => 'All items from this order are currently unavailable.']);
    } else {
        echo json_encode(['status' => 'success', 'added' => $addedCount]);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
