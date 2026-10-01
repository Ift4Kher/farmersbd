<?php
// ============================================================
// FarmersBD — Admin Media Library
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/flash.php';
require_once $base . '/includes/csrf.php';

require_admin();

$uploads_dir = $base . '/uploads';
$allowed_dirs = ['ai', 'blogs', 'consultations', 'diseases', 'fish', 'general', 'hero', 'products'];
$current_dir = isset($_GET['dir']) && in_array($_GET['dir'], $allowed_dirs) ? $_GET['dir'] : 'products';
$target_dir = $uploads_dir . '/' . $current_dir;

if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    csrf_verify();
    $file = basename($_POST['file'] ?? '');
    if ($file) {
        $file_path = $target_dir . '/' . $file;
        if (file_exists($file_path) && is_file($file_path)) {
            unlink($file_path);
            flash('ফাইল মুছে ফেলা হয়েছে।', FLASH_SUCCESS);
        } else {
            flash('ফাইল পাওয়া যায়নি।', FLASH_ERROR);
        }
    }
    redirect(BASE_URL . '/admin/media/index.php?dir=' . $current_dir);
}

// Scan files
$files = array_diff(scandir($target_dir), ['.', '..']);
$media_files = [];
foreach ($files as $f) {
    if (is_file($target_dir . '/' . $f)) {
        $media_files[] = [
            'name' => $f,
            'size' => filesize($target_dir . '/' . $f),
            'time' => filemtime($target_dir . '/' . $f),
            'url' => UPLOAD_BASE_URL . $current_dir . '/' . $f
        ];
    }
}
usort($media_files, fn($a, $b) => $b['time'] <=> $a['time']);

$page_title = "মিডিয়া লাইব্রেরি — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-images text-primary me-2"></i> মিডিয়া লাইব্রেরি</h3>
</div>

<?php display_flash(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom p-3">
        <ul class="nav nav-pills">
            <?php foreach ($allowed_dirs as $d): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $current_dir === $d ? 'active' : '' ?>" href="?dir=<?= $d ?>">
                        <?= ucfirst($d) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="card-body bg-light">
        <div class="row g-3">
            <?php if (empty($media_files)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center m-0">এই ফোল্ডারে কোনো মিডিয়া ফাইল নেই।</div>
                </div>
            <?php else: ?>
                <?php foreach ($media_files as $f): ?>
                    <div class="col-6 col-md-3 col-lg-2">
                        <div class="card h-100 border-0 shadow-sm position-relative overflow-hidden group-hover">
                            <div class="ratio ratio-1x1 bg-white d-flex align-items-center justify-content-center">
                                <?php if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $f['name'])): ?>
                                    <img src="<?= $f['url'] ?>" alt="<?= htmlspecialchars($f['name']) ?>" class="object-fit-contain w-100 h-100 p-2">
                                <?php else: ?>
                                    <i class="bi bi-file-earmark-text text-muted fs-1 d-flex align-items-center justify-content-center w-100 h-100"></i>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-2 bg-white border-top text-center" style="font-size: 0.75rem;">
                                <div class="text-truncate mb-1" title="<?= htmlspecialchars($f['name']) ?>"><?= htmlspecialchars($f['name']) ?></div>
                                <div class="text-muted mb-2"><?= round($f['size'] / 1024, 1) ?> KB</div>
                                
                                <form action="" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই ফাইলটি মুছে ফেলতে চান? এটি ওয়েবসাইটে সমস্যা তৈরি করতে পারে যদি এটি ব্যবহৃত হয়।');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="file" value="<?= htmlspecialchars($f['name']) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100 py-0"><i class="bi bi-trash"></i> মুছুন</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.object-fit-contain { object-fit: contain; }
</style>

<?php require_once $base . '/admin/includes/footer.php'; ?>
