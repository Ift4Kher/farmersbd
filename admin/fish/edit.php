<?php
// ============================================================
// FarmersBD — Admin Edit Fish
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

$stmt = $pdo->prepare("SELECT * FROM fish WHERE id = ?");
$stmt->execute([$id]);
$fish = $stmt->fetch();

if (!$fish) {
    set_flash('error', 'মাছের তথ্য পাওয়া যায়নি।');
    redirect(BASE_URL . '/admin/fish/index.php');
}

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

    $image_name = $fish['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploader = new UploadService();
        $res = $uploader->upload_image($_FILES['image'], 'fish');
        if ($res['success']) {
            if (!empty($fish['image'])) {
                $uploader->delete_image($fish['image'], 'fish');
            }
            $image_name = $res['filename'];
        } else {
            $errors[] = $res['error'];
        }
    }

    if (empty($errors)) {
        try {
            $update = $pdo->prepare("UPDATE fish SET name = ?, scientific_name = ?, category = ?, water_type = ?, farming_tips = ?, description = ?, image = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
            $update->execute([$name, $scientific_name, $category, $water_type, $farming_method, $description, $image_name, $is_active, $id]);

            set_flash('success', 'মাছের তথ্য সফলভাবে তথ্য আপডেট করা হয়েছে।');
            redirect(BASE_URL . '/admin/fish/index.php');
        } catch (PDOException $e) {
            error_log("[Edit Fish Error] " . $e->getMessage());
            $errors[] = "কিছু একটা সমস্যা হয়েছে, পরে আবার চেষ্টা করুন।";
        }
    }
}

$page_title = "মাছের তথ্য সম্পাদনা — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0">মাছের তথ্য সম্পাদনা করুন</h3>
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
                    <input type="text" name="name" class="form-control" required value="<?= h($fish['name'] ?? $fish['name_bn'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">বৈজ্ঞানিক নাম</label>
                    <input type="text" name="scientific_name" class="form-control" value="<?= h($fish['scientific_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">ক্যাটাগরি</label>
                    <select name="category" class="form-select">
                        <option value="কার্প জাতীয়" <?= ($fish['category'] ?? '') === 'কার্প জাতীয়' ? 'selected' : '' ?>>কার্প জাতীয়</option>
                        <option value="ক্যাটফিশ" <?= ($fish['category'] ?? '') === 'ক্যাটফিশ' ? 'selected' : '' ?>>ক্যাটফিশ</option>
                        <option value="তিলাপিয়া" <?= ($fish['category'] ?? '') === 'তিলাপিয়া' ? 'selected' : '' ?>>তিলাপিয়া</option>
                        <option value="অন্যান্য" <?= ($fish['category'] ?? '') === 'অন্যান্য' ? 'selected' : '' ?>>অন্যান্য</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">পানির ধরন</label>
                    <input type="text" name="water_type" class="form-control" value="<?= h($fish['water_type'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">পরিচিতি ও বিবরণ</label>
                    <textarea name="description" class="form-control" rows="3"><?= h($fish['description'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">চাষ পদ্ধতি নির্দেশিকা</label>
                    <textarea name="farming_method" class="form-control" rows="4"><?= h($fish['farming_tips'] ?? $fish['farming_method'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">ছবি</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <?php if (!empty($fish['image'])): ?>
                        <div class="mt-2">
                            <img src="<?= get_upload_url($fish['image'], 'fish') ?>" class="rounded" style="width:80px; height:80px; object-fit:cover;">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">স্ট্যাটাস</label>
                    <select name="is_active" class="form-select">
                        <option value="1" <?= ($fish['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>Active (সক্রিয়)</option>
                        <option value="0" <?= ($fish['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>Inactive (নিষ্ক্রিয়)</option>
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
