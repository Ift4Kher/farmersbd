<?php
// ============================================================
// FarmersBD — User Orders Listing Page
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

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$count_stmt->execute([$user_id]);
$total_records = $count_stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll();

$pagination = paginate($total_records, $limit, $page, BASE_URL . '/account/orders.php');

$page_title = "আমার অর্ডারসমূহ — " . APP_NAME;
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-md-3">
            <?php include dirname(__DIR__) . '/includes/account-sidebar.php'; ?>
        </div>

        <!-- Orders Main -->
        <div class="col-md-9">
            <div class="modern-card p-0">
                <div class="card-header bg-transparent py-3 border-bottom">
                    <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-bag-check text-primary me-2"></i> আমার অর্ডার ইতিহাস</h4>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($orders)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-bag-x text-muted display-4"></i>
                            <p class="mt-3 text-muted">আপনার কোনো অর্ডার পাওয়া যায়নি।</p>
                            <a href="<?= BASE_URL ?>/products/index.php" class="btn btn-primary btn-sm mt-2"><i class="bi bi-shop me-1"></i> কেনাকাটা শুরু করুন</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>অর্ডার নং</th>
                                        <th>তারিখ</th>
                                        <th>পেমেন্ট মেথড</th>
                                        <th>মোট মূল্য</th>
                                        <th>পেমেন্ট স্ট্যাটাস</th>
                                        <th>অর্ডার স্ট্যাটাস</th>
                                        <th class="text-end">অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $ord): ?>
                                        <tr>
                                            <td class="fw-bold">#<?= h($ord['order_number']) ?></td>
                                            <td class="small text-muted"><?= format_date_bn($ord['created_at']) ?></td>
                                            <td><?= strtoupper(h($ord['payment_method'])) ?></td>
                                            <td class="fw-bold text-success"><?= format_price_bn($ord['total'] ?? $ord['grand_total'] ?? 0) ?></td>
                                            <td><?= get_payment_status_badge($ord['payment_status']) ?></td>
                                            <td><?= get_order_status_badge($ord['order_status']) ?></td>
                                            <td class="text-end">
                                                <a href="<?= BASE_URL ?>/account/order-details.php?id=<?= $ord['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> বিস্তারিত</a>
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
