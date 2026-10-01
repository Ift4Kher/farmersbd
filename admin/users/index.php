<?php
// ============================================================
// FarmersBD — Admin Users Management
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/pagination.php';

require_admin();
$pdo = get_db_connection();

$search = sanitize_input($_GET['search'] ?? '');
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(name LIKE ? OR mobile LIKE ? OR email LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM users $where_clause");
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();

// Fetch users with their order count and total spend
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) as order_count,
        (SELECT SUM(total) FROM orders o WHERE o.user_id = u.id AND o.payment_status = 'paid') as total_spent
        FROM users u 
        $where_clause 
        ORDER BY u.created_at DESC LIMIT ? OFFSET ?";

$stmt = $pdo->prepare($sql);
foreach ($params as $idx => $val) {
    $stmt->bindValue($idx + 1, $val);
}
$stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
$stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();

$query_params = [];
if (!empty($search)) $query_params['search'] = $search;
$pagination = paginate($total_records, $limit, $page, BASE_URL . '/admin/users/index.php' . (!empty($query_params) ? '?' . http_build_query(array_diff_key($query_params, ['page'=>''])) : ''));

$page_title = "গ্রাহক ব্যবস্থাপনা — এডমিন প্যানেল";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-bold text-dark mb-0"><i class="bi bi-people-fill text-primary me-2"></i> নিবন্ধিত গ্রাহক তালিকা</h3>
        <span class="badge bg-primary fs-6 mt-2">মোট: <?= format_number_bn($total_records) ?> জন</span>
    </div>
    <form action="<?= BASE_URL ?>/admin/users/index.php" method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="নাম, ইমেইল বা মোবাইল..." value="<?= h($search) ?>">
        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
        <?php if (!empty($search)): ?>
            <a href="<?= BASE_URL ?>/admin/users/index.php" class="btn btn-outline-danger"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
    </form>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>আইডি</th>
                        <th>নাম</th>
                        <th>মোবাইল ও ইমেইল</th>
                        <th>জেলা</th>
                        <th>অর্ডার / খরচ</th>
                        <th>নিবন্ধন তারিখ</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">কোনো গ্রাহক পাওয়া যায়নি।</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="fw-bold">#<?= $u['id'] ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= h($u['name']) ?></div>
                                    <span class="badge <?= $u['is_active'] ? 'bg-success' : 'bg-danger' ?>"><?= $u['is_active'] ? 'সক্রিয়' : 'নিষ্ক্রিয়' ?></span>
                                </td>
                                <td>
                                    <div><i class="bi bi-telephone text-muted"></i> <?= h($u['mobile']) ?></div>
                                    <div class="small"><i class="bi bi-envelope text-muted"></i> <?= h($u['email'] ?? 'N/A') ?></div>
                                </td>
                                <td><?= h($u['district'] ?? 'N/A') ?></td>
                                <td>
                                    <div><span class="badge bg-info text-dark"><?= format_number_bn($u['order_count'] ?? 0) ?> টি অর্ডার</span></div>
                                    <div class="fw-bold text-success mt-1"><?= format_price_bn($u['total_spent'] ?? 0) ?></div>
                                </td>
                                <td class="small text-muted"><?= format_date_bn($u['created_at']) ?></td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/admin/users/view.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-person-lines-fill"></i> প্রোফাইল</a>
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
