<?php
// ============================================================
// FarmersBD — Admin Settings: Delivery Charges & Shipping Rules
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

if (is_post_request()) {
    verify_csrf_token();

    $settings = [
        'delivery_fee_dhaka'         => (float)($_POST['delivery_fee_dhaka'] ?? 60),
        'delivery_fee_outside'       => (float)($_POST['delivery_fee_outside'] ?? 120),
        'free_delivery_active'       => isset($_POST['free_delivery_active']) ? '1' : '0',
        'free_delivery_min_amount'   => (float)($_POST['free_delivery_min_amount'] ?? 1500),
        'express_delivery_active'    => isset($_POST['express_delivery_active']) ? '1' : '0',
        'express_delivery_fee'       => (float)($_POST['express_delivery_fee'] ?? 150),
        'estimated_days_dhaka'       => sanitize_input($_POST['estimated_days_dhaka'] ?? '১-২ কর্মদিবস'),
        'estimated_days_outside'     => sanitize_input($_POST['estimated_days_outside'] ?? '২-৪ কর্মদিবস'),
        'delivery_instructions_text' => sanitize_input($_POST['delivery_instructions_text'] ?? ''),
    ];

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
        $stmt->execute([$key, $value]);
    }

    set_flash('success', 'ডেলিভারি সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে।');
    redirect(BASE_URL . '/admin/settings/delivery.php');
}

$settings_raw = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

$page_title = "ডেলিভারি ও শিপিং চার্জ সেটিংস — FarmersBD Admin";
require_once $base . '/admin/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1"><i class="bi bi-truck text-primary me-2"></i>ডেলিভারি ও শিপিং চার্জ সেটিংস</h1>
        <p class="text-muted small mb-0">ঢাকা ও ঢাকার বাইরের ডেলিভারি চার্জ, ফ্রি ডেলিভারি শর্ত ও সময়সীমা নির্ধারণ করুন</p>
    </div>
</div>

<form method="POST">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Standard Delivery -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">স্ট্যান্ডার্ড ডেলিভারি চার্জ</div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ঢাকার ভিতরে চার্জ (৳)</label>
                            <input type="number" step="1" name="delivery_fee_dhaka" class="form-control" value="<?= e($settings_raw['delivery_fee_dhaka'] ?? '60') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ঢাকার বাইরে চার্জ (৳)</label>
                            <input type="number" step="1" name="delivery_fee_outside" class="form-control" value="<?= e($settings_raw['delivery_fee_outside'] ?? '120') ?>" required>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ঢাকার ভিতরে আনুমানিক সময়</label>
                            <input type="text" name="estimated_days_dhaka" class="form-control" value="<?= e($settings_raw['estimated_days_dhaka'] ?? '১-২ কর্মদিবস') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ঢাকার বাইরে আনুমানিক সময়</label>
                            <input type="text" name="estimated_days_outside" class="form-control" value="<?= e($settings_raw['estimated_days_outside'] ?? '২-৪ কর্মদিবস') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Free Delivery Rule -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">ফ্রি ডেলিভারি অফার (Free Shipping Threshold)</div>
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="free_delivery_active" id="freeDelivSwitch" value="1" <?= ($settings_raw['free_delivery_active'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="freeDelivSwitch">নির্দিষ্ট মূল্যের কেনাকাটায় ফ্রি ডেলিভারি চালু রাখুন</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">সর্বনিম্ন অর্ডারের পরিমাণ (৳)</label>
                        <input type="number" step="1" name="free_delivery_min_amount" class="form-control" value="<?= e($settings_raw['free_delivery_min_amount'] ?? '1500') ?>" style="max-width: 250px;">
                        <div class="form-text small">কার্ট এই মূল্যে পৌঁছালে ডেলিভারি চার্জ ০ টাকা হবে এবং কার্ট ড্রয়ারে প্রোগ্রেস বার দেখাবে।</div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">ডেলিভারি নোট ও বিশেষ নির্দেশনা</div>
                <div class="card-body p-4">
                    <textarea name="delivery_instructions_text" class="form-control" rows="3" placeholder="চেকআউট পেজে গ্রাহককে দেখানোর মতো বিশেষ ডেলিভারি বার্তা..."><?= e($settings_raw['delivery_instructions_text'] ?? 'পণ্য হাতে পেয়ে চেক করে মূল্য পরিশোধের সুযোগ রয়েছে।') ?></textarea>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Express Delivery -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">জরুরী এক্সপ্রেস ডেলিভারি</div>
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="express_delivery_active" id="expSwitch" value="1" <?= ($settings_raw['express_delivery_active'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="expSwitch">একই দিনে / এক্সপ্রেস ডেলিভারি</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">এক্সপ্রেস চার্জ (৳)</label>
                        <input type="number" step="1" name="express_delivery_fee" class="form-control" value="<?= e($settings_raw['express_delivery_fee'] ?? '150') ?>">
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> ডেলিভারি সেটিংস সংরক্ষণ
                </button>
            </div>
        </div>
    </div>
</form>

<?php require_once $base . '/admin/includes/footer.php'; ?>
