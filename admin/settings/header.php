<?php
// ============================================================
// FarmersBD — Admin Settings: Header & Announcement Bar
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
        'announcement_bar_enabled' => isset($_POST['announcement_bar_enabled']) ? '1' : '0',
        'announcement_text'        => sanitize_input($_POST['announcement_text'] ?? ''),
        'announcement_link'        => sanitize_input($_POST['announcement_link'] ?? ''),
        'announcement_bg'          => sanitize_input($_POST['announcement_bg'] ?? '#2e7d32'),
        'announcement_color'       => sanitize_input($_POST['announcement_color'] ?? '#ffffff'),
        'header_hotline'           => sanitize_input($_POST['header_hotline'] ?? '01700000000'),
        'header_whatsapp'          => sanitize_input($_POST['header_whatsapp'] ?? '01700000000'),
        'header_sticky'            => isset($_POST['header_sticky']) ? '1' : '0',
        'show_search_bar'          => isset($_POST['show_search_bar']) ? '1' : '0',
    ];

    // Logo upload
    if (!empty($_FILES['site_logo']['name'])) {
        $upload_dir = $base . '/public/uploads/settings/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg'])) {
            $filename = 'logo_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $upload_dir . $filename)) {
                $settings['site_logo'] = 'uploads/settings/' . $filename;
            }
        }
    }

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
        $stmt->execute([$key, $value]);
    }

    set_flash('success', 'হেডার সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে।');
    redirect(BASE_URL . '/admin/settings/header.php');
}

$settings_raw = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

$page_title = "হেডার ও ব্যানার সেটিংস — FarmersBD Admin";
require_once $base . '/admin/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1"><i class="bi bi-layout-text-window text-primary me-2"></i>হেডার ও অ্যানাউন্সমেন্ট বার</h1>
        <p class="text-muted small mb-0">ওয়েবসাইটের শীর্ষ বার, লোগো, হটলাইন ও নোটিশ স্ক্রল সেটিংস পরিচালনা করুন</p>
    </div>
</div>

<form method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Announcement Bar -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">টপ অ্যানাউন্সমেন্ট বার (Notice Bar)</div>
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="announcement_bar_enabled" id="annSwitch" value="1" <?= ($settings_raw['announcement_bar_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="annSwitch">অ্যানাউন্সমেন্ট বার প্রদর্শন করুন</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">নোটিশ বার্তা (Announcement Text)</label>
                        <input type="text" name="announcement_text" class="form-control" value="<?= e($settings_raw['announcement_text'] ?? '🚚 সারা বাংলাদেশে হোম ডেলিভারি ও দ্রুত সার্ভিস | হটলাইন: 01700-000000') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">ক্লিকেবল লিংক (ঐচ্ছিক)</label>
                        <input type="text" name="announcement_link" class="form-control font-monospace" placeholder="/offers" value="<?= e($settings_raw['announcement_link'] ?? '') ?>">
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ব্যাকগ্রাউন্ড কালার</label>
                            <input type="color" name="announcement_bg" class="form-control form-control-color w-100" value="<?= e($settings_raw['announcement_bg'] ?? '#2e7d32') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">টেক্সট কালার</label>
                            <input type="color" name="announcement_color" class="form-control form-control-color w-100" value="<?= e($settings_raw['announcement_color'] ?? '#ffffff') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Header Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">হেডার কন্টাক্ট ও ন্যাভিগেশন</div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">হটলাইন নম্বর</label>
                            <input type="text" name="header_hotline" class="form-control font-monospace" value="<?= e($settings_raw['header_hotline'] ?? '01700000000') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">WhatsApp হেল্পলাইন</label>
                            <input type="text" name="header_whatsapp" class="form-control font-monospace" value="<?= e($settings_raw['header_whatsapp'] ?? '01700000000') ?>">
                        </div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="header_sticky" id="stickySwitch" value="1" <?= ($settings_raw['header_sticky'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="stickySwitch">স্টিকি হেডার চালু রাখুন (Sticky on Scroll)</label>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="show_search_bar" id="searchSwitch" value="1" <?= ($settings_raw['show_search_bar'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="searchSwitch">হেডারে সার্চ বার প্রদর্শন করুন</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">লোগো (Site Logo)</div>
                <div class="card-body p-4 text-center">
                    <?php if (!empty($settings_raw['site_logo'])): ?>
                        <div class="p-3 bg-light rounded border mb-3">
                            <img src="<?= asset($settings_raw['site_logo']) ?>" alt="Logo" class="img-fluid" style="max-height: 80px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="site_logo" class="form-control" accept="image/*">
                    <div class="form-text small mt-2">PNG / WebP / SVG ফরম্যাট সুপারিশকৃত</div>
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
