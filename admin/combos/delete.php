<?php
// ============================================================
// FarmersBD — Admin Combos Delete
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
    redirect(BASE_URL . '/admin/combos/');
}

if ($id > 0) {
    // Delete combo items first
    $pdo->prepare("DELETE FROM combo_items WHERE combo_id = ?")->execute([$id]);
    // Delete combo
    $stmt = $pdo->prepare("DELETE FROM combos WHERE id = ?");
    $stmt->execute([$id]);

    set_flash('success', 'কম্বো প্যাকেজ মুছে ফেলা হয়েছে।');
}

redirect(BASE_URL . '/admin/combos/');
