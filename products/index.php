<?php
// ============================================================
// FarmersBD — Products Listing Page
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/flash.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/seo.php';
require_once dirname(__DIR__) . '/includes/pagination.php';

// Filters
$search   = trim($_GET['q']        ?? '');
$catId    = (int)($_GET['cat']     ?? 0);
$sort     = $_GET['sort']          ?? 'newest';
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 12;

// Categories
$categories = db_query("SELECT * FROM product_categories WHERE is_active = 1 ORDER BY name ASC");

// Build query
$where  = ['p.is_active = 1', 'p.deleted_at IS NULL'];
$params = [];

if ($search) {
    $where[]  = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($catId) {
    $where[]  = "p.category_id = ?";
    $params[] = $catId;
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

switch($sort) {
    case 'price_asc':   $orderSql = 'ORDER BY IFNULL(p.discount_price, p.price) ASC'; break;
    case 'price_desc':  $orderSql = 'ORDER BY IFNULL(p.discount_price, p.price) DESC'; break;
    case 'name_asc':    $orderSql = 'ORDER BY p.name ASC'; break;
    default:            $orderSql = 'ORDER BY p.created_at DESC'; break;
}

$totalCount = (int) db_query_one("SELECT COUNT(*) AS cnt FROM products p {$whereSql}", $params)['cnt'];
$pager      = paginate($totalCount, $perPage, $page);
$products = db_query(
    "SELECT p.*, c.name AS category_name
     FROM products p
     LEFT JOIN product_categories c ON c.id = p.category_id
     {$whereSql} {$orderSql}
     LIMIT {$perPage} OFFSET {$pager['offset']}",
    $params
);

$activeCat = $catId ? db_query_one("SELECT name FROM product_categories WHERE id = ?", [$catId]) : null;
$pageTitle = $search
    ? "\"$search\" খোঁজার ফলাফল"
    : ($activeCat ? $activeCat['name'] : 'সকল অ্যাকোয়া পণ্য');

$page_seo = ['title' => $pageTitle . ' | ' . setting('site_name', 'FarmersBD')];

$csrf = csrf_generate();
include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/navbar.php';
?>
<link rel="stylesheet" href="<?= asset('assets/css/products.css') ?>?v=<?= time() ?>">

<!-- Hero Banner -->
<div class="products-hero-banner mb-5">
    <div class="container z-1">
        <!-- Badge -->
        <div class="d-inline-flex align-items-center rounded-pill px-3 py-1 mb-3 shadow-sm" style="background: rgba(14, 165, 233, 0.16); border: 1px solid rgba(14, 165, 233, 0.28); backdrop-filter: blur(8px);">
            <span style="width: 22px; height: 22px; background: #0284c7; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: white; margin-right: 8px;">
                <i class="bi bi-box-seam" style="font-size: 0.72rem;"></i>
            </span>
            <span class="fw-bold small" style="color: #0369a1;">বিশ্বস্ত পণ্য, উন্নত মাছ চাষ</span>
        </div>
        
        <!-- Heading -->
        <h1 class="display-5 fw-bold mb-3" style="letter-spacing: -0.5px; color: #083358 !important; font-weight: 800;">FarmersBD পণ্যসমূহ</h1>
        
        <!-- Subtext -->
        <p class="mb-0" style="font-size: 1.05rem; line-height: 1.6; color: #1e3a5f; max-width: 520px; font-weight: 500;">
            মাছ চাষের জন্য প্রয়োজনীয় সকল পণ্য এক জায়গায়।<br>
            বিশ্বস্ত ব্র্যান্ড, উচ্চমানের পণ্য, আপনার সফলতার সঙ্গী।
        </p>
    </div>
</div>

<div class="container pb-5">
    <!-- Filter Toolbar -->
    <div class="bg-white rounded-pill p-2 shadow-sm border mb-4 d-none d-lg-flex align-items-center">
        <!-- Search -->
        <form method="GET" class="d-flex align-items-center flex-grow-1 border-end px-3 m-0">
            <i class="bi bi-search text-muted me-2"></i>
            <input type="text" name="q" class="form-control border-0 shadow-none p-0" placeholder="পণ্য খুঁজুন..." value="<?= h($search) ?>">
            <?php if ($catId): ?><input type="hidden" name="cat" value="<?= $catId ?>"><?php endif; ?>
        </form>
        <!-- Category Dropdown (Mock) -->
        <div class="d-flex align-items-center px-4 border-end">
            <i class="bi bi-grid text-muted me-2"></i>
            <select class="form-select border-0 shadow-none p-0 text-muted" onchange="window.location.href=this.value" style="cursor: pointer; width: auto; min-width: 140px;">
                <option value="<?= url('products/') ?>">সব ক্যাটাগরি</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= url('products/?cat=' . $cat['id']) ?>" <?= $catId === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <!-- Brand Dropdown (Mock) -->
        <div class="d-flex align-items-center px-4 border-end text-muted">
            <i class="bi bi-funnel text-muted me-2"></i>
            <select class="form-select border-0 shadow-none p-0 text-muted" style="width: auto; min-width: 120px;">
                <option>সব ব্র্যান্ড</option>
            </select>
        </div>
        <!-- Sort Dropdown -->
        <div class="d-flex align-items-center px-4">
            <i class="bi bi-sort-down text-muted me-2"></i>
            <select class="form-select border-0 shadow-none p-0 text-muted fw-semibold" id="sort-select" style="cursor: pointer; width: auto; min-width: 150px;">
                <?php
                $sorts = ['newest' => 'সর্বশেষ প্রথমে', 'price_asc' => 'কম দাম আগে', 'price_desc' => 'বেশি দাম আগে', 'name_asc' => 'নাম অনুযায়ী'];
                foreach ($sorts as $val => $label): ?>
                <option value="<?= e($val) ?>" <?= $sort === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <!-- Mobile Filter Toggle -->
    <div class="d-lg-none mb-4">
        <!-- Kept original mobile toggle for simplicity -->
    </div>

    <div class="row g-4">
        <!-- Sidebar Categories -->
        <div class="col-lg-3 col-md-4 order-1 order-md-1">
            <!-- Mobile toggle button -->
            <button class="btn btn-outline-primary w-100 d-md-none mb-3 rounded-pill fw-semibold py-2" 
                    type="button" data-bs-toggle="collapse" data-bs-target="#categoryCollapse" 
                    aria-expanded="false" aria-controls="categoryCollapse">
                <i class="bi bi-funnel-fill me-2"></i>ক্যাটাগরি ফিল্টার
                <i class="bi bi-chevron-down ms-1"></i>
            </button>
            <div class="collapse d-md-block" id="categoryCollapse">
                <div class="sidebar-cat-card">
                    <div class="sidebar-cat-title">পণ্য ক্যাটাগরি</div>
                    <div class="d-flex flex-column">
                        <a href="<?= url('products/') ?>" class="sidebar-cat-link <?= !$catId ? 'active' : '' ?>">
                            <i class="bi bi-grid-fill"></i> সকল পণ্য <?= !$catId ? '<i class="bi bi-chevron-right"></i>' : '' ?>
                        </a>
                        <?php 
                        $catIcons = ['মাছের ওষুধ ও চিকিৎসা' => 'bi-capsule', 'মাছের খাদ্য' => 'bi-bag-fill', 'পুকুর ব্যবস্থাপনা' => 'bi-droplet-half', 'পানি পরীক্ষা ও কিট' => 'bi-thermometer-half', 'ভিটামিন ও সাপ্লিমেন্ট' => 'bi-bandaid', 'যন্ত্রপাতি ও সরঞ্জাম' => 'bi-tools', 'সার ও জৈব প্রোডাক্ট' => 'bi-tree', 'অন্যান্য পণ্য' => 'bi-box'];
                        foreach ($categories as $cat): 
                            $icon = $catIcons[$cat['name']] ?? 'bi-box';
                        ?>
                        <a href="<?= url('products/?cat=' . (int)$cat['id']) ?>" class="sidebar-cat-link <?= $catId === (int)$cat['id'] ? 'active' : '' ?>">
                            <i class="bi <?= $icon ?>"></i> <?= e($cat['name']) ?>
                            <?= $catId === (int)$cat['id'] ? '<i class="bi bi-chevron-right"></i>' : '<i class="bi bi-chevron-right ms-auto opacity-25"></i>' ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content (Promo & Products Grid) -->
        <div class="col-lg-9 col-md-8 order-2 order-md-2">
            
            <!-- Promo Banner Section -->
            <div class="row g-3 mb-4">
                <div class="col-xl-8">
                    <div class="promo-banner h-100">
                        <div class="promo-banner-content">
                            <h2 class="promo-banner-title">সুস্থ মাছ, লাভজনক চাষ</h2>
                            <p class="promo-banner-sub">প্রিমিয়াম মানের মাছের ওষুধ ও চিকিৎসা পণ্য এখন FarmersBD-তে।</p>
                            <a href="<?= url('products/') ?>" class="btn-promo-cta">সব পণ্য দেখুন &rarr;</a>
                            
                            <div class="promo-features-row">
                                <span class="promo-badge-item"><i class="bi bi-shield-check"></i> বিশ্বস্ত ব্র্যান্ড</span>
                                <span class="promo-badge-item"><i class="bi bi-check-circle-fill"></i> নিরাপদ ব্যবহার</span>
                                <span class="promo-badge-item"><i class="bi bi-truck"></i> দ্রুত ডেলিভারি</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="row g-3 h-100">
                        <div class="col-6 col-md-12 col-xl-6" style="height: calc(50% - 0.5rem);">
                            <div class="feature-mini-card flex-column text-center justify-content-center px-2 py-3">
                                <div class="feature-mini-icon mb-2"><i class="bi bi-shield-check"></i></div>
                                <div class="feature-mini-text"><h6>100% আসল পণ্য</h6><p>নিশ্চয়তা</p></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-12 col-xl-6" style="height: calc(50% - 0.5rem);">
                            <div class="feature-mini-card flex-column text-center justify-content-center px-2 py-3">
                                <div class="feature-mini-icon mb-2"><i class="bi bi-truck"></i></div>
                                <div class="feature-mini-text"><h6>দ্রুত ডেলিভারি</h6><p>সারা বাংলাদেশ</p></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-12 col-xl-6" style="height: calc(50% - 0.5rem);">
                            <div class="feature-mini-card flex-column text-center justify-content-center px-2 py-3">
                                <div class="feature-mini-icon mb-2"><i class="bi bi-headset"></i></div>
                                <div class="feature-mini-text"><h6>বিশেষজ্ঞ পরামর্শ</h6><p>ফ্রি</p></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-12 col-xl-6" style="height: calc(50% - 0.5rem);">
                            <div class="feature-mini-card flex-column text-center justify-content-center px-2 py-3">
                                <div class="feature-mini-icon mb-2"><i class="bi bi-flower1"></i></div>
                                <div class="feature-mini-text"><h6>সঠিক ব্যবহার নির্দেশিকা</h6><p>প্রতিটি পণ্যে</p></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Popular Products Header -->
            <div class="d-flex align-items-center justify-content-between mb-3 mt-4">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-fire text-danger me-2"></i>জনপ্রিয় পণ্যসমূহ</h5>
                <a href="<?= url('products/') ?>" class="text-primary fw-semibold small text-decoration-none">সব পণ্য দেখুন &rarr;</a>
            </div>

            <!-- Product Grid -->
            <?php if (empty($products)): ?>
            <div class="empty-state bg-white rounded-4 border p-5 text-center">
                <i class="bi bi-bag-x display-4 text-muted"></i>
                <h5 class="mt-3">কোনো পণ্য পাওয়া যায়নি</h5>
            </div>
            <?php else: ?>
            <div class="row g-2 g-sm-3">
                <?php foreach ($products as $index => $p):
                    // Logic to assign fake badges for mockup match
                    $badgeClass = ''; $badgeText = '';
                    if ($index % 3 == 0) { $badgeClass = 'bestseller'; $badgeText = 'বেস্ট সেলার'; }
                    elseif ($index % 3 == 1) { $badgeClass = 'new'; $badgeText = 'নতুন'; }
                    else { $badgeClass = 'popular'; $badgeText = 'জনপ্রিয়'; }
                ?>
                <div class="col-6 col-sm-6 col-xl-4">
                    <div class="ui-product-card position-relative">
                        <!-- Badges -->
                        <div class="ui-badge-container">
                            <span class="ui-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                        </div>
                        
                        <!-- Wishlist -->
                        <button class="ui-wishlist-btn"><i class="bi bi-heart"></i></button>

                        <a href="<?= url('products/details.php?slug=' . e($p['slug'])) ?>" class="text-decoration-none text-dark d-flex flex-column h-100">
                            <img src="<?= uploaded_image_url('products', $p['image']) ?>" alt="<?= e($p['name']) ?>" class="ui-product-img">
                            
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
            
            <div class="mt-4">
            <?php
            $pagerUrl = url('products/?') . http_build_query(array_filter(['q' => $search, 'cat' => $catId ?: null, 'sort' => $sort !== 'newest' ? $sort : null]));
            render_pagination($pager, $pagerUrl);
            ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('sort-select').addEventListener('change', function () {
    const params = new URLSearchParams(window.location.search);
    params.set('sort', this.value);
    params.delete('page');
    window.location.search = params.toString();
});
</script>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
