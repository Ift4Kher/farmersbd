<?php
// ============================================================
// FarmersBD — Responsive Navbar Component
// ============================================================
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
$site_name  = setting('site_name', 'FarmersBD');
$site_logo  = setting('site_logo');
$cart_count = get_cart_count();
$current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';

function is_active_nav(string $pattern): string {
    global $current_path;
    if ($pattern === '/farmersbd/index' || $pattern === '/') {
        if ($current_path === '/farmersbd/' || $current_path === '/farmersbd/index.php' || $current_path === '/farmersbd' || $current_path === '/') {
            return ' active" aria-current="page';
        }
    }
    return str_starts_with($current_path, $pattern) ? ' active" aria-current="page' : '';
}
?>
<nav class="navbar navbar-expand-lg sticky-top shadow-sm main-site-navbar" id="mainNavbar" aria-label="প্রধান নেভিগেশন">
    <div class="container-fluid px-4 position-relative">
        <!-- Brand / Logo -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= url() ?>">
            <img src="<?= asset('assets/images/logo.png') ?>" alt="FarmersBD Agrovet Limited Logo" style="height: 80px; width: auto; object-fit: contain; margin-top: -10px; margin-bottom: -10px;">
        </a>

        <!-- Mobile Controls -->
        <div class="d-flex align-items-center d-lg-none gap-2">
            <!-- Mobile Search -->
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle d-flex justify-content-center align-items-center nav-circle-btn" 
                    data-bs-toggle="modal" data-bs-target="#navSearchModal" 
                    aria-label="অনুসন্ধান" title="অনুসন্ধান">
                <i class="bi bi-search text-dark"></i>
            </button>
            <!-- Mobile Cart -->
            <a href="<?= url('cart/') ?>" class="btn btn-outline-primary btn-sm position-relative nav-circle-btn" aria-label="কার্ট">
                <i class="bi bi-cart3"></i>
                <?php if ($cart_count > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark cart-badge" 
                      id="cart-count-badge"><?= $cart_count ?></span>
                <?php else: ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark cart-badge d-none" 
                      id="cart-count-badge">0</span>
                <?php endif; ?>
            </a>
            <!-- Hamburger -->
            <button class="navbar-toggler border-0 shadow-none ms-1" type="button" 
                    data-bs-toggle="collapse" data-bs-target="#navbarContent"
                    aria-controls="navbarContent" aria-expanded="false" aria-label="নেভিগেশন টগল">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <!-- Collapsible Nav -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav navbar-nav-center mb-2 mb-lg-0">
                <!-- 1. Home -->
                <li class="nav-item">
                    <a class="nav-link<?= is_active_nav('/farmersbd/index') ?>" href="<?= url() ?>">
                        <svg class="nav-svg-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <defs>
                                <linearGradient id="nav-home-roof" x1="2" y1="12" x2="12" y2="2">
                                    <stop offset="0%" stop-color="#38bdf8"/>
                                    <stop offset="100%" stop-color="#0284c7"/>
                                </linearGradient>
                                <linearGradient id="nav-home-wall" x1="4" y1="10" x2="20" y2="22">
                                    <stop offset="0%" stop-color="#ffffff"/>
                                    <stop offset="100%" stop-color="#cbd5e1"/>
                                </linearGradient>
                                <linearGradient id="nav-home-door" x1="10" y1="14" x2="14" y2="21">
                                    <stop offset="0%" stop-color="#fbbf24"/>
                                    <stop offset="100%" stop-color="#d97706"/>
                                </linearGradient>
                            </defs>
                            <path d="M12 2.5L2.5 10.5H5V20.5C5 21.05 5.45 21.5 6 21.5H18C18.55 21.5 19 21.05 19 20.5V10.5H21.5L12 2.5Z" fill="url(#nav-home-roof)"/>
                            <rect x="6.5" y="11" width="11" height="9.5" rx="1" fill="url(#nav-home-wall)"/>
                            <rect x="9.5" y="14" width="5" height="6.5" rx="1" fill="url(#nav-home-door)"/>
                        </svg>
                        <span>হোম</span>
                    </a>
                </li>

                <!-- 2. Fish Diseases -->
                <li class="nav-item">
                    <a class="nav-link<?= is_active_nav('/farmersbd/diseases') ?>" href="<?= url('diseases/') ?>">
                        <svg class="nav-svg-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <defs>
                                <linearGradient id="nav-dis-shield" x1="2" y1="2" x2="22" y2="22">
                                    <stop offset="0%" stop-color="#06b6d4"/>
                                    <stop offset="100%" stop-color="#0d9488"/>
                                </linearGradient>
                            </defs>
                            <path d="M12 2L3 6V12C3 17.55 6.84 22.74 12 24C17.16 22.74 21 17.55 21 12V6L12 2Z" fill="url(#nav-dis-shield)"/>
                            <path d="M11 7H13V11H17V13H13V17H11V13H7V11H11V7Z" fill="#ffffff"/>
                        </svg>
                        <span>মাছের রোগ</span>
                    </a>
                </li>

                <!-- 3. Consultation -->
                <li class="nav-item">
                    <a class="nav-link<?= is_active_nav('/farmersbd/consultation') ?>" href="<?= url('consultation/') ?>">
                        <svg class="nav-svg-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <defs>
                                <linearGradient id="nav-chat-grad" x1="2" y1="2" x2="22" y2="22">
                                    <stop offset="0%" stop-color="#0284c7"/>
                                    <stop offset="100%" stop-color="#0369a1"/>
                                </linearGradient>
                                <linearGradient id="nav-headset-grad" x1="0" y1="0" x2="24" y2="24">
                                    <stop offset="0%" stop-color="#fbbf24"/>
                                    <stop offset="100%" stop-color="#d97706"/>
                                </linearGradient>
                            </defs>
                            <path d="M20 2H4C2.9 2 2 2.9 2 4V16C2 17.1 2.9 18 4 18H18L22 22V4C22 2.9 21.1 2 20 2Z" fill="url(#nav-chat-grad)"/>
                            <path d="M7 8C7 6.5 9.2 5 12 5C14.8 5 17 6.5 17 8V12C17 12.55 16.55 13 16 13H15V9H17V8C17 6.9 14.8 6 12 6C9.2 6 7 6.9 7 8V9H9V13H8C7.45 13 7 12.55 7 12V8Z" fill="#ffffff"/>
                            <circle cx="15.5" cy="11" r="1.5" fill="url(#nav-headset-grad)"/>
                        </svg>
                        <span>পরামর্শ</span>
                    </a>
                </li>

                <!-- 4. Medicine / Products -->
                <li class="nav-item">
                    <a class="nav-link<?= is_active_nav('/farmersbd/products') ?>" href="<?= url('products/') ?>">
                        <svg class="nav-svg-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <defs>
                                <linearGradient id="nav-pill-left" x1="4" y1="4" x2="12" y2="12">
                                    <stop offset="0%" stop-color="#06b6d4"/>
                                    <stop offset="100%" stop-color="#0284c7"/>
                                </linearGradient>
                                <linearGradient id="nav-pill-right" x1="12" y1="12" x2="20" y2="20">
                                    <stop offset="0%" stop-color="#fbbf24"/>
                                    <stop offset="100%" stop-color="#f59e0b"/>
                                </linearGradient>
                            </defs>
                            <g transform="rotate(-45 12 12)">
                                <rect x="7" y="3" width="10" height="9" rx="5" fill="url(#nav-pill-left)"/>
                                <rect x="7" y="12" width="10" height="9" rx="5" fill="url(#nav-pill-right)"/>
                                <line x1="7" y1="12" x2="17" y2="12" stroke="#ffffff" stroke-width="1.2" opacity="0.7"/>
                            </g>
                        </svg>
                        <span>ওষুধ</span>
                    </a>
                </li>

                <!-- 5. Blog -->
                <li class="nav-item">
                    <a class="nav-link<?= is_active_nav('/farmersbd/blog') ?>" href="<?= url('blog/') ?>">
                        <svg class="nav-svg-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <defs>
                                <linearGradient id="nav-book-cover" x1="3" y1="3" x2="21" y2="21">
                                    <stop offset="0%" stop-color="#0284c7"/>
                                    <stop offset="100%" stop-color="#0f172a"/>
                                </linearGradient>
                                <linearGradient id="nav-book-ribbon" x1="14" y1="3" x2="17" y2="14">
                                    <stop offset="0%" stop-color="#fbbf24"/>
                                    <stop offset="100%" stop-color="#d97706"/>
                                </linearGradient>
                            </defs>
                            <path d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3Z" fill="url(#nav-book-cover)"/>
                            <rect x="6" y="7" width="12" height="2" rx="1" fill="#ffffff"/>
                            <rect x="6" y="11" width="8" height="2" rx="1" fill="#cbd5e1"/>
                            <rect x="6" y="15" width="10" height="2" rx="1" fill="#cbd5e1"/>
                            <path d="M14 3V11L16.5 9.2L19 11V3H14Z" fill="url(#nav-book-ribbon)"/>
                        </svg>
                        <span>ব্লগ</span>
                    </a>
                </li>

                <!-- 6. Contact -->
                <li class="nav-item">
                    <a class="nav-link<?= is_active_nav('/farmersbd/contact') ?>" href="<?= url('contact/') ?>">
                        <svg class="nav-svg-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <defs>
                                <linearGradient id="nav-env-body" x1="2" y1="4" x2="22" y2="20">
                                    <stop offset="0%" stop-color="#0284c7"/>
                                    <stop offset="100%" stop-color="#0d9488"/>
                                </linearGradient>
                            </defs>
                            <rect x="2" y="4" width="20" height="16" rx="3" fill="url(#nav-env-body)"/>
                            <path d="M2 5L12 13L22 5" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="18" cy="7" r="3" fill="#f59e0b" stroke="#ffffff" stroke-width="1"/>
                        </svg>
                        <span>যোগাযোগ</span>
                    </a>
                </li>
            </ul>

            <!-- Right side: Auth + Cart + Search -->
            <ul class="navbar-nav ms-auto align-items-center gap-2 navbar-right-side">
                <!-- Search Button -->
                <li class="nav-item d-none d-lg-block">
                    <button type="button" class="btn btn-nav-circle" 
                            data-bs-toggle="modal" data-bs-target="#navSearchModal" 
                            aria-label="অনুসন্ধান" title="অনুসন্ধান">
                        <i class="bi bi-search fs-6"></i>
                    </button>
                </li>
                
                <!-- Cart Button -->
                <li class="nav-item d-none d-lg-block">
                    <a href="<?= url('cart/') ?>" class="btn btn-nav-circle position-relative" title="কার্ট">
                        <i class="bi bi-cart3 fs-6"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark cart-badge<?= $cart_count === 0 ? ' d-none' : '' ?>" id="cart-count-badge-desktop">
                            <?= $cart_count ?>
                        </span>
                    </a>
                </li>


                <?php if (is_logged_in()): ?>
                <!-- User dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-1 btn-nav-user" href="#" 
                       id="userMenuBtn" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle fs-5 text-primary"></i>
                        <span class="d-none d-lg-inline fw-semibold"><?= e($_SESSION['user_name'] ?? 'অ্যাকাউন্ট') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2" aria-labelledby="userMenuBtn">
                        <li><a class="dropdown-item py-2" href="<?= url('account/dashboard.php') ?>"><i class="bi bi-speedometer2 me-2 text-primary"></i>ড্যাশবোর্ড</a></li>
                        <li><a class="dropdown-item py-2" href="<?= url('account/orders.php') ?>"><i class="bi bi-bag me-2 text-primary"></i>অর্ডার সমূহ</a></li>
                        <li><a class="dropdown-item py-2" href="<?= url('account/ai-history.php') ?>"><i class="bi bi-clock-history me-2 text-primary"></i>AI ইতিহাস</a></li>
                        <li><a class="dropdown-item py-2" href="<?= url('account/consultations.php') ?>"><i class="bi bi-chat-text me-2 text-primary"></i>পরামর্শ</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2" href="<?= url('account/profile.php') ?>"><i class="bi bi-gear me-2 text-secondary"></i>প্রোফাইল</a></li>
                        <li><a class="dropdown-item py-2 text-danger" href="<?= url('auth/logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i>লগআউট</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="btn btn-aquatic-login d-flex align-items-center gap-1" href="<?= url('auth/login.php') ?>">
                        <i class="bi bi-person-lock me-1"></i>
                        <span>লগইন / রেজিস্টার</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- ── Global Search Modal (Lightweight Icy Aqua Design) ──────────────── -->
<link rel="stylesheet" href="<?= asset('assets/css/search.css') ?>?v=<?= time() ?>">
<div class="modal fade" id="navSearchModal" tabindex="-1" aria-labelledby="navSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 bg-transparent shadow-none">
            
            <div class="search-page-wrapper modal-search-wrapper position-relative rounded-4 overflow-hidden shadow-lg w-100" style="min-height: auto; padding: 40px 0 60px 0;">
                
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3 z-3" data-bs-dismiss="modal" aria-label="বন্ধ করুন" style="background-color: rgba(255,255,255,0.5); border-radius: 50%; padding: 0.8rem;"></button>

                <div class="search-page-content">
                    <div class="container text-center px-2 px-md-4">
                        
                        <!-- Logo and Header -->
                        <div class="search-header mb-4">
                            <h2 class="search-title fs-2">FarmersBD অনুসন্ধান</h2>
                            <p class="search-subtitle fs-6">পণ্য, ঔষধ, মাছের রোগ বা পরামর্শ সহজেই খুঁজুন!</p>
                        </div>
                        
                        <!-- Category Tabs -->
                        <div class="search-tabs mb-4 justify-content-center">
                            <label class="search-tab">
                                <input type="radio" name="search_category" value="medicine" checked>
                                <div class="search-tab-content" style="min-width: 110px; padding: 12px;">
                                    <div class="search-tab-icon mb-1 fs-3">
                                        <i class="bi bi-capsule"></i>
                                    </div>
                                    <span style="font-size: 0.9rem;">ঔষধ ও পণ্য</span>
                                    <div class="search-tab-indicator"></div>
                                </div>
                            </label>
                            
                            <label class="search-tab">
                                <input type="radio" name="search_category" value="disease">
                                <div class="search-tab-content" style="min-width: 110px; padding: 12px;">
                                    <div class="search-tab-icon mb-1 fs-3">
                                        <i class="bi bi-virus"></i>
                                    </div>
                                    <span style="font-size: 0.9rem;">মাছের রোগ</span>
                                    <div class="search-tab-indicator"></div>
                                </div>
                            </label>
                            
                            <label class="search-tab">
                                <input type="radio" name="search_category" value="blog">
                                <div class="search-tab-content" style="min-width: 110px; padding: 12px;">
                                    <div class="search-tab-icon mb-1 fs-3">
                                        <i class="bi bi-journal-text"></i>
                                    </div>
                                    <span style="font-size: 0.9rem;">ব্লগ ও পরামর্শ</span>
                                    <div class="search-tab-indicator"></div>
                                </div>
                            </label>
                            
                            <label class="search-tab">
                                <input type="radio" name="search_category" value="info">
                                <div class="search-tab-content" style="min-width: 110px; padding: 12px;">
                                    <div class="search-tab-icon mb-1 fs-3">
                                        <i class="bi bi-water"></i>
                                    </div>
                                    <span style="font-size: 0.9rem;">মাছের তথ্য</span>
                                    <div class="search-tab-indicator"></div>
                                </div>
                            </label>
                            
                            <label class="search-tab">
                                <input type="radio" name="search_category" value="expert">
                                <div class="search-tab-content" style="min-width: 110px; padding: 12px;">
                                    <div class="search-tab-icon mb-1 fs-3">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <span style="font-size: 0.9rem;">বিশেষজ্ঞ সেবা</span>
                                    <div class="search-tab-indicator"></div>
                                </div>
                            </label>
                        </div>
                        
                        <!-- Search Bar Form (Kept ID for existing JS if needed) -->
                        <form id="globalSearchForm" method="GET" action="<?= url('products/') ?>" class="search-form mb-4 mx-auto" style="max-width: 700px;">
                            <div class="search-input-group p-1 ps-3">
                                <div class="search-input-icon">
                                    <i class="bi bi-search"></i>
                                </div>
                                <input type="search" name="q" id="globalSearchInput" class="search-input" placeholder="উদাহরণ: পাংগাস, মাছের রোগ, ভিটামিন..." autocomplete="off">
                                <button type="submit" class="search-btn py-2 px-3">
                                    <i class="bi bi-search me-1 d-none d-sm-inline"></i> খুঁজুন <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </form>
                        
                        <!-- Live Search Results & Suggestions Container (from original modal) -->
                        <div id="searchLiveResults" class="mt-3 d-none mx-auto bg-white rounded-3 shadow-sm p-3 text-start mb-4" style="max-width: 700px;">
                            <!-- Grouped Instant Results Header -->
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom border-info-subtle">
                                <span class="text-muted small fw-semibold d-flex align-items-center gap-1">
                                    <i class="bi bi-stars text-info"></i>
                                    <span>তাৎক্ষণিক ফলাফল</span>
                                </span>
                                <span id="searchResultCount" class="badge banner-count-badge">০ টি পাওয়া গেছে</span>
                            </div>

                            <div id="searchResultsList" class="d-flex flex-column gap-2 banner-results-scroll" style="max-height: 250px; overflow-y: auto;" role="listbox">
                                <!-- Dynamically populated via AJAX -->
                            </div>
                        </div>

                        <!-- Popular Searches -->
                        <div class="search-popular mt-4 position-relative z-3">
                            <div class="popular-title mb-2">
                                <i class="bi bi-fire text-primary"></i> জনপ্রিয় অনুসন্ধান:
                            </div>
                            <div class="popular-tags">
                                <a href="#" class="popular-tag py-1 px-3" style="font-size: 0.85rem;">
                                    <i class="bi bi-capsule fs-6"></i> অ্যামোক্সিসিলিন
                                </a>
                                <a href="#" class="popular-tag py-1 px-3" style="font-size: 0.85rem;">
                                    <i class="bi bi-shield-plus fs-6"></i> মাছ চাষ রোগ
                                </a>
                                <a href="#" class="popular-tag py-1 px-3" style="font-size: 0.85rem;">
                                    <i class="bi bi-droplet-half fs-6"></i> জীবাণুনাশক
                                </a>
                                <a href="#" class="popular-tag py-1 px-3" style="font-size: 0.85rem;">
                                    <i class="bi bi-capsule-pill fs-6"></i> ভিটামিন
                                </a>
                            </div>
                        </div>
                        
                    </div>
                </div>
                
                <!-- Background Decor (Waves and Leaves) -->
                <div class="search-bg-decor">
                    <svg class="search-wave" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                        <!-- Back Wave -->
                        <path fill="#bae6fd" fill-opacity="0.6" d="M0,192L48,208C96,224,192,256,288,256C384,256,480,224,576,202.7C672,181,768,171,864,181.3C960,192,1056,224,1152,245.3C1248,267,1344,277,1392,282.7L1440,288L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                        <!-- Middle Wave -->
                        <path fill="#7dd3fc" fill-opacity="0.8" d="M0,288L48,272C96,256,192,224,288,213.3C384,203,480,213,576,234.7C672,256,768,288,864,288C960,288,1056,256,1152,224C1248,192,1344,160,1392,144L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                        <!-- Front Wave -->
                        <path fill="#38bdf8" fill-opacity="1" d="M0,256L48,250.7C96,245,192,235,288,245.3C384,256,480,288,576,288C672,288,768,256,864,229.3C960,203,1056,181,1152,192C1248,203,1344,245,1392,266.7L1440,288L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Flash messages -->
<div class="container mt-2">
    <?= show_flash() ?>
</div>
