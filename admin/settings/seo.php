<?php
// ============================================================
// FarmersBD — Admin Settings: SEO Configuration
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
        'meta_keywords'    => sanitize_input($_POST['meta_keywords'] ?? ''),
        'meta_description' => sanitize_input($_POST['meta_description'] ?? ''),
        'google_analytics' => $_POST['google_analytics'] ?? ''
    ];

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
        $stmt->execute([$key, $value]);
    }

    set_flash('success', 'SEO সেটিংস সফলভাবে সেভ করা হয়েছে।');
    redirect(BASE_URL . '/admin/settings/seo.php');
}

$settings_raw = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

$page_title = "SEO সেটিংস — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-search-heart text-primary me-2"></i> এসইও (SEO) মেটা সেটিংস</h3>
</div>

<?php display_flash(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <form method="POST" action="">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-medium">গ্লোবাল মেটা কীওয়ার্ডস (Meta Keywords)</label>
                    <input type="text" name="meta_keywords" class="form-control" placeholder="যেমন: fish farming, bangladesh fish, aqua medicine, মাছ চাষ, মাছের রোগ" value="<?= h($settings_raw['meta_keywords'] ?? 'মাছ চাষ, মাছের রোগ, AI রোগ নির্ণয়, একুয়া মেডিসিন, FarmersBD') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">গ্লোবাল মেটা বিবরণ (Meta Description)</label>
                    <textarea name="meta_description" class="form-control" rows="3"><?= h($settings_raw['meta_description'] ?? 'ফার্মার্সবিডি — বাংলাদেশের প্রথম AI চালিত স্মার্ট মৎস্য চাষ ও একুয়া প্রোডাক্ট অনলাইন প্ল্যাটফর্ম।') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Google Analytics / Header Script (HTML/JS)</label>
                    <textarea name="google_analytics" class="form-control font-monospace" rows="4" placeholder="<script>...</script>"><?= h($settings_raw['google_analytics'] ?? '') ?></textarea>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> SEO সেটিংস সংরক্ষণ করুন</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
