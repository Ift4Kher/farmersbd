<?php
// ============================================================
// FarmersBD — Disease Details & Recommended Treatment Page
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/flash.php';
require_once dirname(__DIR__) . '/includes/seo.php';

$slug = sanitize_input($_GET['slug'] ?? '');

$disease = db_query_one("SELECT * FROM diseases WHERE slug = ? AND is_active = 1", [$slug]);

if (!$disease) {
    set_flash('error', 'রোগের তথ্য পাওয়া যায়নি।');
    header('Location: ' . url('diseases/index.php'));
    exit;
}

// Fetch suggested products for this disease via relational mapping
$recommended_products = db_query(
    "SELECT p.* FROM products p 
     JOIN disease_products dp ON dp.product_id = p.id 
     WHERE dp.disease_id = ? AND p.is_active = 1 
     ORDER BY p.id ASC LIMIT 4",
    [$disease['id']]
);

// Fallback to keyword or popular remedies if specific mapping is absent
if (empty($recommended_products)) {
    $recommended_products = db_query(
        "SELECT p.* FROM products p WHERE (p.name LIKE ? OR p.description LIKE ?) AND p.is_active = 1 LIMIT 4",
        ['%' . $disease['name'] . '%', '%' . $disease['name'] . '%']
    );
}
if (empty($recommended_products)) {
    $recommended_products = db_query(
        "SELECT p.* FROM products p WHERE p.is_active = 1 ORDER BY p.id ASC LIMIT 4"
    );
}

$page_title = $disease['name'] . " — লক্ষণ ও চিকিৎসা — " . setting('site_name', 'FarmersBD');
$meta_desc  = mb_strimwidth(strip_tags($disease['symptoms'] ?? ''), 0, 150, '...');
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';

// Extract first sentence from description for hero intro
$introText = mb_strimwidth(strip_tags($disease['description'] ?? ''), 0, 180, '...');
?>

<link rel="stylesheet" href="<?= asset('assets/css/disease-details.css') ?>?v=<?= time() ?>">

