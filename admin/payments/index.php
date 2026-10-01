<?php
// ============================================================
// FarmersBD — Admin Payments Overview
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/pagination.php';

require_admin();
$pdo = get_db_connection();

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$count_stmt = $pdo->query("SELECT COUNT(*) FROM payments");
$total_records = $count_stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT p.*, o.order_number, o.customer_name FROM payments p LEFT JOIN orders o ON p.order_id = o.id ORDER BY p.id DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$payments = $stmt->fetchAll();

$pagination = paginate($total_records, $limit, $page, BASE_URL . '/admin/payments/index.php');

$page_title = "পেমেন্ট লেনদেন ইতিহাস — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-credit-card-2-front text-primary me-2"></i> পেমেন্ট ও লেনদেন ইতিহাস</h3>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ট্রানজেকশন আইডি</th>
                        <th>অর্ডার নং</th>
                        <th>গ্রাহক</th>
                        <th>মেথড</th>
                        <th>পরিমাণ</th>
                        <th>স্ট্যাটাস</th>
                        <th>তারিখ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">কোনো লেনদেন রেকর্ড পাওয়া যায়নি।</td></tr>
                    <?php else: ?>
                        <?php foreach ($payments as $pay): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?= h($pay['transaction_id']) ?></td>
                                <td>#<?= h($pay['order_number'] ?? 'N/A') ?></td>
                                <td><?= h($pay['customer_name'] ?? 'N/A') ?></td>
                                <td><?= strtoupper(h($pay['payment_method'])) ?></td>
                                <td class="fw-bold text-success"><?= format_price_bn($pay['amount']) ?></td>
                                <td><?= get_payment_status_badge($pay['status']) ?></td>
                                <td class="small text-muted"><?= format_date_bn($pay['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="p-3">
            <?= render_pagination($pagination) ?>
        </div>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
