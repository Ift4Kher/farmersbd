<?php
// ============================================================
// FarmersBD — Core Configuration
// ============================================================
header('Content-Type: text/html; charset=utf-8');

// Load .env file if it exists (simple parser, no external library)
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!isset($_ENV[$key])) {
            $_ENV[$key]    = $value;
            putenv("{$key}={$value}");
        }
    }
}

// ── Helper to read env with a default ──────────────────────
function env(string $key, $default = null) {
    $val = $_ENV[$key] ?? getenv($key);
    if ($val === false) return $default;
    if (strtolower($val) === 'true')  return true;
    if (strtolower($val) === 'false') return false;
    return $val;
}

// ── Application ─────────────────────────────────────────────
define('APP_NAME', env('APP_NAME', 'FarmersBD'));
define('MAINTENANCE_MODE', env('MAINTENANCE_MODE', false));

$rawAppUrl = env('APP_URL');
if (isset($_SERVER['HTTP_HOST'])) {
    $currentHost = $_SERVER['HTTP_HOST'];
    $isLocalhost = (strpos($currentHost, 'localhost') !== false || strpos($currentHost, '127.0.0.1') !== false);
    if (!$rawAppUrl || (!$isLocalhost && (strpos($rawAppUrl, 'localhost') !== false || strpos($rawAppUrl, '127.0.0.1') !== false))) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443 || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' ? 'https' : 'http';
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $baseDir = ($scriptDir === '/' || $scriptDir === '\\') ? '' : str_replace('\\', '/', $scriptDir);
        if (!empty($baseDir)) {
            $baseDir = preg_replace('#/(admin|cart|products|fish|diseases|blog|blogs|contact|checkout|auth|ai|api|consultation|services|categories|faq|newsletter|users|settings|orders|payments)(/.*)?$#i', '', $baseDir);
        }
        $rawAppUrl = $scheme . '://' . $currentHost . $baseDir;
    }
}
if (!$rawAppUrl) {
    $rawAppUrl = 'http://localhost/farmersbd';
}
define('APP_URL',      rtrim($rawAppUrl, '/'));
define('APP_ENV',      env('APP_ENV',      'production'));
define('APP_DEBUG',    env('APP_DEBUG',    true));
define('APP_TIMEZONE', env('APP_TIMEZONE', 'Asia/Dhaka'));

date_default_timezone_set(APP_TIMEZONE);

// ── Error Reporting ─────────────────────────────────────────
if (APP_DEBUG || isset($_GET['debug'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', dirname(__DIR__) . '/logs/php_error.log');
}

// ── Session Configuration ────────────────────────────────────
define('SESSION_NAME',     env('SESSION_NAME',     'farmersbd_session'));
define('SESSION_LIFETIME', (int) env('SESSION_LIFETIME', 7200));



if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', (string) SESSION_LIFETIME);
    ini_set('session.name', SESSION_NAME);

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', '1');
    }

    session_start();
}

// ── Security ─────────────────────────────────────────────────
define('SECRET_KEY',       env('SECRET_KEY',       'change-me-to-a-random-64-char-string'));
define('CSRF_TOKEN_NAME',  env('CSRF_TOKEN_NAME',  '_csrf_token'));

// ── File Uploads ─────────────────────────────────────────────
define('UPLOAD_MAX_SIZE',       (int) env('UPLOAD_MAX_SIZE', 52428800));
define('ALLOWED_IMAGE_TYPES',   explode(',', env('ALLOWED_IMAGE_TYPES', 'jpg,jpeg,png,webp')));
define('UPLOAD_BASE_DIR',       dirname(__DIR__) . '/uploads/');
define('UPLOAD_BASE_URL',       APP_URL . '/uploads/');

// ── AI Service ───────────────────────────────────────────────
define('AI_API_URL',         env('AI_API_URL',      'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent'));
define('AI_API_KEY',         env('AI_API_KEY',      ''));
define('AI_API_TIMEOUT',     (int) env('AI_API_TIMEOUT', 45));

define('AI_MAX_IMAGE_SIZE',  (int) env('AI_MAX_IMAGE_SIZE', 52428800));
define('AI_MAX_REQUESTS_PER_DAY', (int) env('AI_MAX_REQUESTS_PER_DAY', 20));

// ── SSLCOMMERZ ───────────────────────────────────────────────
define('SSLCOMMERZ_STORE_ID',       env('SSLCOMMERZ_STORE_ID',       ''));
define('SSLCOMMERZ_STORE_PASSWORD', env('SSLCOMMERZ_STORE_PASSWORD', ''));
define('SSLCOMMERZ_STORE_PASSWD',   SSLCOMMERZ_STORE_PASSWORD);
define('SSLCOMMERZ_SANDBOX',        env('SSLCOMMERZ_SANDBOX',        true));
define('SSLCOMMERZ_IS_SANDBOX',     SSLCOMMERZ_SANDBOX);

// ── Paths ────────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL',  APP_URL);

// ── Character Encoding ───────────────────────────────────────
mb_internal_encoding('UTF-8');
header('Content-Type: text/html; charset=utf-8');
