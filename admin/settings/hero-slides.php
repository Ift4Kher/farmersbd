<?php
// ============================================================
// FarmersBD — Admin Hero Slider Settings
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/csrf.php';
require_once $base . '/includes/flash.php';
require_once $base . '/services/UploadService.php';

require_admin();
$pdo = get_db_connection();

if (is_post_request() && isset($_POST['action']) && $_POST['action'] === 'add') {
    verify_csrf_token();
    
    $title    = sanitize_input($_POST['title'] ?? '');
    $subtitle = sanitize_input($_POST['subtitle'] ?? '');
    $btn_text = sanitize_input($_POST['btn_text'] ?? '');
    $btn_url  = sanitize_input($_POST['btn_url'] ?? '');
    
    $image_name = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploader = new UploadService();
        $res = $uploader->upload_image($_FILES['image'], 'hero');
        if ($res['success']) {
            $image_name = $res['filename'];
        }
    }

    if ($image_name) {
        $stmt = $pdo->prepare("INSERT INTO hero_slides (title, subtitle, btn_text, btn_url, image, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$title, $subtitle, $btn_text, $btn_url, $image_name]);
        set_flash('success', 'নতুন স্লাইড যোগ করা হয়েছে।');
    } else {
        set_flash('error', 'স্লাইডারের ছবি সিলেক্ট করুন।');
    }
    redirect(BASE_URL . '/admin/settings/hero-slides.php');
}

if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT image FROM hero_slides WHERE id = ?");
    $stmt->execute([$del_id]);
    $slide = $stmt->fetch();
    if ($slide) {
        if (!empty($slide['image'])) {
            $uploader = new UploadService();
            $uploader->delete_image($slide['image'], 'hero');
        }
        $pdo->prepare("DELETE FROM hero_slides WHERE id = ?")->execute([$del_id]);
        set_flash('success', 'স্লাইডটি মুছে ফেলা হয়েছে।');
    }
    redirect(BASE_URL . '/admin/settings/hero-slides.php');
}

$slides = $pdo->query("SELECT * FROM hero_slides ORDER BY id DESC")->fetchAll();

$page_title = "হিরো ইমেজ স্লাইডার — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-images text-primary me-2"></i> ডায়নামিক হোম স্লাইডার ম্যানেজমেন্ট</h3>
</div>

<?php display_flash(); ?>

<!-- Add Form -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-bottom-0">
        <h5 class="fw-bold mb-0">নতুন স্লাইড যুক্ত করুন</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">স্লাইড টাইটেল</label>
                    <input type="text" name="title" class="form-control" placeholder="যেমন: আধুনিক মাছের রোগ শনাক্তকরণ">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">স্লাইড সাবটাইটেল</label>
                    <input type="text" name="subtitle" class="form-control" placeholder="যেমন: কৃত্রিম বুদ্ধিমত্তা দিয়ে দ্রুত চিকিৎসা সমাধান">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">বাটন টেক্সট</label>
                    <input type="text" name="btn_text" class="form-control" placeholder="যেমন: রোগ পরীক্ষা করুন">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">বাটন লিংক (URL)</label>
                    <input type="text" name="btn_url" class="form-control" placeholder="<?= BASE_URL ?>/ai/index.php">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">স্লাইডার ব্যাকগ্রাউন্ড ছবি <span class="text-danger">*</span></label>
                    <input type="file" name="image" class="form-control" accept="image/*" required>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-plus-circle me-1"></i> স্লাইড যোগ করুন</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Slide List -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ছবি</th>
                        <th>টাইটেল</th>
                        <th>সাবটাইটেল</th>
                        <th>বাটন</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($slides)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">কোনো কাস্টম স্লাইড পাওয়া যায়নি। ডিফল্ট ব্যানার প্রদর্শিত হচ্ছে।</td></tr>
                    <?php else: ?>
                        <?php foreach ($slides as $sl): ?>
                            <tr>
                                <td>
                                    <img src="<?= get_upload_url($sl['image'], 'hero') ?>" class="rounded" style="width:100px; height:50px; object-fit:cover;" alt="">
                                </td>
                                <td class="fw-bold"><?= h($sl['title']) ?></td>
                                <td class="small text-muted"><?= h($sl['subtitle']) ?></td>
                                <td><span class="badge bg-secondary"><?= h($sl['btn_text']) ?></span></td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/admin/settings/hero-slides.php?delete=<?= $sl['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('মুছে ফেলতে চান?');"><i class="bi bi-trash"></i> মুছে ফেলুন</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
