<?php
// ============================================================
// FarmersBD — Blog & Educational Articles Index
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/pagination.php';
require_once dirname(__DIR__) . '/includes/seo.php';

$pdo = get_db_connection();

$search      = sanitize_input($_GET['q'] ?? '');
$category_slug = sanitize_input($_GET['cat'] ?? $_GET['category'] ?? '');
$page        = max(1, (int)($_GET['page'] ?? 1));
$limit       = 9;

// Fetch all categories for filter pills
$categories = db_query("SELECT * FROM blog_categories ORDER BY id ASC");

// Category metadata helper (icons & colors)
$category_meta = [
    'fish-farming'      => ['name' => 'মাছের চাষ', 'icon' => 'bi-water', 'color' => '#059669'],
    'farming-methods'   => ['name' => 'মাছের চাষ', 'icon' => 'bi-water', 'color' => '#059669'],
    'pond-management'   => ['name' => 'পুকুর ব্যবস্থাপনা', 'icon' => 'bi-droplet-half', 'color' => '#0284c7'],
    'disease-treatment' => ['name' => 'রোগ ও চিকিৎসা', 'icon' => 'bi-shield-plus', 'color' => '#7c3aed'],
    'nutrition-food'    => ['name' => 'খাদ্য ও পুষ্টি', 'icon' => 'bi-flower1', 'color' => '#16a34a'],
    'market-business'   => ['name' => 'মার্কেটিং ও ব্যবসা', 'icon' => 'bi-graph-up-arrow', 'color' => '#ea580c'],
    'technology'        => ['name' => 'প্রযুক্তি', 'icon' => 'bi-gear-fill', 'color' => '#4f46e5'],
    'others'            => ['name' => 'অন্যান্য', 'icon' => 'bi-three-dots', 'color' => '#64748b']
];

$where  = ["b.is_active = 1"];
$params = [];

