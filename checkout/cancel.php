<?php
// ============================================================
// FarmersBD — Checkout Cancel Page
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$order_number = sanitize_input($_GET['order'] ?? '');
if (!empty($order_number)) {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'failed', order_status = 'cancelled' WHERE order_number = ? AND payment_status = 'pending'");
    $stmt->execute([$order_number]);
}

$page_title = "পেমেন্ট বাতিল হয়েছে — " . APP_NAME;
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<div class="container py-5 text-center">
    <div class="modern-card p-5 mx-auto" style="max-width: 600px;">
        <i class="bi bi-x-circle text-warning display-1 mb-3"></i>
        <h2 class="fw-bold text-dark mb-2">পেমেন্ট বাতিল করা হয়েছে</h2>
        <p class="text-muted mb-4">আপনি পেমেন্ট প্রক্রিয়াটি বাতিল করেছেন। আপনি পুনরায় চেষ্টা করতে পারেন বা অন্য মাধ্যম বেছে নিতে পারেন।</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?= BASE_URL ?>/cart/index.php" class="btn btn-modern"><i class="bi bi-cart me-1"></i> কার্টে ফিরে যান</a>
            <a href="<?= BASE_URL ?>/checkout/index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat me-1"></i> পুনরায় চেষ্টা করুন</a>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
