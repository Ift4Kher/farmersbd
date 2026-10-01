<?php
// ============================================================
// FarmersBD — Consultation View / Details & Reply History Page
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

require_login();
$user_id = current_user_id();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pdo = get_db_connection();

$stmt = $pdo->prepare("SELECT * FROM consultations WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$consultation = $stmt->fetch();

if (!$consultation) {
    set_flash('error', 'পরামর্শ আবেদনটি পাওয়া যায়নি।');
    redirect(BASE_URL . '/account/consultations.php');
}

$page_title = "পরামর্শ বিবরণী — " . h($consultation['subject']) . " — " . APP_NAME;
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1"><i class="bi bi-chat-left-text text-primary me-2"></i> <?= h($consultation['subject']) ?></h3>
            <span class="text-muted small">জমা দেওয়ার তারিখ: <?= format_date_bn($consultation['created_at']) ?></span>
        </div>
        <a href="<?= BASE_URL ?>/account/consultations.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> আমার তালিকাসমূহে ফিরে যান</a>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <!-- User Question Box -->
            <div class="modern-card mb-4">
                <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-question-circle text-primary me-2"></i> আপনার প্রশ্ন ও বিবরণ</h5>
                    <?= get_consultation_status_badge($consultation['status']) ?>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3 border-bottom pb-3">
                        <?php if (!empty($consultation['fish_type'])): ?>
                            <div class="col-md-6">
                                <strong>মাছের জাত:</strong> <?= h($consultation['fish_type']) ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($consultation['pond_size'])): ?>
                            <div class="col-md-6">
                                <strong>পুকুরের আকার:</strong> <?= h($consultation['pond_size']) ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($consultation['symptoms'])): ?>
                            <div class="col-12">
                                <strong>প্রকাশিত লক্ষণ:</strong> <?= h($consultation['symptoms']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="lh-lg text-secondary mb-3">
                        <?= nl2br(h($consultation['description'])) ?>
                    </div>

                    <?php if (!empty($consultation['image'])): ?>
                        <div class="mt-3">
                            <h6 class="fw-bold">সংযুক্ত ছবি:</h6>
                            <img src="<?= get_upload_url($consultation['image'], 'consultations') ?>" class="img-fluid rounded border max-w-400" alt="Consultation image">
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Expert Response Box -->
            <div class="modern-card">
                <div class="card-header py-3 border-bottom" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); color: white; border-radius: 12px 12px 0 0;">
                    <h5 class="fw-bold mb-0 text-white"><i class="bi bi-person-badge me-2 text-white"></i> মৎস্য বিশেষজ্ঞের মতামত ও প্রেসক্রিপশন</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($consultation['admin_reply'])): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-hourglass-split display-4 text-warning mb-2"></i>
                            <h5 class="fw-bold text-dark">আবেদনটি বর্তমানে পর্যালোচনায় রয়েছে</h5>
                            <p class="text-muted small mb-0">আমাদের মৎস্য বিজ্ঞানী আপনার আবেদনটি পর্যালোচনা করছেন। উত্তর পাওয়ার সাথে সাথে এখানে দেখতে পাবেন।</p>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success border-0 mb-3">
                            <i class="bi bi-check-circle-fill me-2"></i> <strong>উত্তর প্রদান করা হয়েছে</strong> (<?= format_date_bn($consultation['replied_at'] ?? $consultation['updated_at']) ?>)
                        </div>
                        <div class="lh-lg text-dark fs-6 p-4 rounded-3 border" style="background: rgba(248, 250, 252, 0.5);">
                            <?= nl2br(h($consultation['admin_reply'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="modern-card p-4 text-center">
                <i class="bi bi-headset display-4 text-primary mb-2"></i>
                <h5 class="fw-bold text-dark">জরুরী সাহায্য প্রয়োজন?</h5>
                <p class="small text-muted mb-3">সরাসরি আমাদের হেল্পলাইনে কল করে কথা বলতে পারেন।</p>
                <a href="tel:01700000000" class="btn btn-modern fw-bold"><i class="bi bi-telephone-fill me-1"></i> ০১৭০০-০০০০০০</a>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
