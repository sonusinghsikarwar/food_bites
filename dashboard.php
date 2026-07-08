<?php
// admin/dashboard.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Double check admin authentication security
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

define('LOW_STOCK_THRESHOLD', 10);

// Total Orders
$totalOrders = $pdo->query("SELECT COUNT(id) as count FROM orders")->fetch()['count'] ?? 0;

// Total Revenue
$totalRevenue = $pdo->query("SELECT SUM(grand_total) as sum FROM orders WHERE payment_status = 'paid'")->fetch()['sum'] ?? 0;

// Total Customers
$totalCustomers = $pdo->query("SELECT COUNT(id) as count FROM users")->fetch()['count'] ?? 0;

// Low Stock Alerts (Qty < LOW_STOCK_THRESHOLD)
$lowStock = $pdo->prepare("SELECT COUNT(id) as count FROM products WHERE stock_qty < ?");
$lowStock->execute([LOW_STOCK_THRESHOLD]);
$lowStockCount = $lowStock->fetch()['count'] ?? 0;

// Pending Orders Count
$pendingOrders = $pdo->query("SELECT COUNT(id) as count FROM orders WHERE status = 'pending'")->fetch()['count'] ?? 0;

// Recent Orders
$recentOrders = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5")->fetchAll();

// Daily Sales Chart Data (Last 7 Days)
$salesLabels = [];
$salesData   = [];
for ($i = 6; $i >= 0; $i--) {
    $date        = date('Y-m-d', strtotime("-$i days"));
    $salesLabels[] = date('d M', strtotime($date));
    $stmt = $pdo->prepare("SELECT SUM(grand_total) as total FROM orders WHERE DATE(created_at) = ? AND payment_status = 'paid'");
    $stmt->execute([$date]);
    $salesData[] = floatval($stmt->fetch()['total'] ?? 0.00);
}
?>

<!-- Link unified premium style variables -->
<link rel="stylesheet" href="../assets/css/menu-premium.css">

