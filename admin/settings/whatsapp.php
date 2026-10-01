<?php
// ============================================================
// FarmersBD — Admin Settings: WhatsApp & SMS Message Templates
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
        'wa_support_number'         => sanitize_input($_POST['wa_support_number'] ?? '8801700000000'),
        'wa_template_order_confirm' => sanitize_input($_POST['wa_template_order_confirm'] ?? ''),
        'wa_template_shipped'       => sanitize_input($_POST['wa_template_shipped'] ?? ''),
        'wa_template_lead_recovery' => sanitize_input($_POST['wa_template_lead_recovery'] ?? ''),
        'wa_floating_chat_enabled'  => isset($_POST['wa_floating_chat_enabled']) ? '1' : '0',
    ];

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
        $stmt->execute([$key, $value]);
    }

    set_flash('success', 'WhatsApp ও মেসেজ টেমপ্লেট সফলভাবে সংরক্ষণ করা হয়েছে।');
    redirect(BASE_URL . '/admin/settings/whatsapp.php');
}

$settings_raw = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

$page_title = "WhatsApp ও বার্তা টেমপ্লেট — FarmersBD Admin";
require_once $base . '/admin/includes/header.php';
?>

<div class="admin-content-header mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1"><i class="bi bi-whatsapp text-success me-2"></i>WhatsApp ও মেসেজিং টেমপ্লেট</h1>
        <p class="text-muted small mb-0">অর্ডার নিশ্চিতকরণ, ডেলিভারি ট্র্যাকিং ও লিড রিকভারির স্বয়ংক্রিয় বার্তা টেমপ্লেট</p>
    </div>
</div>

<form method="POST">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- General WhatsApp Settings -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">WhatsApp যোগাযোগ কনফিগারেশন</div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">অফিসিয়াল WhatsApp নম্বর (কান্ট্রি কোড সহ)</label>
                        <input type="text" name="wa_support_number" class="form-control font-monospace" placeholder="8801712345678" value="<?= e($settings_raw['wa_support_number'] ?? '8801700000000') ?>" required>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="wa_floating_chat_enabled" id="waChatSwitch" value="1" <?= ($settings_raw['wa_floating_chat_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="waChatSwitch">ওয়েবসাইটে ভাসমান WhatsApp লাইভ চ্যাট বাটন প্রদর্শন করুন</label>
                    </div>
                </div>
            </div>

            <!-- Templates -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">মেসেজ টেমপ্লেটসমূহ</div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">১. অর্ডার কনফার্মেশন মেসেজ টেমপ্লেট</label>
                        <textarea name="wa_template_order_confirm" class="form-control" rows="3"><?= e($settings_raw['wa_template_order_confirm'] ?? "প্রিয় {customer_name}, FarmersBD-তে আপনার অর্ডার #{order_number} সফলভাবে গৃহীত হয়েছে। মোট মূল্য: ৳{total_amount}। শীঘ্রই পার্সেলটি পাঠানো হবে। ধন্যবাদ!") ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">২. কুরিয়ার শিপমেন্ট ও ট্র্যাকিং মেসেজ</label>
                        <textarea name="wa_template_shipped" class="form-control" rows="3"><?= e($settings_raw['wa_template_shipped'] ?? "প্রিয় {customer_name}, আপনার অর্ডার #{order_number} কুরিয়ারে হস্তান্তর করা হয়েছে। ট্র্যাকিং নং: {tracking_number}। ডেলিভারির সময় রিসিভ করার অনুরোধ রইল।") ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">৩. অসম্পূর্ণ লিড / কার্ট রিকভারি বার্তা</label>
                        <textarea name="wa_template_lead_recovery" class="form-control" rows="3"><?= e($settings_raw['wa_template_lead_recovery'] ?? "আসসালামু আলাইকুম {customer_name}, FarmersBD থেকে যোগাযোগ করছি। আপনার কাঙ্ক্ষিত পণ্যের অর্ডারে কোনো সমস্যা বা জিজ্ঞাসা আছে কি?") ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">শর্টকোড গাইড (Available Tags)</div>
                <div class="card-body p-3">
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2"><code class="text-primary">{customer_name}</code> - গ্রাহকের নাম</li>
                        <li class="mb-2"><code class="text-primary">{order_number}</code> - অর্ডার নম্বর</li>
                        <li class="mb-2"><code class="text-primary">{total_amount}</code> - মোট মূল্য</li>
                        <li class="mb-2"><code class="text-primary">{tracking_number}</code> - ট্র্যাকিং কোড</li>
                        <li class="mb-2"><code class="text-primary">{courier_name}</code> - কুরিয়ার নাম</li>
                    </ul>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> টেমপ্লেট সংরক্ষণ করুন
                </button>
            </div>
        </div>
    </div>
</form>

<?php require_once $base . '/admin/includes/footer.php'; ?>
