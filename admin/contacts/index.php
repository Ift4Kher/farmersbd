<?php
// ============================================================
// FarmersBD — Admin Contact Messages Listing
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/pagination.php';

require_admin();
$pdo = get_db_connection();

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$count_stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages");
$total_records = $count_stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT * FROM contact_messages ORDER BY id DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$messages = $stmt->fetchAll();

$pagination = paginate($total_records, $limit, $page, BASE_URL . '/admin/contacts/index.php');

$page_title = "যোগাযোগ মেসেজসমূহ — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-inbox text-primary me-2"></i> ওয়েবসাইট কন্টাক্ট ইনবক্স</h3>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>নাম ও ফোন</th>
                        <th>ইমেইল</th>
                        <th>বিষয়</th>
                        <th>বার্তা</th>
                        <th>তারিখ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($messages)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">কোনো মেসেজ পাওয়া যায়নি।</td></tr>
                    <?php else: ?>
                        <?php foreach ($messages as $m): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark"><?= h($m['name']) ?></div>
                                    <small class="text-muted"><i class="bi bi-telephone"></i> <?= h($m['phone']) ?></small>
                                </td>
                                <td><?= h($m['email'] ?? 'N/A') ?></td>
                                <td class="fw-bold text-primary"><?= h($m['subject'] ?? 'সাধারণ বার্তা') ?></td>
                                <td class="small text-secondary" style="max-width: 300px;"><?= nl2br(h($m['message'])) ?></td>
                                <td class="small text-muted"><?= format_date_bn($m['created_at']) ?></td>
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
