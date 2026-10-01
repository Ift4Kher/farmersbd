<?php
// ============================================================
// FarmersBD — User Account Dashboard
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/flash.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/seo.php';

require_login();
$user   = current_user();
$userId = (int) $_SESSION['user_id'];

$stats = [
    'orders'        => db_query_one("SELECT COUNT(*) AS cnt FROM orders WHERE user_id = ?", [$userId])['cnt'] ?? 0,
    'pending'       => db_query_one("SELECT COUNT(*) AS cnt FROM orders WHERE user_id = ? AND order_status = 'pending'", [$userId])['cnt'] ?? 0,
    'diagnoses'     => db_query_one("SELECT COUNT(*) AS cnt FROM ai_diagnoses WHERE user_id = ?", [$userId])['cnt'] ?? 0,
    'consultations' => db_query_one("SELECT COUNT(*) AS cnt FROM consultations WHERE user_id = ?", [$userId])['cnt'] ?? 0,
];

$recentOrders = db_query(
    "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5",
    [$userId]
);

$page_seo = ['title' => 'ড্যাশবোর্ড | ' . setting('site_name', 'FarmersBD')];
include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/navbar.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="bi bi-speedometer2 me-2"></i>আমার ড্যাশবোর্ড</h1>
        <p class="mb-0 opacity-75">স্বাগতম, <?= e($user['name']) ?>!</p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <?php include dirname(__DIR__) . '/includes/account-sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Stat Cards -->
            <div class="row g-3 mb-4">
                <?php
                $statCards = [
                    ['value' => $stats['orders'],        'label' => 'মোট অর্ডার',        'icon' => 'bi-bag-check',      'color' => 'text-primary', 'bg' => 'bg-primary-subtle'],
                    ['value' => $stats['pending'],       'label' => 'অপেক্ষমাণ অর্ডার',   'icon' => 'bi-hourglass',      'color' => 'text-warning', 'bg' => 'bg-warning-subtle'],
                    ['value' => $stats['diagnoses'],     'label' => 'AI বিশ্লেষণ',        'icon' => 'bi-cpu',            'color' => 'text-success', 'bg' => 'bg-success-subtle'],
                    ['value' => $stats['consultations'], 'label' => 'পরামর্শ সংখ্যা',      'icon' => 'bi-chat-dots',      'color' => 'text-info',    'bg' => 'bg-info-subtle'],
                ];
                foreach ($statCards as $sc): ?>
                <div class="col-sm-6 col-xl-3">
                    <div class="modern-card p-3 h-100 d-flex align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="service-icon <?= e($sc['bg']) ?> <?= e($sc['color']) ?>" style="width:48px;height:48px;font-size:1.3rem">
                                <i class="bi <?= e($sc['icon']) ?>"></i>
                            </div>
                            <div>
                                <div class="stat-number fs-4 fw-bold text-dark"><?= (int)$sc['value'] ?></div>
                                <div class="stat-label small text-muted fw-medium"><?= e($sc['label']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Recent Orders -->
            <div class="modern-card p-0">
                <div class="card-body p-0">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                        <h2 class="h6 fw-bold mb-0">সাম্প্রতিক অর্ডার</h2>
                        <a href="<?= url('account/orders.php') ?>" class="btn btn-outline-primary btn-sm">সব দেখুন</a>
                    </div>
                    <?php if (empty($recentOrders)): ?>
                    <div class="empty-state p-4">
                        <i class="bi bi-bag mb-2" style="font-size:2rem"></i>
                        <p class="mb-0">এখনো কোনো অর্ডার নেই।</p>
                        <a href="<?= url('products/') ?>" class="btn btn-primary btn-sm mt-2">কেনাকাটা করুন</a>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>অর্ডার নং</th>
                                    <th>তারিখ</th>
                                    <th>মোট</th>
                                    <th>স্ট্যাটাস</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td><span class="fw-semibold"><?= e($order['order_number']) ?></span></td>
                                    <td><?= bangla_date($order['created_at']) ?></td>
                                    <td><?= format_price((float)$order['total']) ?></td>
                                    <td><?= order_status_badge($order['order_status']) ?></td>
                                    <td>
                                        <a href="<?= url('account/order-details.php?id=' . (int)$order['id']) ?>" class="btn btn-outline-secondary btn-sm">দেখুন</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
