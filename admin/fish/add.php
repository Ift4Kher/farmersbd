<?php
// ============================================================
// FarmersBD — Admin Add Fish
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

    $name            = sanitize_input($_POST['name'] ?? $_POST['name_bn'] ?? '');
    $scientific_name = sanitize_input($_POST['scientific_name'] ?? '');
    $category        = sanitize_input($_POST['category'] ?? '');
    $water_type      = sanitize_input($_POST['water_type'] ?? '');
    $farming_method  = sanitize_input($_POST['farming_method'] ?? '');
    $description     = sanitize_input($_POST['description'] ?? '');
    $is_active       = (int)($_POST['is_active'] ?? 1);

    if (empty($name)) $errors[] = "মাছের নাম লিখুন।";

    $image_name = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploader = new UploadService();
        $res = $uploader->upload_image($_FILES['image'], 'fish');
        if ($res['success']) {
            $image_name = $res['filename'];
        } else {
            $errors[] = $res['error'];
        }
    }

    if (empty($errors)) {
        $slug = unique_slug('fish', $name);
        try {
            $stmt = $pdo->prepare("INSERT INTO fish (name, slug, scientific_name, category, water_type, farming_tips, description, image, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $slug, $scientific_name, $category, $water_type, $farming_method, $description, $image_name, $is_active]);

            set_flash('success', 'নতুন মাছের জাত সফলভাবে যুক্ত হয়েছে।');
            redirect(BASE_URL . '/admin/fish/index.php');
        } catch (PDOException $e) {
            error_log("[Add Fish Error] " . $e->getMessage());
            $errors[] = "কিছু একটা সমস্যা হয়েছে, পরে আবার চেষ্টা করুন।";
        }
    }
}

$page_title = "নতুন মাছ যুক্ত করুন — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0">নতুন মাছের জাত ও নির্দেশিকা যোগ করুন</h3>
    <a href="<?= BASE_URL ?>/admin/fish/index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> তালিকায় ফিরে যান</a>
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
                <div class="col-md-6">
                    <label class="form-label fw-medium">মাছের নাম <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="<?= h($_POST['name'] ?? $_POST['name_bn'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">বৈজ্ঞানিক নাম</label>
                    <input type="text" name="scientific_name" class="form-control" value="<?= h($_POST['scientific_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">ক্যাটাগরি</label>
                    <select name="category" class="form-select">
                        <option value="কার্প জাতীয়">কার্প জাতীয়</option>
                        <option value="ক্যাটফিশ">ক্যাটফিশ</option>
                        <option value="তিলাপিয়া">তিলাপিয়া</option>
                        <option value="অন্যান্য">অন্যান্য</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">পানির ধরন</label>
                    <input type="text" name="water_type" class="form-control" placeholder="যেমন: স্বাদু পানি, নোনা পানি" value="<?= h($_POST['water_type'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">পরিচিতি ও বিবরণ</label>
                    <textarea name="description" class="form-control" rows="3"><?= h($_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">চাষ পদ্ধতি নির্দেশিকা</label>
                    <textarea name="farming_method" class="form-control" rows="4"><?= h($_POST['farming_method'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">ছবি</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">স্ট্যাটাস</label>
                    <select name="is_active" class="form-select">
                        <option value="1">Active (সক্রিয়)</option>
                        <option value="0">Inactive (নিষ্ক্রিয়)</option>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> সংরক্ষণ করুন</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
