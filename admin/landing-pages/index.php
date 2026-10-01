<?php
// ============================================================
// FarmersBD — Admin Landing Pages Management (Index)
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
    $where[] = "(title LIKE ? OR slug LIKE ? OR hero_heading LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status !== '') {
    $where[] = "is_active = ?";
    $params[] = (int)$status;
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM landing_pages $where_sql");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();

$sql = "SELECT * FROM landing_pages $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pages = $stmt->fetchAll();

$page_title = 'ল্যান্ডিং পেজ | FarmersBD Admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-layout-sidebar text-primary me-2"></i>ল্যান্ডিং পেজসমূহ</h1>
            <p class="text-muted small mb-0">বিশেষ ক্যাম্পেইন, ফেসবুক অ্যাড এবং পণ্যের রূপান্তর বৃদ্ধির জন্য ল্যান্ডিং পেজ তৈরি করুন</p>
        </div>
        <a href="<?= url('admin/landing-pages/add.php') ?>" class="btn btn-primary px-3 shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> নতুন ল্যান্ডিং পেজ তৈরি করুন
        </a>
    </div>
</div>

<!-- Search Bar -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="পেজের শিরোনাম বা স্লাগ..." value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">সকল স্ট্যাটাস</option>
                    <option value="1" <?= $status === '1' ? 'selected' : '' ?>>প্রকাশিত (Active)</option>
                    <option value="0" <?= $status === '0' ? 'selected' : '' ?>>ড্রাফট (Draft)</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i> ফিল্টার</button>
                <?php if ($search !== '' || $status !== ''): ?>
                    <a href="<?= url('admin/landing-pages/') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
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
                        <th class="ps-3">ব্যানার</th>
                        <th>পেজের শিরোনাম</th>
                        <th>URL লিংক</th>
                        <th>কাউন্টডাউন টাইমার</th>
                        <th>স্ট্যাটাস</th>
                        <th>তৈরির তারিখ</th>
                        <th class="text-end pe-3">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pages)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-layout-sidebar display-5 d-block text-secondary mb-2 opacity-50"></i>
                                কোনো ল্যান্ডিং পেজ পাওয়া যায়নি।
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pages as $p): ?>
                            <tr>
                                <td class="ps-3">
                                    <?php if (!empty($p['hero_image'])): ?>
                                        <img src="<?= asset($p['hero_image']) ?>" alt="<?= e($p['title']) ?>" class="rounded object-fit-cover shadow-sm" width="60" height="40">
                                    <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width:60px; height:40px;">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= e($p['title']) ?></div>
                                    <?php if (!empty($p['hero_heading'])): ?>
                                        <div class="small text-muted"><?= e($p['hero_heading']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= url('landing/' . $p['slug']) ?>" target="_blank" class="badge bg-light text-primary border font-monospace text-decoration-none">
                                        /landing/<?= e($p['slug']) ?> <i class="bi bi-box-arrow-up-right ms-1"></i>
                                    </a>
                                </td>
                                <td>
                                    <?php if (!empty($p['countdown_at']) && strtotime($p['countdown_at']) > time()): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="bi bi-clock-history me-1"></i><?= date('d M, h:i A', strtotime($p['countdown_at'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">কোনো টাইমার নেই</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($p['is_active']): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">সক্রিয়</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">ড্রাফট</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small">
                                    <?= date('d M, Y', strtotime($p['created_at'])) ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('landing/' . $p['slug']) ?>" target="_blank" class="btn btn-outline-secondary" title="লাইভ দেখুন">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= url('admin/landing-pages/edit.php?id=' . $p['id']) ?>" class="btn btn-outline-primary" title="সম্পাদনা">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= url('admin/landing-pages/delete.php?id=' . $p['id'] . '&csrf_token=' . csrf_generate()) ?>" class="btn btn-outline-danger" title="মুছে ফেলুন" onclick="return confirm('আপনি কি নিশ্চিত যে এই ল্যান্ডিং পেজটি মুছে ফেলতে চান?');">
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
            <?= render_pagination($page, ceil($total_rows / $limit), url('admin/landing-pages/?q=' . urlencode($search) . '&status=' . urlencode($status))) ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
