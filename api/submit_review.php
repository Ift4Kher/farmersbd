<?php
// ============================================================
// FarmersBD — API: Submit Review
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/flash.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

csrf_verify();
require_login();

$productId = (int)($_POST['product_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 5);
$review = trim($_POST['review'] ?? '');
$userId = current_user_id();

if ($productId <= 0 || $rating < 1 || $rating > 5 || empty($review)) {
    flash('সকল তথ্য সঠিকভাবে পূরণ করুন।', FLASH_ERROR);
    redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL);
}

// Check if product exists
$product = db_query_one("SELECT slug FROM products WHERE id = ?", [$productId]);
if (!$product) {
    flash('পণ্যটি পাওয়া যায়নি।', FLASH_ERROR);
    redirect(BASE_URL . '/products/');
}

// Check if user already reviewed this product
$existing = db_query_one("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?", [$productId, $userId]);
if ($existing) {
    flash('আপনি ইতিমধ্যে এই পণ্যের রিভিউ দিয়েছেন।', FLASH_ERROR);
    redirect(BASE_URL . '/products/details.php?slug=' . $product['slug']);
}

db_insert(
    "INSERT INTO reviews (product_id, user_id, rating, review, is_approved) VALUES (?, ?, ?, ?, 0)",
    [$productId, $userId, $rating, $review]
);

flash('আপনার রিভিউ সফলভাবে সাবমিট করা হয়েছে। অ্যাডমিন অনুমোদনের পর এটি প্রকাশিত হবে।', FLASH_SUCCESS);
redirect(BASE_URL . '/products/details.php?slug=' . $product['slug']);
