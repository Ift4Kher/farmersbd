<?php
// ============================================================
// FarmersBD — Blog Article Details Page (Premium Design)
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/flash.php';
require_once dirname(__DIR__) . '/includes/seo.php';

$slug = sanitize_input($_GET['slug'] ?? '');

$blog = db_query_one("SELECT * FROM blogs WHERE slug = ? AND is_active = 1", [$slug]);

if (!$blog) {
    set_flash('error', 'আর্টিকেলটি পাওয়া যায়নি।');
    header('Location: ' . url('blog/index.php'));
    exit;
}

// Fetch recent posts
$recent_blogs = db_query(
    "SELECT * FROM blogs WHERE slug != ? AND is_active = 1 ORDER BY id DESC LIMIT 5",
    [$slug]
);

// Calculate reading time (avg 200 Bangla words per minute)
$wordCount = mb_strlen(strip_tags($blog['content'] ?? ''));
$readTime = max(1, (int) ceil($wordCount / 600));

$page_title = $blog['title'] . " — " . setting('site_name', 'FarmersBD');
$meta_desc  = h($blog['excerpt'] ?? mb_strimwidth(strip_tags($blog['content'] ?? ''), 0, 150, '...'));

$extra_css = '<link rel="stylesheet" href="' . asset('assets/css/blog-details.css') . '?v=' . filemtime(dirname(__DIR__) . '/assets/css/blog-details.css') . '">';

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<!-- ── Hero Header ─────────────────────────────────────────── -->
<section class="blog-hero">
    <div class="container position-relative">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url() ?>">হোম</a></li>
                <li class="breadcrumb-item"><a href="<?= url('blog/index.php') ?>">ব্লগ</a></li>
                <li class="breadcrumb-item active"><?= h(mb_strimwidth($blog['title'], 0, 40, '...')) ?></li>
            </ol>
        </nav>

        <h1 class="blog-hero-title"><?= h($blog['title']) ?></h1>

        <?php if (!empty($blog['excerpt'])): ?>
            <p class="blog-hero-excerpt"><?= h($blog['excerpt']) ?></p>
        <?php else: ?>
            <p class="blog-hero-excerpt"><?= h(mb_strimwidth(strip_tags($blog['content'] ?? ''), 0, 180, '...')) ?></p>
        <?php endif; ?>

        <div class="blog-hero-meta">
            <span class="blog-hero-meta-item">
                <i class="bi bi-person-circle"></i> এডমিন
            </span>
            <span class="blog-hero-meta-item">
                <i class="bi bi-calendar3"></i> <?= format_date_bn($blog['created_at']) ?>
            </span>
            <?php if (!empty($blog['category'])): ?>
                <span class="blog-hero-meta-item">
                    <i class="bi bi-bookmark-fill"></i> <?= h($blog['category']) ?>
                </span>
            <?php endif; ?>
            <span class="blog-hero-meta-item">
                <i class="bi bi-clock"></i> <?= $readTime ?> মিনিট পড়ুন
            </span>
        </div>

        <!-- Decorative Stamp -->
        <div class="blog-hero-stamp d-none d-lg-flex">
            সঠিক প্রস্তুতই সফল চাষের মূল
        </div>
    </div>
</section>

