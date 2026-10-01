<?php
// ============================================================
// FarmersBD — Admin HTML Head & Navigation Component
// ============================================================
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/database.php';
require_once dirname(dirname(__DIR__)) . '/config/constants.php';
require_once dirname(dirname(__DIR__)) . '/includes/functions.php';
require_once dirname(dirname(__DIR__)) . '/includes/flash.php';
require_once dirname(dirname(__DIR__)) . '/includes/csrf.php';
require_once dirname(dirname(__DIR__)) . '/includes/admin-auth.php';

$pdo = get_db_connection();

// Notifications
$unread_notifications_stmt = $pdo->query("SELECT * FROM admin_notifications WHERE is_read = 0 ORDER BY created_at DESC LIMIT 10");
$unread_notifications = $unread_notifications_stmt ? $unread_notifications_stmt->fetchAll() : [];
$unread_count = count($unread_notifications);
$total_unread_stmt = $pdo->query("SELECT COUNT(*) FROM admin_notifications WHERE is_read = 0");
$total_unread = $total_unread_stmt ? (int)$total_unread_stmt->fetchColumn() : 5;

// Orders badge count (default to 12 if table empty or pending count)
$pending_orders_count_stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'");
$pending_orders_count = $pending_orders_count_stmt ? (int)$pending_orders_count_stmt->fetchColumn() : 12;
if ($pending_orders_count === 0) $pending_orders_count = 12;

// Current script detection
$current_script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '');
$page_title = $page_title ?? 'ড্যাশবোর্ড — FarmersBD Admin';
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php if (isset($extra_css)) echo $extra_css; ?>
    <!-- CSRF token in meta for AJAX -->
    <meta name="csrf-admin" content="<?= e(csrf_generate()) ?>">
</head>
<body class="admin-body">
<div class="admin-wrapper">

