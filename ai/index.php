<?php
// ============================================================
// FarmersBD — AI Disease Detection (Upload Page)
// Exact Match to Premium Reference UI
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/flash.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/seo.php';

$page_seo = [
    'title' => 'AI দিয়ে মাছের রোগ নির্ণয় | ' . setting('site_name', 'FarmersBD'),
    'description' => 'আপনার মাছের ছবি আপলোড করুন এবং আমাদের অত্যাধুনিক AI-এর সাহায্যে নিমেষেই রোগ নির্ণয় করুন।'
];

$extra_css = '<link rel="stylesheet" href="' . asset('assets/css/ai.css') . '">';
$extra_js  = '<script src="' . asset('assets/js/ai.js') . '"></script>';

include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/navbar.php';
?>

<!-- ── Hero Banner Section ────────────────────────────────────── -->
<div class="ai-hero-banner">
    <div class="container position-relative z-1">
        <div class="row align-items-center g-4">
            
            <!-- Left Hero Content -->
            <div class="col-lg-6">
                <!-- New Feature Pill -->
                <div class="ai-feature-pill">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m19 11-8-8-8.5 8.5a2.12 2.12 0 0 0 0 3l5 5a2.12 2.12 0 0 0 3 0L19 11Z"/>
                        <path d="m5 2 1 2 2 1-2 1-1 2-1-2-2-1 2-1 1-2Z"/>
                        <path d="M20 18l.5 1 1 .5-1 .5-.5 1-.5-1-1-.5 1-.5.5-1Z"/>
                    </svg>
                    <span>নতুন ফিচার</span>
                </div>

                <!-- Main Title with Chip Icon -->
                <h1 class="ai-hero-title">
                    <span class="ai-chip-logo-box">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="16" height="16" x="4" y="4" rx="3"/>
                            <rect width="6" height="6" x="9" y="9" rx="1"/>
                            <path d="M9 1v3"/><path d="M15 1v3"/><path d="M9 20v3"/><path d="M15 20v3"/>
                            <path d="M20 9h3"/><path d="M20 14h3"/><path d="M1 9h3"/><path d="M1 14h3"/>
                        </svg>
                    </span>
                    <span>AI দিয়ে মাছের রোগ নির্ণয়</span>
                </h1>

                <!-- Subtitle -->
                <p class="ai-hero-subtitle">
                    আপনার মাছের ছবি আপলোড করুন এবং আমাদের অত্যাধুনিক AI-এর সাহায্যে মিনিটেই রোগ নির্ণয় করুন।
                </p>

                <!-- 3 Highlights Row -->
                <div class="ai-hero-features-row">
                    <!-- Feat 1 -->
                    <div class="ai-hero-feat-item">
                        <div class="ai-feat-icon-circle">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </div>
                        <div>
                            <div class="ai-feat-title">দ্রুত ফলাফল</div>
                            <div class="ai-feat-sub">কয়েক সেকেন্ডে রিপোর্ট</div>
                        </div>
                    </div>

                    <!-- Feat 2 -->
                    <div class="ai-hero-feat-item">
                        <div class="ai-feat-icon-circle">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/>
                                <path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 9.17 4.24-4.24"/>
                                <path d="m14.83 14.83 4.24 4.24"/><path d="m9.17 14.83-4.24 4.24"/>
                            </svg>
                        </div>
                        <div>
                            <div class="ai-feat-title">সঠিক বিশ্লেষণ</div>
                            <div class="ai-feat-sub">AI প্রযুক্তির মাধ্যমে</div>
                        </div>
                    </div>

                    <!-- Feat 3 -->
                    <div class="ai-hero-feat-item">
                        <div class="ai-feat-icon-circle">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>
                            </svg>
                        </div>
                        <div>
                            <div class="ai-feat-title">বিশ্বস্ত পরামর্শ</div>
                            <div class="ai-feat-sub">বিশেষজ্ঞদের সুপারিশ</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Hero Visuals: Scanner Box & Disease Tags -->
            <div class="col-lg-6">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-center justify-content-lg-end gap-4">
                    <!-- Futuristic Fish Scanner Box -->
                    <div class="ai-hud-scanner-wrapper">
                        <!-- AI Badge Attached to HUD Box -->
                        <div class="ai-hud-badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="16" height="16" x="4" y="4" rx="2"/>
                                <path d="M9 1v3"/><path d="M15 1v3"/><path d="M9 20v3"/><path d="M15 20v3"/>
                                <path d="M20 9h3"/><path d="M20 14h3"/><path d="M1 9h3"/><path d="M1 14h3"/>
                            </svg>
                            <span>AI</span>
                        </div>

                        <!-- Scanner HUD Container -->
                        <div class="ai-hud-box">
                            <div class="ai-hud-corner-tl"></div>
                            <div class="ai-hud-corner-tr"></div>
                            <div class="ai-hud-corner-bl"></div>
                            <div class="ai-hud-corner-br"></div>
                            <img src="<?= asset('assets/images/general/ai_scanner_fish.png') ?>" alt="AI মাছ বিশ্লেষণ" class="ai-hud-fish-img">
                        </div>
                    </div>

                    <!-- 4 Diagnosis Tag Pills -->
                    <div class="ai-diagnosis-tags-column">
                        <div class="ai-tag-pill">
                            <span class="ai-tag-check-circle">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </span>
                            <span>ব্যাকটেরিয়াল সংক্রমণ</span>
                        </div>

                        <div class="ai-tag-pill">
                            <span class="ai-tag-check-circle">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </span>
                            <span>ফাঙ্গাল ইনফেকশন</span>
                        </div>

                        <div class="ai-tag-pill">
                            <span class="ai-tag-check-circle">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </span>
                            <span>পরজীবী সংক্রমণ</span>
                        </div>

                        <div class="ai-tag-pill">
                            <span class="ai-tag-check-circle">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </span>
                            <span>পানির গুণগত মান সমস্যা</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ── Main Form & How it Works Section ───────────────────────── -->
