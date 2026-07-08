<?php
// invoice.php
require_once __DIR__ . '/includes/header.php';

$orderNo = $_GET['order_no'] ?? '';
if (!$orderNo) {
    header("Location: index.php");
    exit;
}

// Fetch order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_no = ? LIMIT 1");
$stmt->execute([$orderNo]);
$order = $stmt->fetch();

if (!$order) {
    echo '<div class="container py-5 text-center"><h3 class="text-danger">Order not found.</h3></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Validate permission (owner of order or admin logged in)
if (!isAdminLoggedIn() && (!isLoggedIn() || $_SESSION['user_id'] !== $order['user_id'])) {
    echo '<div class="container py-5 text-center"><h3 class="text-danger">Access denied.</h3></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch items
$stmt = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->execute([$order['id']]);
$items = $stmt->fetchAll();

$restaurant_name = getSetting('restaurant_name', 'Crispy Bytes');
$email = getSetting('contact_email', 'info@crispybytes.com');
$phone = getSetting('contact_phone', '+91 95164 40137');
$address = getSetting('address', '202, Pink City Food Court, MI Road, Jaipur, India');

// ── Dynamic Timeline Status Mapping ──────────────────────────────────
// Maps DB order status to 4-step pipeline: pending → preparing → boxed → dispatched
$current_status = $order['status'] ?? 'pending';

$step1 = 'completed-step'; // Received: always completed once order exists
$step2 = '';
$step3 = '';
$step4 = '';

if ($current_status === 'preparing') {
    $step2 = 'active-step';
} elseif ($current_status === 'boxed') {
    $step2 = 'completed-step';
    $step3 = 'active-step';
} elseif (in_array($current_status, ['dispatched', 'completed'])) {
    $step2 = 'completed-step';
    $step3 = 'completed-step';
    $step4 = 'active-step';
} elseif ($current_status === 'cancelled') {
    $step1 = 'active-step'; // show stuck at step 1 on cancellation
} else {
    // Default: pending — order received, awaiting kitchen action
    $step1 = 'completed-step';
    $step2 = ''; // prepping not started
}
?>

<!-- Link global premium stylesheet -->
<link rel="stylesheet" href="assets/css/menu-premium.css">

<style>
/* Invoice Special Premium Styles */
.receipt-card {
    background: #ffffff !important;
    border: 1px solid rgba(197, 168, 128, 0.2) !important;
    box-shadow: 0 15px 50px rgba(0,0,0,0.02) !important;
    border-radius: 28px !important;
}
.luxury-header-divider {
    width: 60px;
    height: 3px;
    background: var(--premium-gold);
    border-radius: 2px;
}

/* Live Tracker Timeline Styles */
.timeline-steps {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}
.timeline-steps::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 3px;
    background: #eaeae8;
    z-index: 1;
}
.timeline-step-item {
    position: relative;
    z-index: 2;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
}
.timeline-dot {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #ffffff;
    border: 3px solid #eaeae8;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #a0aec0;
    font-size: 14px;
    transition: all 0.4s ease;
}
.timeline-step-item.active-step .timeline-dot {
    border-color: #121212;
    background: #121212;
    color: var(--premium-gold);
    box-shadow: 0 0 15px rgba(0,0,0,0.15);
}
.timeline-step-item.completed-step .timeline-dot {
    border-color: var(--premium-gold);
    background: var(--premium-gold);
    color: #ffffff;
}
.timeline-step-item.completed-step .timeline-steps::before {
    background: var(--premium-gold);
}

/* Print Overrides — clean A4 output */
@media print {
    .no-print { display: none !important; }
    nav, footer, header, .floating-cart-btn, .floating-help-btn { display: none !important; }
    body { background: #ffffff !important; color: #000000 !important; }
    .receipt-card {
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        padding: 0 !important;
    }
    .container { max-width: 100% !important; padding: 0 !important; }
}
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <!-- Back & Print Actions (No-Print) -->
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <a href="profile.php#orders" class="btn btn-outline-dark rounded-pill px-4 fw-bold text-uppercase" style="font-size: 11px; border-color: var(--matte-dark); letter-spacing: 0.5px;">
                    <i class="fa-solid fa-arrow-left me-2"></i> Dashboard
                </a>
                <button onclick="window.print()" class="btn btn-gradient-yellow rounded-pill px-4 fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-print me-2"></i> Print Certificate
                </button>
            </div>

            <!-- Culinary Progress Tracker (No-Print Component) -->
            <div class="card border-0 filter-sidebar-glass p-4 mb-4 no-print">
                <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                    <h6 class="fw-bold mb-0 text-uppercase text-xs" style="color: var(--premium-gold); letter-spacing: 1.5px;">
                        Gourmet Pipeline Status
                    </h6>
                    <span id="live-status-pill" class="badge rounded-pill fw-bold text-uppercase" style="background:rgba(197,168,128,0.12); color:var(--premium-gold); font-size:9px; padding:5px 12px; letter-spacing:0.4px;">
                        <span style="width:6px;height:6px;background:var(--premium-gold);border-radius:50%;display:inline-block;margin-right:5px;animation:livePulse 2s infinite;"></span>
                        <?= strtoupper($current_status) ?>
                    </span>
                </div>
                <div class="timeline-steps" id="status-timeline">
                    <!-- Step 1: Received -->
                    <div class="timeline-step-item <?= $step1 ?>" data-step="1">
                        <div class="timeline-dot"><i class="fa-solid fa-receipt"></i></div>
                        <span class="text-xs fw-bold mt-2 <?= $step1 ? 'text-dark' : 'text-muted' ?>">Received</span>
                    </div>
                    <!-- Step 2: Prepping -->
                    <div class="timeline-step-item <?= $step2 ?>" data-step="2">
                        <div class="timeline-dot"><i class="fa-solid fa-fire-burner"></i></div>
                        <span class="text-xs fw-bold mt-2 <?= $step2 ? 'text-dark' : 'text-muted' ?>">Prepping</span>
                    </div>
                    <!-- Step 3: Boxed -->
                    <div class="timeline-step-item <?= $step3 ?>" data-step="3">
                        <div class="timeline-dot"><i class="fa-solid fa-box-open"></i></div>
                        <span class="text-xs fw-bold mt-2 <?= $step3 ? 'text-dark' : 'text-muted' ?>">Boxed</span>
                    </div>
                    <!-- Step 4: Dispatched -->
                    <div class="timeline-step-item <?= $step4 ?>" data-step="4">
                        <div class="timeline-dot"><i class="fa-solid fa-moped"></i></div>
                        <span class="text-xs fw-bold mt-2 <?= $step4 ? 'text-dark' : 'text-muted' ?>">Dispatched</span>
                    </div>
                    </div>
                    </div>
                </div>
            </div>

            <!-- ========================================= -->
            <!-- Luxury Printable Receipt Area             -->
            <!-- ========================================= -->
            <div class="card receipt-card p-4 p-md-5" id="printable-invoice">

                <!-- Brand Header -->
                <div class="row align-items-center mb-4 border-bottom pb-4" style="border-color: rgba(197, 168, 128, 0.15) !important;">
                    <div class="col-sm-6 text-center text-sm-start">
                        <h3 class="fw-extrabold mb-1" style="font-family: 'Poppins', sans-serif; color: var(--matte-dark); letter-spacing: -0.5px;">
                            <i class="fa-solid fa-signature me-2" style="color: var(--premium-gold);"></i><?= htmlspecialchars($restaurant_name) ?>
                        </h3>
                        <p class="text-muted mb-0 fw-medium" style="font-size: 12px; line-height: 1.6;">
                            <?= htmlspecialchars($address) ?><br>
                            Contact: <?= htmlspecialchars($phone) ?> &nbsp;|&nbsp; <?= htmlspecialchars($email) ?>
                        </p>
                    </div>
                    <div class="col-sm-6 text-center text-sm-end mt-3 mt-sm-0">
                        <h4 class="fw-bold text-uppercase mb-1" style="color: var(--premium-gold); font-size: 10px; letter-spacing: 1.5px;">Culinary Order Token</h4>
                        <p class="mb-1" style="font-size: 13px;"><strong>ID:</strong> <span style="font-family: 'Poppins', sans-serif; font-weight: 700;"><?= htmlspecialchars($order['order_no']) ?></span></p>
                        <p class="mb-0 text-muted" style="font-size: 11px;"><strong>Timestamp:</strong> <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
                    </div>
                </div>

                <!-- Patron & Execution Info Grid -->
                <div class="row mb-4 p-3 rounded-4 g-3" style="background: var(--luxury-white); border: 1px solid rgba(0,0,0,0.03);">
                    <div class="col-sm-6">
                        <h6 class="fw-bold text-muted mb-2 text-uppercase" style="font-size: 10px; letter-spacing: 1px;">Patron Identity</h6>
                        <p class="mb-1" style="font-size: 13px;"><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                        <p class="mb-0" style="font-size: 13px;"><strong>Registered Phone:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <h6 class="fw-bold text-muted mb-2 text-uppercase" style="font-size: 10px; letter-spacing: 1px;">Execution Class</h6>
                        <p class="mb-1" style="font-size: 13px;"><strong>Assignment Type:</strong>
                            <span class="badge rounded-pill text-uppercase px-2 py-1" style="background: var(--matte-dark) !important; color: var(--premium-gold); font-size: 9px; letter-spacing: 0.5px;"><?= htmlspecialchars($order['order_type']) ?></span>
                        </p>
                        <?php if ($order['order_type'] === 'dine_in'): ?>
                            <p class="mb-0" style="font-size: 13px;"><strong>Lounge Table:</strong> <?= htmlspecialchars($order['table_no']) ?></p>
                        <?php elseif ($order['order_type'] === 'delivery' && !empty($order['address'])): ?>
                            <p class="mb-0 text-muted" style="font-size: 11px; max-width: 280px; margin-left: auto;"><strong>Destination:</strong> <?= htmlspecialchars($order['address']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive mb-4">
                    <table class="table align-middle">
                        <thead>
                            <tr class="text-muted text-uppercase" style="border-bottom: 2px solid rgba(197, 168, 128, 0.2); font-size: 10px; letter-spacing: 0.8px;">
                                <th class="pb-3 fw-bold">Selected Masterpiece</th>
                                <th class="text-center pb-3 fw-bold">Valuation</th>
                                <th class="text-center pb-3 fw-bold">Qty</th>
                                <th class="text-end pb-3 fw-bold">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr style="border-bottom: 1px solid rgba(0,0,0,0.04);">
                                    <td class="fw-bold py-3" style="color: var(--matte-dark); font-size: 13px;"><?= htmlspecialchars($item['name']) ?></td>
                                    <td class="text-center text-muted" style="font-size: 13px;"><?= $currency ?><?= number_format($item['price'], 2) ?></td>
                                    <td class="text-center fw-semibold" style="font-size: 13px;"><?= $item['quantity'] ?></td>
                                    <td class="text-end fw-bold" style="color: var(--matte-dark); font-size: 13px;"><?= $currency ?><?= number_format($item['total'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Financial Summary -->
                <div class="row justify-content-end">
                    <div class="col-md-5">
                        <div class="d-flex justify-content-between mb-2" style="font-size: 13px;">
                            <span class="text-muted">Items Subtotal:</span>
                            <span class="fw-semibold text-dark"><?= $currency ?><?= number_format($order['total_amount'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2" style="font-size: 13px;">
                            <span class="text-muted">Statutory GST Charges:</span>
                            <span class="fw-semibold text-dark"><?= $currency ?><?= number_format($order['tax_amount'], 2) ?></span>
                        </div>
                        <?php if ($order['discount_amount'] > 0): ?>
                            <div class="d-flex justify-content-between mb-2 text-success fw-semibold" style="font-size: 13px;">
                                <span>Privilege Reduction:</span>
                                <span>- <?= $currency ?><?= number_format($order['discount_amount'], 2) ?></span>
                            </div>
                        <?php endif; ?>
                        <hr style="border-color: rgba(197, 168, 128, 0.2); margin: 10px 0;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark" style="font-family: 'Poppins', sans-serif; font-size: 14px;">Sum Total:</span>
                            <span class="fw-extrabold" style="color: #b08d5b !important; font-family: 'Poppins', sans-serif; font-size: 1.6rem;"><?= $currency ?><?= number_format($order['grand_total'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2 text-muted" style="font-size: 11px; border-color: rgba(197, 168, 128, 0.15) !important;">
                            <span>Settlement Method:</span>
                            <span class="fw-bold text-uppercase text-dark">
                                <?= htmlspecialchars($order['payment_method']) ?>
                                (<span class="text-success"><?= htmlspecialchars($order['payment_status']) ?></span>)
                            </span>
                        </div>
                    </div>
                </div>

                <?php if (!empty($order['chef_notes'])): ?>
                <!-- Chef Notes (if any) -->
                <div class="mt-4 p-3 rounded-4" style="background: rgba(197,168,128,0.06); border: 1px solid rgba(197,168,128,0.15);">
                    <span class="text-xs fw-bold text-uppercase" style="color: var(--premium-gold); letter-spacing: 1px;"><i class="fa-regular fa-comment-dots me-1"></i> Culinary Notes for Chef</span>
                    <p class="mb-0 text-muted mt-1" style="font-size: 12px;"><?= htmlspecialchars($order['chef_notes']) ?></p>
                </div>
                <?php endif; ?>

                <!-- Footer Signature Line -->
                <div class="text-center mt-5 pt-4 border-top" style="border-color: rgba(197, 168, 128, 0.15) !important;">
                    <div class="luxury-header-divider mx-auto mb-3"></div>
                    <p class="mb-1 fw-bold text-dark" style="font-family: 'Poppins', sans-serif; font-size: 13px;">Thank you for ordering with <?= htmlspecialchars($restaurant_name) ?>!</p>
                    <p class="text-muted mb-0" style="font-size: 11px;">For immediate assistance, call our concierge desk: <?= htmlspecialchars($phone) ?></p>
                </div>

            </div>
            <!-- End Printable Invoice -->

        </div>
    </div>
</div>

<!-- ===== REAL-TIME SSE STATUS CLIENT ===== -->
<script>
(function() {
    const orderNo   = '<?= addslashes(htmlspecialchars($order['order_no'])) ?>';
    const timeline  = document.getElementById('status-timeline');
    const statusPill = document.getElementById('live-status-pill');

    // Only stream for non-terminal initial statuses
    const terminalStatuses = ['completed', 'cancelled'];
    let currentStatus = '<?= $current_status ?>';

    if (!timeline || terminalStatuses.includes(currentStatus)) return;

    // Apply incoming step classes to timeline DOM nodes
    function applySteps(steps) {
        const stepKeys = ['step1', 'step2', 'step3', 'step4'];
        const nodes    = timeline.querySelectorAll('.timeline-step-item');
        nodes.forEach((node, i) => {
            const cls = steps[stepKeys[i]] || '';
            node.className = 'timeline-step-item ' + cls;
            const label = node.querySelector('span');
            if (label) label.className = 'text-xs fw-bold mt-2 ' + (cls ? 'text-dark' : 'text-muted');
        });
    }

    // Update the live status pill
    function updatePill(status) {
        if (statusPill) {
            statusPill.querySelector('span') && (statusPill.querySelector('span').style.animation =
                terminalStatuses.includes(status) ? 'none' : 'livePulse 2s infinite');
            // Replace text node (keep pulse dot)
            const dot = statusPill.querySelector('span');
            statusPill.textContent = status.toUpperCase();
            if (dot) statusPill.prepend(dot);
        }
    }

    // Connect to SSE stream
    const evtSource = new EventSource('api/order_status_stream.php?order_no=' + encodeURIComponent(orderNo));

    evtSource.addEventListener('statusUpdate', function(e) {
        try {
            const data = JSON.parse(e.data);
            applySteps(data.steps);
            updatePill(data.status);
            currentStatus = data.status;

            // Show toast on status change
            if (typeof Swal !== 'undefined') {
                Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3500 })
                    .fire({ icon: 'info', title: '⚜️ Order Update', text: 'Status changed to: ' + data.status.toUpperCase() });
            }
        } catch(err) { console.warn('SSE parse error', err); }
    });

    evtSource.addEventListener('close', function() {
        evtSource.close();
    });

    evtSource.onerror = function() {
        evtSource.close();
    };

    // Pulse animation CSS (injected inline to avoid SSE dependency on external CSS)
    const style = document.createElement('style');
    style.textContent = '@keyframes livePulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.4;transform:scale(1.4)} }';
    document.head.appendChild(style);
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