<!-- ═══ Breadcrumb ═══ -->
<div class="disease-breadcrumb">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?= url() ?>">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        হোম
                    </a>
                </li>
                <li class="breadcrumb-item"><a href="<?= url('diseases/index.php') ?>">রোগের তালিকা</a></li>
                <li class="breadcrumb-item active"><?= h($disease['name']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-4">
    <div class="row g-4">

        <!-- ═══════════════════════════════════════════════ -->
        <!-- MAIN CONTENT (LEFT ~70%)                       -->
        <!-- ═══════════════════════════════════════════════ -->
        <div class="col-lg-8">

            <!-- ═══ Hero Banner ═══ -->
            <div class="disease-hero">
                <div class="disease-hero-content">
                    <!-- Category Badge -->
                    <div class="disease-cat-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
                        <span>ব্যাকটেরিয়াল রোগ</span>
                    </div>

                    <!-- Disease Name -->
                    <h1><?= h($disease['name']) ?></h1>
                    <?php
                    // Generate Bangla transliteration/subtitle
                    $bnName = '';
                    $name = $disease['name'];
                    if (mb_strpos($name, 'EUS') !== false) $bnName = 'এপিজুটিক আলসারেটিভ সিন্ড্রোম';
                    elseif (mb_strpos($name, 'Columnaris') !== false) $bnName = 'কলামনারিস রোগ';
                    elseif (mb_strpos($name, 'Dropsy') !== false) $bnName = 'ড্রপসি বা পেট ফোলা রোগ';
                    elseif (mb_strpos($name, 'Tail Rot') !== false || mb_strpos($name, 'Fin Rot') !== false) $bnName = 'লেজ ও পাখনা পচা রোগ';
                    elseif (mb_strpos($name, 'White Spot') !== false || mb_strpos($name, 'Ich') !== false) $bnName = 'সাদা দাগ বা ইক রোগ';
                    else $bnName = $disease['name'];
                    ?>
                    <?php if ($bnName !== $disease['name']): ?>
                        <div class="disease-name-bn"><?= h($bnName) ?></div>
                    <?php endif; ?>

                    <!-- Intro Text -->
                    <div class="disease-intro-text">
                        <?= h($introText) ?>
                    </div>

                    <!-- Quick Info Row -->
                    <div class="disease-quick-row">
                        <div class="disease-quick-item">
                            <div class="disease-quick-icon">
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                            </div>
                            <div>
                                <span class="disease-quick-label">প্রভাবিত মাছ</span>
                                <span class="disease-quick-value"><?= h(mb_strimwidth($disease['affected_fish'] ?? 'বিভিন্ন মাছ', 0, 35, '...')) ?></span>
                            </div>
                        </div>

                        <div class="disease-quick-item">
                            <div class="disease-quick-icon">
                                <svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="3.2"/><path d="M9 2L7.17 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2h-3.17L15 2H9zm3 15c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5z"/></svg>
                            </div>
                            <div>
                                <span class="disease-quick-label">কারক জীবাণু</span>
                                <span class="disease-quick-value">ব্যাকটেরিয়া</span>
                            </div>
                        </div>

                        <div class="disease-quick-item">
                            <div class="disease-quick-icon">
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6h-6z"/></svg>
                            </div>
                            <div>
                                <span class="disease-quick-label">সংক্রমের ধরন</span>
                                <span class="disease-quick-value">পানি ও সরাসরি সংস্পর্শ</span>
                            </div>
                        </div>

                        <div class="disease-quick-item">
                            <div class="disease-quick-icon">
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                            </div>
                            <div>
                                <span class="disease-quick-label">মুক্তির মাত্রা</span>
                                <span class="disease-quick-value">উচ্চ</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hero Image -->
                <div class="disease-hero-image d-none d-lg-flex">
                    <img src="<?= uploaded_image_url('diseases', $disease['image']) ?>" alt="<?= h($disease['name']) ?>">
                </div>
            </div>

            <!-- ═══ Tab Navigation ═══ -->
            <div class="disease-tabs" id="diseaseTabs">
                <button class="disease-tab active" data-tab="details" onclick="switchTab('details')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    রোগের বিস্তারিত
                </button>
                <button class="disease-tab" data-tab="symptoms" onclick="switchTab('symptoms')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    লক্ষণ
                </button>
                <button class="disease-tab" data-tab="causes" onclick="switchTab('causes')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4m0-4h.01"/></svg>
                    কারণ
                </button>
                <button class="disease-tab" data-tab="prevention" onclick="switchTab('prevention')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    প্রতিরোধ ও চিকিৎসা
                </button>
            </div>

            <!-- ═══ Warning Alert ═══ -->
            <div class="disease-warning">
                <div class="disease-warning-icon">
                    <svg viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
                </div>
                <div>
                    <h5>গুরুত্বপূর্ণ সতর্কতা</h5>
                    <p>
                        <?= h($disease['name']) ?> রোগ দ্রুত ছড়াতে পারে এবং আক্রান্ত ফেলে মৃত্যুর হতভরে বেশি। লক্ষণ দেখা মানেই দ্রুত ব্যবস্থা নিন এবং আক্রান্ত মাছ আলাদা রাখুন।
                    </p>
                </div>
            </div>

            <!-- ═══ Tab Contents ═══ -->
            <!-- Details Tab -->
            <div class="disease-tab-content active" id="tab-details">
                <div class="disease-section">
                    <div class="disease-section-title">
                        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                        রোগের সংক্ষিপ্ত বিবরণ
                    </div>
                    <div class="disease-section-body">
                        <?= nl2br(h($disease['description'] ?? 'তথ্য উপলব্ধ নয়।')) ?>
                    </div>
                </div>
            </div>

            <!-- Symptoms Tab -->
            <div class="disease-tab-content" id="tab-symptoms">
                <div class="disease-section">
                    <div class="disease-section-title">
                        <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>
                        রোগের প্রাথমিক লক্ষণসমূহ
                    </div>
                    <div class="disease-section-body">
                        <?= nl2br(h($disease['symptoms'] ?? 'তথ্য উপলব্ধ নয়।')) ?>
                    </div>
                </div>
            </div>

            <!-- Causes Tab -->
            <div class="disease-tab-content" id="tab-causes">
                <div class="disease-section">
                    <div class="disease-section-title">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 16v-4m0-4h.01" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                        রোগের কারণ
                    </div>
                    <div class="disease-section-body">
                        <?= nl2br(h($disease['causes'] ?? 'তথ্য উপলব্ধ নয়।')) ?>
                    </div>
                </div>
            </div>

            <!-- Prevention & Treatment Tab -->
            <div class="disease-tab-content" id="tab-prevention">
                <div class="disease-section">
                    <div class="disease-section-title">
                        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                        প্রতিরোধমূলক ব্যবস্থা
                    </div>
                    <div class="disease-section-body">
                        <?= nl2br(h($disease['prevention'] ?? 'তথ্য উপলব্ধ নয়।')) ?>
                    </div>
                </div>

                <div class="disease-section">
                    <div class="disease-section-title">
                        <svg viewBox="0 0 24 24"><path d="M19.5 12.572l-7.5 7.428-7.5-7.428m0 0A5 5 0 1 1 12 6.006a5 5 0 1 1 7.5 6.572" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                        কার্যকারী চিকিৎসা ও ঔষধ প্রয়োগ
                    </div>
                    <div class="disease-section-body">
                        <?= nl2br(h($disease['treatment'] ?? 'তথ্য উপলব্ধ নয়।')) ?>
                    </div>
                </div>
            </div>

            <!-- ═══ Recommended Products ═══ -->
            <?php if (!empty($recommended_products)): ?>
                <div class="disease-products-section">
                    <h4>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        এই রোগের জন্য সুপারিশকৃত ঔষধ
                    </h4>
                    <?php foreach ($recommended_products as $p): ?>
                        <div class="disease-product-item">
                            <img src="<?= uploaded_image_url('products', $p['image']) ?>" alt="<?= h($p['name']) ?>">
                            <div class="flex-grow-1">
                                <h6><a href="<?= url('products/details.php?slug=' . urlencode($p['slug'])) ?>"><?= h($p['name']) ?></a></h6>
                                <span class="price"><?= format_price(effective_price($p)) ?></span>
                            </div>
                            <a href="<?= url('products/details.php?slug=' . urlencode($p['slug'])) ?>" class="btn-buy-sm">কিনুন</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>

        <!-- ═══════════════════════════════════════════════ -->
        <!-- SIDEBAR (RIGHT ~30%)                           -->
        <!-- ═══════════════════════════════════════════════ -->
        <div class="col-lg-4">

            <!-- ═══ AI Scanner Card ═══ -->
            <div class="disease-ai-card">
                <div class="ai-icon-circle">
                    <svg viewBox="0 0 24 24"><path d="M9 2L7.17 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2h-3.17L15 2H9zm3 15c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5z"/></svg>
                </div>
                <h5>মাছের রোগ শনাক্ত করতে চান?</h5>
                <p>মাছের ছবি তুলুন এবং আমাদের AI সিস্টেমকে সঠিকভাবে রোগ শনাক্ত করতে দিন।</p>
                <a href="<?= url('ai/') ?>" class="btn-ai-scan">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    AI রোগ নির্ণয় করুন
                </a>
            </div>

            <!-- ═══ Quick Facts Card ═══ -->
            <div class="disease-facts-card">
                <div class="disease-facts-title">
                    <svg viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    দ্রুত তথ্য
                </div>

                <div class="disease-fact-item">
                    <div class="disease-fact-icon blue">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    </div>
                    <div>
                        <span class="disease-fact-label">আক্রান্ত মাছের ধরন</span>
                        <span class="disease-fact-value"><?= h($disease['affected_fish'] ?? 'বিভিন্ন মাছ') ?></span>
                    </div>
                </div>

                <div class="disease-fact-item">
                    <div class="disease-fact-icon green">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.21 9l-4.38-6.56a.999.999 0 0 0-1.66 0L6.79 9H2v2h1.25l1.67 11.07A1 1 0 0 0 5.91 23h12.18a1 1 0 0 0 .99-.93L20.75 11H22V9h-4.79z"/></svg>
                    </div>
                    <div>
                        <span class="disease-fact-label">সংক্রমণের মাধ্যম</span>
                        <span class="disease-fact-value">পানি, মাটি ও সংস্পর্শ</span>
                    </div>
                </div>

                <div class="disease-fact-item">
                    <div class="disease-fact-icon orange">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/></svg>
                    </div>
                    <div>
                        <span class="disease-fact-label">পুনর্ত তুক</span>
                        <span class="disease-fact-value">১-৩ সিজনে প্রকাশ হতে পারে</span>
                    </div>
                </div>

                <div class="disease-fact-item">
                    <div class="disease-fact-icon red">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    </div>
                    <div>
                        <span class="disease-fact-label">মৃত্যুহার</span>
                        <span class="disease-fact-value">৫০-৮০% পর্যন্ত হতে পারে</span>
                    </div>
                </div>
            </div>

            <!-- ═══ Phone Contact ═══ -->
            <div class="disease-contact-cta">
                <div class="disease-contact-phone">
                    <div class="phone-icon">
                        <svg viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                    </div>
                    <div>
                        <span class="phone-num">01979-606212</span>
                        <span class="phone-label">সরাসরি কল করুন</span>
                    </div>
                </div>
            </div>

            <!-- ═══ Expert Consultation Card ═══ -->
            <div class="disease-expert-card">
                <div class="expert-icon">
                    <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                </div>
                <h5>অভিজ্ঞ মৎস্য বিশেষজ্ঞদের সাথে সরাসরি যোগাযোগ করুন</h5>
                <p>আপনার সমস্যার কথা বলুন, অভিজ্ঞ বিশেষজ্ঞরা আপনাকে সঠিক পরামর্শ দেবেন।</p>
                <a href="<?= url('consultation/') ?>" class="btn-expert-consult">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    AI রোগের বিশ্লেষণ করুন
                </a>
            </div>

        </div>
    </div>
</div>

<!-- Tab Switching Script -->
<script>
function switchTab(tabId) {
    // Deactivate all tabs
    document.querySelectorAll('.disease-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.disease-tab-content').forEach(c => c.classList.remove('active'));
    
    // Activate selected
    document.querySelector(`.disease-tab[data-tab="${tabId}"]`).classList.add('active');
    document.getElementById(`tab-${tabId}`).classList.add('active');
}
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
