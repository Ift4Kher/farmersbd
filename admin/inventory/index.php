<?php
// ============================================================
// FarmersBD — Admin Inventory Management
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

// Ensure inventory_history table exists
$pdo->exec("CREATE TABLE IF NOT EXISTS `inventory_history` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `type` ENUM('increase','decrease','sale','return','adjustment') NOT NULL DEFAULT 'adjustment',
    `quantity` INT NOT NULL,
    `before_qty` INT NOT NULL DEFAULT 0,
    `after_qty` INT NOT NULL DEFAULT 0,
    `reason` VARCHAR(255) DEFAULT NULL,
    `order_id` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ih_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Handle stock adjustment POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'adjust') {
    verify_csrf();
    $product_id = (int)($_POST['product_id'] ?? 0);
    $adj_type   = sanitize_input($_POST['adj_type'] ?? 'increase');
    $qty        = abs((int)($_POST['quantity'] ?? 0));
    $reason     = sanitize_input($_POST['reason'] ?? '');

    if ($product_id && $qty > 0) {
        $prod = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $prod->execute([$product_id]);
        $row = $prod->fetch();
        if ($row) {
            $before = (int)$row['stock'];
            $after  = ($adj_type === 'increase') ? $before + $qty : max(0, $before - $qty);

            $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?")->execute([$after, $product_id]);
            $pdo->prepare("INSERT INTO inventory_history (product_id, type, quantity, before_qty, after_qty, reason) VALUES (?,?,?,?,?,?)")
                ->execute([$product_id, $adj_type, $qty, $before, $after, $reason]);

            set_flash('success', 'স্টক আপডেট হয়েছে।');
        }
    } else {
        set_flash('error', 'সঠিক পরিমাণ দিন।');
    }
    redirect(BASE_URL . '/admin/inventory/');
}

// Filters
$search       = sanitize_input($_GET['search'] ?? '');
$stock_filter = sanitize_input($_GET['stock'] ?? '');
$cat_filter   = (int)($_GET['cat'] ?? 0);
$page         = max(1, (int)($_GET['page'] ?? 1));
$limit        = 20;
$offset       = ($page - 1) * $limit;

$where  = [];
$params = [];

