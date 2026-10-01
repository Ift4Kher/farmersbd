<?php
// ============================================================
// FarmersBD — Admin Incomplete Orders
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/flash.php';
require_once $base . '/includes/pagination.php';
require_once $base . '/includes/csrf.php';

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
    $where[] = "c.status = ?";
    $params[] = $status_filter;
}
if (!empty($search)) {
    $where[] = "(u.name LIKE ? OR u.mobile LIKE ? OR u.email LIKE ? OR c.session_id LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where[] = "(SELECT COUNT(*) FROM cart_items ci WHERE ci.cart_id = c.id) > 0";
$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM carts c LEFT JOIN users u ON c.user_id = u.id $where_clause");
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();

// We need cart total and item count, so we use subqueries
$sql = "SELECT c.*, 
        COALESCE(c.customer_name, u.name) as customer_name, 
        COALESCE(c.customer_mobile, u.mobile) as customer_mobile, 
        COALESCE(c.customer_email, u.email) as customer_email,
        (SELECT COUNT(*) FROM cart_items ci WHERE ci.cart_id = c.id) as item_count,
        (SELECT SUM(ci.quantity * p.price) FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.cart_id = c.id) as cart_total
        FROM carts c 
        LEFT JOIN users u ON c.user_id = u.id 
        $where_clause 
        ORDER BY c.updated_at DESC 
        LIMIT ? OFFSET ?";

$stmt = $pdo->prepare($sql);
foreach ($params as $idx => $val) {
    $stmt->bindValue($idx + 1, $val);
}
$stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
$stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
$stmt->execute();
$carts = $stmt->fetchAll();

$query_params = [];
if (!empty($status_filter)) $query_params['status'] = $status_filter;
if (!empty($search)) $query_params['search'] = $search;
$pagination = paginate($total_records, $limit, $page, BASE_URL . '/admin/incomplete_orders/index.php' . (!empty($query_params) ? '?' . http_build_query(array_diff_key($query_params, ['page'=>''])) : ''));

$page_title = "অসম্পূর্ণ অর্ডার — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-cart-x text-warning me-2"></i> অসম্পূর্ণ অর্ডারসমূহ</h3>
</div>

<?php
// Calculate Summary Data
$summary_sql = "
    SELECT 
        status, 
        COUNT(c.id) as total_count
    FROM carts c
    WHERE (SELECT COUNT(*) FROM cart_items ci WHERE ci.cart_id = c.id) > 0
    GROUP BY status
";
$summary_stmt = $pdo->query($summary_sql);
$summary_data = $summary_stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?? []; // [status => total_count]

// For total value, we need a separate query since fetchAll(PDO::FETCH_KEY_PAIR) only gets 2 columns
$val_stmt = $pdo->query("SELECT status, SUM(
                            (SELECT SUM(ci.quantity * p.price) FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.cart_id = c.id)
                         ) as val FROM carts c WHERE (SELECT COUNT(*) FROM cart_items ci WHERE ci.cart_id = c.id) > 0 GROUP BY status");
$val_data = [];
foreach ($val_stmt as $row) {
    $val_data[$row['status']] = $row['val'];
}

$stat = function($s) use ($summary_data) { return (int)($summary_data[$s] ?? 0); };
$total_incomplete_value = ($val_data['new'] ?? 0) + ($val_data['contacted'] ?? 0) + ($val_data['follow_up'] ?? 0);
$total_recovered_value = $val_data['recovered'] ?? 0;
?>

<div class="row g-3 mb-4">
    <div class="col-md-2 col-6">
        <div class="card bg-warning text-dark border-0 h-100">
            <div class="card-body p-3 text-center">
                <h6 class="mb-1">New</h6>
                <h3 class="fw-bold mb-0"><?= format_number_bn($stat('new')) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card bg-info text-white border-0 h-100">
            <div class="card-body p-3 text-center">
                <h6 class="mb-1">Contacted</h6>
                <h3 class="fw-bold mb-0"><?= format_number_bn($stat('contacted')) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card bg-primary text-white border-0 h-100">
            <div class="card-body p-3 text-center">
                <h6 class="mb-1">Follow Up</h6>
                <h3 class="fw-bold mb-0"><?= format_number_bn($stat('follow_up')) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card bg-success text-white border-0 h-100">
            <div class="card-body p-3 text-center">
                <h6 class="mb-1">Recovered (<?= format_number_bn($stat('recovered')) ?>)</h6>
                <h4 class="fw-bold mb-0"><?= format_price_bn($total_recovered_value) ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card bg-danger text-white border-0 h-100">
            <div class="card-body p-3 text-center">
                <h6 class="mb-1">Lost (<?= format_number_bn($stat('lost')) ?>)</h6>
                <h4 class="fw-bold mb-0"><?= format_price_bn($val_data['lost'] ?? 0) ?></h4>
            </div>
        </div>
    </div>
    <div class="col-12 mt-2">
        <div class="alert alert-secondary mb-0 p-2 text-center">
            <strong>মোট অসম্পূর্ণ কার্ট মূল্য (Active): </strong> <?= format_price_bn($total_incomplete_value) ?>
        </div>
    </div>
</div>

<?php display_flash(); ?>

<div class="row mb-3 g-2 align-items-center">
    <div class="col-md-7">
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>/admin/incomplete_orders/index.php" class="btn btn-sm <?= empty($status_filter) ? 'btn-primary' : 'btn-outline-secondary' ?>">সকল</a>
            <a href="<?= BASE_URL ?>/admin/incomplete_orders/index.php?status=new" class="btn btn-sm <?= $status_filter === 'new' ? 'btn-warning' : 'btn-outline-secondary' ?>">New</a>
            <a href="<?= BASE_URL ?>/admin/incomplete_orders/index.php?status=contacted" class="btn btn-sm <?= $status_filter === 'contacted' ? 'btn-info' : 'btn-outline-secondary' ?>">Contacted</a>
            <a href="<?= BASE_URL ?>/admin/incomplete_orders/index.php?status=follow_up" class="btn btn-sm <?= $status_filter === 'follow_up' ? 'btn-primary' : 'btn-outline-secondary' ?>">Follow Up</a>
            <a href="<?= BASE_URL ?>/admin/incomplete_orders/index.php?status=recovered" class="btn btn-sm <?= $status_filter === 'recovered' ? 'btn-success' : 'btn-outline-secondary' ?>">Recovered</a>
            <a href="<?= BASE_URL ?>/admin/incomplete_orders/index.php?status=lost" class="btn btn-sm <?= $status_filter === 'lost' ? 'btn-danger' : 'btn-outline-secondary' ?>">Lost</a>
        </div>
    </div>
    <div class="col-md-5">
        <form action="<?= BASE_URL ?>/admin/incomplete_orders/index.php" method="GET" class="d-flex gap-2 justify-content-md-end">
            <?php if (!empty($status_filter)): ?>
                <input type="hidden" name="status" value="<?= h($status_filter) ?>">
            <?php endif; ?>
            <input type="text" name="search" class="form-control form-control-sm w-auto" placeholder="নাম বা ফোন..." value="<?= h($search) ?>">
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i> খুঁজুন</button>
            <?php if (!empty($search)): ?>
                <a href="<?= BASE_URL ?>/admin/incomplete_orders/index.php<?= !empty($status_filter) ? '?status='.h($status_filter) : '' ?>" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i></a>
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
                        <th>গ্রাহকের তথ্য</th>
                        <th>কার্ট আইটেম</th>
                        <th>সর্বমোট মূল্য</th>
                        <th>সর্বশেষ আপডেট</th>
                        <th>স্ট্যাটাস</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($carts)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">কোনো তথ্য পাওয়া যায়নি।</td></tr>
                    <?php else: ?>
                        <?php foreach ($carts as $c): ?>
                            <tr>
                                <td>
                                    <?php if ($c['customer_name']): ?>
                                        <div class="fw-bold text-dark">
                                            <?= h($c['customer_name']) ?>
                                            <?php if (!$c['user_id']): ?> <span class="badge bg-secondary ms-1" style="font-size:0.7em">Guest</span> <?php endif; ?>
                                        </div>
                                        <small class="text-muted"><i class="bi bi-telephone"></i> <?= h($c['customer_mobile'] ?? 'N/A') ?></small>
                                    <?php else: ?>
                                        <div class="fw-bold text-muted">Guest (অনিবন্ধিত)</div>
                                        <small class="text-muted"><i class="bi bi-hash"></i> <?= mb_substr($c['session_id'], 0, 8) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-secondary"><?= (int)$c['item_count'] ?> টি পণ্য</span></td>
                                <td class="fw-bold text-success"><?= format_price_bn($c['cart_total'] ?? 0) ?></td>
                                <td><small class="text-muted"><?= format_date_bn($c['updated_at']) ?></small></td>
                                <td>
                                    <?php
                                    $badgeClass = [
                                        'new' => 'bg-warning text-dark',
                                        'contacted' => 'bg-info',
                                        'follow_up' => 'bg-primary',
                                        'recovered' => 'bg-success',
                                        'lost' => 'bg-danger'
                                    ][$c['status']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($c['status']) ?></span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="<?= BASE_URL ?>/admin/incomplete_orders/view.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> বিস্তারিত</a>
                                        <form action="<?= BASE_URL ?>/admin/incomplete_orders/delete.php" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে আপনি এই অসম্পূর্ণ অর্ডারটি মুছে ফেলতে চান?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> মুছুন</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="p-3">
            <?= render_pagination($pagination) ?>
        </div>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