<!-- ── Main Content ────────────────────────────────────────── -->
<div class="blog-details-wrap">
    <div class="container">
        <div class="row g-4">
            <!-- Article Main -->
            <div class="col-lg-8">
                <article class="blog-article-card">
                    <?php if (!empty($blog['image'])): ?>
                        <div class="blog-featured-img-wrap">
                            <img src="<?= uploaded_image_url('blogs', $blog['image']) ?>" 
                                 alt="<?= h($blog['title']) ?>" 
                                 loading="eager">
                            <div class="blog-img-caption">
                                <i class="bi bi-camera-fill"></i>
                                <?= h($blog['image_caption'] ?? $blog['title']) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="blog-article-body">
                        <div class="blog-rendered-content">
                            <?php
                            $blogContent = $blog['content'] ?? '';
                            
                            // Auto-wrap "মনে রাখবেন:" paragraphs in a tip box
                            $blogContent = preg_replace(
                                '/<p>\s*(মনে রাখবেন|টিপস|নোট|সতর্কতা)\s*[:：]\s*/u',
                                '<div class="blog-tip-box"><i class="bi bi-lightbulb-fill tip-icon"></i><p><strong>$1:</strong> ',
                                $blogContent
                            );
                            // Close the tip box div after the matching </p>
                            $blogContent = preg_replace(
                                '/(<div class="blog-tip-box"><i class="bi bi-lightbulb-fill tip-icon"><\/i><p>.*?<\/p>)/us',
                                '$1</div>',
                                $blogContent
                            );
                            
                            echo $blogContent;
                            
                            // If content doesn't have a tip, add a generic one
                            if (stripos($blogContent, 'blog-tip-box') === false) {
                                $tipTexts = [
                                    'সঠিক পুকুর প্রস্তুতি শুধু মাছের স্বাস্থ্যই ভালো রাখে না, বরং আপনার উৎপাদন ধরে কমায় এবং লাভ বাড়ায়।',
                                    'সঠিক সময়ে সঠিক পদক্ষেপ নিলে মাছ চাষে সফলতা অর্জন করা সম্ভব।',
                                    'নিয়মিত পর্যবেক্ষণ এবং সঠিক ব্যবস্থাপনা মাছ চাষের সাফল্যের চাবিকাঠি।',
                                ];
                                $tipIndex = crc32($blog['slug'] ?? '') % count($tipTexts);
                                echo '<div class="blog-tip-box"><i class="bi bi-lightbulb-fill tip-icon"></i><p><strong>মনে রাখবেন:</strong> ' . $tipTexts[$tipIndex] . '</p></div>';
                            }
                            ?>
                        </div>
                    </div>


                </article>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Related Posts -->
                <?php if (!empty($recent_blogs)): ?>
                    <div class="blog-sidebar-card">
                        <div class="blog-sidebar-header">
                            <i class="bi bi-journal-bookmark-fill"></i>
                            <h5>সম্পর্কিত পোস্ট</h5>
                        </div>
                        <div>
                            <?php foreach ($recent_blogs as $rb): ?>
                                <a href="<?= url('blog/details.php?slug=' . urlencode($rb['slug'])) ?>" class="related-post-item">
                                    <?php if (!empty($rb['image'])): ?>
                                        <img src="<?= uploaded_image_url('blogs', $rb['image']) ?>" 
                                             alt="<?= h($rb['title']) ?>" 
                                             class="related-post-thumb"
                                             loading="lazy">
                                    <?php else: ?>
                                        <div class="related-post-thumb d-flex align-items-center justify-content-center" 
                                             style="background: linear-gradient(135deg, #e0f2fe, #bae6fd); color: #0284c7;">
                                            <i class="bi bi-journal-text"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="related-post-info">
                                        <h6><?= h($rb['title']) ?></h6>
                                        <span class="related-post-date">
                                            <i class="bi bi-calendar3"></i>
                                            <?= format_date_bn($rb['created_at']) ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- CTA Card -->
                <div class="blog-cta-card">
                    <div class="cta-icon">
                        <i class="bi bi-chat-dots-fill"></i>
                    </div>
                    <h5>আপনার কোনো প্রশ্ন আছে?</h5>
                    <p>মাছ চাষ, রোগ, ওষুধ বা যেকোনো সমস্যার জন্য আমাদের বিশেষজ্ঞদের সাথে যোগাযোগ করুন।</p>
                    <a href="<?= url('consultation/') ?>" class="blog-cta-btn">
                        <i class="bi bi-headset"></i>
                        বিশেষজ্ঞের সাথে কথা বলুন →
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
