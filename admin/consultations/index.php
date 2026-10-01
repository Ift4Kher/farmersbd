<?php
// ============================================================
// FarmersBD — Admin Consultations Management
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/flash.php';
require_once $base . '/includes/pagination.php';

require_admin();
$pdo = get_db_connection();

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$count_stmt = $pdo->query("SELECT COUNT(*) FROM consultations");
$total_records = $count_stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT c.*, u.name as user_name, u.mobile FROM consultations c LEFT JOIN users u ON c.user_id = u.id ORDER BY c.id DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$consultations = $stmt->fetchAll();

$pagination = paginate($total_records, $limit, $page, BASE_URL . '/admin/consultations/index.php');

$page_title = "বিশেষজ্ঞ পরামর্শ ব্যবস্থাপনা — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-chat-left-dots text-primary me-2"></i> গ্রাহকদের পরামর্শ আবেদন তালিকা</h3>
</div>

<?php display_flash(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>বিষয়</th>
                        <th>আবেদনকারী</th>
                        <th>মাছের জাত</th>
                        <th>তারিখ</th>
                        <th>স্ট্যাটাস</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($consultations)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">কোনো আবেদন পাওয়া যায়নি।</td></tr>
                    <?php else: ?>
                        <?php foreach ($consultations as $c): ?>
                            <tr>
                                <td class="fw-bold text-dark"><?= h($c['subject']) ?></td>
                                <td>
                                    <div class="fw-bold"><?= h($c['user_name']) ?></div>
                                    <small class="text-muted"><?= h($c['mobile']) ?></small>
                                </td>
                                <td><?= h($c['fish_type'] ?? 'N/A') ?></td>
                                <td class="small text-muted"><?= format_date_bn($c['created_at']) ?></td>
                                <td><?= get_consultation_status_badge($c['status']) ?></td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/admin/consultations/view.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-reply-fill"></i> দেখুন ও উত্তর দিন</a>
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
