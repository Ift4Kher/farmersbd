<?php
// ============================================================
// FarmersBD — Admin Consultation Reply Processor
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/csrf.php';
require_once $base . '/includes/flash.php';

require_admin();

if (!is_post_request()) {
    redirect(BASE_URL . '/admin/consultations/index.php');
}

verify_csrf_token();

$id          = (int)($_POST['consultation_id'] ?? 0);
$admin_reply = sanitize_input($_POST['admin_reply'] ?? '');
$status      = sanitize_input($_POST['status'] ?? 'replied');

$pdo = get_db_connection();

$stmt = $pdo->prepare("UPDATE consultations SET admin_reply = ?, status = ?, replied_at = NOW(), updated_at = NOW() WHERE id = ?");
$stmt->execute([$admin_reply, $status, $id]);

set_flash('success', 'বিশেষজ্ঞ পরামর্শের উত্তর সফলভাবে গ্রাহকের কাছে পাঠানো হয়েছে।');
redirect(BASE_URL . '/admin/consultations/view.php?id=' . $id);