<style>
/* ── Command Center Layout ── */
body {
    background-color: #F6F5F2 !important;
}
.admin-sidebar {
    background: #ffffff !important;
    border-right: 1px solid rgba(0, 0, 0, 0.05) !important;
}
.admin-sidebar .sidebar-link {
    color: #5F6B7A !important;
}
.admin-sidebar .sidebar-link.active {
    background: #FF9F00 !important; /* Cafe Amber-Orange */
    color: #ffffff !important;
    box-shadow: 0 8px 20px rgba(255, 159, 0, 0.2) !important;
}
.admin-sidebar .sidebar-link.active i {
    color: #ffffff !important;
}
.admin-content {
    background-color: #F6F5F2 !important;
}
.filter-sidebar-glass {
    background: #ffffff !important;
    border: 1px solid rgba(0, 0, 0, 0.03) !important;
    border-radius: 20px !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.02) !important;
}
.admin-stat-icon {
    width: 48px; height: 48px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.luxury-badge-status {
    font-size: 10px !important;
    font-weight: 700;
    padding: 5px 12px !important;
    border-radius: 50px !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
/* Live alert banner animation */
@keyframes premium-pulse {
    0%   { box-shadow: 0 0 0 0   rgba(255, 159, 0, 0.55); }
    100% { box-shadow: 0 0 0 14px rgba(255, 159, 0, 0); }
}
.pulse-glow { animation: premium-pulse 1.8s infinite; }

/* Stat card number counters */
.stat-counter {
    font-family: 'Poppins', sans-serif;
    color: #FF9F00 !important; /* Cafe Amber-Orange */
    transition: color 0.4s ease;
}
.stat-counter.updated { color: #FF9F00 !important; }
</style>

<!-- Hidden Audio — Swiggy/Zomato style order ringtone -->
<audio id="orderNotificationSound"
       src="https://assets.mixkit.co/active_storage/sfx/911/911-84.wav"
       loop playsinline preload="none"></audio>

<!-- Live Notification Ticker Bar (hidden by default) -->
<div id="liveAlertBanner"
     class="alert d-none align-items-center justify-content-between p-3 mb-4 pulse-glow"
     style="background:#121212; border:1px solid var(--premium-gold); border-radius:16px; display:none!important;">
    <div class="d-flex align-items-center gap-3">
        <div class="spinner-grow" style="background-color:var(--premium-gold); width:1.1rem; height:1.1rem;"></div>
        <span class="text-white fw-bold" style="font-size:13px;">
            <i class="fa-solid fa-bell me-2" style="color:var(--premium-gold);"></i>
            ALERT: New gourmet order(s) incoming — kitchen queue updated!
        </span>
    </div>
    <button onclick="silenceNotificationAlert()"
            class="btn btn-gradient-yellow rounded-pill fw-bold text-uppercase"
            style="font-size:10px; padding:7px 18px; letter-spacing:0.4px; flex-shrink:0;">
        Acknowledge
    </button>
</div>

<!-- ── Stat Cards Row (Viva Food Themed Grid) ── -->
<div class="row g-4 mb-4">
    <!-- Gross Revenue -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 p-4" style="background: #ffffff; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.02) !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Gross Revenue</p>
                    <h2 class="fw-extrabold mb-1 fs-3 stat-counter" id="stat-revenue" style="color: #FF5E36;"><?= $currency ?><?= number_format($totalRevenue, 2) ?></h2>
                    <span class="text-success fw-bold" style="font-size: 11px;"><i class="fa-solid fa-arrow-up me-1"></i>+12.4%</span>
                </div>
                <div class="admin-stat-icon" style="background: #FFF0E6; color: #FF5E36; width: 44px; height: 44px; border-radius: 50%;">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                </div>
            </div>
        </div>
    </div>
 
    <!-- Volume Orders -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 p-4" style="background: #ffffff; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.02) !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Volume Orders</p>
                    <h2 class="fw-extrabold mb-1 fs-3 stat-counter" id="stat-orders" style="color: #FF5E36;"><?= number_format($totalOrders) ?></h2>
                    <span class="text-success fw-bold" style="font-size: 11px;"><i class="fa-solid fa-arrow-up me-1"></i>+8.2%</span>
                </div>
                <div class="admin-stat-icon" style="background: #FFF0E6; color: #FF5E36; width: 44px; height: 44px; border-radius: 50%;">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
            </div>
        </div>
    </div>
 
    <!-- Active Patrons -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 p-4" style="background: #ffffff; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.02) !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Active Patrons</p>
                    <h2 class="fw-extrabold mb-1 fs-3 stat-counter" id="stat-customers" style="color: #FF5E36;"><?= number_format($totalCustomers) ?></h2>
                    <span class="text-success fw-bold" style="font-size: 11px;"><i class="fa-solid fa-arrow-up me-1"></i>+15.1%</span>
                </div>
                <div class="admin-stat-icon" style="background: #FFF0E6; color: #FF5E36; width: 44px; height: 44px; border-radius: 50%;">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
        </div>
    </div>
 
    <!-- Low Stock -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 p-4" style="background: #ffffff; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.02) !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Low Stock Alerts</p>
                    <h2 class="fw-extrabold mb-1 fs-3 stat-counter" id="stat-lowstock" style="color: #FF5E36;"><?= $lowStockCount ?></h2>
                    <span class="text-danger fw-semibold" style="font-size: 11px;">Threshold: &lt; <?= LOW_STOCK_THRESHOLD ?></span>
                </div>
                <div class="admin-stat-icon" style="background: #FFF0E6; color: #FF5E36; width: 44px; height: 44px; border-radius: 50%;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Chart + Low Stock Panel ── -->
<div class="row g-4 mb-4">

    <!-- Weekly Revenue Chart -->
    <div class="col-lg-8">
        <div class="card border-0 filter-sidebar-glass p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h5 class="fw-bold text-dark mb-0" style="font-family:'Poppins',sans-serif;">Weekly Revenue Matrix</h5>
                <span class="badge rounded-pill fw-bold text-uppercase" style="background:rgba(197,168,128,0.12);color:var(--premium-gold);font-size:9px;padding:6px 14px;">Last 7 Days</span>
            </div>
            <div style="height:300px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Inventory Warning -->
    <div class="col-lg-4">
        <div class="card border-0 filter-sidebar-glass p-4 h-100 d-flex flex-column justify-content-between">
            <div>
                <h5 class="fw-bold text-dark mb-4" style="font-family:'Poppins',sans-serif;">Inventory Warning</h5>
                <div class="d-flex flex-column gap-2">
                    <?php
                    $lowProducts = $pdo->prepare("SELECT * FROM products WHERE stock_qty < ? ORDER BY stock_qty ASC LIMIT 4");
                    $lowProducts->execute([LOW_STOCK_THRESHOLD]);
                    $lowProductsList = $lowProducts->fetchAll();
                    if (!empty($lowProductsList)):
                        foreach ($lowProductsList as $p): ?>
                        <div class="d-flex align-items-center justify-content-between p-2 rounded-4"
                             style="background:rgba(220,53,69,0.06); border:1px solid rgba(220,53,69,0.15);">
                            <span class="fw-bold text-danger text-truncate" style="font-size:12px; max-width:160px;"><?= htmlspecialchars($p['name']) ?></span>
                            <span class="badge bg-danger rounded-pill" style="font-size:9px; padding:4px 10px;"><?= $p['stock_qty'] ?> Left</span>
                        </div>
                    <?php endforeach;
                    else: ?>
                        <p class="text-muted text-center py-5" style="font-size:12px;">All inventory units are optimal.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="border-top pt-3 mt-3 text-center" style="border-color:rgba(197,168,128,0.15) !important;">
                <a href="products.php" class="fw-bold text-decoration-none" style="font-size:12px; color:var(--premium-gold);">Manage Inventory Vault →</a>
            </div>
        </div>
    </div>
</div>

<!-- ── Recent Orders Queue ── -->
<div class="card border-0 filter-sidebar-glass p-4 mb-4">
    <h5 class="fw-bold text-dark mb-4" style="font-family:'Poppins',sans-serif;">Recent Order Queue</h5>
    <?php if (!empty($recentOrders)): ?>
        <div class="table-responsive">
            <table class="table align-middle border-light mb-0">
                <thead>
                    <tr class="text-muted text-uppercase" style="font-size:10px; border-bottom:2px solid rgba(197,168,128,0.15);">
                        <th>Token ID</th>
                        <th>Patron</th>
                        <th>Mode</th>
                        <th>Grand Total</th>
                        <th>Pipeline</th>
                        <th>Settlement</th>
                        <th class="text-end">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                        <?php
                            $status = $order['status'];
                            $style  = 'background:rgba(245,158,11,0.1); color:#d97706;';
                            if ($status === 'completed')  $style = 'background:rgba(34,197,94,0.1);   color:#16a34a;';
                            if ($status === 'preparing')  $style = 'background:rgba(59,130,246,0.1);  color:#2563eb;';
                            if ($status === 'boxed')      $style = 'background:rgba(168,85,247,0.1);  color:#9333ea;';
                            if ($status === 'dispatched') $style = 'background:rgba(197,168,128,0.12);color:#b08d5b;';
                            if ($status === 'cancelled')  $style = 'background:rgba(220,53,69,0.1);   color:#dc3545;';
                        ?>
                        <tr style="border-bottom:1px solid rgba(0,0,0,0.03);">
                            <td><span class="fw-bold text-dark" style="font-family:'Poppins',sans-serif;">#<?= htmlspecialchars($order['order_no']) ?></span></td>
                            <td class="fw-medium text-secondary" style="font-size:13px;"><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td><span class="badge rounded-pill text-uppercase" style="background:#121212;color:var(--premium-gold);font-size:9px;padding:4px 10px;"><?= htmlspecialchars($order['order_type']) ?></span></td>
                            <td class="fw-bold text-dark"><?= $currency ?><?= number_format($order['grand_total'], 2) ?></td>
                            <td><span class="luxury-badge-status" style="<?= $style ?>"><?= htmlspecialchars($status) ?></span></td>
                            <td><span class="badge bg-light text-dark border rounded-pill text-uppercase" style="font-size:9px; padding:4px 10px;"><?= htmlspecialchars($order['payment_status']) ?></span></td>
                            <td class="text-muted text-end" style="font-size:12px;"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="text-end mt-4">
            <a href="orders.php" class="btn btn-gradient-yellow rounded-pill btn-sm fw-bold text-uppercase" style="font-size:11px; padding:8px 22px;">Full Order Grid →</a>
        </div>
    <?php else: ?>
        <p class="text-center py-5 text-muted" style="font-size:13px;">No transaction tokens found in queue.</p>
    <?php endif; ?>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// ── State ──────────────────────────────────────────────────────────────
let previousOrderCount = <?= (int)$totalOrders ?>;
let alertRinging       = false;

// ── Kitchen Bell (Web Audio API — no external file dependency) ─────────
function playKitchenBell() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        function note(freq, t, dur) {
            const osc  = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain); gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, t);
            gain.gain.setValueAtTime(0.55, t);
            gain.gain.exponentialRampToValueAtTime(0.001, t + dur);
            osc.start(t); osc.stop(t + dur);
        }
        const t = ctx.currentTime;
        note(523.25, t,        0.55);  // C5
        note(659.25, t + 0.22, 0.55);  // E5
        note(783.99, t + 0.44, 0.85);  // G5
    } catch(e) {}
}

