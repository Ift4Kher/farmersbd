<?php
// ============================================================
// FarmersBD — CSRF Protection
// ============================================================

/**
 * Generate a CSRF token and store it in the session.
 */
function csrf_generate(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Validate the submitted CSRF token.
 */
function csrf_validate(): bool {
    $token = $_POST[CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_GET['csrf_token'] ?? '';
    if (empty($token) || empty($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Output a hidden CSRF input field.
 */
function csrf_field(): string {
    return '<input type="hidden" name="' . e(CSRF_TOKEN_NAME) . '" value="' . e(csrf_generate()) . '">';
}

/**
 * Require a valid CSRF token or abort with an error.
 */
function csrf_verify(): void {
    if (!csrf_validate()) {
        unset($_SESSION[CSRF_TOKEN_NAME]);
        flash('অনুরোধটি যাচাই করা যায়নি। অনুগ্রহ করে আবার চেষ্টা করুন।', FLASH_ERROR);
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
        exit;
    }
    unset($_SESSION[CSRF_TOKEN_NAME]);
}

if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token(): void {
        csrf_verify();
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf(): void {
        csrf_verify();
    }
}

if (!function_exists('csrf_verify_token')) {
    function csrf_verify_token(?string $token): bool {
        if (empty($token) || empty($_SESSION[CSRF_TOKEN_NAME])) {
            return false;
        }
        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
}
