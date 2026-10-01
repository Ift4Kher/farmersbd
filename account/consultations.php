<?php
// ============================================================
// FarmersBD — User Consultations Listing Page
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/pagination.php';

require_login();
$user_id = current_user_id();
$pdo = get_db_connection();

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM consultations WHERE user_id = ?");
$count_stmt->execute([$user_id]);
$total_records = $count_stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT * FROM consultations WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$consultations = $stmt->fetchAll();

$pagination = paginate($total_records, $limit, $page, BASE_URL . '/account/consultations.php');

$page_title = "আমার বিশেষজ্ঞ পরামর্শ — " . APP_NAME;
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-md-3">
            <?php include dirname(__DIR__) . '/includes/account-sidebar.php'; ?>
        </div>

        <!-- Consultations Main -->
        <div class="col-md-9">
            <div class="modern-card p-0">
                <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-chat-dots-fill text-primary me-2"></i> আমার বিশেষজ্ঞ পরামর্শসমূহ</h4>
                    <a href="<?= BASE_URL ?>/consultation/create.php" class="btn btn-modern btn-sm"><i class="bi bi-plus-circle me-1"></i> নতুন পরামর্শ চান</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($consultations)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-chat-square-dots text-muted display-4"></i>
                            <p class="mt-3 text-muted">আপনি এখনও কোনো পরামর্শ আবেদন করেননি।</p>
                            <a href="<?= BASE_URL ?>/consultation/create.php" class="btn btn-primary btn-sm mt-2"><i class="bi bi-pencil-square me-1"></i> নতুন প্রশ্ন জমা দিন</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>বিষয়</th>
                                        <th>মাছের নাম</th>
                                        <th>তারিখ</th>
                                        <th>স্ট্যাটাস</th>
                                        <th class="text-end">অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($consultations as $c): ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><?= h($c['subject']) ?></td>
                                            <td><?= h($c['fish_type'] ?? 'সাধারণ') ?></td>
                                            <td class="small text-muted"><?= format_date_bn($c['created_at']) ?></td>
                                            <td><?= get_consultation_status_badge($c['status']) ?></td>
                                            <td class="text-end">
                                                <a href="<?= BASE_URL ?>/consultation/details.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> বিবরণ</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3">
                            <?= render_pagination($pagination) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
