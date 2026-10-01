<?php
// ============================================================
// FarmersBD — Admin Reviews
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

$count_stmt = $pdo->query("SELECT COUNT(*) FROM reviews");
$total_records = $count_stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT r.*, u.name as user_name, u.mobile as user_mobile, p.name as product_name
    FROM reviews r
    JOIN users u ON u.id = r.user_id
    JOIN products p ON p.id = r.product_id
    ORDER BY r.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$reviews = $stmt->fetchAll();

$pagination = paginate($total_records, $limit, $page, BASE_URL . '/admin/reviews/index.php');
$page_title = "প্রোডাক্ট রিভিউ — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-star text-primary me-2"></i> প্রোডাক্ট রিভিউ</h3>
</div>

<?php display_flash(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ইউজার</th>
                        <th>প্রোডাক্ট</th>
                        <th>রেটিং</th>
                        <th>রিভিউ</th>
                        <th>তারিখ</th>
                        <th>অ্যাপ্রুভড?</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reviews)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">কোনো রিভিউ পাওয়া যায়নি।</td></tr>
                    <?php else: ?>
                        <?php foreach ($reviews as $r): ?>
                            <tr>
                                <td>
                                    <strong><?= h($r['user_name']) ?></strong><br>
                                    <small class="text-muted"><?= h($r['user_mobile']) ?></small>
                                </td>
                                <td>
                                    <span class="d-inline-block text-truncate" style="max-width: 150px;" title="<?= h($r['product_name']) ?>">
                                        <?= h($r['product_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="text-warning">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <i class="bi bi-star<?= $i <= $r['rating'] ? '-fill' : '' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="d-inline-block text-truncate" style="max-width: 250px;" title="<?= h($r['review']) ?>">
                                        <?= h($r['review']) ?>
                                    </span>
                                </td>
                                <td><?= format_date_bn($r['created_at']) ?></td>
                                <td>
                                    <form action="<?= BASE_URL ?>/admin/reviews/toggle-status.php" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <div class="form-check form-switch m-0 p-0 d-inline-block">
                                            <input class="form-check-input ms-0 mt-0" type="checkbox" role="switch" style="cursor:pointer; width: 2.5em; height: 1.25em;" onchange="this.form.submit()" <?= $r['is_approved'] ? 'checked' : '' ?>>
                                        </div>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <form action="<?= BASE_URL ?>/admin/reviews/delete.php" method="POST" class="d-inline" onsubmit="return confirm('আপনি কি নিশ্চিত যে আপনি এই রিভিউটি মুছে ফেলতে চান?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
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
