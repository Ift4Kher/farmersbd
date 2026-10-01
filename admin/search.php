<?php
// ============================================================
// FarmersBD — Admin Global Universal Search
// ============================================================
$base = dirname(__DIR__);
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/flash.php';
require_once $base . '/includes/admin-auth.php';

require_admin();
$pdo = get_db_connection();

$q = sanitize_input($_GET['q'] ?? '');
$page_title = 'অনুসন্ধান ফলাফল — FarmersBD Admin';

$orders = [];
$products = [];
$users = [];
$leads = [];

if (!empty($q)) {
    $search_term = "%$q%";

    // Search Orders
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number LIKE ? OR customer_name LIKE ? OR customer_mobile LIKE ? ORDER BY id DESC LIMIT 10");
    $stmt->execute([$search_term, $search_term, $search_term]);
    $orders = $stmt->fetchAll();

    // Search Products
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN product_categories c ON p.category_id = c.id WHERE p.name LIKE ? OR p.sku LIKE ? ORDER BY p.id DESC LIMIT 10");
    $stmt->execute([$search_term, $search_term]);
    $products = $stmt->fetchAll();

    // Search Customers/Users
    $stmt = $pdo->prepare("SELECT * FROM users WHERE name LIKE ? OR phone LIKE ? OR email LIKE ? ORDER BY id DESC LIMIT 10");
    $stmt->execute([$search_term, $search_term, $search_term]);
    $users = $stmt->fetchAll();

    // Search Leads
    try {
        $stmt = $pdo->prepare("SELECT * FROM lead_recovery WHERE customer_name LIKE ? OR phone LIKE ? ORDER BY id DESC LIMIT 10");
        $stmt->execute([$search_term, $search_term]);
        $leads = $stmt->fetchAll();
    } catch (PDOException $e) {}
}

$total_found = count($orders) + count($products) + count($users) + count($leads);

include __DIR__ . '/includes/header.php';
?>

<div class="page-header mb-3">
    <div>
        <h3><i class="bi bi-search text-primary me-2"></i>অনুসন্ধান ফলাফল</h3>
        <p class="text-muted small mb-0">"<?= e($q) ?>" এর জন্য মোট <b><?= $total_found ?></b> টি ফলাফল পাওয়া গেছে</p>
    </div>
    <form action="<?= url('admin/search.php') ?>" method="GET" class="d-flex gap-2">
        <input type="text" name="q" class="form-control form-control-sm" style="width: 260px;" placeholder="পুনরায় খুঁজুন..." value="<?= e($q) ?>">
        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
    </form>
</div>

<?php if (empty($q)): ?>
    <div class="card p-4 text-center text-muted">
        <i class="bi bi-search fs-1 mb-2"></i>
        <div>অনুসন্ধানের জন্য কোনো কি-ওয়ার্ড লিখুন।</div>
    </div>
<?php elseif ($total_found === 0): ?>
    <div class="card p-4 text-center text-muted">
        <i class="bi bi-exclamation-circle fs-1 mb-2 text-warning"></i>
        <div>"<?= e($q) ?>" দিয়ে কোনো তথ্য পাওয়া যায়নি।</div>
    </div>
