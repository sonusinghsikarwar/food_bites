<?php
// api/get_suggestions.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_helper.php';

header('Content-Type: application/json');

$query = sanitizeInput($_GET['q'] ?? '');
if (strlen($query) < 1) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT name FROM products WHERE status = 'active' AND name LIKE ? LIMIT 5");
    $stmt->execute(["%$query%"]);
    $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($suggestions);
    exit;
} catch (Exception $e) {
    echo json_encode([]);
    exit;
}
?>
