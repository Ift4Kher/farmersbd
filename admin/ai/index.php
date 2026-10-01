<?php
// ============================================================
// FarmersBD — Admin AI Diagnosis Logs Management
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
$limit = 12;
$offset = ($page - 1) * $limit;

$count_stmt = $pdo->query("SELECT COUNT(*) FROM ai_diagnoses");
$total_records = $count_stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT ad.*, u.name as user_name, u.mobile, d.name as disease_name FROM ai_diagnoses ad LEFT JOIN users u ON ad.user_id = u.id LEFT JOIN diseases d ON ad.disease_id = d.id ORDER BY ad.id DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();

$pagination = paginate($total_records, $limit, $page, BASE_URL . '/admin/ai/index.php');

$page_title = "AI ডায়াগনসিস হিস্ট্রি — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-cpu text-primary me-2"></i> AI রোগ শনাক্তকরণ হিস্ট্রি ও পরিসংখ্যান</h3>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ছবি</th>
                        <th>ব্যবহারকারী</th>
                        <th>শনাক্তকৃত রোগ</th>
                        <th>কনফিডেন্স</th>
                        <th>তারিখ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">কোনো AI পরীক্ষা লগ পাওয়া যায়নি।</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                            <tr>
                                <td>
                                    <img src="<?= get_upload_url($l['image_path'], 'ai') ?>" class="rounded" style="width:50px; height:50px; object-fit:cover;" alt="">
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= h($l['user_name'] ?? 'Guest (গেস্ট)') ?></div>
                                    <small class="text-muted"><?= h($l['mobile'] ?? 'N/A') ?></small>
                                </td>
                                <td class="fw-bold text-danger"><?= h($l['disease_name'] ?? 'অজানা রোগ') ?></td>
                                <td><span class="badge bg-success"><?= format_number_bn($l['confidence']) ?>%</span></td>
                                <td class="small text-muted"><?= format_date_bn($l['created_at']) ?></td>
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
