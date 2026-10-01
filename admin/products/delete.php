<?php
// ============================================================
// FarmersBD — Admin Delete Product
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/flash.php';
require_once $base . '/services/UploadService.php';

require_admin();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pdo = get_db_connection();

$stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if ($product) {
    $del = $pdo->prepare("UPDATE products SET deleted_at = NOW() WHERE id = ?");
    $del->execute([$id]);
    set_flash('success', 'পণ্যটি সফলভাবে ট্র্যাশে পাঠানো হয়েছে।');
}

redirect(BASE_URL . '/admin/products/index.php');
