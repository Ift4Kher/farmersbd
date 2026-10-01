<?php
// ============================================================
// FarmersBD — Admin Settings: Order Protection & Fraud Control
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
        'fraud_block_repeated_ips'      => isset($_POST['fraud_block_repeated_ips']) ? '1' : '0',
        'max_orders_per_ip_day'         => (int)($_POST['max_orders_per_ip_day'] ?? 3),
        'min_order_amount'              => (float)($_POST['min_order_amount'] ?? 0),
        'max_order_amount'              => (float)($_POST['max_order_amount'] ?? 50000),
        'otp_verification_enabled'      => isset($_POST['otp_verification_enabled']) ? '1' : '0',
        'block_disposable_emails'       => isset($_POST['block_disposable_emails']) ? '1' : '0',
        'auto_cancel_unpaid_hours'      => (int)($_POST['auto_cancel_unpaid_hours'] ?? 24),
        'duplicate_order_delay_minutes' => (int)($_POST['duplicate_order_delay_minutes'] ?? 5),
    ];

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
        $stmt->execute([$key, $value]);
    }

    set_flash('success', 'অর্ডার প্রোটেকশন সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে।');
    redirect(BASE_URL . '/admin/settings/order-protection.php');
}

$settings_raw = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

$page_title = "অর্ডার প্রোটেকশন ও ফ্রড কন্ট্রোল — FarmersBD Admin";
require_once $base . '/admin/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-shield-check text-primary me-2"></i>অর্ডার প্রোটেকশন ও নিরাপত্তা সেটিংস</h1>
            <p class="text-muted small mb-0">ভুয়া অর্ডার প্রতিরোধ, আইপি ভিত্তিক সীমা ও স্বয়ংক্রিয় নিরাপত্তা ফিল্টার</p>
        </div>
        <a href="<?= url('admin/blocklist/') ?>" class="btn btn-outline-danger">
            <i class="bi bi-shield-x me-1"></i> ব্লকলিস্ট দেখুন
        </a>
    </div>
</div>

<form method="POST">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">অর্ডার সুরক্ষা ফিল্টার</div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="fraud_block_repeated_ips" id="ipBlockSwitch" value="1" <?= ($settings_raw['fraud_block_repeated_ips'] ?? '1') === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="ipBlockSwitch">একই IP থেকে অতিরিক্ত অর্ডার প্রতিরোধ (Rate Limiting)</label>
                        </div>
                        <div class="form-text small text-muted ms-4">একই দিনে নির্দিষ্ট সীমার বেশি অর্ডার আসলে তা স্বয়ংক্রিয়ভাবে ব্লক বা পর্যালোচনায় যাবে।</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">প্রতি IP থেকে দৈনিক সর্বোচ্চ অর্ডার</label>
                            <input type="number" name="max_orders_per_ip_day" class="form-control" value="<?= e($settings_raw['max_orders_per_ip_day'] ?? '3') ?>" min="1" max="50">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ডুপ্লিকেট অর্ডার বিলম্ব সীমা (মিনিট)</label>
                            <input type="number" name="duplicate_order_delay_minutes" class="form-control" value="<?= e($settings_raw['duplicate_order_delay_minutes'] ?? '5') ?>" min="1" max="60">
                            <div class="form-text small">ভুলবশত একই অর্ডারের একাধিক ক্লিক প্রতিরোধে</div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">সর্বনিম্ন অর্ডার মূল্য (৳)</label>
                            <input type="number" step="1" name="min_order_amount" class="form-control" value="<?= e($settings_raw['min_order_amount'] ?? '100') ?>">
                            <div class="form-text small">এই মূল্যের নিচে অর্ডার গ্রহণ করা হবে না</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">সর্বোচ্চ ক্যাশ অন ডেলিভারি সীমা (৳)</label>
                            <input type="number" step="1" name="max_order_amount" class="form-control" value="<?= e($settings_raw['max_order_amount'] ?? '50000') ?>">
                            <div class="form-text small">এর বেশি হলে অগ্রিম পেমেন্ট বাধ্যতামূলক হবে</div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">অনাদায়ী অর্ডারের অটো-বাতিল সময় (ঘণ্টা)</label>
                        <input type="number" name="auto_cancel_unpaid_hours" class="form-control" value="<?= e($settings_raw['auto_cancel_unpaid_hours'] ?? '24') ?>" style="max-width: 200px;">
                        <div class="form-text small">ডিজিটাল পেমেন্ট অমীমাংসিত থাকলে নির্দিষ্ট সময় পর অর্ডার বাতিল হবে</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">SMS / OTP ভেরিফিকেশন</div>
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="otp_verification_enabled" id="otpSwitch" value="1" <?= ($settings_raw['otp_verification_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="otpSwitch">অর্ডারের পূর্বে SMS OTP যাচাই</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="block_disposable_emails" id="dispMailSwitch" value="1" <?= ($settings_raw['block_disposable_emails'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="dispMailSwitch">টেম্পরারি / ফেইক ইমেইল ডোমেইন ব্লক</label>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> সেটিংস সংরক্ষণ করুন
                </button>
            </div>
        </div>
    </div>
</form>

<?php require_once $base . '/admin/includes/footer.php'; ?>
