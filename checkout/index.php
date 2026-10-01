<?php
// ============================================================
// FarmersBD — Checkout Page (COD + SSLCOMMERZ)
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/flash.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/validation.php';
require_once dirname(__DIR__) . '/includes/seo.php';
require_once dirname(__DIR__) . '/services/OrderService.php';
require_once dirname(__DIR__) . '/services/PaymentService.php';

$userId = $_SESSION['user_id'] ?? null;
$cartId = get_or_create_cart($userId);

$cartItems = db_query(
    "SELECT ci.id AS item_id, ci.quantity,
            p.id AS product_id, p.name, p.image, p.price, p.discount_price, p.stock, p.is_active
     FROM cart_items ci
     JOIN products p ON p.id = ci.product_id
     WHERE ci.cart_id = ?",
    [$cartId]
);

if (empty($cartItems)) {
    flash('অর্ডার করার আগে কার্টে পণ্য যোগ করুন।', FLASH_WARNING);
    redirect(BASE_URL . '/products/');
}

$subtotal     = array_reduce($cartItems, fn($c, $i) => $c + effective_price($i) * $i['quantity'], 0.0);
$shippingCost = (float) setting('shipping_cost', 60);
$total        = $subtotal + $shippingCost;

$user  = $userId ? current_user() : null;
$errors = [];

// ── POST: Place order ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $customerData = [
        'name'    => trim($_POST['name']    ?? ''),
        'mobile'  => trim($_POST['mobile']  ?? ''),
        'email'   => trim($_POST['email']   ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'district'=> trim($_POST['district']?? ''),
        'upazila' => trim($_POST['upazila'] ?? ''),
        'notes'   => trim($_POST['notes']   ?? ''),
    ];
    $paymentMethod = $_POST['payment_method'] ?? 'cod';
    $couponCode    = trim($_POST['coupon_code'] ?? '');

    $v = new Validator($customerData);
    $v->required('name',     'নাম')
      ->max_length('name', 150, 'নাম')
      ->required('mobile',   'মোবাইল নম্বর')
      ->mobile('mobile')
      ->email('email')
      ->required('address',  'ঠিকানা')
      ->required('district', 'জেলা')
      ->in('payment_method', ['cod', 'sslcommerz'], 'পেমেন্ট পদ্ধতি');

    if ($v->fails()) {
        $errors = $v->errors();
        $v->store_in_session();
    } else {
        $orderService = new OrderService();
        $result       = $orderService->create_order($cartId, $customerData, $paymentMethod, $userId, $couponCode);

        if (!$result['success']) {
            flash($result['error'], FLASH_ERROR);
        } elseif ($paymentMethod === 'sslcommerz') {
            // Redirect to SSLCOMMERZ gateway
            $payService = new PaymentService();
            $order      = db_query_one("SELECT * FROM orders WHERE id = ?", [$result['order_id']]);
            $gw         = $payService->initialize($order, $customerData);

            if ($gw['success']) {
                redirect($gw['redirect_url']);
            } else {
                flash('পেমেন্ট গেটওয়ে শুরু করা যায়নি। অনুগ্রহ করে ক্যাশ অন ডেলিভারি ব্যবহার করুন।', FLASH_ERROR);
                redirect(BASE_URL . '/checkout/');
            }
        } else {
            // COD success
            flash('অর্ডার সফলভাবে প্রদান করা হয়েছে! অর্ডার নং: ' . $result['order_number'], FLASH_SUCCESS);
            redirect(BASE_URL . '/checkout/success.php?order=' . urlencode($result['order_number']));
        }
    }
}

$page_seo = ['title' => 'চেকআউট | ' . setting('site_name', 'FarmersBD')];
$districts = BANGLADESH_DISTRICTS;

include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/navbar.php';
?>

<link rel="stylesheet" href="<?= asset('assets/css/checkout.css') ?>?v=<?= time() ?>">

