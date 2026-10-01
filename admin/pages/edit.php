<?php
// ============================================================
// FarmersBD — Admin Static CMS Page Edit
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/csrf.php';
require_once $base . '/includes/flash.php';

require_admin();
$pdo = get_db_connection();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
$stmt->execute([$id]);
$page_item = $stmt->fetch();

if (!$page_item) {
    set_flash('error', 'পেজ পাওয়া যায়নি।');
    redirect(BASE_URL . '/admin/pages/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $title      = sanitize_input($_POST['title'] ?? '');
    $slug       = sanitize_input($_POST['slug'] ?? '');
    $content    = $_POST['content'] ?? '';
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active  = isset($_POST['is_active']) ? 1 : 0;
    $meta_title = sanitize_input($_POST['meta_title'] ?? '');
    $meta_desc  = sanitize_input($_POST['meta_desc'] ?? '');

    if (empty($slug)) {
        $slug = slugify($title ?: 'page-' . time());
    } else {
        $slug = slugify($slug);
    }

    // Check slug uniqueness excluding current
    $slug_check = $pdo->prepare("SELECT id FROM pages WHERE slug = ? AND id != ?");
    $slug_check->execute([$slug, $id]);
    if ($slug_check->fetch()) {
        $slug .= '-' . time();
    }

    if (empty($title)) {
        set_flash('error', 'পেজের শিরোনাম আবশ্যক।');
    } else {
        $update_stmt = $pdo->prepare("UPDATE pages SET title = ?, slug = ?, content = ?, sort_order = ?, is_active = ?, meta_title = ?, meta_desc = ? WHERE id = ?");
        $update_stmt->execute([$title, $slug, $content, $sort_order, $is_active, $meta_title, $meta_desc, $id]);

        set_flash('success', 'পেজ সফলভাবে আপডেট করা হয়েছে।');
        redirect(BASE_URL . '/admin/pages/');
    }
}

$page_title = 'পেজ সম্পাদনা | FarmersBD Admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-pencil-square text-primary me-2"></i>পেজ সম্পাদনা</h1>
            <p class="text-muted small mb-0"><?= e($page_item['title']) ?></p>
        </div>
        <a href="<?= url('admin/pages/') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> ফিরে যান
        </a>
    </div>
</div>

<form method="POST">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">পেজ কন্টেন্ট</div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">পেজ শিরোনাম (Title) <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="<?= e($page_item['title']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">URL Slug</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted small"><?= url('page/') ?>/</span>
                            <input type="text" name="slug" class="form-control font-monospace" value="<?= e($page_item['slug']) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">পেজের বিস্তারিত কন্টেন্ট (HTML / Text)</label>
                        <textarea name="content" class="form-control font-monospace" rows="12"><?= e($page_item['content']) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">এসইও মেটা ডাটা (SEO)</div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">মেটা টাইটেল</label>
                        <input type="text" name="meta_title" class="form-control" value="<?= e($page_item['meta_title']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">মেটা ডেসক্রিপশন</label>
                        <textarea name="meta_desc" class="form-control" rows="3"><?= e($page_item['meta_desc']) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">পাবলিশ সেটিংস</div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ক্রম নম্বর (Sort Order)</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= $page_item['sort_order'] ?>">
                    </div>
                    <div class="form-check form-switch pt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActiveSwitch" value="1" <?= $page_item['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="isActiveSwitch">সরাসরি প্রকাশিত রাখুন</label>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> পরিবর্তন সংরক্ষণ করুন
                </button>
            </div>
        </div>
    </div>
</form>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
