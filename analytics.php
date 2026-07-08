<?php
// admin/analytics.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

// ── 1. Top 5 Best-Selling Masterpieces ─────────────────────────────────
$topSellingQuery = $pdo->query("
    SELECT p.name, p.image, SUM(oi.quantity) as total_qty, SUM(oi.total) as total_sales
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status IN ('completed', 'dispatched')
    GROUP BY oi.product_id
    ORDER BY total_qty DESC
    LIMIT 5
");
$topSelling = $topSellingQuery->fetchAll();

// ── 2. Average Order Value (AOV) ──────────────────────────────────────
$aovMetric = (float)($pdo->query(
    "SELECT COALESCE(AVG(grand_total), 0) as avg_val FROM orders WHERE status IN ('completed','dispatched')"
)->fetch()['avg_val'] ?? 0);

// ── 3. Total Revenue (all paid) ────────────────────────────────────────
$totalRevenue = (float)($pdo->query(
    "SELECT COALESCE(SUM(grand_total), 0) as sum FROM orders WHERE payment_status = 'paid'"
)->fetch()['sum'] ?? 0);

// ── 4. Total Completed Orders ──────────────────────────────────────────
$completedOrders = (int)$pdo->query(
    "SELECT COUNT(id) FROM orders WHERE status IN ('completed','dispatched')"
)->fetchColumn();

// ── 5. Service Mode Distribution ──────────────────────────────────────
$modesQuery  = $pdo->query("SELECT order_type, COUNT(id) as count FROM orders GROUP BY order_type");
$modesData   = $modesQuery->fetchAll();
$modeLabels  = [];
$modeCounts  = [];
foreach ($modesData as $row) {
    $modeLabels[] = strtoupper(str_replace('_', ' ', $row['order_type']));
    $modeCounts[] = (int)$row['count'];
}

// ── 6. Daily Revenue – Last 14 Days ───────────────────────────────────
$revenueLabels = [];
$revenueData   = [];
for ($i = 13; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $revenueLabels[] = date('d M', strtotime($date));
    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(grand_total), 0) FROM orders WHERE DATE(created_at) = ? AND payment_status = 'paid'"
    );
    $stmt->execute([$date]);
    $revenueData[] = (float)$stmt->fetchColumn();
}

// ── 7. Peak Ordering Hours ─────────────────────────────────────────────
$hoursQuery = $pdo->query(
    "SELECT HOUR(created_at) as hr, COUNT(id) as cnt FROM orders GROUP BY HOUR(created_at) ORDER BY hr"
);
$hoursRaw  = $hoursQuery->fetchAll();
$hourMap   = array_fill(0, 24, 0);
foreach ($hoursRaw as $h) { $hourMap[(int)$h['hr']] = (int)$h['cnt']; }
$hourLabels = array_map(fn($h) => ($h < 12 ? ($h ?: 12) . ' AM' : ($h === 12 ? '12 PM' : ($h - 12) . ' PM')), range(0, 23));
?>

<link rel="stylesheet" href="../assets/css/menu-premium.css">

