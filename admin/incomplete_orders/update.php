<?php
// ============================================================
// FarmersBD — Admin Incomplete Orders Update
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
    redirect(BASE_URL . '/admin/incomplete_orders/index.php');
}

verify_csrf_token();

$cart_id = (int)($_POST['cart_id'] ?? 0);
$status  = sanitize_input($_POST['status'] ?? 'new');
$notes   = sanitize_input($_POST['notes'] ?? '');

$valid_statuses = ['new', 'contacted', 'follow_up', 'recovered', 'lost'];
if (!in_array($status, $valid_statuses)) {
    $status = 'new';
}

$pdo = get_db_connection();

$stmt = $pdo->prepare("UPDATE carts SET status = ?, notes = ?, updated_at = NOW() WHERE id = ?");
$stmt->execute([$status, $notes, $cart_id]);

set_flash('success', 'অসম্পূর্ণ অর্ডারের স্ট্যাটাস সফলভাবে আপডেট করা হয়েছে।');
redirect(BASE_URL . '/admin/incomplete_orders/view.php?id=' . $cart_id);
