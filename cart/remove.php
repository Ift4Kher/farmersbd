<?php
// ============================================================
// FarmersBD — Cart AJAX: Remove Item
// POST / GET /cart/remove.php — JSON response or redirect
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/flash.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
          || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
}

$itemId = (int) ($_POST['item_id'] ?? $_GET['item_id'] ?? 0);
$userId = $_SESSION['user_id'] ?? null;
$cartId = get_or_create_cart($userId);

if ($itemId <= 0) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'অকার্যকর পণ্য।']);
        exit;
    } else {
        flash_set('danger', 'অকার্যকর পণ্য।');
        redirect(url('cart/'));
        exit;
    }
}

// Verify item exists for current cart or session
$item = db_query_one("SELECT id FROM cart_items WHERE id = ? AND cart_id = ?", [$itemId, $cartId]);
if (!$item) {
    // If not found by cart_id, check if item exists in cart_items by id (e.g. session refreshed)
    $item = db_query_one("SELECT id, cart_id FROM cart_items WHERE id = ?", [$itemId]);
    if ($item) {
        $cartId = (int) $item['cart_id'];
    }
}

if ($item) {
    db_execute("DELETE FROM cart_items WHERE id = ?", [$itemId]);
}

// Recalculate cart subtotal & total
$allItems = db_query(
    "SELECT ci.quantity, p.price, p.discount_price
     FROM cart_items ci
     JOIN products p ON p.id = ci.product_id
     WHERE ci.cart_id = ?",
    [$cartId]
);

$subtotal = array_reduce($allItems, fn($carry, $i) => $carry + effective_price($i) * $i['quantity'], 0.0);
$shippingCost = (float) setting('shipping_cost', 60);
if ($subtotal >= 1000 || $subtotal == 0) {
    $shippingCost = 0;
}
$total = $subtotal + $shippingCost;
$itemCount = array_reduce($allItems, fn($carry, $i) => $carry + $i['quantity'], 0);

if (!$isAjax) {
    flash_set('success', 'পণ্যটি কার্ট থেকে সরানো হয়েছে।');
    redirect(url('cart/'));
    exit;
}

echo json_encode([
    'success'    => true,
    'cart_count' => get_cart_count($cartId),
    'item_count' => format_number_bn($itemCount),
    'subtotal'   => format_price_bn((int)round($subtotal)),
    'shipping'   => $shippingCost > 0 ? format_price_bn((int)round($shippingCost)) : 'ফ্রি',
    'total'      => format_price_bn((int)round($total)),
]);

