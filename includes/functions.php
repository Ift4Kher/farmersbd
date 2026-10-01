<?php
// ============================================================
// FarmersBD — Common Functions
// ============================================================

if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle) {
        return $needle !== '' && substr($haystack, -strlen($needle)) === (string)$needle;
    }
}
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

// ── Database query shorthand ──────────────────────────────────

/**
 * Execute a SELECT query and return all rows.
 */
function db_query(string $sql, array $params = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Execute a SELECT query and return one row.
 */
function db_query_one(string $sql, array $params = []): ?array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Execute an INSERT/UPDATE/DELETE and return affected rows.
 */
function db_execute(string $sql, array $params = []): int
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * Execute an INSERT and return the last insert ID.
 */
function db_insert(string $sql, array $params = []): int
{
    db()->prepare($sql)->execute($params);
    return (int) db()->lastInsertId();
}

/**
 * Send an admin notification.
 */
function send_admin_notification(string $type, string $message, ?string $link = null): void
{
    db_insert("INSERT INTO admin_notifications (type, message, link) VALUES (?, ?, ?)", [$type, $message, $link]);
}

// ── Site Settings ────────────────────────────────────────────

/** In-memory cache for settings */
$_settings_cache = null;

/**
 * Load all site settings into cache.
 */
function load_settings(): array
{
    global $_settings_cache;
    if ($_settings_cache === null) {
        $_settings_cache = [];
        $rows = db_query("SELECT setting_key, setting_value FROM site_settings");
        foreach ($rows as $row) {
            $_settings_cache[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $_settings_cache;
}

/**
 * Get a single site setting value.
 */
function setting(string $key, $default = ''): string
{
    $settings = load_settings();
    return $settings[$key] ?? $default;
}

// ── Typography ───────────────────────────────────────────────

/**
 * Return <style>:root{…}</style> with typography CSS custom properties.
 */
function typography_css(): string
{
    $rows = db_query("SELECT prop_name, prop_value FROM site_typography");
    if (empty($rows))
        return '';

    $css = "<style>:root{";
    foreach ($rows as $row) {
        // Validate: only allow safe CSS property values (no quotes except for font-family)
        $val = sanitize_css_value($row['prop_value']);
        $css .= htmlspecialchars($row['prop_name'], ENT_QUOTES) . ':' . $val . ';';
    }
    $css .= "}</style>";
    return $css;
}

/**
 * Sanitize a CSS property value to prevent injection.
 */
function sanitize_css_value(string $value): string
{
    // Allow: numbers, units (px/rem/em/%), font names in quotes, common keywords, commas, spaces, dots, dashes
    $value = trim($value);
    // Remove any characters that could escape the CSS context
    $value = preg_replace('/[<>{}]/', '', $value);
    // Limit length
    return mb_substr($value, 0, 200);
}

// ── Homepage Content ──────────────────────────────────────────

$_homepage_cache = null;

function load_homepage(): array
{
    global $_homepage_cache;
    if ($_homepage_cache === null) {
        $_homepage_cache = [];
        $rows = db_query("SELECT section, item_key, item_value FROM homepage_content");
        foreach ($rows as $row) {
            $_homepage_cache[$row['section']][$row['item_key']] = $row['item_value'];
        }
    }
    return $_homepage_cache;
}

function hp(string $section, string $key, string $default = ''): string
{
    $hp = load_homepage();
    return htmlspecialchars($hp[$section][$key] ?? $default, ENT_QUOTES, 'UTF-8');
}

// ── Output Escaping ──────────────────────────────────────────

function e(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function raw(?string $str): string
{
    // For trusted admin-entered rich content only
    // Strips dangerous tags
    return strip_tags($str ?? '', '<p><b><strong><i><em><u><ul><ol><li><h1><h2><h3><h4><h5><h6><br><hr><a><img><table><thead><tbody><tr><th><td><blockquote><span><div>');
}

// ── URLs & Routing ───────────────────────────────────────────

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function redirect_url(string $path): void
{
    redirect(url($path));
}

function current_url(): string
{
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    return $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

// ── Slugs ────────────────────────────────────────────────────

function slugify(string $text): string
{
    // Transliterate Bangla + ASCII
    $text = mb_strtolower(trim($text));
    // Replace non-alphanumeric (allow ASCII letters, numbers, spaces)
    $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
    $text = preg_replace('/[\s-]+/u', '-', $text);
    $text = trim($text, '-');
    // Generate a random suffix if empty
    if (empty($text))
        $text = 'item-' . uniqid();
    return $text;
}

function unique_slug(string $table, string $text, ?int $excludeId = null): string
{
    $base = slugify($text);
    $slug = $base;
    $i = 1;
    while (true) {
        $sql = "SELECT id FROM `{$table}` WHERE slug = ?";
        $params = [$slug];
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        $row = db_query_one($sql, $params);
        if (!$row)
            break;
        $slug = $base . '-' . $i++;
    }
    return $slug;
}

// ── Order Numbers ────────────────────────────────────────────

function generate_order_number(): string
{
    return 'FBD-' . strtoupper(substr(md5(uniqid('', true)), 0, 8)) . '-' . date('ymd');
}

// ── Prices & Currency ────────────────────────────────────────

function get_currency_symbol(): string {
    return defined('CURRENCY_SYMBOL') ? CURRENCY_SYMBOL : '৳';
}

function format_price(float $amount): string
{
    return trim(get_currency_symbol() . ' ' . number_format($amount, 2));
}

function format_number_bn($number): string
{
    $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return str_replace($en, $bn, (string) $number);
}

function effective_price(array $product): float
{
    if (!empty($product['discount_price']) && $product['discount_price'] > 0) {
        return (float) $product['discount_price'];
    }
    return (float) $product['price'];
}

function discount_percent(array $product): int
{
    if (empty($product['discount_price']) || $product['discount_price'] <= 0)
        return 0;
    return (int) round((1 - $product['discount_price'] / $product['price']) * 100);
}

// ── Time ─────────────────────────────────────────────────────

function time_ago(string $datetime): string
{
    $time = time() - strtotime($datetime);
    if ($time < 60)
        return 'এইমাত্র';
    if ($time < 3600)
        return (int) ($time / 60) . ' মিনিট আগে';
    if ($time < 86400)
        return (int) ($time / 3600) . ' ঘণ্টা আগে';
    if ($time < 604800)
        return (int) ($time / 86400) . ' দিন আগে';
    return date('d M Y', strtotime($datetime));
}

function bangla_date(string $datetime): string
{
    return date('d-m-Y', strtotime($datetime));
}

// ── Text ─────────────────────────────────────────────────────

function excerpt(string $text, int $length = 150): string
{
    $text = strip_tags($text);
    if (mb_strlen($text) <= $length)
        return $text;
    return mb_substr($text, 0, $length) . '…';
}

// ── File Size ────────────────────────────────────────────────

function format_bytes(int $bytes): string
{
    if ($bytes < 1024)
        return $bytes . ' B';
    if ($bytes < 1048576)
        return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 1) . ' MB';
}

// ── IP Address ───────────────────────────────────────────────

function get_client_ip(): string
{
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP))
                return $ip;
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// ── Cart ─────────────────────────────────────────────────────

function get_or_create_cart(?int $userId = null): int
{
    $uid = $userId ?? ($_SESSION['user_id'] ?? null);
    if ($uid) {
        $cart = db_query_one("SELECT id FROM carts WHERE user_id = ? AND recovery_order_id IS NULL AND status NOT IN ('recovered', 'lost')", [$uid]);
        if ($cart)
            return (int) $cart['id'];
        return (int) db_insert("INSERT INTO carts (user_id) VALUES (?)", [$uid]);
    }
    // Guest cart via session
    if (!isset($_SESSION['cart_session_id'])) {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['cart_session_id'] = bin2hex(random_bytes(16));
    }
    $sid = $_SESSION['cart_session_id'];
    $cart = db_query_one("SELECT id FROM carts WHERE session_id = ? AND recovery_order_id IS NULL AND status NOT IN ('recovered', 'lost')", [$sid]);
    if ($cart)
        return (int) $cart['id'];
    return (int) db_insert("INSERT INTO carts (session_id) VALUES (?)", [$sid]);
}

function get_cart_id(): int
{
    return get_or_create_cart();
}

function get_cart_count(?int $cartId = null): int
{
    $cId = $cartId ?? get_cart_id();
    if (!$cId)
        return 0;
    $row = db_query_one("SELECT SUM(quantity) AS cnt FROM cart_items WHERE cart_id = ?", [$cId]);
    return (int) ($row['cnt'] ?? 0);
}

function get_cart_items(): array
{
    $cartId = get_cart_id();
    if (!$cartId)
        return [];
    return db_query(
        "SELECT ci.id, ci.quantity, p.id AS product_id, p.name, p.slug, p.image, 
                p.price, p.discount_price, p.stock
         FROM cart_items ci
         JOIN products p ON p.id = ci.product_id
         WHERE ci.cart_id = ? AND p.is_active = 1
         ORDER BY ci.created_at ASC",
        [$cartId]
    );
}

// ── Status Labels ────────────────────────────────────────────

function order_status_label(string $status): string
{
    return ORDER_STATUSES[$status] ?? $status;
}

function payment_status_label(string $status): string
{
    return PAYMENT_STATUSES[$status] ?? $status;
}

function consultation_status_label(string $status): string
{
    return CONSULTATION_STATUSES[$status] ?? $status;
}

function order_status_badge(string $status): string
{
    $map = [
        'pending' => 'bg-warning text-dark',
        'processing' => 'bg-info text-dark',
        'shipped' => 'bg-primary',
        'delivered' => 'bg-success',
        'cancelled' => 'bg-danger',
    ];
    $cls = $map[$status] ?? 'bg-secondary';
    return '<span class="badge ' . $cls . '">' . e(order_status_label($status)) . '</span>';
}

function get_order_status_badge(string $status): string
{
    return order_status_badge($status);
}

function payment_status_badge(string $status): string
{
    $map = [
        'pending' => 'bg-warning text-dark',
        'paid' => 'bg-success',
        'failed' => 'bg-danger',
        'cancelled' => 'bg-secondary',
    ];
    $cls = $map[$status] ?? 'bg-secondary';
    return '<span class="badge ' . $cls . '">' . e(payment_status_label($status)) . '</span>';
}

function get_payment_status_badge(string $status): string
{
    return payment_status_badge($status);
}

function get_consultation_status_badge(string $status): string
{
    $map = [
        'pending' => 'bg-warning text-dark',
        'answered' => 'bg-success',
        'closed' => 'bg-secondary',
    ];
    $cls = $map[$status] ?? 'bg-secondary';
    return '<span class="badge ' . $cls . '">' . e(consultation_status_label($status)) . '</span>';
}

function consultation_status_badge(string $status): string
{
    return get_consultation_status_badge($status);
}

// ── Image Helpers ────────────────────────────────────────────

function asset(string $path): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function uploaded_image_url(string $subdir, ?string $filename, string $placeholder = ''): string
{
    if ($filename && file_exists(UPLOAD_BASE_DIR . $subdir . '/' . $filename)) {
        return UPLOAD_BASE_URL . $subdir . '/' . e($filename);
    }
    if ($filename && file_exists(dirname(__DIR__) . '/assets/images/' . $subdir . '/' . $filename)) {
        return asset('assets/images/' . $subdir . '/' . $filename);
    }
    return $placeholder ?: asset('assets/images/general/placeholder.jpg');
}

// ── Active Nav ───────────────────────────────────────────────

function nav_active(string $path): string
{
    $current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
    return str_starts_with($current, $path) ? 'active' : '';
}

// ── Pagination data ──────────────────────────────────────────

function paginate(int $total, int $perPage, int $currentPage, string $baseUrl = ''): array
{
    $totalPages = (int) ceil($total / $perPage);
    $currentPage = max(1, min($currentPage, max(1, $totalPages)));
    $offset = ($currentPage - 1) * $perPage;
    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
        'base_url' => $baseUrl
    ];
}

// ── Additional Compatibility Helpers ─────────────────────────

if (!function_exists('get_db_connection')) {
    function get_db_connection(): PDO
    {
        return db();
    }
}

if (!function_exists('get_db')) {
    function get_db(): PDO
    {
        return db();
    }
}

if (!function_exists('sanitize_input')) {
    function sanitize_input($data): string
    {
        return trim(htmlspecialchars((string) $data, ENT_QUOTES, 'UTF-8'));
    }
}

if (!function_exists('h')) {
    function h($data): string
    {
        return e($data);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void
    {
        header("Location: " . $url);
        exit;
    }
}

if (!function_exists('is_post_request')) {
    function is_post_request(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }
}

if (!function_exists('get_upload_url')) {
    function get_upload_url(?string $filename, string $category = 'general'): string
    {
        return uploaded_image_url($category, $filename);
    }
}

if (!function_exists('format_price_bn')) {
    function format_price_bn($price): string
    {
        return trim(get_currency_symbol() . ' ' . format_number_bn(number_format((float) $price, 2)));
    }
}

if (!function_exists('format_date_bn')) {
    function format_date_bn($date): string
    {
        return date('d M Y', strtotime($date));
    }
}

if (!function_exists('format_ai_markdown')) {
    function format_ai_markdown(?string $text): string
    {
        if (!$text)
            return '';

        // Normalize newlines
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // Escape HTML for safety while allowing styling
        $lines = explode("\n", $text);
        $html = '';
        $inUl = false;
        $inOl = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                if ($inUl) {
                    $html .= "</ul>\n";
                    $inUl = false;
                }
                if ($inOl) {
                    $html .= "</ol>\n";
                    $inOl = false;
                }
                continue;
            }

            // Unordered list item (* or -)
            if (preg_match('/^[\*\-]\s+(.+)$/u', $trimmed, $m)) {
                if ($inOl) {
                    $html .= "</ol>\n";
                    $inOl = false;
                }
                if (!$inUl) {
                    $html .= "<ul class=\"ai-analysis-list mb-3\">\n";
                    $inUl = true;
                }
                $item = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
                $item = preg_replace('/\*\*(.*?)\*\*/u', '<strong class="text-dark">$1</strong>', $item);
                $html .= "<li class=\"mb-1\">{$item}</li>\n";
                continue;
            }

            // Ordered list item (1. 2.)
            if (preg_match('/^(\d+)\.\s+(.+)$/u', $trimmed, $m)) {
                if ($inUl) {
                    $html .= "</ul>\n";
                    $inUl = false;
                }
                if (!$inOl) {
                    $html .= "<ol class=\"ai-analysis-steps mb-3 ps-3\">\n";
                    $inOl = true;
                }
                $item = htmlspecialchars($m[2], ENT_QUOTES, 'UTF-8');
                $item = preg_replace('/\*\*(.*?)\*\*/u', '<strong class="text-dark">$1</strong>', $item);
                $html .= "<li class=\"mb-2 fw-medium\">{$item}</li>\n";
                continue;
            }

            // Close active lists if current line is not a list item
            if ($inUl) {
                $html .= "</ul>\n";
                $inUl = false;
            }
            if ($inOl) {
                $html .= "</ol>\n";
                $inOl = false;
            }

            // Headings
            if (preg_match('/^###\s+(.+)$/u', $trimmed, $m)) {
                $hText = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
                $html .= "<h5 class=\"fw-bold text-dark mt-3 mb-2 d-flex align-items-center gap-2\"><i class=\"bi bi-check2-circle text-success\"></i>{$hText}</h5>\n";
                continue;
            }
            if (preg_match('/^##\s+(.+)$/u', $trimmed, $m)) {
                $hText = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
                $html .= "<h4 class=\"fw-bold text-primary mt-4 mb-2 pb-1 border-bottom border-light-subtle d-flex align-items-center gap-2\"><i class=\"bi bi-stars text-warning\"></i>{$hText}</h4>\n";
                continue;
            }
            if (preg_match('/^#\s+(.+)$/u', $trimmed, $m)) {
                $hText = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
                $html .= "<h3 class=\"fw-bold text-primary-dark mt-4 mb-3\">{$hText}</h3>\n";
                continue;
            }

            // Regular paragraph
            $pText = htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8');
            $pText = preg_replace('/\*\*(.*?)\*\*/u', '<strong class="text-dark">$1</strong>', $pText);
            $html .= "<p class=\"mb-2 text-secondary-emphasis lh-base\">{$pText}</p>\n";
        }

        if ($inUl) {
            $html .= "</ul>\n";
        }
        if ($inOl) {
            $html .= "</ol>\n";
        }

        return $html;
    }
}

/**
 * Log front-end visitor traffic into database
 */
function log_traffic_visit(): void
{
    static $logged = false;
    if ($logged || php_sapi_name() === 'cli') return;
    $logged = true;

    // Skip admin and API internal requests
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (str_contains($uri, '/admin') || str_contains($uri, '/api') || str_contains($uri, '/assets')) {
        return;
    }

    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ref = $_SERVER['HTTP_REFERER'] ?? '';

        // Device detection
        $device = 'desktop';
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $ua)) {
            $device = 'tablet';
        } elseif (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $ua)) {
            $device = 'mobile';
        }

        // Referral source detection
        $source = 'direct';
        if (!empty($ref)) {
            if (str_contains($ref, 'facebook.com') || str_contains($ref, 'fb.com') || str_contains($ref, 'instagram.com') || str_contains($ref, 'tiktok.com') || str_contains($ref, 'youtube.com')) {
                $source = 'social';
            } elseif (str_contains($ref, 'google.com') || str_contains($ref, 'bing.com') || str_contains($ref, 'yahoo.com')) {
                $source = 'search';
            } else {
                $source = 'referral';
            }
        }

        $pdo = get_db_connection();
        $stmt = $pdo->prepare("INSERT INTO traffic_logs (ip_address, user_agent, device_type, page_url, referral_source, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$ip, substr($ua, 0, 255), $device, substr($uri, 0, 255), $source]);
    } catch (Throwable $e) {
        // Silent catch so visitor experience is unaffected
    }
}

/**
 * Human-readable relative time difference in Bengali
 */
if (!function_exists('human_time_diff')) {
    function human_time_diff(string|int $from, ?int $to = null): string
    {
        $fromTime = is_numeric($from) ? (int)$from : strtotime($from);
        $toTime   = $to ?? time();
        $diff     = max(1, $toTime - $fromTime);

        if ($diff < 60) {
            return 'কিছুক্ষণ আগে';
        } elseif ($diff < 3600) {
            $mins = (int)($diff / 60);
            return format_number_bn($mins) . ' মিনিট আগে';
        } elseif ($diff < 86400) {
            $hrs = (int)($diff / 3600);
            return format_number_bn($hrs) . ' ঘণ্টা আগে';
        } elseif ($diff < 604800) {
            $days = (int)($diff / 86400);
            return format_number_bn($days) . ' দিন আগে';
        } elseif ($diff < 2592000) {
            $weeks = (int)($diff / 604800);
            return format_number_bn($weeks) . ' সপ্তাহ আগে';
        } else {
            return date('j M Y', $fromTime);
        }
    }
}