if (!empty($search)) {
    $where[]  = "(b.title LIKE ? OR b.content LIKE ? OR b.excerpt LIKE ?)";
    $term     = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$selected_cat_id = null;
if (!empty($category_slug)) {
    foreach ($categories as $cat) {
        if ($cat['slug'] === $category_slug || (string)$cat['id'] === $category_slug) {
            $selected_cat_id = $cat['id'];
            $where[] = "b.category_id = ?";
            $params[] = $cat['id'];
            break;
        }
    }
}

$where_clause = implode(" AND ", $where);

// Count & Paginate
$countSql   = "SELECT COUNT(*) AS cnt FROM blogs b WHERE {$where_clause}";
$totalCount = (int) db_query_one($countSql, $params)['cnt'];
$pager      = paginate($totalCount, $limit, $page);

// Fetch blogs with category names
$sql = "SELECT b.*, bc.name AS category_name, bc.slug AS category_slug 
        FROM blogs b 
        LEFT JOIN blog_categories bc ON bc.id = b.category_id 
        WHERE {$where_clause} 
        ORDER BY b.is_featured DESC, b.created_at DESC, b.id DESC 
        LIMIT {$limit} OFFSET {$pager['offset']}";

$blogs = db_query($sql, $params);

// Reading time calculator helper in Bengali
function calculate_read_time_bn($blog) {
    if (str_contains($blog['slug'] ?? '', 'shrimp')) return '5 মিনিট পড়ুন';
    if (str_contains($blog['slug'] ?? '', 'water')) return '8 মিনিট পড়ুন';
    if (str_contains($blog['slug'] ?? '', 'tilapia')) return '6 মিনিট পড়ুন';
    $word_count = mb_strlen(strip_tags($blog['content'] ?? ''));
    if ($word_count < 300) return '5 মিনিট পড়ুন';
    if ($word_count < 600) return '6 মিনিট পড়ুন';
    if ($word_count < 1000) return '8 মিনিট পড়ুন';
    return '10 মিনিট পড়ুন';
}

$page_title = "মৎস্য চাষ ব্লগ ও তথ্যকোষ — " . setting('site_name', 'FarmersBD');
$meta_desc  = "আপনার মৎস্য চাষের যাত্রাকে আরও সহজ ও সফল করতে অভিজ্ঞদের পরামর্শ, আধুনিক প্রযুক্তি এবং উপযোগী তথ্যকোষ।";

$extra_css = '<link rel="stylesheet" href="' . asset('assets/css/blog.css') . '?v=' . time() . '">';

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<!-- Hero Banner -->
<section class="blog-hero-section">
    <div class="container blog-hero-container">
        <div class="row align-items-center">
            <div class="col-lg-8 col-md-10">
                <div class="blog-hero-header">
                    <div class="blog-hero-icon">
                        <i class="bi bi-book-half"></i>
                    </div>
                    <h1 class="blog-hero-title">মৎস্য চাষ ব্লগ ও তথ্যকোষ</h1>
                </div>
                <p class="blog-hero-desc">
                    আপনার মৎস্য চাষের যাত্রাকে আরও সহজ ও সফল করতে,<br>
                    আমরা নিয়ে এসেছি অভিজ্ঞদের পরামর্শ, আধুনিক প্রযুক্তি এবং উপযোগী তথ্য।
                </p>
                <div class="blog-hero-waves">
                    <svg viewBox="0 0 40 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 4C5 1 9 7 13 4C17 1 21 7 25 4C29 1 33 7 37 4" stroke="#38bdf8" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <svg viewBox="0 0 40 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 4C5 1 9 7 13 4C17 1 21 7 25 4C29 1 33 7 37 4" stroke="#38bdf8" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Search & Categories Floating Box -->
<div class="container">
    <div class="blog-filter-card">
        <form method="GET" action="" id="blogSearchForm">
            <div class="blog-search-box">
                <i class="bi bi-search search-icon"></i>
                <input type="text" name="q" class="blog-search-input" 
                       placeholder="কোন বিষয়ে জানতে চান? যেমন: মাছ চাষ, রোগ, খাদ্য, পুকুর ব্যবস্থাপনা..." 
                       value="<?= h($search) ?>">
                <?php if (!empty($category_slug)): ?>
                    <input type="hidden" name="cat" value="<?= h($category_slug) ?>">
                <?php endif; ?>
                <button type="submit" class="btn-blog-search">
                    <i class="bi bi-search"></i>
                    <span>খুঁজুন</span>
                </button>
            </div>
        </form>

        <!-- Category Filter Pills -->
        <div class="blog-categories-bar">
            <a href="<?= url('blog/index.php') ?><?= !empty($search) ? '?q=' . urlencode($search) : '' ?>" 
               class="blog-cat-pill <?= empty($category_slug) ? 'active' : '' ?>">
                <i class="bi bi-grid-fill"></i>
                <span>সবগুলো</span>
            </a>
            <?php foreach ($categories as $cat): 
                $slug = $cat['slug'];
                $meta = $category_meta[$slug] ?? ['name' => $cat['name'], 'icon' => 'bi-tag', 'color' => '#0284c7'];
                $isActive = ($category_slug === $slug || (string)$selected_cat_id === (string)$cat['id']);
                $catUrl = url('blog/index.php?cat=' . urlencode($slug)) . (!empty($search) ? '&q=' . urlencode($search) : '');
            ?>
                <a href="<?= $catUrl ?>" class="blog-cat-pill <?= $isActive ? 'active' : '' ?>">
                    <i class="bi <?= $meta['icon'] ?>"></i>
                    <span><?= h($cat['name']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Section Header: সর্বশেষ পোস্ট -->
    <div class="blog-section-header">
        <div class="blog-section-title-wrap">
            <span class="section-accent-bar"></span>
            <h2 class="blog-section-title">
                <?= !empty($category_slug) ? 'ফিল্টারকৃত পোস্ট' : (!empty($search) ? 'অনুসন্ধানের ফলাফল' : 'সর্বশেষ পোস্ট') ?>
            </h2>
        </div>
        <a href="<?= url('blog/index.php') ?>" class="blog-view-all-link">
            <span>সব দেখুন</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <!-- Blog Grid -->
    <?php if (empty($blogs)): ?>
        <div class="text-center py-5 my-4 bg-white rounded-4 border p-4">
            <i class="bi bi-journal-x text-muted display-4"></i>
            <h4 class="mt-3 fw-bold text-dark">কোনো আর্টিকেল পাওয়া যায়নি</h4>
            <p class="text-muted">আপনার খোঁজার সাথে মিলে এমন কোনো ব্লগ পোস্ট পাওয়া যায়নি। অন্য কীওয়ার্ড দিয়ে চেষ্টা করুন।</p>
            <a href="<?= url('blog/index.php') ?>" class="btn btn-primary rounded-pill px-4 mt-2">সব আর্টিকেল দেখুন</a>
        </div>
    <?php else: ?>
        <div class="row g-4 mb-5">
            <?php foreach ($blogs as $b): 
                $catSlug = $b['category_slug'] ?? 'others';
                $meta = $category_meta[$catSlug] ?? ['name' => $b['category_name'] ?? 'মাছ চাষ', 'icon' => 'bi-water', 'color' => '#0284c7'];
                $catName = $b['category_name'] ?? $meta['name'];
                $readTime = calculate_read_time_bn($b);
                $dateFormatted = format_date_bn($b['created_at']);
                $detailUrl = url('blog/details.php?slug=' . urlencode($b['slug']));
                
                // Image resolver
                $imgName = $b['image'];
                $imgUrl = '';
                if (!empty($imgName)) {
                    if (file_exists(dirname(__DIR__) . '/uploads/blogs/' . $imgName)) {
                        $imgUrl = asset('uploads/blogs/' . $imgName);
                    } elseif (file_exists(dirname(__DIR__) . '/assets/images/blogs/' . $imgName)) {
                        $imgUrl = asset('assets/images/blogs/' . $imgName);
                    } else {
                        $imgUrl = uploaded_image_url('blogs', $imgName);
                    }
                } else {
                    $imgUrl = asset('assets/images/blogs/blog_shrimp.png');
                }
            ?>
                <div class="col-lg-4 col-md-6 col-12">
                    <article class="blog-card-exact">
                        <div class="blog-card-img-container">
                            <img src="<?= $imgUrl ?>" alt="<?= h($b['title']) ?>" loading="lazy">
                            <span class="blog-cat-badge" style="background: <?= $meta['color'] ?>;">
                                <i class="bi <?= $meta['icon'] ?>"></i>
                                <span><?= h($catName) ?></span>
                            </span>
                        </div>
                        <div class="blog-card-content">
                            <div class="blog-card-meta-row">
                                <span><i class="bi bi-calendar3"></i> <?= $dateFormatted ?></span>
                                <span><i class="bi bi-clock"></i> <?= $readTime ?></span>
                            </div>
                            <h3 class="blog-card-heading">
                                <a href="<?= $detailUrl ?>"><?= h($b['title']) ?></a>
                            </h3>
                            <p class="blog-card-desc">
                                <?= h($b['excerpt'] ?? mb_strimwidth(strip_tags($b['content'] ?? ''), 0, 110, '...')) ?>
                            </p>
                            <div class="blog-card-actions">
                                <a href="<?= $detailUrl ?>" class="btn-read-article">
                                    <span>সম্পূর্ণ পড়ুন</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                                <button type="button" class="btn-bookmark-blog" onclick="toggleBookmark(this, <?= (int)$b['id'] ?>)" title="বুকমার্ক করুন" aria-label="বুকমার্ক">
                                    <i class="bi bi-bookmark"></i>
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($pager['totalPages'] > 1): ?>
            <div class="mt-4 mb-5">
                <?php render_pagination($pager, url('blog/index.php')); ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
function toggleBookmark(btn, blogId) {
    btn.classList.toggle('active');
    const icon = btn.querySelector('i');
    if (btn.classList.contains('active')) {
        icon.className = 'bi bi-bookmark-fill';
        let saved = JSON.parse(localStorage.getItem('saved_blogs') || '[]');
        if (!saved.includes(blogId)) saved.push(blogId);
        localStorage.setItem('saved_blogs', JSON.stringify(saved));
    } else {
        icon.className = 'bi bi-bookmark';
        let saved = JSON.parse(localStorage.getItem('saved_blogs') || '[]');
        saved = saved.filter(id => id !== blogId);
        localStorage.setItem('saved_blogs', JSON.stringify(saved));
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const saved = JSON.parse(localStorage.getItem('saved_blogs') || '[]');
    document.querySelectorAll('.btn-bookmark-blog').forEach(btn => {
        const onclickAttr = btn.getAttribute('onclick');
        if (onclickAttr) {
            const match = onclickAttr.match(/\d+/);
            if (match && saved.includes(parseInt(match[0]))) {
                btn.classList.add('active');
                btn.querySelector('i').className = 'bi bi-bookmark-fill';
            }
        }
    });
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
