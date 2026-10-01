<?php
// ============================================================
// FarmersBD — Admin Customer Profile View
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/csrf.php';
require_once $base . '/includes/flash.php';

require_admin();
$id = isset($_GET['id']) ? (int)($_GET['id']) : 0;
$pdo = get_db_connection();

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    set_flash('error', 'গ্রাহক খুঁজে পাওয়া যায়নি।');
    redirect(BASE_URL . '/admin/users/index.php');
}

// Fetch user's orders
$orders_stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$orders_stmt->execute([$id]);
$orders = $orders_stmt->fetchAll();

$page_title = "গ্রাহক প্রোফাইল: " . h($user['name']) . " — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">গ্রাহক প্রোফাইল</h3>
        <span class="text-muted small">যোগদান: <?= format_date_bn($user['created_at']) ?></span>
    </div>
    <a href="<?= BASE_URL ?>/admin/users/index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> তালিকায় ফিরে যান</a>
</div>

<?php display_flash(); ?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body text-center">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center fw-bold mb-3" style="width:80px; height:80px; font-size:2rem;">
                    <?= mb_substr(h($user['name']), 0, 1) ?>
                </div>
                <h4 class="fw-bold mb-1"><?= h($user['name']) ?></h4>
                <p class="text-muted mb-2"><?= h($user['mobile']) ?></p>
                <span class="badge <?= $user['is_active'] ? 'bg-success' : 'bg-danger' ?> fs-6 px-3 py-2">
                    <?= $user['is_active'] ? 'সক্রিয় অ্যাকাউন্ট' : 'নিষ্ক্রিয় অ্যাকাউন্ট' ?>
                </span>
            </div>
            <div class="card-footer bg-white p-3">
                <form method="POST" action="<?= BASE_URL ?>/admin/users/toggle-status.php" onsubmit="return confirm('আপনি কি নিশ্চিত?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <input type="hidden" name="status" value="<?= $user['is_active'] ? '0' : '1' ?>">
                    <button type="submit" class="btn <?= $user['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?> w-100">
                        <i class="bi <?= $user['is_active'] ? 'bi-lock' : 'bi-unlock' ?>"></i> 
                        <?= $user['is_active'] ? 'অ্যাকাউন্ট ডিঅ্যাক্টিভেট করুন' : 'অ্যাকাউন্ট অ্যাক্টিভেট করুন' ?>
                    </button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="fw-bold mb-0">যোগাযোগ ও ঠিকানা</h5>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong><i class="bi bi-telephone text-primary me-2"></i> ফোন:</strong> <a href="tel:<?= h($user['mobile']) ?>" class="text-decoration-none"><?= h($user['mobile']) ?></a></p>
                <p class="mb-2"><strong><i class="bi bi-envelope text-primary me-2"></i> ইমেইল:</strong> <?= h($user['email'] ?? 'N/A') ?></p>
                <p class="mb-2"><strong><i class="bi bi-geo-alt text-primary me-2"></i> জেলা:</strong> <?= h($user['district'] ?? 'N/A') ?></p>
                <p class="mb-0"><strong><i class="bi bi-house-door text-primary me-2"></i> ঠিকানা:</strong> <?= h($user['address'] ?? 'N/A') ?></p>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">অর্ডার ইতিহাস</h5>
                <span class="badge bg-secondary"><?= count($orders) ?> টি অর্ডার</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>অর্ডার নং</th>
                                <th>তারিখ</th>
                                <th>মোট মূল্য</th>
                                <th>পেমেন্ট</th>
                                <th>স্ট্যাটাস</th>
                                <th class="text-end">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">কোনো অর্ডার পাওয়া যায়নি।</td></tr>
                            <?php else: ?>
                                <?php foreach ($orders as $o): ?>
                                    <tr>
                                        <td class="fw-bold">#<?= h($o['order_number']) ?></td>
                                        <td><small class="text-muted"><?= format_date_bn($o['created_at']) ?></small></td>
                                        <td class="fw-bold text-success"><?= format_price_bn($o['total'] ?? $o['grand_total'] ?? 0) ?></td>
                                        <td><?= get_payment_status_badge($o['payment_status']) ?></td>
                                        <td><?= get_order_status_badge($o['order_status']) ?></td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>/admin/orders/view.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
