<?php
// ============================================================
// FarmersBD — SSLCOMMERZ Configuration
// ============================================================
// Credentials are loaded from environment variables only.
// Never hardcode credentials here.
// ============================================================

return [
    'store_id'       => SSLCOMMERZ_STORE_ID,
    'store_password' => SSLCOMMERZ_STORE_PASSWORD,
    'sandbox'        => SSLCOMMERZ_SANDBOX,

    // API endpoints
    'api_url_sandbox' => 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php',
    'api_url_live'    => 'https://securepay.sslcommerz.com/gwprocess/v4/api.php',

    // Validation endpoints
    'validate_url_sandbox' => 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php',
    'validate_url_live'    => 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php',

    // Callback URLs (must match what you register in SSLCOMMERZ dashboard)
    'success_url' => APP_URL . '/payment/success.php',
    'fail_url'    => APP_URL . '/payment/fail.php',
    'cancel_url'  => APP_URL . '/payment/cancel.php',
    'ipn_url'     => APP_URL . '/payment/ipn.php',

    // Product category code
    'product_category' => 'Agricultural Products',

    // Currency
    'currency' => 'BDT',
];
