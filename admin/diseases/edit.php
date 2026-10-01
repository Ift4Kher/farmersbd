<?php
// ============================================================
// FarmersBD — Admin Edit Disease
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

$stmt = $pdo->prepare("SELECT * FROM diseases WHERE id = ?");
$stmt->execute([$id]);
$disease = $stmt->fetch();

if (!$disease) {
    set_flash('error', 'রোগের তথ্য পাওয়া যায়নি।');
    redirect(BASE_URL . '/admin/diseases/index.php');
}

$errors = [];

if (is_post_request()) {
    verify_csrf_token();

    $name            = sanitize_input($_POST['name'] ?? $_POST['name_bn'] ?? '');
    $scientific_name = sanitize_input($_POST['scientific_name'] ?? $_POST['name_en'] ?? '');
    $symptoms        = sanitize_input($_POST['symptoms'] ?? '');
    $causes          = sanitize_input($_POST['causes'] ?? '');
    $treatment       = sanitize_input($_POST['treatment'] ?? '');
    $prevention      = sanitize_input($_POST['prevention'] ?? '');
    $is_active       = (int)($_POST['is_active'] ?? 1);

    if (empty($name)) $errors[] = "রোগের নাম লিখুন।";

    $image_name = $disease['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploader = new UploadService();
        $res = $uploader->upload_image($_FILES['image'], 'diseases');
        if ($res['success']) {
            if (!empty($disease['image'])) {
                $uploader->delete_image($disease['image'], 'diseases');
            }
            $image_name = $res['filename'];
        } else {
            $errors[] = $res['error'];
        }
    }

    if (empty($errors)) {
        try {
            $update = $pdo->prepare("UPDATE diseases SET name = ?, scientific_name = ?, symptoms = ?, causes = ?, treatment = ?, prevention = ?, image = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
            $update->execute([$name, $scientific_name, $symptoms, $causes, $treatment, $prevention, $image_name, $is_active, $id]);

            set_flash('success', 'রোগের তথ্য সফলভাবে আপডেট করা হয়েছে।');
            redirect(BASE_URL . '/admin/diseases/index.php');
        } catch (PDOException $e) {
            error_log("[Edit Disease Error] " . $e->getMessage());
            $errors[] = "ডাটাবেজে তথ্য আপডেট করতে সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।";
        }
    }
}

$page_title = "রোগের তথ্য সম্পাদনা — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0">রোগের বিবরণ সম্পাদনা করুন</h3>
    <a href="<?= BASE_URL ?>/admin/diseases/index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> তালিকায় ফিরে যান</a>
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
                    <label class="form-label fw-medium">রোগের নাম <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="<?= h($disease['name'] ?? $disease['name_bn'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">বৈজ্ঞানিক/ইংরেজি নাম</label>
                    <input type="text" name="scientific_name" class="form-control" value="<?= h($disease['scientific_name'] ?? $disease['name_en'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">রোগের লক্ষণসমূহ <span class="text-danger">*</span></label>
                    <textarea name="symptoms" class="form-control" rows="3" required><?= h($disease['symptoms'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">রোগের কারণ</label>
                    <textarea name="causes" class="form-control" rows="2"><?= h($disease['causes'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">চিকিৎসা ও ঔষধ প্রয়োগ</label>
                    <textarea name="treatment" class="form-control" rows="4"><?= h($disease['treatment'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">প্রতিরোধ ব্যবস্থা</label>
                    <textarea name="prevention" class="form-control" rows="3"><?= h($disease['prevention'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">ছবি</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <?php if (!empty($disease['image'])): ?>
                        <div class="mt-2">
                            <img src="<?= get_upload_url($disease['image'], 'diseases') ?>" class="rounded" style="width:80px; height:80px; object-fit:cover;">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">স্ট্যাটাস</label>
                    <select name="is_active" class="form-select">
                        <option value="1" <?= ($disease['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>Active (সক্রিয়)</option>
                        <option value="0" <?= ($disease['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>Inactive (নিষ্ক্রিয়)</option>
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
