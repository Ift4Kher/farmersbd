<?php
// ============================================================
// FarmersBD — Admin Static CMS Pages Management (Index)
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
    $where[] = "(title LIKE ? OR slug LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status !== '') {
    $where[] = "is_active = ?";
    $params[] = (int)$status;
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM pages $where_sql");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();

$sql = "SELECT * FROM pages $where_sql ORDER BY sort_order ASC, id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pages = $stmt->fetchAll();

$page_title = 'স্ট্যাটিক পেজসমূহ | FarmersBD Admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-file-earmark-text text-primary me-2"></i>স্ট্যাটিক পেজসমূহ (CMS Pages)</h1>
            <p class="text-muted small mb-0">আমাদের সম্পর্কে, শর্তাবলী, গোপনীয়তা নীতি, ডেলিভারি পলিসি ও অন্যান্য পেজ পরিচালনা করুন</p>
        </div>
        <a href="<?= url('admin/pages/add.php') ?>" class="btn btn-primary px-3 shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> নতুন পেজ তৈরি করুন
        </a>
    </div>
</div>

<!-- Search & Filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="পেজের শিরোনাম বা স্লাগ খুঁজুন..." value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">সকল স্ট্যাটাস</option>
                    <option value="1" <?= $status === '1' ? 'selected' : '' ?>>সক্রিয় (Active)</option>
                    <option value="0" <?= $status === '0' ? 'selected' : '' ?>>ড্রাফট (Draft)</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i> ফিল্টার</button>
                <?php if ($search !== '' || $status !== ''): ?>
                    <a href="<?= url('admin/pages/') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">পেজের শিরোনাম</th>
                        <th>URL লিংক (Slug)</th>
                        <th>ক্রম নম্বর</th>
                        <th>স্ট্যাটাস</th>
                        <th>সর্বশেষ আপডেট</th>
                        <th class="text-end pe-3">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pages)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-file-earmark-text display-5 d-block text-secondary mb-2 opacity-50"></i>
                                কোনো পেজ পাওয়া যায়নি।
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pages as $p): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark"><?= e($p['title']) ?></div>
                                    <div class="small text-muted"><?= e($p['meta_title'] ?: '') ?></div>
                                </td>
                                <td>
                                    <a href="<?= url('page/' . $p['slug']) ?>" target="_blank" class="badge bg-light text-primary border font-monospace text-decoration-none">
                                        /page/<?= e($p['slug']) ?> <i class="bi bi-box-arrow-up-right ms-1"></i>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary font-monospace"><?= $p['sort_order'] ?></span>
                                </td>
                                <td>
                                    <?php if ($p['is_active']): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">সক্রিয়</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">ড্রাফট</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small">
                                    <?= date('d M, Y', strtotime($p['updated_at'] ?: $p['created_at'])) ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('page/' . $p['slug']) ?>" target="_blank" class="btn btn-outline-secondary" title="প্রিভিউ">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= url('admin/pages/edit.php?id=' . $p['id']) ?>" class="btn btn-outline-primary" title="সম্পাদনা">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= url('admin/pages/delete.php?id=' . $p['id'] . '&csrf_token=' . csrf_generate()) ?>" class="btn btn-outline-danger" title="মুছে ফেলুন" onclick="return confirm('আপনি কি নিশ্চিত যে এই পেজটি মুছে ফেলতে চান?');">
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
            <?= render_pagination($page, ceil($total_rows / $limit), url('admin/pages/?q=' . urlencode($search) . '&status=' . urlencode($status))) ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
