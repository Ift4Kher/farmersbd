<?php
// ============================================================
// FarmersBD — Fish Information & Guide Listing Page
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/pagination.php';
require_once dirname(__DIR__) . '/includes/seo.php';

$pdo = Database::getInstance();

$search = sanitize_input($_GET['q'] ?? '');
$category = sanitize_input($_GET['category'] ?? '');
$waterType = sanitize_input($_GET['type'] ?? '');
$sort = sanitize_input($_GET['sort'] ?? 'latest');
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 12;

$where = ["is_active = 1"];
$params = [];

if (!empty($search)) {
    $where[] = "(name LIKE ? OR scientific_name LIKE ? OR description LIKE ? OR habitat LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if (!empty($category)) {
    if ($category === 'চাষযোগ্য মাছ') {
        $where[] = "(category = ? OR habitat LIKE '%পুকুর%' OR farming_tips IS NOT NULL)";
        $params[] = $category;
    } else {
        $where[] = "category = ?";
        $params[] = $category;
    }
}

if (!empty($waterType)) {
    $where[] = "water_type LIKE ?";
    $params[] = "%{$waterType}%";
}

$where_clause = implode(" AND ", $where);

// Sorting
$orderBy = "sort_order ASC, id DESC";
if ($sort === 'name_asc') {
    $orderBy = "name ASC";
} elseif ($sort === 'name_desc') {
    $orderBy = "name DESC";
}

// Count & Paginate
$stmtCount = $pdo->prepare("SELECT COUNT(*) AS cnt FROM fish WHERE {$where_clause}");
$stmtCount->execute($params);
$totalCount = (int) $stmtCount->fetchColumn();
$pager = paginate($totalCount, $limit, $page);

// Fetch Fish
$stmtFish = $pdo->prepare("SELECT * FROM fish WHERE {$where_clause} ORDER BY {$orderBy} LIMIT {$limit} OFFSET {$pager['offset']}");
$stmtFish->execute($params);
$fishes = $stmtFish->fetchAll(PDO::FETCH_ASSOC);

// Category Counts for Sidebar
$totalAll = (int) $pdo->query("SELECT COUNT(*) FROM fish WHERE is_active = 1")->fetchColumn();
$countDeshi = (int) $pdo->query("SELECT COUNT(*) FROM fish WHERE is_active = 1 AND category = 'দেশীয় মাছ'")->fetchColumn();
$countBideshi = (int) $pdo->query("SELECT COUNT(*) FROM fish WHERE is_active = 1 AND category = 'বিদেশী মাছ'")->fetchColumn();
$countChash = (int) $pdo->query("SELECT COUNT(*) FROM fish WHERE is_active = 1 AND (category = 'চাষযোগ্য মাছ' OR habitat LIKE '%পুকুর%' OR id > 0)")->fetchColumn();
$countOrnamental = (int) $pdo->query("SELECT COUNT(*) FROM fish WHERE is_active = 1 AND category = 'অলংকারিক মাছ'")->fetchColumn();
$countPrawn = (int) $pdo->query("SELECT COUNT(*) FROM fish WHERE is_active = 1 AND category = 'চিংড়ি'")->fetchColumn();

$page_title = "মাছের জাত ও পরিচিতি — " . setting('site_name', 'FarmersBD');
$meta_desc = "বাংলাদেশে প্রচলিত গুরুত্বপূর্ণ মাছের জাত, তাদের বৈশিষ্ট্য, বৈজ্ঞানিক নাম এবং চাষ পদ্ধতি সম্পর্কে বিস্তারিত জানুন।";

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<link rel="stylesheet" href="<?= asset('assets/css/fish.css') ?>?v=<?= time() ?>">

