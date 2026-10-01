<?php
// ============================================================
// FarmersBD — Admin Fraud & Blocklist Management
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

// Handle Actions (Add, Toggle, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verify_csrf();

    if ($_POST['action'] === 'add_block') {
        $type      = sanitize_input($_POST['type'] ?? 'phone');
        $value     = trim(sanitize_input($_POST['value'] ?? ''));
        $reason    = sanitize_input($_POST['reason'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($value)) {
            set_flash('error', 'ব্লক করার মান (ফোন/আইপি/ইমেইল) আবশ্যক।');
        } else {
            // Normalize phone if type is phone
            if ($type === 'phone') {
                $value = preg_replace('/[^0-9]/', '', $value);
            }

            // Check if already exists
            $check = $pdo->prepare("SELECT id FROM blocklist WHERE type = ? AND value = ?");
            $check->execute([$type, $value]);
            if ($check->fetch()) {
                set_flash('error', 'এই মানটি ইতিমধ্যেই ব্লক তালিকায় বিদ্যমান।');
            } else {
                $stmt = $pdo->prepare("INSERT INTO blocklist (type, value, reason, is_active, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$type, $value, $reason, $is_active]);
                set_flash('success', 'ব্লক তালিকায় সফলভাবে যুক্ত করা হয়েছে।');
            }
        }
        redirect(BASE_URL . '/admin/blocklist/');
    }

    if ($_POST['action'] === 'toggle_status') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("UPDATE blocklist SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
            set_flash('success', 'স্ট্যাটাস পরিবর্তন করা হয়েছে।');
        }
        redirect(BASE_URL . '/admin/blocklist/');
    }

    if ($_POST['action'] === 'delete_block') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM blocklist WHERE id = ?")->execute([$id]);
            set_flash('success', 'ব্লক তালিকা থেকে মুছে ফেলা হয়েছে।');
        }
        redirect(BASE_URL . '/admin/blocklist/');
    }
}

