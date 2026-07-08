<?php
// admin/orders.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$success = '';
$error = '';

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = intval($_POST['order_id'] ?? 0);
    $status = sanitizeInput($_POST['status'] ?? '');
    $paymentStatus = sanitizeInput($_POST['payment_status'] ?? '');

    if ($orderId) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ?, payment_status = ? WHERE id = ?");
            $stmt->execute([$status, $paymentStatus, $orderId]);
            
            // If payment marked paid, make sure it has log entry
            if ($paymentStatus === 'paid') {
                $check = $pdo->prepare("SELECT id FROM payments WHERE order_id = ? LIMIT 1");
                $check->execute([$orderId]);
                if (!$check->fetch()) {
                    // Fetch amount
                    $amtQ = $pdo->prepare("SELECT grand_total, payment_method FROM orders WHERE id = ? LIMIT 1");
                    $amtQ->execute([$orderId]);
                    $orderData = $amtQ->fetch();
                    
                    $txn = 'TXN-' . strtoupper(uniqid());
                    $stmt = $pdo->prepare("INSERT INTO payments (order_id, payment_method, transaction_no, amount, status) VALUES (?, ?, ?, ?, 'success')");
                    $stmt->execute([$orderId, $orderData['payment_method'], $txn, $orderData['grand_total']]);
                }
            }
            $success = 'Order updated successfully!';
        } catch (Exception $e) {
            $error = 'Database error updating order status.';
        }
    }
}

// Fetch all orders
$orders = $pdo->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll();
?>

<?php if ($success): ?>
    <div class="alert alert-success border-0 rounded-4 py-3 mb-4"><i class="fa-solid fa-circle-check me-2"></i><?= $success ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger border-0 rounded-4 py-3 mb-4"><i class="fa-solid fa-circle-xmark me-2"></i><?= $error ?></div>
<?php endif; ?>

