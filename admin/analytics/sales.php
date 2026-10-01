<?php
// ============================================================
// FarmersBD — Admin Sales Analytics
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';

require_admin();
$pdo = get_db_connection();

$range = sanitize_input($_GET['range'] ?? '30days');
$days = 30;
if ($range === '7days') $days = 7;
elseif ($range === '90days') $days = 90;
elseif ($range === '365days') $days = 365;

// Overall metrics in selected range
$sales_stmt = $pdo->prepare("SELECT 
    COUNT(*) as total_orders,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_order_value,
    SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as paid_revenue,
    SUM(CASE WHEN order_status = 'delivered' THEN total_amount ELSE 0 END) as delivered_revenue
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) AND order_status != 'cancelled'");
$sales_stmt->execute([$days]);
$metrics = $sales_stmt->fetch();

$total_revenue     = (float)($metrics['total_revenue'] ?? 0);
$total_orders      = (int)($metrics['total_orders'] ?? 0);
$avg_order_value   = (float)($metrics['avg_order_value'] ?? 0);
$delivered_revenue = (float)($metrics['delivered_revenue'] ?? 0);

// Daily revenue chart data
$chart_stmt = $pdo->prepare("SELECT 
    DATE(created_at) as sale_date,
    SUM(total_amount) as daily_total,
    COUNT(*) as daily_orders
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) AND order_status != 'cancelled'
    GROUP BY DATE(created_at) 
    ORDER BY sale_date ASC");
$chart_stmt->execute([$days]);
$daily_sales = $chart_stmt->fetchAll();

$dates = [];
$revenues = [];
$orders_count = [];
foreach ($daily_sales as $row) {
    $dates[] = date('d M', strtotime($row['sale_date']));
    $revenues[] = (float)$row['daily_total'];
    $orders_count[] = (int)$row['daily_orders'];
}

// Payment method distribution
$pay_stmt = $pdo->prepare("SELECT payment_method, COUNT(*) as count, SUM(total_amount) as total 
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) AND order_status != 'cancelled' 
    GROUP BY payment_method");
$pay_stmt->execute([$days]);
$payment_breakdown = $pay_stmt->fetchAll();

// Top selling products by revenue
$top_prod_stmt = $pdo->prepare("SELECT 
    p.name, p.name_bn, p.image, 
    SUM(oi.quantity) as total_sold,
    SUM(oi.quantity * oi.price) as revenue
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    JOIN products p ON p.id = oi.product_id
    WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) AND o.order_status != 'cancelled'
    GROUP BY p.id
    ORDER BY revenue DESC
    LIMIT 5");
$top_prod_stmt->execute([$days]);
$top_products = $top_prod_stmt->fetchAll();

$page_title = 'সেলস অ্যানালিটিক্স | FarmersBD Admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-graph-up text-primary me-2"></i>বিক্রয় ও আয় অ্যানালিটিক্স (Sales Analytics)</h1>
            <p class="text-muted small mb-0">দৈনিক ও মাসিক মোট বিক্রয়, গড় অর্ডার মূল্য এবং রাজস্ব প্রতিবেদন</p>
        </div>
        <div class="btn-group shadow-sm">
            <a href="?range=7days" class="btn btn-sm <?= $range === '7days' ? 'btn-primary' : 'btn-outline-secondary' ?>">গত ৭ দিন</a>
            <a href="?range=30days" class="btn btn-sm <?= $range === '30days' ? 'btn-primary' : 'btn-outline-secondary' ?>">গত ৩০ দিন</a>
            <a href="?range=90days" class="btn btn-sm <?= $range === '90days' ? 'btn-primary' : 'btn-outline-secondary' ?>">গত ৩ মাস</a>
            <a href="?range=365days" class="btn btn-sm <?= $range === '365days' ? 'btn-primary' : 'btn-outline-secondary' ?>">১ বছর</a>
        </div>
    </div>
</div>

<!-- Key Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success-subtle text-success p-3 fs-4"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="text-muted small">মোট বিক্রয় (Revenue)</div>
                    <div class="fs-4 fw-bold text-success"><?= format_price($total_revenue) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary-subtle text-primary p-3 fs-4"><i class="bi bi-bag-check"></i></div>
                <div>
                    <div class="text-muted small">মোট অর্ডার সংখ্যা</div>
                    <div class="fs-4 fw-bold"><?= $total_orders ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-info-subtle text-info p-3 fs-4"><i class="bi bi-calculator"></i></div>
                <div>
                    <div class="text-muted small">গড় অর্ডার মূল্য (AOV)</div>
                    <div class="fs-4 fw-bold text-info"><?= format_price($avg_order_value) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-warning-subtle text-warning p-3 fs-4"><i class="bi bi-truck"></i></div>
                <div>
                    <div class="text-muted small">সম্পন্ন ডেলিভারি আয়</div>
                    <div class="fs-4 fw-bold text-dark"><?= format_price($delivered_revenue) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sales Revenue Chart -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 fw-bold d-flex align-items-center justify-content-between">
                <span><i class="bi bi-bar-chart-line text-primary me-2"></i>আয়ের গতিধারা (Revenue Timeline)</span>
                <span class="badge bg-light text-secondary border"><?= $range ?></span>
            </div>
            <div class="card-body p-4">
                <canvas id="salesChart" height="260"></canvas>
            </div>
        </div>
    </div>

    <!-- Payment Methods Breakdown -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 fw-bold">
                <i class="bi bi-credit-card text-primary me-2"></i>পেমেন্ট পদ্ধতির বণ্টন
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-center">
                <?php if (empty($payment_breakdown)): ?>
                    <div class="text-center text-muted py-4">কোনো পেমেন্ট তথ্য নেই</div>
                <?php else: ?>
                    <div class="list-group list-group-flush mb-3">
                        <?php foreach ($payment_breakdown as $pb): ?>
                            <?php $pct = $total_revenue > 0 ? round(($pb['total'] / $total_revenue) * 100, 1) : 0; ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold text-dark"><?= strtoupper(e($pb['payment_method'])) ?></span>
                                    <span class="text-success fw-bold"><?= format_price($pb['total']) ?> (<?= $pct ?>%)</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pct ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Top Products by Sales -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 fw-bold">
        <i class="bi bi-trophy text-warning me-2"></i>সর্বোচ্চ আয়ের পণ্যসমূহ (Top Revenue Products)
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">পণ্য</th>
                        <th>বিক্রয় পরিমাণ</th>
                        <th>মোট অর্জিত রাজস্ব</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($top_products)): ?>
                        <tr><td colspan="3" class="text-center py-4 text-muted">কোনো ডাটা পাওয়া যায়নি</td></tr>
                    <?php else: ?>
                        <?php foreach ($top_products as $tp): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($tp['image'])): ?>
                                            <img src="<?= asset($tp['image']) ?>" class="rounded object-fit-cover" width="40" height="40">
                                        <?php endif; ?>
                                        <div class="fw-bold text-dark"><?= e($tp['name_bn'] ?: $tp['name']) ?></div>
                                    </div>
                                </td>
                                <td><span class="badge bg-info-subtle text-info fs-7"><?= (int)$tp['total_sold'] ?> টি</span></td>
                                <td class="fw-bold text-success"><?= format_price($tp['revenue']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($dates) ?>,
        datasets: [{
            label: 'বিক্রয় (৳)',
            data: <?= json_encode($revenues) ?>,
            borderColor: '#2e7d32',
            backgroundColor: 'rgba(46, 125, 50, 0.1)',
            fill: true,
            tension: 0.3,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) { return '৳' + value.toLocaleString(); }
                }
            }
        }
    }
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
