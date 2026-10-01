<?php
// ============================================================
// FarmersBD — Product Detail Page
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/flash.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/seo.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) { redirect(BASE_URL . '/products/'); }

$product = db_query_one(
    "SELECT p.*, c.name AS category_name
     FROM products p
     LEFT JOIN product_categories c ON c.id = p.category_id
     WHERE p.slug = ? AND p.is_active = 1 AND p.deleted_at IS NULL",
    [$slug]
);

if (!$product) {
    http_response_code(404);
    flash('পণ্যটি পাওয়া যায়নি।', FLASH_ERROR);
    redirect(BASE_URL . '/products/');
}

// Related products
$related = db_query(
    "SELECT * FROM products WHERE category_id = ? AND id != ? AND is_active = 1 AND deleted_at IS NULL ORDER BY RAND() LIMIT 5",
    [$product['category_id'], $product['id']]
);

// Reviews
$reviews = db_query(
    "SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.product_id = ? AND r.is_approved = 1 ORDER BY r.created_at DESC",
    [$product['id']]
);
$rating_avg = 0;
if (count($reviews) > 0) {
    $sum = array_reduce($reviews, fn($c, $r) => $c + $r['rating'], 0);
    $rating_avg = round($sum / count($reviews), 1);
}

$disc = discount_percent($product);
$price = effective_price($product);

$page_seo = [
    'title'       => e($product['name']) . ' | ' . setting('site_name', 'FarmersBD'),
    'description' => excerpt($product['description'] ?? '', 160),
    'og_image'    => UPLOAD_BASE_URL . 'products/' . ($product['image'] ?? ''),
];

$csrf = csrf_generate();
include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/navbar.php';
?>

<link rel="stylesheet" href="<?= asset('assets/css/product-details.css') ?>?v=<?= time() ?>">

