<?php
// ============================================================
// FarmersBD — Cart Action: Clear Cart
// Supports both AJAX POST and direct GET/POST redirection
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$userId = $_SESSION['user_id'] ?? null;
$cartId = get_or_create_cart($userId);

// Delete all items for current cart
db_execute("DELETE FROM cart_items WHERE cart_id = ?", [$cartId]);

// If AJAX request, return JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success'    => true,
        'cart_count' => 0,
        'message'    => 'কার্ট খালি করা হয়েছে।',
    ]);
    exit;
}

// Otherwise redirect back to cart
redirect(url('cart/'));
