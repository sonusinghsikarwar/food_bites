<?php
// admin/reports.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Total revenue in date range
$stmt = $pdo->prepare("SELECT SUM(grand_total) as total FROM orders WHERE DATE(created_at) BETWEEN ? AND ? AND payment_status = 'paid'");
$stmt->execute([$startDate, $endDate]);
$revenue = $stmt->fetch()['total'] ?? 0.00;

// Total orders in date range
$stmt = $pdo->prepare("SELECT COUNT(id) as count FROM orders WHERE DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$startDate, $endDate]);
$ordersCount = $stmt->fetch()['count'] ?? 0;

// Top selling item in date range
$stmt = $pdo->prepare("SELECT p.name, SUM(oi.quantity) as qty 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
                       JOIN orders o ON oi.order_id = o.id
                       WHERE DATE(o.created_at) BETWEEN ? AND ?
                       GROUP BY oi.product_id 
                       ORDER BY qty DESC LIMIT 1");
$stmt->execute([$startDate, $endDate]);
$topProduct = $stmt->fetch();

// Detailed daily sales list
$stmt = $pdo->prepare("SELECT DATE(created_at) as date, COUNT(id) as orders_count, SUM(grand_total) as daily_total 
                       FROM orders 
                       WHERE DATE(created_at) BETWEEN ? AND ? AND payment_status = 'paid'
                       GROUP BY DATE(created_at) 
                       ORDER BY date DESC");
$stmt->execute([$startDate, $endDate]);
$reports = $stmt->fetchAll();

// Charts labels and datasets
$chartLabels = [];
$chartData = [];
foreach (array_reverse($reports) as $rep) {
    $chartLabels[] = date('d M', strtotime($rep['date']));
    $chartData[] = floatval($rep['daily_total']);
}
?>

<div class="glass-card p-4 mb-4">
    <h5 class="fw-bold text-dark dark-text-white mb-3">Filter Report Period</h5>
    <form action="reports.php" method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label text-xs fw-bold text-uppercase text-muted">Start Date</label>
            <input type="date" name="start_date" class="form-control rounded-pill border-light p-3 shadow-none" value="<?= htmlspecialchars($startDate) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label text-xs fw-bold text-uppercase text-muted">End Date</label>
            <input type="date" name="end_date" class="form-control rounded-pill border-light p-3 shadow-none" value="<?= htmlspecialchars($endDate) ?>">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-warning rounded-pill w-100 py-3 fw-bold"><i class="fa-solid fa-filter me-2"></i> Apply Date Filter</button>
        </div>
    </form>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="glass-card p-4 text-center">
            <h6 class="text-secondary text-xs uppercase tracking-wider mb-2">Total Sales Revenue</h6>
            <h3 class="fw-bold text-success mb-0"><?= $currency ?><?= number_format($revenue, 2) ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="glass-card p-4 text-center">
            <h6 class="text-secondary text-xs uppercase tracking-wider mb-2">Total Orders Placed</h6>
            <h3 class="fw-bold text-warning mb-0"><?= number_format($ordersCount) ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="glass-card p-4 text-center">
            <h6 class="text-secondary text-xs uppercase tracking-wider mb-2">Best Selling Product</h6>
            <h3 class="fw-bold text-info mb-0 text-truncate px-2"><?= $topProduct ? htmlspecialchars($topProduct['name']) : 'None' ?></h3>
            <?php if ($topProduct): ?>
                <span class="small text-muted">(Sold <?= $topProduct['qty'] ?> units)</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Chart Column -->
    <div class="col-lg-7">
        <div class="glass-card p-4">
            <h5 class="fw-bold text-dark dark-text-white mb-4">Sales Trend Chart</h5>
            <div style="height: 320px;">
                <canvas id="reportsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Details Table Column -->
    <div class="col-lg-5">
        <div class="glass-card p-4 h-100">
            <h5 class="fw-bold text-dark dark-text-white mb-4">Daily Sales Report Breakup</h5>
            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table align-middle border-light mb-0">
                    <thead>
                        <tr class="text-secondary text-xs uppercase tracking-wider">
                            <th>Date</th>
                            <th class="text-center">Orders</th>
                            <th class="text-end">Total Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reports)): ?>
                            <?php foreach ($reports as $rep): ?>
                                <tr>
                                    <td class="fw-semibold text-dark dark-text-white"><?= date('d M Y', strtotime($rep['date'])) ?></td>
                                    <td class="text-center"><?= $rep['orders_count'] ?></td>
                                    <td class="text-end fw-bold text-warning"><?= $currency ?><?= number_format($rep['daily_total'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">No sales logged in date range.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('reportsChart').getContext('2d');
    const isDark = document.body.classList.contains('dark-mode');
    const textColor = isDark ? '#a0aec0' : '#4a5568';

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Sales Revenue (<?= $currency ?>)',
                data: @json($chartData),
                backgroundColor: 'rgba(255, 193, 7, 0.85)',
                borderColor: '#ffc107',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { ticks: { color: textColor } },
                y: { beginAtZero: true, ticks: { color: textColor } }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
