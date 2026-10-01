<?php
// ============================================================
// FarmersBD — Admin Order View & Status Update
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/csrf.php';
require_once $base . '/includes/flash.php';

require_admin();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pdo = get_db_connection();

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    set_flash('error', 'অর্ডার পাওয়া যায়নি।');
    redirect(BASE_URL . '/admin/orders/index.php');
}

$items_stmt = $pdo->prepare("SELECT oi.*, p.slug, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$items_stmt->execute([$id]);
$items = $items_stmt->fetchAll();

$page_title = "অর্ডার বিস্তারিত #" . $order['order_number'] . " — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">অর্ডার বিবরণী #<?= h($order['order_number']) ?></h3>
        <span class="text-muted small">তারিখ: <?= format_date_bn($order['created_at']) ?></span>
    </div>
    <a href="<?= BASE_URL ?>/admin/orders/index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> অর্ডারের তালিকায় ফিরে যান</a>
</div>

<?php display_flash(); ?>

<div class="row g-4">
    <!-- Items & Customer Info -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="fw-bold mb-0">অর্ডারকৃত পণ্যসমূহ</h5>
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
                                            <div class="fw-bold text-dark"><?= h($item['product_name']) ?></div>
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

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="fw-bold mb-0">গ্রাহক ও শিপিং ঠিকানা</h5>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>গ্রাহকের নাম:</strong> <?= h($order['customer_name']) ?></p>
                <p class="mb-1"><strong>ফোন নম্বর:</strong> <?= h($order['customer_mobile'] ?? $order['customer_phone'] ?? '') ?></p>
                <p class="mb-1"><strong>ইমেইল:</strong> <?= h($order['customer_email'] ?? 'N/A') ?></p>
                <p class="mb-1"><strong>জেলা:</strong> <?= h($order['district']) ?></p>
                <p class="mb-1"><strong>শিপিং ঠিকানা:</strong> <?= h($order['address'] ?? $order['shipping_address'] ?? '') ?></p>
                <?php if (!empty($order['notes'])): ?>
                    <p class="mb-0 text-muted mt-2 border-top pt-2"><strong>নোট:</strong> <?= h($order['notes']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Status Update Sidebar -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="fw-bold mb-0">অর্ডার স্ট্যাটাস আপডেট</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/admin/orders/update-status.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label fw-medium">অর্ডার স্ট্যাটাস</label>
                        <select name="order_status" class="form-select">
                            <option value="pending" <?= $order['order_status'] === 'pending' ? 'selected' : '' ?>>Pending (অপেক্ষমাণ)</option>
                            <option value="processing" <?= $order['order_status'] === 'processing' ? 'selected' : '' ?>>Processing (প্রসেসিং)</option>
                            <option value="shipped" <?= $order['order_status'] === 'shipped' ? 'selected' : '' ?>>Shipped (শিপড)</option>
                            <option value="delivered" <?= $order['order_status'] === 'delivered' ? 'selected' : '' ?>>Delivered (ডেলিভারড)</option>
                            <option value="cancelled" <?= $order['order_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled (বাতিল)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">পেমেন্ট স্ট্যাটাস</label>
                        <select name="payment_status" class="form-select">
                            <option value="pending" <?= $order['payment_status'] === 'pending' ? 'selected' : '' ?>>Pending (বকেয়া)</option>
                            <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid (পরিশোধিত)</option>
                            <option value="failed" <?= $order['payment_status'] === 'failed' ? 'selected' : '' ?>>Failed (ব্যর্থ)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-circle me-1"></i> স্ট্যাটাস সংরক্ষণ করুন</button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="fw-bold mb-0">হিসাব সারসংক্ষেপ</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>পণ্যের সাবটোটাল:</span>
                    <span><?= format_price_bn($order['subtotal']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>ডেলিভারি চার্জ:</span>
                    <span><?= format_price_bn($order['shipping_cost']) ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold fs-5 text-primary">
                    <span>সর্বমোট:</span>
                    <span><?= format_price_bn($order['total'] ?? $order['grand_total'] ?? 0) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