<style>
.analytics-kpi {
    border-left: 3px solid var(--premium-gold);
    padding-left: 16px;
}
.rank-badge {
    width: 28px; height: 28px; border-radius: 50%;
    background: linear-gradient(135deg, #121212, #2a2a2a);
    color: var(--premium-gold);
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 800; flex-shrink: 0;
}
.rank-badge.rank-1 { background: linear-gradient(135deg, #b08d5b, #C5A880); color: #121212; }
</style>

<!-- Page Header -->
<div class="mb-5">
    <span class="fw-bold text-uppercase" style="color:var(--premium-gold); font-size:10px; letter-spacing:1.5px;">Business Intelligence</span>
    <h1 class="fw-extrabold text-dark mb-2" style="font-family:'Poppins',sans-serif; letter-spacing:-0.5px;">Culinary Analytics</h1>
    <div style="width:50px; height:3px; background:var(--premium-gold); border-radius:2px;"></div>
</div>

<!-- ── KPI Strip ── -->
<div class="row g-4 mb-5">
    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 filter-sidebar-glass p-4">
            <div class="analytics-kpi">
                <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size:10px; letter-spacing:0.5px;">Avg Order Value (AOV)</p>
                <h2 class="fw-extrabold mb-1" style="font-family:'Poppins',sans-serif; color:var(--matte-dark);"><?= $currency ?><?= number_format($aovMetric, 2) ?></h2>
                <p class="text-muted mb-0" style="font-size:11px;">Average per-patron gourmet transaction.</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 filter-sidebar-glass p-4">
            <div class="analytics-kpi">
                <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size:10px; letter-spacing:0.5px;">Total Paid Revenue</p>
                <h2 class="fw-extrabold mb-1" style="font-family:'Poppins',sans-serif; color:var(--matte-dark);"><?= $currency ?><?= number_format($totalRevenue, 2) ?></h2>
                <p class="text-muted mb-0" style="font-size:11px;">Verified settled transactions only.</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 filter-sidebar-glass p-4">
            <div class="analytics-kpi">
                <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size:10px; letter-spacing:0.5px;">Fulfilled Orders</p>
                <h2 class="fw-extrabold mb-1" style="font-family:'Poppins',sans-serif; color:var(--matte-dark);"><?= number_format($completedOrders) ?></h2>
                <p class="text-muted mb-0" style="font-size:11px;">Completed + dispatched orders total.</p>
            </div>
        </div>
    </div>
</div>

<!-- ── Top Products + Distribution Chart ── -->
<div class="row g-4 mb-5">

    <!-- Best-Selling Masterpieces -->
    <div class="col-lg-7">
        <div class="card border-0 filter-sidebar-glass p-4 h-100">
            <h5 class="fw-bold text-dark mb-4" style="font-family:'Poppins',sans-serif;">
                <i class="fa-solid fa-crown me-2" style="color:var(--premium-gold);"></i> Best-Selling Masterpieces
            </h5>

            <?php if (!empty($topSelling)): ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($topSelling as $i => $item):
                        $pct = $topSelling[0]['total_qty'] > 0
                             ? round(($item['total_qty'] / $topSelling[0]['total_qty']) * 100) : 0;
                    ?>
                    <div class="d-flex align-items-center gap-3">
                        <div class="rank-badge <?= $i === 0 ? 'rank-1' : '' ?>"><?= $i + 1 ?></div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark" style="font-size:13px;"><?= htmlspecialchars($item['name']) ?></span>
                                <div class="text-end">
                                    <span class="fw-bold" style="font-size:13px; color:#b08d5b;"><?= $currency ?><?= number_format($item['total_sales'], 2) ?></span>
                                    <span class="text-muted ms-2" style="font-size:11px;"><?= number_format($item['total_qty']) ?> pcs</span>
                                </div>
                            </div>
                            <!-- Progress bar -->
                            <div style="height:4px; background:rgba(0,0,0,0.06); border-radius:2px; overflow:hidden;">
                                <div style="height:100%; width:<?= $pct ?>%; background:<?= $i === 0 ? 'var(--premium-gold)' : 'rgba(197,168,128,0.4)' ?>; border-radius:2px; transition:width 0.8s ease;"></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted text-center py-5" style="font-size:12px;">No completed orders data yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Service Mode Doughnut -->
    <div class="col-lg-5">
        <div class="card border-0 filter-sidebar-glass p-4 h-100">
            <h5 class="fw-bold text-dark mb-4" style="font-family:'Poppins',sans-serif;">Service Distribution</h5>
            <?php if (!empty($modeCounts)): ?>
                <div style="height:240px; position:relative;">
                    <canvas id="distributionChart"></canvas>
                </div>
                <div class="mt-3 d-flex flex-wrap justify-content-center gap-3">
                    <?php
                    $dotColors = ['#121212', '#C5A880', '#e5e5e5', '#6b7280'];
                    foreach ($modeLabels as $mi => $ml): ?>
                    <span class="d-flex align-items-center gap-2" style="font-size:11px; font-weight:600;">
                        <span style="width:10px;height:10px;border-radius:50%;background:<?= $dotColors[$mi % count($dotColors)] ?>;display:inline-block;"></span>
                        <?= htmlspecialchars($ml) ?> (<?= $modeCounts[$mi] ?>)
                    </span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted text-center py-5" style="font-size:12px;">No order distribution data yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── 14-Day Revenue Trend ── -->
<div class="row g-4 mb-5">
    <div class="col-12">
        <div class="card border-0 filter-sidebar-glass p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h5 class="fw-bold text-dark mb-0" style="font-family:'Poppins',sans-serif;">14-Day Revenue Trend</h5>
                <span class="badge rounded-pill fw-bold" style="background:rgba(197,168,128,0.12);color:var(--premium-gold);font-size:9px;padding:6px 14px;">Paid Orders Only</span>
            </div>
            <div style="height:280px;">
                <canvas id="revenueTrendChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ── Peak Ordering Hours Heatmap ── -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 filter-sidebar-glass p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h5 class="fw-bold text-dark mb-0" style="font-family:'Poppins',sans-serif;">Peak Ordering Hours</h5>
                <span class="badge rounded-pill fw-bold" style="background:rgba(197,168,128,0.12);color:var(--premium-gold);font-size:9px;padding:6px 14px;">All Orders</span>
            </div>
            <div style="height:200px;">
                <canvas id="peakHoursChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ── Shared chart defaults ────────────────────────────────────────
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color       = '#6b7280';

    const premiumTooltip = {
        backgroundColor: '#121212',
        borderColor:     'rgba(197,168,128,0.25)',
        borderWidth:     1,
        titleColor:      '#C5A880',
        bodyColor:       '#ffffff',
        padding:         12,
        cornerRadius:    12,
    };

    // ── 1. Service Distribution (Doughnut) ──────────────────────────
    <?php if (!empty($modeCounts)): ?>
    new Chart(document.getElementById('distributionChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($modeLabels) ?>,
            datasets: [{
                data: <?= json_encode($modeCounts) ?>,
                backgroundColor: ['#121212', '#C5A880', '#d1d5db', '#9ca3af'],
                borderWidth: 3,
                borderColor: '#ffffff',
                hoverBorderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: premiumTooltip },
            cutout: '72%'
        }
    });
    <?php endif; ?>

    // ── 2. 14-Day Revenue Trend (Line) ──────────────────────────────
    const trendCtx = document.getElementById('revenueTrendChart');
    const grad = trendCtx.getContext('2d').createLinearGradient(0, 0, 0, 280);
    grad.addColorStop(0,   'rgba(197,168,128,0.18)');
    grad.addColorStop(1,   'rgba(197,168,128,0)');

    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($revenueLabels) ?>,
            datasets: [{
                label: 'Revenue',
                data: <?= json_encode($revenueData) ?>,
                borderColor: '#C5A880',
                backgroundColor: grad,
                borderWidth: 2.5,
                fill: true,
                tension: 0.38,
                pointRadius: 4,
                pointBackgroundColor: '#121212',
                pointBorderColor: '#C5A880',
                pointBorderWidth: 2,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    ...premiumTooltip,
                    callbacks: { label: c => ' Revenue: <?= $currency ?>' + c.parsed.y.toFixed(2) }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { font: { size: 10 }, callback: v => '<?= $currency ?>' + v.toLocaleString() }
                }
            }
        }
    });

    // ── 3. Peak Ordering Hours (Bar) ────────────────────────────────
    new Chart(document.getElementById('peakHoursChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($hourLabels) ?>,
            datasets: [{
                label: 'Orders',
                data: <?= json_encode(array_values($hourMap)) ?>,
                backgroundColor: <?= json_encode(array_values($hourMap)) ?>.map(v => {
                    const max = Math.max(...<?= json_encode(array_values($hourMap)) ?>);
                    return v === max ? '#C5A880' : 'rgba(197,168,128,0.25)';
                }),
                borderRadius: 6,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    ...premiumTooltip,
                    callbacks: { label: c => ' Orders: ' + c.parsed.y }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 9 }, maxRotation: 45 } },
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 10 } } }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
