<?php
// ============================================================
// FarmersBD — Admin Logout Script
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_role']);

session_destroy();

header('Location: ' . BASE_URL . '/admin/login.php');
exit;
