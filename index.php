<?php
// ============================================================
// FarmersBD — Homepage (index.php)
// Database-driven. All content from CMS.
// ============================================================
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/seo.php';

$page_seo = [
    'title'       => setting('meta_title',       'FarmersBD - স্মার্ট মাছ চাষের আধুনিক সমাধান'),
    'description' => setting('meta_description', ''),
    'keywords'    => setting('meta_keywords',    ''),
];

// Fetch data for all homepage sections
$heroSlides = db_query("SELECT * FROM hero_slides WHERE is_active = 1 ORDER BY sort_order ASC");
$popularFish = db_query("SELECT * FROM fish WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 8");
$featuredProducts = db_query(
    "SELECT p.*, c.name AS category_name FROM products p 
     LEFT JOIN product_categories c ON c.id = p.category_id
     WHERE p.is_featured = 1 AND p.is_active = 1 ORDER BY p.created_at DESC LIMIT 8"
);
$recentBlogs = db_query(
    "SELECT b.*, bc.name AS category_name FROM blogs b
     LEFT JOIN blog_categories bc ON bc.id = b.category_id
     WHERE b.is_active = 1 ORDER BY b.created_at DESC LIMIT 3"
);
$faqs = db_query("SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 8");

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<link rel="stylesheet" href="<?= asset('assets/css/fish.css') ?>?v=<?= time() ?>">
<link rel="stylesheet" href="<?= asset('assets/css/products.css') ?>?v=<?= time() ?>">


<!-- ── HERO SLIDER ────────────────────────────────────────── -->
<?php if ($heroSlides): ?>
<section id="hero" aria-label="মুখ্য স্লাইডার">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="3500" data-bs-pause="false">
        <!-- Indicators -->
        <?php if (count($heroSlides) > 1): ?>
        <div class="carousel-indicators">
            <?php foreach ($heroSlides as $i => $slide): ?>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $i ?>"
                    <?= $i === 0 ? 'class="active" aria-current="true"' : '' ?>
                    aria-label="স্লাইড <?= $i + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="carousel-inner">
            <?php foreach ($heroSlides as $i => $slide): ?>
            <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                <?php
                $imgPath = '';
                $imageExists = false;
                if (!empty($slide['image'])) {
                    if (str_starts_with($slide['image'], 'assets/')) {
                        $fullLocalPath = __DIR__ . '/' . $slide['image'];
                        if (file_exists($fullLocalPath)) {
                            $imgPath = asset($slide['image']);
                            $imageExists = true;
                        }
                    } else {
                        $fullLocalPath = UPLOAD_BASE_DIR . 'hero/' . $slide['image'];
                        if (file_exists($fullLocalPath)) {
                            $imgPath = UPLOAD_BASE_URL . 'hero/' . e($slide['image']);
                            $imageExists = true;
                        }
                    }
                }
                ?>
                <?php if ($imageExists): ?>
                <img src="<?= $imgPath ?>" alt="<?= e($slide['title'] ?? 'হিরো স্লাইড') ?>" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>">
                <?php endif; ?>
                <div class="hero-overlay"></div>
                <div class="hero-content container">
                    <div class="hero-text">
                        <?php if ($i === 0): ?>
                        <h1 class="hero-main-title">
                            <span class="title-line-1 d-block">বাংলাদেশের স্মার্ট</span>
                            <span class="title-line-2 d-block">ফিশ ফার্মিং প্ল্যাটফর্ম</span>
                        </h1>
                        <?php else: ?>
                        <div class="hero-main-title h1" role="heading" aria-level="2">
                            <span class="title-line-1 d-block">বাংলাদেশের স্মার্ট</span>
                            <span class="title-line-2 d-block">ফিশ ফার্মিং প্ল্যাটফর্ম</span>
                        </div>
                        <?php endif; ?>
                        <p class="hero-main-subtitle">
                            মাছের রোগ শনাক্ত করুন, বিশেষজ্ঞদের পরামর্শ নিন এবং অনলাইনে ওষুধ অর্ডার করুন।
                        </p>
                        <div class="hero-btn-group mt-3">
                            <a href="<?= url('ai/') ?>" class="btn btn-hero-teal">
                                <i class="bi bi-camera-fill me-2"></i>রোগ শনাক্ত করুন
                            </a>
                            <a href="<?= url('products/') ?>" class="btn btn-hero-blue">
                                <i class="bi bi-bag-fill me-2"></i>ওষুধ কিনুন
                            </a>
                        </div>
                        
                        <!-- Hero Stats Row (Matching Image) -->
                        <div class="hero-stats-image-row mt-4 pt-3">
                            <div class="hero-stat-image-item">
                                <div class="stat-img-num">২৫,০০০+</div>
                                <div class="stat-img-lbl">খামারি যুক্ত</div>
                            </div>
                            <div class="hero-stat-image-item ps-4">
                                <div class="stat-img-num">৯৮.৪%</div>
                                <div class="stat-img-lbl">সঠিক রোগ নির্ণয়</div>
                            </div>
                            <div class="hero-stat-image-item ps-4">
                                <div class="stat-img-num">৬৪ জেলা</div>
                                <div class="stat-img-lbl">ডেলিভারি সেবা</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php else: ?>
