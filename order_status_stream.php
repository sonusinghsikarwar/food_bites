<?php
// api/order_status_stream.php
// Server-Sent Events (SSE) endpoint — streams live order status to customer's invoice.php
// Customer ke browser mein background mein run hota hai, har 5s mein DB check karta hai

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_helper.php';

// SSE headers — keeps the HTTP connection alive
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');  // Disable nginx buffering
header('Access-Control-Allow-Origin: *');

// Disable output buffering completely
if (ob_get_level()) ob_end_clean();

$orderNo = $_GET['order_no'] ?? '';
if (!$orderNo) {
    echo "event: error\ndata: {\"message\":\"No order_no provided\"}\n\n";
    flush();
    exit;
}

// SSE status → pipeline step mapping
function mapStatusToSteps(string $status): array {
    $map = [
        'pending'    => ['step1' => 'completed-step', 'step2' => '',               'step3' => '',               'step4' => ''],
        'preparing'  => ['step1' => 'completed-step', 'step2' => 'active-step',    'step3' => '',               'step4' => ''],
        'boxed'      => ['step1' => 'completed-step', 'step2' => 'completed-step', 'step3' => 'active-step',    'step4' => ''],
        'dispatched' => ['step1' => 'completed-step', 'step2' => 'completed-step', 'step3' => 'completed-step', 'step4' => 'active-step'],
        'completed'  => ['step1' => 'completed-step', 'step2' => 'completed-step', 'step3' => 'completed-step', 'step4' => 'active-step'],
        'cancelled'  => ['step1' => 'active-step',    'step2' => '',               'step3' => '',               'step4' => ''],
    ];
    return $map[$status] ?? $map['pending'];
}

$lastStatus = null;
$iteration  = 0;
$maxIter    = 120; // Auto-close after 10 minutes (120 × 5s)

while ($iteration < $maxIter) {
    // Fetch current order status from DB
    $stmt = $pdo->prepare("SELECT status, payment_status FROM orders WHERE order_no = ? LIMIT 1");
    $stmt->execute([$orderNo]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo "event: error\ndata: " . json_encode(['message' => 'Order not found']) . "\n\n";
        flush();
        break;
    }

    $currentStatus = $row['status'];

    // Only push event if status has changed (avoids redundant DOM updates)
    if ($currentStatus !== $lastStatus) {
        $lastStatus = $currentStatus;
        $steps = mapStatusToSteps($currentStatus);

        $payload = json_encode([
            'status'         => $currentStatus,
            'payment_status' => $row['payment_status'],
            'steps'          => $steps,
            'updated_at'     => date('h:i A'),
        ]);

        echo "event: statusUpdate\n";
        echo "data: {$payload}\n\n";
        flush();

        // Stop streaming once order is terminal (no more changes expected)
        if (in_array($currentStatus, ['completed', 'cancelled'])) {
            echo "event: close\ndata: {\"reason\":\"Order finalized\"}\n\n";
            flush();
            break;
        }
    }

    // Send a heartbeat every 5s to keep connection alive
    echo ": heartbeat\n\n";
    flush();

    sleep(5);
    $iteration++;
}
