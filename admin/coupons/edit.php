<?php
// ============================================================
// FarmersBD — Edit Coupon
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/flash.php';
require_once $base . '/includes/csrf.php';

require_admin();
$pdo = get_db_connection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM coupons WHERE id = ?");
$stmt->execute([$id]);
$coupon = $stmt->fetch();

if (!$coupon) {
    flash('কুপন পাওয়া যায়নি।', FLASH_ERROR);
    redirect(BASE_URL . '/admin/coupons/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    
    $code = trim($_POST['code'] ?? '');
    $discount_type = $_POST['discount_type'] ?? 'fixed';
    $discount_value = (float)($_POST['discount_value'] ?? 0);
    $min_order_amount = (float)($_POST['min_order_amount'] ?? 0);
    $max_discount = !empty($_POST['max_discount']) ? (float)$_POST['max_discount'] : null;
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $usage_limit = !empty($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : null;
    $per_user_limit = !empty($_POST['per_user_limit']) ? (int)$_POST['per_user_limit'] : 1;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($code)) {
        flash('কুপন কোড আবশ্যক।', FLASH_ERROR);
    } else {
        // check unique
        $stmt = $pdo->prepare("SELECT id FROM coupons WHERE code = ? AND id != ?");
        $stmt->execute([$code, $id]);
        if ($stmt->fetch()) {
            flash('এই কুপন কোডটি ইতিমধ্যে ব্যবহৃত হয়েছে।', FLASH_ERROR);
        } else {
            $stmt = $pdo->prepare("UPDATE coupons SET code=?, discount_type=?, discount_value=?, min_order_amount=?, max_discount=?, start_date=?, expiry_date=?, usage_limit=?, per_user_limit=?, is_active=? WHERE id=?");
            $stmt->execute([$code, $discount_type, $discount_value, $min_order_amount, $max_discount, $start_date, $expiry_date, $usage_limit, $per_user_limit, $is_active, $id]);
            flash('কুপন সফলভাবে আপডেট করা হয়েছে।', FLASH_SUCCESS);
            redirect(BASE_URL . '/admin/coupons/');
        }
    }
}

$page_title = "কুপন এডিট করুন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-tag text-primary me-2"></i> কুপন এডিট</h3>
    <a href="<?= BASE_URL ?>/admin/coupons/" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> ফিরে যান</a>
</div>

<?php display_flash(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <form method="POST" action="">
            <?= csrf_field() ?>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">কুপন কোড <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control" required value="<?= h($coupon['code']) ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold">স্ট্যাটাস</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" role="switch" <?= $coupon['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label">সক্রিয়</label>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">ডিসকাউন্ট ধরন</label>
                    <select name="discount_type" class="form-select" id="discountType" required onchange="toggleMaxDiscount()">
                        <option value="fixed" <?= $coupon['discount_type'] === 'fixed' ? 'selected' : '' ?>>ফিক্সড অ্যামাউন্ট (৳)</option>
                        <option value="percentage" <?= $coupon['discount_type'] === 'percentage' ? 'selected' : '' ?>>শতকরা (%)</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">ডিসকাউন্ট পরিমাণ <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="discount_value" class="form-control" required value="<?= $coupon['discount_value'] ?>">
                </div>

                <div class="col-md-4" id="maxDiscountDiv" style="<?= $coupon['discount_type'] === 'percentage' ? 'display:block;' : 'display:none;' ?>">
                    <label class="form-label fw-bold">সর্বোচ্চ ডিসকাউন্ট (৳)</label>
                    <input type="number" step="0.01" name="max_discount" class="form-control" value="<?= $coupon['max_discount'] ?>">
                    <div class="form-text">শতাংশ ডিসকাউন্টের ক্ষেত্রে প্রযোজ্য</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">সর্বনিম্ন অর্ডার (৳) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="min_order_amount" class="form-control" required value="<?= $coupon['min_order_amount'] ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">মোট ব্যবহার সীমা (ঐচ্ছিক)</label>
                    <input type="number" name="usage_limit" class="form-control" value="<?= $coupon['usage_limit'] ?>">
                    <div class="form-text">বর্তমান ব্যবহার: <?= $coupon['used_count'] ?></div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">জনপ্রতি ব্যবহার সীমা <span class="text-danger">*</span></label>
                    <input type="number" name="per_user_limit" class="form-control" required value="<?= $coupon['per_user_limit'] ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">শুরুর তারিখ ও সময় (ঐচ্ছিক)</label>
                    <input type="datetime-local" name="start_date" class="form-control" value="<?= $coupon['start_date'] ? date('Y-m-d\TH:i', strtotime($coupon['start_date'])) : '' ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">শেষের তারিখ ও সময় (ঐচ্ছিক)</label>
                    <input type="datetime-local" name="expiry_date" class="form-control" value="<?= $coupon['expiry_date'] ? date('Y-m-d\TH:i', strtotime($coupon['expiry_date'])) : '' ?>">
                </div>
            </div>

            <hr class="my-4">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> আপডেট করুন</button>
        </form>
    </div>
</div>

<script>
function toggleMaxDiscount() {
    var type = document.getElementById('discountType').value;
    var maxDiv = document.getElementById('maxDiscountDiv');
    if (type === 'percentage') {
        maxDiv.style.display = 'block';
    } else {
        maxDiv.style.display = 'none';
        maxDiv.querySelector('input').value = '';
    }
}
</script>

<?php require_once $base . '/admin/includes/footer.php'; ?>
