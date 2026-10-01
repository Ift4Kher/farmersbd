<?php
// api/cart_update_checkout.php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$cartId = get_or_create_cart($_SESSION['user_id'] ?? null);

$customerData = [
    'name'    => trim($input['name'] ?? ''),
    'mobile'  => trim($input['mobile'] ?? ''),
    'email'   => trim($input['email'] ?? ''),
    'address' => trim($input['address'] ?? ''),
    'district'=> trim($input['district'] ?? ''),
];

$sql = "UPDATE carts SET 
            customer_name = ?, 
            customer_mobile = ?, 
            customer_email = ?, 
            customer_address = ?, 
            customer_district = ? 
        WHERE id = ?";

db_execute($sql, [
    $customerData['name'] !== '' ? $customerData['name'] : null,
    $customerData['mobile'] !== '' ? $customerData['mobile'] : null,
    $customerData['email'] !== '' ? $customerData['email'] : null,
    $customerData['address'] !== '' ? $customerData['address'] : null,
    $customerData['district'] !== '' ? $customerData['district'] : null,
    $cartId
]);

echo json_encode(['success' => true]);
