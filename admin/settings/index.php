<?php
// ============================================================
// FarmersBD — Site Settings Admin Module (Complete 9 Categories)
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/flash.php';
require_once $base . '/includes/csrf.php';
require_once $base . '/includes/admin-auth.php';

require_admin();

$page_title = 'সাইট সেটিংস কন্ট্রোল — FarmersBD এডমিন';
include dirname(__DIR__) . '/includes/header.php';
?>

<!-- ── Unsaved Changes Toast Notice ────────────────────────── -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="settingsToast" class="toast align-items-center text-white bg-dark border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2" id="toastMessage">
                <i class="bi bi-check-circle-fill text-info"></i> সেটিংস আপডেট করা হয়েছে।
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- ── Settings Module Header ──────────────────────────────── -->
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-teal-subtle text-teal rounded-pill px-3 py-1 fw-bold fs-7" style="color:#0d9488!important; background:#ccfbf1!important;">
                <i class="bi bi-sliders me-1"></i> ওয়েবসাইট কন্ট্রোল সেন্টার
            </span>
            <span id="unsavedBadge" class="badge bg-warning text-dark rounded-pill px-2 py-1 fs-7 d-none">
                <i class="bi bi-exclamation-circle me-1"></i> অসংরক্ষিত পরিবর্তন রয়েছে
            </span>
        </div>
        <h3 class="fw-bold text-dark mb-0">ওয়েবসাইট সেটিংস ও কনফিগারেশন</h3>
        <p class="text-muted small mb-0">ফার্মার্সবিডি প্ল্যাটফর্মের মূল সাধারণ তথ্য, হোমপেজ, স্লাইডার, ফন্ট, কালার থিম, এসইও এবং AI সার্ভিস পরিচালনা করুন।</p>
    </div>

    <div class="d-flex align-items-center gap-2">
        <button type="button" id="btnResetSettings" class="btn btn-outline-secondary px-3">
            <i class="bi bi-arrow-counterclockwise me-1"></i> রিসেট
        </button>
        <button type="button" id="btnSaveSettings" class="btn btn-teal px-4 shadow-sm fw-bold text-white" style="background:#0d9488; border-color:#0d9488;">
            <i class="bi bi-save me-1"></i> পরিবর্তন সংরক্ষণ করুন
        </button>
    </div>
</div>

