<?php
// ============================================================
// FarmersBD — Admin Edit FAQ
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/csrf.php';
require_once $base . '/includes/flash.php';

require_admin();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pdo = get_db_connection();

$stmt = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
$stmt->execute([$id]);
$faq = $stmt->fetch();

if (!$faq) {
    set_flash('error', 'প্রশ্ন পাওয়া যায়নি।');
    redirect(BASE_URL . '/admin/faq/index.php');
}

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
        $update = $pdo->prepare("UPDATE faqs SET question = ?, answer = ?, sort_order = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
        $update->execute([$question, $answer, $sort_order, $is_active, $id]);

        set_flash('success', 'FAQ প্রশ্ন সফলভাবে আপডেট করা হয়েছে।');
        redirect(BASE_URL . '/admin/faq/index.php');
    }
}

$page_title = "FAQ সম্পাদনা — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0">FAQ প্রশ্ন ও উত্তর সম্পাদনা করুন</h3>
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
                    <input type="text" name="question" class="form-control" required value="<?= h($faq['question']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">বিস্তারিত উত্তর <span class="text-danger">*</span></label>
                    <textarea name="answer" class="form-control" rows="4" required><?= h($faq['answer']) ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">ক্রম (Sort Order)</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= h($faq['sort_order']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">স্ট্যাটাস</label>
                    <select name="is_active" class="form-select">
                        <option value="1" <?= ($faq['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= ($faq['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i> আপডেট করুন</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
