<?php
// ============================================================
// FarmersBD — Admin Settings: Floating Cart Button
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
        'floating_cart_enabled'  => isset($_POST['floating_cart_enabled']) ? '1' : '0',
        'floating_cart_position' => sanitize_input($_POST['floating_cart_position'] ?? 'bottom-right'),
        'floating_cart_show_qty' => isset($_POST['floating_cart_show_qty']) ? '1' : '0',
        'floating_cart_show_sum' => isset($_POST['floating_cart_show_sum']) ? '1' : '0',
        'floating_cart_pulse'    => isset($_POST['floating_cart_pulse']) ? '1' : '0',
    ];

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
        $stmt->execute([$key, $value]);
    }

    set_flash('success', 'ফ্লোটিং কার্ট বাটন সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে।');
    redirect(BASE_URL . '/admin/settings/floating-cart.php');
}

$settings_raw = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

$page_title = "ফ্লোটিং কার্ট সেটিংস — FarmersBD Admin";
require_once $base . '/admin/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1"><i class="bi bi-cart3 text-primary me-2"></i>ফ্লোটিং কার্ট বাটন সেটিংস</h1>
        <p class="text-muted small mb-0">স্ক্রিনের পাশে ভাসমান কার্ট আইকন, পজিশন, অ্যানিমেশন ও মোট মূল্য ব্যাজ নিয়ন্ত্রণ করুন</p>
    </div>
</div>

<form method="POST">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">ফ্লোটিং কার্ট বাটন সেটিংস</div>
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="floating_cart_enabled" id="fcSwitch" value="1" <?= ($settings_raw['floating_cart_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="fcSwitch">ভাসমান কার্ট বাটন প্রদর্শন করুন (Floating Cart)</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">বাটনের অবস্থান (Position)</label>
                        <select name="floating_cart_position" class="form-select" style="max-width: 300px;">
                            <option value="bottom-right" <?= ($settings_raw['floating_cart_position'] ?? 'bottom-right') === 'bottom-right' ? 'selected' : '' ?>>নিচে ডান পাশে (Bottom Right)</option>
                            <option value="bottom-left" <?= ($settings_raw['floating_cart_position'] ?? 'bottom-right') === 'bottom-left' ? 'selected' : '' ?>>নিচে বাম পাশে (Bottom Left)</option>
                            <option value="middle-right" <?= ($settings_raw['floating_cart_position'] ?? 'bottom-right') === 'middle-right' ? 'selected' : '' ?>>মাঝখানে ডান পাশে (Middle Right)</option>
                        </select>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="floating_cart_show_qty" id="qtySwitch" value="1" <?= ($settings_raw['floating_cart_show_qty'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="qtySwitch">আইটেম সংখ্যা ব্যাজ প্রদর্শন করুন</label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="floating_cart_show_sum" id="sumSwitch" value="1" <?= ($settings_raw['floating_cart_show_sum'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="sumSwitch">মোট কার্ট মূল্য প্রদর্শন করুন (৳)</label>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="floating_cart_pulse" id="pulseSwitch" value="1" <?= ($settings_raw['floating_cart_pulse'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="pulseSwitch">কার্টে নতুন পণ্য যোগ হলে আকর্ষণীয় পালস অ্যানিমেশন দেখান</label>
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
