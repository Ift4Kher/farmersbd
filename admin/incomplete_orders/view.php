<?php
// ============================================================
// FarmersBD — Admin Incomplete Orders View
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
$id = isset($_GET['id']) ? (int)($_GET['id']) : 0;
$pdo = get_db_connection();

$stmt = $pdo->prepare("SELECT c.*, 
                       COALESCE(c.customer_name, u.name) as customer_name, 
                       COALESCE(c.customer_mobile, u.mobile) as customer_mobile, 
                       COALESCE(c.customer_email, u.email) as customer_email, 
                       COALESCE(c.customer_address, u.address) as customer_address, 
                       COALESCE(c.customer_district, u.district) as customer_district 
                       FROM carts c LEFT JOIN users u ON c.user_id = u.id WHERE c.id = ?");
$stmt->execute([$id]);
$cart = $stmt->fetch();

if (!$cart) {
    set_flash('error', 'অসম্পূর্ণ অর্ডার পাওয়া যায়নি।');
    redirect(BASE_URL . '/admin/incomplete_orders/index.php');
}

$items_stmt = $pdo->prepare("SELECT ci.*, p.name as product_name, p.price, p.image 
                             FROM cart_items ci JOIN products p ON ci.product_id = p.id 
                             WHERE ci.cart_id = ?");
$items_stmt->execute([$id]);
$items = $items_stmt->fetchAll();

$page_title = "অসম্পূর্ণ অর্ডার বিস্তারিত — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">অসম্পূর্ণ অর্ডার বিস্তারিত</h3>
        <span class="text-muted small">শেষ আপডেট: <?= format_date_bn($cart['updated_at']) ?></span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/admin/incomplete_orders/index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> তালিকায় ফিরে যান</a>
        <form action="<?= BASE_URL ?>/admin/incomplete_orders/delete.php" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে আপনি এই অসম্পূর্ণ অর্ডারটি মুছে ফেলতে চান?');" class="d-inline">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $cart['id'] ?>">
            <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> মুছুন</button>
        </form>
    </div>
</div>

<?php display_flash(); ?>

<?php if ($cart['recovery_order_id']): ?>
<div class="alert alert-success d-flex align-items-center" role="alert">
    <i class="bi bi-check-circle-fill fs-4 me-3"></i>
    <div>
        <strong>এই অর্ডারটি সফলভাবে সম্পন্ন হয়েছে!</strong> (Recovered)<br>
        সফল অর্ডার আইডি: <a href="<?= BASE_URL ?>/admin/orders/view.php?id=<?= $cart['recovery_order_id'] ?>" class="alert-link fw-bold text-decoration-underline">#<?= $cart['recovery_order_id'] ?></a>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="fw-bold mb-0">কার্টে থাকা পণ্যসমূহ</h5>
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
                            <?php $total = 0; foreach ($items as $item): $subtotal = $item['price'] * $item['quantity']; $total += $subtotal; ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?= !empty($item['image']) ? get_upload_url($item['image'], 'products') : BASE_URL . '/assets/images/product-placeholder.png' ?>" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;" alt="">
                                            <div class="fw-bold text-dark"><?= h($item['product_name']) ?></div>
                                        </div>
                                    </td>
                                    <td class="text-center"><?= format_price_bn($item['price']) ?></td>
                                    <td class="text-center"><?= format_number_bn($item['quantity']) ?></td>
                                    <td class="text-end fw-bold"><?= format_price_bn($subtotal) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end border-0 fs-5 text-primary">সর্বমোট:</th>
                                <th class="text-end border-0 fs-5 text-primary"><?= format_price_bn($total) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="fw-bold mb-0">গ্রাহকের তথ্য</h5>
            </div>
            <div class="card-body">
                <?php if ($cart['customer_name']): ?>
                    <p class="mb-1">
                        <strong>নাম:</strong> <?= h($cart['customer_name']) ?>
                        <?php if (!$cart['user_id']): ?> <span class="badge bg-secondary ms-1" style="font-size:0.7em">Guest</span> <?php endif; ?>
                    </p>
                    <p class="mb-1"><strong>ফোন নম্বর:</strong> <?= h($cart['customer_mobile'] ?? 'N/A') ?> 
                        <?php if ($cart['customer_mobile']): ?>
                        <a href="tel:<?= h($cart['customer_mobile']) ?>" class="btn btn-sm btn-outline-success ms-2 py-0 px-2"><i class="bi bi-telephone"></i> Call</a>
                        <a href="https://wa.me/<?= h($cart['customer_mobile']) ?>" target="_blank" class="btn btn-sm btn-outline-success py-0 px-2"><i class="bi bi-whatsapp"></i> WhatsApp</a>
                        <?php endif; ?>
                    </p>
                    <p class="mb-1"><strong>ইমেইল:</strong> <?= h($cart['customer_email'] ?: 'দেওয়া হয়নি') ?></p>
                    <p class="mb-1"><strong>ঠিকানা:</strong> <?= h($cart['customer_address'] ?: 'দেওয়া হয়নি') ?><?= $cart['customer_district'] ? ', ' . h($cart['customer_district']) : '' ?></p>
                <?php else: ?>
                    <p class="mb-1 text-muted"><strong>নাম:</strong> Guest (অনিবন্ধিত - চেকআউটে কোনো তথ্য দেয়নি)</p>
                    <p class="mb-1 text-muted"><strong>সেশন আইডি:</strong> <?= h($cart['session_id']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="fw-bold mb-0">অ্যাকশন ও ফলো-আপ</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/admin/incomplete_orders/update.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="cart_id" value="<?= $cart['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label fw-medium">স্ট্যাটাস</label>
                        <select name="status" class="form-select">
                            <option value="new" <?= $cart['status'] === 'new' ? 'selected' : '' ?>>New</option>
                            <option value="contacted" <?= $cart['status'] === 'contacted' ? 'selected' : '' ?>>Contacted</option>
                            <option value="follow_up" <?= $cart['status'] === 'follow_up' ? 'selected' : '' ?>>Follow Up</option>
                            <option value="recovered" <?= $cart['status'] === 'recovered' ? 'selected' : '' ?>>Recovered</option>
                            <option value="lost" <?= $cart['status'] === 'lost' ? 'selected' : '' ?>>Lost</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">ফলো-আপ নোট</label>
                        <textarea name="notes" class="form-control" rows="4"><?= h($cart['notes']) ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save me-1"></i> আপডেট করুন</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
