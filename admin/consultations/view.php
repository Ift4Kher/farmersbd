<?php
// ============================================================
// FarmersBD — Admin Consultation View & Reply Form
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
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pdo = get_db_connection();

$stmt = $pdo->prepare("SELECT c.*, u.name as user_name, u.mobile, u.email FROM consultations c LEFT JOIN users u ON c.user_id = u.id WHERE c.id = ?");
$stmt->execute([$id]);
$consultation = $stmt->fetch();

if (!$consultation) {
    set_flash('error', 'আবেদন পাওয়া যায়নি।');
    redirect(BASE_URL . '/admin/consultations/index.php');
}

$page_title = "পরামর্শ রিভিউ — " . h($consultation['subject']) . " — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">পরামর্শ পর্যালোচনা ও প্রতিক্রিয়া</h3>
        <span class="text-muted small">জমা তারিখ: <?= format_date_bn($consultation['created_at']) ?></span>
    </div>
    <a href="<?= BASE_URL ?>/admin/consultations/index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> তালিকায় ফিরে যান</a>
</div>

<?php display_flash(); ?>

<div class="row g-4">
    <!-- Question Content -->
    <div class="col-md-7">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">গ্রাহকের প্রশ্ন ও ছবি</h5>
                <?= get_consultation_status_badge($consultation['status']) ?>
            </div>
            <div class="card-body p-4">
                <h4 class="fw-bold text-dark mb-3"><?= h($consultation['subject']) ?></h4>

                <div class="bg-light p-3 rounded mb-3">
                    <p class="mb-1"><strong>গ্রাহক:</strong> <?= h($consultation['user_name']) ?> (<?= h($consultation['mobile']) ?>)</p>
                    <p class="mb-1"><strong>মাছের জাত:</strong> <?= h($consultation['fish_type'] ?? 'N/A') ?></p>
                    <p class="mb-1"><strong>পুকুরের আকার:</strong> <?= h($consultation['pond_size'] ?? 'N/A') ?></p>
                    <p class="mb-0"><strong>লক্ষণ:</strong> <?= h($consultation['symptoms'] ?? 'N/A') ?></p>
                </div>

                <div class="lh-lg text-secondary mb-3">
                    <?= nl2br(h($consultation['description'])) ?>
                </div>

                <?php if (!empty($consultation['image'])): ?>
                    <div class="mt-3">
                        <h6>সংযুক্ত ছবি:</h6>
                        <img src="<?= get_upload_url($consultation['image'], 'consultations') ?>" class="img-fluid rounded border max-w-400" alt="">
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Reply Form -->
    <div class="col-md-5">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-primary text-white py-3 border-bottom-0">
                <h5 class="fw-bold mb-0"><i class="bi bi-pen me-2"></i> ডাক্তারের প্রেসক্রিপশন ও মতামত লিখুন</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="<?= BASE_URL ?>/admin/consultations/reply.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="consultation_id" value="<?= $consultation['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label fw-medium">পরামর্শ/প্রেসক্রিপশন বিবরণ <span class="text-danger">*</span></label>
                        <textarea name="admin_reply" class="form-control" rows="8" placeholder="রোগের নাম, ঔষধের নাম, প্রয়োগবিধি ও প্রয়োজনীয় পরামর্শ লিখুন..." required><?= h($consultation['admin_reply'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">আবেদনের স্ট্যাটাস</label>
                        <select name="status" class="form-select">
                            <option value="replied" <?= $consultation['status'] === 'replied' ? 'selected' : '' ?>>Replied (উত্তর সম্পন্ন)</option>
                            <option value="pending" <?= $consultation['status'] === 'pending' ? 'selected' : '' ?>>Pending (অপেক্ষমাণ)</option>
                            <option value="closed" <?= $consultation['status'] === 'closed' ? 'selected' : '' ?>>Closed (বন্ধ)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold"><i class="bi bi-send me-1"></i> প্রতিক্রিয়া প্রেরণ করুন</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
