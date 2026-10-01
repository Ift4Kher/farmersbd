<?php
// ============================================================
// FarmersBD — Change Password Page
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/flash.php';

require_login();
$user_id = current_user_id();
$pdo = get_db_connection();

$errors = [];

if (is_post_request()) {
    verify_csrf_token();

    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!password_verify($current_password, $user['password'])) {
        $errors[] = "বর্তমান পাসওয়ার্ড ভুল।";
    }

    if (strlen($new_password) < 6) {
        $errors[] = "নতুন পাসওয়ার্ড অন্তত ৬ অক্ষরের হতে হবে।";
    }

    if ($new_password !== $confirm_password) {
        $errors[] = "নতুন পাসওয়ার্ড ও নিশ্চিতকরণ পাসওয়ার্ড মেলেনি।";
    }

    if (empty($errors)) {
        $hashed = password_hash($new_password, PASSWORD_BCRYPT);
        $update = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
        $update->execute([$hashed, $user_id]);

        set_flash('success', 'পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে।');
        redirect(BASE_URL . '/account/dashboard.php');
    }
}

$page_title = "পাসওয়ার্ড পরিবর্তন — " . APP_NAME;
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-md-3">
            <?php include dirname(__DIR__) . '/includes/account-sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <div class="modern-card p-0">
                <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold text-dark mb-0"><i class="bi bi-key-fill text-primary me-2"></i> পাসওয়ার্ড পরিবর্তন</h4>
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
                        <div class="mb-3">
                            <label class="form-label fw-medium">বর্তমান পাসওয়ার্ড <span class="text-danger">*</span></label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">নতুন পাসওয়ার্ড (কমপক্ষে ৬ অক্ষর) <span class="text-danger">*</span></label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">নতুন পাসওয়ার্ড নিশ্চিত করুন <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="6">
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <button type="submit" class="btn btn-modern px-5 py-2"><i class="bi bi-shield-check me-1"></i> পাসওয়ার্ড আপডেট করুন</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