<div class="ai-page-wrap py-5">
    <div class="container pb-4">
        <div class="row g-4">
            
            <!-- Left Column: Upload Card -->
            <div class="col-lg-7">
                <div class="ai-main-card">
                    
                    <!-- Card Header -->
                    <div class="ai-card-header">
                        <div class="ai-header-icon-circle">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/>
                                <path d="M12 12v9"/>
                                <path d="m16 16-4-4-4 4"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="ai-card-title">ছবি আপলোড করুন</h2>
                            <p class="ai-card-subtitle">আপনার মাছের স্পষ্ট ছবি আপলোড করুন। JPG, PNG বা WEBP ফরম্যাটে এবং সর্বোচ্চ ৫০ MB সাইজ পর্যন্ত।</p>
                        </div>
                    </div>

                    <!-- Upload Form -->
                    <form id="ai-upload-form" action="<?= url('ai/predict.php') ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <!-- Drag and Drop Zone -->
                        <div id="ai-drop-zone" class="ai-drop-zone-box" role="button" tabindex="0" aria-label="ছবি আপলোড জোন">
                            <div class="ai-upload-icon-circle">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="17 8 12 3 7 8"/>
                                    <line x1="12" x2="12" y1="3" y2="15"/>
                                </svg>
                            </div>
                            <div class="ai-drop-title">এখানে ছবি ড্র্যাগ করে ফেলুন</div>
                            <div class="ai-drop-or">অথবা</div>
                            <button type="button" class="ai-select-file-btn" onclick="document.getElementById('ai-file-input').click();">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="18" x="3" y="3" rx="2"/>
                                    <circle cx="9" cy="9" r="2"/>
                                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                </svg>
                                <span>ফাইল নির্বাচন করুন</span>
                            </button>
                        </div>

                        <!-- Hidden Native File Input -->
                        <input type="file" id="ai-file-input" name="image"
                               accept="image/jpeg,image/png,image/webp"
                               class="d-none" aria-label="ছবি নির্বাচন">

                        <!-- Preview Area -->
                        <div id="ai-preview-box" class="d-none mt-4 text-center">
                            <div class="position-relative d-inline-block shadow-sm rounded-4 overflow-hidden mb-4" style="border: 1px solid rgba(0,0,0,0.1);">
                                <img id="ai-preview" src="" alt="আপলোড প্রিভিউ" class="img-fluid" style="max-height:300px; object-fit: contain; background: #f8fafc;">
                            </div>
                            <div class="d-flex gap-3 justify-content-center">
                                <button type="button" id="ai-clear-btn" class="btn btn-light border px-4 py-2 rounded-pill fw-semibold shadow-sm">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>পরিবর্তন করুন
                                </button>
                                <button type="submit" id="ai-submit-btn" class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow-sm" disabled>
                                    <i class="bi bi-cpu-fill me-2"></i>রোগ নির্ণয় শুরু করুন
                                </button>
                            </div>
                        </div>

                    </form>

                    <!-- Bottom 3-Segment Information Bar -->
                    <div class="ai-upload-info-bar">
                        <!-- Segment 1: Tips -->
                        <div class="ai-info-segment" style="flex: 1.6;">
                            <div class="ai-info-bulb-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/>
                                    <path d="M9 18h6"/><path d="M10 22h4"/>
                                </svg>
                            </div>
                            <div class="ai-info-tips-text">
                                <strong>টিপস:</strong> ভালো ফলাফলের জন্য মাছের মুখ, পাখনা এবং আক্রান্ত অংশের স্পষ্ট ছবি আপলোড করুন।
                            </div>
                        </div>

                        <div class="ai-info-divider"></div>

                        <!-- Segment 2: Format -->
                        <div class="ai-info-segment">
                            <div class="ai-info-meta-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/>
                                    <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                                </svg>
                            </div>
                            <div>
                                <div class="ai-info-meta-label">সমর্থিত ফরম্যাট:</div>
                                <div class="ai-info-meta-val">JPG, PNG, WEBP</div>
                            </div>
                        </div>

                        <div class="ai-info-divider"></div>

                        <!-- Segment 3: Max Size -->
                        <div class="ai-info-segment">
                            <div class="ai-info-meta-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="18" x="3" y="3" rx="2"/>
                                    <path d="M8 12h8"/><path d="M12 8v8"/>
                                </svg>
                            </div>
                            <div>
                                <div class="ai-info-meta-label">সর্বোচ্চ সাইজ:</div>
                                <div class="ai-info-meta-val">৫০ MB</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Right Column: How It Works -->
            <div class="col-lg-5">
                <div class="ai-main-card">
                    
                    <!-- Card Header -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="ai-sparkle-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="1">
                                    <path d="M12 2l2.4 6.6L21 11l-6.6 2.4L12 20l-2.4-6.6L3 11l6.6-2.4L12 2z"/>
                                </svg>
                            </span>
                            <h2 class="ai-card-title mb-0">কিভাবে কাজ করে?</h2>
                        </div>
                        <p class="ai-card-subtitle">মাত্র কয়েকটি ধাপে আপনার মাছের রোগ নির্ণয় করুন</p>
                    </div>

                    <!-- 4 Steps List -->
                    <div class="ai-step-rows-wrap">
                        
                        <!-- Step 1 -->
                        <div class="ai-step-row ai-step-row-1">
                            <div class="ai-step-left">
                                <div class="ai-step-icon-box ai-step-box-1">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/>
                                        <circle cx="12" cy="13" r="3"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="ai-step-title">১. ছবি তুলুন</div>
                                    <p class="ai-step-desc">আক্রান্ত মাছের একটি পরিষ্কার ও স্পষ্ট ছবি তুলুন।</p>
                                </div>
                            </div>
                            <div class="ai-step-chevron ai-chevron-1">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="ai-step-row ai-step-row-2">
                            <div class="ai-step-left">
                                <div class="ai-step-icon-box ai-step-box-2">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/>
                                        <path d="M12 12v9"/><path d="m16 16-4-4-4 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="ai-step-title">২. আপলোড করুন</div>
                                    <p class="ai-step-desc">ছবিটি এখানে আপলোড করুন এবং বিশ্লেষণ শুরু হবে।</p>
                                </div>
                            </div>
                            <div class="ai-step-chevron ai-chevron-2">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="ai-step-row ai-step-row-3">
                            <div class="ai-step-left">
                                <div class="ai-step-icon-box ai-step-box-3">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#9333ea" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="16" height="16" x="4" y="4" rx="2"/>
                                        <rect width="6" height="6" x="9" y="9" rx="1"/>
                                        <path d="M9 1v3"/><path d="M15 1v3"/><path d="M9 20v3"/><path d="M15 20v3"/>
                                        <path d="M20 9h3"/><path d="M20 14h3"/><path d="M1 9h3"/><path d="M1 14h3"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="ai-step-title">৩. AI বিশ্লেষণ</div>
                                    <p class="ai-step-desc">আমাদের AI মডেল ছবিটি বিশ্লেষণ করে সম্ভাব্য রোগ নির্ণয় করবে।</p>
                                </div>
                            </div>
                            <div class="ai-step-chevron ai-chevron-3">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="ai-step-row ai-step-row-4">
                            <div class="ai-step-left">
                                <div class="ai-step-icon-box ai-step-box-4">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/>
                                        <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                                        <path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="ai-step-title">৪. ফলাফল ও পরামর্শ</div>
                                    <p class="ai-step-desc">রোগের নাম, লক্ষণ ও বিস্তারিত চিকিৎসা পরামর্শ পাবেন।</p>
                                </div>
                            </div>
                            <div class="ai-step-chevron ai-chevron-4">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"/>
                                </svg>
                            </div>
                        </div>

                    </div>

                    <!-- Bottom Caution Alert -->
                    <div class="ai-caution-banner">
                        <div class="ai-caution-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                        </div>
                        <div class="ai-caution-text">
                            <strong>গুরুত্বপূর্ণ:</strong> এটি একটি প্রাথমিক সহায়তা ব্যবস্থা। চূড়ান্ত চিকিৎসার জন্য বিশেষজ্ঞের পরামর্শ নেওয়া উত্তম।
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
