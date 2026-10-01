<?php
// ============================================================
// FarmersBD — SSLCOMMERZ IPN (Instant Payment Notification)
// Called server-to-server by SSLCOMMERZ
// Must return 200 OK immediately; no HTML output
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/services/PaymentService.php';

// Log IPN
error_log('[IPN] Received: ' . json_encode($_POST));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    exit('Invalid request.');
}

$tranId = $_POST['tran_id'] ?? '';
$valId  = $_POST['val_id']  ?? '';
$status = $_POST['status']  ?? '';

if (empty($tranId)) {
    http_response_code(400);
    exit('Missing tran_id.');
}

$order = db_query_one("SELECT * FROM orders WHERE order_number = ?", [$tranId]);

if (!$order) {
    http_response_code(200); // Return 200 even if not found to prevent retries
    exit('Order not found.');
}

if ($order['payment_status'] === 'paid') {
    http_response_code(200);
    exit('Already processed.');
}

$payService = new PaymentService();

if ($status === 'VALID' || $status === 'VALIDATED') {
    $validation = $payService->validate($valId, (float)$order['total']);
    if ($validation['valid']) {
        $payService->complete_payment(
            (int)$order['id'],
            $tranId,
            $valId,
            (float)$order['total'],
            $validation['data']
        );
        error_log("[IPN] Order {$tranId} marked as paid.");
    } else {
        error_log("[IPN] Validation failed for {$tranId}");
    }
} elseif (in_array($status, ['FAILED', 'CANCELLED', 'EXPIRED'], true)) {
    db_execute(
        "UPDATE orders SET payment_status = ?, order_status = 'cancelled' WHERE order_number = ?",
        [strtolower($status), $tranId]
    );
    error_log("[IPN] Order {$tranId} status: {$status}");
}

http_response_code(200);
exit('OK');