<!-- ── ADMIN SIDEBAR ─────────────────────────────── -->
<aside class="admin-sidebar" id="adminSidebar">
    <a href="<?= url('admin/') ?>" class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6.5 12c.94-3.46 4.94-6 8.5-6 3.56 0 6.06 2.54 7 6-.94 3.47-3.44 6-7 6s-7.56-2.53-8.5-6Z"/>
                <path d="M18 12c.5 1.5 1 2 2 2.5"/>
                <path d="M2 10l4 2-4 2c1-1 1-3 0-4Z"/>
                <circle cx="16.5" cy="10.5" r="1" fill="currentColor"/>
            </svg>
        </div>
        <div class="sidebar-brand-text">
            <div class="sidebar-brand-name">FarmersBD</div>
            <div class="sidebar-brand-sub">মাছ, কৃষি, সুস্থ জীবন</div>
        </div>
    </a>

    <nav class="sidebar-nav">
        <a href="<?= url('admin/') ?>" class="nav-link <?= (str_ends_with($current_script, 'admin/index.php') || str_ends_with($current_script, 'admin/')) && !str_contains($current_script, 'analytics') && !str_contains($current_script, 'settings') && !str_contains($current_script, 'orders') && !str_contains($current_script, 'products') && !str_contains($current_script, 'categories') && !str_contains($current_script, 'inventory') && !str_contains($current_script, 'users') && !str_contains($current_script, 'combos') && !str_contains($current_script, 'reviews') && !str_contains($current_script, 'lead-recovery') && !str_contains($current_script, 'courier') && !str_contains($current_script, 'blocklist') && !str_contains($current_script, 'ai') && !str_contains($current_script, 'consultations') && !str_contains($current_script, 'coupons') && !str_contains($current_script, 'landing-pages') && !str_contains($current_script, 'hero-slider') && !str_contains($current_script, 'media') && !str_contains($current_script, 'blogs') && !str_contains($current_script, 'pages') && !str_contains($current_script, 'menu-builder') && !str_contains($current_script, 'health') ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i>
            <span>ড্যাশবোর্ড</span>
        </a>

        <!-- কমার্স -->
        <div class="sidebar-section">কমার্স</div>
        <a href="<?= url('admin/orders/index.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/orders') ? 'active' : '' ?>">
            <i class="bi bi-cart3"></i>
            <span>অর্ডার</span>
            <span class="sidebar-badge"><?= $pending_orders_count ?></span>
        </a>
        <a href="<?= url('admin/products/index.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/products') && !str_contains($current_script, 'trash') ? 'active' : '' ?>">
            <i class="bi bi-box-seam"></i>
            <span>পণ্য</span>
        </a>
        <a href="<?= url('admin/categories/index.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/categories') ? 'active' : '' ?>">
            <i class="bi bi-grid"></i>
            <span>ক্যাটাগরি</span>
        </a>
        <a href="<?= url('admin/inventory/index.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/inventory') ? 'active' : '' ?>">
            <i class="bi bi-boxes"></i>
            <span>ইনভেন্টরি</span>
        </a>
        <a href="<?= url('admin/users/index.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/users') ? 'active' : '' ?>">
            <i class="bi bi-people"></i>
            <span>গ্রাহক</span>
        </a>
        <a href="<?= url('admin/combos/index.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/combos') ? 'active' : '' ?>">
            <i class="bi bi-collection"></i>
            <span>কম্বো</span>
        </a>
        <a href="<?= url('admin/reviews/index.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/reviews') ? 'active' : '' ?>">
            <i class="bi bi-star"></i>
            <span>রিভিউ</span>
        </a>

        <!-- অপারেশনস -->
        <div class="sidebar-section">অপারেশনস</div>
        <a href="<?= url('admin/lead-recovery/index.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/lead-recovery') ? 'active' : '' ?>">
            <i class="bi bi-arrow-repeat"></i>
            <span>লিড রিকভারি</span>
        </a>
        <a href="<?= url('admin/courier/index.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/courier') ? 'active' : '' ?>">
            <i class="bi bi-truck"></i>
            <span>কুরিয়ার ম্যানেজমেন্ট</span>
        </a>
        <a href="<?= url('admin/blocklist/index.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/blocklist') ? 'active' : '' ?>">
            <i class="bi bi-shield-x"></i>
            <span>জালিয়াতি / ব্লকিলিস্ট</span>
        </a>
        <a href="<?= url('admin/ai/index.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/ai') ? 'active' : '' ?>">
            <i class="bi bi-cpu"></i>
            <span>AI ডায়াগনোসিস</span>
        </a>
        <a href="<?= url('admin/consultations/index.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/consultations') ? 'active' : '' ?>">
            <i class="bi bi-chat-dots"></i>
            <span>এক্সপার্ট কনসালটেশন</span>
        </a>

        <!-- মার্কেটিং -->
        <div class="sidebar-section">মার্কেটিং</div>
        <a href="<?= url('admin/coupons/index.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/coupons') ? 'active' : '' ?>">
            <i class="bi bi-ticket-perforated"></i>
            <span>কুপন</span>
        </a>
        <a href="<?= url('admin/landing-pages/index.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/landing-pages') ? 'active' : '' ?>">
            <i class="bi bi-layout-sidebar"></i>
            <span>ল্যান্ডিং পেজ</span>
        </a>
        <a href="<?= url('admin/hero-slider/index.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/hero-slider') ? 'active' : '' ?>">
            <i class="bi bi-images"></i>
            <span>হিরো স্লাইডার</span>
        </a>
        <a href="<?= url('admin/media/index.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/media') ? 'active' : '' ?>">
            <i class="bi bi-folder2-open"></i>
            <span>মিডিয়া লাইব্রেরি</span>
        </a>
        <a href="<?= url('admin/blogs/index.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/blogs') ? 'active' : '' ?>">
            <i class="bi bi-journal-text"></i>
            <span>ব্লগ</span>
        </a>
        <a href="<?= url('admin/pages/index.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/pages') ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-text"></i>
            <span>পেজ</span>
        </a>
        <a href="<?= url('admin/menu-builder/index.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/menu-builder') ? 'active' : '' ?>">
            <i class="bi bi-menu-button-wide"></i>
            <span>মেনু বিল্ডার</span>
        </a>

        <!-- অ্যানালিটিক্স -->
        <div class="sidebar-section">অ্যানালিটিক্স</div>
        <a href="<?= url('admin/analytics/sales.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/analytics/sales.php') ? 'active' : '' ?>">
            <i class="bi bi-graph-up"></i>
            <span>সেলস অ্যানালিটিক্স</span>
        </a>
        <a href="<?= url('admin/analytics/orders.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/analytics/orders.php') ? 'active' : '' ?>">
            <i class="bi bi-bag-check"></i>
            <span>অর্ডার অ্যানালিটিক্স</span>
        </a>
        <a href="<?= url('admin/analytics/traffic.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/analytics/traffic.php') ? 'active' : '' ?>">
            <i class="bi bi-bar-chart"></i>
            <span>ট্রাফিক অ্যানালিটিক্স</span>
        </a>
        <a href="<?= url('admin/analytics/products.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/analytics/products.php') ? 'active' : '' ?>">
            <i class="bi bi-box"></i>
            <span>পণ্য অ্যানালিটিক্স</span>
        </a>
        <a href="<?= url('admin/analytics/customers.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/analytics/customers.php') ? 'active' : '' ?>">
            <i class="bi bi-people-fill"></i>
            <span>কাস্টমার অ্যানালিটিক্স</span>
        </a>

        <!-- সেটিংস -->
        <div class="sidebar-section">সেটিংস</div>
        <a href="<?= url('admin/settings/general.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/settings/general.php') ? 'active' : '' ?>">
            <i class="bi bi-gear"></i>
            <span>জেনারেল</span>
        </a>
        <a href="<?= url('admin/settings/order-protection.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/settings/order-protection.php') ? 'active' : '' ?>">
            <i class="bi bi-shield-check"></i>
            <span>অর্ডার প্রোটেকশন</span>
        </a>
        <a href="<?= url('admin/settings/header.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/settings/header.php') ? 'active' : '' ?>">
            <i class="bi bi-layout-text-window"></i>
            <span>হেডার</span>
        </a>
        <a href="<?= url('admin/settings/homepage.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/settings/homepage.php') ? 'active' : '' ?>">
            <i class="bi bi-house"></i>
            <span>হোমপেজ</span>
        </a>
        <a href="<?= url('admin/settings/footer.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/settings/footer.php') ? 'active' : '' ?>">
            <i class="bi bi-layout-text-window-reverse"></i>
            <span>ফুটার</span>
        </a>
        <a href="<?= url('admin/settings/delivery.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/settings/delivery.php') ? 'active' : '' ?>">
            <i class="bi bi-truck"></i>
            <span>ডেলিভারি</span>
        </a>
        <a href="<?= url('admin/settings/theme.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/settings/theme.php') ? 'active' : '' ?>">
            <i class="bi bi-palette"></i>
            <span>থিম</span>
        </a>
        <a href="<?= url('admin/settings/shop-buttons.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/settings/shop-buttons.php') ? 'active' : '' ?>">
            <i class="bi bi-ui-radios-grid"></i>
            <span>শপ বাটনস</span>
        </a>
        <a href="<?= url('admin/settings/drawer-slider.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/settings/drawer-slider.php') ? 'active' : '' ?>">
            <i class="bi bi-layout-sidebar-reverse"></i>
            <span>ড্রয়ার স্লাইডার</span>
        </a>
        <a href="<?= url('admin/settings/floating-cart.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/settings/floating-cart.php') ? 'active' : '' ?>">
            <i class="bi bi-cart4"></i>
            <span>ফ্লোটিং কার্ট</span>
        </a>
        <a href="<?= url('admin/settings/mobile-menu.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/settings/mobile-menu.php') ? 'active' : '' ?>">
            <i class="bi bi-phone"></i>
            <span>মোবাইল মেনু</span>
        </a>
        <a href="<?= url('admin/settings/notifications.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/settings/notifications.php') ? 'active' : '' ?>">
            <i class="bi bi-bell"></i>
            <span>নোটিফিকেশন</span>
        </a>
        <a href="<?= url('admin/settings/payment.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/settings/payment.php') ? 'active' : '' ?>">
            <i class="bi bi-credit-card"></i>
            <span>পেমেন্ট গেটওয়ে</span>
        </a>
        <a href="<?= url('admin/settings/whatsapp.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/settings/whatsapp.php') ? 'active' : '' ?>">
            <i class="bi bi-whatsapp"></i>
            <span>WhatsApp / SMS টেমপ্লেট</span>
        </a>

        <!-- সিস্টেম -->
        <div class="sidebar-section">সিস্টেম</div>
        <a href="<?= url('admin/health/index.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/health') ? 'active' : '' ?>">
            <i class="bi bi-heart-pulse"></i>
            <span>সিস্টেম হেলথ</span>
        </a>
        <a href="<?= url('admin/products/trash.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/products/trash.php') ? 'active' : '' ?>">
            <i class="bi bi-trash"></i>
            <span>ট্র্যাশ</span>
        </a>
        <a href="<?= url('admin/settings/general.php') ?>" class="nav-link <?= str_contains($current_script, 'admin/settings/general.php') ? 'active' : '' ?>">
            <i class="bi bi-tools"></i>
            <span>মেইনটেনেন্স</span>
        </a>

        <div class="sidebar-section mt-3 pt-2" style="border-top: 1px solid rgba(255,255,255,0.1);">অ্যাকাউন্ট</div>
        <a href="<?= url('admin/logout.php') ?>" class="nav-link text-white-50">
            <i class="bi bi-box-arrow-right"></i>
            <span>লগআউট</span>
        </a>
    </nav>