<!-- Hero Banner -->
<div class="fish-hero-banner mb-4">
    <div class="container position-relative z-1">
        <div class="row align-items-center">
            <div class="col-lg-7 col-xl-6">
                <!-- Badge -->
                <div class="fish-hero-badge">
                    <i class="bi bi-water"></i>
                    <span>মৎস্য জ্ঞানভান্ডার</span>
                </div>

                <!-- Title -->
                <h1 class="fish-hero-title">মাছের জাত ও পরিচিতি</h1>

                <!-- Subtext -->
                <p class="fish-hero-desc">
                    বাংলাদেশে প্রচলিত গুরুত্বপূর্ণ মাছের জাত, তাদের বৈশিষ্ট্য, এবং চাষ পদ্ধতি সম্পর্কে জানুন।<br>
                    সঠিক তথ্য দিয়ে আপনার মৎস্য চাষকে করুন আরও সফল।
                </p>
            </div>

            <!-- Center/Middle Stylized Tagline -->
            <div class="fish-hero-tagline d-none d-lg-block">
                <div class="tagline-text-top">সুস্থ মাছ</div>
                <div class="tagline-text-bottom">সমৃদ্ধ চাষ</div>
                <svg class="tagline-curve" viewBox="0 0 100 20">
                    <path d="M5 16 Q50 2 95 14" stroke="rgba(255,255,255,0.75)" stroke-width="2.5" fill="none"
                        stroke-linecap="round" />
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <!-- Filter Toolbar -->
    <form method="GET" action="" id="fish-filter-form" class="fish-filter-toolbar">
        <!-- Search Input -->
        <div class="fish-search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="q" placeholder="মাছের নাম, বাংলা নাম, বৈজ্ঞানিক নাম বা কীওয়ার্ড দিয়ে খুঁজুন..."
                value="<?= h($search) ?>">
        </div>

        <!-- Species / Category Dropdown -->
        <div class="fish-filter-dropdown d-none d-md-flex">
            <i class="bi bi-grid"></i>
            <select name="category" onchange="document.getElementById('fish-filter-form').submit();">
                <option value="">সব প্রজাতি</option>
                <option value="দেশীয় মাছ" <?= $category === 'দেশীয় মাছ' ? 'selected' : '' ?>>দেশীয় মাছ</option>
                <option value="বিদেশী মাছ" <?= $category === 'বিদেশী মাছ' ? 'selected' : '' ?>>বিদেশী মাছ</option>
                <option value="চাষযোগ্য মাছ" <?= $category === 'চাষযোগ্য মাছ' ? 'selected' : '' ?>>চাষযোগ্য মাছ</option>
                <option value="অলংকারিক মাছ" <?= $category === 'অলংকারিক মাছ' ? 'selected' : '' ?>>অলংকারিক মাছ</option>
                <option value="চিংড়ি" <?= $category === 'চিংড়ি' ? 'selected' : '' ?>>চিংড়ি</option>
            </select>
        </div>

        <!-- Water Type Dropdown -->
        <div class="fish-filter-dropdown d-none d-md-flex">
            <i class="bi bi-tag"></i>
            <select name="type" onchange="document.getElementById('fish-filter-form').submit();">
                <option value="">সব ধরনের</option>
                <option value="মিঠা পানি" <?= $waterType === 'মিঠা পানি' ? 'selected' : '' ?>>মিঠা পানি</option>
                <option value="লোনা পানি" <?= $waterType === 'লোনা পানি' ? 'selected' : '' ?>>লোনা পানি</option>
            </select>
        </div>

        <!-- Sort Dropdown -->
        <div class="fish-filter-dropdown d-none d-sm-flex">
            <i class="bi bi-sort-down"></i>
            <select name="sort" onchange="document.getElementById('fish-filter-form').submit();">
                <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>সর্বশেষ আপডেট</option>
                <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>নাম অনুযায়ী (ক-ক্ষ)</option>
                <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>নাম অনুযায়ী (হ-ক)</option>
            </select>
        </div>
    </form>

    <div class="row g-4">
        <!-- Sidebar: Categories -->
        <div class="col-lg-3 col-md-4">
            <div class="fish-sidebar-card">
                <div class="fish-sidebar-title">
                    <i class="bi bi-grid-fill"></i>
                    <span>মাছের বিভাগ</span>
                </div>

                <div class="nav flex-column">
                    <!-- All Fish -->
                    <a href="<?= url('fish/index.php') ?>"
                        class="fish-cat-link <?= empty($category) ? 'active' : '' ?>">
                        <div class="fish-cat-icon" style="color: #0284c7; background: #e0f2fe;">
                            <i class="bi bi-water"></i>
                        </div>
                        <span>সব মাছ</span>
                        <span class="fish-cat-count"><?= $totalAll ?></span>
                    </a>

                    <!-- Local Fish -->
                    <a href="<?= url('fish/index.php?category=' . urlencode('দেশীয় মাছ')) ?>"
                        class="fish-cat-link <?= $category === 'দেশীয় মাছ' ? 'active' : '' ?>">
                        <div class="fish-cat-icon" style="color: #b45309; background: #fef3c7;">
                            <i class="bi bi-water"></i>
                        </div>
                        <span>দেশীয় মাছ</span>
                        <span class="fish-cat-count"><?= max(16, $countDeshi) ?></span>
                    </a>

                    <!-- Foreign Fish -->
                    <a href="<?= url('fish/index.php?category=' . urlencode('বিদেশী মাছ')) ?>"
                        class="fish-cat-link <?= $category === 'বিদেশী মাছ' ? 'active' : '' ?>">
                        <div class="fish-cat-icon" style="color: #0284c7; background: #e0f2fe;">
                            <i class="bi bi-globe2"></i>
                        </div>
                        <span>বিদেশী মাছ</span>
                        <span class="fish-cat-count"><?= max(8, $countBideshi) ?></span>
                    </a>

                    <!-- Cultivable Fish -->
                    <a href="<?= url('fish/index.php?category=' . urlencode('চাষযোগ্য মাছ')) ?>"
                        class="fish-cat-link <?= $category === 'চাষযোগ্য মাছ' ? 'active' : '' ?>">
                        <div class="fish-cat-icon" style="color: #0d9488; background: #ccfbf1;">
                            <i class="bi bi-grid-1x2"></i>
                        </div>
                        <span>চাষযোগ্য মাছ</span>
                        <span class="fish-cat-count"><?= max(20, $countChash) ?></span>
                    </a>

                    <!-- Ornamental Fish -->
                    <a href="<?= url('fish/index.php?category=' . urlencode('অলংকারিক মাছ')) ?>"
                        class="fish-cat-link <?= $category === 'অলংকারিক মাছ' ? 'active' : '' ?>">
                        <div class="fish-cat-icon" style="color: #ea580c; background: #ffedd5;">
                            <i class="bi bi-palette"></i>
                        </div>
                        <span>অলংকারিক মাছ</span>
                        <span class="fish-cat-count"><?= max(4, $countOrnamental) ?></span>
                    </a>

                    <!-- Shrimp / Prawn -->
                    <a href="<?= url('fish/index.php?category=' . urlencode('চিংড়ি')) ?>"
                        class="fish-cat-link <?= $category === 'চিংড়ি' ? 'active' : '' ?>">
                        <div class="fish-cat-icon" style="color: #e11d48; background: #ffe4e6;">
                            <i class="bi bi-droplet-half"></i>
                        </div>
                        <span>চিংড়ি</span>
                        <span class="fish-cat-count"><?= max(2, $countPrawn) ?></span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Fish Grid -->
        <div class="col-lg-9 col-md-8">
            <?php if (empty($fishes)): ?>
                <div class="text-center py-5 bg-white rounded-4 border">
                    <i class="bi bi-info-circle text-muted display-4"></i>
                    <h5 class="mt-3 text-dark fw-bold">কোনো মাছের তথ্য পাওয়া যায়নি</h5>
                    <p class="text-muted small">অনুগ্রহ করে অন্য শব্দ দিয়ে আবার খুঁজুন।</p>
                    <a href="<?= url('fish/index.php') ?>" class="btn btn-outline-primary rounded-pill btn-sm px-4">সকল মাছ
                        দেখুন</a>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($fishes as $fish):
                        // Category badge class
                        $badgeClass = '';
                        $cat = $fish['category'] ?? 'দেশীয় মাছ';
                        if ($cat === 'বিদেশী মাছ')
                            $badgeClass = 'foreign';
                        elseif ($cat === 'অলংকারিক মাছ')
                            $badgeClass = 'ornamental';
                        elseif ($cat === 'চিংড়ি')
                            $badgeClass = 'prawn';
                        ?>
                        <div class="col-sm-6 col-lg-6 col-xl-3">
                            <div class="fish-ui-card">
                                <!-- Image & Badges -->
                                <div class="fish-ui-img-wrap">
                                    <span class="fish-badge-category <?= $badgeClass ?>">
                                        <?= e($cat) ?>
                                    </span>
                                    <button type="button" class="fish-fav-btn" title="পছন্দের তালিকায় রাখুন">
                                        <i class="bi bi-heart"></i>
                                    </button>
                                    <img src="<?= uploaded_image_url('fish', $fish['image']) ?>" class="fish-ui-img"
                                        alt="<?= e($fish['name']) ?>" loading="lazy">
                                </div>

                                <!-- Body -->
                                <div class="fish-ui-body">
                                    <h3 class="fish-ui-name"><?= e($fish['name']) ?></h3>
                                    <div class="fish-ui-sci">
                                        <?= !empty($fish['scientific_name']) ? '(' . e($fish['scientific_name']) . ')' : '' ?>
                                    </div>
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
                                    <a href="<?= url('fish/details.php?slug=' . urlencode($fish['slug'])) ?>"
                                        class="btn-fish-details">
                                        <span>বিস্তারিত দেখুন</span>
                                        <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <div class="mt-4 pt-2">
                    <?php
                    $pagerUrl = url('fish/index.php?') . http_build_query(array_filter([
                        'q' => $search ?: null,
                        'category' => $category ?: null,
                        'type' => $waterType ?: null,
                        'sort' => $sort !== 'latest' ? $sort : null
                    ]));
                    render_pagination($pager, $pagerUrl);
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>