// Search & Filter
$search = trim($_GET['q'] ?? '');
$type   = trim($_GET['type'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 15;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(value LIKE ? OR reason LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($type !== '') {
    $where[] = "type = ?";
    $params[] = $type;
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM blocklist $where_sql");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();

$sql = "SELECT * FROM blocklist $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$blocked_items = $stmt->fetchAll();

// Stats
$stats_stmt = $pdo->query("SELECT 
    COUNT(*) as total_blocked,
    SUM(CASE WHEN type = 'phone' THEN 1 ELSE 0 END) as total_phones,
    SUM(CASE WHEN type = 'ip' THEN 1 ELSE 0 END) as total_ips,
    SUM(CASE WHEN type = 'email' THEN 1 ELSE 0 END) as total_emails
    FROM blocklist");
$stats = $stats_stmt->fetch();

$page_title = 'প্রতারণা প্রতিরোধ ও ব্লকিলিস্ট | FarmersBD Admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-shield-x text-danger me-2"></i>প্রতারণা প্রতিরোধ ও ব্লকিলিস্ট</h1>
            <p class="text-muted small mb-0">ভুয়া অর্ডার, রিটার্ন প্রতারণা ও সন্দেহভাজন গ্রাহক বা আইপি ব্লক করে অর্ডার সুরক্ষা নিশ্চিত করুন</p>
        </div>
        <button type="button" class="btn btn-danger px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addBlockModal">
            <i class="bi bi-plus-circle me-1"></i> নতুন ব্লকলিস্ট এন্ট্রি
        </button>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-danger-subtle text-danger p-3 fs-4"><i class="bi bi-shield-fill-x"></i></div>
                <div>
                    <div class="text-muted small">মোট ব্লক এন্ট্রি</div>
                    <div class="fs-4 fw-bold text-danger"><?= (int)($stats['total_blocked'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary-subtle text-primary p-3 fs-4"><i class="bi bi-phone"></i></div>
                <div>
                    <div class="text-muted small">ব্লককৃত ফোন নম্বর</div>
                    <div class="fs-4 fw-bold text-primary"><?= (int)($stats['total_phones'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-warning-subtle text-warning p-3 fs-4"><i class="bi bi-globe"></i></div>
                <div>
                    <div class="text-muted small">ব্লককৃত IP অ্যাড্রেস</div>
                    <div class="fs-4 fw-bold text-warning"><?= (int)($stats['total_ips'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-info-subtle text-info p-3 fs-4"><i class="bi bi-envelope-at"></i></div>
                <div>
                    <div class="text-muted small">ব্লককৃত ইমেইল</div>
                    <div class="fs-4 fw-bold text-info"><?= (int)($stats['total_emails'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="ফোন নম্বর, IP, ইমেইল বা কারণ খুঁজুন..." value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select">
                    <option value="">সকল ধরন (All Types)</option>
                    <option value="phone" <?= $type === 'phone' ? 'selected' : '' ?>>ফোন নম্বর (Phone)</option>
                    <option value="ip" <?= $type === 'ip' ? 'selected' : '' ?>>IP অ্যাড্রেস (IP Address)</option>
                    <option value="email" <?= $type === 'email' ? 'selected' : '' ?>>ইমেইল (Email)</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i> ফিল্টার</button>
                <?php if ($search !== '' || $type !== ''): ?>
                    <a href="<?= url('admin/blocklist/') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Blocklist Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">ধরন</th>
                        <th>মান (Blocked Value)</th>
                        <th>ব্লক করার কারণ (Reason)</th>
                        <th>স্ট্যাটাস</th>
                        <th>ব্লক করার তারিখ</th>
                        <th class="text-end pe-3">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($blocked_items)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-shield-check display-5 d-block text-success mb-2 opacity-50"></i>
                                কোনো ব্লক রেকর্ড নেই।
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($blocked_items as $b): ?>
                            <tr>
                                <td class="ps-3">
                                    <?php if ($b['type'] === 'phone'): ?>
                                        <span class="badge bg-primary-subtle text-primary border"><i class="bi bi-telephone me-1"></i>ফোন</span>
                                    <?php elseif ($b['type'] === 'ip'): ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border"><i class="bi bi-globe me-1"></i>IP</span>
                                    <?php else: ?>
                                        <span class="badge bg-info-subtle text-info border"><i class="bi bi-envelope me-1"></i>ইমেইল</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="font-monospace fw-bold text-dark fs-6"><?= e($b['value']) ?></span>
                                </td>
                                <td>
                                    <span class="text-muted small"><?= e($b['reason'] ?: 'কোনো কারণ উল্লেখ নেই') ?></span>
                                </td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                        <button type="submit" class="btn btn-sm border-0 <?= $b['is_active'] ? 'text-success' : 'text-secondary' ?>" title="ক্লিক করে চালু/বন্ধ করুন">
                                            <i class="bi <?= $b['is_active'] ? 'bi-toggle-on fs-4' : 'bi-toggle-off fs-4' ?>"></i>
                                            <span class="small ms-1"><?= $b['is_active'] ? 'সক্রিয়' : 'বন্ধ' ?></span>
                                        </button>
                                    </form>
                                </td>
                                <td class="text-muted small">
                                    <?= date('d M, Y', strtotime($b['created_at'])) ?>
                                </td>
                                <td class="text-end pe-3">
                                    <form method="POST" class="d-inline" onsubmit="return confirm('ব্লক তালিকা থেকে এটি মুছে ফেলতে চান?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_block">
                                        <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="মুছে ফেলুন">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
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
            <?= render_pagination($page, ceil($total_rows / $limit), url('admin/blocklist/?q=' . urlencode($search) . '&type=' . urlencode($type))) ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Add Block -->
<div class="modal fade" id="addBlockModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add_block">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-shield-x me-2"></i>ব্লক তালিকায় যুক্ত করুন</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">ব্লকের ধরন</label>
                    <select name="type" class="form-select">
                        <option value="phone">মোবাইল নম্বর (Phone)</option>
                        <option value="ip">IP অ্যাড্রেস (IP Address)</option>
                        <option value="email">ইমেইল (Email)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">মান (Value) <span class="text-danger">*</span></label>
                    <input type="text" name="value" class="form-control font-monospace" placeholder="যেমন: 01712345678 বা 103.25.4.12" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">ব্লক করার কারণ (Reason)</label>
                    <textarea name="reason" class="form-control" rows="3" placeholder="যেমন: একাধিকবার ফেক অর্ডার দিয়ে পার্সেল রিসিভ করেনি..."></textarea>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" id="blockActiveSwitch" value="1" checked>
                    <label class="form-check-label fw-semibold" for="blockActiveSwitch">সরাসরি ব্লক সক্রিয় করুন</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="btn btn-danger">ব্লক করুন</button>
            </div>
        </form>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
