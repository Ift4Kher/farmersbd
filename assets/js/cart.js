/**
 * FarmersBD — Cart JavaScript
 * Handles AJAX add-to-cart, quantity controls, cart badge update.
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initAddToCart();
    initCartQuantityControls();
});

// ── Add to Cart (AJAX) ────────────────────────────────────────
function initAddToCart() {
    document.querySelectorAll('[data-action="add-to-cart"]').forEach(btn => {
        btn.addEventListener('click', async e => {
            e.preventDefault();

            const productId = btn.dataset.productId;
            const csrf      = btn.dataset.csrf || document.querySelector('meta[name="csrf"]')?.content || '';
            const qtyInput  = document.querySelector(`[data-qty-for="${productId}"]`);
            const qty       = qtyInput ? parseInt(qtyInput.value) || 1 : 1;

            if (!productId) return;

            const original = btn.innerHTML;
            btn.disabled   = true;
            btn.innerHTML  = '<span class="spinner-border spinner-border-sm me-1"></span>যোগ হচ্ছে…';

            try {
                const res = await fetch(FARMERSBD.urls.cartAdd, {
                    method:  'POST',
                    headers: {
                        'Content-Type':    'application/x-www-form-urlencoded',
                        'X-Requested-With':'XMLHttpRequest',
                    },
                    body: `product_id=${encodeURIComponent(productId)}&quantity=${qty}&_csrf_token=${encodeURIComponent(csrf)}`,
                });

                const data = await res.json();

                if (data.success) {
                    updateCartBadge(data.cart_count);
                    showToast(data.message || 'কার্টে যোগ হয়েছে!', 'success');
                    // Animate button briefly
                    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>যোগ হয়েছে';
                    setTimeout(() => { btn.innerHTML = original; btn.disabled = false; }, 1800);
                } else {
                    showToast(data.message || 'ত্রুটি ঘটেছে।', 'danger');
                    btn.innerHTML = original;
                    btn.disabled  = false;
                }
            } catch {
                showToast('নেটওয়ার্ক ত্রুটি। পুনরায় চেষ্টা করুন।', 'danger');
                btn.innerHTML = original;
                btn.disabled  = false;
            }
        });
    });
}

// ── Update cart badge count ───────────────────────────────────
function updateCartBadge(count) {
    document.querySelectorAll('[id^="cart-count-badge"]').forEach(el => {
        el.textContent = count;
        el.classList.toggle('d-none', count <= 0);
    });
}

// ── Cart page quantity controls ───────────────────────────────
// ── Cart page quantity controls ───────────────────────────────
function initCartQuantityControls() {
    // Increment
    document.querySelectorAll('[data-action="qty-increase"]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const stepper = btn.closest('.cart-stepper') || btn.closest('.qty-control');
            const input = stepper?.querySelector('input[type="number"]');
            if (!input) return;
            const max = parseInt(input.max) || 999;
            const val = parseInt(input.value) || 1;
            if (val < max) { input.value = val + 1; triggerCartUpdate(input); }
        });
    });

    // Decrement
    document.querySelectorAll('[data-action="qty-decrease"]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const stepper = btn.closest('.cart-stepper') || btn.closest('.qty-control');
            const input = stepper?.querySelector('input[type="number"]');
            if (!input) return;
            const min = parseInt(input.min) || 1;
            const val = parseInt(input.value) || 1;
            if (val > min) { input.value = val - 1; triggerCartUpdate(input); }
        });
    });

    // Direct input
    document.querySelectorAll('.cart-qty-input').forEach(input => {
        input.addEventListener('change', () => {
            const val = parseInt(input.value);
            const min = parseInt(input.min) || 1;
            const max = parseInt(input.max) || 999;
            input.value = Math.min(Math.max(val, min), max);
            triggerCartUpdate(input);
        });
    });
}

// ── Trigger cart update (debounced) ──────────────────────────
let cartUpdateTimer = null;
async function triggerCartUpdate(input) {
    clearTimeout(cartUpdateTimer);
    cartUpdateTimer = setTimeout(async () => {
        const itemId  = input.dataset.itemId;
        const qty     = input.value;
        const csrf    = input.dataset.csrf || document.querySelector('meta[name="csrf"]')?.content || '';
        const row     = input.closest('tr') || input.closest('.cart-row');

        if (!itemId) return;

        try {
            const updateUrl = window.FARMERSBD?.urls?.cartUpdate || './update.php';
            const res = await fetch(updateUrl, {
                method:  'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body:    `item_id=${itemId}&quantity=${qty}&_csrf_token=${encodeURIComponent(csrf)}`,
            });
            const data = await res.json();
            if (data.success) {
                // Update row subtotal
                const subtotalEl = row?.querySelector('[data-role="item-subtotal"]');
                if (subtotalEl && data.item_subtotal !== undefined) subtotalEl.textContent = data.item_subtotal;
                // Update cart totals
                const totalEl = document.querySelector('[data-role="cart-total"]');
                if (totalEl && data.total !== undefined) totalEl.textContent = data.total;
                const subtotalAllEl = document.querySelector('[data-role="cart-subtotal"]');
                if (subtotalAllEl && data.subtotal !== undefined) subtotalAllEl.textContent = data.subtotal;
                const shippingEl = document.querySelector('[data-role="cart-shipping"]');
                if (shippingEl && data.shipping !== undefined) shippingEl.textContent = data.shipping;
                updateCartBadge(data.cart_count);
            } else {
                showToast(data.message || 'আপডেট ব্যর্থ।', 'danger');
                location.reload();
            }
        } catch {
            showToast('নেটওয়ার্ক ত্রুটি।', 'danger');
        }
    }, 400);
}

// ── Helper to resolve endpoint URLs safely ───────────────────
function getCartEndpoint(key, fallbackPath) {
    let urlStr = window.FARMERSBD?.urls?.[key] || fallbackPath;
    if (urlStr && (urlStr.startsWith('http://') || urlStr.startsWith('https://'))) {
        try {
            const parsed = new URL(urlStr);
            return window.location.origin + parsed.pathname;
        } catch (e) {
            return fallbackPath;
        }
    }
    return urlStr || fallbackPath;
}

// ── Remove cart item (Event Delegation & Samsung Touch Double-Click Protection) ─────────
document.addEventListener('click', async e => {
    const btn = e.target.closest('[data-action="remove-cart-item"]');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    if (btn.dataset.submitting === 'true') return;

    if (!confirm('এই পণ্যটি কার্ট থেকে সরাবেন?')) return;

    btn.dataset.submitting = 'true';
    btn.style.pointerEvents = 'none';
    btn.style.opacity = '0.4';

    const itemId = btn.dataset.itemId;
    const csrf   = btn.dataset.csrf || document.querySelector('meta[name="csrf"]')?.content || '';
    const row    = btn.closest('tr') || btn.closest('.cart-row');

    try {
        const removeUrl = getCartEndpoint('cartRemove', './remove.php');
        const res = await fetch(removeUrl, {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body:    `item_id=${encodeURIComponent(itemId)}&_csrf_token=${encodeURIComponent(csrf)}`,
        });
        const data = await res.json();
        if (data.success) {
            row?.remove();
            updateCartBadge(data.cart_count);
            const totalEl = document.querySelector('[data-role="cart-total"]');
            if (totalEl && data.total !== undefined) totalEl.textContent = data.total;
            const subtotalAllEl = document.querySelector('[data-role="cart-subtotal"]');
            if (subtotalAllEl && data.subtotal !== undefined) subtotalAllEl.textContent = data.subtotal;
            const shippingEl = document.querySelector('[data-role="cart-shipping"]');
            if (shippingEl && data.shipping !== undefined) shippingEl.textContent = data.shipping;
            if (data.cart_count === 0) location.reload();
            showToast('পণ্যটি সরানো হয়েছে।', 'success');
        } else {
            showToast(data.message || 'ত্রুটি ঘটেছে।', 'danger');
            btn.dataset.submitting = 'false';
            btn.style.pointerEvents = '';
            btn.style.opacity = '';
        }
    } catch (err) {
        console.error('Remove cart error:', err);
        showToast('নেটওয়ার্ক ত্রুটি। পুনরায় চেষ্টা করুন।', 'danger');
        btn.dataset.submitting = 'false';
        btn.style.pointerEvents = '';
        btn.style.opacity = '';
    }
});

// ── Clear cart ────────────────────────────────────────────────
document.addEventListener('click', async e => {
    const btn = e.target.closest('[data-action="clear-cart"]');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    if (btn.dataset.submitting === 'true') return;

    if (!confirm('আপনি কি নিশ্চিত যে কার্ট খালি করতে চান?')) return;

    btn.dataset.submitting = 'true';
    btn.style.pointerEvents = 'none';

    const csrf = btn.dataset.csrf || document.querySelector('meta[name="csrf"]')?.content || '';

    try {
        const clearUrl = getCartEndpoint('cartClear', './clear.php');
        const res = await fetch(clearUrl, {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body:    `_csrf_token=${encodeURIComponent(csrf)}`,
        });
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            showToast(data.message || 'ত্রুটি ঘটেছে।', 'danger');
            btn.dataset.submitting = 'false';
            btn.style.pointerEvents = '';
        }
    } catch (err) {
        console.error('Clear cart error:', err);
        showToast('নেটওয়ার্ক ত্রুটি।', 'danger');
        btn.dataset.submitting = 'false';
        btn.style.pointerEvents = '';
    }
});

