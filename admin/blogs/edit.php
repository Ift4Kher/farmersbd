<?php
// ============================================================
// FarmersBD — Admin Edit Blog Article
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/csrf.php';
require_once $base . '/includes/flash.php';
require_once $base . '/services/UploadService.php';

require_admin();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pdo = get_db_connection();

$stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
$stmt->execute([$id]);
$blog = $stmt->fetch();

if (!$blog) {
    set_flash('error', 'ব্লগ নিবন্ধ পাওয়া যায়নি।');
    redirect(BASE_URL . '/admin/blogs/index.php');
}

$errors = [];

if (is_post_request()) {
    verify_csrf_token();

    $title   = sanitize_input($_POST['title'] ?? '');
    $excerpt = sanitize_input($_POST['excerpt'] ?? '');
    $content = sanitize_input($_POST['content'] ?? '');
    $status  = sanitize_input($_POST['status'] ?? 'published');

    if (empty($title)) $errors[] = "ব্লগের শিরোনাম লিখুন।";
    if (empty($content)) $errors[] = "ব্লগের মূল বক্তব্য বা কনটেন্ট লিখুন।";

    $image_name = $blog['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploader = new UploadService();
        $res = $uploader->upload_image($_FILES['image'], 'blog');
        if ($res['success']) {
            if (!empty($blog['image'])) {
                $uploader->delete_image($blog['image'], 'blog');
            }
            $image_name = $res['filename'];
        } else {
            $errors[] = $res['error'];
        }
    }

    $is_active = ($status === 'published' || $status === '1') ? 1 : 0;

    if (empty($errors)) {
        $update = $pdo->prepare("UPDATE blogs SET title = ?, excerpt = ?, content = ?, image = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
        $update->execute([$title, $excerpt, $content, $image_name, $is_active, $id]);

        set_flash('success', 'ব্লগ নিবন্ধ সফলভাবে আপডেট করা হয়েছে।');
        redirect(BASE_URL . '/admin/blogs/index.php');
    }
}

$page_title = "ব্লগ সম্পাদনা — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0">ব্লগ আর্টিকেল সম্পাদনা করুন</h3>
    <a href="<?= BASE_URL ?>/admin/blogs/index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> তালিকায় ফিরে যান</a>
</div>

<?php display_flash(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <form method="POST" action="" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-medium">শিরোনাম</label>
                    <input type="text" name="title" class="form-control" value="<?= e($blog['title']) ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">সংক্ষিপ্ত বিবরণ (Excerpt)</label>
                    <textarea name="excerpt" class="form-control" rows="2"><?= e($blog['excerpt']) ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">মূল বক্তব্য (Content)</label>
                    <textarea name="content" class="form-control" rows="8" required><?= e($blog['content']) ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">ব্লগ কভার ছবি</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <?php if (!empty($blog['image'])): ?>
                        <div class="mt-2">
                            <img src="<?= get_upload_url($blog['image'], 'blog') ?>" class="rounded" style="width:80px; height:80px; object-fit:cover;">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">স্ট্যাটাস</label>
                    <select name="status" class="form-select">
                        <option value="published" <?= !empty($blog['is_active']) ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= empty($blog['is_active']) ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i> আপডেট করুন</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
