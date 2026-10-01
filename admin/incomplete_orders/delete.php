<?php
// ============================================================
// FarmersBD — Admin Incomplete Orders Delete
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/csrf.php';
require_once $base . '/includes/flash.php';

require_admin();
csrf_verify();

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    set_flash('error', 'অবৈধ অনুরোধ।');
    redirect(BASE_URL . '/admin/incomplete_orders/index.php');
}

$pdo = get_db_connection();

// Ensure it exists
$stmt = $pdo->prepare("SELECT id FROM carts WHERE id = ?");
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    set_flash('error', 'কার্ট খুঁজে পাওয়া যায়নি।');
    redirect(BASE_URL . '/admin/incomplete_orders/index.php');
}

try {
    $pdo->beginTransaction();

    // Delete cart items first
    $stmt1 = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
    $stmt1->execute([$id]);

    // Delete the cart
    $stmt2 = $pdo->prepare("DELETE FROM carts WHERE id = ?");
    $stmt2->execute([$id]);

    $pdo->commit();
    set_flash('success', 'অসম্পূর্ণ অর্ডারটি সফলভাবে মুছে ফেলা হয়েছে।');
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Cart deletion failed: " . $e->getMessage());
    set_flash('error', 'সিস্টেম ত্রুটির কারণে কার্ট মোছা সম্ভব হয়নি।');
}

redirect(BASE_URL . '/admin/incomplete_orders/index.php');
