<?php
// ============================================================
// FarmersBD — Admin Order Status Update Action
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
    redirect(BASE_URL . '/admin/orders/index.php');
}

verify_csrf_token();

$order_id       = (int)($_POST['order_id'] ?? 0);
$order_status   = sanitize_input($_POST['order_status'] ?? 'pending');
$payment_status = sanitize_input($_POST['payment_status'] ?? 'pending');

$pdo = get_db_connection();

$stmt = $pdo->prepare("UPDATE orders SET order_status = ?, payment_status = ?, updated_at = NOW() WHERE id = ?");
$stmt->execute([$order_status, $payment_status, $order_id]);

set_flash('success', 'অর্ডার স্ট্যাটাস সফলভাবে আপডেট করা হয়েছে।');
redirect(BASE_URL . '/admin/orders/view.php?id=' . $order_id);
