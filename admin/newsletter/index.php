<?php
// ============================================================
// FarmersBD — Admin Newsletter Subscribers List
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/pagination.php';

require_admin();
$pdo = get_db_connection();

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$count_stmt = $pdo->query("SELECT COUNT(*) FROM newsletter_subscribers");
$total_records = $count_stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT * FROM newsletter_subscribers ORDER BY id DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$subscribers = $stmt->fetchAll();

$pagination = paginate($total_records, $limit, $page, BASE_URL . '/admin/newsletter/index.php');

$page_title = "নিউজলেটার গ্রাহক তালিকা — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-envelope-paper-heart text-primary me-2"></i> নিউজলেটার সাবস্ক্রাইবার তালিকা</h3>
    <span class="badge bg-primary fs-6">মোট: <?= format_number_bn($total_records) ?> জন</span>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>আইডি</th>
                        <th>ইমেইল ঠিকানা</th>
                        <th>সাবস্ক্রিপশনের তারিখ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($subscribers)): ?>
                        <tr><td colspan="3" class="text-center py-4 text-muted">কোনো সাবস্ক্রাইবার পাওয়া যায়নি।</td></tr>
                    <?php else: ?>
                        <?php foreach ($subscribers as $s): ?>
                            <tr>
                                <td class="fw-bold">#<?= $s['id'] ?></td>
                                <td class="fw-bold text-dark"><?= h($s['email']) ?></td>
                                <td class="small text-muted"><?= format_date_bn($s['created_at']) ?></td>
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