<!-- Fallback hero if no slides configured -->
<section class="bg-primary-gradient text-white py-5 text-center">
    <div class="container py-4">
        <h1 class="text-white fw-bold">মাছ চাষে আধুনিক প্রযুক্তির ব্যবহার</h1>
        <p class="fs-5 opacity-75">সঠিক রোগ নির্ণয় ও ব্যবস্থাপনার মাধ্যমে আপনার মাছের উৎপাদন বাড়ান।</p>
        <a href="<?= url('ai/') ?>" class="btn btn-warning btn-lg fw-bold">AI দিয়ে রোগ নির্ণয় করুন</a>
    </div>
</section>
<?php endif; ?>

<!-- ── SERVICES ───────────────────────────────────────────── -->
<section id="services" class="section-padding bg-primary-subtle">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title services-main-title"><?= hp('services', 'title', 'আমাদের সেবাসমূহ') ?></h2>
            <div class="section-divider"></div>
            <p class="section-subtitle"><?= hp('services', 'subtitle', 'মাছ চাষকে আরও সহজ ও লাভজনক করতে আমাদের সকল সেবা') ?></p>
        </div>
        <div class="row g-4">
            <?php
            $services = [
                [
                    'id' => 'ai-disease',
                    'color' => 'coral',
                    'title' => 'মাছের রোগ নির্ণয়',
                    'desc' => 'AI প্রযুক্তি ব্যবহার করে দ্রুত ও নির্ভুল মাছের রোগ নির্ণয় করুন।',
                    'url' => url('ai/'),
                    'badge' => '<span class="service-badge badge-coral"><i class="bi bi-cpu-fill me-1"></i>AI চালিত</span>',
                    'svg' => '<svg width="48" height="48" viewBox="0 0 48 48" fill="none" class="srv-svg-icon">
                        <defs>
                            <linearGradient id="srv-cam-bg" x1="0" y1="0" x2="48" y2="48">
                                <stop offset="0%" stop-color="#ff6b81"/>
                                <stop offset="100%" stop-color="#e11d48"/>
                            </linearGradient>
                            <linearGradient id="srv-cam-lens" x1="16" y1="16" x2="32" y2="32">
                                <stop offset="0%" stop-color="#ffffff"/>
                                <stop offset="100%" stop-color="#e2e8f0"/>
                            </linearGradient>
                            <linearGradient id="srv-cam-inner" x1="18" y1="18" x2="30" y2="30">
                                <stop offset="0%" stop-color="#0284c7"/>
                                <stop offset="100%" stop-color="#0f172a"/>
                            </linearGradient>
                        </defs>
                        <rect width="48" height="48" rx="14" fill="url(#srv-cam-bg)"/>
                        <path d="M19 13L21 10H27L29 13H19Z" fill="#ffffff" opacity="0.9"/>
                        <rect x="8" y="13" width="32" height="24" rx="5" fill="#ffffff"/>
                        <circle cx="24" cy="25" r="9" fill="url(#srv-cam-lens)" stroke="#cbd5e1" stroke-width="1.5"/>
                        <circle cx="24" cy="25" r="6" fill="url(#srv-cam-inner)"/>
                        <circle cx="22" cy="23" r="2" fill="#38bdf8" opacity="0.85"/>
                        <circle cx="34" cy="17" r="1.5" fill="#fbbf24"/>
                    </svg>'
                ],
                [
                    'id' => 'aqua-med',
                    'color' => 'cyan',
                    'title' => 'অ্যাকোয়া ওষুধ',
                    'desc' => 'বিশ্বস্ত ব্র্যান্ডের সকল মাছের ওষুধ ও স্বাস্থ্য পণ্য অর্ডার করুন।',
                    'url' => url('products/'),
                    'badge' => '<span class="service-badge badge-cyan"><i class="bi bi-shield-check me-1"></i>বিশ্বস্ত পণ্য</span>',
                    'svg' => '<svg width="48" height="48" viewBox="0 0 48 48" fill="none" class="srv-svg-icon">
                        <defs>
                            <linearGradient id="srv-med-bg" x1="0" y1="0" x2="48" y2="48"><stop offset="0%" stop-color="#06b6d4"/><stop offset="100%" stop-color="#0d9488"/></linearGradient>
                            <linearGradient id="srv-med-capsule1" x1="16" y1="12" x2="32" y2="28"><stop offset="0%" stop-color="#ffffff"/><stop offset="100%" stop-color="#cff4fc"/></linearGradient>
                            <linearGradient id="srv-med-capsule2" x1="16" y1="20" x2="32" y2="36"><stop offset="0%" stop-color="#fbbf24"/><stop offset="100%" stop-color="#f59e0b"/></linearGradient>
                        </defs>
                        <rect width="48" height="48" rx="14" fill="url(#srv-med-bg)"/>
                        <g transform="rotate(-30 24 24)">
                            <rect x="18" y="10" width="12" height="14" rx="6" fill="url(#srv-med-capsule1)"/>
                            <rect x="18" y="24" width="12" height="14" rx="6" fill="url(#srv-med-capsule2)"/>
                            <line x1="18" y1="24" x2="30" y2="24" stroke="#0f172a" stroke-width="1.2" opacity="0.3"/>
                        </g>
                    </svg>'
                ],
                [
                    'id' => 'expert-consult',
                    'color' => 'purple',
                    'title' => 'বিশেষজ্ঞ পরামর্শ',
                    'desc' => 'অভিজ্ঞ মৎস্য বিশেষজ্ঞ দলের সাথে সরাসরি পরামর্শ করুন।',
                    'url' => url('consultation/'),
                    'badge' => '<span class="service-badge badge-purple"><i class="bi bi-headset me-1"></i>সরাসরি কল</span>',
                    'svg' => '<svg width="48" height="48" viewBox="0 0 48 48" fill="none" class="srv-svg-icon">
                        <defs>
                            <linearGradient id="srv-exp-bg" x1="0" y1="0" x2="48" y2="48"><stop offset="0%" stop-color="#8b5cf6"/><stop offset="100%" stop-color="#6d28d9"/></linearGradient>
                            <linearGradient id="srv-exp-bubble" x1="10" y1="10" x2="38" y2="34"><stop offset="0%" stop-color="#ffffff"/><stop offset="100%" stop-color="#f3e8ff"/></linearGradient>
                        </defs>
                        <rect width="48" height="48" rx="14" fill="url(#srv-exp-bg)"/>
                        <path d="M38 12H10C7.8 12 6 13.8 6 16V28C6 30.2 7.8 32 10 32H30L38 38V16C38 13.8 36.2 12 34 12Z" fill="url(#srv-exp-bubble)"/>
                        <path d="M16 20C16 17.8 19.6 16 24 16C28.4 16 32 17.8 32 20V24C32 24.55 31.55 25 31 25H29V19.5H32V20C32 18.5 28.4 17.2 24 17.2C19.6 17.2 16 18.5 16 20V19.5H19V25H17C16.45 25 16 24.55 16 24V20Z" fill="#6d28d9"/>
                        <circle cx="29.5" cy="22.5" r="2" fill="#fbbf24"/>
                    </svg>'
                ],
                [
                    'id' => 'online-shop',
                    'color' => 'amber',
                    'title' => 'অনলাইন শপ',
                    'desc' => 'দেশের যেকোনো প্রান্ত থেকে সহজে অ্যাকোয়া পণ্য কিনুন।',
                    'url' => url('products/'),
                    'badge' => '<span class="service-badge badge-amber"><i class="bi bi-truck me-1"></i>দ্রুত ডেলিভারি</span>',
                    'svg' => '<svg width="48" height="48" viewBox="0 0 48 48" fill="none" class="srv-svg-icon">
                        <defs>
                            <linearGradient id="srv-shop-bg" x1="0" y1="0" x2="48" y2="48"><stop offset="0%" stop-color="#f59e0b"/><stop offset="100%" stop-color="#d97706"/></linearGradient>
                            <linearGradient id="srv-shop-roof" x1="8" y1="12" x2="40" y2="22"><stop offset="0%" stop-color="#ffffff"/><stop offset="100%" stop-color="#fef3c7"/></linearGradient>
                        </defs>
                        <rect width="48" height="48" rx="14" fill="url(#srv-shop-bg)"/>
                        <path d="M10 22V36C10 37.1 10.9 38 12 38H36C37.1 38 38 37.1 38 36V22H10Z" fill="url(#srv-shop-roof)"/>
                        <path d="M6 14L10 22H38L42 14H6Z" fill="#ffffff"/>
                        <rect x="20" y="28" width="8" height="10" rx="1" fill="#92400e"/>
                        <rect x="13" y="26" width="5" height="5" rx="1" fill="#0284c7"/>
                        <rect x="30" y="26" width="5" height="5" rx="1" fill="#0284c7"/>
                    </svg>'
                ],
                [
                    'id' => 'fish-training',
                    'color' => 'sky',
                    'title' => 'মাছ চাষের প্রশিক্ষণ',
                    'desc' => 'আধুনিক মাছ চাষ পদ্ধতি ও কৌশল সম্পর্কে জানুন।',
                    'url' => url('blog/'),
                    'badge' => '<span class="service-badge badge-sky"><i class="bi bi-book-half me-1"></i>ফ্রি কোর্স</span>',
                    'svg' => '<svg width="48" height="48" viewBox="0 0 48 48" fill="none" class="srv-svg-icon">
                        <defs>
                            <linearGradient id="srv-edu-bg" x1="0" y1="0" x2="48" y2="48"><stop offset="0%" stop-color="#0284c7"/><stop offset="100%" stop-color="#0369a1"/></linearGradient>
                            <linearGradient id="srv-edu-cap" x1="8" y1="12" x2="40" y2="32"><stop offset="0%" stop-color="#ffffff"/><stop offset="100%" stop-color="#e0f2fe"/></linearGradient>
                        </defs>
                        <rect width="48" height="48" rx="14" fill="url(#srv-edu-bg)"/>
                        <path d="M24 10L6 18L24 26L42 18L24 10Z" fill="url(#srv-edu-cap)"/>
                        <path d="M14 22.5V30C14 33.3 18.5 36 24 36C29.5 36 34 33.3 34 30V22.5L24 27L14 22.5Z" fill="#bae6fd"/>
                        <path d="M38 19.8V31.5" stroke="#fbbf24" stroke-width="2.5" stroke-linecap="round"/>
                        <circle cx="38" cy="33" r="2" fill="#f59e0b"/>
                    </svg>'
                ],
                [
                    'id' => 'yield-growth',
                    'color' => 'blue',
                    'title' => 'উৎপাদন বৃদ্ধি',
                    'desc' => 'সঠিক পদ্ধতিতে মাছ চাষ করে উৎপাদন বহুগুণ বাড়ান।',
                    'url' => url('blog/'),
                    'badge' => '<span class="service-badge badge-blue"><i class="bi bi-graph-up-arrow me-1"></i>উৎপাদন বৃদ্ধি</span>',
                    'svg' => '<svg width="48" height="48" viewBox="0 0 48 48" fill="none" class="srv-svg-icon">
                        <defs>
                            <linearGradient id="srv-gro-bg" x1="0" y1="0" x2="48" y2="48"><stop offset="0%" stop-color="#3b82f6"/><stop offset="100%" stop-color="#1d4ed8"/></linearGradient>
                        </defs>
                        <rect width="48" height="48" rx="14" fill="url(#srv-gro-bg)"/>
                        <rect x="10" y="28" width="6" height="10" rx="2" fill="#ffffff" opacity="0.6"/>
                        <rect x="19" y="22" width="6" height="16" rx="2" fill="#ffffff" opacity="0.8"/>
                        <rect x="28" y="16" width="6" height="22" rx="2" fill="#ffffff"/>
                        <path d="M10 32L19 24L28 20L38 12" stroke="#fbbf24" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M30 12H38V20" stroke="#fbbf24" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>'
                ],
            ];
            foreach ($services as $s): ?>
            <div class="col-6 col-md-6 col-xl-4">
                <div class="service-card h-100 srv-card-<?= e($s['color']) ?>">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="service-icon-wrapper">
                            <?= $s['svg'] ?>
                        </div>
                        <?= $s['badge'] ?>
                    </div>
                    <div class="service-card-body">
                        <h3 class="fw-bold mb-2"><?= e($s['title']) ?></h3>
                        <p class="mb-4"><?= e($s['desc']) ?></p>
                    </div>
                    <a href="<?= e($s['url']) ?>" class="btn btn-service-action">
                        <span>আরও জানুন</span>
                        <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── POPULAR FISH ───────────────────────────────────────── -->
