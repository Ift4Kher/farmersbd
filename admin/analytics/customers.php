<?php
// ============================================================
// FarmersBD — Admin Customer Analytics
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';

require_admin();
$pdo = get_db_connection();

// Customer general stats
$cust_stats_stmt = $pdo->query("SELECT 
    COUNT(*) as total_registered_users,
    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users
    FROM users");
$cust_stats = $cust_stats_stmt->fetch();
$total_users = (int)($cust_stats['total_registered_users'] ?? 0);
$active_users = (int)($cust_stats['active_users'] ?? 0);

// Order customer aggregation (registered + guest)
$order_cust_stmt = $pdo->query("SELECT 
    COUNT(DISTINCT phone) as total_unique_buyers,
    COUNT(DISTINCT CASE WHEN user_id IS NOT NULL THEN user_id END) as registered_buyers
    FROM orders WHERE order_status != 'cancelled'");
$order_cust = $order_cust_stmt->fetch();
$unique_buyers = (int)($order_cust['total_unique_buyers'] ?? 0);

// Repeat customer analysis
$repeat_stmt = $pdo->query("SELECT phone, COUNT(*) as order_count 
    FROM orders WHERE order_status != 'cancelled' 
    GROUP BY phone 
    HAVING order_count > 1");
$repeat_customers = count($repeat_stmt->fetchAll());
$repeat_rate = $unique_buyers > 0 ? round(($repeat_customers / $unique_buyers) * 100, 1) : 0;

// Top VIP customers by spending
$vip_stmt = $pdo->query("SELECT 
    customer_name, phone, email, district,
    COUNT(*) as total_orders,
    SUM(total_amount) as total_spent,
    MAX(created_at) as last_order_date
    FROM orders 
    WHERE order_status != 'cancelled'
    GROUP BY phone 
    ORDER BY total_spent DESC 
    LIMIT 10");
$vip_customers = $vip_stmt->fetchAll();

$page_title = 'কাস্টমার অ্যানালিটিক্স | FarmersBD Admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-people-fill text-primary me-2"></i>গ্রাহক অ্যানালিটিক্স ও লয়্যালটি</h1>
            <p class="text-muted small mb-0">নিবন্ধিত ব্যবহারকারী, রিপিট ক্রেতার অনুপাত এবং শীর্ষ মূল্যবান (VIP) গ্রাহকদের তথ্য</p>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary-subtle text-primary p-3 fs-4"><i class="bi bi-people"></i></div>
                <div>
                    <div class="text-muted small">নিবন্ধিত গ্রাহক</div>
                    <div class="fs-4 fw-bold"><?= $total_users ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success-subtle text-success p-3 fs-4"><i class="bi bi-bag-heart"></i></div>
                <div>
                    <div class="text-muted small">ইউনিক ক্রেতা (Unique Buyers)</div>
                    <div class="fs-4 fw-bold text-success"><?= $unique_buyers ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-info-subtle text-info p-3 fs-4"><i class="bi bi-arrow-repeat"></i></div>
                <div>
                    <div class="text-muted small">রিপিট ক্রেতা (Repeat Buyers)</div>
                    <div class="fs-4 fw-bold text-info"><?= $repeat_customers ?> জন</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-warning-subtle text-warning p-3 fs-4"><i class="bi bi-stars"></i></div>
                <div>
                    <div class="text-muted small">রিপিট ক্রয়ের হার (Repeat Rate)</div>
                    <div class="fs-4 fw-bold text-warning"><?= $repeat_rate ?>%</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top VIP Customers -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 fw-bold">
        <i class="bi bi-award text-warning me-2"></i>শীর্ষ মূল্যবান গ্রাহকবৃন্দ (Top VIP Customers)
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">গ্রাহকের নাম ও ফোন</th>
                        <th>জেলা</th>
                        <th>মোট অর্ডার সংখ্যা</th>
                        <th>মোট কেনাকাটার পরিমাণ</th>
                        <th>সর্বশেষ অর্ডার</th>
                        <th class="text-end pe-3">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($vip_customers)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">কোনো গ্রাহকের অর্ডার রেকর্ড নেই</td></tr>
                    <?php else: ?>
                        <?php foreach ($vip_customers as $vip): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark"><?= e($vip['customer_name'] ?: 'অজ্ঞাত গ্রাহক') ?></div>
                                    <div class="text-muted small font-monospace"><i class="bi bi-telephone me-1"></i><?= e($vip['phone']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= e($vip['district'] ?: 'বাংলাদেশ') ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary fw-semibold fs-7"><?= (int)$vip['total_orders'] ?> টি অর্ডার</span>
                                </td>
                                <td class="fw-bold text-success fs-6"><?= format_price($vip['total_spent']) ?></td>
                                <td class="text-muted small"><?= date('d M, Y', strtotime($vip['last_order_date'])) ?></td>
                                <td class="text-end pe-3">
                                    <a href="<?= url('admin/orders/?q=' . urlencode($vip['phone'])) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-bag me-1"></i> অর্ডারসমূহ
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
