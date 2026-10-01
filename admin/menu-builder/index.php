<?php
// ============================================================
// FarmersBD — Admin Menu Builder
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

$selected_menu = sanitize_input($_GET['menu'] ?? 'main');

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verify_csrf();

    if ($_POST['action'] === 'add_item') {
        $menu       = sanitize_input($_POST['menu'] ?? 'main');
        $label      = sanitize_input($_POST['label'] ?? '');
        $url_val    = sanitize_input($_POST['url'] ?? '');
        $parent_id  = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $target     = sanitize_input($_POST['target'] ?? '_self');
        $is_active  = isset($_POST['is_active']) ? 1 : 0;

        if (empty($label) || empty($url_val)) {
            set_flash('error', 'মেনু লেবেল এবং লিংক উভয়ই আবশ্যক।');
        } else {
            $stmt = $pdo->prepare("INSERT INTO menu_items (menu, label, url, parent_id, sort_order, target, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$menu, $label, $url_val, $parent_id, $sort_order, $target, $is_active]);
            set_flash('success', 'মেনু আইটেম সফলভাবে যোগ করা হয়েছে।');
        }
        redirect(BASE_URL . '/admin/menu-builder/?menu=' . urlencode($menu));
    }

    if ($_POST['action'] === 'edit_item') {
        $id         = (int)($_POST['id'] ?? 0);
        $menu       = sanitize_input($_POST['menu'] ?? 'main');
        $label      = sanitize_input($_POST['label'] ?? '');
        $url_val    = sanitize_input($_POST['url'] ?? '');
        $parent_id  = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $target     = sanitize_input($_POST['target'] ?? '_self');
        $is_active  = isset($_POST['is_active']) ? 1 : 0;

        if ($id > 0 && !empty($label) && !empty($url_val)) {
            $stmt = $pdo->prepare("UPDATE menu_items SET label = ?, url = ?, parent_id = ?, sort_order = ?, target = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$label, $url_val, $parent_id, $sort_order, $target, $is_active, $id]);
            set_flash('success', 'মেনু আইটেম আপডেট হয়েছে।');
        }
        redirect(BASE_URL . '/admin/menu-builder/?menu=' . urlencode($menu));
    }

    if ($_POST['action'] === 'delete_item') {
        $id   = (int)($_POST['id'] ?? 0);
        $menu = sanitize_input($_POST['menu'] ?? 'main');
        if ($id > 0) {
            $pdo->prepare("DELETE FROM menu_items WHERE id = ? OR parent_id = ?")->execute([$id, $id]);
            set_flash('success', 'মেনু আইটেম মুছে ফেলা হয়েছে।');
        }
        redirect(BASE_URL . '/admin/menu-builder/?menu=' . urlencode($menu));
    }
}

// Fetch items for current selected menu
$stmt = $pdo->prepare("SELECT * FROM menu_items WHERE menu = ? ORDER BY sort_order ASC, id ASC");
$stmt->execute([$selected_menu]);
$items = $stmt->fetchAll();

// Fetch categories and pages for quick-add helper
$categories = $pdo->query("SELECT id, name, name_bn, slug FROM product_categories ORDER BY name ASC")->fetchAll();
$cms_pages   = $pdo->query("SELECT id, title, slug FROM pages WHERE is_active = 1 ORDER BY title ASC")->fetchAll();

$page_title = 'মেনু বিল্ডার | FarmersBD Admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-menu-button-wide text-primary me-2"></i>ন্যাভিগেশন মেনু বিল্ডার</h1>
            <p class="text-muted small mb-0">ওয়েবসাইটের হেডার, ফুটার এবং মোবাইল মেনু ড্রপডাউন সহ সহজে সাজান</p>
        </div>
    </div>
</div>

<!-- Menu Selector Tabs -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex gap-2">
                <a href="<?= url('admin/menu-builder/?menu=main') ?>" class="btn <?= $selected_menu === 'main' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    <i class="bi bi-layout-text-window me-1"></i> হেডার মেইন মেনু
                </a>
                <a href="<?= url('admin/menu-builder/?menu=footer_quick') ?>" class="btn <?= $selected_menu === 'footer_quick' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    <i class="bi bi-link-45deg me-1"></i> ফুটার কুইক লিংক
                </a>
                <a href="<?= url('admin/menu-builder/?menu=footer_info') ?>" class="btn <?= $selected_menu === 'footer_info' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    <i class="bi bi-info-circle me-1"></i> ফুটার ইনফো / সাপোর্ট
                </a>
                <a href="<?= url('admin/menu-builder/?menu=mobile') ?>" class="btn <?= $selected_menu === 'mobile' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    <i class="bi bi-phone me-1"></i> মোবাইল বটম বার
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Quick Add Helpers -->
    <div class="col-lg-4">
        <!-- Add Custom Link -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 fw-bold"><i class="bi bi-plus-circle text-primary me-1"></i> কাস্টম মেনু আইটেম যোগ করুন</div>
            <div class="card-body p-3">
                <form method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add_item">
                    <input type="hidden" name="menu" value="<?= e($selected_menu) ?>">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">মেনু লেবেল (নাম) <span class="text-danger">*</span></label>
                        <input type="text" name="label" id="inputLabel" class="form-control" placeholder="যেমন: বিশেষ অফার" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">URL লিংক <span class="text-danger">*</span></label>
                        <input type="text" name="url" id="inputUrl" class="form-control font-monospace" placeholder="/offers বা https://..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">প্যারেন্ট মেনু (যদি সাব-মেনু হয়)</label>
                        <select name="parent_id" class="form-select">
                            <option value="">-- শীর্ষ স্তর (Main Item) --</option>
                            <?php foreach ($items as $item): ?>
                                <?php if (empty($item['parent_id'])): ?>
                                    <option value="<?= $item['id'] ?>"><?= e($item['label']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">ক্রম নম্বর</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">টার্গেট</label>
                            <select name="target" class="form-select">
                                <option value="_self">একই ট্যাবে (_self)</option>
                                <option value="_blank">নতুন ট্যাবে (_blank)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="activeSwitch" value="1" checked>
                        <label class="form-check-label small fw-semibold" for="activeSwitch">সক্রিয় রাখুন</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 shadow-sm">
                        <i class="bi bi-plus-lg me-1"></i> মেনুতে যোগ করুন
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick Add Accordion -->
        <div class="accordion shadow-sm mb-4" id="quickAddAccordion">
            <!-- Categories -->
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCategories">
                        <i class="bi bi-tags text-primary me-2"></i> প্রোডাক্ট ক্যাটাগরি তালিকা
                    </button>
                </h2>
                <div id="collapseCategories" class="accordion-collapse collapse" data-bs-parent="#quickAddAccordion">
                    <div class="accordion-body p-3">
                        <div class="list-group list-group-flush small">
                            <?php foreach ($categories as $cat): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><?= e($cat['name_bn'] ?: $cat['name']) ?></span>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 quick-fill-btn"
                                        data-label="<?= e($cat['name_bn'] ?: $cat['name']) ?>"
                                        data-url="/category/<?= e($cat['slug']) ?>">
                                        <i class="bi bi-arrow-up-right"></i> বসান
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pages -->
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePages">
                        <i class="bi bi-file-earmark-text text-primary me-2"></i> তৈরি করা পেজসমূহ
                    </button>
                </h2>
                <div id="collapsePages" class="accordion-collapse collapse" data-bs-parent="#quickAddAccordion">
                    <div class="accordion-body p-3">
                        <div class="list-group list-group-flush small">
                            <?php foreach ($cms_pages as $cp): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><?= e($cp['title']) ?></span>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 quick-fill-btn"
                                        data-label="<?= e($cp['title']) ?>"
                                        data-url="/page/<?= e($cp['slug']) ?>">
                                        <i class="bi bi-arrow-up-right"></i> বসান
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Structure View -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold">
                    <i class="bi bi-list-nested text-primary me-2"></i>
                    মেনু স্ট্রাকচার: <span class="text-primary"><?= strtoupper($selected_menu) ?></span>
                </span>
                <span class="badge bg-light text-secondary border"><?= count($items) ?> টি আইটেম</span>
            </div>
            <div class="card-body p-3">
                <?php if (empty($items)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-menu-button display-5 d-block text-secondary mb-2 opacity-50"></i>
                        এই মেনুতে এখনও কোনো লিংক যোগ করা হয়নি।
                    </div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($items as $it): ?>
                            <div class="list-group-item p-3 mb-2 rounded border <?= $it['parent_id'] ? 'ms-4 bg-light' : 'bg-white shadow-sm' ?>">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-grip-vertical text-muted"></i>
                                        <?php if ($it['parent_id']): ?>
                                            <i class="bi bi-arrow-return-right text-muted me-1"></i>
                                        <?php endif; ?>
                                        <div>
                                            <span class="fw-bold text-dark"><?= e($it['label']) ?></span>
                                            <span class="badge bg-light text-muted border font-monospace ms-2"><?= e($it['url']) ?></span>
                                            <?php if ($it['target'] === '_blank'): ?>
                                                <span class="badge bg-info-subtle text-info small ms-1">নতুন ট্যাব</span>
                                            <?php endif; ?>
                                            <?php if (!$it['is_active']): ?>
                                                <span class="badge bg-danger-subtle text-danger small ms-1">লুকানো</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-secondary-subtle text-secondary font-monospace">ক্রম: <?= $it['sort_order'] ?></span>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary edit-menu-btn"
                                                data-id="<?= $it['id'] ?>"
                                                data-label="<?= e($it['label']) ?>"
                                                data-url="<?= e($it['url']) ?>"
                                                data-parent="<?= $it['parent_id'] ?>"
                                                data-order="<?= $it['sort_order'] ?>"
                                                data-target="<?= e($it['target']) ?>"
                                                data-active="<?= $it['is_active'] ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('এই মেনু আইটেমটি মুছে ফেলতে চান?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="delete_item">
                                                <input type="hidden" name="menu" value="<?= e($selected_menu) ?>">
                                                <input type="hidden" name="id" value="<?= $it['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit Item -->
<div class="modal fade" id="editMenuModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="edit_item">
            <input type="hidden" name="menu" value="<?= e($selected_menu) ?>">
            <input type="hidden" name="id" id="editItemId">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>মেনু আইটেম সম্পাদনা</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">মেনু লেবেল</label>
                    <input type="text" name="label" id="editItemLabel" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">URL লিংক</label>
                    <input type="text" name="url" id="editItemUrl" class="form-control font-monospace" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">প্যারেন্ট মেনু</label>
                    <select name="parent_id" id="editItemParent" class="form-select">
                        <option value="">-- শীর্ষ স্তর (Main Item) --</option>
                        <?php foreach ($items as $item): ?>
                            <?php if (empty($item['parent_id'])): ?>
                                <option value="<?= $item['id'] ?>"><?= e($item['label']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">ক্রম নম্বর</label>
                        <input type="number" name="sort_order" id="editItemOrder" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">টার্গেট</label>
                        <select name="target" id="editItemTarget" class="form-select">
                            <option value="_self">একই ট্যাবে (_self)</option>
                            <option value="_blank">নতুন ট্যাবে (_blank)</option>
                        </select>
                    </div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" id="editItemActive" value="1">
                    <label class="form-check-label fw-semibold" for="editItemActive">সক্রিয় রাখুন</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="btn btn-primary">সংরক্ষণ করুন</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.quick-fill-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('inputLabel').value = this.dataset.label;
        document.getElementById('inputUrl').value = this.dataset.url;
        document.getElementById('inputLabel').focus();
    });
});

document.querySelectorAll('.edit-menu-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('editItemId').value = this.dataset.id;
        document.getElementById('editItemLabel').value = this.dataset.label;
        document.getElementById('editItemUrl').value = this.dataset.url;
        document.getElementById('editItemParent').value = this.dataset.parent || '';
        document.getElementById('editItemOrder').value = this.dataset.order;
        document.getElementById('editItemTarget').value = this.dataset.target;
        document.getElementById('editItemActive').checked = this.dataset.active === '1';

        new bootstrap.Modal(document.getElementById('editMenuModal')).show();
    });
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
