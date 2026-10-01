<?php
// ============================================================
// FarmersBD — AI Prediction Handler
// POST only — accepts image, returns result page
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/flash.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/seo.php';
require_once dirname(__DIR__) . '/services/AIService.php';
require_once dirname(__DIR__) . '/services/UploadService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/ai/');
}
csrf_verify();

$ai      = new AIService();
$uploader = new UploadService();
$userId  = $_SESSION['user_id'] ?? null;
$ip      = $_SERVER['REMOTE_ADDR'] ?? '';

// ── Rate Limiting (simple: max 10 requests per IP per day) ──
$rateKey   = 'ai_rate_' . preg_replace('/[^0-9a-f]/', '', md5($ip));
$rateCount = $_SESSION[$rateKey] ?? 0;
if ($rateCount >= AI_MAX_REQUESTS_PER_DAY) {
    flash('প্রতিদিন সর্বোচ্চ ' . AI_MAX_REQUESTS_PER_DAY . ' বার AI বিশ্লেষণ করা যাবে।', FLASH_WARNING);
    redirect(BASE_URL . '/ai/');
}
$_SESSION[$rateKey] = $rateCount + 1;

// ── Upload the image ─────────────────────────────────────────
if (empty($_FILES['image']['name'])) {
    flash('ছবি নির্বাচন করুন।', FLASH_ERROR);
    redirect(BASE_URL . '/ai/');
}

$upload = $uploader->upload($_FILES['image'], 'ai');
if (!$upload['success']) {
    flash($upload['error'], FLASH_ERROR);
    redirect(BASE_URL . '/ai/');
}

$imagePath  = UPLOAD_BASE_DIR . 'ai/' . $upload['filename'];
$imageUrl   = UPLOAD_BASE_URL . 'ai/' . $upload['filename'];

// ── Call AI model ─────────────────────────────────────────────
$result  = $ai->predict($imagePath);
$disease = null;
$products = [];
$diagId  = null;

if ($result['success'] && $result['label']) {
    $disease  = $ai->find_disease_by_label($result['label']);
    if ($disease) {
        $products = $ai->get_related_products((int)$disease['id']);
    }
}

// ── Save diagnosis record ─────────────────────────────────────
$diagId = $ai->save_diagnosis(
    $userId,
    $upload['filename'],
    $result['label'] ?? null,
    $result['confidence'] ?? null,
    $disease['id'] ?? null,
    json_encode($result['raw'] ?? []),
    $ip
);

$confidence = $result['confidence'] ?? null;
$confPct    = $confidence !== null ? round($confidence * 100) : null;

// ── Display Result Page ───────────────────────────────────────
$page_seo = ['title' => 'AI রোগ নির্ণয়ের ফলাফল | ' . setting('site_name', 'FarmersBD')];
include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/navbar.php';
?>