<!-- Checkout Header Section -->
<div class="co-header">
    <div class="co-header-decor"></div>
    <div class="container position-relative">
        <div class="co-header-slogan">নিরাপদ মাছ,<br>সুস্থ আগামী</div>
        
        <div class="co-breadcrumb">
            <a href="<?= url() ?>">হোম</a> <span>&gt;</span>
            <a href="<?= url('cart/') ?>">কার্ট</a> <span>&gt;</span>
            <span>চেকআউট</span>
        </div>
        
        <div class="co-header-title">
            <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="#0f172a" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            চেকআউট
        </div>
        <div class="co-header-subtitle">
            আপনার অর্ডার সম্পন্ন করতে নিচের তথ্যগুলো সঠিকভাবে পূরণ করুন।
        </div>
    </div>
</div>

<div class="container co-main-wrapper pb-5">
    <form method="POST" action="" novalidate id="checkoutForm">
        <?= csrf_field() ?>
        <input type="hidden" name="coupon_code" id="couponCodeInput" value="">
        
        <div class="row g-4">
            <!-- Left Column: Forms -->
            <div class="col-lg-7">
                <div class="co-form-card">
                    
                    <!-- Step 1: Customer Info -->
                    <div class="co-step1">
                        <div class="co-step-header">
                            <div class="co-step-num">1</div>
                            <div>
                                <h3 class="co-step-title">গ্রাহকের তথ্য</h3>
                                <div class="co-step-subtitle">আপনার ব্যক্তিগত এবং যোগাযোগের তথ্য দিন।</div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="co-form-group">
                                    <label>পূর্ণ নাম <span class="text-danger">*</span></label>
                                    <div class="co-input-wrapper">
                                        <div class="co-input-icon">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        </div>
                                        <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                               name="name" value="<?= old('name', $user['name'] ?? '') ?>"
                                               placeholder="আপনার পূর্ণ নাম লিখুন" required>
                                    </div>
                                    <?php if (isset($errors['name'])): ?><div class="invalid-feedback d-block mt-1"><?= e($errors['name']) ?></div><?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="co-form-group">
                                    <label>মোবাইল নম্বর <span class="text-danger">*</span></label>
                                    <div class="co-input-wrapper">
                                        <div class="co-input-icon">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                        </div>
                                        <input type="tel" class="form-control <?= isset($errors['mobile']) ? 'is-invalid' : '' ?>"
                                               name="mobile" value="<?= old('mobile', $user['mobile'] ?? '') ?>"
                                               placeholder="01XXXXXXXXX" required>
                                    </div>
                                    <?php if (isset($errors['mobile'])): ?><div class="invalid-feedback d-block mt-1"><?= e($errors['mobile']) ?></div><?php endif; ?>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="co-form-group">
                                    <label>ইমেইল (ঐচ্ছিক)</label>
                                    <div class="co-input-wrapper">
                                        <div class="co-input-icon">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                        </div>
                                        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                               name="email" value="<?= old('email', $user['email'] ?? '') ?>"
                                               placeholder="email@example.com">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="co-form-group">
                                    <label>সম্পূর্ণ ঠিকানা <span class="text-danger">*</span></label>
                                    <div class="co-input-wrapper">
                                        <div class="co-input-icon">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                        </div>
                                        <textarea class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>"
                                                  name="address" rows="2"
                                                  placeholder="বাড়ি নং, রাস্তা, এলাকা, শহর, জেলা" required><?= old('address', $user['address'] ?? '') ?></textarea>
                                    </div>
                                    <?php if (isset($errors['address'])): ?><div class="invalid-feedback d-block mt-1"><?= e($errors['address']) ?></div><?php endif; ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="co-form-group">
                                    <label>জেলা <span class="text-danger">*</span></label>
                                    <div class="co-input-wrapper">
                                        <div class="co-input-icon">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                        </div>
                                        <select class="form-select <?= isset($errors['district']) ? 'is-invalid' : '' ?>"
                                                name="district" required>
                                            <option value="">— জেলা নির্বাচন করুন —</option>
                                            <?php foreach ($districts as $d): ?>
                                            <option value="<?= e($d) ?>" <?= old('district', $user['district'] ?? '') === $d ? 'selected' : '' ?>>
                                                <?= e($d) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php if (isset($errors['district'])): ?><div class="invalid-feedback d-block mt-1"><?= e($errors['district']) ?></div><?php endif; ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="co-form-group">
                                    <label>উপজেলা (ঐচ্ছিক)</label>
                                    <div class="co-input-wrapper">
                                        <div class="co-input-icon">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>
                                        </div>
                                        <input type="text" class="form-control" name="upazila"
                                               value="<?= old('upazila') ?>" placeholder="উপজেলা লিখুন">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="co-form-group mb-0">
                                    <label>বিশেষ নির্দেশনা (ঐচ্ছিক)</label>
                                    <div class="co-input-wrapper">
                                        <div class="co-input-icon">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                        </div>
                                        <textarea class="form-control" name="notes" rows="2"
                                                  placeholder="যেমন: ডেলিভারি সংক্রান্ত কোনো নির্দেশনা..."><?= old('notes') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Delivery & Payment (Expanded for now to select method) -->
                    <div class="co-step2-header" id="step2Toggle">
                        <div class="co-step-header">
                            <div class="co-step-num">2</div>
                            <div>
                                <h3 class="co-step-title">ডেলিভারি ও পেমেন্ট</h3>
                                <div class="co-step-subtitle">ডেলিভারি পদ্ধতি ও পেমেন্ট মেথড নির্বাচন করুন (পরবর্তী ধাপে)।</div>
                            </div>
                        </div>
                        <div class="co-step2-chevron">
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                    </div>

                    <div id="step2Content" style="display:none;">
                        <div class="co-payment-grid">
                            <label class="payment-card selected" id="cod-card" for="pay-cod">
                                <input type="radio" name="payment_method" id="pay-cod" value="cod" checked>
                                <div class="payment-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
                                </div>
                                <div>
                                    <div class="fw-bold" style="font-size:0.95rem;color:#0f172a;">ক্যাশ অন ডেলিভারি</div>
                                    <div style="font-size:0.8rem;color:#64748b;">ডেলিভারির সময় নগদ পরিশোধ</div>
                                </div>
                            </label>
                            
                            <label class="payment-card" id="ssl-card" for="pay-ssl">
                                <input type="radio" name="payment_method" id="pay-ssl" value="sslcommerz">
                                <div class="payment-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                </div>
                                <div>
                                    <div class="fw-bold" style="font-size:0.95rem;color:#0f172a;">অনলাইন পেমেন্ট</div>
                                    <div style="font-size:0.8rem;color:#64748b;">বিকাশ, নগদ, কার্ড ও অন্যান্য</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Order Summary -->
            <div class="col-lg-5">
                <div class="co-summary-card">
                    <div class="co-summary-header">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="#0284c7" stroke="none"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16zM12 2.73l5.5 3.14L12 9l-5.5-3.14L12 2.73zM5.5 7.6L11 10.74v6.27l-5.5-3.14V7.6zm13 6.27l-5.5 3.14v-6.27L18.5 7.6v6.27z"/></svg>
                        অর্ডার সারাংশ
                    </div>

                    <div class="co-summary-body">
                        <div class="co-item-list">
                            <?php foreach ($cartItems as $item):
                                $unitPrc = effective_price($item); ?>
                            <div class="co-item">
                                <img src="<?= uploaded_image_url('products', $item['image']) ?>" alt="<?= e($item['name']) ?>" class="co-item-img">
                                <div class="co-item-details">
                                    <div class="co-item-title"><?= e($item['name']) ?></div>
                                    <div class="co-item-qty">× <?= (int)$item['quantity'] ?></div>
                                </div>
                                <div class="co-item-price"><?= format_price($unitPrc * $item['quantity']) ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="co-summary-row">
                            <span>উপমোট</span>
                            <span style="font-weight:700;color:#0f172a;" id="summarySubtotal"><?= format_price($subtotal) ?></span>
                        </div>
                        
                        <div class="co-summary-row" id="discountRow" style="display:none; color: #16a34a;">
                            <span>ডিসকাউন্ট (কুপন)</span>
                            <span style="font-weight:700;" id="summaryDiscount">- <?= format_price(0) ?></span>
                        </div>
                        
                        <div class="co-summary-row">
                            <span>ডেলিভারি চার্জ</span>
                            <span style="font-weight:700;color:#0f172a;" id="summaryShipping"><?= format_price($shippingCost) ?></span>
                        </div>

                        <div class="mb-3 mt-3">
                            <div class="input-group">
                                <input type="text" id="couponCode" class="form-control" placeholder="কুপন কোড (যদি থাকে)">
                                <button type="button" class="btn btn-outline-primary" id="btnApplyCoupon">এপ্লাই</button>
                            </div>
                            <div id="couponMsg" class="form-text mt-1"></div>
                        </div>

                        <div class="co-summary-total">
                            <span>মোট পরিমাণ</span>
                            <span id="summaryTotal"><?= format_price($total) ?></span>
                        </div>

                        <button type="submit" class="btn-co-confirm">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            অর্ডার নিশ্চিত করুন <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </button>

                        <div class="co-divider">
                            <span>অথবা</span>
                        </div>

                        <a href="<?= url('cart/') ?>" class="btn-co-return">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                            কার্টে ফিরে যান
                        </a>

                        <div class="co-security-badge">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                            আপনার তথ্য সম্পূর্ণ নিরাপদ ও সুরক্ষিত।
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Payment method card toggle
document.querySelectorAll('[name="payment_method"]').forEach(r => {
    r.addEventListener('change', () => {
        document.querySelectorAll('.payment-card').forEach(c => c.classList.remove('selected'));
        r.closest('.payment-card')?.classList.add('selected');
    });
});

