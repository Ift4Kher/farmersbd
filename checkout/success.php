<?php
// ============================================================
// FarmersBD — SSLCOMMERZ Payment Callbacks & Order Success
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/flash.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/seo.php';
require_once dirname(__DIR__) . '/services/PaymentService.php';

// ── Handle SSLCOMMERZ success POST callback ──────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valId   = $_POST['val_id']   ?? '';
    $tranId  = $_POST['tran_id']  ?? '';    // This is our order_number
    $amount  = (float)($_POST['amount'] ?? 0);

    if (empty($valId) || empty($tranId)) {
        flash('পেমেন্ট তথ্য অসম্পূর্ণ।', FLASH_ERROR);
        redirect(BASE_URL . '/');
    }

    $order = db_query_one("SELECT * FROM orders WHERE order_number = ?", [$tranId]);

    if (!$order) {
        flash('অর্ডার পাওয়া যায়নি।', FLASH_ERROR);
        redirect(BASE_URL . '/');
    }

    if ($order['payment_status'] === 'paid') {
        // Already processed (double-callback protection)
        redirect(BASE_URL . '/checkout/success.php?order=' . urlencode($tranId));
    }

    // Server-side validation against SSLCOMMERZ API
    $payService = new PaymentService();
    $validation = $payService->validate($valId, (float)$order['total']);

    if ($validation['valid']) {
        $payService->complete_payment(
            (int)$order['id'],
            $tranId,
            $valId,
            (float)$order['total'],
            $validation['data']
        );
        flash('পেমেন্ট সফলভাবে সম্পন্ন হয়েছে!', FLASH_SUCCESS);
        redirect(BASE_URL . '/checkout/success.php?order=' . urlencode($tranId));
    } else {
        error_log('[Payment] Validation failed for tran_id: ' . $tranId);
        db_execute(
            "UPDATE orders SET payment_status = 'failed' WHERE order_number = ?",
            [$tranId]
        );
        flash('পেমেন্ট যাচাই ব্যর্থ হয়েছে।', FLASH_ERROR);
        redirect(BASE_URL . '/checkout/failed.php');
    }
}

// ── GET: Order confirmation page ─────────────────────────────
$orderNum = $_GET['order'] ?? '';
$order    = $orderNum ? db_query_one("SELECT * FROM orders WHERE order_number = ?", [$orderNum]) : null;

// Security: logged-in users can only see their own orders (guests see if order num matches)
if ($order && is_logged_in() && $order['user_id'] && (int)$order['user_id'] !== (int)($_SESSION['user_id'] ?? 0)) {
    $order = null;
}

$orderItems = [];
if ($order) {
    // Fetch items with product images
    $orderItems = db_query(
        "SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?",
        [$order['id']]
    );
}

$page_seo = ['title' => 'অর্ডার সফল | ' . setting('site_name', 'FarmersBD')];
include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/navbar.php';
?>

<link rel="stylesheet" href="<?= asset('assets/css/success.css') ?>?v=<?= time() ?>">

<?php if ($order): ?>
<!-- Order Success UI -->
<div class="succ-hero">
    <div class="succ-hero-bg">
        <div class="succ-hero-bg-left"></div>
        <div class="succ-hero-bg-right"></div>
    </div>
    <div class="container succ-hero-content">
        <div class="succ-icon-wrapper">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
        </div>
        <h1 class="succ-title">অর্ডার সফলভাবে সম্পন্ন হয়েছে!</h1>
        <p class="succ-subtitle">আপনার অর্ডারটি সফলভাবে গ্রহণ করা হয়েছে। আমরা দ্রুত আপনার কাছে পণ্য পৌঁছে দেওয়ার জন্য কাজ করছি।</p>
        <div class="succ-badge">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            অর্ডার নম্বর: <?= e($order['order_number']) ?>
        </div>
    </div>
</div>

