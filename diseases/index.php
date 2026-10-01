<?php
// ============================================================
// FarmersBD — Fish Disease Information Directory
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/pagination.php';
require_once dirname(__DIR__) . '/includes/seo.php';

$pdo = get_db_connection();

$search = sanitize_input($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 12;

$where  = ["is_active = 1"];
$params = [];

if (!empty($search)) {
    $where[]  = "(name LIKE ? OR symptoms LIKE ? OR treatment LIKE ?)";
    $term     = "%{$search}%";
    $params   = [$term, $term, $term];
}

$where_clause = implode(" AND ", $where);

// Count & Paginate
$totalCount = (int) db_query_one("SELECT COUNT(*) AS cnt FROM diseases WHERE {$where_clause}", $params)['cnt'];
$pager      = paginate($totalCount, $limit, $page);

// Fetch
$diseases = db_query("SELECT * FROM diseases WHERE {$where_clause} ORDER BY id DESC LIMIT {$limit} OFFSET {$pager['offset']}", $params);

$page_title = "মাছের রোগ ও চিকিৎসা গাইড — " . setting('site_name', 'FarmersBD');
$meta_desc  = "বাংলাদেশের কার্প, ক্যাটফিশ ও অন্যান্য মাছের রোগ, লক্ষণ, প্রতিরোধ ও কার্যকারী চিকিৎসা নির্দেশিকা।";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>
<style>
/* Custom CSS for the Diseases Page Redesign */
.disease-banner {
    background: url('https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&q=80&w=2000') no-repeat center center;
    background-size: cover;
    min-height: 400px;
    display: flex;
    align-items: center;
}
.disease-banner::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(90deg, rgba(2, 108, 166, 0.9) 0%, rgba(15, 118, 110, 0.7) 100%);
}
.text-shadow {
    text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.3);
}
.disease-search-form {
    border: 2px solid rgba(255, 255, 255, 0.2);
}
.disease-search-form input:focus {
    outline: none;
    box-shadow: none;
}
.disease-modern-card {
    border-radius: 16px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.disease-modern-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.08);
}
.disease-icon-wrapper {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 50%;
}
.disease-fish-floating {
    position: absolute;
    bottom: -5%;
    right: -5%;
    width: 50%;
    max-height: 110px;
    object-fit: cover;
    border-radius: 10px;
    filter: drop-shadow(-5px 5px 15px rgba(0,0,0,0.15));
    pointer-events: none;
    z-index: 0;
}
.disease-card-btn {
    position: relative;
    z-index: 2;
}
</style>

