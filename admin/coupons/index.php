<?php
// ============================================================
// FarmersBD — Admin Coupons
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/flash.php';
require_once $base . '/includes/pagination.php';
require_once $base . '/includes/csrf.php';

require_admin();
$pdo = get_db_connection();

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$count_stmt = $pdo->query("SELECT COUNT(*) FROM coupons");
$total_records = $count_stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT * FROM coupons ORDER BY id DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$coupons = $stmt->fetchAll();

$pagination = paginate($total_records, $limit, $page, BASE_URL . '/admin/coupons/index.php');
$page_title = "কুপন ও প্রমোশন — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-tag text-primary me-2"></i> কুপন ও প্রমোশন</h3>
    <a href="<?= BASE_URL ?>/admin/coupons/create.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> নতুন কুপন</a>
</div>

<?php display_flash(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>কোড</th>
                        <th>ডিসকাউন্ট</th>
                        <th>সীমা</th>
                        <th>ব্যবহার</th>
                        <th>মেয়াদ</th>
                        <th>স্ট্যাটাস</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($coupons)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">কোনো কুপন পাওয়া যায়নি।</td></tr>
                    <?php else: ?>
                        <?php foreach ($coupons as $c): ?>
                            <tr>
                                <td><span class="badge bg-dark fs-6"><?= h($c['code']) ?></span></td>
                                <td>
                                    <?php if ($c['discount_type'] === 'percentage'): ?>
                                        <span class="text-success fw-bold"><?= format_number_bn((int)$c['discount_value']) ?>%</span>
                                        <?php if ($c['max_discount'] > 0): ?>
                                            <br><small class="text-muted">সর্বোচ্চ <?= format_price_bn($c['max_discount']) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-success fw-bold"><?= format_price_bn($c['discount_value']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="d-block text-muted">সর্বনিম্ন: <?= format_price_bn($c['min_order_amount']) ?></small>
                                    <small class="d-block text-muted">জনপ্রতি: <?= format_number_bn($c['per_user_limit'] ?? 1) ?> বার</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= format_number_bn($c['used_count']) ?></span>
                                    <?php if ($c['usage_limit']): ?> / <?= format_number_bn($c['usage_limit']) ?><?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($c['start_date']): ?>
                                        <small class="d-block"><span class="text-muted">শুরু:</span> <?= format_date_bn($c['start_date']) ?></small>
                                    <?php endif; ?>
                                    <?php if ($c['expiry_date']): ?>
                                        <small class="d-block"><span class="text-muted">শেষ:</span> <?= format_date_bn($c['expiry_date']) ?></small>
                                    <?php endif; ?>
                                    <?php if (!$c['start_date'] && !$c['expiry_date']) echo '<small class="text-muted">আজীবন</small>'; ?>
                                </td>
                                <td>
                                    <form action="<?= BASE_URL ?>/admin/coupons/toggle-status.php" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <div class="form-check form-switch m-0 p-0 d-inline-block">
                                            <input class="form-check-input ms-0 mt-0" type="checkbox" role="switch" style="cursor:pointer; width: 2.5em; height: 1.25em;" onchange="this.form.submit()" <?= $c['is_active'] ? 'checked' : '' ?>>
                                        </div>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/admin/coupons/edit.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <form action="<?= BASE_URL ?>/admin/coupons/delete.php" method="POST" class="d-inline" onsubmit="return confirm('আপনি কি নিশ্চিত যে আপনি এই কুপনটি মুছে ফেলতে চান?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="p-3">
            <?= render_pagination($pagination) ?>
        </div>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
