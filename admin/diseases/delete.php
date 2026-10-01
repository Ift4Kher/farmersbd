<?php
// ============================================================
// FarmersBD — Admin Delete Disease
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

$stmt = $pdo->prepare("SELECT image FROM diseases WHERE id = ?");
$stmt->execute([$id]);
$disease = $stmt->fetch();

if ($disease) {
    if (!empty($disease['image'])) {
        $uploader = new UploadService();
        $uploader->delete_image($disease['image'], 'diseases');
    }
    $del = $pdo->prepare("DELETE FROM diseases WHERE id = ?");
    $del->execute([$id]);
    set_flash('success', 'রোগের তথ্য সফলভাবে মুছে ফেলা হয়েছে।');
}

redirect(BASE_URL . '/admin/diseases/index.php');
