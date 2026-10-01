<?php
// ============================================================
// FarmersBD — Cart AJAX: Update Quantity
// POST /cart/update.php — JSON response
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate()) {
    echo json_encode(['success' => false, 'message' => 'অবৈধ অনুরোধ।']);
    exit;
}

$itemId = (int) ($_POST['item_id']  ?? 0);
$qty    = max(1, min(999, (int) ($_POST['quantity'] ?? 1)));
$userId = $_SESSION['user_id'] ?? null;

// Verify the cart item belongs to the current cart
$cartId = get_or_create_cart($userId);
$item   = db_query_one(
    "SELECT ci.id, ci.product_id, ci.quantity,
            p.price, p.discount_price, p.stock
     FROM cart_items ci
     JOIN products p ON p.id = ci.product_id
     WHERE ci.id = ? AND ci.cart_id = ?",
    [$itemId, $cartId]
);

if (!$item) {
    echo json_encode(['success' => false, 'message' => 'আইটেম পাওয়া যায়নি।']);
    exit;
}

if ($qty > $item['stock']) {
    $qty = $item['stock'];
}

db_execute("UPDATE cart_items SET quantity = ? WHERE id = ?", [$qty, $itemId]);

$unitPrice    = effective_price($item);
$itemSubtotal = $unitPrice * $qty;

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

echo json_encode([
    'success'       => true,
    'cart_count'    => get_cart_count($cartId),
    'item_count'    => format_number_bn($itemCount),
    'item_subtotal' => format_price_bn((int)round($itemSubtotal)),
    'subtotal'      => format_price_bn((int)round($subtotal)),
    'shipping'      => $shippingCost > 0 ? format_price_bn((int)round($shippingCost)) : 'ফ্রি',
    'total'         => format_price_bn((int)round($total)),
]);
