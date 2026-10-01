<?php
// ============================================================
// FarmersBD — Admin Orders Management (Listing)
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/flash.php';
require_once $base . '/includes/pagination.php';

require_admin();
$pdo = get_db_connection();

$status_filter = sanitize_input($_GET['status'] ?? '');
$search = sanitize_input($_GET['search'] ?? '');
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if (!empty($status_filter)) {
    $where[] = "order_status = ?";
    $params[] = $status_filter;
}
if (!empty($search)) {
    $where[] = "(order_number LIKE ? OR customer_name LIKE ? OR customer_mobile LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM orders $where_clause");
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT * FROM orders $where_clause ORDER BY id DESC LIMIT ? OFFSET ?");
foreach ($params as $idx => $val) {
    $stmt->bindValue($idx + 1, $val);
}
$stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
$stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll();

$query_params = [];
if (!empty($status_filter)) $query_params['status'] = $status_filter;
if (!empty($search)) $query_params['search'] = $search;
$query_str = !empty($query_params) ? '?' . http_build_query($query_params) : '';

$pagination = paginate($total_records, $limit, $page, BASE_URL . '/admin/orders/index.php' . (!empty($query_params) ? '?' . http_build_query(array_diff_key($query_params, ['page'=>''])) : ''));

$page_title = "অর্ডার ব্যবস্থাপনা — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-bag-check-fill text-primary me-2"></i> গ্রাহকদের অনলাইন অর্ডারসমূহ</h3>
</div>

<?php display_flash(); ?>

<div class="row mb-3 g-2 align-items-center">
    <div class="col-md-6">
        <!-- Filter Tabs -->
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>/admin/orders/index.php" class="btn btn-sm <?= empty($status_filter) ? 'btn-primary' : 'btn-outline-secondary' ?>">সকল অর্ডার</a>
            <a href="<?= BASE_URL ?>/admin/orders/index.php?status=pending" class="btn btn-sm <?= $status_filter === 'pending' ? 'btn-warning' : 'btn-outline-secondary' ?>">Pending</a>
            <a href="<?= BASE_URL ?>/admin/orders/index.php?status=processing" class="btn btn-sm <?= $status_filter === 'processing' ? 'btn-info' : 'btn-outline-secondary' ?>">Processing</a>
            <a href="<?= BASE_URL ?>/admin/orders/index.php?status=shipped" class="btn btn-sm <?= $status_filter === 'shipped' ? 'btn-primary' : 'btn-outline-secondary' ?>">Shipped</a>
            <a href="<?= BASE_URL ?>/admin/orders/index.php?status=delivered" class="btn btn-sm <?= $status_filter === 'delivered' ? 'btn-success' : 'btn-outline-secondary' ?>">Delivered</a>
            <a href="<?= BASE_URL ?>/admin/orders/index.php?status=cancelled" class="btn btn-sm <?= $status_filter === 'cancelled' ? 'btn-danger' : 'btn-outline-secondary' ?>">Cancelled</a>
        </div>
    </div>
    <div class="col-md-6">
        <form action="<?= BASE_URL ?>/admin/orders/index.php" method="GET" class="d-flex gap-2 justify-content-md-end">
            <?php if (!empty($status_filter)): ?>
                <input type="hidden" name="status" value="<?= h($status_filter) ?>">
            <?php endif; ?>
            <input type="text" name="search" class="form-control form-control-sm w-auto" placeholder="অর্ডার নং বা নাম..." value="<?= h($search) ?>">
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i> খুঁজুন</button>
            <?php if (!empty($search)): ?>
                <a href="<?= BASE_URL ?>/admin/orders/index.php<?= !empty($status_filter) ? '?status='.h($status_filter) : '' ?>" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i></a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>অর্ডার নং</th>
                        <th>গ্রাহকের নাম ও ফোন</th>
                        <th>জেলা</th>
                        <th>পেমেন্ট</th>
                        <th>মোট মূল্য</th>
                        <th>পেমেন্ট স্ট্যাটাস</th>
                        <th>অর্ডার স্ট্যাটাস</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $ord): ?>
                        <tr>
                            <td class="fw-bold">#<?= h($ord['order_number']) ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= h($ord['customer_name']) ?></div>
                                <small class="text-muted"><i class="bi bi-telephone"></i> <?= h($ord['customer_mobile'] ?? $ord['customer_phone'] ?? '') ?></small>
                            </td>
                            <td><?= h($ord['district']) ?></td>
                            <td><?= strtoupper(h($ord['payment_method'])) ?></td>
                            <td class="fw-bold text-success"><?= format_price_bn($ord['total'] ?? $ord['grand_total'] ?? 0) ?></td>
                            <td><?= get_payment_status_badge($ord['payment_status']) ?></td>
                            <td><?= get_order_status_badge($ord['order_status']) ?></td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/admin/orders/view.php?id=<?= $ord['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> বিস্তারিত</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="p-3">
            <?= render_pagination($pagination) ?>
        </div>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
