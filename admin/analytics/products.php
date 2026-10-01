<?php
// ============================================================
// FarmersBD — Admin Products Analytics
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';

require_admin();
$pdo = get_db_connection();

// Top Selling Products
$top_sold_stmt = $pdo->query("SELECT 
    p.id, p.name, p.name_bn, p.sku, p.image, p.stock, p.regular_price, p.sale_price,
    COALESCE(SUM(oi.quantity), 0) as total_units_sold,
    COALESCE(SUM(oi.quantity * oi.price), 0) as total_sales_val
    FROM products p
    LEFT JOIN order_items oi ON oi.product_id = p.id
    LEFT JOIN orders o ON o.id = oi.order_id AND o.order_status != 'cancelled'
    WHERE p.status = 'published'
    GROUP BY p.id
    ORDER BY total_units_sold DESC
    LIMIT 15");
$top_products = $top_sold_stmt->fetchAll();

// Low stock / out of stock alerts
$low_stock_stmt = $pdo->query("SELECT id, name, name_bn, sku, stock, min_stock_warning, image 
    FROM products 
    WHERE status = 'published' AND stock <= COALESCE(min_stock_warning, 5) 
    ORDER BY stock ASC 
    LIMIT 10");
$low_stock_products = $low_stock_stmt->fetchAll();

// Category-wise product count and sales
$cat_stmt = $pdo->query("SELECT 
    c.name, c.name_bn,
    COUNT(DISTINCT p.id) as total_products,
    COALESCE(SUM(oi.quantity), 0) as units_sold,
    COALESCE(SUM(oi.quantity * oi.price), 0) as revenue
    FROM product_categories c
    LEFT JOIN products p ON p.category_id = c.id
    LEFT JOIN order_items oi ON oi.product_id = p.id
    LEFT JOIN orders o ON o.id = oi.order_id AND o.order_status != 'cancelled'
    GROUP BY c.id
    ORDER BY revenue DESC");
$categories_data = $cat_stmt->fetchAll();

$page_title = 'প্রোডাক্ট অ্যানালিটিক্স | FarmersBD Admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-box-seam-fill text-primary me-2"></i>প্রোডাক্ট পারফরম্যান্স অ্যানালিটিক্স</h1>
            <p class="text-muted small mb-0">সর্বাধিক বিক্রীত পণ্য, লো-স্টক অ্যালার্ট এবং ক্যাটাগরি অনুযায়ী বিক্রয় বিশ্লেষণ</p>
        </div>
    </div>
</div>

<!-- Top Products Table -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 fw-bold">
        <i class="bi bi-trophy text-warning me-2"></i>সর্বাধিক বিক্রীত পণ্যসমূহ (Top Selling Products)
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">পণ্য</th>
                        <th>SKU</th>
                        <th>বর্তমান স্টক</th>
                        <th>বিক্রীত একক (Units Sold)</th>
                        <th>মোট অর্জিত বিক্রয়</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($top_products)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">কোনো ডাটা পাওয়া যায়নি</td></tr>
                    <?php else: ?>
                        <?php foreach ($top_products as $tp): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($tp['image'])): ?>
                                            <img src="<?= asset($tp['image']) ?>" class="rounded object-fit-cover" width="40" height="40">
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold text-dark"><?= e($tp['name_bn'] ?: $tp['name']) ?></div>
                                            <div class="text-muted small"><?= e($tp['name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="font-monospace text-muted small"><?= e($tp['sku'] ?: '-') ?></td>
                                <td>
                                    <span class="badge <?= $tp['stock'] > 10 ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?>">
                                        <?= (int)$tp['stock'] ?> টি
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary fw-semibold fs-7"><?= (int)$tp['total_units_sold'] ?> টি বিক্রি</span>
                                </td>
                                <td class="fw-bold text-success fs-6"><?= format_price($tp['total_sales_val']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Category-wise breakdown -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 fw-bold">
                <i class="bi bi-tags text-primary me-2"></i>ক্যাটাগরি ভিত্তিক পারফরম্যান্স
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">ক্যাটাগরি</th>
                                <th>পণ্য সংখ্যা</th>
                                <th>বিক্রয় একক</th>
                                <th class="text-end pe-3">মোট আয়</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories_data as $cd): ?>
                                <tr>
                                    <td class="ps-3 fw-semibold text-dark"><?= e($cd['name_bn'] ?: $cd['name']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= $cd['total_products'] ?></span></td>
                                    <td><?= $cd['units_sold'] ?></td>
                                    <td class="text-end pe-3 text-success fw-bold"><?= format_price($cd['revenue']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Low stock warning -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 fw-bold text-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>লো স্টক / শেষ হওয়ার ঝুঁকিতে থাকা পণ্য
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (empty($low_stock_products)): ?>
                        <div class="text-center text-muted py-4">সব পণ্যের পর্যাপ্ত স্টক আছে</div>
                    <?php else: ?>
                        <?php foreach ($low_stock_products as $lp): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold text-dark small"><?= e($lp['name_bn'] ?: $lp['name']) ?></div>
                                    <div class="text-muted small font-monospace">SKU: <?= e($lp['sku'] ?: '-') ?></div>
                                </div>
                                <span class="badge bg-danger">স্টক: <?= (int)$lp['stock'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-footer bg-white border-0 py-3 text-center">
                <a href="<?= url('admin/inventory/') ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-boxes me-1"></i> ইনভেন্টরি ম্যানেজ করুন
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
