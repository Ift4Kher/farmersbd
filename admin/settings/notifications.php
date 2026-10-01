<?php
// ============================================================
// FarmersBD — Admin Settings: System Notifications & Alerts
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
        'notify_admin_new_order'     => isset($_POST['notify_admin_new_order']) ? '1' : '0',
        'notify_admin_email'         => sanitize_input($_POST['notify_admin_email'] ?? ''),
        'notify_admin_low_stock'     => isset($_POST['notify_admin_low_stock']) ? '1' : '0',
        'notify_customer_order_placed' => isset($_POST['notify_customer_order_placed']) ? '1' : '0',
        'notify_customer_shipped'    => isset($_POST['notify_customer_shipped']) ? '1' : '0',
        'sound_alert_on_new_order'   => isset($_POST['sound_alert_on_new_order']) ? '1' : '0',
    ];

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
        $stmt->execute([$key, $value]);
    }

    set_flash('success', 'নোটিফিকেশন ও অ্যালার্ট সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে।');
    redirect(BASE_URL . '/admin/settings/notifications.php');
}

$settings_raw = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

$page_title = "নোটিফিকেশন সেটিংস — FarmersBD Admin";
require_once $base . '/admin/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1"><i class="bi bi-bell text-primary me-2"></i>অর্ডার নোটিফিকেশন ও অ্যালার্ট</h1>
        <p class="text-muted small mb-0">নতুন অর্ডারের ইমেইল/সাউন্ড অ্যালার্ট এবং গ্রাহক নোটিফিকেশন সেটিংস</p>
    </div>
</div>

<form method="POST">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Admin Alerts -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">অ্যাডমিন অ্যালার্ট (Admin Alerts)</div>
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="notify_admin_new_order" id="adminOrderSwitch" value="1" <?= ($settings_raw['notify_admin_new_order'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="adminOrderSwitch">নতুন অর্ডার আসলে অ্যাডমিনকে ইমেইল নোটিফিকেশন পাঠান</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">অ্যাডমিন নোটিফিকেশন ইমেইল</label>
                        <input type="email" name="notify_admin_email" class="form-control" value="<?= e($settings_raw['notify_admin_email'] ?? 'admin@farmersbd.com') ?>">
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="sound_alert_on_new_order" id="soundSwitch" value="1" <?= ($settings_raw['sound_alert_on_new_order'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="soundSwitch">অ্যাডমিন ড্যাশবোর্ডে নতুন অর্ডারের সাথে সাথে রিংটোন/সাউন্ড বাজান</label>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="notify_admin_low_stock" id="stockAlertSwitch" value="1" <?= ($settings_raw['notify_admin_low_stock'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="stockAlertSwitch">পণ্যের স্টক শেষ হয়ে এলে সতর্কবার্তা নোটিফিকেশন দিন</label>
                    </div>
                </div>
            </div>

            <!-- Customer Alerts -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">গ্রাহক নোটিফিকেশন (Customer Alerts)</div>
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="notify_customer_order_placed" id="custOrderSwitch" value="1" <?= ($settings_raw['notify_customer_order_placed'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="custOrderSwitch">অর্ডার সফলভাবে প্লেস হলে গ্রাহককে নিশ্চিতকরণ ইমেইল/বার্তা পাঠান</label>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="notify_customer_shipped" id="custShipSwitch" value="1" <?= ($settings_raw['notify_customer_shipped'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="custShipSwitch">পার্সেল কুরিয়ারে হস্তান্তর (Shipped) হলে গ্রাহককে ট্র্যাকিং নম্বর সহ নোটিফাই করুন</label>
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
