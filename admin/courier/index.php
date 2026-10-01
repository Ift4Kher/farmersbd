<?php
// ============================================================
// FarmersBD — Admin Courier & Shipment Management
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/config/constants.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';
require_once $base . '/includes/csrf.php';
require_once $base . '/includes/flash.php';
require_once $base . '/includes/pagination.php';

require_admin();
$pdo = get_db_connection();

// Handle Shipment Creation or Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verify_csrf();

    if ($_POST['action'] === 'create_shipment') {
        $order_id        = (int)($_POST['order_id'] ?? 0);
        $courier_name    = sanitize_input($_POST['courier_name'] ?? '');
        $tracking_number = sanitize_input($_POST['tracking_number'] ?? '');
        $tracking_url    = sanitize_input($_POST['tracking_url'] ?? '');
        $status          = sanitize_input($_POST['status'] ?? 'pending');
        $notes           = sanitize_input($_POST['notes'] ?? '');

        // Generate default tracking URL if empty based on courier
        if (empty($tracking_url) && !empty($tracking_number)) {
            if (stripos($courier_name, 'steadfast') !== false) {
                $tracking_url = 'https://steadfast.com.bd/t/' . $tracking_number;
            } elseif (stripos($courier_name, 'redx') !== false) {
                $tracking_url = 'https://redx.com.bd/track-order?trackingId=' . $tracking_number;
            } elseif (stripos($courier_name, 'pathao') !== false) {
                $tracking_url = 'https://pathao.com/courier/tracking/?consignment_id=' . $tracking_number;
            }
        }

        if ($order_id > 0 && !empty($courier_name)) {
            $stmt = $pdo->prepare("INSERT INTO shipments (order_id, courier_name, tracking_number, tracking_url, status, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$order_id, $courier_name, $tracking_number, $tracking_url, $status, $notes]);

            // If status is shipped, update order status as well
            if (in_array($status, ['shipped', 'in_transit'])) {
                $pdo->prepare("UPDATE orders SET order_status = 'shipped' WHERE id = ?")->execute([$order_id]);
            }

            set_flash('success', 'নতুন শিপমেন্ট সফলভাবে এন্ট্রি হয়েছে।');
        } else {
            set_flash('error', 'সঠিক অর্ডার আইডি ও কুরিয়ার নাম নির্বাচন করুন।');
        }
        redirect(BASE_URL . '/admin/courier/');
    }

    if ($_POST['action'] === 'update_status') {
        $shipment_id = (int)($_POST['shipment_id'] ?? 0);
        $status      = sanitize_input($_POST['status'] ?? 'pending');
        $tracking    = sanitize_input($_POST['tracking_number'] ?? '');
        $notes       = sanitize_input($_POST['notes'] ?? '');

        if ($shipment_id > 0) {
            $shipped_at = in_array($status, ['shipped', 'in_transit']) ? date('Y-m-d') : null;
            $delivered_at = ($status === 'delivered') ? date('Y-m-d') : null;

            $stmt = $pdo->prepare("UPDATE shipments SET status = ?, tracking_number = ?, notes = ?, 
                shipped_at = COALESCE(?, shipped_at), 
                delivered_at = COALESCE(?, delivered_at) 
                WHERE id = ?");
            $stmt->execute([$status, $tracking, $notes, $shipped_at, $delivered_at, $shipment_id]);

            // Update corresponding order status
            $ship_stmt = $pdo->prepare("SELECT order_id FROM shipments WHERE id = ?");
            $ship_stmt->execute([$shipment_id]);
            $order_id = $ship_stmt->fetchColumn();

            if ($order_id) {
                if ($status === 'delivered') {
                    $pdo->prepare("UPDATE orders SET order_status = 'delivered', payment_status = 'paid' WHERE id = ?")->execute([$order_id]);
                } elseif ($status === 'shipped' || $status === 'in_transit') {
                    $pdo->prepare("UPDATE orders SET order_status = 'shipped' WHERE id = ?")->execute([$order_id]);
                } elseif ($status === 'returned') {
                    $pdo->prepare("UPDATE orders SET order_status = 'returned' WHERE id = ?")->execute([$order_id]);
                } elseif ($status === 'cancelled') {
                    $pdo->prepare("UPDATE orders SET order_status = 'cancelled' WHERE id = ?")->execute([$order_id]);
                }
            }

            set_flash('success', 'শিপমেন্ট স্ট্যাটাস আপডেট হয়েছে।');
        }
        redirect(BASE_URL . '/admin/courier/');
    }

    if ($_POST['action'] === 'delete_shipment') {
        $shipment_id = (int)($_POST['shipment_id'] ?? 0);
        if ($shipment_id > 0) {
            $pdo->prepare("DELETE FROM shipments WHERE id = ?")->execute([$shipment_id]);
            set_flash('success', 'শিপমেন্ট রেকর্ড মুছে ফেলা হয়েছে।');
        }
        redirect(BASE_URL . '/admin/courier/');
    }
}

// Search & Filter
$search  = trim($_GET['q'] ?? '');
$courier = trim($_GET['courier'] ?? '');
$status  = trim($_GET['status'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$limit   = 15;
$offset  = ($page - 1) * $limit;

$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(s.tracking_number LIKE ? OR o.order_number LIKE ? OR o.customer_name LIKE ? OR o.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($courier !== '') {
    $where[] = "s.courier_name = ?";
    $params[] = $courier;
}

if ($status !== '') {
    $where[] = "s.status = ?";
    $params[] = $status;
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM shipments s LEFT JOIN orders o ON o.id = s.order_id $where_sql");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();

$sql = "SELECT s.*, o.order_number, o.customer_name, o.phone, o.district, o.total_amount, o.payment_method, o.order_status 
        FROM shipments s 
        LEFT JOIN orders o ON o.id = s.order_id 
        $where_sql 
        ORDER BY s.id DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$shipments = $stmt->fetchAll();

// Stats
$stats_stmt = $pdo->query("SELECT 
    COUNT(*) as total_shipments,
    SUM(CASE WHEN status IN ('pending', 'processing') THEN 1 ELSE 0 END) as pending_dispatch,
    SUM(CASE WHEN status IN ('shipped', 'in_transit') THEN 1 ELSE 0 END) as in_transit,
    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
    SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned
    FROM shipments");
$stats = $stats_stmt->fetch();

// Get recent pending/processing orders for quick shipment creation dropdown
$orders_stmt = $pdo->query("SELECT id, order_number, customer_name, total_amount FROM orders WHERE order_status IN ('pending', 'processing', 'confirmed') ORDER BY id DESC LIMIT 50");
$recent_orders = $orders_stmt->fetchAll();

$page_title = 'কুরিয়ার ও ডেলিভারি ম্যানেজমেন্ট | FarmersBD Admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-truck text-primary me-2"></i>কুরিয়ার ও পার্সেল ম্যানেজমেন্ট</h1>
            <p class="text-muted small mb-0">Steadfast, Pathao, RedX ও অন্যান্য কুরিয়ারে বুকিং ও পার্সেল ট্র্যাকিং পরিচালনা</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#newShipmentModal">
                <i class="bi bi-plus-circle me-1"></i> নতুন শিপমেন্ট বুকিং
            </button>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-secondary-subtle text-secondary p-3 fs-4"><i class="bi bi-box-seam"></i></div>
                <div>
                    <div class="text-muted small">মোট শিপমেন্ট</div>
                    <div class="fs-4 fw-bold"><?= (int)($stats['total_shipments'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-warning-subtle text-warning p-3 fs-4"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="text-muted small">ডেসপ্যাচ অপেক্ষমাণ</div>
                    <div class="fs-4 fw-bold text-warning"><?= (int)($stats['pending_dispatch'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary-subtle text-primary p-3 fs-4"><i class="bi bi-truck"></i></div>
                <div>
                    <div class="text-muted small">ট্রানজিটে আছে (In Transit)</div>
                    <div class="fs-4 fw-bold text-primary"><?= (int)($stats['in_transit'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success-subtle text-success p-3 fs-4"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="text-muted small">ডেলিভার্ড সফল</div>
                    <div class="fs-4 fw-bold text-success"><?= (int)($stats['delivered'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="ট্র্যাকিং নং, অর্ডার নং বা গ্রাহক..." value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="courier" class="form-select">
                    <option value="">সকল কুরিয়ার</option>
                    <option value="Steadfast" <?= $courier === 'Steadfast' ? 'selected' : '' ?>>Steadfast Courier</option>
                    <option value="Pathao" <?= $courier === 'Pathao' ? 'selected' : '' ?>>Pathao Courier</option>
                    <option value="RedX" <?= $courier === 'RedX' ? 'selected' : '' ?>>RedX Delivery</option>
                    <option value="Paperfly" <?= $courier === 'Paperfly' ? 'selected' : '' ?>>Paperfly</option>
                    <option value="Sundarban" <?= $courier === 'Sundarban' ? 'selected' : '' ?>>Sundarban Courier</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">সকল স্ট্যাটাস</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="shipped" <?= $status === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="in_transit" <?= $status === 'in_transit' ? 'selected' : '' ?>>In Transit</option>
                    <option value="delivered" <?= $status === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="returned" <?= $status === 'returned' ? 'selected' : '' ?>>Returned</option>
                    <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel"></i></button>
                <?php if ($search !== '' || $courier !== '' || $status !== ''): ?>
                    <a href="<?= url('admin/courier/') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Shipments Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">অর্ডার নং</th>
                        <th>গ্রাহকের তথ্য</th>
                        <th>কুরিয়ার সার্ভিস</th>
                        <th>ট্র্যাকিং নম্বর</th>
                        <th>শিপমেন্ট স্ট্যাটাস</th>
                        <th>তারিখ</th>
                        <th class="text-end pe-3">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($shipments)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-truck display-5 d-block text-secondary mb-2 opacity-50"></i>
                                কোনো শিপমেন্ট তথ্য পাওয়া যায়নি।
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($shipments as $s): ?>
                            <tr>
                                <td class="ps-3">
                                    <a href="<?= url('admin/orders/view.php?id=' . $s['order_id']) ?>" class="fw-bold text-decoration-none">
                                        #<?= e($s['order_number'] ?: $s['order_id']) ?>
                                    </a>
                                    <div class="text-success small fw-semibold"><?= format_price($s['total_amount'] ?? 0) ?></div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark"><?= e($s['customer_name']) ?></div>
                                    <div class="text-muted small font-monospace"><?= e($s['phone']) ?></div>
                                    <div class="text-secondary small"><?= e($s['district']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-building me-1"></i><?= e($s['courier_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($s['tracking_number'])): ?>
                                        <div class="font-monospace fw-semibold text-primary"><?= e($s['tracking_number']) ?></div>
                                        <?php if (!empty($s['tracking_url'])): ?>
                                            <a href="<?= e($s['tracking_url']) ?>" target="_blank" class="small text-decoration-none text-muted">
                                                <i class="bi bi-box-arrow-up-right me-1"></i>ট্র্যাক করুন
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">ট্র্যাকিং যোগ করা হয়নি</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $badge = 'secondary';
                                    if ($s['status'] === 'delivered') $badge = 'success';
                                    elseif (in_array($s['status'], ['shipped', 'in_transit'])) $badge = 'primary';
                                    elseif (in_array($s['status'], ['pending', 'processing'])) $badge = 'warning text-dark';
                                    elseif (in_array($s['status'], ['returned', 'cancelled'])) $badge = 'danger';
                                    ?>
                                    <span class="badge bg-<?= $badge ?>"><?= strtoupper(e($s['status'])) ?></span>
                                </td>
                                <td class="text-muted small">
                                    <?= date('d M, Y', strtotime($s['created_at'])) ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary update-shipment-btn"
                                            data-id="<?= $s['id'] ?>"
                                            data-order="<?= e($s['order_number'] ?: $s['order_id']) ?>"
                                            data-tracking="<?= e($s['tracking_number']) ?>"
                                            data-status="<?= e($s['status']) ?>"
                                            data-notes="<?= e($s['notes']) ?>"
                                            title="স্ট্যাটাস পরিবর্তন">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('এই শিপমেন্ট রেকর্ডটি মুছে ফেলতে চান?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete_shipment">
                                            <input type="hidden" name="shipment_id" value="<?= $s['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger" title="মুছে ফেলুন">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($total_rows > $limit): ?>
        <div class="card-footer bg-white border-0 py-3">
            <?= render_pagination($page, ceil($total_rows / $limit), url('admin/courier/?q=' . urlencode($search) . '&courier=' . urlencode($courier) . '&status=' . urlencode($status))) ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: New Shipment Booking -->
<div class="modal fade" id="newShipmentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create_shipment">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle text-primary me-2"></i>নতুন শিপমেন্ট বুকিং</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">অর্ডার নির্বাচন করুন <span class="text-danger">*</span></label>
                    <select name="order_id" class="form-select" required>
                        <option value="">-- অর্ডার বাছাই করুন --</option>
                        <?php foreach ($recent_orders as $ro): ?>
                            <option value="<?= $ro['id'] ?>">
                                #<?= e($ro['order_number']) ?> - <?= e($ro['customer_name']) ?> (<?= format_price($ro['total_amount']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">কুরিয়ার কোম্পানি <span class="text-danger">*</span></label>
                    <select name="courier_name" class="form-select" required>
                        <option value="Steadfast">Steadfast Courier</option>
                        <option value="Pathao">Pathao Courier</option>
                        <option value="RedX">RedX Delivery</option>
                        <option value="Paperfly">Paperfly</option>
                        <option value="Sundarban">Sundarban Courier</option>
                        <option value="eCourier">eCourier</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">ট্র্যাকিং কোড / কনসাইনমেন্ট আইডি</label>
                    <input type="text" name="tracking_number" class="form-control font-monospace" placeholder="যেমন: ST12345678">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">কাস্টম ট্র্যাকিং URL (ঐচ্ছিক)</label>
                    <input type="url" name="tracking_url" class="form-control" placeholder="https://...">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">প্রাথমিক স্ট্যাটাস</label>
                    <select name="status" class="form-select">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped" selected>Shipped</option>
                        <option value="in_transit">In Transit</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">নোট</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="পার্সেল সম্পর্কিত বিশেষ নির্দেশনা..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="btn btn-primary">শিপমেন্ট তৈরি করুন</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Update Shipment Status -->
<div class="modal fade" id="updateShipmentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="shipment_id" id="modalShipmentId">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>শিপমেন্ট স্ট্যাটাস পরিবর্তন</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">অর্ডার নং: <span id="modalShipmentOrder" class="text-primary fw-bold"></span></label>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">ট্র্যাকিং নম্বর</label>
                    <input type="text" name="tracking_number" id="modalShipmentTracking" class="form-control font-monospace">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">স্ট্যাটাস</label>
                    <select name="status" id="modalShipmentStatus" class="form-select">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="in_transit">In Transit</option>
                        <option value="delivered">Delivered (সম্পন্ন)</option>
                        <option value="returned">Returned (ফেরত)</option>
                        <option value="cancelled">Cancelled (বাতিল)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">নোট</label>
                    <textarea name="notes" id="modalShipmentNotes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="btn btn-primary">আপডেট সংরক্ষণ করুন</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.update-shipment-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('modalShipmentId').value = this.dataset.id;
        document.getElementById('modalShipmentOrder').textContent = '#' + this.dataset.order;
        document.getElementById('modalShipmentTracking').value = this.dataset.tracking;
        document.getElementById('modalShipmentStatus').value = this.dataset.status;
        document.getElementById('modalShipmentNotes').value = this.dataset.notes;

        new bootstrap.Modal(document.getElementById('updateShipmentModal')).show();
    });
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
