<?php
// ============================================================
// FarmersBD — Custom Landing Page Renderer
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
    redirect(BASE_URL . '/');
}

$pdo = get_db_connection();
$stmt = $pdo->prepare("SELECT * FROM landing_pages WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$lp = $stmt->fetch();

if (!$lp) {
    http_response_code(404);
    flash('ল্যান্ডিং পেজটি পাওয়া যায়নি।', FLASH_ERROR);
    redirect(BASE_URL . '/');
}

// Fetch linked products if any
$products = [];
if (!empty($lp['products'])) {
    $pids = array_filter(array_map('intval', explode(',', $lp['products'])));
    if (!empty($pids)) {
        $in_clause = implode(',', array_fill(0, count($pids), '?'));
        $p_stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($in_clause) AND is_active = 1");
        $p_stmt->execute($pids);
        $products = $p_stmt->fetchAll();
    }
}

$page_title = ($lp['meta_title'] ?: $lp['title']) . ' | FarmersBD';
$meta_description = $lp['meta_desc'] ?: $lp['hero_sub'];
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="py-5 bg-gradient text-dark position-relative overflow-hidden" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
    <div class="container py-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <?php if (!empty($lp['countdown_at']) && strtotime($lp['countdown_at']) > time()): ?>
                    <div class="d-inline-flex align-items-center gap-2 bg-danger text-white px-3 py-1 rounded-pill mb-3 shadow-sm font-monospace small">
                        <i class="bi bi-clock-history"></i>
                        <span>অফার শেষ হতে বাকি: <span id="landingCountdown" data-expire="<?= date('c', strtotime($lp['countdown_at'])) ?>">লোড হচ্ছে...</span></span>
                    </div>
                <?php endif; ?>

                <h1 class="display-5 fw-extrabold text-dark mb-3 lh-sm">
                    <?= e($lp['hero_heading'] ?: $lp['title']) ?>
                </h1>

                <?php if (!empty($lp['hero_sub'])): ?>
                    <p class="fs-5 text-secondary mb-4 leading-relaxed">
                        <?= nl2br(e($lp['hero_sub'])) ?>
                    </p>
                <?php endif; ?>

                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= e($lp['cta_link'] ?: '#order-section') ?>" class="btn btn-primary btn-lg px-4 py-3 shadow fw-bold rounded-pill">
                        <i class="bi bi-bag-check-fill me-2"></i><?= e($lp['cta_text'] ?: 'এখনই অর্ডার করুন') ?>
                    </a>
                    <?php
                    $wa_msg = urlencode("আসসালামু আলাইকুম, আমি '" . $lp['title'] . "' অফারটি সম্পর্কে জানতে চাই।");
                    $wa_num = setting('header_whatsapp', '8801700000000');
                    ?>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $wa_num) ?>?text=<?= $wa_msg ?>" target="_blank" class="btn btn-outline-success btn-lg px-4 py-3 shadow-sm rounded-pill">
                        <i class="bi bi-whatsapp me-2"></i> WhatsApp-এ যোগাযোগ
                    </a>
                </div>
            </div>

            <?php if (!empty($lp['hero_image'])): ?>
                <div class="col-lg-5 text-center">
                    <img src="<?= asset($lp['hero_image']) ?>" alt="<?= e($lp['title']) ?>" class="img-fluid rounded-4 shadow-lg object-fit-cover" style="max-height: 420px;">
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Content Body -->
<?php if (!empty($lp['content'])): ?>
    <section class="py-5 bg-white">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 fs-5 leading-relaxed text-dark">
                    <?= raw($lp['content']) ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Products Section -->
<?php if (!empty($products)): ?>
    <section class="py-5 bg-light" id="order-section">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2 rounded-pill mb-2">স্পেশাল প্যাকেজ</span>
                <h2 class="h2 fw-bold text-dark">অফারের অন্তর্ভুক্ত পণ্যসমূহ</h2>
            </div>

            <div class="row g-4 justify-content-center">
                <?php foreach ($products as $p): ?>
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                            <?php if (!empty($p['image'])): ?>
                                <img src="<?= asset($p['image']) ?>" alt="<?= e($p['name_bn'] ?: $p['name']) ?>" class="card-img-top object-fit-cover" style="height: 180px;">
                            <?php endif; ?>
                            <div class="card-body p-3 d-flex flex-column">
                                <h5 class="fw-bold fs-6 mb-2"><?= e($p['name_bn'] ?: $p['name']) ?></h5>
                                <div class="mt-auto d-flex justify-content-between align-items-center mb-3">
                                    <div class="fw-bold text-success fs-5"><?= format_price($p['sale_price'] ?: $p['regular_price']) ?></div>
                                    <?php if (!empty($p['sale_price']) && $p['regular_price'] > $p['sale_price']): ?>
                                        <del class="text-muted small"><?= format_price($p['regular_price']) ?></del>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= url('products/details.php?slug=' . $p['slug']) ?>" class="btn btn-primary btn-sm w-100 fw-semibold">
                                    <i class="bi bi-cart-plus me-1"></i> অর্ডার করুন
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<script>
const cdElem = document.getElementById('landingCountdown');
if (cdElem && cdElem.dataset.expire) {
    const expireTime = new Date(cdElem.dataset.expire).getTime();
    const interval = setInterval(function() {
        const now = new Date().getTime();
        const dist = expireTime - now;
        if (dist < 0) {
            clearInterval(interval);
            cdElem.innerHTML = "অফার সমাপ্ত";
            return;
        }
        const hours = Math.floor((dist % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((dist % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((dist % (1000 * 60)) / 1000);
        cdElem.innerHTML = hours + "ঘণ্টা " + minutes + "মি. " + seconds + "সে.";
    }, 1000);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
