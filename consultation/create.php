<?php
// ============================================================
// FarmersBD — Request Expert Consultation Form
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/flash.php';
require_once dirname(__DIR__) . '/services/UploadService.php';

require_login();
$user_id = current_user_id();
$pdo = get_db_connection();

$errors = [];

if (is_post_request()) {
    verify_csrf_token();

    $subject     = sanitize_input($_POST['subject'] ?? '');
    $fish_type   = sanitize_input($_POST['fish_type'] ?? '');
    $pond_size   = sanitize_input($_POST['pond_size'] ?? '');
    $symptoms    = sanitize_input($_POST['symptoms'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');

    if (empty($subject)) $errors[] = "পরামর্শের বিষয় উল্লেখ করুন।";
    if (empty($description)) $errors[] = "সমস্যার বিস্তারিত বিবরণ দিন।";

    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploader = new UploadService();
        $upload_res = $uploader->upload_image($_FILES['image'], 'consultations');
        if ($upload_res['success']) {
            $image_path = $upload_res['filename'];
        } else {
            $errors[] = $upload_res['error'];
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO consultations (user_id, subject, fish_type, pond_size, symptoms, description, image, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
        $stmt->execute([$user_id, $subject, $fish_type, $pond_size, $symptoms, $description, $image_path]);
        $new_id = $pdo->lastInsertId();

        send_admin_notification('alert', "নতুন পরামর্শ অনুরোধ এসেছে: {$subject}", BASE_URL . "/admin/consultations/view.php?id={$new_id}");

        set_flash('success', 'আপনার পরামর্শের আবেদন সফলভাবে জমা হয়েছে। আমাদের বিশেষজ্ঞ খুব শীঘ্রই পর্যালোচনা করে উত্তর দিবেন।');
        redirect(BASE_URL . '/consultation/details.php?id=' . $new_id);
    }
}

$page_title = "নতুন মৎস্য পরামর্শ আবেদন — " . APP_NAME;
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="modern-card p-1">
                <div class="card-header bg-transparent py-3 border-bottom">
                    <h4 class="fw-bold text-dark mb-0"><i class="bi bi-chat-left-text text-primary me-2"></i> পরামর্শ ফর্ম জমা দিন</h4>
                </div>
                <div class="card-body p-4">
                    <?php display_flash(); ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= h($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label fw-medium">পরামর্শের বিষয় <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control form-control-modern" placeholder="যেমন: পাঙ্গাস মাছের শরীরে লাল দাগ ও ক্ষতের চিকিৎসা" required value="<?= h($_POST['subject'] ?? '') ?>">
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">আক্রান্ত মাছের ধরন</label>
                                <input type="text" name="fish_type" class="form-control form-control-modern" placeholder="যেমন: রুই, কাতলা, পাঙ্গাস, তেলাপিয়া" value="<?= h($_POST['fish_type'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">পুকুরের আয়তন / সংখ্যা</label>
                                <input type="text" name="pond_size" class="form-control form-control-modern" placeholder="যেমন: ৫০ শতক, ১ টি পুকুর" value="<?= h($_POST['pond_size'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">লক্ষণসমূহ</label>
                            <input type="text" name="symptoms" class="form-control form-control-modern" placeholder="যেমন: মাছ ভেসে ওঠা, পাখনা পচা, খাওয়া বন্ধ করা" value="<?= h($_POST['symptoms'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">সমস্যার বিস্তারিত বিবরণ <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control form-control-modern" rows="5" placeholder="কবে থেকে সমস্যা শুরু হয়েছে, কি কি ঔষধ বা খাবার দেওয়া হয়েছে বিস্তারিত লিখুন..." required><?= h($_POST['description'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium">মাছের/পুকুরের ছবি (ঐচ্ছিক)</label>
                            <input type="file" name="image" class="form-control form-control-modern" accept="image/*" style="padding: 0.75rem 1.25rem;">
                            <div class="form-text">স্পষ্ট ছবি যুক্ত করলে সঠিক রোগ নির্ণয় করা সহজ হয়।</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="<?= BASE_URL ?>/consultation/index.php" class="btn btn-outline-secondary fw-bold" style="border-radius: 10px;"><i class="bi bi-arrow-left"></i> বাতিল</a>
                            <button type="submit" class="btn btn-modern px-4"><i class="bi bi-send me-1"></i> পরামর্শ জমা দিন</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