<?php if ($popularFish): ?>
<section id="popular-fish" class="section-padding bg-primary-subtle">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title fish-main-title"><?= hp('fish', 'title', 'জনপ্রিয় মাছ') ?></h2>
            <div class="section-divider"></div>
            <p class="section-subtitle"><?= hp('fish', 'subtitle', 'বাংলাদেশের সবচেয়ে জনপ্রিয় চাষযোগ্য মাছের তথ্য') ?></p>
        </div>
        <div class="row g-4">
            <?php foreach ($popularFish as $fish): 
                $badgeClass = '';
                $cat = $fish['category'] ?? 'দেশীয় মাছ';
                if ($cat === 'বিদেশী মাছ') $badgeClass = 'foreign';
                elseif ($cat === 'অলংকারিক মাছ') $badgeClass = 'ornamental';
                elseif ($cat === 'চিংড়ি') $badgeClass = 'prawn';
            ?>
            <div class="col-6 col-md-4 col-xl-3">
                <div class="fish-ui-card">
                    <!-- Image & Badges -->
                    <div class="fish-ui-img-wrap">
                        <span class="fish-badge-category <?= $badgeClass ?>">
                            <?= e($cat) ?>
                        </span>
                        <button type="button" class="fish-fav-btn" title="পছন্দের তালিকায় রাখুন">
                            <i class="bi bi-heart"></i>
                        </button>
                        <img src="<?= uploaded_image_url('fish', $fish['image']) ?>" class="fish-ui-img" alt="<?= e($fish['name']) ?>" loading="lazy">
                    </div>

                    <!-- Body -->
                    <div class="fish-ui-body">
                        <h3 class="fish-ui-name"><?= e($fish['name']) ?></h3>
                        <div class="fish-ui-sci"><?= !empty($fish['scientific_name']) ? '(' . e($fish['scientific_name']) . ')' : '' ?></div>
                        <p class="fish-ui-desc">
                            <?= e(mb_strimwidth(strip_tags($fish['description'] ?? 'চাষপদ্ধতি ও যত্ন নির্দেশিকা'), 0, 75, '...')) ?>
                        </p>

                        <!-- Specs -->
                        <div class="fish-ui-params">
                            <span class="fish-param-item">
                                <i class="bi bi-thermometer-half"></i>
                                <span>তাপমাত্রা: <?= e($fish['water_temp'] ?? '২০-৩০°সে') ?></span>
                            </span>
                            <span class="fish-param-item ms-auto">
                                <i class="bi bi-droplet-fill"></i>
                                <span>pH: <?= e($fish['ph_level'] ?? '৭.০-৮.৫') ?></span>
                            </span>
                        </div>

                        <!-- CTA Button -->
                        <a href="<?= url('fish/details.php?slug=' . urlencode($fish['slug'])) ?>" class="btn-fish-details">
                            <span>বিস্তারিত দেখুন</span>
                            <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="<?= url('fish/') ?>" class="btn btn-aquatic-primary btn-lg px-4">
                <span>সব মাছ দেখুন</span>
                <span class="btn-icon-circle ms-2"><i class="bi bi-arrow-right"></i></span>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── AI DISEASE DETECTION ───────────────────────────────── -->
