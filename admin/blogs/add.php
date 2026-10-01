<?php
// ============================================================
// FarmersBD — Admin Add Blog Article
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
$pdo = get_db_connection();
$errors = [];

if (is_post_request()) {
    verify_csrf_token();

    $title   = sanitize_input($_POST['title'] ?? '');
    $excerpt = sanitize_input($_POST['excerpt'] ?? '');
    $content = sanitize_input($_POST['content'] ?? '');
    $status  = sanitize_input($_POST['status'] ?? 'published');

    if (empty($title)) $errors[] = "ব্লগের শিরোনাম লিখুন।";
    if (empty($content)) $errors[] = "ব্লগের মূল বক্তব্য বা কনটেন্ট লিখুন।";

    $slug = unique_slug('blogs', $title);
    $is_active = ($status === 'published') ? 1 : 0;

    $image_name = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploader = new UploadService();
        $res = $uploader->upload_image($_FILES['image'], 'blog');
        if ($res['success']) {
            $image_name = $res['filename'];
        } else {
            $errors[] = $res['error'];
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO blogs (title, slug, excerpt, content, image, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$title, $slug, $excerpt, $content, $image_name, $is_active]);

        set_flash('success', 'নতুন ব্লগ নিবন্ধ সফলভাবে প্রকাশিত হয়েছে।');
        redirect(BASE_URL . '/admin/blogs/index.php');
    }
}

$page_title = "নতুন ব্লগ নিবন্ধন — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0">নতুন ব্লগ আর্টিকেল লিখুন</h3>
    <a href="<?= BASE_URL ?>/admin/blogs/index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> তালিকায় ফিরে যান</a>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?= h($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-medium">আর্টিকেল শিরোনাম <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required value="<?= h($_POST['title'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">সংক্ষিপ্ত বিবরণ (Excerpt)</label>
                    <textarea name="excerpt" class="form-control" rows="2"><?= h($_POST['excerpt'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">সম্পূর্ণ নিবন্ধ/কনটেন্ট <span class="text-danger">*</span></label>
                    <textarea name="content" class="form-control" rows="8" required><?= h($_POST['content'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">ফিচার্ড ছবি</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">স্ট্যাটাস</label>
                    <select name="status" class="form-select">
                        <option value="published">Published (প্রকাশিত)</option>
                        <option value="draft">Draft (খসড়া)</option>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-send me-1"></i> প্রকাশ করুন</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
