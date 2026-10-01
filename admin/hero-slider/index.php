<?php
// ============================================================
// FarmersBD — Admin Hero Slider Management
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/csrf.php';
require_once $base . '/includes/flash.php';

require_admin();
$pdo = get_db_connection();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verify_csrf();

    if ($_POST['action'] === 'add_slide') {
        $title      = sanitize_input($_POST['title'] ?? '');
        $subtitle   = sanitize_input($_POST['subtitle'] ?? '');
        $btn_text   = sanitize_input($_POST['btn_text'] ?? '');
        $btn_url    = sanitize_input($_POST['btn_url'] ?? '');
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $is_active  = isset($_POST['is_active']) ? 1 : 0;

        $image_path = '';
        if (!empty($_FILES['image']['name'])) {
            $upload_dir = $base . '/public/uploads/hero/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $filename = 'hero_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                    $image_path = 'uploads/hero/' . $filename;
                }
            }
        }

        if (empty($image_path)) {
            set_flash('error', 'স্লাইডার ব্যানার ইমেজ নির্বাচন করুন।');
        } else {
            $stmt = $pdo->prepare("INSERT INTO hero_slides (image, title, subtitle, btn_text, btn_url, sort_order, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$image_path, $title, $subtitle, $btn_text, $btn_url, $sort_order, $is_active]);
            set_flash('success', 'নতুন হিরো স্লাইড সফলভাবে যোগ করা হয়েছে।');
        }
        redirect(BASE_URL . '/admin/hero-slider/');
    }

    if ($_POST['action'] === 'edit_slide') {
        $id         = (int)($_POST['id'] ?? 0);
        $title      = sanitize_input($_POST['title'] ?? '');
        $subtitle   = sanitize_input($_POST['subtitle'] ?? '');
        $btn_text   = sanitize_input($_POST['btn_text'] ?? '');
        $btn_url    = sanitize_input($_POST['btn_url'] ?? '');
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $is_active  = isset($_POST['is_active']) ? 1 : 0;

        $slide_stmt = $pdo->prepare("SELECT image FROM hero_slides WHERE id = ?");
        $slide_stmt->execute([$id]);
        $curr_img = $slide_stmt->fetchColumn();

        $image_path = $curr_img;
        if (!empty($_FILES['image']['name'])) {
            $upload_dir = $base . '/public/uploads/hero/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $filename = 'hero_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                    $image_path = 'uploads/hero/' . $filename;
                }
            }
        }

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE hero_slides SET image = ?, title = ?, subtitle = ?, btn_text = ?, btn_url = ?, sort_order = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$image_path, $title, $subtitle, $btn_text, $btn_url, $sort_order, $is_active, $id]);
            set_flash('success', 'স্লাইডার সফলভাবে আপডেট করা হয়েছে।');
        }
        redirect(BASE_URL . '/admin/hero-slider/');
    }

    if ($_POST['action'] === 'toggle_status') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("UPDATE hero_slides SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
            set_flash('success', 'স্লাইডারের দৃশ্যমানতা পরিবর্তিত হয়েছে।');
        }
        redirect(BASE_URL . '/admin/hero-slider/');
    }

    if ($_POST['action'] === 'delete_slide') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM hero_slides WHERE id = ?")->execute([$id]);
            set_flash('success', 'হিরো স্লাইড মুছে ফেলা হয়েছে।');
        }
        redirect(BASE_URL . '/admin/hero-slider/');
    }
}

// Fetch all slides ordered by sort_order
$slides_stmt = $pdo->query("SELECT * FROM hero_slides ORDER BY sort_order ASC, id DESC");
$slides = $slides_stmt->fetchAll();

$page_title = 'হিরো স্লাইডার ম্যানেজমেন্ট | FarmersBD Admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-images text-primary me-2"></i>হোমপেজ হিরো স্লাইডার</h1>
            <p class="text-muted small mb-0">হোমপেজের মূল ব্যানার স্লাইডার ছবি, অফার টেক্সট ও লিংক পরিবর্তন করুন</p>
        </div>
        <button type="button" class="btn btn-primary px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addSlideModal">
            <i class="bi bi-plus-circle me-1"></i> নতুন স্লাইড যোগ করুন
        </button>
    </div>
</div>

