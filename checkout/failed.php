<?php
// ============================================================
// FarmersBD — Payment Fail / Cancel Pages
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/flash.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/seo.php';

// Mark as failed if POST from SSLCOMMERZ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['tran_id'])) {
    db_execute(
        "UPDATE orders SET payment_status = 'failed', order_status = 'cancelled' WHERE order_number = ?",
        [$_POST['tran_id']]
    );
}

$page_seo = ['title' => 'পেমেন্ট ব্যর্থ | ' . setting('site_name', 'FarmersBD')];
include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/navbar.php';
?>
<div class="container py-5">
    <div class="result-page">
        <div class="result-icon error"><i class="bi bi-x-circle-fill"></i></div>
        <h1 class="h3 fw-bold mt-3">পেমেন্ট ব্যর্থ হয়েছে</h1>
        <p class="text-muted">আপনার পেমেন্ট প্রক্রিয়া সম্পন্ন হয়নি।</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap mt-3">
            <a href="<?= url('cart/') ?>" class="btn btn-primary">কার্টে ফিরুন</a>
            <a href="<?= url() ?>" class="btn btn-outline-secondary">হোমে যান</a>
        </div>
    </div>
</div>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