<?php else: ?>

    <!-- Orders Results -->
    <?php if (!empty($orders)): ?>
    <div class="card mb-3">
        <div class="card-header bg-white d-flex justify-content-between">
            <span class="fw-bold"><i class="bi bi-cart3 text-primary me-1"></i> অর্ডারসমূহ (<?= count($orders) ?>)</span>
            <a href="<?= url('admin/orders/?search='.urlencode($q)) ?>" class="small text-decoration-none">সব দেখুন →</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>অর্ডার নং</th>
                        <th>গ্রাহকের নাম</th>
                        <th>ফোন</th>
                        <th>মোট মূল্য</th>
                        <th>স্ট্যাটাস</th>
                        <th>অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td class="fw-bold text-primary"><?= e($o['order_number'] ?? '#'.$o['id']) ?></td>
                        <td><?= e($o['customer_name'] ?? '—') ?></td>
                        <td><?= e($o['customer_mobile'] ?? '—') ?></td>
                        <td class="fw-bold">৳<?= format_price_bn((float)($o['total'] ?? $o['grand_total'] ?? 0)) ?></td>
                        <td><span class="badge bg-secondary"><?= e($o['order_status'] ?? 'pending') ?></span></td>
                        <td>
                            <a href="<?= url('admin/orders/view.php?id='.(int)$o['id']) ?>" class="btn btn-sm btn-light border"><i class="bi bi-eye"></i> দেখুন</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Products Results -->
    <?php if (!empty($products)): ?>
    <div class="card mb-3">
        <div class="card-header bg-white d-flex justify-content-between">
            <span class="fw-bold"><i class="bi bi-box-seam text-success me-1"></i> পণ্যসমূহ (<?= count($products) ?>)</span>
            <a href="<?= url('admin/products/?search='.urlencode($q)) ?>" class="small text-decoration-none">সব দেখুন →</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>নাম</th>
                        <th>ক্যাটাগরি</th>
                        <th>SKU</th>
                        <th>মূল্য</th>
                        <th>স্টক</th>
                        <th>অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td class="fw-bold"><?= e($p['name']) ?></td>
                        <td><?= e($p['category_name'] ?? '—') ?></td>
                        <td><code><?= e($p['sku'] ?? '—') ?></code></td>
                        <td class="fw-bold text-success">৳<?= format_price_bn((float)$p['price']) ?></td>
                        <td><?= format_number_bn((int)($p['stock'] ?? 0)) ?></td>
                        <td>
                            <a href="<?= url('admin/products/edit.php?id='.(int)$p['id']) ?>" class="btn btn-sm btn-light border"><i class="bi bi-pencil"></i> এডিট</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Customers Results -->
    <?php if (!empty($users)): ?>
    <div class="card mb-3">
        <div class="card-header bg-white d-flex justify-content-between">
            <span class="fw-bold"><i class="bi bi-people text-info me-1"></i> গ্রাহকগণ (<?= count($users) ?>)</span>
            <a href="<?= url('admin/users/?search='.urlencode($q)) ?>" class="small text-decoration-none">সব দেখুন →</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>নাম</th>
                        <th>ফোন</th>
                        <th>ইমেইল</th>
                        <th>অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="fw-bold"><?= e($u['name']) ?></td>
                        <td><?= e($u['phone'] ?? '—') ?></td>
                        <td><?= e($u['email'] ?? '—') ?></td>
                        <td>
                            <a href="<?= url('admin/users/view.php?id='.(int)$u['id']) ?>" class="btn btn-sm btn-light border"><i class="bi bi-eye"></i> বিস্তারিত</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Leads Results -->
    <?php if (!empty($leads)): ?>
    <div class="card mb-3">
        <div class="card-header bg-white d-flex justify-content-between">
            <span class="fw-bold"><i class="bi bi-arrow-repeat text-warning me-1"></i> লিড রিকভারি (<?= count($leads) ?>)</span>
            <a href="<?= url('admin/lead-recovery/?q='.urlencode($q)) ?>" class="small text-decoration-none">সব দেখুন →</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>নাম</th>
                        <th>ফোন</th>
                        <th>স্ট্যাটাস</th>
                        <th>অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leads as $l): ?>
                    <tr>
                        <td class="fw-bold"><?= e($l['customer_name'] ?? '—') ?></td>
                        <td><?= e($l['phone'] ?? '—') ?></td>
                        <td><span class="badge bg-primary"><?= e($l['status'] ?? 'new') ?></span></td>
                        <td>
                            <a href="<?= url('admin/lead-recovery/') ?>" class="btn btn-sm btn-light border"><i class="bi bi-telephone"></i> যোগাযোগ</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
