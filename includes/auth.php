<?php
// ============================================================
// FarmersBD — Authentication Middleware
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/flash.php';

/**
 * Require the user to be logged in.
 * Redirects to login page if not authenticated.
 */
function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        flash('এই পৃষ্ঠা দেখতে প্রথমে লগইন করুন।', FLASH_WARNING);
        redirect(BASE_URL . '/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

/**
 * Check if user is logged in.
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Get the currently logged-in user.
 */
function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    static $user = null;
    if ($user === null) {
        $user = db_query_one(
            "SELECT id, name, mobile, email, address, district, avatar, is_active FROM users WHERE id = ?",
            [$_SESSION['user_id']]
        );
        // Check if account is deactivated
        if ($user && !$user['is_active']) {
            session_destroy();
            redirect(BASE_URL . '/auth/login.php');
        }
    }
    return $user ?: null;
}

/**
 * Log in a user by setting session variables.
 */
function login_user(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = 'user';

    // Merge guest cart to user cart if it exists
    if (!empty($_SESSION['cart_session_id'])) {
        merge_guest_cart((int)$user['id'], $_SESSION['cart_session_id']);
        unset($_SESSION['cart_session_id']);
    }
}

/**
 * Merge a guest cart into the authenticated user's cart.
 */
function merge_guest_cart(int $userId, string $sessionId): void {
    $guestCart = db_query_one("SELECT id FROM carts WHERE session_id = ?", [$sessionId]);
    if (!$guestCart) return;

    $guestCartId = $guestCart['id'];

    // Get or create user cart
    $userCart = db_query_one("SELECT id FROM carts WHERE user_id = ?", [$userId]);
    if (!$userCart) {
        db_execute("UPDATE carts SET user_id = ?, session_id = NULL WHERE id = ?", [$userId, $guestCartId]);
        return;
    }
    $userCartId = $userCart['id'];

    // Merge items: if product already in user cart, add quantities
    $guestItems = db_query("SELECT product_id, quantity FROM cart_items WHERE cart_id = ?", [$guestCartId]);
    foreach ($guestItems as $item) {
        $existing = db_query_one(
            "SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?",
            [$userCartId, $item['product_id']]
        );
        if ($existing) {
            db_execute(
                "UPDATE cart_items SET quantity = quantity + ? WHERE id = ?",
                [$item['quantity'], $existing['id']]
            );
        } else {
            db_execute(
                "INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)",
                [$userCartId, $item['product_id'], $item['quantity']]
            );
        }
    }

    // Delete guest cart
    db_execute("DELETE FROM carts WHERE id = ?", [$guestCartId]);
}

/**
 * Log out the current user.
 */
function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}
