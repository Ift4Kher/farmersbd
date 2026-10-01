<?php
// ============================================================
// FarmersBD — Maintenance Mode
// ============================================================
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (!defined('MAINTENANCE_MODE') || !MAINTENANCE_MODE) {
    redirect(BASE_URL . '/');
}
if (is_admin()) {
    redirect(BASE_URL . '/admin/');
}

$page_seo = ['title' => 'ক্ষণিকের জন্য বন্ধ — FarmersBD'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5 text-center" style="min-height: 80vh; display: flex; align-items: center; justify-content: center;">
    <div>
        <i class="bi bi-tools text-primary" style="font-size: 5rem;"></i>
        <h1 class="fw-bold mt-4">আমরা একটু মেরামতের কাজে ব্যস্ত!</h1>
        <p class="text-muted fs-5 mt-3 max-w-600 mx-auto">
            আপনাকে সেরা অভিজ্ঞতা দিতে আমাদের সিস্টেমে কিছু উন্নয়নমূলক কাজ চলছে। দয়া করে কিছুক্ষণ পর আবার চেষ্টা করুন। সাময়িক এই অসুবিধার জন্য আমরা আন্তরিকভাবে দুঃখিত।
        </p>
        <?php if (!is_logged_in()): ?>
            <div class="mt-4">
                <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-outline-secondary"><i class="bi bi-person-fill-lock"></i> এডমিন লগইন</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
