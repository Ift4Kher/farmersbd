<?php
// ============================================================
// FarmersBD — Single Combo Offer Page
// ============================================================
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/seo.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) {
    redirect(BASE_URL . '/products/');
}

$pdo = get_db_connection();
$stmt = $pdo->prepare("SELECT * FROM combos WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$combo = $stmt->fetch();

if (!$combo) {
    http_response_code(404);
    flash('কম্বো প্যাকেজটি পাওয়া যায়নি।', FLASH_ERROR);
    redirect(BASE_URL . '/products/');
}

// Fetch combo items
$items_stmt = $pdo->prepare("SELECT ci.*, p.name, p.name_bn, p.image, p.regular_price, p.sale_price, p.unit, p.slug as prod_slug 
    FROM combo_items ci
    JOIN products p ON p.id = ci.product_id
    WHERE ci.combo_id = ?");
$items_stmt->execute([$combo['id']]);
$combo_items = $items_stmt->fetchAll();

$page_title = ($combo['name_bn'] ?: $combo['name']) . ' — বিশেষ কম্বো অফার | FarmersBD';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4 py-lg-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('/') ?>" class="text-decoration-none">হোম</a></li>
            <li class="breadcrumb-item"><a href="<?= url('/products/') ?>" class="text-decoration-none">পণ্যসমূহ</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e($combo['name_bn'] ?: $combo['name']) ?></li>
        </ol>
    </nav>

    <div class="row g-4 g-lg-5">
        <!-- Combo Image -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-sticky" style="top: 100px;">
                <?php if (!empty($combo['image'])): ?>
                    <img src="<?= asset($combo['image']) ?>" alt="<?= e($combo['name_bn'] ?: $combo['name']) ?>" class="img-fluid w-100 object-fit-cover" style="max-height: 450px;">
                <?php else: ?>
                    <div class="bg-light py-5 text-center text-muted">
                        <i class="bi bi-collection display-1 text-secondary opacity-25"></i>
                    </div>
                <?php endif; ?>
                <div class="position-absolute top-0 start-0 m-3">
                    <span class="badge bg-danger fs-6 px-3 py-2 shadow-sm rounded-pill">
                        <i class="bi bi-lightning-fill me-1"></i> স্পেশাল কম্বো অফার
                    </span>
                </div>
            </div>
        </div>

        <!-- Combo Details & Items -->
        <div class="col-lg-6">
            <div class="ps-lg-3">
                <h1 class="h2 fw-bold text-dark mb-2"><?= e($combo['name_bn'] ?: $combo['name']) ?></h1>
                <?php if (!empty($combo['name_bn']) && !empty($combo['name'])): ?>
                    <div class="text-muted mb-3 fs-6"><?= e($combo['name']) ?></div>
                <?php endif; ?>

                <!-- Price Box -->
                <div class="p-3 bg-light rounded-3 d-flex align-items-center gap-3 mb-4 border">
                    <div class="fs-2 fw-bold text-success"><?= format_price($combo['price']) ?></div>
                    <?php if (!empty($combo['regular_price']) && $combo['regular_price'] > $combo['price']): ?>
                        <del class="text-muted fs-5"><?= format_price($combo['regular_price']) ?></del>
                        <span class="badge bg-danger rounded-pill px-2 py-1">
                            <?= round((($combo['regular_price'] - $combo['price']) / $combo['regular_price']) * 100) ?>% সাশ্রয়
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <?php if (!empty($combo['description'])): ?>
                    <div class="mb-4 text-secondary leading-relaxed">
                        <?= nl2br(e($combo['description'])) ?>
                    </div>
                <?php endif; ?>

                <!-- Included Products List -->
                <div class="card border border-primary-subtle bg-primary-subtle bg-opacity-10 rounded-3 mb-4 shadow-sm">
                    <div class="card-header bg-transparent border-0 pt-3 pb-0 fw-bold text-primary">
                        <i class="bi bi-boxes me-2"></i>এই কম্বো প্যাকেজে অন্তর্ভুক্ত রয়েছে:
                    </div>
                    <div class="card-body p-3">
                        <div class="list-group list-group-flush bg-transparent">
                            <?php foreach ($combo_items as $ci): ?>
                                <div class="list-group-item bg-transparent d-flex align-items-center justify-content-between px-0 py-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($ci['image'])): ?>
                                            <img src="<?= asset($ci['image']) ?>" alt="<?= e($ci['name_bn'] ?: $ci['name']) ?>" class="rounded object-fit-cover shadow-sm" width="40" height="40">
                                        <?php endif; ?>
                                        <div>
                                            <a href="<?= url('products/details.php?slug=' . $ci['prod_slug']) ?>" class="fw-semibold text-dark text-decoration-none">
                                                <?= e($ci['name_bn'] ?: $ci['name']) ?>
                                            </a>
                                            <div class="text-muted small"><?= e($ci['unit'] ?? '') ?></div>
                                        </div>
                                    </div>
                                    <span class="badge bg-primary text-white rounded-pill px-2 py-1">
                                        পরিমাণ: <?= (int)$ci['quantity'] ?> টি
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-3">
                    <form action="<?= url('checkout/') ?>" method="POST" class="d-grid">
                        <?= csrf_field() ?>
                        <input type="hidden" name="combo_id" value="<?= $combo['id'] ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm py-3 fw-bold">
                            <i class="bi bi-bag-check-fill me-2"></i> এখনই অর্ডার করুন (Cash on Delivery)
                        </button>
                    </form>

                    <?php
                    $wa_msg = urlencode("আসসালামু আলাইকুম, আমি '" . ($combo['name_bn'] ?: $combo['name']) . "' কম্বো প্যাকেজটি অর্ডার করতে চাই।");
                    $wa_num = setting('header_whatsapp', '8801700000000');
                    ?>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $wa_num) ?>?text=<?= $wa_msg ?>" target="_blank" class="btn btn-outline-success btn-lg shadow-sm">
                        <i class="bi bi-whatsapp me-2"></i> WhatsApp-এ অর্ডার করুন
                    </a>
                </div>

                <!-- Guarantee info -->
                <div class="row g-3 text-center mt-4 pt-3 border-top">
                    <div class="col-4">
                        <i class="bi bi-shield-check text-success fs-3 d-block mb-1"></i>
                        <span class="small fw-semibold text-muted">১০০% অরিজিনাল</span>
                    </div>
                    <div class="col-4">
                        <i class="bi bi-truck text-primary fs-3 d-block mb-1"></i>
                        <span class="small fw-semibold text-muted">দ্রুত ডেলিভারি</span>
                    </div>
                    <div class="col-4">
                        <i class="bi bi-cash-coin text-warning fs-3 d-block mb-1"></i>
                        <span class="small fw-semibold text-muted">হাতে পেয়ে পেমেন্ট</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
