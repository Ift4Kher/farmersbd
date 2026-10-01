<?php
// ============================================================
// FarmersBD — Admin User Status Toggle
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/csrf.php';
require_once $base . '/includes/flash.php';

require_admin();

if (!is_post_request()) {
    redirect(BASE_URL . '/admin/users/index.php');
}

verify_csrf_token();

$user_id = (int)($_POST['user_id'] ?? 0);
$status  = (int)($_POST['status'] ?? 0); // 1 = active, 0 = inactive

// Cannot deactivate super admin (assuming ID 1 is a super admin, although admins table is separate)
// Wait, this is the users table (customers). Safe to deactivate.

$pdo = get_db_connection();
$stmt = $pdo->prepare("UPDATE users SET is_active = ?, updated_at = NOW() WHERE id = ?");
$stmt->execute([$status, $user_id]);

set_flash('success', 'অ্যাকাউন্ট স্ট্যাটাস আপডেট করা হয়েছে।');
redirect(BASE_URL . '/admin/users/view.php?id=' . $user_id);
