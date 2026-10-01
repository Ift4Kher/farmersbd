<?php
// ============================================================
// FarmersBD — Cart Page
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/flash.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/seo.php';

$page_seo = ['title' => 'আপনার কার্ট | ' . setting('site_name', 'FarmersBD')];

$userId = $_SESSION['user_id'] ?? null;
$cartId = get_or_create_cart($userId);


$cartItems = db_query(
    "SELECT ci.id AS item_id, ci.quantity,
            p.id AS product_id, p.name, p.slug, p.image, p.price, p.discount_price, p.stock, p.is_active,
            p.description, c.name AS category_name
     FROM cart_items ci
     JOIN products p ON p.id = ci.product_id
     LEFT JOIN product_categories c ON c.id = p.category_id
     WHERE ci.cart_id = ?
     ORDER BY ci.created_at DESC",
    [$cartId]
);

$subtotal     = 0.0;
$itemCount    = 0;
$shippingCost = (float) setting('shipping_cost', 60);

foreach ($cartItems as $item) {
    $subtotal  += effective_price($item) * $item['quantity'];
    $itemCount += (int)$item['quantity'];
}

$freeShippingThreshold = 1000;
if ($subtotal >= $freeShippingThreshold || $subtotal == 0) {
    $shippingCost = 0;
}
$total = $subtotal + $shippingCost;

$csrf = csrf_generate();
include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/navbar.php';
?>
<link rel="stylesheet" href="<?= asset('assets/css/cart.css') ?>?v=<?= time() ?>">

<!-- ── CART HERO BANNER ───────────────────────────────────── -->
<div class="cart-hero-banner">
    <div class="container position-relative">
        <nav aria-label="breadcrumb" class="cart-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url() ?>"><i class="bi bi-house-door-fill me-1"></i>হোম</a></li>
                <li class="breadcrumb-item active" aria-current="page">কার্ট</li>
            </ol>
        </nav>
        <div class="cart-hero-content">
            <div>
                <h1 class="cart-hero-title">আপনার কার্ট</h1>
                <p class="cart-hero-sub">
                    <?php if (empty($cartItems)): ?>
                        আপনার কার্টে এখনো কোনো পণ্য নেই।<br>
                        চমৎকার কিছু মাছ ও পণ্য যোগ করুন!
                    <?php else: ?>
                        আপনার পছন্দের টাটকা মাছগুলো এখানে রয়েছে।<br>
                        সুস্থ ও নিরাপদ খাবারের জন্য এখনই অর্ডার করুন।
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Hero Banner Decorative Graphics (Simulated via CSS/SVGs in stylesheet) -->
    <div class="cart-hero-decor"></div>
</div>

