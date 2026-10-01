<?php
// ============================================================
// FarmersBD — Admin Edit Category
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/csrf.php';
require_once $base . '/includes/flash.php';

require_admin();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pdo = get_db_connection();

$stmt = $pdo->prepare("SELECT * FROM product_categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    set_flash('error', 'ক্যাটাগরি পাওয়া যায়নি।');
    redirect(BASE_URL . '/admin/categories/index.php');
}

$errors = [];

if (is_post_request()) {
    verify_csrf_token();

    $name        = sanitize_input($_POST['name'] ?? $_POST['name_bn'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $status      = sanitize_input($_POST['status'] ?? 'active');
    $is_active   = ($status === 'active' || $status === '1') ? 1 : 0;

    if (empty($name)) $errors[] = "ক্যাটাগরির নাম লিখুন।";

    if (empty($errors)) {
        $update = $pdo->prepare("UPDATE product_categories SET name = ?, description = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
        $update->execute([$name, $description, $is_active, $id]);

        set_flash('success', 'ক্যাটাগরি সফলভাবে আপডেট করা হয়েছে।');
        redirect(BASE_URL . '/admin/categories/index.php');
    }
}

$page_title = "ক্যাটাগরি সম্পাদনা — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0">ক্যাটাগরি তথ্য সম্পাদনা করুন</h3>
    <a href="<?= BASE_URL ?>/admin/categories/index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> তালিকায় ফিরে যান</a>
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

        <form method="POST" action="">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-medium">ক্যাটাগরি নাম <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="<?= e($category['name']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">বিবরণ</label>
                    <textarea name="description" class="form-control" rows="3"><?= e($category['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">স্ট্যাটাস</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= !empty($category['is_active']) ? 'selected' : '' ?>>Active (সক্রিয়)</option>
                        <option value="inactive" <?= empty($category['is_active']) ? 'selected' : '' ?>>Inactive (নিষ্ক্রিয়)</option>
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
