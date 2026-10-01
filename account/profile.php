<?php
// ============================================================
// FarmersBD — User Profile Edit Page
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/flash.php';
require_once dirname(__DIR__) . '/includes/validation.php';

require_login();
$user_id = current_user_id();
$pdo = get_db_connection();

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$errors = [];

if (is_post_request()) {
    verify_csrf_token();
    
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $mobile = sanitize_input($_POST['mobile'] ?? '');
    $division = sanitize_input($_POST['division'] ?? '');
    $district = sanitize_input($_POST['district'] ?? '');
    $upazila = sanitize_input($_POST['upazila'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');

    if (empty($full_name)) $errors[] = "নাম প্রয়োজন।";
    if (empty($mobile) || !validate_phone_bd($mobile)) $errors[] = "সঠিক মোবাইল নম্বর প্রদান করুন।";

    // Check duplicate mobile/email
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM users WHERE (mobile = ? OR (email = ? AND email != '')) AND id != ?");
        $check->execute([$mobile, $email, $user_id]);
        if ($check->fetch()) {
            $errors[] = "এই মোবাইল নম্বর বা ইমেইল ইতিমধ্যে অন্য অ্যাকাউন্টে ব্যবহৃত হচ্ছে।";
        }
    }

    if (empty($errors)) {
        try {
            $update = $pdo->prepare("UPDATE users SET name = ?, email = ?, mobile = ?, district = ?, address = ?, updated_at = NOW() WHERE id = ?");
            $update->execute([$full_name, $email, $mobile, $district, $address, $user_id]);
        } catch (PDOException $e) {
            error_log("[Profile Update Error] " . $e->getMessage());
            $errors[] = "প্রোফাইল তথ্য আপডেট করতে সমস্যা হয়েছে।";
        }
        
        if (empty($errors)) {
            // Update session name
            $_SESSION['user_name'] = $full_name;

            set_flash('success', 'প্রোফাইল সফলভাবে আপডেট করা হয়েছে।');
            redirect(BASE_URL . '/account/profile.php');
        }
    }
}

$page_title = "প্রোফাইল সম্পাদন — " . APP_NAME;
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-md-3">
            <?php include dirname(__DIR__) . '/includes/account-sidebar.php'; ?>
        </div>

        <!-- Main Form -->
        <div class="col-md-9">
            <div class="modern-card p-0">
                <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-person-gear text-primary me-2"></i> প্রোফাইল সম্পাদনা</h4>
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

                    <form method="POST" action="">
                        <?= csrf_field() ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">পূর্ণ নাম <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control" value="<?= h($user['name'] ?? $user['full_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">মোবাইল নম্বর <span class="text-danger">*</span></label>
                                <input type="text" name="mobile" class="form-control" value="<?= h($user['mobile']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">ইমেইল ঠিকানা</label>
                                <input type="email" name="email" class="form-control" value="<?= h($user['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">বিভাগ</label>
                                <input type="text" name="division" class="form-control" value="<?= h($user['division'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">জেলা</label>
                                <input type="text" name="district" class="form-control" value="<?= h($user['district'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">উপজেলা/থানা</label>
                                <input type="text" name="upazila" class="form-control" value="<?= h($user['upazila'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">বিস্তারিত ঠিকানা</label>
                                <textarea name="address" class="form-control form-control-modern" rows="3"><?= h($user['address'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12 text-end mt-4">
                                <button type="submit" class="btn btn-modern px-5 py-2"><i class="bi bi-check-lg me-1"></i> তথ্য সংরক্ষণ করুন</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
