<?php
// ============================================================
// FarmersBD — User Order Details Page
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

require_login();
$user_id = current_user_id();
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pdo = get_db_connection();

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    set_flash('error', 'অর্ডার পাওয়া যায়নি।');
    redirect(BASE_URL . '/account/orders.php');
}

$items_stmt = $pdo->prepare("SELECT oi.*, p.slug, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$items_stmt->execute([$order_id]);
$items = $items_stmt->fetchAll();

$page_title = "অর্ডার #" . $order['order_number'] . " — " . APP_NAME;
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1"><i class="bi bi-receipt text-primary me-2"></i> অর্ডার বিবরণী #<?= h($order['order_number']) ?></h3>
            <p class="text-muted small mb-0">অর্ডারের তারিখ: <?= format_date_bn($order['created_at']) ?></p>
        </div>
        <a href="<?= BASE_URL ?>/account/orders.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> আমার অর্ডারসমূহে ফিরে যান</a>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="modern-card mb-4 p-0">
                <div class="card-header bg-transparent py-3 border-bottom">
                    <h5 class="fw-bold mb-0">পণ্য তালিকা</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>পণ্য</th>
                                    <th class="text-center">মূল্য</th>
                                    <th class="text-center">পরিমাণ</th>
                                    <th class="text-end">মোট</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?= !empty($item['image']) ? get_upload_url($item['image'], 'products') : BASE_URL . '/assets/images/product-placeholder.png' ?>" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;" alt="">
                                                <div>
                                                    <a href="<?= BASE_URL ?>/products/details.php?slug=<?= h($item['slug'] ?? '') ?>" class="fw-medium text-dark text-decoration-none"><?= h($item['product_name']) ?></a>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center"><?= format_price_bn($item['unit_price']) ?></td>
                                        <td class="text-center"><?= format_number_bn($item['quantity']) ?></td>
                                        <td class="text-end fw-bold"><?= format_price_bn($item['subtotal']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="modern-card mb-4 p-0">
                <div class="card-header bg-transparent py-3 border-bottom">
                    <h5 class="fw-bold mb-0">অর্ডার সারসংক্ষেপ</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">পণ্যের সাবটোটাল:</span>
                        <span><?= format_price_bn($order['subtotal']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">ডেলিভারি চার্জ:</span>
                        <span><?= format_price_bn($order['shipping_cost']) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                        <span>সর্বমোট:</span>
                        <span class="text-primary"><?= format_price_bn($order['total'] ?? $order['grand_total'] ?? 0) ?></span>
                    </div>
                    <div class="mb-2">
                        <strong>পেমেন্ট মেথড:</strong> <?= strtoupper(h($order['payment_method'])) ?>
                    </div>
                    <div class="mb-2">
                        <strong>পেমেন্ট স্ট্যাটাস:</strong> <?= get_payment_status_badge($order['payment_status']) ?>
                    </div>
                    <div class="mb-0">
                        <strong>অর্ডার স্ট্যাটাস:</strong> <?= get_order_status_badge($order['order_status']) ?>
                    </div>
                </div>
            </div>

            <div class="modern-card p-0">
                <div class="card-header bg-transparent py-3 border-bottom">
                    <h5 class="fw-bold mb-0">শিপিং ঠিকানা</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1 fw-bold"><?= h($order['customer_name']) ?></p>
                    <p class="mb-1 text-muted"><i class="bi bi-telephone me-1"></i> <?= h($order['customer_mobile'] ?? $order['customer_phone'] ?? '') ?></p>
                    <p class="mb-1 text-muted"><i class="bi bi-geo-alt me-1"></i> <?= h($order['address'] ?? $order['shipping_address'] ?? '') ?>, <?= h($order['district']) ?></p>
                    <?php if (!empty($order['notes'])): ?>
                        <p class="small text-muted mt-2 border-top pt-2"><strong>নোট:</strong> <?= h($order['notes']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