<!-- Header Banner -->
<div class="disease-banner position-relative mb-5">
    <div class="container h-100 position-relative z-1 py-5">
        <div class="row align-items-center">
            <div class="col-lg-8 col-xl-7">
                <!-- Badge -->
                <div class="d-inline-flex align-items-center bg-white bg-opacity-25 rounded-pill px-3 py-1 mb-3 shadow-sm" style="backdrop-filter: blur(5px);">
                    <i class="bi bi-shield-plus text-white me-2"></i>
                    <span class="text-white fw-medium small">মাছের রোগ ও চিকিৎসা গাইড</span>
                </div>
                
                <!-- Heading -->
                <h1 class="display-4 fw-bold text-white mb-3 text-shadow">মাছের রোগ ও চিকিৎসা গাইড</h1>
                
                <!-- Subtext -->
                <p class="lead text-white opacity-90 mb-4 text-shadow" style="font-size: 1.1rem; line-height: 1.6;">
                    মাছের বিভিন্ন রোগের লক্ষণ, কারণ, প্রতিরোধ ও সঠিক চিকিৎসা সম্পর্কে জানুন<br class="d-none d-md-block">
                    বিশেষজ্ঞদের পরামর্শ ও FarmersBD এর যাচাইকৃত তথ্যের মাধ্যমে।
                </p>

                <!-- Search Form -->
                <form method="GET" action="" class="disease-search-form bg-white rounded-pill p-1 shadow-lg d-flex mb-0">
                    <div class="input-group border-0 align-items-center">
                        <span class="input-group-text bg-transparent border-0 ps-3 pe-2 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control border-0 shadow-none bg-transparent py-2 px-0" placeholder="রোগের নাম, মাছের প্রজাতি বা সমস্যার লক্ষণ লিখে খুঁজুন..." value="<?= h($search) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold text-nowrap" style="background-color: #026ca6; border: none;">খুঁজুন &rarr;</button>
                </form>
            </div>
            
            <div class="col-lg-4 col-xl-5 d-none d-lg-block position-relative">
                <!-- Handwritten text -->
                <div class="position-absolute" style="left: 10%; top: -30px; transform: rotate(-10deg);">
                    <span class="text-white fw-bold" style="font-size: 2rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); font-style: italic;">সুস্থ মাছ<br>সফল চাষ</span>
                    <svg class="mt-1 d-block" width="100" height="20" viewBox="0 0 100 20"><path d="M0 10 Q 50 20 100 0" stroke="white" stroke-width="2" fill="transparent"/></svg>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    
    <!-- Section Title -->
    <div class="d-flex align-items-center mb-4">
        <div class="me-3 p-2 bg-primary bg-opacity-10 rounded-circle text-primary">
            <i class="bi bi-search fs-4"></i>
        </div>
        <div>
            <h3 class="fw-bold mb-1 text-primary" style="color: #026ca6 !important;">রোগের ধরন অনুযায়ী খুঁজুন</h3>
            <p class="text-muted small mb-0">নিচের তালিকা থেকে আপনার প্রয়োজনীয় তথ্য দ্রুত খুঁজে নিন।</p>
        </div>
    </div>

    <!-- Disease Cards -->
    <?php if (empty($diseases)): ?>
        <div class="text-center py-5">
            <i class="bi bi-emoji-frown text-muted display-4"></i>
            <p class="mt-3 text-muted lead">কোনো রোগের তথ্য পাওয়া যায়নি।</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php 
            $card_styles = [
                'eus' => [
                    'bg' => 'linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%)',
                    'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="#0369a1"><path d="M22 12c0-5.5-4.5-10-10-10S2 6.5 2 12s4.5 10 10 10 10-4.5 10-10zm-10-8c4.4 0 8 3.6 8 8s-3.6 8-8 8-8-3.6-8-8 3.6-8 8-8zm-2 11h4v2h-4v-2zm0-6h4v4h-4v-4z"/></svg>',
                ],
                'gill rot' => [
                    'bg' => 'linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%)',
                    'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="#15803d"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>',
                ],
                'pop eye' => [
                    'bg' => 'linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%)',
                    'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="#4338ca"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>',
                ],
                'anchor worm' => [
                    'bg' => 'linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%)',
                    'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="#0284c7"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14v-2h2v2h-2zm0-4V7h2v5h-2z"/></svg>',
                ],
                'ব্যাগটেরিয়াল' => [
                    'bg' => 'linear-gradient(135deg, #ffe4e6 0%, #fecdd3 100%)',
                    'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="#be123c"><path d="M20 12c0-4.41-3.59-8-8-8s-8 3.59-8 8 3.59 8 8 8 8-3.59 8-8zm-8 6c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6zm1-9h-2v4h2V9zm0 6h-2v2h2v-2z"/></svg>',
                ],
                'ফাঙ্গাল' => [
                    'bg' => 'linear-gradient(135deg, #ccfbf1 0%, #99f6e4 100%)',
                    'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="#0f766e"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 16c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6zm-1-9h2v2h-2zm0 4h2v4h-2z"/></svg>',
                ],
                'ভাইরাল' => [
                    'bg' => 'linear-gradient(135deg, #fef3c7 0%, #fde68a 100%)',
                    'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="#b45309"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z"/></svg>',
                ],
                'প্রতিরোধ' => [
                    'bg' => 'linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%)',
                    'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="#026ca6"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>',
                ],
            ];
            
            // Fallback generic gradients and icons
            $generic_bg = ['linear-gradient(135deg, #e0f2fe 0%, #cffafe 100%)', 'linear-gradient(135deg, #dcfce7 0%, #ccfbf1 100%)', 'linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%)', 'linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%)'];

            foreach ($diseases as $index => $dis): 
                $bg = $generic_bg[$index % 4];
                $icon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="#026ca6"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z"/></svg>';
                $real_image = uploaded_image_url('diseases', $dis['image']); // Default

                // Match with hardcoded real image and icons based on disease name to replicate the provided design
                $name_lower = strtolower($dis['name']);
                foreach ($card_styles as $key => $style) {
                    if (str_contains($name_lower, strtolower($key))) {
                        $bg = $style['bg'];
                        $icon = $style['icon'];
                        break;
                    }
                }
            ?>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card disease-modern-card border-0 h-100 position-relative overflow-hidden" style="background: <?= $bg ?>;">
                        
                        <!-- Topic Badge -->
                        <div class="position-absolute top-0 end-0 m-3 bg-white bg-opacity-50 px-2 py-1 rounded text-primary fw-medium shadow-sm" style="font-size: 0.75rem; z-index: 2;">
                            <i class="bi bi-journal-text me-1"></i>১টি টপিক
                        </div>

                        <div class="card-body p-4 position-relative z-1 d-flex flex-column h-100">
                            <!-- Icon -->
                            <div class="disease-icon-wrapper mb-3 text-primary shadow-sm">
                                <?= $icon ?>
                            </div>
                            
                            <h5 class="fw-bold text-dark mb-2 pe-5" style="font-size: 1.05rem; line-height: 1.4; max-width: 80%;"><?= h($dis['name']) ?></h5>
                            
                            <p class="text-muted small mb-4 flex-grow-1" style="line-height: 1.5; font-size: 0.8rem; width: 65%;">
                                <?php
                                    $symptoms = strip_tags($dis['symptoms'] ?? '');
                                    echo mb_strlen($symptoms) > 50 ? mb_substr($symptoms, 0, 50) . '...' : ($symptoms ?: 'মাছের রোগের কারণ, লক্ষণ ও চিকিৎসা সম্পর্কে জানুন।');
                                ?>
                            </p>
                            
                            <div class="mt-auto">
                                <a href="<?= url('diseases/details.php?slug=' . urlencode($dis['slug'])) ?>" class="btn btn-primary rounded-pill btn-sm px-4 fw-bold disease-card-btn shadow-sm" style="background-color: #026ca6; border: none; position: relative; z-index: 5;">বিস্তারিত দেখুন &rarr;</a>
                            </div>
                        </div>

                        <!-- Floating Fish Image -->
                        <img src="<?= $real_image ?>" alt="<?= h($dis['name']) ?>" class="disease-fish-floating">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-5 d-flex justify-content-center">
            <?php render_pagination($pager, url('diseases/index.php')); ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