if ($search) {
    $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($stock_filter === 'out') {
    $where[] = "p.stock = 0";
} elseif ($stock_filter === 'low') {
    $where[] = "p.stock > 0 AND p.stock <= 10";
} elseif ($stock_filter === 'ok') {
    $where[] = "p.stock > 10";
}
if ($cat_filter) {
    $where[] = "p.category_id = ?";
    $params[] = $cat_filter;
}

$wc = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$total = $pdo->prepare("SELECT COUNT(*) FROM products p $wc");
$total->execute($params);
$total_records = $total->fetchColumn();

$stmt = $pdo->prepare("
    SELECT p.*, pc.name AS cat_name
    FROM products p
    LEFT JOIN product_categories pc ON p.category_id = pc.id
    $wc ORDER BY p.stock ASC, p.name ASC
    LIMIT ? OFFSET ?
");
foreach ($params as $i => $v) $stmt->bindValue($i + 1, $v);
$stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
$stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT id, name FROM product_categories WHERE is_active=1 ORDER BY name")->fetchAll();

// Summary stats
$stats = $pdo->query("SELECT
    COUNT(*) as total_products,
    SUM(stock) as total_stock,
    SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as out_of_stock,
    SUM(CASE WHEN stock > 0 AND stock <= 10 THEN 1 ELSE 0 END) as low_stock
    FROM products WHERE is_active=1")->fetch();

// Recent history
$history = $pdo->query("
    SELECT ih.*, p.name AS product_name
    FROM inventory_history ih
    JOIN products p ON ih.product_id = p.id
    ORDER BY ih.created_at DESC LIMIT 10
")->fetchAll();

$pagination = paginate($total_records, $limit, $page, BASE_URL . '/admin/inventory/?search=' . urlencode($search) . '&stock=' . urlencode($stock_filter) . '&cat=' . $cat_filter);

$page_title = "ইনভেন্টরি ব্যবস্থাপনা — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-boxes text-primary me-2"></i> ইনভেন্টরি ব্যবস্থাপনা</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#adjustModal">
        <i class="bi bi-plus-circle me-1"></i> স্টক অ্যাডজাস্ট
    </button>
</div>

<?php display_flash(); ?>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 rounded-3">
            <div class="fs-2 fw-bold text-primary"><?= number_format((int)$stats['total_products']) ?></div>
            <div class="text-muted small">মোট পণ্য</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 rounded-3">
            <div class="fs-2 fw-bold text-success"><?= number_format((int)$stats['total_stock']) ?></div>
            <div class="text-muted small">মোট স্টক</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 rounded-3">
            <div class="fs-2 fw-bold text-warning"><?= number_format((int)$stats['low_stock']) ?></div>
            <div class="text-muted small">স্বল্প স্টক (≤10)</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 rounded-3">
            <div class="fs-2 fw-bold text-danger"><?= number_format((int)$stats['out_of_stock']) ?></div>
            <div class="text-muted small">স্টক শেষ</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Products Table -->
    <div class="col-lg-8">
        <!-- Filters -->
        <div class="card border-0 shadow-sm rounded-3 mb-3 p-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="পণ্যের নাম বা SKU..." value="<?= h($search) ?>">
                </div>
                <div class="col-md-3">
                    <select name="cat" class="form-select form-select-sm">
                        <option value="">সব ক্যাটাগরি</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $cat_filter == $c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="stock" class="form-select form-select-sm">
                        <option value="">সব স্টক</option>
                        <option value="out" <?= $stock_filter === 'out' ? 'selected' : '' ?>>স্টক শেষ</option>
                        <option value="low" <?= $stock_filter === 'low' ? 'selected' : '' ?>>স্বল্প স্টক</option>
                        <option value="ok" <?= $stock_filter === 'ok' ? 'selected' : '' ?>>পর্যাপ্ত স্টক</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-search"></i> ফিল্টার</button>
                </div>
            </form>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>পণ্য</th>
                                <th>SKU</th>
                                <th>ক্যাটাগরি</th>
                                <th class="text-center">বর্তমান স্টক</th>
                                <th class="text-center">অবস্থা</th>
                                <th class="text-center">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">কোনো পণ্য পাওয়া যায়নি।</td></tr>
                            <?php endif; ?>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= h($p['name']) ?></div>
                                    </td>
                                    <td><code><?= h($p['sku'] ?? '—') ?></code></td>
                                    <td><small class="text-muted"><?= h($p['cat_name'] ?? '—') ?></small></td>
                                    <td class="text-center fw-bold <?= $p['stock'] == 0 ? 'text-danger' : ($p['stock'] <= 10 ? 'text-warning' : 'text-success') ?>">
                                        <?= number_format((int)$p['stock']) ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($p['stock'] == 0): ?>
                                            <span class="badge bg-danger">স্টক শেষ</span>
                                        <?php elseif ($p['stock'] <= 10): ?>
                                            <span class="badge bg-warning text-dark">স্বল্প</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">পর্যাপ্ত</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary"
                                            onclick="openAdjust(<?= $p['id'] ?>, '<?= addslashes(h($p['name'])) ?>', <?= $p['stock'] ?>)">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="p-3"><?= render_pagination($pagination) ?></div>
            </div>
        </div>
    </div>

    <!-- Recent History -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>সাম্প্রতিক পরিবর্তন</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if (empty($history)): ?>
                        <li class="list-group-item text-center text-muted py-4">কোনো ইতিহাস নেই।</li>
                    <?php endif; ?>
                    <?php foreach ($history as $h_row): ?>
                        <li class="list-group-item px-3 py-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-bold small"><?= h($h_row['product_name']) ?></div>
                                    <small class="text-muted"><?= h($h_row['reason'] ?: '—') ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge <?= $h_row['type'] === 'increase' ? 'bg-success' : 'bg-danger' ?> mb-1">
                                        <?= $h_row['type'] === 'increase' ? '+' : '-' ?><?= abs($h_row['quantity']) ?>
                                    </span>
                                    <div class="small text-muted"><?= date('d/m H:i', strtotime($h_row['created_at'])) ?></div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Stock Adjust Modal -->
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-boxes me-2"></i>স্টক অ্যাডজাস্ট</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="adjust">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">পণ্য নির্বাচন করুন</label>
                        <select name="product_id" id="adj_product_id" class="form-select" required>
                            <option value="">— পণ্য বেছে নিন —</option>
                            <?php
                            $all_products = $pdo->query("SELECT id, name, stock FROM products WHERE is_active=1 ORDER BY name")->fetchAll();
                            foreach ($all_products as $ap):
                            ?>
                                <option value="<?= $ap['id'] ?>" data-stock="<?= $ap['stock'] ?>"><?= h($ap['name']) ?> (স্টক: <?= $ap['stock'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">পরিবর্তনের ধরন</label>
                        <select name="adj_type" class="form-select" required>
                            <option value="increase">বৃদ্ধি করুন (Increase)</option>
                            <option value="decrease">হ্রাস করুন (Decrease)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">পরিমাণ</label>
                        <input type="number" name="quantity" class="form-control" min="1" required placeholder="পরিমাণ লিখুন">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">কারণ (ঐচ্ছিক)</label>
                        <input type="text" name="reason" class="form-control" placeholder="যেমন: নতুন মজুদ, ক্ষতিগ্রস্ত...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAdjust(id, name, stock) {
    document.getElementById('adj_product_id').value = id;
    var modal = new bootstrap.Modal(document.getElementById('adjustModal'));
    modal.show();
}
</script>

<?php require_once $base . '/admin/includes/footer.php'; ?>