<!-- ── Settings Navigation & Workspace Layout ──────────────── -->
<div class="row g-4">
    <!-- Left Navigation Sidebar (9 Categories) -->
    <div class="col-lg-3 col-md-4">
        <div class="admin-card sticky-top" style="top: 80px; z-index: 1000;">
            <div class="admin-card-header bg-light py-3 px-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-grid-fill me-2 text-teal" style="color:#0d9488;"></i> সেটিংস ক্যাটাগরি</h6>
            </div>
            <div class="list-group list-group-flush p-2" id="settingsTabs" role="tablist">
                <button class="list-group-item list-group-item-action d-flex align-items-center gap-2.5 rounded-3 mb-1 active" id="tab-general-btn" data-bs-toggle="pill" data-bs-target="#tab-general" type="button" role="tab">
                    <i class="bi bi-gear-fill fs-5 text-teal" style="color:#0d9488;"></i>
                    <span class="fw-semibold fs-7">১. সাধারণ সেটিংস</span>
                </button>
                <button class="list-group-item list-group-item-action d-flex align-items-center gap-2.5 rounded-3 mb-1" id="tab-homepage-btn" data-bs-toggle="pill" data-bs-target="#tab-homepage" type="button" role="tab">
                    <i class="bi bi-house-door-fill fs-5 text-primary"></i>
                    <span class="fw-semibold fs-7">২. হোমপেজ সেকশনসমূহ</span>
                </button>
                <button class="list-group-item list-group-item-action d-flex align-items-center gap-2.5 rounded-3 mb-1" id="tab-hero-btn" data-bs-toggle="pill" data-bs-target="#tab-hero" type="button" role="tab">
                    <i class="bi bi-images fs-5 text-indigo" style="color:#4f46e5;"></i>
                    <span class="fw-semibold fs-7">৩. হিরো স্লাইডার</span>
                </button>
                <button class="list-group-item list-group-item-action d-flex align-items-center gap-2.5 rounded-3 mb-1" id="tab-typography-btn" data-bs-toggle="pill" data-bs-target="#tab-typography" type="button" role="tab">
                    <i class="bi bi-fonts fs-5 text-amber" style="color:#d97706;"></i>
                    <span class="fw-semibold fs-7">৪. টাইপোগ্রাফি & ফন্ট</span>
                </button>
                <button class="list-group-item list-group-item-action d-flex align-items-center gap-2.5 rounded-3 mb-1" id="tab-theme-btn" data-bs-toggle="pill" data-bs-target="#tab-theme" type="button" role="tab">
                    <i class="bi bi-palette-fill fs-5 text-cyan" style="color:#06b6d4;"></i>
                    <span class="fw-semibold fs-7">৫. কালার ও থিম</span>
                </button>
                <button class="list-group-item list-group-item-action d-flex align-items-center gap-2.5 rounded-3 mb-1" id="tab-footer-btn" data-bs-toggle="pill" data-bs-target="#tab-footer" type="button" role="tab">
                    <i class="bi bi-layout-sidebar-reverse fs-5 text-secondary"></i>
                    <span class="fw-semibold fs-7">৬. ফুটার কনফিগারেশন</span>
                </button>
                <button class="list-group-item list-group-item-action d-flex align-items-center gap-2.5 rounded-3 mb-1" id="tab-social-btn" data-bs-toggle="pill" data-bs-target="#tab-social" type="button" role="tab">
                    <i class="bi bi-share-fill fs-5 text-blue" style="color:#2563eb;"></i>
                    <span class="fw-semibold fs-7">৭. সোশ্যাল মিডিয়া</span>
                </button>
                <button class="list-group-item list-group-item-action d-flex align-items-center gap-2.5 rounded-3 mb-1" id="tab-seo-btn" data-bs-toggle="pill" data-bs-target="#tab-seo" type="button" role="tab">
                    <i class="bi bi-search-heart fs-5 text-sky" style="color:#0284c7;"></i>
                    <span class="fw-semibold fs-7">৮. এসইও (SEO) সেটিংস</span>
                </button>
                <button class="list-group-item list-group-item-action d-flex align-items-center gap-2.5 rounded-3 mb-1" id="tab-ai-btn" data-bs-toggle="pill" data-bs-target="#tab-ai" type="button" role="tab">
                    <i class="bi bi-cpu-fill fs-5 text-purple" style="color:#9333ea;"></i>
                    <span class="fw-semibold fs-7">৯. AI সার্ভিস কনফিগারেশন</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Settings Content Area -->
    <div class="col-lg-9 col-md-8">
        <form id="settingsMasterForm" onchange="markUnsaved()" oninput="markUnsaved()">
            <div class="tab-content" id="settingsTabContent">

                <!-- ============================================================ -->
                <!-- 1. GENERAL SETTINGS TAB -->
                <!-- ============================================================ -->
                <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h5><i class="bi bi-info-circle me-2 text-teal" style="color:#0d9488;"></i> মৌলিক তথ্যাবলি (Basic Information)</h5>
                        </div>
                        <div class="admin-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">ওয়েবসাইটের নাম (Site Name)</label>
                                    <input type="text" class="form-control" name="site_name" value="FarmersBD (ফার্মার্সবিডি)">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">ট্যাগলাইন / স্লোগান (Site Tagline)</label>
                                    <input type="text" class="form-control" name="site_slogan" value="স্মার্ট মৎস্য চাষ ও জলজ প্রযুক্তি প্ল্যাটফর্ম">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">ওয়েবসাইটের বিবরণ (Site Description)</label>
                                    <textarea class="form-control" name="site_description" rows="3">ফার্মার্সবিডি — বাংলাদেশের প্রথম AI চালিত স্মার্ট মৎস্য চাষ, মাছের রোগ নির্ণয় ও একুয়া প্রোডাক্ট অনলাইন প্ল্যাটফর্ম।</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">হেল্পলাইন নম্বর (Phone Number)</label>
                                    <input type="text" class="form-control" name="contact_phone" value="+৮৮০ ১৭০০-০০০০০০">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">অফিসিয়াল ইমেইল (Email Address)</label>
                                    <input type="email" class="form-control" name="contact_email" value="support@farmersbd.com">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">অফিসের ঠিকানা (Business Address)</label>
                                    <textarea class="form-control" name="contact_address" rows="2">লেভেল ৪, অ্যাকুয়াকালচার সেন্টার, মতিঝিল, ঢাকা-১০০০, বাংলাদেশ</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Branding Sub-section -->
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h5><i class="bi bi-image me-2 text-primary"></i> ব্র্যান্ডিং ও লোগো (Branding Assets)</h5>
                        </div>
                        <div class="admin-card-body">
                            <div class="row g-4">
                                <div class="col-md-6 border-end">
                                    <label class="form-label fw-bold mb-2">সাইট লোগো (Site Logo)</label>
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="p-3 bg-light rounded-3 border text-center" style="width: 140px; height: 80px; display:flex; align-items:center; justify-content:center;">
                                            <img id="logoPreview" src="<?= BASE_URL ?>/assets/images/logo.png" alt="Logo" style="max-width:100%; max-height:50px; object-fit:contain;" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'40\'><rect width=\'100\' height=\'40\' fill=\'%230d9488\'/><text x=\'50%25\' y=\'60%25\' fill=\'white\' font-weight=\'bold\' text-anchor=\'middle\'>FarmersBD</text></svg>'">
                                        </div>
                                        <div>
                                            <label class="btn btn-sm btn-outline-primary mb-1 d-block" for="logoInput">
                                                <i class="bi bi-upload me-1"></i> আপলোড করুন
                                            </label>
                                            <input type="file" id="logoInput" class="d-none" accept="image/*" onchange="previewImage(this, 'logoPreview')">
                                            <button type="button" class="btn btn-sm btn-light border text-danger" onclick="removeImage('logoPreview')">
                                                <i class="bi bi-trash me-1"></i> মুছুন
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block">পরামর্শকৃত সাইজ: ২৫০×৬০ পিক্সেল (PNG/WEBP)</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold mb-2">ফেভিকন (Favicon)</label>
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="p-2 bg-light rounded-3 border text-center" style="width: 60px; height: 60px; display:flex; align-items:center; justify-content:center;">
                                            <img id="faviconPreview" src="<?= BASE_URL ?>/assets/images/favicon.png" alt="Favicon" style="width:32px; height:32px; object-fit:contain;" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'32\' height=\'32\'><circle cx=\'16\' cy=\'16\' r=\'14\' fill=\'%230d9488\'/><text x=\'50%25\' y=\'65%25\' fill=\'white\' font-size=\'16\' font-weight=\'bold\' text-anchor=\'middle\'>F</text></svg>'">
                                        </div>
                                        <div>
                                            <label class="btn btn-sm btn-outline-primary mb-1 d-block" for="faviconInput">
                                                <i class="bi bi-upload me-1"></i> আপলোড
                                            </label>
                                            <input type="file" id="faviconInput" class="d-none" accept="image/*" onchange="previewImage(this, 'faviconPreview')">
                                            <button type="button" class="btn btn-sm btn-light border text-danger" onclick="removeImage('faviconPreview')">
                                                <i class="bi bi-trash me-1"></i> মুছুন
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block">পরামর্শকৃত সাইজ: ৩২×৩২ পিক্সেল (ICO/PNG)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status & Maintenance Mode Sub-section -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h5><i class="bi bi-power me-2 text-warning"></i> ওয়েবসাইট অ্যাক্সেস ও মেইনটেন্যান্স মোড</h5>
                        </div>
                        <div class="admin-card-body">
                            <div class="row g-4">
                                <div class="col-md-6 border-end">
                                    <div class="form-check form-switch fs-5">
                                        <input class="form-check-input" type="checkbox" id="siteActiveToggle" checked>
                                        <label class="form-check-label fs-6 fw-bold text-dark" for="siteActiveToggle">ওয়েবসাইট সচল রাখুন (Website Active)</label>
                                    </div>
                                    <p class="text-muted small mt-1 mb-0">বন্ধ থাকলে সাধারণ ব্যবহারকারীরা ওয়েবসাইটটি দেখতে পাবেন না।</p>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch fs-5">
                                        <input class="form-check-input" type="checkbox" id="maintModeToggle" onchange="toggleMaintenanceMode(this)">
                                        <label class="form-check-label fs-6 fw-bold text-dark" for="maintModeToggle">মেইনটেন্যান্স মোড (Maintenance Mode)</label>
                                    </div>
                                    <p class="text-muted small mt-1 mb-0">সক্রিয় করলে সাধারণ ভিজিটরদের কাছে মেইনটেন্যান্স বার্তা প্রদর্শিত হবে।</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- 2. HOMEPAGE SETTINGS TAB -->
                <!-- ============================================================ -->
                <div class="tab-pane fade" id="tab-homepage" role="tabpanel">
                    <div class="admin-card mb-4">
                        <div class="admin-card-header d-flex justify-content-between align-items-center">
                            <h5><i class="bi bi-window-sidebar me-2 text-primary"></i> হোমপেজের ১২টি মূল সেকশন পরিচালনা করুন</h5>
                            <span class="badge bg-light text-secondary border">সহজ সেকশন কন্ট্রোল</span>
                        </div>
                        <div class="admin-card-body">
                            <p class="text-muted small mb-4">নিচের তালিকা থেকে হোমপেজের প্রতিটি সেকশন চালু/বন্ধ করুন, শিরোনাম এবং প্রদর্শনের ক্রম সাজান।</p>

                            <div class="accordion" id="homepageSectionsAccordion">
                                <?php
                                $hp_sections = [
                                    ['id' => 'hero',        'name' => '১. হিরো স্লাইডার (Hero Slider)',         'title' => 'স্মার্ট মৎস্য চাষে আপনার ডিজিটাল সঙ্গী', 'desc' => 'কৃত্রিম বুদ্ধিমত্তা দিয়ে মাছের রোগ নির্ণয় ও সমাধান পান', 'btn' => 'রোগ নির্ণয় করুন', 'url' => '/ai/diagnosis.php'],
                                    ['id' => 'services',    'name' => '২. সেবাসমূহ (Smart Services)',           'title' => 'আমাদের বিশেষায়িত সেবা', 'desc' => 'মৎস্য চাষীদের জন্য আধুনিক প্রযুক্তির সমন্বয়', 'btn' => 'সব সেবা দেখুন', 'url' => '/services/'],
                                    ['id' => 'fish',        'name' => '৩. জনপ্রিয় মাছের জাত (Popular Fish)',       'title' => 'বাণিজ্যিক মাছের জাত ও নির্দেশিকা', 'desc' => 'সঠিক জাত নির্বাচন ও পুকুর প্রস্তুতের পরামর্শ', 'btn' => 'জাতের তালিকা', 'url' => '/fish/'],
                                    ['id' => 'ai_detector', 'name' => '৪. AI রোগ নির্ণয় (AI Disease Detection)',  'title' => 'তাত্ক্ষণিক AI মাছের রোগ নির্ণয়', 'desc' => 'রোগের ছবি আপলোড করুন এবং ৯৮% নির্ভুল পরামর্শ পান', 'btn' => 'এখনই স্ক্যান করুন', 'url' => '/ai/diagnosis.php'],
                                    ['id' => 'pharmacy',    'name' => '৫. একুয়া ফার্মেসি (Aqua Pharmacy)',       'title' => 'অনলাইন একুয়া মেডিসিন শপ', 'desc' => 'প্রমাণিত মৎস্য ঔষধ ও পানি শোধনকারী কেমিক্যাল', 'btn' => 'ঔষধ কিনুন', 'url' => '/shop/'],
                                    ['id' => 'featured_prod','name' => '৬. ফিচার্ড প্রোডাক্টস (Featured Products)','title' => 'সেরা বিক্রিত একুয়া প্রোডাক্টসমূহ', 'desc' => 'খামারিদের পছন্দের শীর্ষে থাকা প্রডাক্টস', 'btn' => 'সব প্রোডাক্টস', 'url' => '/products/'],
                                    ['id' => 'blogs',       'name' => '৭. সর্বশেষ ব্লগ (Recent Blogs)',           'title' => 'মৎস্য চাষের আধুনিক টিপস ও নিবন্ধ', 'desc' => 'বিশেষজ্ঞদের পরামর্শ ও চাষের নতুন কৌশল', 'btn' => 'ব্লগ পড়ুন', 'url' => '/blogs/'],
                                    ['id' => 'why_us',      'name' => '৮. কেন ফার্মার্সবিডি? (Why Choose Us)',   'title' => 'খামারিদের প্রথম পছন্দ ফার্মার্সবিডি', 'desc' => '১০,০০০+ সফল খামারির নির্ভরযোগ্য সঙ্গী', 'btn' => 'বিস্তারিত জানুন', 'url' => '/about/'],
                                    ['id' => 'expert',      'name' => '৯. বিশেষজ্ঞ পরামর্শ (Expert Consultation)',  'title' => 'সরাসরি মৎস্য ডাক্তারের পরামর্শ নিন', 'desc' => 'অভিজ্ঞ ডাক্তারের কাছ থেকে খামার সমাধান পান', 'btn' => 'প্রশ্ন করুন', 'url' => '/consultation/'],
                                    ['id' => 'faq',         'name' => '১০. সাধারণ জিজ্ঞাসাবলি (FAQ)',              'title' => 'সচরাচর জিজ্ঞাসিত প্রশ্নাবলি', 'desc' => 'আপনার প্রশ্নের দ্রুত সমাধান পান', 'btn' => 'আরও জানুন', 'url' => '/faq/'],
                                    ['id' => 'newsletter',  'name' => '১১. নিউজলেটার (Newsletter)',              'title' => 'মৎস্য খবরের সাথে যুক্ত থাকুন', 'desc' => 'প্রতি সপ্তাহে বিনামূল্যে টিপস ও অফার আপডেট পান', 'btn' => 'সাবস্ক্রাইব', 'url' => '#'],
                                    ['id' => 'contact',     'name' => '১২. যোগাযোগ সেকশন (Contact Us)',            'title' => 'আমাদের সাথে সরাসরি যোগাযোগ করুন', 'desc' => 'যেকোনো প্রয়োজনে আমরা আপনার পাশে আছি', 'btn' => 'মেসেজ দিন', 'url' => '/contact/'],
                                ];
                                foreach ($hp_sections as $idx => $sec): ?>
                                <div class="accordion-item mb-2 border rounded-3 overflow-hidden">
                                    <h2 class="accordion-header" id="heading-<?= $sec['id'] ?>">
                                        <button class="accordion-button collapsed py-2.5 px-3 bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $sec['id'] ?>">
                                            <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                                <span class="fw-bold text-dark fs-7"><?= $sec['name'] ?></span>
                                                <div class="form-check form-switch mb-0" onclick="event.stopPropagation();">
                                                    <input class="form-check-input" type="checkbox" id="toggle-sec-<?= $sec['id'] ?>" checked>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse-<?= $sec['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#homepageSectionsAccordion">
                                        <div class="accordion-body p-3 bg-white">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium fs-7">সেকশন টাইটেল (Title)</label>
                                                    <input type="text" class="form-control form-control-sm" name="sec_title_<?= $sec['id'] ?>" value="<?= e($sec['title']) ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium fs-7">সেকশন বিবরণ (Description)</label>
                                                    <input type="text" class="form-control form-control-sm" name="sec_desc_<?= $sec['id'] ?>" value="<?= e($sec['desc']) ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-medium fs-7">বাটন টেক্সট</label>
                                                    <input type="text" class="form-control form-control-sm" name="sec_btn_<?= $sec['id'] ?>" value="<?= e($sec['btn']) ?>">
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label fw-medium fs-7">বাটন লিঙ্ক (URL)</label>
                                                    <input type="text" class="form-control form-control-sm" name="sec_url_<?= $sec['id'] ?>" value="<?= e($sec['url']) ?>">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-medium fs-7">প্রদর্শনের ক্রম (Order)</label>
                                                    <input type="number" class="form-control form-control-sm" name="sec_order_<?= $sec['id'] ?>" value="<?= $idx + 1 ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- 3. HERO SLIDER SETTINGS TAB -->
                <!-- ============================================================ -->
                <div class="tab-pane fade" id="tab-hero" role="tabpanel">
                    <!-- Hero Color Rule Reminder Banner -->
                    <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-3" style="background:#e0f2fe; color:#0369a1;">
                        <i class="bi bi-shield-check fs-2 text-cyan" style="color:#0891b2;"></i>
                        <div>
                            <h6 class="fw-bold mb-1">গুরুত্বপূর্ণ হিরো ইমেজ প্রসেসিং নিয়ম (Hero Image Rule)</h6>
                            <p class="small mb-0">হিরো স্লাইডারের ছবিসমূহ সবসময় অরিজিনাল কালার ও স্বচ্ছতায় প্রদর্শিত হবে। কোনো কৃত্রিম ওভারলে, ডার্ক ফিল্টার, গ্রেডিয়েন্ট বা কালার টিন্ট প্রয়োগ করা হবে না।</p>
                        </div>
                    </div>

                    <!-- Slide Management List -->
                    <div class="admin-card mb-4">
                        <div class="admin-card-header d-flex justify-content-between align-items-center">
                            <h5><i class="bi bi-images me-2 text-indigo" style="color:#4f46e5;"></i> হিরো স্লাইড সমূহ (Slide Manager)</h5>
                            <button type="button" class="btn btn-sm btn-teal text-white" style="background:#0d9488;" data-bs-toggle="modal" data-bs-target="#slideModal" onclick="openAddSlideModal()">
                                <i class="bi bi-plus-lg me-1"></i> নতুন স্লাইড যোগ করুন
                            </button>
                        </div>
                        <div class="admin-card-body p-0">
                            <div class="table-responsive">
                                <table class="admin-table align-middle">
                                    <thead>
                                        <tr>
                                            <th>ছবি প্রিভিউ</th>
                                            <th>বাংলা শিরোনাম & সাব-টাইটেল</th>
                                            <th>বাটন লিঙ্ক</th>
                                            <th>ক্রম</th>
                                            <th>স্ট্যাটাস</th>
                                            <th class="text-end">অ্যাকশন</th>
                                        </tr>
                                    </thead>
                                    <tbody id="heroSlideTableBody">
                                        <tr>
                                            <td>
                                                <img src="<?= BASE_URL ?>/assets/images/hero-1.jpg" class="rounded border" style="width:90px; height:50px; object-fit:cover;" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'90\' height=\'50\' fill=\'%230369a1\'><text x=\'50%25\' y=\'60%25\' fill=\'white\' font-size=\'10\' text-anchor=\'middle\'>Original Fish</text></svg>'">
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark">স্মার্ট মৎস্য চাষে আপনার ডিজিটাল সঙ্গী</div>
                                                <small class="text-muted">কৃত্রিম বুদ্ধিমত্তা দিয়ে মাছের রোগ নির্ণয় করুন</small>
                                            </td>
                                            <td><span class="badge bg-light text-dark border">/ai/diagnosis.php</span></td>
                                            <td><span class="fw-bold">১</span></td>
                                            <td><span class="badge bg-success-subtle text-success border border-success-subtle">সক্রিয়</span></td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="openEditSlideModal(1)"><i class="bi bi-pencil"></i></button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteSlide(this)"><i class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <img src="<?= BASE_URL ?>/assets/images/hero-2.jpg" class="rounded border" style="width:90px; height:50px; object-fit:cover;" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'90\' height=\'50\' fill=\'%230d9488\'><text x=\'50%25\' y=\'60%25\' fill=\'white\' font-size=\'10\' text-anchor=\'middle\'>Aqua Medicine</text></svg>'">
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark">প্রমাণিত একুয়া মেডিসিন ও কেমিক্যাল</div>
                                                <small class="text-muted">সরাসরি প্রস্তুতকারক থেকে খাঁটি ঔষধ সংগ্রহ করুন</small>
                                            </td>
                                            <td><span class="badge bg-light text-dark border">/shop/</span></td>
                                            <td><span class="fw-bold">২</span></td>
                                            <td><span class="badge bg-success-subtle text-success border border-success-subtle">সক্রিয়</span></td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="openEditSlideModal(2)"><i class="bi bi-pencil"></i></button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteSlide(this)"><i class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Slider Playback Controls -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h5><i class="bi bi-sliders2 me-2 text-teal" style="color:#0d9488;"></i> স্লাইডার আচরণ ও কন্ট্রোলস (Slider Controls)</h5>
                        </div>
                        <div class="admin-card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="form-check form-switch fs-5">
                                        <input class="form-check-input" type="checkbox" id="sliderAutoplay" checked>
                                        <label class="form-check-label fs-6 fw-medium" for="sliderAutoplay">অটো-প্লে (Autoplay)</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-medium fs-7">অটো-প্লে সময়সীমা (মিলিসেকেন্ড)</label>
                                    <input type="number" class="form-control" name="slider_interval" value="5000" step="500">
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch fs-5">
                                        <input class="form-check-input" type="checkbox" id="sliderArrows" checked>
                                        <label class="form-check-label fs-6 fw-medium" for="sliderArrows">নেভিগেশন অ্যারো প্রদর্শন</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch fs-5">
                                        <input class="form-check-input" type="checkbox" id="sliderDots" checked>
                                        <label class="form-check-label fs-6 fw-medium" for="sliderDots">ইন্ডিকেটর ডটস প্রদর্শন</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- 4. TYPOGRAPHY SETTINGS TAB -->
                <!-- ============================================================ -->
                <div class="tab-pane fade" id="tab-typography" role="tabpanel">
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h5><i class="bi bi-fonts me-2 text-amber" style="color:#d97706;"></i> ফন্ট নির্বাচন ও আকার (Typography & Font System)</h5>
                        </div>
                        <div class="admin-card-body">
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">প্রাইমারি ফন্ট (Primary Font)</label>
                                    <select class="form-select" id="fontPrimary" onchange="updateTypoPreview()">
                                        <option value="Noto Sans Bengali" selected>Noto Sans Bengali (ডিফল্ট)</option>
                                        <option value="Kalpurush">Kalpurush (কালপুরুষ)</option>
                                        <option value="SolaimanLipi">SolaimanLipi (সোলাইমান লিপি)</option>
                                        <option value="Hind Siliguri">Hind Siliguri (হিন্দ শিলিগুড়ি)</option>
                                        <option value="Roboto">Roboto / System Sans</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">হেডিং ফন্ট (Heading Font)</label>
                                    <select class="form-select" id="fontHeading" onchange="updateTypoPreview()">
                                        <option value="Noto Sans Bengali" selected>Noto Sans Bengali</option>
                                        <option value="Hind Siliguri">Hind Siliguri</option>
                                        <option value="Kalpurush">Kalpurush</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">বাংলা ফন্ট (Bangla Script Font)</label>
                                    <select class="form-select" id="fontBangla" onchange="updateTypoPreview()">
                                        <option value="Noto Sans Bengali" selected>Noto Sans Bengali</option>
                                        <option value="SolaimanLipi">SolaimanLipi</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Font Sizes Grid -->
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">ফন্ট সাইজসমূহ (Font Sizes)</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-2 col-4">
                                    <label class="form-label fs-7">H1 হেডিং</label>
                                    <input type="text" class="form-control form-control-sm" name="fs_h1" value="2.25rem">
                                </div>
                                <div class="col-md-2 col-4">
                                    <label class="form-label fs-7">H2 হেডিং</label>
                                    <input type="text" class="form-control form-control-sm" name="fs_h2" value="1.75rem">
                                </div>
                                <div class="col-md-2 col-4">
                                    <label class="form-label fs-7">H3 হেডিং</label>
                                    <input type="text" class="form-control form-control-sm" name="fs_h3" value="1.35rem">
                                </div>
                                <div class="col-md-2 col-4">
                                    <label class="form-label fs-7">H4 হেডিং</label>
                                    <input type="text" class="form-control form-control-sm" name="fs_h4" value="1.15rem">
                                </div>
                                <div class="col-md-2 col-4">
                                    <label class="form-label fs-7">মূল বডি টেক্সট</label>
                                    <input type="text" class="form-control form-control-sm" name="fs_body" value="0.95rem">
                                </div>
                                <div class="col-md-2 col-4">
                                    <label class="form-label fs-7">নেভবার টেক্সট</label>
                                    <input type="text" class="form-control form-control-sm" name="fs_nav" value="0.9rem">
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label fs-7">বাটন টেক্সট</label>
                                    <input type="text" class="form-control form-control-sm" name="fs_btn" value="0.9rem">
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label fs-7">কার্ড টাইটেল</label>
                                    <input type="text" class="form-control form-control-sm" name="fs_card_title" value="1.1rem">
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label fs-7">কার্ড বিবরণ</label>
                                    <input type="text" class="form-control form-control-sm" name="fs_card_desc" value="0.85rem">
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label fs-7">ফুটার টেক্সট</label>
                                    <input type="text" class="form-control form-control-sm" name="fs_footer" value="0.85rem">
                                </div>
                            </div>

                            <!-- Weights & Line Heights -->
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">ফন্ট ওয়েট ও লাইন হাইট (Weights & Line Heights)</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fs-7">হেডিং ফন্ট ওয়েট</label>
                                    <select class="form-select form-select-sm" name="weight_heading">
                                        <option value="600">600 (Semi-Bold)</option>
                                        <option value="700" selected>700 (Bold)</option>
                                        <option value="800">800 (Extra Bold)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fs-7">বডি ফন্ট ওয়েট</label>
                                    <select class="form-select form-select-sm" name="weight_body">
                                        <option value="400" selected>400 (Regular)</option>
                                        <option value="500">500 (Medium)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fs-7">হেডিং লাইন হাইট</label>
                                    <input type="text" class="form-control form-control-sm" name="lh_heading" value="1.3">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fs-7">বডি লাইন হাইট</label>
                                    <input type="text" class="form-control form-control-sm" name="lh_body" value="1.6">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Live Typography Preview Box -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h5><i class="bi bi-eye me-2 text-teal" style="color:#0d9488;"></i> টাইপোগ্রাফি লাইভ প্রিভিউ (Live Preview)</h5>
                        </div>
                        <div class="admin-card-body p-4 bg-light rounded-bottom-3" id="typoPreviewBox" style="font-family: 'Noto Sans Bengali', sans-serif;">
                            <h2 class="fw-bold text-dark mb-2">স্মার্ট মৎস্য চাষ ও আধুনিক খামার সমাধান (H2 Header)</h2>
                            <p class="text-secondary mb-3 fs-6">ফার্মার্সবিডি প্লাটফর্মের মাধ্যমে খামারিরা সহজেই AI রোগ নির্ণয় প্রযুক্তি ব্যবহার করে মাছের যেকোনো জটিল রোগ সনাক্ত করতে পারছেন এবং তাৎক্ষণিক অভিজ্ঞ ডাক্তারের পরামর্শ পাচ্ছেন। (Body Text)</p>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn text-white px-3 py-2 fw-bold" style="background:#0d9488;">রোগ নির্ণয় করুন (Button Text)</button>
                                <span class="badge bg-info-subtle text-info border px-3 py-2">একুয়া কেয়ার সার্ভিস</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- 5. COLORS & THEME SETTINGS TAB -->
                <!-- ============================================================ -->
                <div class="tab-pane fade" id="tab-theme" role="tabpanel">
                    <!-- Strict Color Rule Notice -->
                    <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-3" style="background:#fff7ed; color:#9a3412;">
                        <i class="bi bi-droplet-half fs-2 text-amber" style="color:#d97706;"></i>
                        <div>
                            <h6 class="fw-bold mb-1">অ্যাকুয়াটিক থিম ও কালার পলিসি (Aquatic Identity Policy)</h6>
                            <p class="small mb-0">ফার্মার্সবিডি সম্পূর্ণ অ্যাকুয়াটিক ও ওশান থিম মেনে চলে (Deep Ocean Blue, Deep Teal, Aqua, Sky Blue, Sand Gold)। সবুজ (Green) রঙের ব্যবহার সম্পূর্ণ নিষিদ্ধ।</p>
                        </div>
                    </div>

                    <div class="admin-card mb-4">
                        <div class="admin-card-header d-flex justify-content-between align-items-center">
                            <h5><i class="bi bi-palette me-2 text-cyan" style="color:#06b6d4;"></i> কালার প্যালেট নির্বাচন (Color Palette Picker)</h5>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetThemeColors()">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> ডিফল্ট থিমে রিসেট
                            </button>
                        </div>
                        <div class="admin-card-body">
                            <div class="row g-3">
                                <div class="col-md-3 col-6">
                                    <label class="form-label fs-7 fw-bold">Primary Color (Teal)</label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" class="form-control form-control-color" id="clrPrimary" value="#0d9488" onchange="updateThemePreview()">
                                        <input type="text" class="form-control" id="clrPrimaryHex" value="#0d9488" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label fs-7 fw-bold">Secondary Color (Ocean)</label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" class="form-control form-control-color" id="clrSecondary" value="#0284c7" onchange="updateThemePreview()">
                                        <input type="text" class="form-control" id="clrSecondaryHex" value="#0284c7" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label fs-7 fw-bold">Accent Color (Sand Gold)</label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" class="form-control form-control-color" id="clrAccent" value="#d97706" onchange="updateThemePreview()">
                                        <input type="text" class="form-control" id="clrAccentHex" value="#d97706" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label fs-7 fw-bold">Main Background</label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" class="form-control form-control-color" id="clrBg" value="#f8fafc" onchange="updateThemePreview()">
                                        <input type="text" class="form-control" id="clrBgHex" value="#f8fafc" readonly>
                                    </div>
                                </div>

                                <div class="col-md-3 col-6">
                                    <label class="form-label fs-7 fw-bold">Card Background</label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" class="form-control form-control-color" id="clrCard" value="#ffffff" onchange="updateThemePreview()">
                                        <input type="text" class="form-control" id="clrCardHex" value="#ffffff" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label fs-7 fw-bold">Heading Color (Navy)</label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" class="form-control form-control-color" id="clrHeading" value="#0f172a" onchange="updateThemePreview()">
                                        <input type="text" class="form-control" id="clrHeadingHex" value="#0f172a" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label fs-7 fw-bold">Body Text Color</label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" class="form-control form-control-color" id="clrBody" value="#334155" onchange="updateThemePreview()">
                                        <input type="text" class="form-control" id="clrBodyHex" value="#334155" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label fs-7 fw-bold">Muted Text Color</label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" class="form-control form-control-color" id="clrMuted" value="#64748b" onchange="updateThemePreview()">
                                        <input type="text" class="form-control" id="clrMutedHex" value="#64748b" readonly>
                                    </div>
                                </div>

                                <div class="col-md-3 col-6">
                                    <label class="form-label fs-7 fw-bold">Border Color</label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" class="form-control form-control-color" id="clrBorder" value="#e2e8f0" onchange="updateThemePreview()">
                                        <input type="text" class="form-control" id="clrBorderHex" value="#e2e8f0" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label fs-7 fw-bold">Success Color (Sky)</label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" class="form-control form-control-color" id="clrSuccess" value="#0284c7" onchange="updateThemePreview()">
                                        <input type="text" class="form-control" id="clrSuccessHex" value="#0284c7" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label fs-7 fw-bold">Warning Color (Amber)</label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" class="form-control form-control-color" id="clrWarning" value="#d97706" onchange="updateThemePreview()">
                                        <input type="text" class="form-control" id="clrWarningHex" value="#d97706" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label fs-7 fw-bold">Error Color (Rose)</label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" class="form-control form-control-color" id="clrError" value="#e11d48" onchange="updateThemePreview()">
                                        <input type="text" class="form-control" id="clrErrorHex" value="#e11d48" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Live Theme Preview -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h5><i class="bi bi-laptop me-2 text-teal" style="color:#0d9488;"></i> থিম লাইভ প্রিভিউ কার্ড (Theme Preview)</h5>
                        </div>
                        <div class="admin-card-body p-4" id="themePreviewContainer" style="background:#f8fafc;">
                            <div class="card border rounded-3 p-3 shadow-sm" id="prevCard" style="background:#ffffff; border-color:#e2e8f0;">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge text-white" id="prevBadge" style="background:#0d9488;">একুয়া কেয়ার প্লাস</span>
                                    <span class="small fw-bold" id="prevPrice" style="color:#d97706;"><?= format_price_bn(1250) ?></span>
                                </div>
                                <h5 class="fw-bold mb-1" id="prevHeading" style="color:#0f172a;">পুকুর বিশুদ্ধকরণ ও মাটির শোধক</h5>
                                <p class="small mb-3" id="prevBody" style="color:#334155;">পুকুরের অ্যামোনিয়া ও বিষাক্ত গ্যাস দূর করে মাছের দ্রুত শারীরিক বৃদ্ধিতে সহায়ক।</p>
                                <button type="button" class="btn text-white w-100 fw-bold py-2" id="prevBtn" style="background:#0284c7;">অর্ডার করুন</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- 6. FOOTER SETTINGS TAB -->
                <!-- ============================================================ -->
                <div class="tab-pane fade" id="tab-footer" role="tabpanel">
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h5><i class="bi bi-layout-sidebar-reverse me-2 text-secondary"></i> ফুটার কন্টেন্ট ও কপিরাইট</h5>
                        </div>
                        <div class="admin-card-body">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6 border-end">
                                    <label class="form-label fw-bold">ফুটার লোগো (Footer Logo)</label>
                                    <div class="d-flex align-items-center gap-3">
                                        <img id="footerLogoPreview" src="<?= BASE_URL ?>/assets/images/logo.png" style="max-height:45px;" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'40\'><rect width=\'100\' height=\'40\' fill=\'%230d9488\'/><text x=\'50%25\' y=\'60%25\' fill=\'white\' font-weight=\'bold\' text-anchor=\'middle\'>FarmersBD</text></svg>'">
                                        <label class="btn btn-sm btn-outline-secondary" for="footerLogoInput">আপলোড</label>
                                        <input type="file" id="footerLogoInput" class="d-none" onchange="previewImage(this, 'footerLogoPreview')">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">কপিরাইট নোটিশ (Copyright Text)</label>
                                    <input type="text" class="form-control" name="footer_copyright" value="© ২০২৬ FarmersBD. সর্বস্বত্ব সংরক্ষিত। স্মার্ট মৎস্য প্রযুক্তি প্ল্যাটফর্ম।">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">ফুটার শর্ট বিবরণ (Footer About Text)</label>
                                    <textarea class="form-control" name="footer_about" rows="2">ফার্মার্সবিডি — বাংলাদেশের খামারিদের জন্য বিশ্বস্ত স্মার্ট মাছ চাষ, AI রোগ নিরাময় ও একুয়া ঔষধ সরবরাহের একমাত্র প্ল্যাটফর্ম।</textarea>
                                </div>
                            </div>

                            <!-- Footer Links Manager -->
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">ফুটার নেভিগেশন লিঙ্কসমূহ (Footer Quick Links)</h6>
                            <div class="table-responsive mb-3">
                                <table class="admin-table align-middle">
                                    <thead>
                                        <tr>
                                            <th>লিঙ্কের নাম (Bangla Name)</th>
                                            <th>ইউআরএল (URL)</th>
                                            <th>ক্রম</th>
                                            <th>স্ট্যাটাস</th>
                                            <th class="text-end">অ্যাকশন</th>
                                        </tr>
                                    </thead>
                                    <tbody id="footerLinksTableBody">
                                        <tr>
                                            <td><input type="text" class="form-control form-control-sm" value="আমাদের সম্পর্কে"></td>
                                            <td><input type="text" class="form-control form-control-sm" value="/about/"></td>
                                            <td><input type="number" class="form-control form-control-sm" value="1" style="width:70px;"></td>
                                            <td><input type="checkbox" class="form-check-input" checked></td>
                                            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteRow(this)"><i class="bi bi-trash"></i></button></td>
                                        </tr>
                                        <tr>
                                            <td><input type="text" class="form-control form-control-sm" value="AI রোগ নির্ণয়"></td>
                                            <td><input type="text" class="form-control form-control-sm" value="/ai/diagnosis.php"></td>
                                            <td><input type="number" class="form-control form-control-sm" value="2" style="width:70px;"></td>
                                            <td><input type="checkbox" class="form-check-input" checked></td>
                                            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteRow(this)"><i class="bi bi-trash"></i></button></td>
                                        </tr>
                                        <tr>
                                            <td><input type="text" class="form-control form-control-sm" value="একুয়া ফার্মেসি"></td>
                                            <td><input type="text" class="form-control form-control-sm" value="/shop/"></td>
                                            <td><input type="number" class="form-control form-control-sm" value="3" style="width:70px;"></td>
                                            <td><input type="checkbox" class="form-check-input" checked></td>
                                            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteRow(this)"><i class="bi bi-trash"></i></button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addFooterLinkRow()">
                                <i class="bi bi-plus-circle me-1"></i> নতুন লিঙ্ক যোগ করুন
                            </button>
                        </div>
                    </div>

                    <!-- Live Footer Preview -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h5><i class="bi bi-eye me-2 text-teal" style="color:#0d9488;"></i> ফুটার লাইভ প্রিভিউ (Live Footer Preview)</h5>
                        </div>
                        <div class="admin-card-body bg-dark text-white p-4 rounded-bottom-3" style="background:#0f172a!important;">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-white mb-2">FarmersBD</h6>
                                    <p class="small text-white-50 mb-0">ফার্মার্সবিডি — বাংলাদেশের খামারিদের জন্য বিশ্বস্ত স্মার্ট মাছ চাষ, AI রোগ নিরাময় ও একুয়া ঔষধ সরবরাহের একমাত্র প্ল্যাটফর্ম।</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-white mb-2">দ্রুত লিঙ্কসমূহ</h6>
                                    <ul class="list-inline small text-white-50 mb-0">
                                        <li class="list-inline-item me-3"><a href="#" class="text-white-50 text-decoration-none">আমাদের সম্পর্কে</a></li>
                                        <li class="list-inline-item me-3"><a href="#" class="text-white-50 text-decoration-none">AI রোগ নির্ণয়</a></li>
                                        <li class="list-inline-item me-3"><a href="#" class="text-white-50 text-decoration-none">একুয়া ফার্মেসি</a></li>
                                    </ul>
                                </div>
                            </div>
                            <hr class="border-secondary my-3">
                            <div class="text-center small text-white-50">
                                © ২০২৬ FarmersBD. সর্বস্বত্ব সংরক্ষিত।
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- 7. SOCIAL MEDIA SETTINGS TAB -->
                <!-- ============================================================ -->
                <div class="tab-pane fade" id="tab-social" role="tabpanel">
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h5><i class="bi bi-share me-2 text-blue" style="color:#2563eb;"></i> ৫টি অনুমোদিত সোশ্যাল মিডিয়া প্রোফাইল সংযোগ</h5>
                        </div>
                        <div class="admin-card-body">
                            <p class="text-muted small mb-4">ওয়েবসাইটের ফুটার ও হেডার সেকশনে প্রদর্শনের জন্য অফিসিয়াল সোশ্যাল পেজ লিঙ্ক যুক্ত করুন।</p>

                            <div class="row g-3">
                                <!-- Facebook -->
                                <div class="col-12 p-3 bg-light rounded-3 border">
                                    <div class="row align-items-center g-3">
                                        <div class="col-md-3 d-flex align-items-center gap-2">
                                            <i class="bi bi-facebook fs-3 text-primary"></i>
                                            <span class="fw-bold text-dark fs-6">Facebook (ফেসবুক)</span>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="url" class="form-control form-control-sm" name="social_fb_url" value="https://facebook.com/farmersbd.official" placeholder="https://facebook.com/yourpage">
                                        </div>
                                        <div class="col-md-2 col-8">
                                            <input type="number" class="form-control form-control-sm" name="social_fb_order" value="1" placeholder="Order">
                                        </div>
                                        <div class="col-md-1 col-4 text-end">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="social_fb_active" checked>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Instagram -->
                                <div class="col-12 p-3 bg-light rounded-3 border">
                                    <div class="row align-items-center g-3">
                                        <div class="col-md-3 d-flex align-items-center gap-2">
                                            <i class="bi bi-instagram fs-3 text-danger"></i>
                                            <span class="fw-bold text-dark fs-6">Instagram (ইনস্টাগ্রাম)</span>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="url" class="form-control form-control-sm" name="social_insta_url" value="https://instagram.com/farmersbd.official" placeholder="https://instagram.com/yourprofile">
                                        </div>
                                        <div class="col-md-2 col-8">
                                            <input type="number" class="form-control form-control-sm" name="social_insta_order" value="2" placeholder="Order">
                                        </div>
                                        <div class="col-md-1 col-4 text-end">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="social_insta_active" checked>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- YouTube -->
                                <div class="col-12 p-3 bg-light rounded-3 border">
                                    <div class="row align-items-center g-3">
                                        <div class="col-md-3 d-flex align-items-center gap-2">
                                            <i class="bi bi-youtube fs-3 text-danger"></i>
                                            <span class="fw-bold text-dark fs-6">YouTube (ইউটিউব)</span>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="url" class="form-control form-control-sm" name="social_yt_url" value="https://youtube.com/@farmersbd" placeholder="https://youtube.com/@yourchannel">
                                        </div>
                                        <div class="col-md-2 col-8">
                                            <input type="number" class="form-control form-control-sm" name="social_yt_order" value="3" placeholder="Order">
                                        </div>
                                        <div class="col-md-1 col-4 text-end">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="social_yt_active" checked>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- LinkedIn -->
                                <div class="col-12 p-3 bg-light rounded-3 border">
                                    <div class="row align-items-center g-3">
                                        <div class="col-md-3 d-flex align-items-center gap-2">
                                            <i class="bi bi-linkedin fs-3 text-primary"></i>
                                            <span class="fw-bold text-dark fs-6">LinkedIn (লিঙ্কডইন)</span>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="url" class="form-control form-control-sm" name="social_li_url" value="https://linkedin.com/company/farmersbd" placeholder="https://linkedin.com/company/yourcompany">
                                        </div>
                                        <div class="col-md-2 col-8">
                                            <input type="number" class="form-control form-control-sm" name="social_li_order" value="4" placeholder="Order">
                                        </div>
                                        <div class="col-md-1 col-4 text-end">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="social_li_active" checked>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- X / Twitter -->
                                <div class="col-12 p-3 bg-light rounded-3 border">
                                    <div class="row align-items-center g-3">
                                        <div class="col-md-3 d-flex align-items-center gap-2">
                                            <i class="bi bi-twitter-x fs-3 text-dark"></i>
                                            <span class="fw-bold text-dark fs-6">X / Twitter (টুইটার)</span>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="url" class="form-control form-control-sm" name="social_tw_url" value="https://x.com/farmersbd" placeholder="https://x.com/yourhandle">
                                        </div>
                                        <div class="col-md-2 col-8">
                                            <input type="number" class="form-control form-control-sm" name="social_tw_order" value="5" placeholder="Order">
                                        </div>
                                        <div class="col-md-1 col-4 text-end">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="social_tw_active" checked>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- 8. SEO SETTINGS TAB -->
                <!-- ============================================================ -->
                <div class="tab-pane fade" id="tab-seo" role="tabpanel">
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h5><i class="bi bi-search-heart me-2 text-sky" style="color:#0284c7;"></i> সার্চ ইঞ্জিন অপ্টিমাইজেশন (Homepage SEO)</h5>
                        </div>
                        <div class="admin-card-body">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">এসইও টাইটেল (SEO Title)</label>
                                    <input type="text" class="form-control" id="seoTitleInput" name="seo_title" value="FarmersBD — স্মার্ট মৎস্য চাষ ও AI মাছের রোগ নিরাময় প্ল্যাটফর্ম" oninput="updateSeoPreview()">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">ক্যানোনিকাল ইউআরএল (Canonical URL)</label>
                                    <input type="url" class="form-control" id="seoUrlInput" name="seo_canonical" value="https://farmersbd.com" oninput="updateSeoPreview()">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">মেটা ডেসক্রিপশন (Meta Description)</label>
                                    <textarea class="form-control" id="seoDescInput" name="seo_description" rows="3" oninput="updateSeoPreview()">ফার্মার্সবিডি — বাংলাদেশের প্রথম AI চালিত মাছের রোগ সনাক্তকরণ এবং অভিজ্ঞ ডাক্তারদের পরামর্শের মাধ্যমে পুকুরের পানি শোধন ও খাঁটি ঔষধ সরবরাহের অনলাইন কেন্দ্র।</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">মেটা কীওয়ার্ডস (Keywords)</label>
                                    <input type="text" class="form-control" name="seo_keywords" value="মাছ চাষ, মাছের রোগ নির্ণয়, AI রোগ সনাক্তকরণ, একুয়া মেডিসিন, রুই মাছের রোগ, FarmersBD">
                                </div>
                            </div>

                            <!-- Open Graph Social Sharing -->
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">সোশ্যাল শেয়ারিং মেটা (Open Graph Social Meta)</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fs-7">OG Title (ফেসবুক শেয়ার টাইটেল)</label>
                                    <input type="text" class="form-control form-control-sm" name="og_title" value="FarmersBD — স্মার্ট মৎস্য চাষ সমাধান">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-7">OG Description (শেয়ার বিবরণ)</label>
                                    <input type="text" class="form-control form-control-sm" name="og_desc" value="আপনার পুকুরের মাছের রোগ সনাক্ত করুন ছবি তুলে এবং পান তাৎক্ষণিক চিকিৎসা।">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fs-7">OG Image URL (সোশ্যাল শেয়ারিং ছবি)</label>
                                    <input type="text" class="form-control form-control-sm" name="og_image" value="https://farmersbd.com/assets/images/og-share.jpg">
                                </div>
                            </div>

                            <!-- Search Engine Verification -->
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">সার্চ ইঞ্জিন ভেরিফিকেশন (Webmaster Verification Codes)</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fs-7">Google Search Console Verification Code</label>
                                    <input type="text" class="form-control form-control-sm font-monospace" name="verify_google" value="google-site-verification=abc123xyz890">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-7">Bing Webmaster Verification Code</label>
                                    <input type="text" class="form-control form-control-sm font-monospace" name="verify_bing" value="MSBING-VERIFY-887766">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Live Google Search Result Preview -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h5><i class="bi bi-google me-2 text-primary"></i> গুগল সার্চ রেজাল্ট প্রিভিউ (Google Search SERP Preview)</h5>
                        </div>
                        <div class="admin-card-body p-4 bg-light rounded-bottom-3">
                            <div class="p-3 bg-white border rounded-3 shadow-sm" style="max-width: 650px;">
                                <div class="d-flex align-items-center gap-2 text-muted small mb-1">
                                    <img src="<?= BASE_URL ?>/assets/images/favicon.png" style="width:16px; height:16px;" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'16\' height=\'16\'><circle cx=\'8\' cy=\'8\' r=\'8\' fill=\'%230d9488\'/></svg>'">
                                    <span id="serpUrl" class="text-truncate">https://farmersbd.com</span>
                                </div>
                                <h5 id="serpTitle" class="fw-bold text-primary mb-1 text-truncate" style="color:#1a0dab!important; font-size:1.1rem;">FarmersBD — স্মার্ট মৎস্য চাষ ও AI মাছের রোগ নিরাময় প্ল্যাটফর্ম</h5>
                                <p id="serpDesc" class="text-secondary small mb-0 line-clamp-2" style="color:#4d5156!important;">ফার্মার্সবিডি — বাংলাদেশের প্রথম AI চালিত মাছের রোগ সনাক্তকরণ এবং অভিজ্ঞ ডাক্তারদের পরামর্শের মাধ্যমে পুকুরের পানি শোধন ও খাঁটি ঔষধ সরবরাহের অনলাইন কেন্দ্র।</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- 9. AI SETTINGS TAB -->
                <!-- ============================================================ -->
                <div class="tab-pane fade" id="tab-ai" role="tabpanel">
                    <!-- Pre-trained Model Note Alert -->
                    <div class="alert alert-primary border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-3" style="background:#e0f2fe; color:#0369a1;">
                        <i class="bi bi-cpu-fill fs-2 text-purple" style="color:#9333ea;"></i>
                        <div>
                            <h6 class="fw-bold mb-1">বিদ্যমান প্রি-ট্রেইনড AI মডেল সংযোগ (AI Service Config)</h6>
                            <p class="small mb-0">ফার্মার্সবিডি একটি বিদ্যমান প্রি-ট্রেইনড AI ফিশ ডিসিজ ডিটেকশন মডেল ব্যবহার করে। নিচের সেটিংসসমূহ পরবর্তীতে ব্যাকএন্ডের মাধ্যমে AI সার্ভিস এপিআই-এর সাথে সংযুক্ত করা হবে।</p>
                        </div>
                    </div>

                    <div class="admin-card mb-4">
                        <div class="admin-card-header d-flex justify-content-between align-items-center">
                            <h5><i class="bi bi-robot me-2 text-purple" style="color:#9333ea;"></i> AI রোগ নির্ণয় সার্ভিস কনফিগারেশন</h5>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="aiServiceEnabled" checked>
                                <label class="form-check-label fw-bold text-dark ms-1" for="aiServiceEnabled">AI সার্ভিস সক্রিয় (Enabled)</label>
                            </div>
                        </div>
                        <div class="admin-card-body">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">এআই সার্ভিস এপিআই এন্ডপয়েন্ট (AI Service Endpoint URL)</label>
                                    <input type="url" class="form-control" name="ai_api_url" value="https://api.farmersbd.com/v1/ai-disease-detector">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">এপিআই সিক্রেট কি (Masked API Key)</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control font-monospace" id="aiApiKey" name="ai_api_key" value="fb_live_ai_secret_key_8899221100xx">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('aiApiKey', this)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">রিকোয়েস্ট টাইমআউট (Timeout in Seconds)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="ai_timeout" value="30">
                                        <span class="input-group-text">সেকেন্ড</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">সর্বোচ্চ ইমেজ সাইজ (Max Image Size)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="ai_max_size" value="5">
                                        <span class="input-group-text">মেগাবাইট (MB)</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">অনুমোদিত ইমেজ ফরম্যাট (Allowed Image Formats)</label>
                                    <div class="d-flex align-items-center gap-3 pt-2">
                                        <div class="form-check"><input class="form-check-input" type="checkbox" checked id="fmtJpg"><label class="form-check-label fs-7" for="fmtJpg">JPG</label></div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" checked id="fmtJpeg"><label class="form-check-label fs-7" for="fmtJpeg">JPEG</label></div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" checked id="fmtPng"><label class="form-check-label fs-7" for="fmtPng">PNG</label></div>
                                        <div class="form-check"><input class="form-check-input" type="checkbox" checked id="fmtWebp"><label class="form-check-label fs-7" for="fmtWebp">WEBP</label></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<!-- ── Slide Modal (For Hero Slider Tab) ────────────────────── -->
<div class="modal fade" id="slideModal" tabindex="-1" aria-labelledby="slideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-header-title fw-bold mb-0 text-dark" id="slideModalTitle">নতুন হিরো স্লাইড যোগ করুন</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="slideForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">ডেক্সটপ ইমেজ (Desktop Image)</label>
                            <input type="file" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">মোবাইল ইমেজ (Mobile Image)</label>
                            <input type="file" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">বাংলা টাইটেল (Title) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="slideTitleInput" required placeholder="যেমন: স্মার্ট মৎস্য চাষে আপনার ডিজিটাল সঙ্গী">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">বাংলা সাব-টাইটেল (Subtitle)</label>
                            <input type="text" class="form-control" id="slideSubtitleInput" placeholder="যেমন: AI দিয়ে রোগ নির্ণয় করুন">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">বাটন টেক্সট (Button Text)</label>
                            <input type="text" class="form-control" id="slideBtnTextInput" value="রোগ নির্ণয় করুন">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">বাটন লিঙ্ক (Button URL)</label>
                            <input type="text" class="form-control" id="slideBtnUrlInput" value="/ai/diagnosis.php">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">প্রদর্শন ক্রম (Display Order)</label>
                            <input type="number" class="form-control" id="slideOrderInput" value="3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">স্ট্যাটাস (Status)</label>
                            <select class="form-select" id="slideStatusSelect">
                                <option value="active" selected>সক্রিয় (Active)</option>
                                <option value="inactive">নিষ্ক্রিয় (Inactive)</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top bg-light">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">বাতিল</button>
                <button type="button" class="btn btn-teal text-white px-4 fw-bold" style="background:#0d9488;" onclick="saveSlideModal()">সংরক্ষণ করুন</button>
            </div>
        </div>
    </div>
</div>

<!-- ── Settings Interactive Frontend Script ─────────────────── -->
<script>
let isUnsaved = false;

function markUnsaved() {
    isUnsaved = true;
    const badge = document.getElementById('unsavedBadge');
    if (badge) badge.classList.remove('d-none');
}

function clearUnsaved() {
    isUnsaved = false;
    const badge = document.getElementById('unsavedBadge');
    if (badge) badge.classList.add('d-none');
}

// Unsaved changes alert before unloading page
window.addEventListener('beforeunload', function (e) {
    if (isUnsaved) {
        e.preventDefault();
        e.returnValue = 'আপনার অসংরক্ষিত পরিবর্তন রয়েছে। আপনি কি নিশ্চিত যে স্থান ত্যাগ করতে চান?';
    }
});

// Image preview handler
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
            markUnsaved();
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage(previewId) {
    document.getElementById(previewId).src = 'data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'40\'><rect width=\'100\' height=\'40\' fill=\'%23cbd5e1\'/><text x=\'50%25\' y=\'60%25\' fill=\'white\' font-size=\'12\' text-anchor=\'middle\'>No Image</text></svg>';
    markUnsaved();
}

// Maintenance Mode Toggle Confirm
function toggleMaintenanceMode(checkbox) {
    if (checkbox.checked) {
        if (!confirm('আপনি কি নিশ্চিত যে মেইনটেন্যান্স মোড সক্রিয় করতে চান? এটি সক্রিয় থাকলে সাধারণ ভিজিটররা সাইটে প্রবেশ করতে পারবেন না।')) {
            checkbox.checked = false;
            return;
        }
    }
    markUnsaved();
}

// Typography Live Preview Update
function updateTypoPreview() {
    const font = document.getElementById('fontPrimary').value;
    const box = document.getElementById('typoPreviewBox');
    if (box) {
        box.style.fontFamily = `'${font}', sans-serif`;
    }
    markUnsaved();
}

// Colors & Theme Live Preview Update
function updateThemePreview() {
    const p = document.getElementById('clrPrimary').value;
    const s = document.getElementById('clrSecondary').value;
    const a = document.getElementById('clrAccent').value;
    const bg = document.getElementById('clrBg').value;
    const card = document.getElementById('clrCard').value;
    const h = document.getElementById('clrHeading').value;
    const b = document.getElementById('clrBody').value;
    const border = document.getElementById('clrBorder').value;

    document.getElementById('clrPrimaryHex').value = p;
    document.getElementById('clrSecondaryHex').value = s;
    document.getElementById('clrAccentHex').value = a;
    document.getElementById('clrBgHex').value = bg;
    document.getElementById('clrCardHex').value = card;
    document.getElementById('clrHeadingHex').value = h;
    document.getElementById('clrBodyHex').value = b;
    document.getElementById('clrBorderHex').value = border;

    // Update Preview Elements
    const prevCont = document.getElementById('themePreviewContainer');
    const prevCard = document.getElementById('prevCard');
    const prevBadge = document.getElementById('prevBadge');
    const prevPrice = document.getElementById('prevPrice');
    const prevHeading = document.getElementById('prevHeading');
    const prevBody = document.getElementById('prevBody');
    const prevBtn = document.getElementById('prevBtn');

    if (prevCont) prevCont.style.background = bg;
    if (prevCard) { prevCard.style.background = card; prevCard.style.borderColor = border; }
    if (prevBadge) prevBadge.style.background = p;
    if (prevPrice) prevPrice.style.color = a;
    if (prevHeading) prevHeading.style.color = h;
    if (prevBody) prevBody.style.color = b;
    if (prevBtn) prevBtn.style.background = s;

    markUnsaved();
}

function resetThemeColors() {
    document.getElementById('clrPrimary').value = '#0d9488';
    document.getElementById('clrSecondary').value = '#0284c7';
    document.getElementById('clrAccent').value = '#d97706';
    document.getElementById('clrBg').value = '#f8fafc';
    document.getElementById('clrCard').value = '#ffffff';
    document.getElementById('clrHeading').value = '#0f172a';
    document.getElementById('clrBody').value = '#334155';
    document.getElementById('clrMuted').value = '#64748b';
    document.getElementById('clrBorder').value = '#e2e8f0';
    document.getElementById('clrSuccess').value = '#0284c7';
    document.getElementById('clrWarning').value = '#d97706';
    document.getElementById('clrError').value = '#e11d48';
    updateThemePreview();
}

// SEO Google SERP Live Preview Update
function updateSeoPreview() {
    const title = document.getElementById('seoTitleInput').value;
    const url = document.getElementById('seoUrlInput').value;
    const desc = document.getElementById('seoDescInput').value;

    document.getElementById('serpTitle').innerText = title || 'FarmersBD Title';
    document.getElementById('serpUrl').innerText = url || 'https://farmersbd.com';
    document.getElementById('serpDesc').innerText = desc || 'Meta Description...';
    markUnsaved();
}

// Masked API Password Toggle
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

// Slide Manager Modal Handlers
function openAddSlideModal() {
    document.getElementById('slideModalTitle').innerText = 'নতুন হিরো স্লাইড যোগ করুন';
    document.getElementById('slideTitleInput').value = '';
    document.getElementById('slideSubtitleInput').value = '';
}

function openEditSlideModal(id) {
    document.getElementById('slideModalTitle').innerText = 'হিরো স্লাইড সম্পাদনা করুন (ID: ' + id + ')';
    document.getElementById('slideTitleInput').value = 'স্মার্ট মৎস্য চাষে আপনার ডিজিটাল সঙ্গী';
    document.getElementById('slideSubtitleInput').value = 'কৃত্রিম বুদ্ধিমত্তা দিয়ে মাছের রোগ নির্ণয় করুন';
    const modal = new bootstrap.Modal(document.getElementById('slideModal'));
    modal.show();
}

function saveSlideModal() {
    const modalEl = document.getElementById('slideModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
    showToast('স্লাইড তথ্য সফলভাবে আপডেট হয়েছে।');
    markUnsaved();
}

function deleteSlide(btn) {
    if (confirm('আপনি কি নিশ্চিত যে এই স্লাইডটি মুছে ফেলতে চান?')) {
        const row = btn.closest('tr');
        if (row) row.remove();
        showToast('স্লাইড মুছে ফেলা হয়েছে।');
        markUnsaved();
    }
}

// Footer Link Dynamic Rows
function addFooterLinkRow() {
    const tbody = document.getElementById('footerLinksTableBody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" class="form-control form-control-sm" placeholder="লিঙ্কের নাম"></td>
        <td><input type="text" class="form-control form-control-sm" placeholder="/url"></td>
        <td><input type="number" class="form-control form-control-sm" value="${tbody.children.length + 1}" style="width:70px;"></td>
        <td><input type="checkbox" class="form-check-input" checked></td>
        <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteRow(this)"><i class="bi bi-trash"></i></button></td>
    `;
    tbody.appendChild(tr);
    markUnsaved();
}

function deleteRow(btn) {
    btn.closest('tr').remove();
    markUnsaved();
}

// Toast Feedback Helper
function showToast(msg) {
    document.getElementById('toastMessage').innerHTML = '<i class="bi bi-check-circle-fill text-info me-1"></i> ' + msg;
    const toastEl = document.getElementById('settingsToast');
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}

// Save Settings Action Event
document.getElementById('btnSaveSettings').addEventListener('click', function () {
    const btn = this;
    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> সংরক্ষণ করা হচ্ছে...';

    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        clearUnsaved();
        showToast('সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে।');
    }, 800);
});

// Reset Button Event
document.getElementById('btnResetSettings').addEventListener('click', function () {
    if (confirm('আপনি কি পরিবর্তনসমূহ বাতিল করে পূর্ববর্তী অবস্থায় ফিরে যেতে চান?')) {
        document.getElementById('settingsMasterForm').reset();
        clearUnsaved();
        showToast('পরিবর্তনসমূহ বাতিল করা হয়েছে।');
    }
});
</script>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
