<?php
// ============================================================
// FarmersBD — Admin Blog Articles Management (Listing)
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

$count_stmt = $pdo->query("SELECT COUNT(*) FROM blogs");
$total_records = $count_stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT b.*, bc.name as category_name FROM blogs b LEFT JOIN blog_categories bc ON b.category_id = bc.id ORDER BY b.id DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$blogs = $stmt->fetchAll();

$pagination = paginate($total_records, $limit, $page, BASE_URL . '/admin/blogs/index.php');

$page_title = "ব্লগ ও নিবন্ধ ব্যবস্থাপনা — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-journal-text text-primary me-2"></i> মৎস্য চাষ ব্লগ ও শিক্ষামূলক নিবন্ধ তালিকা</h3>
    <a href="<?= BASE_URL ?>/admin/blogs/add.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> নতুন ব্লগ লিখুন</a>
</div>

<?php display_flash(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ছবি</th>
                        <th>শিরোনাম</th>
                        <th>ক্যাটাগরি</th>
                        <th>প্রকাশের তারিখ</th>
                        <th>স্ট্যাটাস</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($blogs as $b): ?>
                        <tr>
                            <td>
                                <img src="<?= !empty($b['image']) ? get_upload_url($b['image'], 'blog') : BASE_URL . '/assets/images/blog-placeholder.jpg' ?>" class="rounded" style="width:50px; height:50px; object-fit:cover;" alt="">
                            </td>
                            <td class="fw-bold text-dark"><?= e($b['title']) ?></td>
                            <td><?= e($b['category_name'] ?? 'সাধারণ') ?></td>
                            <td class="small text-muted"><?= format_date_bn($b['created_at']) ?></td>
                            <td>
                                <span class="badge <?= !empty($b['is_active']) ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= !empty($b['is_active']) ? 'Published' : 'Draft' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/admin/blogs/edit.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                                <a href="<?= BASE_URL ?>/admin/blogs/delete.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('আপনি কি নিশ্চিত যে এই ব্লগ টি মুছে ফেলতে চান?');"><i class="bi bi-trash"></i></a>
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