// ── Fallback: external audio file loop ────────────────────────────────
function startExternalRingtone() {
    const snd = document.getElementById('orderNotificationSound');
    if (snd && !alertRinging) {
        snd.play().catch(() => {});
        alertRinging = true;
    }
}

function stopExternalRingtone() {
    const snd = document.getElementById('orderNotificationSound');
    if (snd) { snd.pause(); snd.currentTime = 0; }
    alertRinging = false;
}

// ── Show / hide banner ─────────────────────────────────────────────────
function showAlertBanner(count) {
    const banner = document.getElementById('liveAlertBanner');
    if (!banner) return;
    banner.style.display = '';
    banner.classList.remove('d-none');
    banner.classList.add('d-flex');
}

function silenceNotificationAlert() {
    stopExternalRingtone();
    const banner = document.getElementById('liveAlertBanner');
    if (banner) { banner.classList.add('d-none'); banner.style.display = 'none'; }
    window.location.reload();
}

// ── Animate counter update ─────────────────────────────────────────────
function flashCounter(el, newValue) {
    if (!el) return;
    el.classList.add('updated');
    el.textContent = newValue;
    setTimeout(() => el.classList.remove('updated'), 1200);
}

// ── Background polling — every 7 seconds ──────────────────────────────
function checkLiveIncomingOrders() {
    fetch('../api/get_live_admin_stats.php')
        .then(r => r.json())
        .then(data => {
            if (data.status !== 'success') return;

            // Update live counters
            const ordersEl    = document.getElementById('stat-orders');
            const revenueEl   = document.getElementById('stat-revenue');
            const customersEl = document.getElementById('stat-customers');
            const stockEl     = document.getElementById('stat-lowstock');

            if (data.total_orders !== previousOrderCount) {
                flashCounter(ordersEl, data.total_orders.toLocaleString());
            }
            if (revenueEl && data.total_revenue !== undefined) {
                flashCounter(revenueEl, '<?= $currency ?>' + parseFloat(data.total_revenue).toFixed(2));
            }
            if (customersEl && data.total_customers !== undefined) {
                flashCounter(customersEl, data.total_customers.toLocaleString());
            }
            if (stockEl && data.low_stock !== undefined) {
                flashCounter(stockEl, data.low_stock);
            }

            // New order alert
            if (data.total_orders > previousOrderCount) {
                const newCount = data.total_orders - previousOrderCount;
                previousOrderCount = data.total_orders;

                // Play Web Audio bell first
                playKitchenBell();
                // Show banner
                showAlertBanner(newCount);
                // Start external ringtone loop (Swiggy-style persistent alert)
                setTimeout(startExternalRingtone, 800);
            }
        })
        .catch(() => {}); // Silent fail
}

// ── Chart.js: Premium line chart ──────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart').getContext('2d');

    // Orange gradient fill
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0,   'rgba(255, 94, 54, 0.18)');
    gradient.addColorStop(1,   'rgba(255, 94, 54, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($salesLabels) ?>,
            datasets: [{
                label: 'Revenue',
                data: <?= json_encode($salesData) ?>,
                borderColor: '#FF5E36',
                backgroundColor: gradient,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 6,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#FF5E36',
                pointBorderWidth: 3,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#121212',
                    borderColor: 'rgba(197,168,128,0.3)',
                    borderWidth: 1,
                    titleColor: '#C5A880',
                    bodyColor: '#ffffff',
                    padding: 12,
                    cornerRadius: 12,
                    callbacks: {
                        label: ctx => ' Revenue: <?= $currency ?>' + ctx.parsed.y.toFixed(2)
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: {
                        font: { size: 11 },
                        callback: v => '<?= $currency ?>' + v.toLocaleString()
                    }
                }
            }
        }
    });

    // Start background polling
    setInterval(checkLiveIncomingOrders, 7000);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
