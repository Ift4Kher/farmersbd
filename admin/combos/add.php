<?php
// ============================================================
// FarmersBD — Admin Combos Add / Create
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

// Get active products for selector
$prod_stmt = $pdo->query("SELECT id, name, name_bn, regular_price, sale_price, stock, image FROM products WHERE status = 'published' AND is_active = 1 ORDER BY name ASC");
$products = $prod_stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name           = sanitize_input($_POST['name'] ?? '');
    $name_bn        = sanitize_input($_POST['name_bn'] ?? '');
    $slug           = sanitize_input($_POST['slug'] ?? '');
    $description    = $_POST['description'] ?? '';
    $price          = (float)($_POST['price'] ?? 0);
    $regular_price  = (float)($_POST['regular_price'] ?? 0);
    $is_active      = isset($_POST['is_active']) ? 1 : 0;
    $selected_prods = $_POST['items'] ?? []; // array of ['product_id' => x, 'quantity' => y]

    if (empty($slug)) {
        $slug = slugify($name ?: $name_bn ?: 'combo-' . time());
    } else {
        $slug = slugify($slug);
    }

    // Check slug uniqueness
    $slug_check = $pdo->prepare("SELECT id FROM combos WHERE slug = ?");
    $slug_check->execute([$slug]);
    if ($slug_check->fetch()) {
        $slug .= '-' . time();
    }

    $image_path = '';
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = $base . '/public/uploads/combos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $filename = 'combo_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                $image_path = 'uploads/combos/' . $filename;
            }
        }
    }

    if (empty($name) && empty($name_bn)) {
        set_flash('error', 'কম্বোর নাম আবশ্যক।');
    } elseif ($price <= 0) {
        set_flash('error', 'সঠিক বিক্রয় মূল্য দিন।');
    } else {
        $stmt = $pdo->prepare("INSERT INTO combos (name, name_bn, slug, description, price, regular_price, image, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$name, $name_bn, $slug, $description, $price, $regular_price, $image_path, $is_active]);
        $combo_id = $pdo->lastInsertId();

        // Insert items
        if (!empty($selected_prods) && is_array($selected_prods)) {
            $item_stmt = $pdo->prepare("INSERT INTO combo_items (combo_id, product_id, quantity) VALUES (?, ?, ?)");
            foreach ($selected_prods as $item) {
                $pid = (int)($item['product_id'] ?? 0);
                $qty = max(1, (int)($item['quantity'] ?? 1));
                if ($pid > 0) {
                    $item_stmt->execute([$combo_id, $pid, $qty]);
                }
            }
        }

        set_flash('success', 'নতুন কম্বো সফলভাবে তৈরি করা হয়েছে।');
        redirect(BASE_URL . '/admin/combos/');
    }
}

$page_title = 'নতুন কম্বো তৈরি | FarmersBD Admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-plus-circle text-primary me-2"></i>নতুন কম্বো তৈরি করুন</h1>
            <p class="text-muted small mb-0">একাধিক প্রোডাক্ট যুক্ত করে স্পেশাল অফার কম্বো প্যাকেজ সাজান</p>
        </div>
        <a href="<?= url('admin/combos/') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> ফিরে যান
        </a>
    </div>
</div>

<form method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-4">
        <!-- Main Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">মৌলিক তথ্য (Basic Info)</div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">কম্বো নাম (English) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="যেমন: Fish Growth Master Combo" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">কম্বো নাম (বাংলা)</label>
                            <input type="text" name="name_bn" class="form-control" placeholder="যেমন: মাছ বৃদ্ধির স্পেশাল কম্বো">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">URL Slug</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted small"><?= url('combo/') ?>/</span>
                                <input type="text" name="slug" class="form-control font-monospace" placeholder="fish-growth-master-combo">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">বিবরণ (Description)</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="কম্বোর উপকারিতা ও ব্যবহারের নিয়মাবলী লিখুন..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Combo Items Selection -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold">কম্বোর অন্তর্ভুক্ত পণ্যসমূহ (Included Products)</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                        <i class="bi bi-plus-lg me-1"></i> পণ্য যোগ করুন
                    </button>
                </div>
                <div class="card-body p-4">
                    <div id="comboItemsContainer">
                        <div class="combo-item-row row g-2 align-items-center mb-3 p-3 bg-light rounded border">
                            <div class="col-md-7">
                                <label class="form-label small fw-semibold text-muted mb-1">প্রোডাক্ট নির্বাচন করুন</label>
                                <select name="items[0][product_id]" class="form-select product-select" required>
                                    <option value="">-- প্রোডাক্ট বেছে নিন --</option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?= $p['id'] ?>" data-price="<?= $p['regular_price'] ?>">
                                            <?= e($p['name_bn'] ?: $p['name']) ?> (<?= format_price($p['regular_price']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">পরিমাণ (Qty)</label>
                                <input type="number" name="items[0][quantity]" class="form-control qty-input" value="1" min="1" required>
                            </div>
                            <div class="col-md-2 text-end pt-4">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn" disabled>
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar / Pricing / Media -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">মূল্য নির্ধারণ (Pricing)</div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">রেগুলার মূল্য (৳)</label>
                        <input type="number" step="0.01" name="regular_price" class="form-control" placeholder="0.00">
                        <div class="form-text small">পণ্যগুলোর সাধারণ মূল্যের যোগফল</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">কম্বো অফার মূল্য (৳) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="price" class="form-control form-control-lg text-primary fw-bold" placeholder="0.00" required>
                        <div class="form-text small">গ্রাহক যে মূল্যে কম্বোটি কিনতে পারবেন</div>
                    </div>
                    <div class="form-check form-switch pt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActiveSwitch" value="1" checked>
                        <label class="form-check-label fw-semibold" for="isActiveSwitch">কম্বো সক্রিয় রাখুন (Active)</label>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">কম্বো ব্যানার / ছবি</div>
                <div class="card-body p-4 text-center">
                    <div class="mb-3">
                        <div class="bg-light border rounded d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 100%; height: 180px;" id="previewContainer">
                            <i class="bi bi-cloud-arrow-up display-4 text-muted"></i>
                        </div>
                        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this)">
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> কম্বো সেভ করুন
                </button>
            </div>
        </div>
    </div>
</form>

<script>
let itemIndex = 1;
document.getElementById('addItemBtn').addEventListener('click', function() {
    const container = document.getElementById('comboItemsContainer');
    const firstRow = container.querySelector('.combo-item-row');
    const newRow = firstRow.cloneNode(true);
    
    newRow.querySelector('.product-select').name = `items[${itemIndex}][product_id]`;
    newRow.querySelector('.product-select').value = '';
    newRow.querySelector('.qty-input').name = `items[${itemIndex}][quantity]`;
    newRow.querySelector('.qty-input').value = '1';
    
    const removeBtn = newRow.querySelector('.remove-row-btn');
    removeBtn.disabled = false;
    removeBtn.addEventListener('click', function() {
        newRow.remove();
    });
    
    container.appendChild(newRow);
    itemIndex++;
});

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewContainer').innerHTML = `<img src="${e.target.result}" class="img-fluid rounded object-fit-cover w-100 h-100">`;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