<section id="ai-section" class="section-padding bg-primary-gradient text-white">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="badge bg-warning text-dark mb-3 px-3 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-cpu me-1"></i>AI প্রযুক্তি
                </span>
                <h2 class="text-white fw-bold mb-3"><?= hp('ai', 'title', 'AI দিয়ে মাছের রোগ নির্ণয় করুন') ?></h2>
                <p class="opacity-90 fs-5 mb-4"><?= hp('ai', 'subtitle', 'আপনার মাছের ছবি আপলোড করুন এবং কৃত্রিম বুদ্ধিমত্তার সাহায্যে তাৎক্ষণিক রোগ নির্ণয় করুন।') ?></p>
                <ul class="list-unstyled mb-4">
                    <?php
                    $aiFeatures = ['তাৎক্ষণিক রোগ নির্ণয়', 'রোগের বিস্তারিত তথ্য', 'চিকিৎসার পরামর্শ', 'সংশ্লিষ্ট পণ্যের সুপারিশ', 'পূর্ববর্তী নির্ণয়ের ইতিহাস'];
                    foreach ($aiFeatures as $f): ?>
                    <li class="mb-2 d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-warning"></i>
                        <span class="opacity-90"><?= e($f) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <a href="<?= url('ai/') ?>" class="btn btn-warning btn-lg fw-bold">
                    <i class="bi bi-cpu me-2"></i><?= hp('ai', 'btn_text', 'AI দিয়ে রোগ নির্ণয় করুন') ?>
                </a>
            </div>
            <div class="col-lg-6">
                <div class="ai-workflow-container p-4 rounded-4 border border-white border-opacity-25 shadow-lg" style="background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(12px);">
                    <h4 class="text-white fw-bold mb-4 text-center d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-diagram-3-fill text-warning"></i>
                        <span>কীভাবে কাজ করে</span>
                    </h4>
                    
                    <div class="row g-2 g-sm-3 align-items-center justify-content-center text-center">
                        <!-- Step 1 Card -->
                        <div class="col-4 col-sm-3">
                            <div class="ai-step-card p-2 p-sm-3 bg-white rounded-4 shadow-sm text-dark h-100 d-flex flex-column align-items-center justify-content-center">
                                <div class="ai-step-icon mb-1 mb-sm-2 text-primary fs-3 fs-sm-2">
                                    <i class="bi bi-camera-fill"></i>
                                </div>
                                <div class="fw-bold ai-step-text text-slate-800">ছবি আপলোড করুন</div>
                            </div>
                        </div>

                        <!-- Arrow 1 (Desktop) -->
                        <div class="col-sm-1 d-none d-sm-block text-white text-opacity-75 fs-4 p-0 text-center">
                            <i class="bi bi-chevron-right"></i>
                        </div>

                        <!-- Step 2 Card -->
                        <div class="col-4 col-sm-3">
                            <div class="ai-step-card p-2 p-sm-3 bg-white rounded-4 shadow-sm text-dark h-100 d-flex flex-column align-items-center justify-content-center">
                                <div class="ai-step-icon mb-1 mb-sm-2 text-info fs-3 fs-sm-2">
                                    <i class="bi bi-search"></i>
                                </div>
                                <div class="fw-bold ai-step-text text-slate-800">AI বিশ্লেষণ</div>
                            </div>
                        </div>

                        <!-- Arrow 2 (Desktop) -->
                        <div class="col-sm-1 d-none d-sm-block text-white text-opacity-75 fs-4 p-0 text-center">
                            <i class="bi bi-chevron-right"></i>
                        </div>

                        <!-- Step 3 Card -->
                        <div class="col-4 col-sm-3">
                            <div class="ai-step-card p-2 p-sm-3 bg-white rounded-4 shadow-sm text-dark h-100 d-flex flex-column align-items-center justify-content-center">
                                <div class="ai-step-icon mb-1 mb-sm-2 fs-3 fs-sm-2" style="color: #0d9488;">
                                    <i class="bi bi-file-earmark-medical-fill"></i>
                                </div>
                                <div class="fw-bold ai-step-text text-slate-800">রোগ নির্ণয় & সমাধান</div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="text-center mt-4">
                        <a href="<?= url('ai/') ?>" class="btn btn-warning btn-lg rounded-pill fw-bold px-4 shadow-sm">
                            <i class="bi bi-camera me-1"></i> এখনই ছবি তুলুন / আপলোড করুন
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── FEATURED PRODUCTS ──────────────────────────────────── -->
<?php if ($featuredProducts): ?>
<section id="products" class="section-padding">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title fish-main-title"><?= hp('products', 'title', 'বিশেষ পণ্যসমূহ') ?></h2>
            <div class="section-divider"></div>
            <p class="section-subtitle"><?= hp('products', 'subtitle', 'আমাদের বিশেষভাবে বাছাই করা সেরা পণ্যগুলো দেখুন') ?></p>
        </div>
        <div class="row g-4">
            <?php 
            $csrf = csrf_generate();
            foreach ($featuredProducts as $index => $p): 
                $disc = discount_percent($p);
                if ($disc > 0) {
                    $badgeClass = 'bestseller'; 
                    $badgeText = $disc . '% ছাড়';
                } elseif ($index % 3 == 0) {
                    $badgeClass = 'bestseller'; 
                    $badgeText = 'বেস্ট সেলার';
                } elseif ($index % 3 == 1) {
                    $badgeClass = 'new'; 
                    $badgeText = 'নতুন';
                } else {
                    $badgeClass = 'popular'; 
                    $badgeText = 'জনপ্রিয়';
                }
            ?>
            <div class="col-6 col-md-4 col-xl-3">
                <div class="ui-product-card position-relative">
                    <!-- Badges -->
                    <div class="ui-badge-container">
                        <span class="ui-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                    </div>
                    
                    <!-- Wishlist -->
                    <button type="button" class="ui-wishlist-btn" title="পছন্দের তালিকায় রাখুন"><i class="bi bi-heart"></i></button>

                    <a href="<?= url('products/details.php?slug=' . e($p['slug'])) ?>" class="text-decoration-none text-dark d-flex flex-column h-100">
                        <img src="<?= uploaded_image_url('products', $p['image']) ?>" alt="<?= e($p['name']) ?>" class="ui-product-img" loading="lazy">
                        
                        <h3 class="ui-product-title"><?= e($p['name']) ?></h3>
                        <p class="ui-product-sub"><?= e(excerpt($p['description'] ?? 'মাছের রোগ প্রতিরোধ ও সুস্থতার জন্য', 40)) ?></p>
                        
                        <div class="ui-product-price"><?= format_price(effective_price($p)) ?></div>
                        
                        <div class="ui-product-bottom-row">
                            <div class="ui-product-rating">
                                <i class="bi bi-star-fill"></i>
                                <span>4.8 (124)</span>
                            </div>
                            <?php if ($p['stock'] < 1): ?>
                                <button class="btn btn-secondary rounded-pill btn-sm disabled" style="font-weight: 600; font-size: 0.8rem; padding: 0.5rem 0.8rem;">স্টকে নেই</button>
                            <?php else: ?>
                                <button class="btn-add-cart-ui rounded-pill"
                                        data-action="add-to-cart"
                                        data-product-id="<?= (int)$p['id'] ?>"
                                        data-csrf="<?= $csrf ?>"
                                        onclick="event.preventDefault();">
                                    <i class="bi bi-cart-plus"></i> কার্টে যোগ করুন
                                </button>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?= url('products/') ?>" class="btn btn-aquatic-primary btn-lg px-4">
                <span>সব পণ্য দেখুন</span>
                <span class="btn-icon-circle ms-2"><i class="bi bi-arrow-right"></i></span>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── WHY CHOOSE US ─────────────────────────────────────── -->
