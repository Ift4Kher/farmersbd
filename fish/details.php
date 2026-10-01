<?php
// ============================================================
// FarmersBD — Fish Details Page Redesign
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/seo.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) {
    redirect(BASE_URL . '/fish/');
}

$pdo = Database::getInstance();
$stmt = $pdo->prepare("SELECT * FROM fish WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$fish = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$fish) {
    http_response_code(404);
    redirect(BASE_URL . '/fish/');
}

// SEO
$page_seo = [
    'title'       => e($fish['name']) . ' | মাছের জাত ও পরিচিতি | ' . setting('site_name', 'FarmersBD'),
    'description' => excerpt(strip_tags($fish['description'] ?? ''), 160),
    'og_image'    => UPLOAD_BASE_URL . 'fish/' . ($fish['image'] ?? ''),
];

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';

// Safe placeholders for UI mock
$sci_name = !empty($fish['scientific_name']) ? $fish['scientific_name'] : 'অজানা';
$water_type = !empty($fish['water_type']) ? $fish['water_type'] : 'মিঠা পানির মাছ';
$category = !empty($fish['category']) ? $fish['category'] : 'দেশীয় মাছ';
$desc = !empty($fish['description']) ? $fish['description'] : 'তেলাপিয়া একটি জনপ্রিয় মিঠা পানির মাছ, যা দ্রুত বৃদ্ধি, সহনশীলতা এবং উচ্চ বাজারমূল্যের জন্য চাষিদের কাছে খুবই পরিচিত।';

// Placeholder data since DB lacks these
$size = "১৫ - ৪০ সেমি";
$weight = "১০০ - ৮০০ গ্রাম";
$food = "সর্বভুক";
?>

<link rel="stylesheet" href="<?= asset('assets/css/fish.css') ?>?v=<?= time() ?>">

