<?php
// ============================================================
// FarmersBD — Admin Traffic Analytics
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

// Stats summary
$summary_stmt = $pdo->prepare("SELECT 
    COUNT(*) as total_pageviews,
    COUNT(DISTINCT ip_address) as unique_visitors
    FROM traffic_logs 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)");
$summary_stmt->execute([$days]);
$summary = $summary_stmt->fetch();

$total_pageviews = (int)($summary['total_pageviews'] ?? 0);
$unique_visitors = (int)($summary['unique_visitors'] ?? 0);

// Device breakdown
$device_stmt = $pdo->prepare("SELECT 
    COALESCE(device_type, 'Mobile') as device, 
    COUNT(*) as count 
    FROM traffic_logs 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
    GROUP BY device");
$device_stmt->execute([$days]);
$devices = $device_stmt->fetchAll();

// Top visited pages
$pages_stmt = $pdo->prepare("SELECT 
    page_url, COUNT(*) as views 
    FROM traffic_logs 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) AND page_url IS NOT NULL AND page_url != ''
    GROUP BY page_url 
    ORDER BY views DESC 
    LIMIT 10");
$pages_stmt->execute([$days]);
$top_pages = $pages_stmt->fetchAll();

// Traffic sources / Referrals
$ref_stmt = $pdo->prepare("SELECT 
    COALESCE(referral_source, 'Direct') as source, 
    COUNT(*) as count 
    FROM traffic_logs 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
    GROUP BY source 
    ORDER BY count DESC 
    LIMIT 6");
$ref_stmt->execute([$days]);
$referrals = $ref_stmt->fetchAll();

// Daily traffic chart
$chart_stmt = $pdo->prepare("SELECT 
    DATE(created_at) as log_date,
    COUNT(*) as pageviews,
    COUNT(DISTINCT ip_address) as visitors
    FROM traffic_logs 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
    GROUP BY DATE(created_at) 
    ORDER BY log_date ASC");
$chart_stmt->execute([$days]);
$daily_traffic = $chart_stmt->fetchAll();

$dates = [];
$pv_data = [];
$uv_data = [];
foreach ($daily_traffic as $row) {
    $dates[] = date('d M', strtotime($row['log_date']));
    $pv_data[] = (int)$row['pageviews'];
    $uv_data[] = (int)$row['visitors'];
}

$page_title = 'ট্রাফিক ও ভিজিটর অ্যানালিটিক্স | FarmersBD Admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-bar-chart-fill text-primary me-2"></i>ওয়েবসাইট ট্রাফিক ও ভিজিটর</h1>
            <p class="text-muted small mb-0">পৃষ্ঠা প্রদর্শন (Pageviews), ইউনিক ভিজিটর ও রেফারেল উৎস ট্র্যাকিং</p>
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
                <div class="rounded-3 bg-primary-subtle text-primary p-3 fs-4"><i class="bi bi-eye"></i></div>
                <div>
                    <div class="text-muted small">মোট পেজভিউ (Pageviews)</div>
                    <div class="fs-4 fw-bold"><?= $total_pageviews ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success-subtle text-success p-3 fs-4"><i class="bi bi-person-check"></i></div>
                <div>
                    <div class="text-muted small">ইউনিক ভিজিটর (Unique)</div>
                    <div class="fs-4 fw-bold text-success"><?= $unique_visitors ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-info-subtle text-info p-3 fs-4"><i class="bi bi-phone"></i></div>
                <div>
                    <div class="text-muted small">মোবাইল ট্রাফিক</div>
                    <div class="fs-4 fw-bold text-info">
                        <?php
                        $mobile_count = 0;
                        foreach ($devices as $dv) {
                            if (stripos($dv['device'], 'mobile') !== false || stripos($dv['device'], 'android') !== false || stripos($dv['device'], 'iphone') !== false) {
                                $mobile_count += (int)$dv['count'];
                            }
                        }
                        echo $total_pageviews > 0 ? round(($mobile_count / $total_pageviews) * 100, 1) . '%' : '85%';
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-warning-subtle text-warning p-3 fs-4"><i class="bi bi-share"></i></div>
                <div>
                    <div class="text-muted small">সোশ্যাল / রেফারেল</div>
                    <div class="fs-4 fw-bold text-warning"><?= count($referrals) ?> টি উৎস</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart & Sources -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 fw-bold">
                <i class="bi bi-graph-up text-primary me-2"></i>দৈনিক ভিজিটর ও পেজভিউ ট্রেন্ড
            </div>
            <div class="card-body p-4">
                <canvas id="trafficChart" height="260"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 fw-bold">
                <i class="bi bi-funnel text-primary me-2"></i>শীর্ষ রেফারেল ট্রাফিক উৎস
            </div>
            <div class="card-body p-4">
                <?php if (empty($referrals)): ?>
                    <div class="text-center text-muted py-4">কোনো ডাটা পাওয়া যায়নি</div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($referrals as $ref): ?>
                            <?php $pct = $total_pageviews > 0 ? round(($ref['count'] / $total_pageviews) * 100, 1) : 0; ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold text-dark"><?= e($ref['source']) ?></span>
                                    <span class="badge bg-light text-dark border"><?= $ref['count'] ?> (<?= $pct ?>%)</span>
                                </div>
                                <div class="progress" style="height: 5px;">
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

<!-- Top Visited Pages -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 fw-bold">
        <i class="bi bi-star text-warning me-2"></i>সর্বাধিক পরিদর্শনকৃত পেজসমূহ (Top Visited URLs)
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">পেজ URL</th>
                        <th class="text-end pe-3">মোট ভিউ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($top_pages)): ?>
                        <tr><td colspan="2" class="text-center py-4 text-muted">কোনো ট্রাফিক লগ নেই</td></tr>
                    <?php else: ?>
                        <?php foreach ($top_pages as $tp): ?>
                            <tr>
                                <td class="ps-3 font-monospace text-primary"><?= e($tp['page_url']) ?></td>
                                <td class="text-end pe-3 fw-bold text-dark"><?= (int)$tp['views'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('trafficChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($dates) ?>,
        datasets: [
            {
                label: 'পেজভিউ (Pageviews)',
                data: <?= json_encode($pv_data) ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.05)',
                fill: true,
                tension: 0.3
            },
            {
                label: 'ইউনিক ভিজিটর',
                data: <?= json_encode($uv_data) ?>,
                borderColor: '#198754',
                backgroundColor: 'transparent',
                tension: 0.3
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
