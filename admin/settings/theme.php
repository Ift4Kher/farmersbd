<?php
// ============================================================
// FarmersBD — Admin Settings: Theme Colors & Custom CSS
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
        'theme_primary_color'   => sanitize_input($_POST['theme_primary_color'] ?? '#2e7d32'),
        'theme_secondary_color' => sanitize_input($_POST['theme_secondary_color'] ?? '#1565c0'),
        'theme_accent_color'    => sanitize_input($_POST['theme_accent_color'] ?? '#f57c00'),
        'theme_font_family'     => sanitize_input($_POST['theme_font_family'] ?? "'Noto Sans Bengali', sans-serif"),
        'theme_border_radius'   => sanitize_input($_POST['theme_border_radius'] ?? '8px'),
        'custom_css'            => $_POST['custom_css'] ?? '',
        'custom_js'             => $_POST['custom_js'] ?? '',
    ];

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
        $stmt->execute([$key, $value]);
    }

    set_flash('success', 'থিম ও স্টাইলিং সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে।');
    redirect(BASE_URL . '/admin/settings/theme.php');
}

$settings_raw = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

$page_title = "থিম ও কালার সেটিংস — FarmersBD Admin";
require_once $base . '/admin/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1"><i class="bi bi-palette text-primary me-2"></i>থিম ও ভিজ্যুয়াল স্টাইলিং</h1>
        <p class="text-muted small mb-0">ব্র্যান্ড প্রাইমারি কালার, ফন্ট, বর্ডার রেডিয়াস ও কাস্টম সিএসএস নির্ধারণ করুন</p>
    </div>
</div>

<form method="POST">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">ব্র্যান্ড কালার প্যালেট</div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">প্রাইমারি ব্র্যান্ড কালার (Primary)</label>
                            <input type="color" name="theme_primary_color" class="form-control form-control-color w-100" value="<?= e($settings_raw['theme_primary_color'] ?? '#2e7d32') ?>">
                            <div class="form-text small">বাটন, হেডার ও মূল হাইলাইট</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">সেকেন্ডারি কালার (Secondary)</label>
                            <input type="color" name="theme_secondary_color" class="form-control form-control-color w-100" value="<?= e($settings_raw['theme_secondary_color'] ?? '#1565c0') ?>">
                            <div class="form-text small">সাব-হেডিং ও ইনফো ব্যাজ</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">অ্যাকসেন্ট কালার (Accent / CTA)</label>
                            <input type="color" name="theme_accent_color" class="form-control form-control-color w-100" value="<?= e($settings_raw['theme_accent_color'] ?? '#f57c00') ?>">
                            <div class="form-text small">অফার ব্যাজ ও স্পেশাল নোটিশ</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">কাস্টম CSS কোড</div>
                <div class="card-body p-4">
                    <textarea name="custom_css" class="form-control font-monospace" rows="6" placeholder="/* আপনার কাস্টম CSS কোড এখানে লিখুন */"><?= e($settings_raw['custom_css'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">কাস্টম হেডার/ফুটার স্ক্রিপ্ট (JS / Analytics)</div>
                <div class="card-body p-4">
                    <textarea name="custom_js" class="form-control font-monospace" rows="5" placeholder="<!-- Google Analytics, Facebook Pixel or Custom Scripts -->"><?= e($settings_raw['custom_js'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">উপাদান শৈলী (Element Styling)</div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">বর্ডার রেডিয়াস (Border Radius)</label>
                        <select name="theme_border_radius" class="form-select">
                            <option value="4px" <?= ($settings_raw['theme_border_radius'] ?? '8px') === '4px' ? 'selected' : '' ?>>Square (4px)</option>
                            <option value="8px" <?= ($settings_raw['theme_border_radius'] ?? '8px') === '8px' ? 'selected' : '' ?>>Modern Rounded (8px)</option>
                            <option value="12px" <?= ($settings_raw['theme_border_radius'] ?? '8px') === '12px' ? 'selected' : '' ?>>Smooth Soft (12px)</option>
                            <option value="16px" <?= ($settings_raw['theme_border_radius'] ?? '8px') === '16px' ? 'selected' : '' ?>>Pill Curved (16px)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">বাংলা ফন্ট</label>
                        <select name="theme_font_family" class="form-select">
                            <option value="'Noto Sans Bengali', sans-serif">Noto Sans Bengali (ডিফল্ট)</option>
                            <option value="'Hind Siliguri', sans-serif">Hind Siliguri</option>
                            <option value="'Kalpurush', sans-serif">Kalpurush</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> থিম সংরক্ষণ করুন
                </button>
            </div>
        </div>
    </div>
</form>

<?php require_once $base . '/admin/includes/footer.php'; ?>
