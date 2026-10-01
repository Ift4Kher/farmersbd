<?php
// ============================================================
// FarmersBD — Admin Lead Recovery & Abandoned Carts
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

// Handle status / note updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verify_csrf();

    if ($_POST['action'] === 'update_status') {
        $lead_id    = (int)($_POST['lead_id'] ?? 0);
        $status     = sanitize_input($_POST['status'] ?? 'new');
        $call_notes = sanitize_input($_POST['call_notes'] ?? '');

        if ($lead_id > 0) {
            $stmt = $pdo->prepare("UPDATE lead_recovery SET status = ?, call_notes = ?, last_contacted_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $call_notes, $lead_id]);
            set_flash('success', 'লিড স্ট্যাটাস সফলভাবে আপডেট করা হয়েছে।');
        }
        redirect(BASE_URL . '/admin/lead-recovery/');
    }

    if ($_POST['action'] === 'add_lead') {
        $customer_name = sanitize_input($_POST['customer_name'] ?? '');
        $phone         = sanitize_input($_POST['phone'] ?? '');
        $address       = sanitize_input($_POST['address'] ?? '');
        $total_amount  = (float)($_POST['total_amount'] ?? 0);
        $call_notes    = sanitize_input($_POST['call_notes'] ?? '');

        if (empty($phone)) {
            set_flash('error', 'মোবাইল নম্বর আবশ্যক।');
        } else {
            $stmt = $pdo->prepare("INSERT INTO lead_recovery (customer_name, phone, address, total_amount, status, call_notes, created_at) VALUES (?, ?, ?, ?, 'new', ?, NOW())");
            $stmt->execute([$customer_name, $phone, $address, $total_amount, $call_notes]);
            set_flash('success', 'নতুন লিড যোগ করা হয়েছে।');
        }
        redirect(BASE_URL . '/admin/lead-recovery/');
    }

    if ($_POST['action'] === 'delete_lead') {
        $lead_id = (int)($_POST['lead_id'] ?? 0);
        if ($lead_id > 0) {
            $pdo->prepare("DELETE FROM lead_recovery WHERE id = ?")->execute([$lead_id]);
            set_flash('success', 'লিড মুছে ফেলা হয়েছে।');
        }
        redirect(BASE_URL . '/admin/lead-recovery/');
    }
}