<section id="why-us" class="section-padding position-relative">
    <div class="container position-relative z-1">
        <div class="row align-items-center g-5">
            <div class="col-lg-5 text-white">
                <span class="badge bg-white px-3 py-2 rounded-pill shadow-sm mb-3 d-inline-flex align-items-center gap-1" style="font-weight: 600; font-size: 0.9rem; color: #0f172a !important;">
                    <i class="bi bi-award-fill text-primary"></i>
                    <span>আমাদের বিশেষত্ব</span>
                </span>
                <h2 class="display-6 fw-bold text-white mb-3">
                    <?= hp('why_us', 'title', 'কেন <span class="text-info">FarmersBD</span> বেছে নেবেন?') ?>
                </h2>
                <p class="text-white opacity-90 fs-6 lh-lg mb-4">
                    <?= hp('why_us', 'subtitle', 'আমরা আধুনিক কৃত্রিম বুদ্ধিমত্তা (AI) ও অভিজ্ঞ মৎস্য বিশেষজ্ঞদের মাধ্যমে মাছ চাষীদের জন্য ১০০% বিশ্বস্ত ও বৈজ্ঞানিক স্বাস্থ্য সেবা নিশ্চিত করি।') ?>
                </p>

                <!-- Value Stat Badges -->
                <div class="row g-3 mb-4">
                    <div class="col-4">
                        <div class="why-us-stat-badge">
                            <div class="why-us-stat-num">১০,০০০+</div>
                            <div class="why-us-stat-lbl">বিশ্বস্ত খামারি</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="why-us-stat-badge">
                            <div class="why-us-stat-num">৯৮%</div>
                            <div class="why-us-stat-lbl">সফল শণাক্ত</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="why-us-stat-badge">
                            <div class="why-us-stat-num">২৪/৭</div>
                            <div class="why-us-stat-lbl">জরুরি সহায়তা</div>
                        </div>
                    </div>
                </div>

                <a href="<?= url('contact/') ?>" class="btn btn-light text-primary fw-bold shadow-sm px-4 py-2.5 rounded-pill hover-top">
                    <i class="bi bi-chat-dots-fill me-2"></i>আমাদের সাথে যোগাযোগ করুন
                </a>
            </div>
            <div class="col-lg-7">
                <div class="row g-3">
                    <?php
                    $whyUs = [
                        [
                            'icon' => 'bi-person-check-fill',
                            'bg' => 'linear-gradient(135deg, #0284c7, #0f766e)',
                            'key' => 'point1',
                            'default' => 'বিশেষজ্ঞ পরামর্শদাতা দল',
                            'desc' => 'অভিজ্ঞ মৎস্যবিজ্ঞানী ও চিকিৎসকদের সরাসরি নির্দেশিকা'
                        ],
                        [
                            'icon' => 'bi-cpu-fill',
                            'bg' => 'linear-gradient(135deg, #7c3aed, #4f46e5)',
                            'key' => 'point2',
                            'default' => 'AI-চালিত রোগ নির্ণয় ব্যবস্থা',
                            'desc' => 'ছবির মাধ্যমে মুহূর্তের মধ্যে মাছের রোগ শনাক্তকরণ'
                        ],
                        [
                            'icon' => 'bi-headset',
                            'bg' => 'linear-gradient(135deg, #ec4899, #d97706)',
                            'key' => 'point3',
                            'default' => 'সার্বক্ষণিক গ্রাহক সেবা',
                            'desc' => '২৪ ঘণ্টা ফোন ও হোয়াটসঅ্যাপে জরুরি পরামর্শ সেবা'
                        ],
                        [
                            'icon' => 'bi-truck-flatbed',
                            'bg' => 'linear-gradient(135deg, #ea580c, #ca8a04)',
                            'key' => 'point4',
                            'default' => 'দ্রুত ও নিরাপদ ডেলিভারি',
                            'desc' => 'সারা দেশে খামারে দ্রুততম সময়ে মানসম্মত পণ্য পৌঁছানো'
                        ],
                        [
                            'icon' => 'bi-shield-check-fill',
                            'bg' => 'linear-gradient(135deg, #059669, #0d9488)',
                            'key' => 'point5',
                            'default' => 'বিশ্বস্ত ও মানসম্পন্ন পণ্য',
                            'desc' => 'প্রস্তুতকারক সার্টিফাইড ১০০% আসল ওষুধ ও ফিড'
                        ],
                        [
                            'icon' => 'bi-tags-fill',
                            'bg' => 'linear-gradient(135deg, #0891b2, #2563eb)',
                            'key' => 'point6',
                            'default' => 'সাশ্রয়ী মূল্যে সেরা সেবা',
                            'desc' => 'খামারিদের উৎপাদন খরচ কমাতে বিশেষ সাশ্রয়ী প্যাকেজ'
                        ],
                    ];
                    foreach ($whyUs as $item): ?>
                    <div class="col-6 col-md-6">
                        <div class="why-us-card">
                            <div class="why-us-icon-wrapper" style="background: <?= $item['bg'] ?>;">
                                <i class="bi <?= e($item['icon']) ?>"></i>
                            </div>
                            <div>
                                <h3 class="why-us-title"><?= hp('why_us', $item['key'], $item['default']) ?></h3>
                                <p class="why-us-desc"><?= e($item['desc']) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── CONSULTATION CTA ──────────────────────────────────── -->
