<?php
// api/cart_action.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_helper.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$productId = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

$userId = isLoggedIn() ? $_SESSION['user_id'] : null;
$sessionId = session_id();

// Helper to count items in cart
function getCartCount($pdo, $userId, $sessionId) {
    if ($userId) {
        $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM carts WHERE user_id = ?");
        $stmt->execute([$userId]);
    } else {
        $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM carts WHERE session_id = ?");
        $stmt->execute([$sessionId]);
    }
    return intval($stmt->fetch()['count'] ?? 0);
}

// Helper to get cart total
function getCartTotal($pdo, $userId, $sessionId) {
    if ($userId) {
        $stmt = $pdo->prepare("SELECT SUM(c.quantity * CASE WHEN p.discount_price > 0 THEN p.discount_price ELSE p.price END) as total FROM carts c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
        $stmt->execute([$userId]);
    } else {
        $stmt = $pdo->prepare("SELECT SUM(c.quantity * CASE WHEN p.discount_price > 0 THEN p.discount_price ELSE p.price END) as total FROM carts c JOIN products p ON c.product_id = p.id WHERE c.session_id = ?");
        $stmt->execute([$sessionId]);
    }
    return round(floatval($stmt->fetch()['total'] ?? 0), 2);
}

// Handle count action (GET or POST)
if ($action === 'count') {
    echo json_encode(['status' => 'success', 'cart_count' => getCartCount($pdo, $userId, $sessionId), 'cart_total' => getCartTotal($pdo, $userId, $sessionId)]);
    exit;
}

if (!$productId && !in_array($action, ['clear', 'count', 'reorder_paste'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product_id.']);
    exit;
}


try {
    if ($action === 'add') {
        // Check product stock and details
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product) {
            echo json_encode(['status' => 'error', 'message' => 'Product not found or inactive.']);
            exit;
        }

        // Check if item already exists in cart
        if ($userId) {
            $stmt = $pdo->prepare("SELECT * FROM carts WHERE user_id = ? AND product_id = ? LIMIT 1");
            $stmt->execute([$userId, $productId]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM carts WHERE session_id = ? AND product_id = ? LIMIT 1");
            $stmt->execute([$sessionId, $productId]);
        }
        $existing = $stmt->fetch();

        if ($existing) {
            $newQty = $existing['quantity'] + $quantity;
            if ($newQty > $product['stock_qty']) {
                echo json_encode(['status' => 'error', 'message' => "Only {$product['stock_qty']} units available in stock."]);
                exit;
            }
            $stmt = $pdo->prepare("UPDATE carts SET quantity = ? WHERE id = ?");
            $stmt->execute([$newQty, $existing['id']]);
        } else {
            if ($quantity > $product['stock_qty']) {
                echo json_encode(['status' => 'error', 'message' => "Only {$product['stock_qty']} units available in stock."]);
                exit;
            }
            $stmt = $pdo->prepare("INSERT INTO carts (user_id, session_id, product_id, quantity) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $sessionId, $productId, $quantity]);
        }

        $cartCount = getCartCount($pdo, $userId, $sessionId);
        $cartTotal = getCartTotal($pdo, $userId, $sessionId);
        echo json_encode(['status' => 'success', 'message' => 'Added to cart successfully!', 'cart_count' => $cartCount, 'cart_total' => $cartTotal]);
        exit;
        
    } elseif ($action === 'update') {
        // Update specific item quantity
        $stmt = $pdo->prepare("SELECT p.stock_qty FROM products p WHERE p.id = ? LIMIT 1");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if ($quantity <= 0) {
            if ($userId) {
                $stmt = $pdo->prepare("DELETE FROM carts WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$userId, $productId]);
            } else {
                $stmt = $pdo->prepare("DELETE FROM carts WHERE session_id = ? AND product_id = ?");
                $stmt->execute([$sessionId, $productId]);
            }
            $message = 'Item removed from cart.';
        } else {
            if ($product && $quantity > $product['stock_qty']) {
                echo json_encode(['status' => 'error', 'message' => "Only {$product['stock_qty']} units available in stock."]);
                exit;
            }
            if ($userId) {
                $stmt = $pdo->prepare("UPDATE carts SET quantity = ? WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$quantity, $userId, $productId]);
            } else {
                $stmt = $pdo->prepare("UPDATE carts SET quantity = ? WHERE session_id = ? AND product_id = ?");
                $stmt->execute([$quantity, $sessionId, $productId]);
            }
            $message = 'Cart updated successfully.';
        }

        $cartCount = getCartCount($pdo, $userId, $sessionId);
        echo json_encode(['status' => 'success', 'message' => $message, 'cart_count' => $cartCount]);
        exit;

    } elseif ($action === 'remove') {
        if ($userId) {
            $stmt = $pdo->prepare("DELETE FROM carts WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM carts WHERE session_id = ? AND product_id = ?");
            $stmt->execute([$sessionId, $productId]);
        }

        $cartCount = getCartCount($pdo, $userId, $sessionId);
        echo json_encode(['status' => 'success', 'message' => 'Item removed from cart.', 'cart_count' => $cartCount]);
        exit;
        
    } elseif ($action === 'reorder_paste') {
        // Re-add all items from a past order into the current cart
        if (!$userId) {
            echo json_encode(['status' => 'error', 'message' => 'Please login to reorder.']);
            exit;
        }
        $orderId = intval($_POST['order_id'] ?? 0);
        if (!$orderId) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid order ID.']);
            exit;
        }
        // Ownership check
        $ownerChk = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ? LIMIT 1");
        $ownerChk->execute([$orderId, $userId]);
        if (!$ownerChk->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
            exit;
        }
        // Fetch order items joined with current product status
        $itemsStmt = $pdo->prepare(
            "SELECT oi.product_id, oi.quantity, p.stock_qty, p.status 
             FROM order_items oi 
             JOIN products p ON oi.product_id = p.id 
             WHERE oi.order_id = ?"
        );
        $itemsStmt->execute([$orderId]);
        $reorderItems = $itemsStmt->fetchAll();
        $addedCount = 0;
        foreach ($reorderItems as $ri) {
            if ($ri['status'] !== 'active' || $ri['stock_qty'] < 1) continue;
            $chk = $pdo->prepare("SELECT id, quantity FROM carts WHERE user_id = ? AND product_id = ?");
            $chk->execute([$userId, $ri['product_id']]);
            $existingRow = $chk->fetch();
            if ($existingRow) {
                $pdo->prepare("UPDATE carts SET quantity = ? WHERE id = ?")
                    ->execute([$existingRow['quantity'] + $ri['quantity'], $existingRow['id']]);
            } else {
                $pdo->prepare("INSERT INTO carts (user_id, session_id, product_id, quantity) VALUES (?, ?, ?, ?)")
                    ->execute([$userId, $sessionId, $ri['product_id'], $ri['quantity']]);
            }
            $addedCount++;
        }
        if ($addedCount === 0) {
            echo json_encode(['status' => 'error', 'message' => 'All items in this order are currently unavailable.']);
        } else {
            $cartCount = getCartCount($pdo, $userId, $sessionId);
            echo json_encode(['status' => 'success', 'message' => 'Reorder successful!', 'added' => $addedCount, 'cart_count' => $cartCount]);
        }
        exit;

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
?>
