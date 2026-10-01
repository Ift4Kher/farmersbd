<?php
// ============================================================
// FarmersBD — Admin Add Product
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

$categories = $pdo->query("SELECT * FROM product_categories ORDER BY name ASC")->fetchAll();

$errors = [];

if (is_post_request()) {
    verify_csrf_token();

    $name            = sanitize_input($_POST['name'] ?? $_POST['name_bn'] ?? '');
    $category_id     = (int)($_POST['category_id'] ?? 0);
    $price           = (float)($_POST['price'] ?? 0);
    $sale_price      = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : (!empty($_POST['original_price']) ? (float)$_POST['original_price'] : null);
    $stock           = (int)($_POST['stock'] ?? $_POST['stock_quantity'] ?? 0);
    $sku             = sanitize_input($_POST['sku'] ?? '');
    $unit            = sanitize_input($_POST['unit'] ?? 'Pack');
    $description     = sanitize_input($_POST['description'] ?? '');
    $is_featured     = isset($_POST['is_featured']) ? 1 : 0;
    $is_active       = (int)($_POST['is_active'] ?? 1);

    if (empty($name)) $errors[] = "পণ্যের নাম লিখুন।";
    if ($price <= 0) $errors[] = "সঠিক মূল্য নির্ধারণ করুন।";

    $slug = unique_slug('products', $name);

    $image_name = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploader = new UploadService();
        $res = $uploader->upload_image($_FILES['image'], 'products');
        if ($res['success']) {
            $image_name = $res['filename'];
        } else {
            $errors[] = $res['error'];
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, slug, category_id, price, discount_price, stock, sku, unit, description, image, is_featured, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $slug, $category_id, $price, $sale_price, $stock, $sku, $unit, $description, $image_name, $is_featured, $is_active]);

            set_flash('success', 'নতুন পণ্য সফলভাবে যুক্ত করা হয়েছে।');
            redirect(BASE_URL . '/admin/products/index.php');
        } catch (PDOException $e) {
            error_log("[Add Product Error] " . $e->getMessage());
            $errors[] = "কিছু একটা সমস্যা হয়েছে, পরে আবার চেষ্টা করুন।";
        }
    }
}

$page_title = "নতুন পণ্য যুক্ত করুন — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0">নতুন একুয়া প্রোডাক্ট বা মেডিসিন যোগ করুন</h3>
    <a href="<?= BASE_URL ?>/admin/products/index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> তালিকায় ফিরে যান</a>
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
                    <label class="form-label fw-medium">পণ্যের নাম <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="<?= h($_POST['name'] ?? $_POST['name_bn'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">ক্যাটাগরি</label>
                    <select name="category_id" class="form-select">
                        <option value="0">নির্বাচন করুন</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= h($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">বিক্রয় মূল্য (<?= get_currency_symbol() ?>) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="price" class="form-control" required value="<?= h($_POST['price'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">ছাড়ের মূল্য/অফার প্রাইস (<?= get_currency_symbol() ?>)</label>
                    <input type="number" step="0.01" name="sale_price" class="form-control" value="<?= h($_POST['sale_price'] ?? $_POST['original_price'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">স্টক পরিমাণ <span class="text-danger">*</span></label>
                    <input type="number" name="stock_quantity" class="form-control" required value="<?= h($_POST['stock_quantity'] ?? 10) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">SKU / পণ্য কোড</label>
                    <input type="text" name="sku" class="form-control" value="<?= h($_POST['sku'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">প্যাকিং একক (যেমন: ৫০০ গ্রাম, ১ লিটার)</label>
                    <input type="text" name="unit" class="form-control" value="<?= h($_POST['unit'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">পণ্যের বিবরণ</label>
                    <textarea name="description" class="form-control" rows="4"><?= h($_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">ছবি</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">স্ট্যাটাস</label>
                    <select name="is_active" class="form-select">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="feat">
                        <label class="form-check-label fw-bold" for="feat">ফিচার্ড প্রোডাক্ট</label>
                    </div>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> পণ্য সংরক্ষণ করুন</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
