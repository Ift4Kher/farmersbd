<?php
// ============================================================
// FarmersBD — Admin Notifications Center
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/flash.php';
require_once $base . '/includes/csrf.php';
require_once $base . '/includes/admin-auth.php';

require_admin();
$pdo = get_db_connection();

// Ensure admin_notifications table exists
try {
    $pdo->query("SELECT 1 FROM admin_notifications LIMIT 1");
} catch (PDOException $e) {
    $pdo->exec("CREATE TABLE admin_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(50) DEFAULT 'info',
        message TEXT NOT NULL,
        link VARCHAR(255) DEFAULT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
}

// Seed sample notifications if empty
$count_chk = (int)($pdo->query("SELECT COUNT(*) FROM admin_notifications")->fetchColumn() ?? 0);
if ($count_chk === 0) {
    $samples = [
        ['order', 'নতুন অর্ডার এসেছে #FB1024 - রফিক উদ্দিন (৳1,450)', 'admin/orders/', 0, date('Y-m-d H:i:s', strtotime('-5 minutes'))],
        ['order', 'অর্ডার #FB1023 পেন্ডিং আছে - করিম হোসেন (৳850)', 'admin/orders/?status=pending', 0, date('Y-m-d H:i:s', strtotime('-12 minutes'))],
        ['stock', 'সতর্কতা: "প্রিমিয়াম রুই মাছ" এর স্টক ৫ কেজির নিচে নেমে গেছে', 'admin/inventory/index.php', 0, date('Y-m-d H:i:s', strtotime('-28 minutes'))],
        ['review', 'নতুন রিভিউ এসেছে: "অর্গানিক ফিশ ফিড" পণ্যে ৫ স্টার রিভিউ', 'admin/reviews/index.php', 0, date('Y-m-d H:i:s', strtotime('-1 hour'))],
        ['lead', 'পরিত্যক্ত কার্ট পাওয়া গেছে: সুমাইয়া আক্তার (ফোন: 01712345678)', 'admin/lead-recovery/index.php', 0, date('Y-m-d H:i:s', strtotime('-2 hours'))],
        ['system', 'সিস্টেম অটোমেটিক ব্যাকআপ সফলভাবে সম্পন্ন হয়েছে', 'admin/health/index.php', 1, date('Y-m-d H:i:s', strtotime('-1 day'))],
    ];
    $ins = $pdo->prepare("INSERT INTO admin_notifications (type, message, link, is_read, created_at) VALUES (?, ?, ?, ?, ?)");
    foreach ($samples as $s) {
        $ins->execute($s);
    }
}

// Actions
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'read_all') {
        $pdo->exec("UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0");
        set_flash('success', 'সকল নোটিফিকেশন পড়া হয়েছে হিসেবে মার্ক করা হয়েছে।');
        redirect(BASE_URL . '/admin/notifications/index.php');
    }
    if ($_GET['action'] === 'read_single' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $pdo->prepare("UPDATE admin_notifications SET is_read = 1 WHERE id = ?")->execute([$id]);
        if (!empty($_GET['redirect'])) {
            redirect(BASE_URL . '/' . ltrim($_GET['redirect'], '/'));
        }
        redirect(BASE_URL . '/admin/notifications/index.php');
    }
    if ($_GET['action'] === 'delete_all') {
        $pdo->exec("DELETE FROM admin_notifications WHERE is_read = 1");
        set_flash('success', 'পড়া হয়ে যাওয়া পুরানো নোটিফিকেশন মুছে ফেলা হয়েছে।');
        redirect(BASE_URL . '/admin/notifications/index.php');
    }
}

$filter = sanitize_input($_GET['filter'] ?? 'all');
$where = [];
if ($filter === 'unread') {
    $where[] = "is_read = 0";
} elseif ($filter === 'read') {
    $where[] = "is_read = 1";
}
$where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$notifications = $pdo->query("SELECT * FROM admin_notifications $where_sql ORDER BY created_at DESC LIMIT 50")->fetchAll();
$unread_total = (int)($pdo->query("SELECT COUNT(*) FROM admin_notifications WHERE is_read = 0")->fetchColumn() ?? 0);

