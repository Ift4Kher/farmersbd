<?php
// ============================================================
// FarmersBD — Admin Combos Management (Index)
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/csrf.php';
require_once $base . '/includes/flash.php';
require_once $base . '/includes/pagination.php';

require_admin();
$pdo = get_db_connection();

$search = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 15;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(c.name LIKE ? OR c.name_bn LIKE ? OR c.slug LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status !== '') {
    $where[] = "c.is_active = ?";
    $params[] = (int)$status;
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM combos c $where_sql");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();

$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM combo_items ci WHERE ci.combo_id = c.id) AS total_items,
        (SELECT SUM(ci.quantity * p.regular_price) FROM combo_items ci JOIN products p ON p.id = ci.product_id WHERE ci.combo_id = c.id) AS calculated_regular_price
        FROM combos c 
        $where_sql 
        ORDER BY c.id DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$combos = $stmt->fetchAll();

$page_title = 'কম্বো প্যাকেজ | FarmersBD Admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-collection text-primary me-2"></i>কম্বো অফার ও প্যাকেজ</h1>
            <p class="text-muted small mb-0">বিশেষ ছাড়ের জন্য একাধিক পণ্যের আকর্ষণীয় কম্বো প্যাকেজ তৈরি ও পরিচালনা করুন</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('admin/combos/add.php') ?>" class="btn btn-primary px-3 shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> নতুন কম্বো তৈরি করুন
            </a>
        </div>
    </div>
</div>

<!-- Search and Filter Bar -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="কম্বোর নাম দিয়ে খুঁজুন..." value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">সকল স্ট্যাটাস</option>
                    <option value="1" <?= $status === '1' ? 'selected' : '' ?>>সক্রিয় (Active)</option>
                    <option value="0" <?= $status === '0' ? 'selected' : '' ?>>নিষ্ক্রিয় (Inactive)</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i> ফিল্টার</button>
                <?php if ($search !== '' || $status !== ''): ?>
                    <a href="<?= url('admin/combos/') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Combos Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:60px;">ছবি</th>
                        <th>কম্বো নাম</th>
                        <th>পণ্য সংখ্যা</th>
                        <th>মূল্য ও ছাড়</th>
                        <th>স্ট্যাটাস</th>
                        <th>তৈরির তারিখ</th>
                        <th class="text-end pe-3">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($combos)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-collection display-5 d-block text-secondary mb-2 opacity-50"></i>
                                কোনো কম্বো প্যাকেজ পাওয়া যায়নি।
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($combos as $c): ?>
                            <tr>
                                <td class="ps-3">
                                    <?php if (!empty($c['image'])): ?>
                                        <img src="<?= asset($c['image']) ?>" alt="<?= e($c['name']) ?>" class="rounded object-fit-cover shadow-sm" width="50" height="50">
                                    <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width:50px; height:50px;">
                                            <i class="bi bi-image fs-5"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= e($c['name_bn'] ?: $c['name']) ?></div>
                                    <div class="small text-muted"><?= e($c['name']) ?></div>
                                    <span class="badge bg-light text-secondary border font-monospace mt-1">/combo/<?= e($c['slug']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info fw-semibold">
                                        <i class="bi bi-boxes me-1"></i> <?= (int)$c['total_items'] ?> টি পণ্য
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-success fs-6"><?= format_price($c['price']) ?></div>
                                    <?php if (!empty($c['regular_price']) && $c['regular_price'] > $c['price']): ?>
                                        <del class="text-muted small"><?= format_price($c['regular_price']) ?></del>
                                        <span class="badge bg-danger-subtle text-danger small ms-1">
                                            -<?= round((($c['regular_price'] - $c['price']) / $c['regular_price']) * 100) ?>%
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($c['is_active']): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">সক্রিয়</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">নিষ্ক্রিয়</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small">
                                    <?= date('d M, Y', strtotime($c['created_at'])) ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('combo/' . $c['slug']) ?>" target="_blank" class="btn btn-outline-secondary" title="প্রিভিউ">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= url('admin/combos/edit.php?id=' . $c['id']) ?>" class="btn btn-outline-primary" title="সম্পাদনা">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= url('admin/combos/delete.php?id=' . $c['id'] . '&csrf_token=' . csrf_generate()) ?>" class="btn btn-outline-danger" title="মুছে ফেলুন" onclick="return confirm('আপনি কি নিশ্চিত যে এই কম্বোটি মুছে ফেলতে চান?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($total_rows > $limit): ?>
        <div class="card-footer bg-white border-0 py-3">
            <?= render_pagination($page, ceil($total_rows / $limit), url('admin/combos/?q=' . urlencode($search) . '&status=' . urlencode($status))) ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
