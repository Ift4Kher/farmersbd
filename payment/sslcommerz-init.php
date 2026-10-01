<?php
// ============================================================
// FarmersBD — Direct Payment Initiator
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/services/PaymentService.php';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$pdo = get_db_connection();

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found.");
}

$paymentService = new PaymentService();
$res = $paymentService->initiate_sslcommerz_payment($order);

if ($res['success']) {
    header("Location: " . $res['gateway_url']);
    exit;
} else {
    echo "Payment initialization failed: " . h($res['error']);
}
