<?php
// ============================================================
// FarmersBD — Cart AJAX: Add to Cart
// POST /cart/add.php — JSON response
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/auth.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'অবৈধ অনুরোধ।']);
    exit;
}

// CSRF
if (!csrf_validate()) {
    echo json_encode(['success' => false, 'message' => 'অনুরোধটি যাচাই করা যায়নি।']);
    exit;
}

$productId = (int) ($_POST['product_id'] ?? 0);
$qty       = max(1, min(999, (int) ($_POST['quantity'] ?? 1)));

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'অবৈধ পণ্য।']);
    exit;
}

// Load product from DB — never trust client price
$product = db_query_one(
    "SELECT id, name, stock, is_active FROM products WHERE id = ?",
    [$productId]
);

if (!$product || !$product['is_active']) {
    echo json_encode(['success' => false, 'message' => 'পণ্যটি পাওয়া যায়নি।']);
    exit;
}

if ($product['stock'] < 1) {
    echo json_encode(['success' => false, 'message' => 'পণ্যটি স্টকে নেই।']);
    exit;
}

if ($product['stock'] < $qty) {
    echo json_encode(['success' => false, 'message' => "শুধুমাত্র {$product['stock']} টি স্টকে আছে।"]);
    exit;
}

// Get or create cart
$userId    = $_SESSION['user_id'] ?? null;
$cartId    = get_or_create_cart($userId);

// Check if item already in cart
$existing = db_query_one(
    "SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?",
    [$cartId, $productId]
);

if ($existing) {
    $newQty = $existing['quantity'] + $qty;
    if ($newQty > $product['stock']) $newQty = $product['stock'];
    db_execute(
        "UPDATE cart_items SET quantity = ? WHERE id = ?",
        [$newQty, $existing['id']]
    );
} else {
    db_insert(
        "INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)",
        [$cartId, $productId, $qty]
    );
}

$cartCount = get_cart_count($cartId);

echo json_encode([
    'success'    => true,
    'message'    => 'কার্টে যোগ করা হয়েছে।',
    'cart_count' => $cartCount,
]);