// Filters & Search
$search = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 15;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(customer_name LIKE ? OR phone LIKE ? OR address LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status !== '') {
    $where[] = "status = ?";
    $params[] = $status;
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM lead_recovery $where_sql");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();

$sql = "SELECT * FROM lead_recovery $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll();

// Overview Stats
$stats_stmt = $pdo->query("SELECT 
    COUNT(*) as total_leads,
    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_leads,
    SUM(CASE WHEN status = 'follow_up' THEN 1 ELSE 0 END) as follow_up_leads,
    SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted_leads
    FROM lead_recovery");
$stats = $stats_stmt->fetch();
$total_leads     = (int)($stats['total_leads'] ?? 0);
$new_leads       = (int)($stats['new_leads'] ?? 0);
$follow_up_leads = (int)($stats['follow_up_leads'] ?? 0);
$converted_leads = (int)($stats['converted_leads'] ?? 0);
$recovery_rate   = $total_leads > 0 ? round(($converted_leads / $total_leads) * 100, 1) : 0;

$page_title = 'লিড রিকভারি | FarmersBD Admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-arrow-repeat text-primary me-2"></i>লিড রিকভারি ও অসম্পূর্ণ অর্ডার</h1>
            <p class="text-muted small mb-0">অসম্পূর্ণ চেকআউট ও পরিত্যক্ত লিডগুলো ট্র্যাকিং করে বিক্রয়ে রূপান্তর করুন</p>
        </div>
        <button type="button" class="btn btn-primary px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addLeadModal">
            <i class="bi bi-plus-circle me-1"></i> নতুন লিড এন্ট্রি
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary-subtle text-primary p-3 fs-4"><i class="bi bi-people"></i></div>
                <div>
                    <div class="text-muted small">মোট লিড</div>
                    <div class="fs-4 fw-bold"><?= $total_leads ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-warning-subtle text-warning p-3 fs-4"><i class="bi bi-bell"></i></div>
                <div>
                    <div class="text-muted small">নতুন লিড (New)</div>
                    <div class="fs-4 fw-bold text-warning"><?= $new_leads ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-info-subtle text-info p-3 fs-4"><i class="bi bi-telephone-outbound"></i></div>
                <div>
                    <div class="text-muted small">ফলো-আপ প্রয়োজন</div>
                    <div class="fs-4 fw-bold text-info"><?= $follow_up_leads ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success-subtle text-success p-3 fs-4"><i class="bi bi-check2-all"></i></div>
                <div>
                    <div class="text-muted small">কনভার্ট হয়েছে (<?= $recovery_rate ?>%)</div>
                    <div class="fs-4 fw-bold text-success"><?= $converted_leads ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="নাম, ফোন অথবা ঠিকানা দিয়ে খুঁজুন..." value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">সকল স্ট্যাটাস</option>
                    <option value="new" <?= $status === 'new' ? 'selected' : '' ?>>নতুন (New)</option>
                    <option value="contacted" <?= $status === 'contacted' ? 'selected' : '' ?>>যোগাযোগকৃত (Contacted)</option>
                    <option value="follow_up" <?= $status === 'follow_up' ? 'selected' : '' ?>>ফলো-আপ (Follow Up)</option>
                    <option value="converted" <?= $status === 'converted' ? 'selected' : '' ?>>কনভার্টেড (Converted)</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i> ফিল্টার</button>
                <?php if ($search !== '' || $status !== ''): ?>
                    <a href="<?= url('admin/lead-recovery/') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Leads Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">গ্রাহকের নাম ও ফোন</th>
                        <th>ঠিকানা / বিবরণ</th>
                        <th>সম্ভাব্য মূল্য</th>
                        <th>স্ট্যাটাস</th>
                        <th>নোট ও শেষ যোগাযোগ</th>
                        <th>রেকর্ড সময়</th>
                        <th class="text-end pe-3">কুইক অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leads)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block text-secondary mb-2 opacity-50"></i>
                                কোনো লিড পাওয়া যায়নি।
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leads as $l): ?>
                            <?php
                            $phone_clean = preg_replace('/[^0-9]/', '', $l['phone']);
                            if (str_starts_with($phone_clean, '0')) {
                                $wa_phone = '88' . $phone_clean;
                            } elseif (str_starts_with($phone_clean, '880')) {
                                $wa_phone = $phone_clean;
                            } else {
                                $wa_phone = '880' . $phone_clean;
                            }
                            $wa_msg = urlencode("আসসালামু আলাইকুম " . ($l['customer_name'] ?: 'স্যার') . ", FarmersBD থেকে যোগাযোগ করছি। আপনার অর্ডারের বিষয়ে কি কোনো সাহায্য প্রয়োজন?");
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark"><?= e($l['customer_name'] ?: 'অজ্ঞাত গ্রাহক') ?></div>
                                    <div class="font-monospace text-primary small">
                                        <i class="bi bi-telephone me-1"></i><?= e($l['phone']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="small text-muted" style="max-width: 250px;">
                                        <?= e($l['address'] ?: 'ঠিকানা দেওয়া হয়নি') ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-success">
                                        <?= format_price($l['total_amount'] ?? 0) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($l['status'] === 'new'): ?>
                                        <span class="badge bg-warning text-dark">নতুন</span>
                                    <?php elseif ($l['status'] === 'contacted'): ?>
                                        <span class="badge bg-info">যোগাযোগ হয়েছে</span>
                                    <?php elseif ($l['status'] === 'follow_up'): ?>
                                        <span class="badge bg-primary">ফলো-আপ</span>
                                    <?php elseif ($l['status'] === 'converted'): ?>
                                        <span class="badge bg-success">অর্ডারে রূপান্তরিত</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small text-dark fw-medium"><?= e($l['call_notes'] ?: 'কোনো নোট নেই') ?></div>
                                    <?php if (!empty($l['last_contacted_at'])): ?>
                                        <div class="text-muted" style="font-size:0.75rem;">
                                            <i class="bi bi-clock me-1"></i><?= date('d M, h:i A', strtotime($l['last_contacted_at'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small">
                                    <?= date('d M, Y', strtotime($l['created_at'])) ?>
                                    <div style="font-size: 0.75rem;"><?= date('h:i A', strtotime($l['created_at'])) ?></div>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="tel:<?= e($l['phone']) ?>" class="btn btn-outline-primary" title="কল করুন">
                                            <i class="bi bi-telephone-fill"></i>
                                        </a>
                                        <a href="https://wa.me/<?= $wa_phone ?>?text=<?= $wa_msg ?>" target="_blank" class="btn btn-outline-success" title="WhatsApp বার্তা পাঠান">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-secondary edit-lead-btn" 
                                            data-id="<?= $l['id'] ?>"
                                            data-name="<?= e($l['customer_name']) ?>"
                                            data-status="<?= e($l['status']) ?>"
                                            data-notes="<?= e($l['call_notes']) ?>"
                                            title="স্ট্যাটাস আপডেট">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('এই লিডটি মুছে ফেলতে চান?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete_lead">
                                            <input type="hidden" name="lead_id" value="<?= $l['id'] ?>">
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
            <?= render_pagination($page, ceil($total_rows / $limit), url('admin/lead-recovery/?q=' . urlencode($search) . '&status=' . urlencode($status))) ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Add Lead -->
<div class="modal fade" id="addLeadModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add_lead">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle text-primary me-2"></i>নতুন লিড এন্ট্রি</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">গ্রাহকের নাম</label>
                    <input type="text" name="customer_name" class="form-control" placeholder="নাম লিখুন">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">মোবাইল নম্বর <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control" placeholder="017xxxxxxxx" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">ঠিকানা</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="গ্রাহকের ঠিকানা..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">সম্ভাব্য মূল্য (৳)</label>
                    <input type="number" step="0.01" name="total_amount" class="form-control" placeholder="0.00">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">নোট / বিস্তারিত</label>
                    <textarea name="call_notes" class="form-control" rows="2" placeholder="গ্রাহকের আগ্রহ বা মন্তব্য..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="btn btn-primary">সংরক্ষণ করুন</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Update Lead Status & Notes -->
<div class="modal fade" id="updateLeadModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="lead_id" id="modalLeadId">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>লিড স্ট্যাটাস ও নোট আপডেট</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">গ্রাহক: <span id="modalLeadCustomer" class="text-primary"></span></label>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">স্ট্যাটাস</label>
                    <select name="status" id="modalLeadStatus" class="form-select">
                        <option value="new">নতুন (New)</option>
                        <option value="contacted">যোগাযোগকৃত (Contacted)</option>
                        <option value="follow_up">ফলো-আপ (Follow Up)</option>
                        <option value="converted">কনভার্টেড (Converted to Order)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">কল নোট / ফলো-আপ সারাংশ</label>
                    <textarea name="call_notes" id="modalLeadNotes" class="form-control" rows="3" placeholder="গ্রাহকের সাথে কি কথা হলো লিখুন..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="btn btn-primary">আপডেট করুন</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.edit-lead-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const name = this.dataset.name || 'গ্রাহক';
        const status = this.dataset.status;
        const notes = this.dataset.notes;

        document.getElementById('modalLeadId').value = id;
        document.getElementById('modalLeadCustomer').textContent = name;
        document.getElementById('modalLeadStatus').value = status;
        document.getElementById('modalLeadNotes').value = notes;

        new bootstrap.Modal(document.getElementById('updateLeadModal')).show();
    });
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
