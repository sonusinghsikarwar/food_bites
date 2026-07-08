<?php
// api/get_live_admin_stats.php
// Lightweight polling endpoint — returns live dashboard stats for admin background polling
// Called every 7 seconds by admin/dashboard.php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_helper.php';

// Only admin can poll this
if (!isAdminLoggedIn()) {
    echo json_encode(['status' => 'denied']);
    exit;
}

try {
    $totalOrders = (int)$pdo->query("SELECT COUNT(id) FROM orders")->fetchColumn();

    $totalRevenue = (float)($pdo->query(
        "SELECT COALESCE(SUM(grand_total), 0) FROM orders WHERE payment_status = 'paid'"
    )->fetchColumn());

    $totalCustomers = (int)$pdo->query("SELECT COUNT(id) FROM users")->fetchColumn();

    $lowStockStmt = $pdo->prepare("SELECT COUNT(id) FROM products WHERE stock_qty < 10");
    $lowStockStmt->execute();
    $lowStock = (int)$lowStockStmt->fetchColumn();

    $pendingOrders = (int)$pdo->query(
        "SELECT COUNT(id) FROM orders WHERE status = 'pending'"
    )->fetchColumn();

    echo json_encode([
        'status'          => 'success',
        'total_orders'    => $totalOrders,
        'total_revenue'   => round($totalRevenue, 2),
        'total_customers' => $totalCustomers,
        'low_stock'       => $lowStock,
        'pending_orders'  => $pendingOrders,
        'polled_at'       => date('H:i:s'),
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
exit;
