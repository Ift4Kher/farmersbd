<?php
// ============================================================
// FarmersBD — Admin Categories Management
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/flash.php';

require_admin();
$pdo = get_db_connection();

$stmt = $pdo->query("SELECT c.*, COUNT(p.id) as product_count FROM product_categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.id DESC");
$categories = $stmt->fetchAll();

$page_title = "পণ্য ক্যাটাগরি — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-tags text-primary me-2"></i> প্রোডাক্ট ক্যাটাগরি তালিকা</h3>
    <a href="<?= BASE_URL ?>/admin/categories/add.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> নতুন ক্যাটাগরি</a>
</div>

<?php display_flash(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ক্যাটাগরির নাম</th>
                        <th>স্লাগ (Slug)</th>
                        <th>মোট প্রোডাক্ট</th>
                        <th>স্ট্যাটাস</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $c): ?>
                        <tr>
                            <td class="fw-bold text-dark"><?= e($c['name']) ?></td>
                            <td><code><?= e($c['slug']) ?></code></td>
                            <td><span class="badge bg-info text-dark"><?= e($c['product_count']) ?> টি</span></td>
                            <td>
                                <span class="badge <?= !empty($c['is_active']) ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= !empty($c['is_active']) ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/admin/categories/edit.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                                <a href="<?= BASE_URL ?>/admin/categories/delete.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('এই ক্যাটাগরি মুছে ফেলতে চান?');"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
