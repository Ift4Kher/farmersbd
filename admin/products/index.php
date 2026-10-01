<?php
// ============================================================
// FarmersBD — Admin Products Management (Listing)
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

$search = sanitize_input($_GET['search'] ?? '');
$category_id = (int)($_GET['category_id'] ?? 0);
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$where = ["p.deleted_at IS NULL"];
$params = [];

if ($category_id > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $category_id;
}
if (!empty($search)) {
    $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM products p $where_clause");
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();

$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN product_categories c ON p.category_id = c.id 
        $where_clause 
        ORDER BY p.id DESC LIMIT ? OFFSET ?";

$stmt = $pdo->prepare($sql);
foreach ($params as $idx => $val) {
    $stmt->bindValue($idx + 1, $val);
}
$stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
$stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM product_categories ORDER BY name ASC")->fetchAll();

$query_params = [];
if (!empty($search)) $query_params['search'] = $search;
if ($category_id > 0) $query_params['category_id'] = $category_id;
$pagination = paginate($total_records, $limit, $page, BASE_URL . '/admin/products/index.php' . (!empty($query_params) ? '?' . http_build_query(array_diff_key($query_params, ['page'=>''])) : ''));

$page_title = "পণ্য ও ঔষধ ব্যবস্থাপনা — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-bold text-dark mb-0"><i class="bi bi-box-seam text-primary me-2"></i> একুয়া প্রোডাক্ট ও মেডিসিন লিস্ট</h3>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <form action="<?= BASE_URL ?>/admin/products/index.php" method="GET" class="d-flex gap-2">
            <select name="category_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <option value="0">সকল ক্যাটাগরি</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $category_id == $c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="পণ্যের নাম বা SKU..." value="<?= h($search) ?>">
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
            <?php if (!empty($search) || $category_id > 0): ?>
                <a href="<?= BASE_URL ?>/admin/products/index.php" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </form>
        <a href="<?= BASE_URL ?>/admin/products/trash.php" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i> ট্র্যাশ</a>
        <a href="<?= BASE_URL ?>/admin/products/add.php" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i> নতুন পণ্য</a>
    </div>
</div>

<?php display_flash(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ছবি</th>
                        <th>পণ্যের নাম</th>
                        <th>ক্যাটাগরি</th>
                        <th>মূল্য</th>
                        <th>স্টক</th>
                        <th>স্ট্যাটাস</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td>
                                <img src="<?= !empty($p['image']) ? get_upload_url($p['image'], 'products') : BASE_URL . '/assets/images/product-placeholder.png' ?>" class="rounded" style="width:50px; height:50px; object-fit:cover;" alt="">
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= h($p['name'] ?? $p['name_bn'] ?? '') ?></div>
                                <small class="text-muted"><?= h($p['sku'] ?? '') ?></small>
                            </td>
                            <td><span class="badge bg-secondary"><?= h($p['category_name'] ?? 'সাধারণ') ?></span></td>
                            <td class="fw-bold text-success"><?= format_price_bn($p['price']) ?></td>
                            <td>
                                <?php $stk = $p['stock'] ?? $p['stock_quantity'] ?? 0; ?>
                                <?php if ($stk <= 5): ?>
                                    <span class="badge bg-danger"><?= format_number_bn($stk) ?> টি (কম)</span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?= format_number_bn($stk) ?> টি</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= ($p['is_active'] ?? 1) == 1 ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= ($p['is_active'] ?? 1) == 1 ? 'ACTIVE' : 'INACTIVE' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/admin/products/edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                                <a href="<?= BASE_URL ?>/admin/products/delete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('আপনি কি নিশ্চিত যে এই প্রোডাক্ট মুছে ফেলতে চান?');"><i class="bi bi-trash"></i></a>
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
