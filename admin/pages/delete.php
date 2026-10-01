<?php
// ============================================================
// FarmersBD — Admin Static CMS Page Delete
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/csrf.php';
require_once $base . '/includes/flash.php';

require_admin();
$pdo = get_db_connection();

$id = (int)($_GET['id'] ?? 0);
$csrf_token = $_GET['csrf_token'] ?? '';

if (!csrf_verify_token($csrf_token)) {
    set_flash('error', 'নিরাপত্তা টোকেন সঠিক নয়।');
    redirect(BASE_URL . '/admin/pages/');
}

if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
    $stmt->execute([$id]);

    set_flash('success', 'পেজ মুছে ফেলা হয়েছে।');
}

redirect(BASE_URL . '/admin/pages/');
