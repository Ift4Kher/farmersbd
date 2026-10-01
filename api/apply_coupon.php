<?php
// ============================================================
// FarmersBD — API: Apply Coupon
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/services/CouponService.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$code = $data['code'] ?? '';

if (empty($code)) {
    echo json_encode(['success' => false, 'error' => 'কুপন কোড দিন।']);
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
$cartId = get_or_create_cart($userId);

// Calculate subtotal
$cartItems = db_query(
    "SELECT ci.id AS cart_item_id, ci.quantity,
            p.id AS product_id, p.name, p.price, p.discount_price, p.stock, p.is_active
     FROM cart_items ci
     JOIN products p ON p.id = ci.product_id
     WHERE ci.cart_id = ?",
    [$cartId]
);

if (empty($cartItems)) {
    echo json_encode(['success' => false, 'error' => 'আপনার কার্ট খালি।']);
    exit;
}

$subtotal = 0.00;
foreach ($cartItems as $item) {
    if (!$item['is_active']) continue;
    $unitPrice = effective_price($item);
    $subtotal += $unitPrice * $item['quantity'];
}

// User phone might not be known here for guest, but we do our best.
// If guest entered phone in checkout, we'd need them to submit it, but API gets just code.
// For now, API validates without phone for guests (backend create_order validates strictly).

$couponService = new CouponService();
$result = $couponService->validateCoupon($code, $subtotal, $userId, null);

if ($result['success']) {
    echo json_encode([
        'success' => true,
        'discount' => $result['discount'],
        'subtotal' => $subtotal,
        'message' => 'কুপনটি সফলভাবে প্রয়োগ করা হয়েছে।'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => $result['error']
    ]);
}