<section id="consultation" class="consult-cta-section">
    <!-- Decorative floating orbs -->
    <div class="consult-orb consult-orb-1"></div>
    <div class="consult-orb consult-orb-2"></div>
    <div class="consult-orb consult-orb-3"></div>

    <div class="container position-relative" style="z-index:2;">
        <div class="row align-items-center g-5">

            <!-- LEFT: Content -->
            <div class="col-lg-6">
                <span class="consult-badge mb-3 d-inline-flex align-items-center gap-2">
                    <span class="consult-badge-dot"></span>
                    বিশেষজ্ঞ সেবা চালু আছে
                </span>

                <h2 class="consult-headline mb-3">
                    <?= hp('consultation', 'title', 'বিশেষজ্ঞ পরামর্শ নিন') ?>
                </h2>

                <p class="consult-subtext mb-4">
                    <?= hp('consultation', 'subtitle', 'আমাদের অভিজ্ঞ বিশেষজ্ঞ দলের সাথে যোগাযোগ করুন এবং আপনার মাছের সমস্যার দ্রুত সমাধান পান।') ?>
                </p>

                <!-- Feature pills -->
                <div class="consult-features mb-5">
                    <div class="consult-feature-pill">
                        <i class="bi bi-clock-fill"></i>
                        <span>২৪ ঘণ্টার মধ্যে উত্তর</span>
                    </div>
                    <div class="consult-feature-pill">
                        <i class="bi bi-shield-check-fill"></i>
                        <span>যাচাইকৃত বিশেষজ্ঞ</span>
                    </div>
                    <div class="consult-feature-pill">
                        <i class="bi bi-chat-heart-fill"></i>
                        <span>ব্যক্তিগত পরামর্শ</span>
                    </div>
                    <div class="consult-feature-pill">
                        <i class="bi bi-camera-video-fill"></i>
                        <span>ভিডিও সেশন</span>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <a href="<?= url('consultation/create.php') ?>" class="btn-consult-primary">
                        <i class="bi bi-chat-text-fill me-2"></i>
                        <?= hp('consultation', 'btn_text', 'পরামর্শ নিন') ?>
                        <span class="btn-consult-arrow"><i class="bi bi-arrow-right"></i></span>
                    </a>
                    <a href="<?= url('consultation/') ?>" class="btn-consult-ghost">
                        আরও জানুন <i class="bi bi-chevron-right ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- RIGHT: Visual stats card -->
            <div class="col-lg-6">
                <div class="consult-card-grid">
                    <!-- Expert online badge -->
                    <div class="consult-stat-card consult-stat-main">
                        <div class="consult-stat-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <div class="consult-stat-number">৫০+</div>
                            <div class="consult-stat-label">সক্রিয় বিশেষজ্ঞ</div>
                        </div>
                    </div>

                    <div class="consult-stat-card consult-stat-alt">
                        <div class="consult-stat-icon consult-icon-green">
                            <i class="bi bi-patch-check-fill"></i>
                        </div>
                        <div>
                            <div class="consult-stat-number">৯৮%</div>
                            <div class="consult-stat-label">সন্তুষ্টি হার</div>
                        </div>
                    </div>

                    <div class="consult-stat-card consult-stat-wide">
                        <div class="d-flex align-items-center gap-3">
                            <div class="consult-stat-icon consult-icon-amber">
                                <i class="bi bi-award-fill"></i>
                            </div>
                            <div>
                                <div class="consult-stat-number">১০,০০০+</div>
                                <div class="consult-stat-label">সফল পরামর্শ সেশন সম্পন্ন</div>
                            </div>
                        </div>
                        <div class="consult-progress-bar mt-3">
                            <div class="consult-progress-fill"></div>
                        </div>
                    </div>

                    <div class="consult-stat-card consult-stat-response">
                        <i class="bi bi-lightning-charge-fill consult-lightning"></i>
                        <div class="consult-stat-number">৩ ঘণ্টা</div>
                        <div class="consult-stat-label">গড় প্রতিক্রিয়া সময়</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>


