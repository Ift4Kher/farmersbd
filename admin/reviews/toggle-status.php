<?php
// ============================================================
// FarmersBD — Toggle Review Status
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
    $stmt = $pdo->prepare("UPDATE reviews SET is_approved = NOT is_approved WHERE id = ?");
    $stmt->execute([$id]);
    
    flash('রিভিউ স্ট্যাটাস পরিবর্তন করা হয়েছে।', FLASH_SUCCESS);
}

redirect(BASE_URL . '/admin/reviews/');
