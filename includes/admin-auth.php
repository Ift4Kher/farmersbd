<?php
// ============================================================
// FarmersBD — Admin Authentication Middleware
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/flash.php';

/**
 * Require admin authentication.
 * Redirects to admin login if not authenticated.
 */
function require_admin(): void {
    if (empty($_SESSION['admin_id'])) {
        flash('এই অ্যাক্সেসের জন্য অ্যাডমিন লগইন প্রয়োজন।', FLASH_WARNING);
        redirect(BASE_URL . '/admin/login.php');
    }
}

/**
 * Check if an admin is logged in.
 */
function is_admin_logged_in(): bool {
    return !empty($_SESSION['admin_id']);
}

/**
 * Get the currently logged-in admin.
 */
function current_admin(): ?array {
    if (empty($_SESSION['admin_id'])) return null;
    static $admin = null;
    if ($admin === null) {
        $admin = db_query_one(
            "SELECT id, name, email, is_active FROM admins WHERE id = ?",
            [$_SESSION['admin_id']]
        );
        if ($admin && !$admin['is_active']) {
            admin_logout();
            redirect(BASE_URL . '/admin/login.php');
        }
    }
    return $admin ?: null;
}

/**
 * Log in an admin.
 */
function admin_login(array $admin): void {
    session_regenerate_id(true);
    $_SESSION['admin_id']   = $admin['id'];
    $_SESSION['admin_name'] = $admin['name'];
    $_SESSION['admin_role'] = 'admin';
}

/**
 * Log out the current admin.
 */
function admin_logout(): void {
    unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_role']);
}