<!-- Breadcrumb -->
<div class="fd-breadcrumb">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="<?= url() ?>"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></a>
                </li>
                <li class="breadcrumb-item"><a href="<?= url('fish/') ?>">তথ্যভান্ডার</a></li>
                <li class="breadcrumb-item"><a href="<?= url('fish/') ?>">মাছের পরিচিতি</a></li>
                <li class="breadcrumb-item active"><?= e($fish['name']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- Hero Section -->
<div class="fd-hero">
    <div class="container fd-hero-container">
        <div class="row align-items-center h-100 position-relative z-1">
            <div class="col-lg-8">
                <div class="fd-hero-badge">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    <?= e($water_type) ?>
                </div>
                
                <h1 class="fd-hero-title"><?= e($fish['name']) ?></h1>
                
                <div class="fd-hero-sci">
                    (<?= e($sci_name) ?>)
                </div>
                
                <div class="fd-hero-sci-badge">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                    বৈজ্ঞানিক নাম: <?= e($sci_name) ?>
                </div>
                
                <p class="fd-hero-desc">
                    <?= e(mb_strimwidth(strip_tags($desc), 0, 160, '...')) ?>
                </p>
            </div>
            <div class="col-lg-4 fd-hero-img-col">
                <div class="fd-hero-img-wrapper">
                    <img src="<?= uploaded_image_url('fish', $fish['image']) ?>" alt="<?= e($fish['name']) ?>" class="fd-hero-img">
                    <button class="fd-zoom-btn">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                        বড় ছবি দেখুন
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-4 pb-5">
    <div class="row g-4">
        <!-- Left: Main Content (Tabs) -->
        <div class="col-lg-8">
            <ul class="nav nav-pills fd-tabs mb-4" id="fishTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="intro-tab" data-bs-toggle="pill" data-bs-target="#intro" type="button" role="tab" aria-controls="intro" aria-selected="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        পরিচিতি
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="feature-tab" data-bs-toggle="pill" data-bs-target="#feature" type="button" role="tab" aria-controls="feature" aria-selected="false">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                        বৈশিষ্ট্য
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="habitat-tab" data-bs-toggle="pill" data-bs-target="#habitat" type="button" role="tab" aria-controls="habitat" aria-selected="false">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2.69l5.66 4.12A8 8 0 1 1 6.34 6.81L12 2.69z"/></svg>
                        বাসস্থান ও আবাসন
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="food-tab" data-bs-toggle="pill" data-bs-target="#food" type="button" role="tab" aria-controls="food" aria-selected="false">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
                        খাদ্য
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="farming-tab" data-bs-toggle="pill" data-bs-target="#farming" type="button" role="tab" aria-controls="farming" aria-selected="false">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/></svg>
                        চাষ পদ্ধতি
                    </button>
                </li>
            </ul>

            <div class="tab-content fd-tab-content">
                <!-- Intro Tab -->
                <div class="tab-pane fade show active" id="intro" role="tabpanel" aria-labelledby="intro-tab">
                    
                    <div class="fd-section-title">
                        <div class="fd-st-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <h2>মূল পরিচিতি</h2>
                    </div>
                    
                    <p class="fd-text">
                        <?= nl2br(e($desc)) ?>
                    </p>
                    
                    <!-- Quick Facts Grid -->
                    <div class="row g-3 my-4">
                        <div class="col-6 col-md-3">
                            <div class="fd-qf-card">
                                <div class="fd-qf-icon">
                                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                </div>
                                <div class="fd-qf-label">বৈজ্ঞানিক নাম</div>
                                <div class="fd-qf-val"><?= e($sci_name) ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="fd-qf-card">
                                <div class="fd-qf-icon">
                                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="7.5 4.21 12 6.81 16.5 4.21"/><polyline points="7.5 19.79 7.5 14.6 3 12"/><polyline points="21 12 16.5 14.6 16.5 19.79"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                                </div>
                                <div class="fd-qf-label">সাধারণ আকার</div>
                                <div class="fd-qf-val"><?= e($size) ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="fd-qf-card">
                                <div class="fd-qf-icon">
                                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                </div>
                                <div class="fd-qf-label">গড় ওজন</div>
                                <div class="fd-qf-val"><?= e($weight) ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="fd-qf-card">
                                <div class="fd-qf-icon">
                                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
                                </div>
                                <div class="fd-qf-label">খাদ্যাভ্যাস</div>
                                <div class="fd-qf-val"><?= e($food) ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Features List -->
                    <div class="fd-section-title mt-5">
                        <div class="fd-st-icon-star">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        </div>
                        <h2>প্রধান বৈশিষ্ট্য</h2>
                    </div>
                    
                    <ul class="fd-feature-list">
                        <li>
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                            দ্রুত বৃদ্ধি পায় এবং কম সময়ে বাজারজাত করা যায়।
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                            পরিবেশের সাথে মানিয়ে নিতে সক্ষম।
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                            রোগ প্রতিরোধ ক্ষমতা তুলনামূলকভাবে বেশি।
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                            মাংস সুস্বাদু এবং পুষ্টিগুণে ভরপুর।
                        </li>
                    </ul>

                </div>
                
                <!-- Features Tab -->
                <div class="tab-pane fade" id="feature" role="tabpanel" aria-labelledby="feature-tab">
                    <h3 class="fw-bold mb-4">শারীরিক বৈশিষ্ট্য</h3>
                    <p class="fd-text text-muted">এই ট্যাবের ডেটা ডাটাবেস থেকে আসতে পারে। আপাতত এটি কেবল একটি প্লেসহোল্ডার।</p>
                </div>
                <!-- Habitat Tab -->
                <div class="tab-pane fade" id="habitat" role="tabpanel" aria-labelledby="habitat-tab">
                    <h3 class="fw-bold mb-4">বাসস্থান ও আবাসন</h3>
                    <p class="fd-text text-muted">
                        <?= !empty($fish['habitat']) ? nl2br(e($fish['habitat'])) : 'কোনো তথ্য উপলব্ধ নেই।' ?>
                    </p>
                </div>
                <!-- Food Tab -->
                <div class="tab-pane fade" id="food" role="tabpanel" aria-labelledby="food-tab">
                    <h3 class="fw-bold mb-4">খাদ্য</h3>
                    <p class="fd-text text-muted">খাদ্যাভ্যাস এবং তালিকা।</p>
                </div>
                <!-- Farming Tab -->
                <div class="tab-pane fade" id="farming" role="tabpanel" aria-labelledby="farming-tab">
                    <h3 class="fw-bold mb-4">চাষ পদ্ধতি</h3>
                    <p class="fd-text text-muted">
                        <?= !empty($fish['farming_tips']) ? nl2br(e($fish['farming_tips'])) : 'কোনো তথ্য উপলব্ধ নেই।' ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right: Sidebar -->
        <div class="col-lg-4">
            <!-- AI Card -->
            <div class="fd-ai-card">
                <div class="fd-ai-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><circle cx="12" cy="12" r="3" fill="#000"/></svg>
                </div>
                <h4>এই মাছের স্বাস্থ্য পরীক্ষা চান?</h4>
                <p>আমাদের আধুনিক AI দিয়ে ছবি আপলোড করে নিমেষেই মাছের রোগ ও সমাধান নির্ণয় করুন।</p>
                <a href="<?= url('ai/') ?>" class="fd-ai-btn">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" stroke="none"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                    AI রোগ নির্ণয় শুরু করুন <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>

            <!-- Related Info Card -->
            <div class="fd-sidebar-card">
                <div class="fd-sc-header">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" class="text-primary"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10" stroke="#fff" stroke-width="2" fill="none"/></svg>
                    সম্পর্কিত তথ্য
                </div>
                <div class="fd-sc-list">
                    <a href="#" class="fd-sc-item">
                        <div class="fd-sc-icon ic-green"><svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></div>
                        <div class="fd-sc-text">
                            <h6>খাদ্য তালিকা</h6>
                            <span>প্রাকৃতিক ও কৃত্রিম খাদ্য</span>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                    <a href="#" class="fd-sc-item">
                        <div class="fd-sc-icon ic-teal"><svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></div>
                        <div class="fd-sc-text">
                            <h6>চাষ পদ্ধতি</h6>
                            <span>পুকুরে ও ট্যাংকে চাষ</span>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                    <a href="#" class="fd-sc-item">
                        <div class="fd-sc-icon ic-green2"><svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></div>
                        <div class="fd-sc-text">
                            <h6>রোগ ব্যবস্থাপনা</h6>
                            <span>সাধারণ রোগ ও প্রতিকার</span>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                    <a href="#" class="fd-sc-item">
                        <div class="fd-sc-icon ic-green3"><svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></div>
                        <div class="fd-sc-text">
                            <h6>বাজার মূল্য</h6>
                            <span>বর্তমান বাজার দর ও সম্ভাবনা</span>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                </div>
            </div>

            <!-- Special Tip Card -->
            <div class="fd-tip-card">
                <div class="fd-tip-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M12 2a7 7 0 0 0-7 7c0 2.38 1.19 4.47 3 5.74V17a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-2.26c1.81-1.27 3-3.36 3-5.74a7 7 0 0 0-7-7zm-1 19a1 1 0 0 0 2 0v-1h-2v1z"/></svg>
                </div>
                <div>
                    <h5>বিশেষ পরামর্শ</h5>
                    <p><?= e($fish['name']) ?> চাষে পানির মান নিয়ন্ত্রণ এবং সুষম খাদ্য ব্যবহার করলে উৎপাদন বেশি পাওয়া যায়।</p>
                </div>
            </div>

            <!-- Expert Advice Card -->
            <div class="fd-expert-card">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="fd-expert-icon">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15.05 5A5 5 0 0 1 19 8.95M15.05 1A9 9 0 0 1 23 8.94m-1 7.98v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </div>
                    <div>
                        <h5>বিশেষজ্ঞের পরামর্শ নিন</h5>
                        <p>কোনো প্রশ্ন থাকলে আমাদের বিশেষজ্ঞদের সাথে সরাসরি কথা বলুন।</p>
                    </div>
                </div>
                <a href="tel:01979606212" class="fd-expert-btn">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" stroke="none"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    01979-606212 <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>