<div class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="<?= url() ?>">হোম</a></li>
                <li class="breadcrumb-item"><a href="<?= url('ai/') ?>">AI রোগ নির্ণয়</a></li>
                <li class="breadcrumb-item active">ফলাফল</li>
            </ol>
        </nav>
        <h1><i class="bi bi-clipboard2-check me-2"></i>রোগ নির্ণয়ের ফলাফল</h1>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4 justify-content-center">
        <!-- Uploaded Image -->
        <div class="col-lg-4">
            <div class="modern-card p-3 text-center">
                <h2 class="h6 fw-bold mb-3">আপলোডকৃত ছবি</h2>
                <img src="<?= e($imageUrl) ?>" alt="আপলোডকৃত মাছের ছবি"
                     class="img-fluid rounded mb-3" style="max-height:260px;object-fit:cover">
                <a href="<?= url('ai/') ?>" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>নতুন ছবি দিন
                </a>
            </div>
        </div>

        <!-- Result -->
        <div class="col-lg-8">
            <?php if (!$result['success'] || $result['error'] === 'AI_NOT_CONFIGURED'): ?>
            <!-- AI not configured -->
            <div class="modern-card p-4">
                <div class="result-page text-center py-4">
                    <div class="result-icon error mb-3"><i class="bi bi-cpu fs-1 text-danger"></i></div>
                    <h2 class="h4 fw-bold mt-2">AI সার্ভিস উপলব্ধ নেই</h2>
                    <p class="text-muted">অনুগ্রহ করে আমাদের বিশেষজ্ঞ দলের সাথে সরাসরি যোগাযোগ করুন।</p>
                    <a href="<?= url('consultation/create.php') ?>" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-chat-text me-2"></i>বিশেষজ্ঞ পরামর্শ নিন
                    </a>
                </div>
            </div>

            <?php elseif (!$result['success']): ?>
            <!-- API error -->
            <div class="modern-card p-4">
                <div class="alert alert-danger d-flex align-items-center gap-3 rounded-3">
                    <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                    <div>
                        <strong>বিশ্লেষণে সমস্যা হয়েছে:</strong>
                        <p class="mb-0 small">ছবি বিশ্লেষণ করতে সমস্যা হয়েছে। অনুগ্রহ করে আরেকটি স্পষ্ট ও আলোকিত ছবি দিয়ে চেষ্টা করুন।</p>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="<?= url('ai/') ?>" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-arrow-clockwise me-1"></i>আবার চেষ্টা করুন
                    </a>
                </div>
            </div>

            <?php else: ?>
            <!-- Successful Gemini AI Analysis -->
            <div class="modern-card p-4 p-md-5 mb-4 shadow-sm border-0 rounded-4">
                <!-- AI Specialist Badge Header -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pb-3 mb-4 border-bottom border-light-subtle">
                    <div class="d-flex align-items-center gap-2">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle" style="width: 44px; height: 44px;">
                            <i class="bi bi-cpu-fill fs-5"></i>
                        </div>
                        <div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small mb-1">
                                <i class="bi bi-patch-check-fill me-1"></i>AI বিশেষজ্ঞ বিশ্লেষণ
                            </span>
                            <h2 class="h5 fw-bold mb-0 text-dark"><?= e($result['disease_name'] ?: 'রোগ ও সমাধান বিশ্লেষণ') ?></h2>
                        </div>
                    </div>
                    <div class="text-end">
                        <?php if ($confPct !== null): ?>
                        <span class="badge bg-primary text-white rounded-pill px-3 py-2 fw-semibold">
                            <i class="bi bi-stars me-1"></i>নির্ভুলতা <?= $confPct ?>%
                        </span>
                        <?php else: ?>
                        <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background:#ccfbf1;color:#0f766e;border:1px solid #99f6e4;">
                            <i class="bi bi-robot me-1"></i>AI মডেল পূর্বাভাস
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Gemini AI Markdown Rendered Output -->
                <?php if (!empty($result['analysis_text'])): ?>
                <div class="ai-generated-report bg-light bg-opacity-50 p-4 rounded-4 mb-4 border border-light-subtle">
                    <div class="ai-content-body">
                        <?= format_ai_markdown($result['analysis_text']) ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- DB Reference Info if matched -->
                <?php if ($disease): ?>
                <div class="accordion accordion-flush mb-4 rounded-3 overflow-hidden border border-light-subtle" id="accordionDbInfo">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingDb">
                            <button class="accordion-button collapsed fw-semibold text-dark bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDb">
                                <i class="bi bi-journal-medical text-primary me-2"></i>ডাটাবেজ রেফারেন্স ও অতিরিক্ত তথ্য (<?= e($disease['name']) ?>)
                            </button>
                        </h2>
                        <div id="collapseDb" class="accordion-collapse collapse" data-bs-parent="#accordionDbInfo">
                            <div class="accordion-body bg-light p-3">
                                <?php if ($disease['description']): ?>
                                <p class="small mb-2"><strong>বিবরণ:</strong> <?= nl2br(e($disease['description'])) ?></p>
                                <?php endif; ?>
                                <?php if ($disease['treatment']): ?>
                                <p class="small mb-0"><strong>প্রস্তাবিত চিকিৎসা:</strong> <?= nl2br(e($disease['treatment'])) ?></p>
                                <?php endif; ?>
                                <div class="mt-2">
                                    <a href="<?= url('diseases/details.php?slug=' . e($disease['slug'])) ?>" class="btn btn-outline-primary btn-sm rounded-pill">
                                        <i class="bi bi-arrow-right-circle me-1"></i>এই রোগের পূর্ণ ডাটাবেজ গাইড দেখুন
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Disclaimer & Action Buttons -->
                <div class="alert alert-warning small d-flex align-items-center gap-2 rounded-3 mb-4">
                    <i class="bi bi-info-circle-fill text-warning fs-5 flex-shrink-0"></i>
                    <div>
                        এটি কৃত্রিম বুদ্ধিমত্তা (AI) দ্বারা প্রদানকৃত পরামর্শ। খামারের জটিল বা জরুরি পরিস্থিতিতে অনুমোদিত মৎস্য কর্মকর্তার প্রত্যক্ষ সহায়তা নিন।
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= url('consultation/create.php') ?>" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                        <i class="bi bi-person-lines-fill me-1"></i>মৎস্য বিশেষজ্ঞের সাথে কথা বলুন
                    </a>
                    <a href="<?= url('ai/') ?>" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="bi bi-arrow-left me-1"></i>আরেকটি ছবি পরীক্ষা করুন
                    </a>
                    <?php if (is_logged_in()): ?>
                    <a href="<?= url('ai/history.php') ?>" class="btn btn-light border rounded-pill px-3">
                        <i class="bi bi-clock-history me-1"></i>ইতিহাস
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($products)): ?>
    <div class="mt-5">
        <h2 class="h5 fw-bold mb-3">প্রস্তাবিত পণ্যসমূহ</h2>
        <div class="row g-3">
            <?php foreach ($products as $p): ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="modern-card h-100 p-1">
                    <div class="card-img-wrapper" style="height:150px">
                        <img src="<?= uploaded_image_url('products', $p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                    </div>
                    <div class="card-body p-3">
                        <h3 class="h6 fw-bold mb-1"><?= e($p['name']) ?></h3>
                        <p class="price-current mb-2"><?= format_price(effective_price($p)) ?></p>
                        <a href="<?= url('products/details.php?slug=' . e($p['slug'])) ?>" class="btn btn-outline-primary btn-sm w-100">বিস্তারিত</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
