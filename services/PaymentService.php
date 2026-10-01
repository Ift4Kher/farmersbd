<?php
// ============================================================
// FarmersBD — SSLCOMMERZ Payment Service
// ============================================================

class PaymentService {

    private array $config;

    public function __construct() {
        $this->config = require dirname(__DIR__) . '/config/sslcommerz.php';
    }

    /**
     * Get the correct API URL based on sandbox setting.
     */
    public function api_url(): string {
        return $this->config['sandbox']
            ? $this->config['api_url_sandbox']
            : $this->config['api_url_live'];
    }

    /**
     * Get the correct validation URL.
     */
    public function validate_url(): string {
        return $this->config['sandbox']
            ? $this->config['validate_url_sandbox']
            : $this->config['validate_url_live'];
    }

    /**
     * Initialize an SSLCOMMERZ transaction.
     * Returns the gateway URL to redirect the customer to, or an error.
     *
     * @param  array $order  Order record from DB
     * @param  array $customer  Customer info
     * @return array ['success' => bool, 'redirect_url' => string|null, 'error' => string|null]
     */
    public function initialize(array $order, array $customer): array {
        if (empty($this->config['store_id']) || empty($this->config['store_password'])) {
            error_log('[PaymentService] SSLCOMMERZ credentials not configured.');
            return ['success' => false, 'redirect_url' => null, 'error' => 'PAYMENT_NOT_CONFIGURED'];
        }

        $postData = [
            // Merchant credentials
            'store_id'       => $this->config['store_id'],
            'store_passwd'   => $this->config['store_password'],

            // Transaction info
            'total_amount'   => number_format((float) $order['total'], 2, '.', ''),
            'currency'       => $this->config['currency'],
            'tran_id'        => $order['order_number'],  // Our unique transaction ID

            // Callback URLs
            'success_url'    => $this->config['success_url'],
            'fail_url'       => $this->config['fail_url'],
            'cancel_url'     => $this->config['cancel_url'],
            'ipn_url'        => $this->config['ipn_url'],

            // Customer info
            'cus_name'       => $customer['name'],
            'cus_email'      => $customer['email'] ?: 'noreply@farmersbd.com',
            'cus_add1'       => $customer['address'],
            'cus_city'       => $customer['district'],
            'cus_state'      => $customer['district'],
            'cus_postcode'   => '1000',
            'cus_country'    => 'Bangladesh',
            'cus_phone'      => $customer['mobile'],

            // Shipping
            'ship_name'      => $customer['name'],
            'ship_add1'      => $customer['address'],
            'ship_city'      => $customer['district'],
            'ship_state'     => $customer['district'],
            'ship_postcode'  => '1000',
            'ship_country'   => 'Bangladesh',
            'shipping_method'=> 'Courier',

            // Product info
            'product_name'   => 'Aqua Products - ' . setting('site_name', 'FarmersBD'),
            'product_category' => $this->config['product_category'],
            'product_profile'=> 'general',

            // Multi-card (optional — enable more payment options)
            'multi_card_name'=> 'mastercard,visacard,amexcard,bkash,nagad,rocket',
        ];

        $response = $this->post($this->api_url(), $postData);

        if (!$response || !isset($response['GatewayPageURL'])) {
            error_log('[PaymentService] SSLCOMMERZ init failed: ' . json_encode($response));
            return ['success' => false, 'redirect_url' => null, 'error' => 'GATEWAY_INIT_FAILED'];
        }

        // Log transaction initiation
        db_execute(
            "INSERT INTO payments (order_id, tran_id, amount, currency, status, gateway_response)
             VALUES (?, ?, ?, ?, 'pending', ?)
             ON DUPLICATE KEY UPDATE gateway_response = VALUES(gateway_response)",
            [
                $order['id'],
                $order['order_number'],
                $order['total'],
                'BDT',
                json_encode($response),
            ]
        );

        return [
            'success'      => true,
            'redirect_url' => $response['GatewayPageURL'],
            'error'        => null,
        ];
    }

    /**
     * Validate the SSLCOMMERZ callback via server-side API call.
     * NEVER trust the success callback alone.
     *
     * @param  string $valId   val_id from the success POST
     * @param  float  $amount  Expected amount
     * @return array  ['valid' => bool, 'data' => array]
     */
    public function validate(string $valId, float $amount): array {
        $params = [
            'val_id'       => $valId,
            'store_id'     => $this->config['store_id'],
            'store_passwd' => $this->config['store_password'],
            'format'       => 'json',
        ];

        $url      = $this->validate_url() . '?' . http_build_query($params);
        $response = $this->get($url);

        if (!$response || !isset($response['status'])) {
            return ['valid' => false, 'data' => $response ?? []];
        }

        if ($response['status'] !== 'VALID' && $response['status'] !== 'VALIDATED') {
            return ['valid' => false, 'data' => $response];
        }

        // Server-side amount validation — critical security check
        $gatewayAmount = (float) ($response['amount'] ?? 0);
        if (abs($gatewayAmount - $amount) > 1.00) {
            error_log("[PaymentService] Amount mismatch! Expected: {$amount}, Got: {$gatewayAmount}");
            return ['valid' => false, 'data' => $response];
        }

        return ['valid' => true, 'data' => $response];
    }

    /**
     * Update order and payment records after successful IPN/callback validation.
     */
    public function complete_payment(int $orderId, string $tranId, string $valId, float $amount, array $gatewayData): void {
        db_execute(
            "UPDATE orders SET payment_status = 'paid', order_status = 'processing' WHERE id = ?",
            [$orderId]
        );

        db_execute(
            "INSERT INTO payments (order_id, tran_id, val_id, amount, currency, payment_method, status, gateway_response)
             VALUES (?, ?, ?, ?, 'BDT', ?, 'paid', ?)
             ON DUPLICATE KEY UPDATE
               val_id = VALUES(val_id),
               status = 'paid',
               payment_method = VALUES(payment_method),
               gateway_response = VALUES(gateway_response)",
            [
                $orderId,
                $tranId,
                $valId,
                $amount,
                $gatewayData['card_type'] ?? 'online',
                json_encode($gatewayData),
            ]
        );
    }

    /**
     * POST data using cURL.
     */
    private function post(string $url, array $data): ?array {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => !APP_DEBUG,
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result ? json_decode($result, true) : null;
    }

    /**
     * GET request using cURL.
     */
    private function get(string $url): ?array {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => !APP_DEBUG,
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result ? json_decode($result, true) : null;
    }
}
