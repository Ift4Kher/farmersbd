<?php
// ============================================================
// FarmersBD — Admin Edit Product
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

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('error', 'পণ্য পাওয়া যায়নি।');
    redirect(BASE_URL . '/admin/products/index.php');
}

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

    $image_name = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploader = new UploadService();
        $res = $uploader->upload_image($_FILES['image'], 'products');
        if ($res['success']) {
            if (!empty($product['image'])) {
                $uploader->delete_image($product['image'], 'products');
            }
            $image_name = $res['filename'];
        } else {
            $errors[] = $res['error'];
        }
    }

    if (empty($errors)) {
        try {
            $update = $pdo->prepare("UPDATE products SET name = ?, category_id = ?, price = ?, discount_price = ?, stock = ?, sku = ?, unit = ?, description = ?, image = ?, is_featured = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
            $update->execute([$name, $category_id, $price, $sale_price, $stock, $sku, $unit, $description, $image_name, $is_featured, $is_active, $id]);

            set_flash('success', 'পণ্যের তথ্য সফলভাবে আপডেট করা হয়েছে।');
            redirect(BASE_URL . '/admin/products/index.php');
        } catch (PDOException $e) {
            error_log("[Edit Product Error] " . $e->getMessage());
            $errors[] = "কিছু একটা সমস্যা হয়েছে, পরে আবার চেষ্টা করুন।";
        }
    }
}

$page_title = "পণ্য সম্পাদনা — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0">পণ্য তথ্য সম্পাদনা করুন</h3>
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
                    <input type="text" name="name" class="form-control" required value="<?= h($product['name'] ?? $product['name_bn'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">ক্যাটাগরি</label>
                    <select name="category_id" class="form-select">
                        <option value="0">নির্বাচন করুন</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>><?= h($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">বিক্রয় মূল্য (<?= get_currency_symbol() ?>) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="price" class="form-control" required value="<?= h($product['price'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">ছাড়ের মূল্য (<?= get_currency_symbol() ?>)</label>
                    <input type="number" step="0.01" name="sale_price" class="form-control" value="<?= h($product['sale_price'] ?? $product['original_price'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">স্টক পরিমাণ <span class="text-danger">*</span></label>
                    <input type="number" name="stock_quantity" class="form-control" required value="<?= h($product['stock_quantity'] ?? 0) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">SKU / কোড</label>
                    <input type="text" name="sku" class="form-control" value="<?= h($product['sku'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">প্যাকিং একক</label>
                    <input type="text" name="unit" class="form-control" value="<?= h($product['unit'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">পণ্যের বিবরণ</label>
                    <textarea name="description" class="form-control" rows="4"><?= h($product['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">ছবি</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <?php if (!empty($product['image'])): ?>
                        <div class="mt-2">
                            <img src="<?= get_upload_url($product['image'], 'products') ?>" class="rounded" style="width:80px; height:80px; object-fit:cover;">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">স্ট্যাটাস</label>
                    <select name="is_active" class="form-select">
                        <option value="1" <?= ($product['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= ($product['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="feat" <?= !empty($product['is_featured']) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="feat">ফিচার্ড প্রোডাক্ট</label>
                    </div>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i> তথ্য আপডেট করুন</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
