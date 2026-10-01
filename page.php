<?php
// ============================================================
// FarmersBD — Static CMS Page Viewer
// ============================================================
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/seo.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) {
    redirect(BASE_URL . '/');
}

$pdo = get_db_connection();
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    http_response_code(404);
    flash('পেজটি পাওয়া যায়নি।', FLASH_ERROR);
    redirect(BASE_URL . '/');
}

$page_title = ($page['meta_title'] ?: $page['title']) . ' | FarmersBD';
$meta_description = $page['meta_desc'] ?: '';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>" class="text-decoration-none">হোম</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= e($page['title']) ?></li>
                </ol>
            </nav>

            <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 bg-white">
                <h1 class="h2 fw-bold text-dark mb-4 border-bottom pb-3"><?= e($page['title']) ?></h1>
                <div class="page-content leading-relaxed text-secondary fs-6">
                    <?= raw($page['content']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
