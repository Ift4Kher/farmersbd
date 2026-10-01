<?php
// ============================================================
// FarmersBD — Admin Manual Order Creation
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/csrf.php';
require_once $base . '/includes/flash.php';
require_once $base . '/includes/admin-auth.php';

require_admin();
$pdo = get_db_connection();

$products = $pdo->query("SELECT id, name, price, stock FROM products WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $customer_name   = sanitize_input($_POST['customer_name'] ?? '');
    $customer_mobile = sanitize_input($_POST['customer_mobile'] ?? '');
    $shipping_address= sanitize_input($_POST['shipping_address'] ?? '');
    $district        = sanitize_input($_POST['district'] ?? 'ঢাকা');
    $payment_method  = sanitize_input($_POST['payment_method'] ?? 'COD');
    $payment_status  = sanitize_input($_POST['payment_status'] ?? 'unpaid');
    $order_status    = sanitize_input($_POST['order_status'] ?? 'pending');
    $shipping_cost   = (float)($_POST['shipping_cost'] ?? 60);
    $notes           = sanitize_input($_POST['admin_notes'] ?? '');

    $product_ids     = $_POST['product_id'] ?? [];
    $quantities      = $_POST['quantity'] ?? [];
    $prices          = $_POST['price'] ?? [];

    if (empty($customer_name) || empty($customer_mobile) || empty($shipping_address)) {
        set_flash('error', 'গ্রাহকের নাম, মোবাইল নম্বর এবং ঠিকানা পূরণ করুন।');
    } elseif (empty($product_ids)) {
        set_flash('error', 'কমপক্ষে একটি পণ্য সিলেক্ট করুন।');
    } else {
        try {
            $pdo->beginTransaction();

            $subtotal = 0;
            $items_to_insert = [];

            foreach ($product_ids as $idx => $pid) {
                $pid = (int)$pid;
                $qty = max(1, (int)($quantities[$idx] ?? 1));
                $prc = (float)($prices[$idx] ?? 0);

                // Fetch product details if price empty
                if ($prc <= 0) {
                    $p_stmt = $pdo->prepare("SELECT name, price FROM products WHERE id = ?");
                    $p_stmt->execute([$pid]);
                    $p_info = $p_stmt->fetch();
                    $prc = (float)($p_info['price'] ?? 0);
                    $p_name = $p_info['name'] ?? 'পণ্য';
                } else {
                    $p_stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
                    $p_stmt->execute([$pid]);
                    $p_name = $p_stmt->fetchColumn() ?: 'পণ্য';
                }

                $item_total = $prc * $qty;
                $subtotal += $item_total;

                $items_to_insert[] = [
                    'product_id' => $pid,
                    'product_name' => $p_name,
                    'price' => $prc,
                    'quantity' => $qty,
                    'total' => $item_total
                ];
            }

            $total = $subtotal + $shipping_cost;
            $order_number = 'FB' . date('ymd') . rand(100, 999);

            $stmt = $pdo->prepare("INSERT INTO orders 
                (order_number, customer_name, customer_mobile, shipping_address, district, payment_method, payment_status, order_status, subtotal, shipping_cost, total, admin_notes, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            
            $stmt->execute([
                $order_number, $customer_name, $customer_mobile, $shipping_address, $district,
                $payment_method, $payment_status, $order_status, $subtotal, $shipping_cost, $total, $notes
            ]);

            $order_id = $pdo->lastInsertId();

            // Insert items
            $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, total) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($items_to_insert as $item) {
                $item_stmt->execute([
                    $order_id, $item['product_id'], $item['product_name'], $item['price'], $item['quantity'], $item['total']
                ]);

                // Reduce stock
                $pdo->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?")->execute([$item['quantity'], $item['product_id']]);
            }

            // Notification
            $pdo->prepare("INSERT INTO admin_notifications (type, message, link, is_read, created_at) VALUES ('order', ?, ?, 0, NOW())")
                ->execute(["নতুন ম্যানুয়াল অর্ডার তৈরি করা হয়েছে: #{$order_number}", "admin/orders/view.php?id={$order_id}"]);

            $pdo->commit();
            set_flash('success', "অর্ডার সফলভাবে তৈরি হয়েছে! অর্ডার নং: #{$order_number}");
            redirect(BASE_URL . '/admin/orders/view.php?id=' . $order_id);
        } catch (Exception $e) {
            $pdo->rollBack();
            set_flash('error', 'অর্ডার তৈরিতে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }
}

$page_title = 'নতুন অর্ডার তৈরি করুন — FarmersBD Admin';
include dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header mb-3">
    <div>
        <h3><i class="bi bi-plus-circle-fill text-primary me-2"></i>ম্যানুয়াল নতুন অর্ডার তৈরি</h3>
        <p class="text-muted small mb-0">গ্রাহকের পক্ষ থেকে সরাসরি অ্যাডমিন থেকে অর্ডার অ্যান্ট্রি করুন</p>
    </div>
    <a href="<?= url('admin/orders/') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> সকল অর্ডার
    </a>
</div>

<form action="<?= url('admin/orders/create.php') ?>" method="POST" class="card shadow-sm border-0">
    <?= csrf_field() ?>
    <div class="card-body p-3">
        <div class="row g-3">
            <!-- Customer Info -->
            <div class="col-md-6 border-end">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-person text-primary me-1"></i> গ্রাহকের তথ্য</h6>
                
                <div class="mb-2">
                    <label class="form-label">গ্রাহকের নাম <span class="text-danger">*</span></label>
                    <input type="text" name="customer_name" class="form-control form-control-sm" required placeholder="সম্পূর্ণ নাম">
                </div>

                <div class="mb-2">
                    <label class="form-label">মোবাইল নম্বর <span class="text-danger">*</span></label>
                    <input type="text" name="customer_mobile" class="form-control form-control-sm" required placeholder="017XXXXXXXX">
                </div>

                <div class="mb-2">
                    <label class="form-label">ডেলিভারি ঠিকানা <span class="text-danger">*</span></label>
                    <textarea name="shipping_address" class="form-control form-control-sm" rows="2" required placeholder="বাসা/গ্রাম, রাস্তা, থানা"></textarea>
                </div>

                <div class="row g-2">
                    <div class="col-6 mb-2">
                        <label class="form-label">জেলা</label>
                        <select name="district" class="form-select form-select-sm">
                            <option value="ঢাকা">ঢাকা</option>
                            <option value="চট্টগ্রাম">চট্টগ্রাম</option>
                            <option value="রাজশাহী">রাজশাহী</option>
                            <option value="খুলনা">খুলনা</option>
                            <option value="বরিশাল">বরিশাল</option>
                            <option value="সিলেট">সিলেট</option>
                            <option value="রংপুর">রংপুর</option>
                            <option value="ময়মনসিংহ">ময়মনসিংহ</option>
                        </select>
                    </div>
                    <div class="col-6 mb-2">
                        <label class="form-label">ডেলিভারি চার্জ (৳)</label>
                        <input type="number" name="shipping_cost" class="form-control form-control-sm" value="60" id="shippingCost">
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-6 mb-2">
                        <label class="form-label">পেমেন্ট মেথড</label>
                        <select name="payment_method" class="form-select form-select-sm">
                            <option value="COD">ক্যাশ অন ডেলিভারি (COD)</option>
                            <option value="bKash">বিকাশ (bKash)</option>
                            <option value="Nagad">নগদ (Nagad)</option>
                            <option value="Bank">ব্যাংক ট্রান্সফার</option>
                        </select>
                    </div>
                    <div class="col-6 mb-2">
                        <label class="form-label">পেমেন্ট স্ট্যাটাস</label>
                        <select name="payment_status" class="form-select form-select-sm">
                            <option value="unpaid">বাকি (Unpaid)</option>
                            <option value="paid">পরিশোধিত (Paid)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label">অর্ডার স্ট্যাটাস</label>
                    <select name="order_status" class="form-select form-select-sm">
                        <option value="confirmed">কনফার্মড (Confirmed)</option>
                        <option value="pending">পেন্ডিং (Pending)</option>
                        <option value="processing">প্রসেসিং (Processing)</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">অ্যাডমিন নোট (ঐচ্ছিক)</label>
                    <input type="text" name="admin_notes" class="form-control form-control-sm" placeholder="ফোন কল বা স্পেশাল ইন্সট্রাকশন">
                </div>
            </div>

            <!-- Product Selection -->
            <div class="col-md-6">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-box-seam text-success me-1"></i> পণ্যের তালিকা</h6>
                
                <div id="productRowsContainer">
                    <div class="product-item-row p-2 border rounded bg-light mb-2">
                        <div class="row g-2 align-items-center">
                            <div class="col-7">
                                <label class="form-label small mb-1">পণ্য সিলেক্ট করুন</label>
                                <select name="product_id[]" class="form-select form-select-sm prod-select" onchange="updatePrice(this)" required>
                                    <option value="">-- পণ্য বাছুন --</option>
                                    <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>" data-stock="<?= $p['stock'] ?>">
                                        <?= e($p['name']) ?> (স্টক: <?= $p['stock'] ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-2">
                                <label class="form-label small mb-1">পরিমাণ</label>
                                <input type="number" name="quantity[]" class="form-control form-control-sm prod-qty" value="1" min="1" onchange="calcTotal()">
                            </div>
                            <div class="col-3">
                                <label class="form-label small mb-1">মূল্য (৳)</label>
                                <input type="number" name="price[]" class="form-control form-control-sm prod-price" value="0" step="0.01" onchange="calcTotal()">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-outline-primary mb-3" onclick="addProductRow()">
                    <i class="bi bi-plus-lg"></i> আরও পণ্য যোগ করুন
                </button>

                <div class="p-3 bg-light rounded border">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">সাবটোটাল:</span>
                        <span class="fw-bold" id="subtotalDisplay">৳ 0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">ডেলিভারি চার্জ:</span>
                        <span class="fw-bold" id="shippingDisplay">৳ 60</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fs-6">
                        <span class="fw-bold text-dark">সর্বমোট প্রদেয়:</span>
                        <span class="fw-bold text-success fs-5" id="grandTotalDisplay">৳ 60</span>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100 mt-3 py-2 fw-bold">
                    <i class="bi bi-check-circle-fill me-1"></i> অর্ডার কনফার্ম ও সংরক্ষণ করুন
                </button>
            </div>
        </div>
    </div>
</form>

<script>
function updatePrice(selectElem) {
    const row = selectElem.closest('.product-item-row');
    const selected = selectElem.options[selectElem.selectedIndex];
    const price = selected.getAttribute('data-price') || 0;
    row.querySelector('.prod-price').value = price;
    calcTotal();
}

function calcTotal() {
    let subtotal = 0;
    document.querySelectorAll('.product-item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.prod-qty').value) || 0;
        const price = parseFloat(row.querySelector('.prod-price').value) || 0;
        subtotal += (qty * price);
    });

    const shipping = parseFloat(document.getElementById('shippingCost').value) || 0;
    const grand = subtotal + shipping;

    document.getElementById('subtotalDisplay').textContent = '৳ ' + subtotal.toLocaleString();
    document.getElementById('shippingDisplay').textContent = '৳ ' + shipping.toLocaleString();
    document.getElementById('grandTotalDisplay').textContent = '৳ ' + grand.toLocaleString();
}

function addProductRow() {
    const container = document.getElementById('productRowsContainer');
    const firstRow = container.querySelector('.product-item-row');
    const clone = firstRow.cloneNode(true);
    clone.querySelector('.prod-qty').value = 1;
    clone.querySelector('.prod-price').value = 0;
    clone.querySelector('.prod-select').selectedIndex = 0;
    container.appendChild(clone);
}

document.getElementById('shippingCost').addEventListener('input', calcTotal);
</script>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
