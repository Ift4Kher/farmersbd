<?php
// ============================================================
// FarmersBD — Admin Add FAQ
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
$errors = [];

if (is_post_request()) {
    verify_csrf_token();

    $question   = sanitize_input($_POST['question'] ?? '');
    $answer     = sanitize_input($_POST['answer'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active  = (int)($_POST['is_active'] ?? 1);

    if (empty($question)) $errors[] = "প্রশ্ন লিখুন।";
    if (empty($answer)) $errors[] = "উত্তর লিখুন।";

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO faqs (question, answer, sort_order, is_active, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$question, $answer, $sort_order, $is_active]);

        set_flash('success', 'FAQ প্রশ্ন সফলভাবে যুক্ত করা হয়েছে।');
        redirect(BASE_URL . '/admin/faq/index.php');
    }
}

$page_title = "নতুন FAQ প্রশ্ন — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0">নতুন সাধারণ জিজ্ঞাসা (FAQ) যোগ করুন</h3>
    <a href="<?= BASE_URL ?>/admin/faq/index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> তালিকায় ফিরে যান</a>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
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
                <div class="col-12">
                    <label class="form-label fw-medium">প্রশ্ন <span class="text-danger">*</span></label>
                    <input type="text" name="question" class="form-control" required value="<?= h($_POST['question'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">বিস্তারিত উত্তর <span class="text-danger">*</span></label>
                    <textarea name="answer" class="form-control" rows="4" required><?= h($_POST['answer'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">ক্রম (Sort Order)</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= h($_POST['sort_order'] ?? 0) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">স্ট্যাটাস</label>
                    <select name="is_active" class="form-select">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> সংরক্ষণ করুন</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