// Optional: Toggle Step 2 content visibility
const step2Toggle = document.getElementById('step2Toggle');
const step2Content = document.getElementById('step2Content');
const step2Chevron = step2Toggle.querySelector('.co-step2-chevron svg');

step2Toggle.addEventListener('click', () => {
    if (step2Content.style.display === 'none') {
        step2Content.style.display = 'block';
        step2Chevron.style.transform = 'rotate(0deg)';
    } else {
        step2Content.style.display = 'none';
        step2Chevron.style.transform = 'rotate(-180deg)';
    }
});

// Auto-save checkout progress for Incomplete Orders
document.querySelectorAll('#checkoutForm input, #checkoutForm textarea, #checkoutForm select').forEach(el => {
    el.addEventListener('blur', () => {
        const formData = new FormData(document.getElementById('checkoutForm'));
        const data = Object.fromEntries(formData.entries());
        fetch('<?= url("api/cart_update_checkout.php") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        }).catch(err => console.error('Auto-save failed:', err));
    });
});

// Coupon Application Logic
const subtotalBase = <?= $subtotal ?>;
const shippingCostBase = <?= $shippingCost ?>;
let discountApplied = 0.00;

document.getElementById('btnApplyCoupon').addEventListener('click', async function() {
    const code = document.getElementById('couponCode').value.trim();
    const msgEl = document.getElementById('couponMsg');
    
    if (!code) {
        msgEl.innerHTML = '<span class="text-danger">কুপন কোড লিখুন।</span>';
        return;
    }
    
    msgEl.innerHTML = '<span class="text-info">যাচাই করা হচ্ছে...</span>';
    
    try {
        const response = await fetch('<?= url("api/apply_coupon.php") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code: code })
        });
        
        const data = await response.json();
        if (data.success) {
            msgEl.innerHTML = `<span class="text-success">${data.message}</span>`;
            document.getElementById('couponCodeInput').value = code;
            discountApplied = parseFloat(data.discount);
            
            document.getElementById('discountRow').style.display = 'flex';
            // Wait, format_price formatting needs to be simulated or we can just fetch formatted strings from backend. 
            // Better to format in JS simply.
            document.getElementById('summaryDiscount').innerText = '- ৳ ' + formatNumberBn(discountApplied.toFixed(2));
            
            const newTotal = (subtotalBase - discountApplied) + shippingCostBase;
            document.getElementById('summaryTotal').innerText = '৳ ' + formatNumberBn(newTotal.toFixed(2));
            
        } else {
            msgEl.innerHTML = `<span class="text-danger">${data.error}</span>`;
            document.getElementById('couponCodeInput').value = '';
            discountApplied = 0;
            document.getElementById('discountRow').style.display = 'none';
            const newTotal = subtotalBase + shippingCostBase;
            document.getElementById('summaryTotal').innerText = '৳ ' + formatNumberBn(newTotal.toFixed(2));
        }
    } catch (err) {
        msgEl.innerHTML = '<span class="text-danger">সার্ভার ত্রুটি। আবার চেষ্টা করুন।</span>';
    }
});

function formatNumberBn(numberStr) {
    const en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    const bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return numberStr.replace(/[0-9]/g, match => bn[en.indexOf(match)]);
}
</script>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