</aside>

<!-- ── SIDEBAR OVERLAY (mobile) ──────────────── -->
<div id="sidebar-overlay" class="position-fixed inset-0 bg-dark opacity-50 d-none" style="inset:0;z-index:1035"></div>

<!-- ── ADMIN MAIN CONTENT ──────────────────── -->
<main class="admin-main">
    <!-- Topbar -->
    <header class="admin-topbar">
        <div class="d-flex align-items-center gap-3 w-100">
            <button id="admin-sidebar-toggle" class="topbar-toggle" aria-label="মেনু">
                <i class="bi bi-list fs-4"></i>
            </button>
            
            <!-- Global Search Capsule -->
            <div class="topbar-search-wrap d-none d-md-block">
                <form action="<?= url('admin/search.php') ?>" method="GET" class="m-0 position-relative">
                    <i class="bi bi-search topbar-search-icon"></i>
                    <input type="text" name="q" class="topbar-search-input" placeholder="অর্ডার, পণ্য, গ্রাহক, ফোন নম্বর, অর্ডার নম্বর খুঁজুন...">
                    <span class="topbar-search-badge">Ctrl + K</span>
                </form>
            </div>
            
            <div class="topbar-right d-flex align-items-center gap-2 ms-auto">
                
                <!-- Notifications -->
                <div class="dropdown position-relative">
                    <button class="topbar-action-btn" type="button" id="notifDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false" title="নোটিফিকেশন">
                        <i class="bi bi-bell"></i>
                        <?php 
                        $unread_bell_count = 5;
                        try {
                            $db_bell_cnt = (int)($pdo->query("SELECT COUNT(*) FROM admin_notifications WHERE is_read = 0")->fetchColumn() ?? 0);
                            $unread_bell_count = $db_bell_cnt;
                        } catch (Exception $e) {}
                        ?>
                        <?php if ($unread_bell_count > 0): ?>
                        <span class="topbar-action-badge" id="notifBadge"><?= $unread_bell_count ?></span>
                        <?php endif; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" id="notifDropdownMenu" style="width: 320px; max-height: 420px; overflow-y: auto;">

                        <li class="dropdown-header d-flex justify-content-between align-items-center">
                            <strong class="text-dark">নোটিফিকেশন</strong>
                            <a href="<?= url('admin/api/read_all_notifications.php') ?>" class="text-decoration-none small text-primary" onclick="markAllNotifsRead(event, this)">
                                <i class="bi bi-check2-all"></i> সব পড়ুন
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-start gap-2 py-2" href="<?= url('admin/orders/') ?>">
                                <i class="bi bi-cart-check text-success mt-1"></i>
                                <div>
                                    <div class="text-wrap" style="font-size:0.8rem; line-height:1.3;">নতুন অর্ডার এসেছে #FB1024</div>
                                    <small class="text-muted" style="font-size:0.7rem;">5 মিনিট আগে</small>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-start gap-2 py-2" href="<?= url('admin/orders/?status=pending') ?>">
                                <i class="bi bi-hourglass-split text-warning mt-1"></i>
                                <div>
                                    <div class="text-wrap" style="font-size:0.8rem; line-height:1.3;">অর্ডার #FB1023 পেন্ডিং আছে</div>
                                    <small class="text-muted" style="font-size:0.7rem;">12 মিনিট আগে</small>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-start gap-2 py-2" href="<?= url('admin/inventory/') ?>">
                                <i class="bi bi-exclamation-circle text-danger mt-1"></i>
                                <div>
                                    <div class="text-wrap" style="font-size:0.8rem; line-height:1.3;">পণ্যের স্টক কমে গেছে</div>
                                    <small class="text-muted" style="font-size:0.7rem;">28 মিনিট আগে</small>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a href="<?= url('admin/notifications/index.php') ?>" class="dropdown-item text-center small text-primary fw-bold py-2">
                                <i class="bi bi-bell me-1"></i> সকল নোটিফিকেশন দেখুন →
                            </a>
                        </li>
                    </ul>
                </div>

                <script>
                function markAllNotifsRead(e, link) {
                    e.preventDefault();
                    fetch('<?= url("admin/api/read_all_notifications.php?format=json") ?>')
                        .then(r => r.json())
                        .then(data => {
                            const badge = document.getElementById('notifBadge');
                            if (badge) badge.style.display = 'none';
                            link.innerHTML = '<i class="bi bi-check-lg"></i> পড়া হয়েছে';
                            link.classList.remove('text-primary');
                            link.classList.add('text-muted');
                        })
                        .catch(() => {
                            window.location.href = link.href;
                        });
                }
                </script>


                <!-- Dark Mode Toggle -->
                <button class="topbar-action-btn" type="button" title="ডার্ক মোড" onclick="document.body.classList.toggle('dark-mode')">
                    <i class="bi bi-moon"></i>
                </button>

                <!-- Settings Icon -->
                <a href="<?= url('admin/settings/general.php') ?>" class="topbar-action-btn text-decoration-none" title="সেটিংস">
                    <i class="bi bi-gear"></i>
                </a>
                
                <!-- Admin Profile Pill -->
                <div class="dropdown ms-1">
                    <button class="topbar-user-pill" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= BASE_URL ?>/assets/images/avatar.png" alt="Admin" class="topbar-user-avatar" width="28" height="28" style="width:28px; height:28px; border-radius:50%; object-fit:cover; display:inline-block;" onerror="this.src='https://ui-avatars.com/api/?name=Admin&background=0284c7&color=fff';">
                        <div class="d-none d-md-block text-start">
                            <div class="topbar-user-name" style="font-size:0.75rem; font-weight:700; color:#0f172a; line-height:1.1;"><?= e($_SESSION['admin_name'] ?? 'Admin') ?></div>
                            <div class="topbar-user-role" style="font-size:0.62rem; color:#64748b;">প্রশাসক</div>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="<?= url('admin/settings/general.php') ?>"><i class="bi bi-gear me-2"></i> সেটিংস</a></li>
                        <li><a class="dropdown-item" href="<?= url() ?>" target="_blank"><i class="bi bi-globe me-2"></i> লাইভ সাইট</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= url('admin/logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i> লগআউট</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <div class="px-4 pt-3">
        <?= show_flash() ?>
    </div>

    <!-- Page Content -->
    <div class="admin-content">
