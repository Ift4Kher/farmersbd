<?php
// ============================================================
// FarmersBD — Admin Settings: Mobile Bottom Navigation
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
        'mobile_bottom_nav_enabled'  => isset($_POST['mobile_bottom_nav_enabled']) ? '1' : '0',
        'mobile_nav_show_home'       => isset($_POST['mobile_nav_show_home']) ? '1' : '0',
        'mobile_nav_show_categories' => isset($_POST['mobile_nav_show_categories']) ? '1' : '0',
        'mobile_nav_show_cart'       => isset($_POST['mobile_nav_show_cart']) ? '1' : '0',
        'mobile_nav_show_account'    => isset($_POST['mobile_nav_show_account']) ? '1' : '0',
        'mobile_nav_show_call'       => isset($_POST['mobile_nav_show_call']) ? '1' : '0',
    ];

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
        $stmt->execute([$key, $value]);
    }

    set_flash('success', 'মোবাইল বটম মেনু সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে।');
    redirect(BASE_URL . '/admin/settings/mobile-menu.php');
}

$settings_raw = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

$page_title = "মোবাইল মেনু সেটিংস — FarmersBD Admin";
require_once $base . '/admin/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1"><i class="bi bi-phone text-primary me-2"></i>মোবাইল বটম ন্যাভিগেশন বার</h1>
        <p class="text-muted small mb-0">স্মার্টফোনে ব্রাউজ করার সময় নিচের ফিক্সড মেনু বার এবং বাটন নিয়ন্ত্রণ করুন</p>
    </div>
</div>

<form method="POST">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">মোবাইল অ্যাপ-লাইক বটম বার</div>
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="mobile_bottom_nav_enabled" id="mobNavSwitch" value="1" <?= ($settings_raw['mobile_bottom_nav_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="mobNavSwitch">মোবাইল বটম ন্যাভিগেশন বার সক্রিয় রাখুন</label>
                    </div>

                    <h6 class="fw-bold mb-3 text-secondary">বটম বারে দৃশ্যমান আইকনসমূহ</h6>

                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="bi bi-house text-primary me-2 fs-5"></i>
                                <span class="fw-semibold">হোমপেজ লিংক (Home)</span>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="mobile_nav_show_home" value="1" <?= ($settings_raw['mobile_nav_show_home'] ?? '1') === '1' ? 'checked' : '' ?>>
                            </div>
                        </div>

                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="bi bi-grid text-success me-2 fs-5"></i>
                                <span class="fw-semibold">ক্যাটাগরি ড্রয়ার (Categories)</span>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="mobile_nav_show_categories" value="1" <?= ($settings_raw['mobile_nav_show_categories'] ?? '1') === '1' ? 'checked' : '' ?>>
                            </div>
                        </div>

                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="bi bi-cart3 text-warning me-2 fs-5"></i>
                                <span class="fw-semibold">কার্ট আইকন ও ব্যাজ (Cart Drawer)</span>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="mobile_nav_show_cart" value="1" <?= ($settings_raw['mobile_nav_show_cart'] ?? '1') === '1' ? 'checked' : '' ?>>
                            </div>
                        </div>

                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="bi bi-person text-info me-2 fs-5"></i>
                                <span class="fw-semibold">অ্যাকাউন্ট / প্রোফাইল (My Account)</span>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="mobile_nav_show_account" value="1" <?= ($settings_raw['mobile_nav_show_account'] ?? '1') === '1' ? 'checked' : '' ?>>
                            </div>
                        </div>

                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="bi bi-telephone-outbound text-danger me-2 fs-5"></i>
                                <span class="fw-semibold">সরাসরি কল বাটন (Call Now)</span>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="mobile_nav_show_call" value="1" <?= ($settings_raw['mobile_nav_show_call'] ?? '1') === '1' ? 'checked' : '' ?>>
                            </div>
                        </div>
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