<!-- ── MAIN CART SECTION ──────────────────────────────────── -->
<div class="cart-page-wrapper">
    <div class="container position-relative">
        <?php if (empty($cartItems)): ?>
        <div class="cart-empty-state text-center my-4">
            <!-- Seaweed decors -->
            <div class="seaweed seaweed-left"></div>
            <div class="seaweed seaweed-right"></div>
            
            <div class="cart-empty-content">
                <!-- Sad Cart Graphic -->
                <div class="sad-cart-graphic mx-auto mb-4">
                    <svg width="220" height="150" viewBox="0 0 220 150" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Water splashes background -->
                        <path d="M40,110 C20,110 20,80 40,80 C60,80 60,110 40,110 Z" fill="#e0f2fe"/>
                        <path d="M180,110 C200,110 200,80 180,80 C160,80 160,110 180,110 Z" fill="#e0f2fe"/>
                        <path d="M110,130 C60,130 50,110 110,110 C170,110 160,130 110,130 Z" fill="#e0f2fe"/>
                        <!-- Additional splashes -->
                        <circle cx="30" cy="70" r="10" fill="#e0f2fe"/>
                        <circle cx="190" cy="60" r="8" fill="#e0f2fe"/>
                        <circle cx="150" cy="40" r="6" fill="#e0f2fe"/>
                        <circle cx="70" cy="45" r="5" fill="#e0f2fe"/>
                        
                        <!-- Cart Body -->
                        <path d="M60,40 L70,100 L150,100 L160,40 Z" fill="#0284c7"/>
                        <path d="M60,40 L70,100 L150,100 L160,40 Z" stroke="#0f172a" stroke-width="4" stroke-linejoin="round"/>
                        <path d="M60,40 L160,40" stroke="#0f172a" stroke-width="6" stroke-linecap="round"/>
                        
                        <!-- Cart Handle -->
                        <path d="M40,30 L60,40" stroke="#0f172a" stroke-width="6" stroke-linecap="round"/>
                        
                        <!-- Wheels -->
                        <circle cx="90" cy="115" r="8" fill="#f8fafc" stroke="#0f172a" stroke-width="4"/>
                        <circle cx="130" cy="115" r="8" fill="#f8fafc" stroke="#0f172a" stroke-width="4"/>
                        
                        <!-- Sad Face inside Cart -->
                        <circle cx="110" cy="70" r="18" fill="#bae6fd" stroke="#0f172a" stroke-width="3"/>
                        <circle cx="103" cy="65" r="2.5" fill="#0f172a"/>
                        <circle cx="117" cy="65" r="2.5" fill="#0f172a"/>
                        <path d="M103,78 Q110,72 117,78" stroke="#0f172a" stroke-width="2.5" stroke-linecap="round" fill="none"/>
                    </svg>
                </div>
                
                <h2 class="cart-empty-title">আপনার কার্টটি খালি</h2>
                <p class="cart-empty-subtitle mb-4">আপনার কার্টে বর্তমানে কোনো পণ্য নেই। নতুন কিছু অর্ডার দিতে পণ্য তালিকায় যান।</p>
                <a href="<?= url('products/') ?>" class="btn btn-empty-action">
                    <i class="bi bi-bag-fill me-2"></i>পণ্যসমূহ দেখুন <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
        </div>

        <!-- ── FEATURES SECTION (Only in Empty State) ─────────────────────── -->
        <div class="row g-4 cart-empty-features mt-2 mb-5">
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center cart-feature-item">
                    <div class="cart-feature-icon bg-success-subtle text-success">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div class="ms-3 text-start">
                        <h6 class="mb-0 fw-bold">১০০% নিরাপদ মাছ</h6>
                        <small class="text-muted">সরাসরি খামার থেকে</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center cart-feature-item">
                    <div class="cart-feature-icon bg-primary-subtle text-primary">
                        <i class="bi bi-patch-check-fill"></i>
                    </div>
                    <div class="ms-3 text-start">
                        <h6 class="mb-0 fw-bold">নিরাপত্তা ও স্বাস্থ্যকর</h6>
                        <small class="text-muted">পরিবারের সবার জন্য</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center cart-feature-item">
                    <div class="cart-feature-icon bg-info-subtle text-info">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div class="ms-3 text-start">
                        <h6 class="mb-0 fw-bold">দ্রুত ডেলিভারি</h6>
                        <small class="text-muted">আপনার সুবিধামত সময়ে</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center cart-feature-item">
                    <div class="cart-feature-icon" style="background-color: #f3e8ff; color: #a855f7;">
                        <i class="bi bi-headset"></i>
                    </div>
                    <div class="ms-3 text-start">
                        <h6 class="mb-0 fw-bold">সার্বক্ষণিক সাপোর্ট</h6>
                        <small class="text-muted">আমরা আছি আপনার পাশে</small>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="row g-4 align-items-start">
            <!-- Left Column: Cart Items -->
            <div class="col-12 col-lg-8">
                <div class="cart-table-card">
                    <div class="table-responsive">
                        <table class="cart-ui-table">
                            <thead>
                                <tr>
                                    <th style="width: 44%;">পণ্য</th>
                                    <th class="text-center" style="width: 16%;">দাম</th>
                                    <th class="text-center" style="width: 18%;">পরিমাণ</th>
                                    <th class="text-center" style="width: 14%;">মোট</th>
                                    <th class="text-center" style="width: 8%;">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $item):
                                    $unitPrice = effective_price($item);
                                    $linePrc   = $unitPrice * $item['quantity'];
                                    $maxQty    = max(1, (int)$item['stock']);
                                    $subDesc   = !empty($item['description']) ? $item['description'] : 'তাঁজা দেশী মাছ (প্রতি কেজি)';
                                ?>
                                <tr class="cart-row">
                                    <!-- Product Info -->
                                    <td class="cart-col-info">
                                        <div class="cart-prod-cell">
                                            <img src="<?= uploaded_image_url('products', $item['image']) ?>"
                                                 alt="<?= e($item['name']) ?>"
                                                 class="cart-prod-img">
                                            <div class="cart-prod-info">
                                                <h4>
                                                    <a href="<?= url('products/details.php?slug=' . e($item['slug'])) ?>">
                                                        <?= e($item['name']) ?>
                                                    </a>
                                                </h4>
                                                <p><?= e(excerpt($subDesc, 40)) ?></p>
                                                <span class="badge-fresh-ui">
                                                    <i class="bi bi-check-circle-fill"></i> ফ্রেশ
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Unit Price -->
                                    <td class="text-center cart-col-price">
                                        <div class="cart-price-text"><?= format_price_bn((int)round($unitPrice)) ?></div>
                                        <span class="cart-price-unit">প্রতি কেজি</span>
                                    </td>

                                    <!-- Quantity Stepper -->
                                    <td class="text-center cart-col-qty">
                                        <div class="cart-stepper">
                                            <button type="button" class="cart-stepper-btn decrease"
                                                    data-action="qty-decrease" aria-label="কমান">−</button>
                                            <input type="number"
                                                   class="cart-stepper-input cart-qty-input"
                                                   value="<?= (int)$item['quantity'] ?>"
                                                   min="1"
                                                   max="<?= $maxQty ?>"
                                                   data-item-id="<?= (int)$item['item_id'] ?>"
                                                   data-csrf="<?= $csrf ?>"
                                                   readonly
                                                   aria-label="পরিমাণ">
                                            <button type="button" class="cart-stepper-btn increase"
                                                    data-action="qty-increase" aria-label="বাড়ান">+</button>
                                        </div>
                                    </td>

                                    <!-- Line Total -->
                                    <td class="text-center cart-col-total">
                                        <div class="cart-item-total" data-role="item-subtotal">
                                            <?= format_price_bn((int)round($linePrc)) ?>
                                        </div>
                                    </td>

                                    <!-- Delete Action -->
                                    <td class="text-center cart-col-delete">
                                        <button type="button"
                                                class="cart-btn-delete mx-auto border-0 bg-transparent p-0"
                                                data-action="remove-cart-item"
                                                data-item-id="<?= (int)$item['item_id'] ?>"
                                                data-csrf="<?= $csrf ?>"
                                                title="সরান"
                                                aria-label="সরান">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Continue Shopping -->
                <div class="d-flex justify-content-center mt-4">
                    <a href="<?= url('products/') ?>" class="cart-continue-link">
                        <i class="bi bi-arrow-left"></i> কন্টিনিউ শপিং
                    </a>
                </div>
            </div>

            <!-- Right Column: Order Summary -->
            <div class="col-12 col-lg-4">
                <div class="cart-summary-card">
                    <h3 class="cart-summary-title">অর্ডার সারসংক্ষেপ</h3>

                    <div class="cart-summary-row">
                        <span>মোট পণ্য (<?= format_number_bn($itemCount) ?>টি)</span>
                        <span class="val" data-role="cart-subtotal"><?= format_price_bn((int)round($subtotal)) ?></span>
                    </div>

                    <div class="cart-summary-row">
                        <span>ডেলিভারি চার্জ</span>
                        <span class="val" data-role="cart-shipping"><?= $shippingCost > 0 ? format_price_bn((int)round($shippingCost)) : 'ফ্রি' ?></span>
                    </div>

                    <div class="cart-summary-divider"></div>

                    <div class="cart-total-row">
                        <span class="cart-total-label">সর্বমোট</span>
                        <span class="cart-total-val" data-role="cart-total"><?= format_price_bn((int)round($total)) ?></span>
                    </div>

                    <div class="cart-free-delivery-box">
                        <div class="cart-free-delivery-left">
                            <i class="bi bi-truck"></i>
                            <span>১,০০০ টাকার বেশি অর্ডারে<br>ডেলিভারি চার্জ ফ্রি!</span>
                        </div>
                        <div class="cart-free-delivery-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="#10b981" opacity="0.75"><path d="M17 8C8 10 5.9 16.17 3.82 21.34L5.71 22l1-2.3A4.49 4.49 0 0 0 8 20C19 20 22 3 22 3c-1 2-8 2-11 5-2.23 2.23-3 5-3 5s3.5-1.5 7-1.5z"/></svg>
                        </div>
                    </div>

                    <a href="<?= url('checkout/') ?>" class="btn-checkout-ui">
                        <i class="bi bi-lock-fill me-2"></i> চেকআউট করুন <i class="bi bi-arrow-right ms-2"></i>
                    </a>

                    <a href="<?= url('cart/clear.php') ?>"
                       class="btn-clear-cart-ui text-decoration-none"
                       data-action="clear-cart"
                       data-csrf="<?= $csrf ?>"
                       onclick="if(!confirm('আপনি কি নিশ্চিত যে কার্ট খালি করতে চান?')) { return false; }">
                        <i class="bi bi-trash3 me-2"></i> কার্ট খালি করুন
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Bottom Features Strip -->
        <div class="cart-features-section mt-5 mb-3">
            <div class="row g-4 text-center text-md-start">
                <div class="col-6 col-lg-3">
                    <div class="feature-item d-flex flex-column flex-md-row align-items-center align-items-md-start gap-3">
                        <div class="feature-icon feature-icon-green">
                            <i class="bi bi-shield-fill-check"></i>
                        </div>
                        <div>
                            <h4 class="feature-title">১০০% নিরাপদ মাছ</h4>
                            <p class="feature-sub">সরাসরি খামার থেকে</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="feature-item d-flex flex-column flex-md-row align-items-center align-items-md-start gap-3">
                        <div class="feature-icon feature-icon-blue">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div>
                            <h4 class="feature-title">নিরাপত্তা ও স্বাস্থ্যকর</h4>
                            <p class="feature-sub">পরিবারের সবার জন্য</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="feature-item d-flex flex-column flex-md-row align-items-center align-items-md-start gap-3">
                        <div class="feature-icon feature-icon-mint">
                            <i class="bi bi-truck"></i>
                        </div>
                        <div>
                            <h4 class="feature-title">দ্রুত ডেলিভারি</h4>
                            <p class="feature-sub">আপনার সুবিধামত সময়ে</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="feature-item d-flex flex-column flex-md-row align-items-center align-items-md-start gap-3">
                        <div class="feature-icon feature-icon-purple">
                            <i class="bi bi-headset"></i>
                        </div>
                        <div>
                            <h4 class="feature-title">সার্বক্ষণিক সাপোর্ট</h4>
                            <p class="feature-sub">আমরা আছি আপনার পাশে</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Corner Leaves from Mockup placed at bottom corners of cart wrapper -->
    <img src="<?= asset('assets/images/general/leaf_bottom_left.png') ?>" alt="" class="cart-leaf-bl">
    <img src="<?= asset('assets/images/general/leaf_bottom_right.png') ?>" alt="" class="cart-leaf-br">
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
