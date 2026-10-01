<?php
// ============================================================
// FarmersBD — Admin Settings: Cart Drawer Slider & Upsell
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

if (is_post_request()) {
    verify_csrf_token();

    $settings = [
        'cart_drawer_enabled'        => isset($_POST['cart_drawer_enabled']) ? '1' : '0',
        'drawer_free_shipping_bar'   => isset($_POST['drawer_free_shipping_bar']) ? '1' : '0',
        'drawer_upsell_enabled'      => isset($_POST['drawer_upsell_enabled']) ? '1' : '0',
        'drawer_upsell_heading'      => sanitize_input($_POST['drawer_upsell_heading'] ?? 'আপনার জন্য বিশেষ পছন্দ'),
        'drawer_upsell_product_ids'  => sanitize_input($_POST['drawer_upsell_product_ids'] ?? ''),
        'drawer_coupon_box_enabled'  => isset($_POST['drawer_coupon_box_enabled']) ? '1' : '0',
    ];

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
        $stmt->execute([$key, $value]);
    }

    set_flash('success', 'কার্ট ড্রয়ার স্লাইডার সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে।');
    redirect(BASE_URL . '/admin/settings/drawer-slider.php');
}

$settings_raw = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

$page_title = "কার্ট ড্রয়ার স্লাইডার — FarmersBD Admin";
require_once $base . '/admin/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1"><i class="bi bi-layout-sidebar-reverse text-primary me-2"></i>কার্ট ড্রয়ার স্লাইডার সেটিংস</h1>
        <p class="text-muted small mb-0">সাইড কার্ট স্লাইডার, ফ্রি শিপিং প্রোগ্রেস বার এবং আপসেল পণ্যের সুপারিশ</p>
    </div>
</div>

<form method="POST">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">স্লাইড কার্ট ড্রয়ার কনফিগারেশন</div>
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="cart_drawer_enabled" id="drawerSwitch" value="1" <?= ($settings_raw['cart_drawer_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="drawerSwitch">সাইড কার্ট ড্রয়ার চালু রাখুন (Slide Cart on Add-to-Cart)</label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="drawer_free_shipping_bar" id="fsBarSwitch" value="1" <?= ($settings_raw['drawer_free_shipping_bar'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="fsBarSwitch">ড্রয়ারে ফ্রি শিপিং প্রোগ্রেস বার প্রদর্শন করুন</label>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="drawer_coupon_box_enabled" id="couponBoxSwitch" value="1" <?= ($settings_raw['drawer_coupon_box_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="couponBoxSwitch">ড্রয়ারের ভেতর সরাসরি কুপন কোড ইনপুট বক্স দেখান</label>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">ড্রয়ার আপসেল প্রোডাক্ট (Upsell / Cross-sell)</div>
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="drawer_upsell_enabled" id="upsellSwitch" value="1" <?= ($settings_raw['drawer_upsell_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="upsellSwitch">কার্ট ড্রয়ারে সম্পর্কিত/জনপ্রিয় পণ্য সুপারিশ করুন</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">আপসেল সেকশন শিরোনাম</label>
                        <input type="text" name="drawer_upsell_heading" class="form-control" value="<?= e($settings_raw['drawer_upsell_heading'] ?? 'আপনার জন্য বিশেষ পছন্দ') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">নির্দিষ্ট প্রোডাক্ট আইডি (কমা দিয়ে, ফাঁকা রাখলে অটো সিলেক্ট হবে)</label>
                        <input type="text" name="drawer_upsell_product_ids" class="form-control font-monospace" placeholder="যেমন: 2, 5, 8" value="<?= e($settings_raw['drawer_upsell_product_ids'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> সেটিংস সংরক্ষণ করুন
                </button>
            </div>
        </div>
    </div>
</form>

<?php require_once $base . '/admin/includes/footer.php'; ?>
