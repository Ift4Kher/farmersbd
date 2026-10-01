<?php
// ============================================================
// FarmersBD — User AI Diagnosis History Page
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
$limit = 9;
$offset = ($page - 1) * $limit;

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM ai_diagnoses WHERE user_id = ?");
$count_stmt->execute([$user_id]);
$total_records = $count_stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT ad.*, d.name_bn as disease_name_bn, d.name_en as disease_name_en, d.slug as disease_slug FROM ai_diagnoses ad LEFT JOIN diseases d ON ad.disease_id = d.id WHERE ad.user_id = ? ORDER BY ad.created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$history = $stmt->fetchAll();

$pagination = paginate($total_records, $limit, $page, BASE_URL . '/account/ai-history.php');

$page_title = "AI রোগ শনাক্তকরণ ইতিহাস — " . APP_NAME;
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-md-3">
            <?php include dirname(__DIR__) . '/includes/account-sidebar.php'; ?>
        </div>

        <!-- AI History Main -->
        <div class="col-md-9">
            <div class="modern-card p-0">
                <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-cpu-fill text-primary me-2"></i> AI রোগ শনাক্তকরণ ইতিহাস</h4>
                    <a href="<?= BASE_URL ?>/ai/index.php" class="btn btn-modern btn-sm"><i class="bi bi-camera me-1"></i> নতুন পরীক্ষা করুন</a>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($history)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-search text-muted display-4"></i>
                            <p class="mt-3 text-muted">আপনি এখনও কোনো মাছের রোগ AI দ্বারা পরীক্ষা করেননি।</p>
                            <a href="<?= BASE_URL ?>/ai/index.php" class="btn btn-primary btn-sm mt-2"><i class="bi bi-magic me-1"></i> এখনই রোগ পরীক্ষা করুন</a>
                        </div>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($history as $item): ?>
                                <div class="col-md-4">
                                    <div class="modern-card h-100 overflow-hidden p-0">
                                        <img src="<?= get_upload_url($item['image_path'], 'ai') ?>" class="card-img-top" style="height: 160px; object-fit: cover;" alt="Diagnosed image">
                                        <div class="card-body">
                                            <span class="badge bg-success mb-2"><?= format_number_bn($item['confidence']) ?>% নিশ্চিততা</span>
                                            <h6 class="fw-bold text-dark mb-1">
                                                <?= h($item['disease_name_bn'] ?? 'অজানা রোগ') ?>
                                            </h6>
                                            <p class="small text-muted mb-2"><i class="bi bi-calendar3 me-1"></i> <?= format_date_bn($item['created_at']) ?></p>
                                            <?php if (!empty($item['disease_slug'])): ?>
                                                <a href="<?= BASE_URL ?>/diseases/details.php?slug=<?= h($item['disease_slug']) ?>" class="btn btn-outline-primary btn-sm w-100 mt-2">রোগের চিকিৎসা দেখুন</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-4">
                            <?= render_pagination($pagination) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