<!-- ── RECENT BLOGS ──────────────────────────────────────── -->
<?php if ($recentBlogs): ?>
<section id="blogs" class="section-padding blog-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title blog-main-title"><?= hp('blogs', 'title', 'সাম্প্রতিক ব্লগ') ?></h2>
            <div class="section-divider"></div>
            <p class="section-subtitle"><?= hp('blogs', 'subtitle', 'মাছ চাষ সম্পর্কিত সর্বশেষ তথ্য ও টিপস') ?></p>
        </div>
        <div class="row g-4 justify-content-center">
            <?php foreach ($recentBlogs as $blog): ?>
            <div class="col-12 col-md-4">
                <article class="blog-card-modern">
                    <!-- Image -->
                    <div class="blog-card-img-wrap">
                        <img src="<?= uploaded_image_url('blogs', $blog['image']) ?>"
                             alt="<?= e($blog['title']) ?>" loading="lazy">
                        <?php if ($blog['category_name']): ?>
                        <span class="blog-card-cat"><?= e($blog['category_name']) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Body -->
                    <div class="blog-card-body">
                        <div class="blog-card-meta mb-2">
                            <i class="bi bi-calendar3"></i>
                            <span><?= bangla_date($blog['created_at']) ?></span>
                        </div>
                        <h3 class="blog-card-title"><?= e($blog['title']) ?></h3>
                        <p class="blog-card-excerpt"><?= e(excerpt($blog['excerpt'] ?? $blog['content'] ?? '', 90)) ?></p>
                        <a href="<?= url('blog/details.php?slug=' . e($blog['slug'])) ?>" class="blog-card-btn">
                            <span>পড়ুন</span>
                            <span class="blog-btn-icon"><i class="bi bi-arrow-right"></i></span>
                        </a>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="<?= url('blog/') ?>" class="btn btn-aquatic-primary btn-lg px-4">
                <span>সব ব্লগ দেখুন</span>
                <span class="btn-icon-circle ms-2"><i class="bi bi-arrow-right"></i></span>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- ── FAQ ──────────────────────────────────────────────── -->
