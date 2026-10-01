<?php
// ============================================================
// FarmersBD — Admin Diseases Management (Listing)
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

$count_stmt = $pdo->query("SELECT COUNT(*) FROM diseases");
$total_records = $count_stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT * FROM diseases ORDER BY id DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$diseases = $stmt->fetchAll();

$pagination = paginate($total_records, $limit, $page, BASE_URL . '/admin/diseases/index.php');

$page_title = "মাছের রোগ ব্যবস্থাপনা — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-bug text-danger me-2"></i> মাছের রোগ ও চিকিৎসা তথ্যকোষ</h3>
    <a href="<?= BASE_URL ?>/admin/diseases/add.php" class="btn btn-danger"><i class="bi bi-plus-lg me-1"></i> নতুন রোগ যোগ করুন</a>
</div>

<?php display_flash(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ছবি</th>
                        <th>রোগের নাম</th>
                        <th>আক্রান্ত মাছ</th>
                        <th>AI লেবেল</th>
                        <th>স্ট্যাটাস</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($diseases as $d): ?>
                        <tr>
                            <td>
                                <img src="<?= !empty($d['image']) ? get_upload_url($d['image'], 'diseases') : BASE_URL . '/assets/images/disease-placeholder.jpg' ?>" class="rounded" style="width:50px; height:50px; object-fit:cover;" alt="">
                            </td>
                            <td class="fw-bold text-dark"><?= e($d['name']) ?></td>
                            <td><?= e($d['affected_fish'] ?? 'সকল') ?></td>
                            <td><code><?= e($d['ai_label'] ?? 'N/A') ?></code></td>
                            <td>
                                <span class="badge <?= !empty($d['is_active']) ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= !empty($d['is_active']) ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/admin/diseases/edit.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                                <a href="<?= BASE_URL ?>/admin/diseases/delete.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('আপনি কি নিশ্চিত যে এই রেকর্ডটি মুছে ফেলতে চান?');"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="p-3">
            <?= render_pagination($pagination) ?>
        </div>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
