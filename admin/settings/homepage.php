<?php
// ============================================================
// FarmersBD — Admin Settings: Homepage CMS & Section Toggles
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
        'hero_title'           => sanitize_input($_POST['hero_title'] ?? ''),
        'hero_subtitle'        => sanitize_input($_POST['hero_subtitle'] ?? ''),
        'show_ai_banner'       => isset($_POST['show_ai_banner']) ? '1' : '0',
        'show_featured_products'=> isset($_POST['show_featured_products']) ? '1' : '0',
        'show_diseases_section'=> isset($_POST['show_diseases_section']) ? '1' : '0',
        'show_latest_blogs'    => isset($_POST['show_latest_blogs']) ? '1' : '0'
    ];

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
        $stmt->execute([$key, $value]);
    }

    set_flash('success', 'হোমপেজ সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে।');
    redirect(BASE_URL . '/admin/settings/homepage.php');
}

$settings_raw = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

$page_title = "হোমপেজ কন্টেন্ট কন্ট্রোল — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-window-sidebar text-primary me-2"></i> ডায়নামিক হোমপেজ কন্টেন্ট ম্যানেজমেন্ট</h3>
</div>

<?php display_flash(); ?>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-4">
        <form method="POST" action="">
            <?= csrf_field() ?>
            <h5 class="fw-bold text-primary mb-3">হিরো সেকশন টেক্সট</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-medium">হিরো টাইটেল</label>
                    <input type="text" name="hero_title" class="form-control" value="<?= h($settings_raw['hero_title'] ?? 'স্মার্ট মৎস্য চাষে আপনার বিশ্বস্ত ডিজিটাল সঙ্গী') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">হিরো সাব-টাইটেল</label>
                    <input type="text" name="hero_subtitle" class="form-control" value="<?= h($settings_raw['hero_subtitle'] ?? 'কৃত্রিম বুদ্ধিমত্তা দিয়ে মাছের রোগ নির্ণয় করুন ও বিশেষজ্ঞ সমাধান পান') ?>">
                </div>
            </div>

            <h5 class="fw-bold text-primary mb-3">হোমপেজ সেকশন ভিজিবিলিটি টগল</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" name="show_ai_banner" id="ai_ban" <?= ($settings_raw['show_ai_banner'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fs-6 fw-medium" for="ai_ban">AI ব্যানার সেকশন</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" name="show_featured_products" id="feat_prod" <?= ($settings_raw['show_featured_products'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fs-6 fw-medium" for="feat_prod">ফিচার্ড প্রোডাক্টস</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" name="show_diseases_section" id="dis_sec" <?= ($settings_raw['show_diseases_section'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fs-6 fw-medium" for="dis_sec">মাছের রোগ ডিরেক্টরি</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" name="show_latest_blogs" id="blog_sec" <?= ($settings_raw['show_latest_blogs'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fs-6 fw-medium" for="blog_sec">সর্বশেষ ব্লগ</label>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> পরিবর্তন সংরক্ষণ করুন</button>
            </div>
        </form>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
