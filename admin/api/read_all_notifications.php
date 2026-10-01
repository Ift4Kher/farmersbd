<?php
// ============================================================
// FarmersBD — Mark all notifications as read API
// ============================================================
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/database.php';
require_once dirname(dirname(__DIR__)) . '/includes/functions.php';
require_once dirname(dirname(__DIR__)) . '/includes/admin-auth.php';

require_admin();

try {
    $pdo = get_db_connection();
    $pdo->exec("UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0");
} catch (Exception $e) {}

// If AJAX/Fetch requested
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || isset($_GET['format']) && $_GET['format'] === 'json') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'সব নোটিফিকেশন পড়া হয়েছে।']);
    exit;
}

$ref = $_SERVER['HTTP_REFERER'] ?? '';
if (!empty($ref) && !str_contains($ref, 'read_all_notifications.php')) {
    redirect($ref);
} else {
    redirect(BASE_URL . '/admin/notifications/index.php');
}
