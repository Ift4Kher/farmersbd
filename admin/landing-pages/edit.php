<?php
// ============================================================
// FarmersBD — Admin Landing Page Edit
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
$stmt = $pdo->prepare("SELECT * FROM landing_pages WHERE id = ?");
$stmt->execute([$id]);
$page_item = $stmt->fetch();

if (!$page_item) {
    set_flash('error', 'ল্যান্ডিং পেজ পাওয়া যায়নি।');
    redirect(BASE_URL . '/admin/landing-pages/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $title        = sanitize_input($_POST['title'] ?? '');
    $slug         = sanitize_input($_POST['slug'] ?? '');
    $hero_heading = sanitize_input($_POST['hero_heading'] ?? '');
    $hero_sub     = sanitize_input($_POST['hero_sub'] ?? '');
    $cta_text     = sanitize_input($_POST['cta_text'] ?? 'এখনই অর্ডার করুন');
    $cta_link     = sanitize_input($_POST['cta_link'] ?? '#order-section');
    $content      = $_POST['content'] ?? '';
    $products     = sanitize_input($_POST['products'] ?? '');
    $countdown_at = !empty($_POST['countdown_at']) ? $_POST['countdown_at'] : null;
    $is_active    = isset($_POST['is_active']) ? 1 : 0;
    $meta_title   = sanitize_input($_POST['meta_title'] ?? '');
    $meta_desc    = sanitize_input($_POST['meta_desc'] ?? '');

    if (empty($slug)) {
        $slug = slugify($title ?: 'landing-' . time());
    } else {
        $slug = slugify($slug);
    }

    // Check slug uniqueness excluding current
    $slug_check = $pdo->prepare("SELECT id FROM landing_pages WHERE slug = ? AND id != ?");
    $slug_check->execute([$slug, $id]);
    if ($slug_check->fetch()) {
        $slug .= '-' . time();
    }

    $hero_image = $page_item['hero_image'];
    if (!empty($_FILES['hero_image']['name'])) {
        $upload_dir = $base . '/public/uploads/landing/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $ext = strtolower(pathinfo($_FILES['hero_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $filename = 'landing_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['hero_image']['tmp_name'], $upload_dir . $filename)) {
                $hero_image = 'uploads/landing/' . $filename;
            }
        }
    }

    if (empty($title)) {
        set_flash('error', 'পেজের শিরোনাম আবশ্যক।');
    } else {
        $update_stmt = $pdo->prepare("UPDATE landing_pages SET title = ?, slug = ?, hero_image = ?, hero_heading = ?, hero_sub = ?, cta_text = ?, cta_link = ?, content = ?, products = ?, countdown_at = ?, is_active = ?, meta_title = ?, meta_desc = ? WHERE id = ?");
        $update_stmt->execute([$title, $slug, $hero_image, $hero_heading, $hero_sub, $cta_text, $cta_link, $content, $products, $countdown_at, $is_active, $meta_title, $meta_desc, $id]);

        set_flash('success', 'ল্যান্ডিং পেজ সফলভাবে আপডেট করা হয়েছে।');
        redirect(BASE_URL . '/admin/landing-pages/');
    }
}

$page_title = 'ল্যান্ডিং পেজ সম্পাদনা | FarmersBD Admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-pencil-square text-primary me-2"></i>ল্যান্ডিং পেজ সম্পাদনা</h1>
            <p class="text-muted small mb-0"><?= e($page_item['title']) ?></p>
        </div>
        <a href="<?= url('admin/landing-pages/') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> ফিরে যান
        </a>
    </div>
</div>

<form method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">মূল হেডার ও কন্টেন্ট</div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">পেজ টাইটেল (Title) <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="<?= e($page_item['title']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">URL Slug</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted small"><?= url('landing/') ?>/</span>
                                <input type="text" name="slug" class="form-control font-monospace" value="<?= e($page_item['slug']) ?>">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">হিরো সেকশন প্রধান শিরোনাম (Hero Heading)</label>
                            <input type="text" name="hero_heading" class="form-control" value="<?= e($page_item['hero_heading']) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">হিরো সাব-টাইটেল (Sub-heading)</label>
                            <textarea name="hero_sub" class="form-control" rows="2"><?= e($page_item['hero_sub']) ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">CTA বাটন টেক্সট</label>
                            <input type="text" name="cta_text" class="form-control" value="<?= e($page_item['cta_text']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">CTA বাটন লিংক</label>
                            <input type="text" name="cta_link" class="form-control font-monospace" value="<?= e($page_item['cta_link']) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">প্রধান কন্টেন্ট ও ফিচারস (Rich HTML Content)</label>
                            <textarea name="content" class="form-control font-monospace" rows="8"><?= e($page_item['content']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Meta -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">সার্চ ইঞ্জিন অপটিমাইজেশন (SEO Meta)</div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">মেটা টাইটেল</label>
                        <input type="text" name="meta_title" class="form-control" value="<?= e($page_item['meta_title']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">মেটা বিবরণ</label>
                        <textarea name="meta_desc" class="form-control" rows="3"><?= e($page_item['meta_desc']) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">পাবলিশ ও সেটিংস</div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">অফার শেষ হওয়ার সময় (কাউন্টডাউন)</label>
                        <input type="datetime-local" name="countdown_at" class="form-control" value="<?= $page_item['countdown_at'] ? date('Y-m-d\TH:i', strtotime($page_item['countdown_at'])) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">অন্তর্ভুক্ত প্রোডাক্ট আইডি (কমা দিয়ে)</label>
                        <input type="text" name="products" class="form-control font-monospace" value="<?= e($page_item['products']) ?>">
                    </div>
                    <div class="form-check form-switch pt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActiveSwitch" value="1" <?= $page_item['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="isActiveSwitch">সরাসরি প্রকাশিত রাখুন (Active)</label>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">হিরো ব্যানার ইমেজ</div>
                <div class="card-body p-4 text-center">
                    <div class="mb-3">
                        <div class="bg-light border rounded d-flex align-items-center justify-content-center mx-auto mb-3 overflow-hidden" style="width: 100%; height: 180px;" id="previewContainer">
                            <?php if (!empty($page_item['hero_image'])): ?>
                                <img src="<?= asset($page_item['hero_image']) ?>" class="img-fluid rounded object-fit-cover w-100 h-100">
                            <?php else: ?>
                                <i class="bi bi-cloud-arrow-up display-4 text-muted"></i>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="hero_image" class="form-control" accept="image/*" onchange="previewImage(this)">
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

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewContainer').innerHTML = `<img src="${e.target.result}" class="img-fluid rounded object-fit-cover w-100 h-100">`;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
