<?php
// ============================================================
// FarmersBD — Admin Settings: Footer & Social Links
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
        'footer_about_text'  => sanitize_input($_POST['footer_about_text'] ?? ''),
        'footer_copyright'   => sanitize_input($_POST['footer_copyright'] ?? ''),
        'social_facebook'    => sanitize_input($_POST['social_facebook'] ?? ''),
        'social_youtube'     => sanitize_input($_POST['social_youtube'] ?? ''),
        'social_whatsapp'    => sanitize_input($_POST['social_whatsapp'] ?? ''),
        'social_instagram'   => sanitize_input($_POST['social_instagram'] ?? ''),
        'social_tiktok'      => sanitize_input($_POST['social_tiktok'] ?? ''),
        'footer_show_payment_badges' => isset($_POST['footer_show_payment_badges']) ? '1' : '0',
    ];

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
        $stmt->execute([$key, $value]);
    }

    set_flash('success', 'ফুটার সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে।');
    redirect(BASE_URL . '/admin/settings/footer.php');
}

$settings_raw = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

$page_title = "ফুটার ও সোশ্যাল সেটিংস — FarmersBD Admin";
require_once $base . '/admin/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1"><i class="bi bi-layout-text-window-reverse text-primary me-2"></i>ফুটার ও সোশ্যাল মিডিয়া</h1>
        <p class="text-muted small mb-0">ফুটার সম্পর্কে বিবরণ, কপিরাইট টেক্সট এবং সোশ্যাল মিডিয়া লিংক কনফিগার করুন</p>
    </div>
</div>

<form method="POST">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">ফুটার কন্টেন্ট</div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ফুটার সংক্ষিপ্ত বিবরণ (About Us Summary)</label>
                        <textarea name="footer_about_text" class="form-control" rows="3"><?= e($settings_raw['footer_about_text'] ?? 'FarmersBD — বাংলাদেশের আধুনিক মৎস্যচাষীদের বিশ্বস্ত সহযোগী। মানসম্মত পণ্য ও বিশেষজ্ঞ পরামর্শ এক ছাদের নিচে।') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">কপিরাইট টেক্সট</label>
                        <input type="text" name="footer_copyright" class="form-control" value="<?= e($settings_raw['footer_copyright'] ?? '© 2026 FarmersBD. সর্বস্বত্ব সংরক্ষিত।') ?>">
                    </div>

                    <div class="form-check form-switch pt-2">
                        <input class="form-check-input" type="checkbox" name="footer_show_payment_badges" id="payBadgeSwitch" value="1" <?= ($settings_raw['footer_show_payment_badges'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="payBadgeSwitch">পেমেন্ট গেটওয়ে ব্যাজ প্রদর্শন করুন (bKash, Nagad, Rocket, Cards)</label>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">সোশ্যাল মিডিয়া লিংকসমূহ (Social Links)</div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><i class="bi bi-facebook text-primary me-1"></i> ফেসবুক পেজ URL</label>
                            <input type="url" name="social_facebook" class="form-control" placeholder="https://facebook.com/..." value="<?= e($settings_raw['social_facebook'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><i class="bi bi-youtube text-danger me-1"></i> ইউটিউব চ্যানেল URL</label>
                            <input type="url" name="social_youtube" class="form-control" placeholder="https://youtube.com/..." value="<?= e($settings_raw['social_youtube'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><i class="bi bi-whatsapp text-success me-1"></i> WhatsApp লিংক</label>
                            <input type="text" name="social_whatsapp" class="form-control" placeholder="https://wa.me/880..." value="<?= e($settings_raw['social_whatsapp'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><i class="bi bi-instagram text-danger me-1"></i> ইনস্টাগ্রাম URL</label>
                            <input type="url" name="social_instagram" class="form-control" placeholder="https://instagram.com/..." value="<?= e($settings_raw['social_instagram'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">মেনু পরিচালনা</div>
                <div class="card-body p-4 text-center">
                    <p class="text-muted small">ফুটারের কুইক লিংক ও পলিসি মেনু সাজাতে মেনু বিল্ডার ব্যবহার করুন।</p>
                    <a href="<?= url('admin/menu-builder/?menu=footer_quick') ?>" class="btn btn-outline-primary w-100">
                        <i class="bi bi-menu-button-wide me-1"></i> ফুটার মেনু সাজান
                    </a>
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
