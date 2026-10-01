<?php
// ============================================================
// FarmersBD — Admin Trash (Recycle Bin)
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/flash.php';
require_once $base . '/services/UploadService.php';

require_admin();

$pdo = get_db_connection();

// Restore or Force Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    if ($_POST['action'] === 'restore') {
        $stmt = $pdo->prepare("UPDATE products SET deleted_at = NULL WHERE id = ?");
        $stmt->execute([$id]);
        flash('পণ্যটি সফলভাবে রিস্টোর করা হয়েছে।', FLASH_SUCCESS);
    } elseif ($_POST['action'] === 'force_delete') {
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if ($product) {
            if (!empty($product['image'])) {
                $uploader = new UploadService();
                $uploader->delete_image($product['image'], 'products');
            }
            $del = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $del->execute([$id]);
            flash('পণ্যটি স্থায়ীভাবে মুছে ফেলা হয়েছে।', FLASH_SUCCESS);
        }
    }
    redirect(BASE_URL . '/admin/products/trash.php');
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$count_stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE deleted_at IS NOT NULL");
$total_records = $count_stmt->fetchColumn();

$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN product_categories c ON p.category_id = c.id 
        WHERE p.deleted_at IS NOT NULL
        ORDER BY p.deleted_at DESC 
        LIMIT $limit OFFSET $offset";
$products = $pdo->query($sql)->fetchAll();

$pagination = paginate($total_records, $limit, $page, BASE_URL . '/admin/products/trash.php');

$page_title = "রিসাইকেল বিন — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-trash text-danger me-2"></i> রিসাইকেল বিন (ট্র্যাশ)</h3>
    <a href="<?= url('admin/products/') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> পণ্য তালিকায় ফিরে যান
    </a>
</div>

<?php display_flash(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ছবি</th>
                        <th>পণ্যের নাম</th>
                        <th>ক্যাটাগরি</th>
                        <th>মূল্য</th>
                        <th>মুছে ফেলার সময়</th>
                        <th class="text-end pe-4">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">রিসাইকেল বিনে কোনো পণ্য নেই।</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td class="ps-4">
                                    <img src="<?= get_upload_url($p['image'], 'products') ?>" class="rounded object-fit-cover" width="40" height="40" alt="">
                                </td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($p['name']) ?></div>
                                    <small class="text-muted">SKU: <?= htmlspecialchars($p['sku']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($p['category_name']) ?></td>
                                <td>৳<?= number_format($p['price'], 2) ?></td>
                                <td><small class="text-danger"><?= date('d M, Y h:i A', strtotime($p['deleted_at'])) ?></small></td>
                                <td class="text-end pe-4">
                                    <form method="POST" action="" class="d-inline-block m-0" onsubmit="return confirm('আপনি কি নিশ্চিত যে পণ্যটি রিস্টোর করতে চান?');">
                                        <input type="hidden" name="action" value="restore">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success" title="রিস্টোর করুন"><i class="bi bi-arrow-counterclockwise"></i></button>
                                    </form>
                                    <form method="POST" action="" class="d-inline-block m-0 ms-1" onsubmit="return confirm('আপনি কি নিশ্চিত যে পণ্যটি স্থায়ীভাবে মুছে ফেলতে চান? এটি আর ফেরত পাওয়া যাবে না।');">
                                        <input type="hidden" name="action" value="force_delete">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="স্থায়ীভাবে মুছুন"><i class="bi bi-trash-fill"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($total_records > $limit): ?>
        <div class="card-footer bg-white border-top p-3">
            <?= $pagination ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