<?php if ($faqs): ?>
<section id="faq" class="section-padding faq-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="section-header">
                    <h2 class="section-title faq-main-title">সচরাচর জিজ্ঞাসা</h2>
                    <div class="section-divider"></div>
                </div>
                <div class="accordion modern-accordion" id="faqAccordion">
                    <?php foreach ($faqs as $i => $faq): ?>
                    <div class="accordion-item faq-card mb-3 border-0">
                        <h3 class="accordion-header">
                            <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#faq<?= (int)$faq['id'] ?>"
                                    aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>">
                                <span class="faq-icon-wrap"><i class="bi bi-question-circle"></i></span>
                                <span class="faq-question-text"><?= e($faq['question']) ?></span>
                            </button>
                        </h3>
                        <div id="faq<?= (int)$faq['id'] ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                             data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p class="faq-answer-text"><?= e($faq['answer']) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── NEWSLETTER ────────────────────────────────────────── -->
<section id="newsletter" class="section-padding bg-primary-gradient text-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <i class="bi bi-envelope-paper-fill fs-1 mb-3 opacity-75"></i>
                <h2 class="text-white fw-bold mb-2"><?= hp('newsletter', 'title', 'নিউজলেটার সাবস্ক্রাইব করুন') ?></h2>
                <p class="opacity-90 mb-4"><?= hp('newsletter', 'subtitle', 'সর্বশেষ তথ্য, টিপস এবং অফার পেতে সাবস্ক্রাইব করুন।') ?></p>
                <form id="newsletter-form" class="d-flex gap-2 justify-content-center flex-wrap" 
                      action="<?= url('contact/newsletter-subscribe.php') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="email" name="email" class="form-control" style="max-width:320px"
                           placeholder="আপনার ইমেইল ঠিকানা" required aria-label="ইমেইল">
                    <button type="submit" class="btn btn-warning fw-bold">
                        <i class="bi bi-send me-1"></i>সাবস্ক্রাইব
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- ── CONTACT INFO ─────────────────────────────────────── -->
<section id="contact-info" class="section-padding">
    <div class="container">
        <div class="section-header">
            <span class="badge-label"><i class="bi bi-telephone me-1"></i>যোগাযোগ</span>
            <h2 class="section-title">আমাদের সাথে যোগাযোগ করুন</h2>
            <div class="section-divider"></div>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4 text-center">
                <div class="stat-card">
                    <i class="bi bi-telephone-fill text-primary fs-2 mb-2"></i>
                    <h4 class="fw-bold"><?= e(setting('site_phone', '01979-606212')) ?></h4>
                    <p class="text-muted mb-0">ফোন</p>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="stat-card">
                    <i class="bi bi-envelope-fill text-primary fs-2 mb-2"></i>
                    <h4 class="fw-bold"><?= e(setting('site_email', 'Info.Farmersbd@gmail.com')) ?></h4>
                    <p class="text-muted mb-0">ইমেইল</p>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="stat-card">
                    <i class="bi bi-geo-alt-fill text-primary fs-2 mb-2"></i>
                    <h4 class="fw-bold"><?= e(setting('site_address', 'ঢাকা, বাংলাদেশ')) ?></h4>
                    <p class="text-muted mb-0">ঠিকানা</p>
                </div>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="<?= url('contact/') ?>" class="btn btn-modern">বার্তা পাঠান <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