<div class="succ-content">
    <div class="container">
        <div class="row g-4">
            <!-- Left: Order Details & Features -->
            <div class="col-lg-7">
                <div class="succ-features">
        <div class="succ-feature">
            <div class="succ-feature-icon icon-teal">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
            </div>
            <div class="succ-feature-text">
                <h6>নিরাপদ লেনদেন</h6>
                <p>আপনার তথ্য ১০০% নিরাপদ</p>
            </div>
        </div>
        <div class="succ-feature">
            <div class="succ-feature-icon icon-green">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            </div>
            <div class="succ-feature-text">
                <h6>দ্রুত ডেলিভারি</h6>
                <p>সঠিক সময়ে, সঠিক পণ্য</p>
            </div>
        </div>
        <div class="succ-feature">
            <div class="succ-feature-icon icon-blue">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
            </div>
            <div class="succ-feature-text">
                <h6>বিশেষজ্ঞ পরামর্শ</h6>
                <p>সবসময় আপনার সাথে</p>
            </div>
        </div>
        </div>
        </div>
        <!-- Spacer for right side so right card is pushed down -->
        <div class="col-lg-5"></div>
    </div>
    
    <!-- Second row for cards -->
    <div class="row g-4 mt-2">
        <!-- Left: Order Details -->
        <div class="col-lg-7">
            <div class="succ-card">
                <div class="succ-card-header">
                        <div class="succ-card-header-icon">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16zM12 2.73l5.5 3.14L12 9l-5.5-3.14L12 2.73zM5.5 7.6L11 10.74v6.27l-5.5-3.14V7.6zm13 6.27l-5.5 3.14v-6.27L18.5 7.6v6.27z"/></svg>
                        </div>
                        অর্ডার বিবরণ
                    </div>
                    <div class="succ-card-body">
                        <?php foreach ($orderItems as $item): ?>
                        <div class="succ-item">
                            <?php if ($item['image']): ?>
                                <img src="<?= uploaded_image_url('products', $item['image']) ?>" alt="<?= e($item['product_name']) ?>" class="succ-item-img">
                            <?php else: ?>
                                <img src="<?= asset('assets/images/no-image.png') ?>" alt="<?= e($item['product_name']) ?>" class="succ-item-img">
                            <?php endif; ?>
                            
                            <div class="succ-item-info">
                                <div class="succ-item-title"><?= e($item['product_name']) ?></div>
                                <div class="succ-item-qty">× <?= (int)$item['quantity'] ?></div>
                            </div>
                            <div class="succ-item-price"><?= format_price($item['subtotal']) ?></div>
                        </div>
                        <?php endforeach; ?>

                        <div class="succ-summary-row mt-4">
                            <span>উপমোট</span>
                            <span style="font-weight:700;color:#0f172a;"><?= format_price($order['subtotal']) ?></span>
                        </div>
                        <div class="succ-summary-row mb-4">
                            <span>ডেলিভারি চার্জ</span>
                            <span style="font-weight:700;color:#0f172a;"><?= format_price($order['shipping_cost']) ?></span>
                        </div>
                    </div>
                    <div class="succ-summary-total">
                        <span>মোট পরিমাণ</span>
                        <span><?= format_price($order['total']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Right: Next Steps -->
            <div class="col-lg-5">
                <div class="succ-next-steps">
                    <div class="succ-ns-icon">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                    </div>
                    <h2 class="succ-ns-title">এখন কি করবেন?</h2>
                    <p class="succ-ns-desc">আপনার অর্ডারটি ট্র্যাক করতে বা অন্য কোনো প্রয়োজন হলে নিচের বাটনগুলো ব্যবহার করুন।</p>
                    

                    
                    <a href="<?= url() ?>" class="succ-btn-outline">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        হোম পেজে যান
                    </a>
                    
                    <a href="<?= url('products/') ?>" class="succ-btn-outline">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        আরো পণ্য কিনুন
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Fallback if no order found -->
<div class="container py-5 text-center">
    <div class="succ-icon-wrapper mx-auto" style="margin-bottom:1.5rem;">
        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
    </div>
    <h1 class="succ-title">অর্ডার সফলভাবে দেওয়া হয়েছে!</h1>
    <p class="succ-subtitle">ধন্যবাদ আপনার অর্ডারের জন্য।</p>
    <a href="<?= url() ?>" class="succ-btn-primary d-inline-flex w-auto mt-3" style="padding:0.75rem 2rem;">হোমে যান</a>
</div>
<?php endif; ?>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