<!-- ═══ Breadcrumb ═══ -->
<div class="pd-breadcrumb">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url() ?>">হোম</a></li>
                <li class="breadcrumb-item"><a href="<?= url('products/') ?>"><?= e($product['category_name'] ?? 'ওষুধ') ?></a></li>
                <li class="breadcrumb-item active"><?= e($product['name']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container pd-top-section">
    <div class="row g-5">
        <!-- ═══════════════════════════════════════════════ -->
        <!-- IMAGE GALLERY (LEFT ~40%)                       -->
        <!-- ═══════════════════════════════════════════════ -->
        <div class="col-lg-5">
            <div class="pd-gallery">
                <div class="pd-thumbnails">
                    <!-- Showing main image as thumbnail + some placeholders -->
                    <div class="pd-thumb active"><img src="<?= uploaded_image_url('products', $product['image']) ?>" alt="Thumb 1"></div>
                    <div class="pd-thumb"><img src="<?= uploaded_image_url('products', $product['image']) ?>" alt="Thumb 2"></div>
                    <div class="pd-thumb"><img src="<?= uploaded_image_url('products', $product['image']) ?>" alt="Thumb 3"></div>
                </div>
                <div class="pd-main-image">
                    <button class="pd-nav-btn prev"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg></button>
                    <img src="<?= uploaded_image_url('products', $product['image']) ?>" alt="<?= e($product['name']) ?>">
                    <button class="pd-nav-btn next"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></button>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════ -->
        <!-- PRODUCT INFO (RIGHT ~60%)                       -->
        <!-- ═══════════════════════════════════════════════ -->
        <div class="col-lg-7">
            <div class="row g-4">
                
                <!-- Main Info Section (Left column of the right side) -->
                <div class="col-md-7">
                    <div class="pd-info">
                        <div class="pd-category-badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            বিজ্ঞানসম্মত পণ্য
                        </div>
                        
                        <h1 class="pd-title"><?= e($product['name']) ?></h1>
                        <div class="pd-subtitle">Bio Feed Booster | Aquaculture Probiotic Supplement</div>
                        
                        <div class="pd-rating">
                            <div class="pd-stars">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <svg viewBox="0 0 24 24" fill="<?= $i <= round($rating_avg) ? '#fbbf24' : '#e5e7eb' ?>"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                <?php endfor; ?>
                            </div>
                            <span class="pd-rating-text"><?= $rating_avg ?> (<?= count($reviews) ?> রিভিউ)</span>
                        </div>

                        <div class="pd-price-row">
                            <span class="pd-price-current"><?= format_price($price) ?></span>
                            <?php if ($disc > 0): ?>
                                <span class="pd-price-old"><?= format_price((float)$product['price']) ?></span>
                                <span class="pd-discount-badge"><?= $disc ?>% ছাড়</span>
                            <?php endif; ?>
                        </div>

                        <div class="pd-short-desc">
                            মাছের দ্রুত বৃদ্ধি এবং রোগ প্রতিরোধ ক্ষমতা বৃদ্ধিতে সাহায্য করে। এটি প্রোবায়োটিক সমৃদ্ধ একটি উন্নত মানের ফিড সাপ্লিমেন্ট, যা মাছের স্বাস্থ্য এবং উৎপাদনশীলতা বাড়াতে সহায়ক।
                        </div>

                        <?php if ($product['stock'] > 0): ?>
                        <div class="pd-cart-row">
                            <div class="pd-qty-control qty-control">
                                <button class="pd-qty-btn" data-action="qty-decrease">-</button>
                                <input type="number" id="product-qty" class="pd-qty-input" value="1" min="1" max="<?= (int)$product['stock'] ?>">
                                <button class="pd-qty-btn" data-action="qty-increase">+</button>
                            </div>
                            <button class="btn-add-cart" id="main-add-btn" data-action="add-to-cart" data-product-id="<?= (int)$product['id'] ?>" data-csrf="<?= $csrf ?>">
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1.003 1.003 0 0 0 20 4H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
                                কার্টে যোগ করুন
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-danger mb-4">পণ্যটি বর্তমানে স্টকে নেই</div>
                        <?php endif; ?>

                        <button class="btn-wishlist">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                            পছন্দের তালিকায় যুক্ত করুন
                        </button>
                    </div>
                </div>

                <!-- Features Sidebar (Right column of the right side) -->
                <div class="col-md-5">
                    <div class="pd-features">
                        <div class="pd-feature-card">
                            <div class="pd-feature-icon" style="background:#e0f2fe; color:#0ea5e9;">
                                <svg viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                            </div>
                            <div>
                                <div class="pd-feature-title">দ্রুত বৃদ্ধি নিশ্চিত করে</div>
                                <div class="pd-feature-sub">মাছের ওজন বাড়াতে সাহায্য করে</div>
                            </div>
                        </div>
                        
                        <div class="pd-feature-card">
                            <div class="pd-feature-icon" style="background:#d1fae5; color:#10b981;">
                                <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                            </div>
                            <div>
                                <div class="pd-feature-title">রোগ প্রতিরোধ ক্ষমতা বাড়ায়</div>
                                <div class="pd-feature-sub">পারি ও স্বাস্থ্যকর মাছ</div>
                            </div>
                        </div>

                        <div class="pd-feature-card">
                            <div class="pd-feature-icon" style="background:#ccfbf1; color:#0f766e;">
                                <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" fill="none" stroke="currentColor" stroke-width="2"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                            </div>
                            <div>
                                <div class="pd-feature-title">ফিড কনভার্সন রেট উন্নত করে</div>
                                <div class="pd-feature-sub">খাদ্য অপচয় কমায়</div>
                            </div>
                        </div>

                        <div class="pd-feature-card">
                            <div class="pd-feature-icon" style="background:#e0e7ff; color:#4f46e5;">
                                <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" fill="none" stroke="currentColor" stroke-width="2"/><polyline points="22 4 12 14.01 9 11.01" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                            </div>
                            <div>
                                <div class="pd-feature-title">সহজে ব্যবহারযোগ্য</div>
                                <div class="pd-feature-sub">পাউডার/গ্রানুলার ফর্মে পাওয়া যায়</div>
                            </div>
                        </div>

                        <!-- Eco friendly banner -->
                        <div class="pd-feature-card mt-2" style="background: #f0fdf4; border-color: #bbf7d0;">
                            <div class="pd-feature-icon" style="background: #dcfce7; color:#16a34a;">
                                <svg viewBox="0 0 24 24"><path d="M2 22a8 8 0 0 1 8-8h10a8 8 0 0 1 8 8M12 14V2M12 2C8 2 5 5 5 9s7 5 7 5 7-1 7-5-3-7-7-7z" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                            </div>
                            <div>
                                <div class="pd-feature-title" style="color:#166534">নিরাপদ ও পরিবেশবান্ধব</div>
                                <div class="pd-feature-sub" style="color:#15803d">জলাশয়ের জন্য উপযোগী</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════ -->
    <!-- LOWER SECTION                                   -->
    <!-- ═══════════════════════════════════════════════ -->
    <div class="row g-5 mt-2">
        <!-- Left Column: Tabs Content -->
        <div class="col-lg-8">
            <div class="pd-tabs" id="pdTabs">
                <button class="pd-tab active" data-tab="details" onclick="switchTab('details')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    পণ্যের বিবরণ
                </button>
                <button class="pd-tab" data-tab="usage" onclick="switchTab('usage')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    ব্যবহারবিধি
                </button>
                <button class="pd-tab" data-tab="ingredients" onclick="switchTab('ingredients')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                    উপাদানসমূহ
                </button>
                <button class="pd-tab" data-tab="warnings" onclick="switchTab('warnings')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    সতর্কতা
                </button>
                <button class="pd-tab" data-tab="reviews" onclick="switchTab('reviews')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                    রিভিউ (<?= count($reviews) ?>)
                </button>
            </div>

            <div class="pd-content-section">
                <!-- Tab Details -->
                <div class="pd-tab-content active" id="tab-details">
                    <div class="pd-content-title">পণ্যের বিবরণ</div>
                    <div class="pd-content-body">
                        <p><?= nl2br(e($product['description'] ?: 'প্রোবায়োটিক গ্রোথ প্লাস একটি উন্নতমানের বায়ো-ফিড বুস্টার যা মাছের পরিপাকতন্ত্রকে সুস্থ রাখে এবং প্রাকৃতিকভাবে রোগ প্রতিরোধ ক্ষমতা বাড়ায়। এতে থাকা উপকারী ব্যাকটেরিয়া মাছের খাদ্য হজমে সাহায্য করে, ফলে খাদ্যের সঠিক ব্যবহার নিশ্চিত হয় এবং মাছের দ্রুত বৃদ্ধি ঘটে। এটি বিশেষভাবে চাষের পুকুরে, রেস সার্কুলেশনে এবং উচ্চ ঘনত্বের মাছ চাষে অত্যন্ত কার্যকর।')) ?></p>

                        <div class="mt-4 pd-content-title" style="font-size: 1rem; border:none; margin-bottom:0.5rem">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2" width="16" height="16"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            উপকারিতা
                        </div>
                        <div class="pd-benefits-row">
                            <div class="pd-benefit-tag">
                                <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg> দ্রুত বৃদ্ধি
                            </div>
                            <div class="pd-benefit-tag">
                                <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg> রোগ প্রতিরোধ ক্ষমতা বৃদ্ধি
                            </div>
                            <div class="pd-benefit-tag">
                                <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg> খাদ্য গ্রহণে আগ্রহ বাড়ায়
                            </div>
                            <div class="pd-benefit-tag">
                                <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg> পানির গুণগত মান বজায় রাখতে সাহায্য করে
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Usage -->
                <div class="pd-tab-content" id="tab-usage">
                    <div class="pd-content-title">ব্যবহারবিধি</div>
                    <div class="pd-content-body">
                        <?= nl2br(e($product['usage'] ?: 'তথ্য উপলব্ধ নয়।')) ?>
                    </div>
                </div>

                <!-- Tab Ingredients -->
                <div class="pd-tab-content" id="tab-ingredients">
                    <div class="pd-content-title">উপাদানসমূহ</div>
                    <div class="pd-content-body">
                        প্রোবায়োটিক স্ট্রেন, ভিটামিন এবং মিনারেলস।
                    </div>
                </div>

                <!-- Tab Warnings -->
                <div class="pd-tab-content" id="tab-warnings">
                    <div class="pd-content-title">সতর্কতা</div>
                    <div class="pd-content-body text-danger">
                        শিশুদের নাগালের বাইরে রাখুন। শুষ্ক ও ঠাণ্ডা স্থানে সংরক্ষণ করুন।
                    </div>
                </div>

                <!-- Tab Reviews -->
                <div class="pd-tab-content" id="tab-reviews">
                    <div class="pd-content-title">গ্রাহকদের রিভিউ</div>
                    <div class="pd-content-body">
                        <?php if (is_logged_in()): ?>
                            <form method="POST" action="<?= url('api/submit_review.php') ?>" class="mb-4 border p-3 rounded">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <h5>আপনার রিভিউ দিন</h5>
                                <div class="mb-2">
                                    <label class="form-label">রেটিং (১-৫)</label>
                                    <select name="rating" class="form-select w-auto" required>
                                        <option value="5">৫ - চমৎকার</option>
                                        <option value="4">৪ - ভালো</option>
                                        <option value="3">৩ - চলনসই</option>
                                        <option value="2">২ - খারাপ</option>
                                        <option value="1">১ - অত্যন্ত খারাপ</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">মতামত</label>
                                    <textarea name="review" class="form-control" rows="3" required placeholder="আপনার মতামত লিখুন..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">সাবমিট করুন</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info">
                                রিভিউ দিতে <a href="<?= url('auth/login.php') ?>" class="fw-bold text-decoration-underline">লগইন</a> করুন।
                            </div>
                        <?php endif; ?>

                        <div class="reviews-list">
                            <?php if (empty($reviews)): ?>
                                <p class="text-muted">এখনো কোনো রিভিউ নেই।</p>
                            <?php else: ?>
                                <?php foreach ($reviews as $rev): ?>
                                    <div class="border-bottom pb-3 mb-3">
                                        <div class="d-flex align-items-center mb-1">
                                            <strong class="me-2"><?= e($rev['user_name']) ?></strong>
                                            <div class="pd-stars">
                                                <?php for($i=1; $i<=5; $i++): ?>
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="<?= $i <= $rev['rating'] ? '#fbbf24' : '#e5e7eb' ?>"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                                <?php endfor; ?>
                                            </div>
                                            <small class="text-muted ms-auto"><?= format_date_bn($rev['created_at']) ?></small>
                                        </div>
                                        <p class="mb-0 text-secondary"><?= nl2br(e($rev['review'])) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Specs & Contact -->
        <div class="col-lg-4">
            <div class="pd-info-card">
                <div class="pd-info-card-title">পণ্যের তথ্য</div>
                
                <div class="pd-spec-row">
                    <div class="pd-spec-icon"><svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg></div>
                    <span class="pd-spec-label">ব্র্যান্ড</span>
                    <span class="pd-spec-value">Aqua Biotech Solutions</span>
                </div>
                
                <div class="pd-spec-row">
                    <div class="pd-spec-icon"><svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></div>
                    <span class="pd-spec-label">ধরন</span>
                    <span class="pd-spec-value"><?= e($product['category_name'] ?? 'প্রোবায়োটিক') ?></span>
                </div>
                
                <div class="pd-spec-row">
                    <div class="pd-spec-icon"><svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></div>
                    <span class="pd-spec-label">পরিমাণ</span>
                    <span class="pd-spec-value">১ কেজি</span>
                </div>
                
                <div class="pd-spec-row border-0">
                    <div class="pd-spec-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="10" r="3"/><path d="M12 21.7C17.3 17 20 13 20 10a8 8 0 1 0-16 0c0 3 2.7 7 8 11.7z"/></svg></div>
                    <span class="pd-spec-label">উৎপত্তি দেশ</span>
                    <span class="pd-spec-value">বাংলাদেশ</span>
                </div>
            </div>

            <div class="pd-contact-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="pd-spec-icon" style="background:#e0f2fe; width:46px; height:46px; color:#0284c7;">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    </div>
                    <div>
                        <h6>কোনো প্রশ্ন? বিশেষজ্ঞদের সাথে কথা বলুন</h6>
                        <p>পণ্য সম্পর্কে বিস্তারিত জানুন বা অর্ডার করতে যোগাযোগ করুন।</p>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="tel:01979606212" class="btn-pd-phone">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        01979-606212
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════ -->
    <!-- RELATED PRODUCTS                                -->
    <!-- ═══════════════════════════════════════════════ -->
    <?php if (!empty($related)): ?>
    <div class="pd-related-section">
        <div class="pd-related-title">
            <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            সম্পর্কিত পণ্যসমূহ
        </div>
        <div class="pd-related-grid">
            <?php foreach ($related as $rp): $rd = discount_percent($rp); ?>
            <div class="pd-related-card">
                <div class="pd-related-fav">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </div>
                <div class="pd-related-img">
                    <a href="<?= url('products/details.php?slug=' . e($rp['slug'])) ?>">
                        <img src="<?= uploaded_image_url('products', $rp['image']) ?>" alt="<?= e($rp['name']) ?>">
                    </a>
                </div>
                <div class="pd-related-body">
                    <div class="pd-related-name">
                        <a href="<?= url('products/details.php?slug=' . e($rp['slug'])) ?>"><?= e($rp['name']) ?></a>
                    </div>
                    <div class="pd-related-price"><?= format_price(effective_price($rp)) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Interactive Scripts -->
<script>
// Quantity Selector
document.querySelectorAll('.pd-qty-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = document.getElementById('product-qty');
        let val = parseInt(input.value) || 1;
        const max = parseInt(input.max) || 999;
        
        if (this.dataset.action === 'qty-decrease' && val > 1) {
            val--;
        } else if (this.dataset.action === 'qty-increase' && val < max) {
            val++;
        }
        input.value = val;
    });
});

// Pass qty from input to cart.js
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('main-add-btn');
    const qty = document.getElementById('product-qty');
    if (btn && qty) {
        btn.addEventListener('click', async (e) => {
            // cart.js handles the fetch, we just attach the quantity
            btn.dataset.quantity = qty.value;
        }, true);
    }
});

// Tab Switcher
function switchTab(tabId) {
    document.querySelectorAll('.pd-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.pd-tab-content').forEach(c => c.classList.remove('active'));
    
    document.querySelector(`.pd-tab[data-tab="${tabId}"]`).classList.add('active');
    document.getElementById(`tab-${tabId}`).classList.add('active');
}
</script>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
