<?php
// ============================================================
// FarmersBD — Application Constants
// ============================================================

// ── Application Base Constants ───────────────────────────────
if (!defined('BASE_URL')) {
    define('BASE_URL', (function_exists('url') ? url() : '/farmersbd'));
}
if (!defined('APP_NAME')) {
    define('APP_NAME', 'FarmersBD');
}

// ── Order Statuses ────────────────────────────────────────────
define('ORDER_STATUS_PENDING',    'pending');
define('ORDER_STATUS_PROCESSING', 'processing');
define('ORDER_STATUS_SHIPPED',    'shipped');
define('ORDER_STATUS_DELIVERED',  'delivered');
define('ORDER_STATUS_CANCELLED',  'cancelled');

define('ORDER_STATUSES', [
    ORDER_STATUS_PENDING    => 'অপেক্ষমাণ',
    ORDER_STATUS_PROCESSING => 'প্রক্রিয়াধীন',
    ORDER_STATUS_SHIPPED    => 'পাঠানো হয়েছে',
    ORDER_STATUS_DELIVERED  => 'ডেলিভারি সম্পন্ন',
    ORDER_STATUS_CANCELLED  => 'বাতিল',
]);

// ── Payment Statuses ─────────────────────────────────────────
define('PAYMENT_STATUS_PENDING',   'pending');
define('PAYMENT_STATUS_PAID',      'paid');
define('PAYMENT_STATUS_FAILED',    'failed');
define('PAYMENT_STATUS_CANCELLED', 'cancelled');

define('PAYMENT_STATUSES', [
    PAYMENT_STATUS_PENDING   => 'অপেক্ষমাণ',
    PAYMENT_STATUS_PAID      => 'পরিশোধিত',
    PAYMENT_STATUS_FAILED    => 'ব্যর্থ',
    PAYMENT_STATUS_CANCELLED => 'বাতিল',
]);

// ── Payment Methods ──────────────────────────────────────────
define('PAYMENT_COD',        'cod');
define('PAYMENT_SSLCOMMERZ', 'sslcommerz');

define('PAYMENT_METHODS', [
    PAYMENT_COD        => 'ক্যাশ অন ডেলিভারি',
    PAYMENT_SSLCOMMERZ => 'অনলাইন পেমেন্ট (SSLCommerz)',
]);

// ── Consultation Statuses ────────────────────────────────────
define('CONSULTATION_PENDING',  'pending');
define('CONSULTATION_ANSWERED', 'answered');
define('CONSULTATION_CLOSED',   'closed');

define('CONSULTATION_STATUSES', [
    CONSULTATION_PENDING  => 'অপেক্ষমাণ',
    CONSULTATION_ANSWERED => 'উত্তর দেওয়া হয়েছে',
    CONSULTATION_CLOSED   => 'বন্ধ',
]);

// ── Pagination ───────────────────────────────────────────────
define('ITEMS_PER_PAGE',       12);
define('ADMIN_ITEMS_PER_PAGE', 20);
define('BLOG_ITEMS_PER_PAGE',  9);

// ── Flash Message Types ──────────────────────────────────────
define('FLASH_SUCCESS', 'success');
define('FLASH_ERROR',   'danger');
define('FLASH_WARNING', 'warning');
define('FLASH_INFO',    'info');

// ── User Roles ───────────────────────────────────────────────
define('ROLE_USER',  'user');
define('ROLE_ADMIN', 'admin');

// ── Content Statuses ─────────────────────────────────────────
define('STATUS_ACTIVE',   1);
define('STATUS_INACTIVE', 0);

// ── Image Dimensions ────────────────────────────────────────
define('MAX_IMAGE_WIDTH',  2000);
define('MAX_IMAGE_HEIGHT', 2000);
define('HERO_IMAGE_WIDTH', 1920);

// ── Currency ─────────────────────────────────────────────────
define('CURRENCY_SYMBOL', '৳');
define('CURRENCY_CODE',   'BDT');

// ── Districts of Bangladesh ──────────────────────────────────
define('BANGLADESH_DISTRICTS', [
    'ঢাকা','চট্টগ্রাম','রাজশাহী','খুলনা','বরিশাল','সিলেট','রংপুর','ময়মনসিংহ',
    'কুমিল্লা','ফরিদপুর','টাঙ্গাইল','জামালপুর','নরসিংদী','নারায়ণগঞ্জ','মানিকগঞ্জ',
    'মুন্সিগঞ্জ','গাজীপুর','কিশোরগঞ্জ','নেত্রকোনা','শেরপুর','ময়মনসিংহ','গোপালগঞ্জ',
    'মাদারীপুর','রাজবাড়ী','শরীয়তপুর','চাঁদপুর','ব্রাহ্মণবাড়িয়া','লক্ষ্মীপুর',
    'নোয়াখালী','ফেনী','খাগড়াছড়ি','রাঙামাটি','বান্দরবান','কক্সবাজার',
    'পাবনা','সিরাজগঞ্জ','বগুড়া','জয়পুরহাট','নওগাঁ','নাটোর','চাঁপাইনবাবগঞ্জ',
    'যশোর','সাতক্ষীরা','মেহেরপুর','নড়াইল','ঝিনাইদহ','মাগুরা','কুষ্টিয়া',
    'চুয়াডাঙ্গা','বাগেরহাট','পিরোজপুর','ঝালকাঠি','বরগুনা','পটুয়াখালী','ভোলা',
    'হবিগঞ্জ','মৌলভীবাজার','সুনামগঞ্জ','কুড়িগ্রাম','লালমনিরহাট','নীলফামারী',
    'গাইবান্ধা','পঞ্চগড়','ঠাকুরগাঁও','দিনাজপুর',
]);
