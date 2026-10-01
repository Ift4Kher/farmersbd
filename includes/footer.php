<?php
// ============================================================
// FarmersBD — Modern Footer Component
// Matches exact reference UI with bespoke handcrafted SVG icons
// ============================================================
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';

$phone   = setting('site_phone', '01979-606212');
$email   = setting('site_email', 'Info.Farmersbd@gmail.com');
$address = setting('site_address', 'ঢাকা, বাংলাদেশ');
?>

<footer class="footer-exact-wrap">
    <!-- Top Organic Wave Transition (Exact Reference) -->
    <div class="footer-wave-top">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <!-- Cyan / Ocean Blue Wave Layer -->
            <path d="M0,82 C140,105 240,42 360,48 C500,56 640,84 820,82 C1020,80 1180,68 1280,72 C1345,55 1395,28 1440,14 L1440,120 L0,120 Z" fill="#0284c7" opacity="0.85"></path>
            <!-- Deep Navy Wave Layer -->
            <path d="M0,98 C140,118 240,62 360,65 C500,70 640,84 820,82 C1020,80 1180,68 1275,64 C1340,38 1395,12 1440,0 L1440,120 L0,120 Z" fill="#011f35"></path>
        </svg>
    </div>

    <!-- Main Footer Body -->
    <div class="footer-main-body">
        <div class="container position-relative z-2">
            <div class="row g-4 justify-content-between">
                
                <!-- 1. Brand Info -->
                <div class="col-xl-3 col-lg-3 col-md-6 col-12">
                    <div class="footer-brand-header mb-3">
                        <a href="<?= url() ?>" class="d-inline-flex align-items-center gap-2 text-decoration-none">
                            <img src="<?= asset('assets/images/logo.png') ?>" alt="FarmersBD Logo" class="footer-brand-logo">
                            <div>
                                <div class="footer-brand-name">FarmersBD</div>
                                <div class="footer-brand-tagline">Fresh Fish, Healthy Life</div>
                            </div>
                        </a>
                    </div>
                    <p class="footer-bio-text mb-4">
                        প্রাকৃতিক পদ্ধতিতে উৎপাদিত তাজা মাছ এখন আপনার ঘরে। সুস্থ থাকুন, ভালো থাকুন।
                    </p>
                    <div class="footer-social-icons d-flex align-items-center gap-2">
                        <a href="<?= e(setting('facebook_url', 'https://www.facebook.com/share/1YaDdNybXm/')) ?>" target="_blank" rel="noopener noreferrer" class="footer-social-btn" aria-label="Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="<?= e(setting('youtube_url', 'https://youtube.com')) ?>" target="_blank" rel="noopener noreferrer" class="footer-social-btn" aria-label="YouTube">
                            <i class="bi bi-youtube"></i>
                        </a>
                        <a href="<?= e(setting('instagram_url', 'https://instagram.com')) ?>" target="_blank" rel="noopener noreferrer" class="footer-social-btn" aria-label="Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="<?= e(setting('whatsapp_url', 'https://wa.me/8801979606212')) ?>" target="_blank" rel="noopener noreferrer" class="footer-social-btn" aria-label="WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                </div>

                <!-- 2. দ্রুত লিঙ্ক (Quick Links) with Handcrafted Fish Icon -->
                <div class="col-xl-2 col-lg-2 col-md-3 col-6">
                    <div class="footer-column-heading">
                        <svg class="footer-icon-svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 4C7.8 4 4.1 6.8 2.2 10.7C1.9 11.3 1.9 12.1 2.2 12.7C4.1 16.6 7.8 19.4 12 19.4C15.2 19.4 18.6 17.8 20.6 15.8L22.6 17.2V6.2L20.6 7.6C18.6 5.6 15.2 4 12 4ZM6.5 12.5C5.7 12.5 5 11.8 5 11C5 10.2 5.7 9.5 6.5 9.5C7.3 9.5 8 10.2 8 11C8 11.8 7.3 12.5 6.5 12.5Z"/>
                        </svg>
                        <span>দ্রুত লিঙ্ক</span>
                    </div>
                    <ul class="footer-link-list">
                        <li>
                            <a href="<?= url() ?>">
                                <span>হোম</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('fish/') ?>">
                                <span>মাছের তথ্য</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('diseases/') ?>">
                                <span>মাছের রোগ</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('ai/') ?>">
                                <span>AI রোগ নির্ণয়</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('products/') ?>">
                                <span>আলোচ্য ওষুধ</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- 3. আরও (More Links) with Handcrafted Document Icon -->
                <div class="col-xl-2 col-lg-2 col-md-3 col-6">
                    <div class="footer-column-heading">
                        <svg class="footer-icon-svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V8L14 2ZM18 20H6V4H13V9H18V20ZM8 12H16V14H8V12ZM8 16H16V18H8V16Z"/>
                        </svg>
                        <span>আরও</span>
                    </div>
                    <ul class="footer-link-list">
                        <li>
                            <a href="<?= url('auth/register.php') ?>">
                                <span>নিবন্ধন পদ্ধতি</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('blog/') ?>">
                                <span>ব্লগ</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('contact/') ?>">
                                <span>যোগাযোগ</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('blog/') ?>">
                                <span>রেসিপি</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('auth/login.php') ?>">
                                <span>লগইন</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- 4. যোগাযোগ (Contact Info) with Handcrafted Headset & Contact Icons -->
                <div class="col-xl-2 col-lg-2 col-md-4 col-12">
                    <div class="footer-column-heading">
                        <svg class="footer-icon-svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 1C6.5 1 2 5.5 2 11V18C2 19.7 3.3 21 5 21H8V13H4V11C4 6.6 7.6 3 12 3C16.4 3 20 6.6 20 11V13H16V21H19C20.7 21 22 19.7 22 18V11C22 5.5 17.5 1 12 1ZM6 19H5C4.4 19 4 18.6 4 18V15H6V19ZM20 18C20 18.6 19.6 19 19 19H18V15H20V18Z"/>
                        </svg>
                        <span>যোগাযোগ</span>
                    </div>
                    <ul class="footer-contact-list">
                        <li>
                            <svg class="footer-contact-svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M6.6 10.8C8 13.6 10.3 15.9 13.1 17.3L15.3 15.1C15.6 14.8 16 14.7 16.4 14.8C17.5 15.2 18.7 15.4 20 15.4C20.6 15.4 21 15.8 21 16.4V19.9C21 20.5 20.6 20.9 20 20.9C10.6 20.9 3 13.3 3 3.9C3 3.3 3.4 2.9 4 2.9H7.5C8.1 2.9 8.5 3.3 8.5 3.9C8.5 5.2 8.7 6.4 9.1 7.5C9.2 7.9 9.1 8.3 8.8 8.6L6.6 10.8Z"/>
                            </svg>
                            <a href="tel:<?= e($phone) ?>" class="text-decoration-none"><?= e($phone) ?></a>
                        </li>
                        <li>
                            <svg class="footer-contact-svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20 4H4C2.9 4 2 4.9 2 6V18C2 19.1 2.9 20 4 20H20C21.1 20 22 19.1 22 18V6C22 4.9 21.1 4 20 4ZM20 8L12 13L4 8V6L12 11L20 6V8Z"/>
                            </svg>
                            <a href="mailto:<?= e($email) ?>" class="text-decoration-none"><?= e($email) ?></a>
                        </li>
                        <li>
                            <svg class="footer-contact-svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C8.1 2 5 5.1 5 9C5 14.2 12 22 12 22C12 22 19 14.2 19 9C19 5.1 15.9 2 12 2ZM12 11.5C10.6 11.5 9.5 10.4 9.5 9C9.5 7.6 10.6 6.5 12 6.5C13.4 6.5 14.5 7.6 14.5 9C14.5 10.4 13.4 11.5 12 11.5Z"/>
                            </svg>
                            <span><?= e($address) ?></span>
                        </li>
                    </ul>
                </div>

                <!-- 5. আপডেট পেতে সাবস্ক্রাইব করুন (Subscribe Card) with Handcrafted Envelope & Send Icon -->
                <div class="col-xl-3 col-lg-3 col-md-8 col-12">
                    <div class="footer-subscribe-box">
                        <div class="footer-sub-header">
                            <div class="footer-sub-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20 4H4C2.9 4 2 4.9 2 6V18C2 19.1 2.9 20 4 20H20C21.1 20 22 19.1 22 18V6C22 4.9 21.1 4 20 4ZM20 8L12 13L4 8V6L12 11L20 6V8Z"/>
                                </svg>
                            </div>
                            <span class="footer-sub-title">আপডেট পেতে সাবস্ক্রাইব করুন</span>
                        </div>
                        <p class="footer-sub-desc">
                            আমাদের নতুন অফার, মাছের তথ্য ও আপডেট পেতে আপনার ইমেইলটি সাবমিট করুন।
                        </p>
                        <form id="footer-newsletter-form" action="<?= url('contact/newsletter-subscribe.php') ?>" method="POST" class="footer-subscribe-form">
                            <?= csrf_field() ?>
                            <div class="footer-input-pill">
                                <input type="email" name="email" class="footer-input-field" placeholder="আপনার ইমেইল ঠিকানা লিখুন" required>
                                <button type="submit" class="footer-send-btn" aria-label="সাবস্ক্রাইব">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M2.01 21L23 12L2.01 3L2 10L17 12L2 14L2.01 21Z"/>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

            <!-- Bottom Copyright & Credit Bar -->
            <div class="footer-bottom-row">
                <div class="footer-bottom-copy">
                    © <?= date('Y') ?> FarmersBD. সর্বস্বত্ব সংরক্ষিত।
                </div>
                <div class="footer-bottom-powered">
                    <span class="footer-powered-divider">|</span> Powered by <strong class="footer-powered-brand">FarmersBD</strong>
                </div>
            </div>

        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Dynamic JS Config (generated by PHP so URLs work on both localhost and live) -->
<script>
window.FARMERSBD = {
    urls: {
        newsletter: '<?= url('contact/newsletter-subscribe.php') ?>',
        cartAdd:    '<?= url('cart/add.php') ?>',
        cartUpdate: '<?= url('cart/update.php') ?>',
        cartRemove: '<?= url('cart/remove.php') ?>',
        cartClear:  '<?= url('cart/clear.php') ?>',
        placeholder:'<?= asset('assets/images/general/placeholder.jpg') ?>',
        searchApi:  '<?= url('api/search.php') ?>',
    }
};
</script>
<!-- FarmersBD Scripts -->
<script src="<?= asset('assets/js/script.js') ?>?v=<?= filemtime(dirname(__DIR__) . '/assets/js/script.js') ?>"></script>
<script src="<?= asset('assets/js/cart.js') ?>?v=<?= filemtime(dirname(__DIR__) . '/assets/js/cart.js') ?>"></script>
<?php if (isset($extra_js)) echo $extra_js; ?>
</body>
</html>
