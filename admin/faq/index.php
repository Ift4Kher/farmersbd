<?php
// ============================================================
// FarmersBD — Admin FAQ Management (Listing)
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/flash.php';

require_admin();
$pdo = get_db_connection();

$stmt = $pdo->query("SELECT * FROM faqs ORDER BY sort_order ASC, id DESC");
$faqs = $stmt->fetchAll();

$page_title = "সাধারণ জিজ্ঞাসাবলি (FAQ) — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-question-circle text-primary me-2"></i> সাধারণ জিজ্ঞাসাবলি (FAQ) তালিকা</h3>
    <a href="<?= BASE_URL ?>/admin/faq/add.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> নতুন প্রশ্ন-উত্তর যোগ করুন</a>
</div>

<?php display_flash(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ক্রম</th>
                        <th>প্রশ্ন</th>
                        <th>উত্তর</th>
                        <th>স্ট্যাটাস</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($faqs)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">কোনো FAQ যুক্ত নেই।</td></tr>
                    <?php else: ?>
                        <?php foreach ($faqs as $faq): ?>
                            <tr>
                                <td class="fw-bold"><?= $faq['sort_order'] ?></td>
                                <td class="fw-bold text-dark me-2"><?= h($faq['question']) ?></td>
                                <td class="text-muted small line-clamp-2"><?= mb_strimwidth(h($faq['answer']), 0, 90, '...') ?></td>
                                <td>
                                    <span class="badge <?= ($faq['is_active'] ?? 1) == 1 ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= ($faq['is_active'] ?? 1) == 1 ? 'ACTIVE' : 'INACTIVE' ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/admin/faq/edit.php?id=<?= $faq['id'] ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                                    <a href="<?= BASE_URL ?>/admin/faq/delete.php?id=<?= $faq['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('আপনি কি নিশ্চিত যে এই প্রশ্নটি মুছে ফেলতে চান?');"><i class="bi bi-trash"></i></a>
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
