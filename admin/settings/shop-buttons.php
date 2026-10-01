<?php
// ============================================================
// FarmersBD — Admin Settings: Shop Buttons & Action Triggers
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
        'btn_buy_now_text'         => sanitize_input($_POST['btn_buy_now_text'] ?? 'এখনই কিনুন'),
        'btn_add_to_cart_text'     => sanitize_input($_POST['btn_add_to_cart_text'] ?? 'কার্টে যোগ করুন'),
        'btn_order_now_direct'     => isset($_POST['btn_order_now_direct']) ? '1' : '0', // direct to checkout vs popup
        'btn_show_sticky_bar'      => isset($_POST['btn_show_sticky_bar']) ? '1' : '0', // sticky buy bar on mobile
        'btn_whatsapp_order_text'  => sanitize_input($_POST['btn_whatsapp_order_text'] ?? 'হোয়াটসঅ্যাপে অর্ডার করুন'),
        'btn_show_whatsapp_button' => isset($_POST['btn_show_whatsapp_button']) ? '1' : '0',
    ];

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
        $stmt->execute([$key, $value]);
    }

    set_flash('success', 'শপ বাটন সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে।');
    redirect(BASE_URL . '/admin/settings/shop-buttons.php');
}

$settings_raw = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

$page_title = "শপ বাটন সেটিংস — FarmersBD Admin";
require_once $base . '/admin/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1"><i class="bi bi-ui-radios-grid text-primary me-2"></i>শপ বাটন ও অ্যাকশন সেটিংস</h1>
        <p class="text-muted small mb-0">পণ্য পেজ ও কার্ডের বাটন টেক্সট, ডিরেক্ট চেকআউট অ্যাকশন ও স্টিকি বার কনফিগারেশন</p>
    </div>
</div>

<form method="POST">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">বাটন লেবেল ও টেক্সট</div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">"এখনই কিনুন" বাটন টেক্সট</label>
                            <input type="text" name="btn_buy_now_text" class="form-control" value="<?= e($settings_raw['btn_buy_now_text'] ?? 'এখনই কিনুন') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">"কার্টে যোগ করুন" বাটন টেক্সট</label>
                            <input type="text" name="btn_add_to_cart_text" class="form-control" value="<?= e($settings_raw['btn_add_to_cart_text'] ?? 'কার্টে যোগ করুন') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">"WhatsApp অর্ডার" বাটন টেক্সট</label>
                        <input type="text" name="btn_whatsapp_order_text" class="form-control" value="<?= e($settings_raw['btn_whatsapp_order_text'] ?? 'হোয়াটসঅ্যাপে অর্ডার করুন') ?>">
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">ক্লিক অ্যাকশন আচরণ (Click Behavior)</div>
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="btn_order_now_direct" id="directCheckSwitch" value="1" <?= ($settings_raw['btn_order_now_direct'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="directCheckSwitch">"এখনই কিনুন" ক্লিকে সরাসরি চেকআউট পেজে নিয়ে যান (Direct Checkout)</label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="btn_show_sticky_bar" id="stickyBarSwitch" value="1" <?= ($settings_raw['btn_show_sticky_bar'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="stickyBarSwitch">মোবাইলে স্ক্রল করার সময় নিচে স্টিকি "Buy Now" বার প্রদর্শন করুন</label>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="btn_show_whatsapp_button" id="waBtnSwitch" value="1" <?= ($settings_raw['btn_show_whatsapp_button'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="waBtnSwitch">প্রোডাক্ট পেজে সরাসরি WhatsApp অর্ডার বাটন প্রদর্শন করুন</label>
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
