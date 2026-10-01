<?php
// ============================================================
// FarmersBD — Admin Settings: SSLCOMMERZ & COD Payment Gateway
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/csrf.php';
require_once $base . '/includes/flash.php';

require_admin();
$pdo = get_db_connection();

if (is_post_request()) {
    verify_csrf_token();

    $settings = [
        'ssl_store_id'     => sanitize_input($_POST['ssl_store_id'] ?? ''),
        'ssl_store_passwd' => sanitize_input($_POST['ssl_store_passwd'] ?? ''),
        'ssl_mode'         => sanitize_input($_POST['ssl_mode'] ?? 'sandbox'),
        'cod_enabled'      => isset($_POST['cod_enabled']) ? '1' : '0'
    ];

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
        $stmt->execute([$key, $value]);
    }

    set_flash('success', 'পেমেন্ট গেটওয়ে সেটিংস সফলভাবে আপডেট করা হয়েছে।');
    redirect(BASE_URL . '/admin/settings/payment.php');
}

$settings_raw = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

$page_title = "পেমেন্ট গেটওয়ে সেটিংস — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-wallet2 text-primary me-2"></i> SSLCOMMERZ ও COD কনফিগারেশন</h3>
</div>

<?php display_flash(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <form method="POST" action="">
            <?= csrf_field() ?>
            <h5 class="fw-bold text-primary mb-3">SSLCOMMERZ মার্চেন্ট তথ্য</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Store ID</label>
                    <input type="text" name="ssl_store_id" class="form-control" value="<?= h($settings_raw['ssl_store_id'] ?? SSLCOMMERZ_STORE_ID) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Store Password</label>
                    <input type="password" name="ssl_store_passwd" class="form-control" value="<?= h($settings_raw['ssl_store_passwd'] ?? SSLCOMMERZ_STORE_PASSWD) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">এনভায়রনমেন্ট / মোড</label>
                    <select name="ssl_mode" class="form-select">
                        <option value="sandbox" <?= ($settings_raw['ssl_mode'] ?? (SSLCOMMERZ_IS_SANDBOX ? 'sandbox' : 'live')) === 'sandbox' ? 'selected' : '' ?>>Sandbox (টেস্টিং মোড)</option>
                        <option value="live" <?= ($settings_raw['ssl_mode'] ?? '') === 'live' ? 'selected' : '' ?>>Live / Production (বাস্তব অনলাইন পেমেন্ট)</option>
                    </select>
                </div>
            </div>

            <h5 class="fw-bold text-primary mb-3">ক্যাশ অন ডেলিভারি (COD)</h5>
            <div class="form-check form-switch fs-5 mb-4">
                <input class="form-check-input" type="checkbox" name="cod_enabled" id="cod_sw" <?= ($settings_raw['cod_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                <label class="form-check-label fs-6 fw-medium" for="cod_sw">ক্যাশ অন ডেলিভারি মোড সক্রিয় রাখুন</label>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> গেটওয়ে সেটিংস সেভ করুন</button>
            </div>
        </form>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