<div class="glass-card p-4">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <h5 class="fw-bold text-dark dark-text-white mb-0">Orders Management</h5>
        <button onclick="archiveOldOrders()" class="btn btn-sm rounded-pill fw-bold text-uppercase" style="background:linear-gradient(135deg,#121212,#2a2a2a);color:#C5A880;border:1px solid rgba(197,168,128,0.3);font-size:10px;padding:7px 18px;letter-spacing:0.4px;">
            <i class="fa-solid fa-box-archive me-1"></i> Archive Completed Orders
        </button>
    </div>
    <div class="table-responsive">
        <table class="table align-middle border-light mb-0">
            <thead>
                <tr class="text-secondary text-xs uppercase tracking-wider">
                    <th>Order No</th>
                    <th>Customer Details</th>
                    <th>Type / Specs</th>
                    <th>Grand Total</th>
                    <th>Order Status</th>
                    <th>Payment Status</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><span class="fw-bold text-dark dark-text-white"><?= htmlspecialchars($order['order_no']) ?></span></td>
                            <td>
                                <strong class="text-dark dark-text-white"><?= htmlspecialchars($order['customer_name']) ?></strong><br>
                                <span class="small text-muted"><?= htmlspecialchars($order['customer_phone']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-secondary text-uppercase text-xs"><?= htmlspecialchars($order['order_type']) ?></span>
                                <?php if ($order['order_type'] === 'dine_in'): ?>
                                    <span class="small text-muted d-block mt-1">Table: <?= htmlspecialchars($order['table_no']) ?></span>
                                <?php elseif ($order['order_type'] === 'delivery' && $order['address']): ?>
                                    <span class="small text-muted d-block mt-1 text-truncate" style="max-width: 150px;"><?= htmlspecialchars($order['address']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold text-warning"><?= $currency ?><?= number_format($order['grand_total'], 2) ?></td>
                            <td>
                                <form action="orders.php" method="POST" class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <input type="hidden" name="payment_status" value="<?= $order['payment_status'] ?>">
                                    <select name="status" class="form-select form-select-sm rounded-pill shadow-none" onchange="this.form.submit()">
                                        <option value="pending"    <?= $order['status'] === 'pending'    ? 'selected' : '' ?>>Pending</option>
                                        <option value="preparing"  <?= $order['status'] === 'preparing'  ? 'selected' : '' ?>>Preparing</option>
                                        <option value="boxed"      <?= $order['status'] === 'boxed'      ? 'selected' : '' ?>>Boxed</option>
                                        <option value="dispatched" <?= $order['status'] === 'dispatched' ? 'selected' : '' ?>>Dispatched</option>
                                        <option value="completed"  <?= $order['status'] === 'completed'  ? 'selected' : '' ?>>Completed</option>
                                        <option value="cancelled"  <?= $order['status'] === 'cancelled'  ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <form action="orders.php" method="POST" class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <input type="hidden" name="status" value="<?= $order['status'] ?>">
                                    <select name="payment_status" class="form-select form-select-sm rounded-pill shadow-none" onchange="this.form.submit()">
                                        <option value="pending" <?= $order['payment_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                                        <option value="failed" <?= $order['payment_status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                                    </select>
                                </form>
                            </td>
                            <td class="small text-secondary"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></td>
                            <td class="text-end">
                                <a href="../invoice.php?order_no=<?= urlencode($order['order_no']) ?>" target="_blank" class="btn btn-warning rounded-pill btn-sm px-3 fw-bold"><i class="fa-solid fa-file-invoice"></i> Bill</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">No orders processed yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- ===== ARCHIVE ORDERS JAVASCRIPT ===== -->
<script>
function archiveOldOrders() {
    Swal.fire({
        title: '📦 Archive Old Orders?',
        html: `<p class="text-sm text-muted mb-0">This will move all <strong>completed</strong> and <strong>cancelled</strong> orders older than 7 days to the archive ledger. Active orders will not be affected.</p>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#121212',
        cancelButtonColor: '#e5e5e5',
        confirmButtonText: 'Yes, Archive Now',
        customClass: { popup: 'rounded-4 border-0' }
    }).then(result => {
        if (!result.isConfirmed) return;

        Swal.fire({
            title: '⚙️ Archiving...',
            html: `<div class="text-center py-2"><div class="spinner-grow" style="color:#C5A880;"></div><p class="text-xs text-muted mt-3 mb-0">Shifting eligible orders to archive ledger...</p></div>`,
            showConfirmButton: false,
            allowOutsideClick: false
        });

        fetch('api/archive_orders.php')
            .then(r => r.json())
            .then(data => {
                Swal.fire({
                    icon: data.status === 'success' ? 'success' : 'error',
                    title: data.status === 'success' ? '⚜️ Archive Complete' : 'Error',
                    text: data.message,
                    confirmButtonColor: '#121212',
                    customClass: { popup: 'rounded-4 border-0' }
                }).then(() => {
                    if (data.status === 'success' && data.archived > 0) window.location.reload();
                });
            })
            .catch(() => Swal.fire('Error', 'Archive request failed.', 'error'));
    });
}
</script>

<!-- ===== ADMIN LIVE ORDER NOTIFICATION SYSTEM ===== -->
<script>
// Store the last known newest order ID on page load
let lastKnownOrderId = <?= !empty($orders) ? (int)$orders[0]['id'] : 0 ?>;
let notificationAllowed = false;

// Create AudioContext for a premium kitchen ding sound (no external file needed)
function playKitchenDing() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();

        function ding(freq, startTime, duration) {
            const osc  = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, startTime);
            gain.gain.setValueAtTime(0.6, startTime);
            gain.gain.exponentialRampToValueAtTime(0.001, startTime + duration);
            osc.start(startTime);
            osc.stop(startTime + duration);
        }

        // Three-note premium kitchen bell: C5 → E5 → G5
        const t = ctx.currentTime;
        ding(523.25, t,        0.6);
        ding(659.25, t + 0.25, 0.6);
        ding(783.99, t + 0.5,  0.9);
    } catch (e) {
        console.log('Audio not available:', e);
    }
}

// Show a premium toast notification
function showOrderAlert(count) {
    const alertEl = document.getElementById('live-order-alert');
    if (alertEl) {
        alertEl.querySelector('#new-order-count').innerText = count;
        alertEl.style.transform = 'translateX(0)';
        alertEl.style.opacity   = '1';
        setTimeout(() => {
            alertEl.style.transform = 'translateX(110%)';
            alertEl.style.opacity   = '0';
        }, 7000);
    }
    playKitchenDing();
}

// Poll every 30 seconds for new orders
function pollNewOrders() {
    fetch('api/check_new_orders.php?since=' + lastKnownOrderId)
        .then(r => r.json())
        .then(data => {
            if (data.new_count > 0) {
                lastKnownOrderId = data.latest_id;
                showOrderAlert(data.new_count);
            }
        })
        .catch(() => {}); // silent fail — kitchen keeps running
}

// Unlock audio on first user interaction (browser security)
document.addEventListener('click', () => { notificationAllowed = true; }, { once: true });

// Start polling after 30 seconds
setInterval(pollNewOrders, 30000);
</script>

<!-- Live Order Alert Toast (fixed bottom-left) -->
<div id="live-order-alert" style="
    position: fixed; bottom: 30px; left: 30px; z-index: 9999;
    background: linear-gradient(135deg, #121212 0%, #1e1e1e 100%);
    border: 1px solid rgba(197, 168, 128, 0.3);
    border-radius: 18px; padding: 16px 22px;
    display: flex; align-items: center; gap: 14px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.4);
    transform: translateX(-110%); opacity: 0;
    transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    min-width: 280px;">
    <div style="position:relative;">
        <div style="width:42px;height:42px;background:rgba(197,168,128,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;">🔔</div>
        <span id="new-order-count" style="
            position:absolute;top:-4px;right:-4px;
            background:#C5A880;color:#121212;
            font-size:10px;font-weight:800;
            width:18px;height:18px;border-radius:50%;
            display:flex;align-items:center;justify-content:center;">1</span>
    </div>
    <div>
        <p style="color:#C5A880;font-weight:700;font-size:13px;margin:0;font-family:'Poppins',sans-serif;">New Order Arrived!</p>
        <p style="color:rgba(255,255,255,0.6);font-size:11px;margin:2px 0 0;">Kitchen queue updated. Refresh to view.</p>
    </div>
    <button onclick="window.location.reload()" style="
        background:#C5A880;color:#121212;border:none;
        border-radius:10px;padding:6px 12px;
        font-size:11px;font-weight:700;cursor:pointer;">View</button>
</div>

