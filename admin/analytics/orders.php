<?php
// ============================================================
// FarmersBD — Admin Orders Analytics
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

// Status breakdown
$status_stmt = $pdo->prepare("SELECT order_status, COUNT(*) as total_count, SUM(total_amount) as total_val 
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
    GROUP BY order_status");
$status_stmt->execute([$days]);
$statuses = $status_stmt->fetchAll();

$total_orders = 0;
$status_counts = [
    'pending' => 0, 'confirmed' => 0, 'processing' => 0,
    'shipped' => 0, 'delivered' => 0, 'returned' => 0, 'cancelled' => 0
];
foreach ($statuses as $st) {
    $total_orders += (int)$st['total_count'];
    $status_counts[$st['order_status']] = (int)$st['total_count'];
}

// Order fulfillment rate
$delivered = $status_counts['delivered'] ?? 0;
$cancelled = $status_counts['cancelled'] ?? 0;
$returned  = $status_counts['returned'] ?? 0;
$fulfillment_rate = $total_orders > 0 ? round(($delivered / $total_orders) * 100, 1) : 0;
$cancel_rate      = $total_orders > 0 ? round(($cancelled / $total_orders) * 100, 1) : 0;
$return_rate      = $total_orders > 0 ? round(($returned / $total_orders) * 100, 1) : 0;

// Daily order volume chart
$chart_stmt = $pdo->prepare("SELECT 
    DATE(created_at) as order_date,
    COUNT(*) as total_count,
    SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_count
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
    GROUP BY DATE(created_at) 
    ORDER BY order_date ASC");
$chart_stmt->execute([$days]);
$daily_orders = $chart_stmt->fetchAll();

$dates = [];
$all_orders = [];
$deliv_orders = [];
foreach ($daily_orders as $row) {
    $dates[] = date('d M', strtotime($row['order_date']));
    $all_orders[] = (int)$row['total_count'];
    $deliv_orders[] = (int)$row['delivered_count'];
}

// District / City distribution
$district_stmt = $pdo->prepare("SELECT district, COUNT(*) as count, SUM(total_amount) as total 
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) AND district IS NOT NULL AND district != ''
    GROUP BY district 
    ORDER BY count DESC 
    LIMIT 6");
$district_stmt->execute([$days]);
$districts = $district_stmt->fetchAll();

$page_title = 'অর্ডার অ্যানালিটিক্স | FarmersBD Admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-bag-check text-primary me-2"></i>অর্ডার অ্যানালিটিক্স ও ফুলফিলমেন্ট</h1>
            <p class="text-muted small mb-0">অর্ডার স্ট্যাটাস বিতরণ, ডেলিভারি সফলতার হার এবং জেলাভিত্তিক অর্ডার ভলিউম</p>
        </div>
        <div class="btn-group shadow-sm">
            <a href="?range=7days" class="btn btn-sm <?= $range === '7days' ? 'btn-primary' : 'btn-outline-secondary' ?>">গত ৭ দিন</a>
            <a href="?range=30days" class="btn btn-sm <?= $range === '30days' ? 'btn-primary' : 'btn-outline-secondary' ?>">গত ৩০ দিন</a>
            <a href="?range=90days" class="btn btn-sm <?= $range === '90days' ? 'btn-primary' : 'btn-outline-secondary' ?>">গত ৩ মাস</a>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary-subtle text-primary p-3 fs-4"><i class="bi bi-inboxes"></i></div>
                <div>
                    <div class="text-muted small">মোট অর্ডার প্লেসড</div>
                    <div class="fs-4 fw-bold"><?= $total_orders ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success-subtle text-success p-3 fs-4"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="text-muted small">সফল ডেলিভারি রেট</div>
                    <div class="fs-4 fw-bold text-success"><?= $fulfillment_rate ?>%</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-danger-subtle text-danger p-3 fs-4"><i class="bi bi-x-circle"></i></div>
                <div>
                    <div class="text-muted small">বাতিল অর্ডার রেট</div>
                    <div class="fs-4 fw-bold text-danger"><?= $cancel_rate ?>%</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-warning-subtle text-warning p-3 fs-4"><i class="bi bi-arrow-return-left"></i></div>
                <div>
                    <div class="text-muted small">রিটার্ন রেট</div>
                    <div class="fs-4 fw-bold text-warning"><?= $return_rate ?>%</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart & District Distribution -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 fw-bold">
                <i class="bi bi-bar-chart text-primary me-2"></i>দৈনিক মোট অর্ডার বনাম ডেলিভার্ড
            </div>
            <div class="card-body p-4">
                <canvas id="ordersChart" height="260"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 fw-bold">
                <i class="bi bi-geo-alt text-primary me-2"></i>শীর্ষ জেলাভিত্তিক অর্ডার
            </div>
            <div class="card-body p-4">
                <?php if (empty($districts)): ?>
                    <div class="text-center text-muted py-4">কোনো তথ্য নেই</div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($districts as $d): ?>
                            <?php $pct = $total_orders > 0 ? round(($d['count'] / $total_orders) * 100, 1) : 0; ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold text-dark"><?= e($d['district']) ?></span>
                                    <span class="badge bg-primary-subtle text-primary"><?= $d['count'] ?> টি (<?= $pct ?>%)</span>
                                </div>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $pct ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('ordersChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($dates) ?>,
        datasets: [
            {
                label: 'মোট অর্ডার',
                data: <?= json_encode($all_orders) ?>,
                backgroundColor: '#0d6efd',
                borderRadius: 4
            },
            {
                label: 'ডেলিভার্ড অর্ডার',
                data: <?= json_encode($deliv_orders) ?>,
                backgroundColor: '#198754',
                borderRadius: 4
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        }
    }
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
