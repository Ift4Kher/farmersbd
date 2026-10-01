<?php
// ============================================================
// FarmersBD — Contact Us & Feedback Page
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/flash.php';
require_once dirname(__DIR__) . '/includes/seo.php';

$pdo = get_db_connection();
$errors = [];

if (is_post_request()) {
    verify_csrf_token();

    $name    = sanitize_input($_POST['name'] ?? '');
    $phone   = sanitize_input($_POST['phone'] ?? '');
    $email   = sanitize_input($_POST['email'] ?? '');
    $subject = sanitize_input($_POST['subject'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');

    if (empty($name)) $errors[] = "আপনার নাম লিখুন।";
    if (empty($phone)) $errors[] = "ফোন নম্বর প্রদান করুন।";
    if (empty($message)) $errors[] = "আপনার বার্তা বা মতামত লিখুন।";

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, phone, email, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$name, $phone, $email, $subject, $message]);

        send_admin_notification('alert', "নতুন মেসেজ এসেছে: {$name} ({$subject})", BASE_URL . "/admin/contacts/");

        set_flash('success', 'আপনার বার্তা সফলভাবে পাঠানো হয়েছে। ধন্যবাদ!');
        redirect(BASE_URL . '/contact/index.php');
    }
}

$page_title = "যোগাযোগ করুন — " . APP_NAME;
$meta_desc = "FarmersBD সাপোর্ট টিম ও বিজ্ঞানীদের সাথে যোগাযোগের ঠিকানা, ফোন নম্বর, ইমেইল এবং মেসেজ ফর্ম।";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<div class="container py-4 py-md-5" style="max-width: 1100px;">
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="background: rgba(255,255,255,0.9); backdrop-filter: blur(20px);">
        <div class="row g-0">
            <!-- Contact Info Panel -->
            <div class="col-md-5 position-relative p-4 p-md-5 text-white" style="background: linear-gradient(135deg, #0c4a6e 0%, #0284c7 100%);">
                <!-- Abstract Decorative Circles (desktop only) -->
                <div class="position-absolute rounded-circle d-none d-md-block" style="width: 200px; height: 200px; background: rgba(255,255,255,0.05); top: -50px; right: -50px; filter: blur(20px);"></div>
                <div class="position-absolute rounded-circle d-none d-md-block" style="width: 150px; height: 150px; background: rgba(255,255,255,0.05); bottom: -50px; left: -30px; filter: blur(15px);"></div>

                <div class="position-relative z-1">
                    <h3 class="fw-bold mb-2 mb-md-4" style="letter-spacing: -0.5px; font-size: 1.3rem;">আমাদের সাথে যোগাযোগ</h3>
                    <p class="opacity-75 mb-3 mb-md-5 d-none d-md-block" style="font-size: 1.05rem; line-height: 1.7;">যেকোনো প্রশ্ন, মতামত বা তথ্য সহায়তার জন্য সরাসরি আমাদের সাথে যোগাযোগ করতে পারেন। আমরা দ্রুততম সময়ের মধ্যে আপনার সাথে যুক্ত হবো।</p>

                    <!-- Desktop: vertical stack | Mobile: compact rows -->
                    <div class="d-flex flex-column gap-3 gap-md-4">
                        <div class="d-flex align-items-center align-items-md-start">
                            <div class="bg-white bg-opacity-10 rounded-circle me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                <i class="bi bi-geo-alt-fill text-warning"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 mb-md-1 text-white opacity-90 small">হেড অফিস</h6>
                                <p class="mb-0 text-white-50 small">ফার্মার্সবিডি, মৎস্য ভবন সংলগ্ন, ঢাকা</p>
                            </div>
                        </div>

                        <div class="d-flex align-items-center align-items-md-start">
                            <div class="bg-white bg-opacity-10 rounded-circle me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                <i class="bi bi-telephone-fill text-warning"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 mb-md-1 text-white opacity-90 small">হেল্পলাইন</h6>
                                <p class="mb-0 text-white-50 small">+৮৮০ ১৯৭৯-৬০৬২১২ (01979-606212)</p>
                            </div>
                        </div>

                        <div class="d-flex align-items-center align-items-md-start">
                            <div class="bg-white bg-opacity-10 rounded-circle me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                <i class="bi bi-envelope-fill text-warning"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 mb-md-1 text-white opacity-90 small">ইমেইল ঠিকানা</h6>
                                <p class="mb-0 text-white-50 small">Info.Farmersbd@gmail.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-md-7 p-4 p-md-5">
                <h4 class="fw-bold text-dark mb-2" style="letter-spacing: -0.5px; font-size: 1.2rem;">মেসেজ পাঠান</h4>
                <p class="text-muted mb-3 mb-md-4 small">নিচের ফর্মটি পূরণ করে আপনার বার্তা আমাদের কাছে পাঠিয়ে দিন।</p>
                
                <?php display_flash(); ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger rounded-3 border-0 bg-danger bg-opacity-10 text-danger">
                        <ul class="mb-0 small fw-medium">
                            <?php foreach ($errors as $err): ?>
                                <li><?= h($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-uppercase text-muted" style="letter-spacing: 0.5px; font-size: 0.7rem;">আপনার নাম <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-modern" required value="<?= h($_POST['name'] ?? '') ?>" placeholder="নাম লিখুন">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-uppercase text-muted" style="letter-spacing: 0.5px; font-size: 0.7rem;">ফোন নম্বর <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control form-control-modern" required value="<?= h($_POST['phone'] ?? '') ?>" placeholder="০১৭XXXXXXXX">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-uppercase text-muted" style="letter-spacing: 0.5px; font-size: 0.7rem;">ইমেইল ঠিকানা</label>
                            <input type="email" name="email" class="form-control form-control-modern" value="<?= h($_POST['email'] ?? '') ?>" placeholder="উদাহরণ@email.com">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-uppercase text-muted" style="letter-spacing: 0.5px; font-size: 0.7rem;">বিষয়</label>
                            <input type="text" name="subject" class="form-control form-control-modern" value="<?= h($_POST['subject'] ?? '') ?>" placeholder="কী বিষয়ে জানতে চান?">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-uppercase text-muted" style="letter-spacing: 0.5px; font-size: 0.7rem;">বার্তা বা মতামত <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control form-control-modern" rows="4" required placeholder="আপনার বিস্তারিত বার্তা এখানে লিখুন..."><?= h($_POST['message'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-modern px-5 py-3 rounded-pill fw-bold shadow-lg w-100" style="letter-spacing: 0.5px;">
                                বার্তা পাঠান <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
