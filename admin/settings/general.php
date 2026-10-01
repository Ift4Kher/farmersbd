<?php
// ============================================================
// FarmersBD — Admin Settings: General Settings
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
        'site_title'     => sanitize_input($_POST['site_title'] ?? ''),
        'site_slogan'    => sanitize_input($_POST['site_slogan'] ?? ''),
        'contact_email'  => sanitize_input($_POST['contact_email'] ?? ''),
        'contact_phone'  => sanitize_input($_POST['contact_phone'] ?? ''),
        'contact_address'=> sanitize_input($_POST['contact_address'] ?? '')
    ];

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
        $stmt->execute([$key, $value]);
    }
    
    // Update MAINTENANCE_MODE in .env
    $maintenance_mode = isset($_POST['maintenance_mode']) ? 'true' : 'false';
    $envFile = dirname(dirname(__DIR__)) . '/.env';
    if (file_exists($envFile)) {
        $envContent = file_get_contents($envFile);
        if (strpos($envContent, 'MAINTENANCE_MODE=') !== false) {
            $envContent = preg_replace('/^MAINTENANCE_MODE=.*$/m', 'MAINTENANCE_MODE=' . $maintenance_mode, $envContent);
        } else {
            $envContent .= "\nMAINTENANCE_MODE=" . $maintenance_mode . "\n";
        }
        file_put_contents($envFile, $envContent);
    }

    set_flash('success', 'সাধারণ সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে।');
    redirect(BASE_URL . '/admin/settings/general.php');
}

// Fetch current
$settings_raw = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

$page_title = "সাধারণ সেটিংস — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-gear-fill text-primary me-2"></i> সাধারণ ওয়েবসাইট সেটিংস</h3>
</div>

<?php display_flash(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <form method="POST" action="">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">ওয়েবসাইটের নাম</label>
                    <input type="text" name="site_title" class="form-control" value="<?= h($settings_raw['site_title'] ?? 'FarmersBD (ফার্মার্সবিডি)') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">স্লোগান / ট্যাগলাইন</label>
                    <input type="text" name="site_slogan" class="form-control" value="<?= h($settings_raw['site_slogan'] ?? 'স্মার্ট মৎস্য চাষ ও রোগ সমাধান প্ল্যাটফর্ম') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">যোগাযোগের ইমেইল</label>
                    <input type="email" name="contact_email" class="form-control" value="<?= h($settings_raw['contact_email'] ?? 'support@farmersbd.com') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">ফোন / হেল্পলাইন নম্বর</label>
                    <input type="text" name="contact_phone" class="form-control" value="<?= h($settings_raw['contact_phone'] ?? '+৮৮০ ১৭০০-০০০০০০') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">অফিসের ঠিকানা</label>
                    <textarea name="contact_address" class="form-control" rows="3"><?= h($settings_raw['contact_address'] ?? 'ঢাকা, বাংলাদেশ') ?></textarea>
                </div>
                <div class="col-12 mt-4 pt-3 border-top">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="maintenance_mode" name="maintenance_mode" <?= defined('MAINTENANCE_MODE') && MAINTENANCE_MODE ? 'checked' : '' ?>>
                        <label class="form-check-label text-danger fw-bold" for="maintenance_mode">মেইনটেনেন্স মোড (Maintenance Mode) চালু করুন</label>
                        <div class="form-text">এটি চালু করলে সাধারণ ব্যবহারকারীরা ওয়েবসাইট দেখতে পারবেন না, শুধুমাত্র এডমিনরা সাইট অ্যাক্সেস করতে পারবেন।</div>
                    </div>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> সেটিংস সেভ করুন</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
