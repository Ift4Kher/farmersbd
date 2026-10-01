<?php
// ============================================================
// FarmersBD — Admin Delete FAQ
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/flash.php';

require_admin();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pdo = get_db_connection();

$del = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
$del->execute([$id]);
set_flash('success', 'FAQ প্রশ্ন সফলভাবে মুছে ফেলা হয়েছে।');

redirect(BASE_URL . '/admin/faq/index.php');
