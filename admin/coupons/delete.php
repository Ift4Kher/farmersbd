<?php
// ============================================================
// FarmersBD — Delete Coupon
// ============================================================
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/database.php';
require_once dirname(dirname(__DIR__)) . '/includes/functions.php';
require_once dirname(dirname(__DIR__)) . '/includes/admin-auth.php';
require_once dirname(dirname(__DIR__)) . '/includes/flash.php';
require_once dirname(dirname(__DIR__)) . '/includes/csrf.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
    $stmt->execute([$id]);
    
    flash('কুপন মুছে ফেলা হয়েছে।', FLASH_SUCCESS);
}

redirect(BASE_URL . '/admin/coupons/');