$page_title = 'নোটিফিকেশন সেন্টার — FarmersBD Admin';
include dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header mb-3">
    <div>
        <h3><i class="bi bi-bell-fill text-primary me-2"></i>নোটিফিকেশন সেন্টার</h3>
        <p class="text-muted small mb-0">অর্ডার, স্টক, রিভিউ এবং সিস্টেম অ্যালার্টসমূহ পরিচালনা করুন</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('admin/notifications/index.php?action=read_all') ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-check2-all"></i> সব পড়ুন
        </a>
        <a href="<?= url('admin/settings/notifications.php') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-gear"></i> সেটিংস
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex gap-1">
                    <a href="<?= url('admin/notifications/index.php') ?>" class="btn btn-sm <?= $filter === 'all' ? 'btn-primary' : 'btn-light border' ?>">সকল (<?= count($notifications) ?>)</a>
                    <a href="<?= url('admin/notifications/index.php?filter=unread') ?>" class="btn btn-sm <?= $filter === 'unread' ? 'btn-warning' : 'btn-light border' ?>">অপঠিত (<?= $unread_total ?>)</a>
                    <a href="<?= url('admin/notifications/index.php?filter=read') ?>" class="btn btn-sm <?= $filter === 'read' ? 'btn-secondary' : 'btn-light border' ?>">পঠিত</a>
                </div>
                <?php if ($unread_total === 0): ?>
                <a href="<?= url('admin/notifications/index.php?action=delete_all') ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('পঠিত নোটিফিকেশনগুলো মুছে ফেলতে চান?')">
                    <i class="bi bi-trash"></i> পুরানো নোটিফিকেশন মুছুন
                </a>
                <?php endif; ?>
            </div>

            <div class="card-body p-0">
                <?php if (empty($notifications)): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-bell-slash fs-1 d-block mb-2 text-secondary"></i>
                    <div>কোনো নোটিফিকেশন পাওয়া যায়নি।</div>
                </div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($notifications as $n): 
                        $icon = match($n['type'] ?? 'info') {
                            'order' => 'bi-cart-check text-success',
                            'stock' => 'bi-exclamation-triangle text-danger',
                            'review' => 'bi-star text-warning',
                            'lead' => 'bi-people text-info',
                            default => 'bi-info-circle text-primary'
                        };
                    ?>
                    <div class="list-group-item d-flex align-items-center justify-content-between gap-3 p-3 <?= $n['is_read'] ? 'bg-white' : 'bg-light' ?>">
                        <div class="d-flex align-items-start gap-3">
                            <div class="fs-4 mt-1"><i class="bi <?= $icon ?>"></i></div>
                            <div>
                                <div class="fw-<?= $n['is_read'] ? 'normal' : 'bold' ?> text-dark" style="font-size: 0.88rem;">
                                    <?= e($n['message']) ?>
                                </div>
                                <div class="text-muted small mt-1">
                                    <i class="bi bi-clock me-1"></i> <?= date('j M Y, h:i A', strtotime($n['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <?php if (!empty($n['link'])): ?>
                            <a href="<?= url('admin/notifications/index.php?action=read_single&id='.(int)$n['id'].'&redirect='.urlencode($n['link'])) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-box-arrow-up-right"></i> দেখুন
                            </a>
                            <?php endif; ?>
                            <?php if (!$n['is_read']): ?>
                            <a href="<?= url('admin/notifications/index.php?action=read_single&id='.(int)$n['id']) ?>" class="btn btn-sm btn-light border" title="পড়া হয়েছে হিসেবে মার্ক করুন">
                                <i class="bi bi-check2"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