<div class="row g-4">
    <?php if (empty($slides)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm text-center py-5">
                <i class="bi bi-images display-4 text-muted mb-2"></i>
                <h5 class="text-secondary fw-semibold">কোনো স্লাইডার ব্যানার পাওয়া যায়নি</h5>
                <p class="text-muted small mb-3">নতুন স্লাইড তৈরি করতে উপরের বাটনে ক্লিক করুন।</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($slides as $s): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="position-relative">
                        <img src="<?= asset($s['image']) ?>" alt="<?= e($s['title'] ?: 'Hero Slide') ?>" class="w-100 object-fit-cover" style="height: 190px;">
                        <span class="position-absolute top-0 start-0 m-2 badge bg-dark bg-opacity-75 font-monospace">
                            ক্রম: #<?= $s['sort_order'] ?>
                        </span>
                        <span class="position-absolute top-0 end-0 m-2 badge <?= $s['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                            <?= $s['is_active'] ? 'সক্রিয়' : 'নিষ্ক্রিয়' ?>
                        </span>
                    </div>
                    <div class="card-body p-3 d-flex flex-column">
                        <h5 class="fw-bold mb-1 text-dark fs-6"><?= e($s['title'] ?: 'কোনো শিরোনাম নেই') ?></h5>
                        <p class="text-muted small mb-2 flex-grow-1"><?= e($s['subtitle'] ?: 'কোনো সাব-শিরোনাম নেই') ?></p>
                        
                        <?php if (!empty($s['btn_text'])): ?>
                            <div class="mb-3">
                                <span class="badge bg-light text-primary border">
                                    <i class="bi bi-link-45deg me-1"></i><?= e($s['btn_text']) ?>: <?= e($s['btn_url']) ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <form method="POST" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi <?= $s['is_active'] ? 'bi-eye-slash' : 'bi-eye' ?> me-1"></i>
                                    <?= $s['is_active'] ? 'লুকান' : 'প্রকাশ করুন' ?>
                                </button>
                            </form>

                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary edit-slide-btn"
                                    data-id="<?= $s['id'] ?>"
                                    data-title="<?= e($s['title']) ?>"
                                    data-subtitle="<?= e($s['subtitle']) ?>"
                                    data-btn-text="<?= e($s['btn_text']) ?>"
                                    data-btn-url="<?= e($s['btn_url']) ?>"
                                    data-order="<?= $s['sort_order'] ?>"
                                    data-active="<?= $s['is_active'] ?>"
                                    data-img="<?= asset($s['image']) ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('এই স্লাইডটি মুছে ফেলতে চান?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete_slide">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal: Add Slide -->
<div class="modal fade" id="addSlideModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add_slide">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle text-primary me-2"></i>নতুন ব্যানার স্লাইড</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">ব্যানার ইমেজ <span class="text-danger">*</span></label>
                    <input type="file" name="image" class="form-control" accept="image/*" required>
                    <div class="form-text small">অনুকূল সাইজ: 1920x600 px (WebP / JPG / PNG)</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">প্রধান শিরোনাম</label>
                    <input type="text" name="title" class="form-control" placeholder="যেমন: আধুনিক মৎস্য চাষের নির্ভরযোগ্য সঙ্গী">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">সাব-শিরোনাম</label>
                    <input type="text" name="subtitle" class="form-control" placeholder="যেমন: শতভাগ খাঁটি ও পরীক্ষিত উপাদান">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">বাটন টেক্সট</label>
                        <input type="text" name="btn_text" class="form-control" placeholder="যেমন: পণ্য কিনুন">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">বাটন লিংক</label>
                        <input type="text" name="btn_url" class="form-control font-monospace" placeholder="/shop">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">ক্রম নম্বর (Sort Order)</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                    <div class="col-md-6 pt-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="addActiveSwitch" value="1" checked>
                            <label class="form-check-label fw-semibold" for="addActiveSwitch">সরাসরি সক্রিয় রাখুন</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="btn btn-primary">স্লাইড আপলোড করুন</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Slide -->
<div class="modal fade" id="editSlideModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="edit_slide">
            <input type="hidden" name="id" id="editSlideId">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>স্লাইডার সম্পাদনা</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="mb-2 text-center">
                        <img id="editSlideImgPreview" src="" class="img-fluid rounded object-fit-cover w-100" style="height: 120px;">
                    </div>
                    <label class="form-label fw-semibold">নতুন ইমেজ আপলোড (যদি পরিবর্তন করতে চান)</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">প্রধান শিরোনাম</label>
                    <input type="text" name="title" id="editSlideTitle" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">সাব-শিরোনাম</label>
                    <input type="text" name="subtitle" id="editSlideSubtitle" class="form-control">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">বাটন টেক্সট</label>
                        <input type="text" name="btn_text" id="editSlideBtnText" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">বাটন লিংক</label>
                        <input type="text" name="btn_url" id="editSlideBtnUrl" class="form-control font-monospace">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">ক্রম নম্বর</label>
                        <input type="number" name="sort_order" id="editSlideOrder" class="form-control">
                    </div>
                    <div class="col-md-6 pt-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editSlideActive" value="1">
                            <label class="form-check-label fw-semibold" for="editSlideActive">সক্রিয় রাখুন</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="btn btn-primary">পরিবর্তন সেভ করুন</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.edit-slide-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('editSlideId').value = this.dataset.id;
        document.getElementById('editSlideTitle').value = this.dataset.title;
        document.getElementById('editSlideSubtitle').value = this.dataset.subtitle;
        document.getElementById('editSlideBtnText').value = this.dataset.btnText;
        document.getElementById('editSlideBtnUrl').value = this.dataset.btnUrl;
        document.getElementById('editSlideOrder').value = this.dataset.order;
        document.getElementById('editSlideActive').checked = this.dataset.active === '1';
        document.getElementById('editSlideImgPreview').src = this.dataset.img;

        new bootstrap.Modal(document.getElementById('editSlideModal')).show();
    });
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
