<?php
// ============================================================
// FarmersBD — Admin Delete Blog
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

$stmt = $pdo->prepare("SELECT image FROM blogs WHERE id = ?");
$stmt->execute([$id]);
$blog = $stmt->fetch();

if ($blog) {
    if (!empty($blog['image'])) {
        $uploader = new UploadService();
        $uploader->delete_image($blog['image'], 'blog');
    }
    $del = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
    $del->execute([$id]);
    set_flash('success', 'ব্লগ নিবন্ধ সফলভাবে মুছে ফেলা হয়েছে।');
}

redirect(BASE_URL . '/admin/blogs/index.php');
