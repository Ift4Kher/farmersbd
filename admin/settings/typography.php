<?php
// ============================================================
// FarmersBD — Admin Settings: Dynamic Typography & Font Control
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
        'primary_font'   => sanitize_input($_POST['primary_font'] ?? 'Kalpurush'),
        'base_font_size' => sanitize_input($_POST['base_font_size'] ?? '16px'),
        'heading_font_weight' => sanitize_input($_POST['heading_font_weight'] ?? '700')
    ];

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
        $stmt->execute([$key, $value]);
    }

    set_flash('success', 'ফন্ট ও টাইপোগ্রাফি সেটিংস সফলভাবে সেভ করা হয়েছে।');
    redirect(BASE_URL . '/admin/settings/typography.php');
}

$settings_raw = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

$page_title = "টাইপোগ্রাফি সেটিংস — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-fonts text-primary me-2"></i> ডায়নামিক বাংলা ফন্ট ও সাইজ কন্ট্রোল</h3>
</div>

<?php display_flash(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <form method="POST" action="">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-medium">প্রাইমারি বাংলা ফন্ট</label>
                    <select name="primary_font" class="form-select">
                        <option value="Noto Sans Bengali" <?= ($settings_raw['primary_font'] ?? 'Noto Sans Bengali') === 'Noto Sans Bengali' ? 'selected' : '' ?>>Noto Sans Bengali (নোতো সান্স বাংলা - ডিফল্ট)</option>
                        <option value="Kalpurush" <?= ($settings_raw['primary_font'] ?? '') === 'Kalpurush' ? 'selected' : '' ?>>Kalpurush (কালপুরুষ)</option>
                        <option value="SolaimanLipi" <?= ($settings_raw['primary_font'] ?? '') === 'SolaimanLipi' ? 'selected' : '' ?>>SolaimanLipi (সোলাইমান লিপি)</option>
                        <option value="Hind Siliguri" <?= ($settings_raw['primary_font'] ?? '') === 'Hind Siliguri' ? 'selected' : '' ?>>Hind Siliguri (হিন্দ শিলিগুড়ি)</option>
                        <option value="Roboto" <?= ($settings_raw['primary_font'] ?? '') === 'Roboto' ? 'selected' : '' ?>>Roboto / System Sans</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-medium">মূল ফন্ট সাইজ (Base Font Size)</label>
                    <select name="base_font_size" class="form-select">
                        <option value="14px" <?= ($settings_raw['base_font_size'] ?? '') === '14px' ? 'selected' : '' ?>>14px (ছোট)</option>
                        <option value="16px" <?= ($settings_raw['base_font_size'] ?? '16px') === '16px' ? 'selected' : '' ?>>16px (স্বাভাবিক - সুপারিশকৃত)</option>
                        <option value="18px" <?= ($settings_raw['base_font_size'] ?? '') === '18px' ? 'selected' : '' ?>>18px (বড়)</option>
                        <option value="20px" <?= ($settings_raw['base_font_size'] ?? '') === '20px' ? 'selected' : '' ?>>20px (অতিরিক্ত বড়)</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-medium">হেডিং ফন্ট ওয়েট (Heading Weight)</label>
                    <select name="heading_font_weight" class="form-select">
                        <option value="600" <?= ($settings_raw['heading_font_weight'] ?? '') === '600' ? 'selected' : '' ?>>600 (Semi-Bold)</option>
                        <option value="700" <?= ($settings_raw['heading_font_weight'] ?? '700') === '700' ? 'selected' : '' ?>>700 (Bold)</option>
                        <option value="800" <?= ($settings_raw['heading_font_weight'] ?? '') === '800' ? 'selected' : '' ?>>800 (Extra Bold)</option>
                    </select>
                </div>

                <div class="col-12 mt-4">
                    <div class="p-4 bg-light rounded-3 border">
                        <h5 class="fw-bold mb-2">লাইভ প্রিভিউ টেস্ট:</h5>
                        <p class="mb-0 fs-5">মাছের রোগ নির্ণয় ও পুকুরের সঠিক পরিচর্যাই সফল খামারের মূল চাবিকাঠি।</p>
                    </div>
                </div>

                <div class="col-12 text-end mt-3">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> টাইপোগ্রাফি সংরক্ষণ করুন</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
