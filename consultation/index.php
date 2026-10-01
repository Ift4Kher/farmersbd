<?php
// ============================================================
// FarmersBD — Expert Consultation Overview Page
// Matches exact reference UI
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/seo.php';

$phone   = setting('site_phone', '01979-606212');
$email   = setting('site_email', 'Info.Farmersbd@gmail.com');
$address = setting('site_address', 'ঢাকা, বাংলাদেশ');

$page_title = "অভিজ্ঞ মৎস্য বিশেষজ্ঞদের সাথে সরাসরি যোগাযোগ — " . setting('site_name', 'FarmersBD');
$meta_desc  = "আপনার পুকুরের মাছের চাহিদা পূরণে, পানি বা খামারের যেকোনো সমস্যায় অভিজ্ঞ মৎস্য বিশেষজ্ঞ টিমের সাথে সরাসরি যোগাযোগ করুন।";

$extra_css = '<link rel="stylesheet" href="' . asset('assets/css/consultation.css') . '?v=' . time() . '">';

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>

<div class="container py-4">
    <!-- Hero Banner Card -->
    <div class="consult-hero-card">
        <div class="row align-items-center">
            <div class="col-lg-7 col-md-9 col-12">
                <span class="consult-badge">
                    <i class="bi bi-leaf-fill"></i>
                    <span>আমাদের সম্পর্কে জানুন</span>
                </span>
                
                <h1 class="consult-hero-title">
                    অভিজ্ঞ মৎস্য বিশেষজ্ঞদের সাথে
                    <span class="consult-hero-highlight">সরাসরি যোগাযোগ</span>
                </h1>
                
                <p class="consult-hero-desc">
                    আপনার পুকুরের মাছের চাহিদা পূরণে, পানি বা খামারের যেকোনো সমস্যায়<br class="d-none d-md-inline">
                    অভিজ্ঞ টিমের সাথে সরাসরি যোগাযোগ করুন।
                </p>
                
                <a href="<?= url('consultation/create.php') ?>" class="btn-consult-primary">
                    <i class="bi bi-chat-dots-fill"></i>
                    <span>এখনই পরামর্শ আবেদন করুন</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- 3 Feature Badges Strip (Bottom of Hero) -->
        <div class="consult-features-strip">
            <div class="consult-feature-badge">
                <div class="consult-feat-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <div class="consult-feat-title">বিশেষজ্ঞ পরামর্শ</div>
                    <div class="consult-feat-sub">অভিজ্ঞ টিম থেকে</div>
                </div>
            </div>

            <div class="consult-feature-badge">
                <div class="consult-feat-icon">
                    <i class="bi bi-headset"></i>
                </div>
                <div>
                    <div class="consult-feat-title">দ্রুত সেবা</div>
                    <div class="consult-feat-sub">যত দ্রুত সম্ভব</div>
                </div>
            </div>

            <div class="consult-feature-badge">
                <div class="consult-feat-icon">
                    <i class="bi bi-leaf-fill"></i>
                </div>
                <div>
                    <div class="consult-feat-title">নির্ভরযোগ্য সমাধান</div>
                    <div class="consult-feat-sub">আপনার সফলতার জন্য</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Header: কীভাবে কাজ করে? -->
    <div class="consult-section-header">
        <div class="consult-section-accent-bar"></div>
        <h2 class="consult-section-title">কীভাবে কাজ করে?</h2>
        <p class="consult-section-subtitle">সহজ ৩ টি ধাপে সমাধান পান</p>
    </div>

    <!-- 3 Step Cards Grid -->
    <div class="row g-4">
        <!-- Step 1 Card -->
        <div class="col-lg-4 col-md-6 col-12">
            <div class="consult-step-card step-blue">
                <div class="consult-step-content">
                    <div class="consult-step-top">
                        <div class="consult-step-icon-box">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <span class="consult-step-pill">01</span>
                    </div>
                    <h3 class="consult-step-title">তথ্য ও ছবি দিন</h3>
                    <p class="consult-step-desc">
                        আপনার মাছের লক্ষণ, পুকুরের তথ্য ও ছবিসহ ফর্ম পূরণ করে জমা দিন।
                    </p>
                    <a href="<?= url('consultation/create.php') ?>" class="step-btn-blue">
                        <span class="step-circle-icon"><i class="bi bi-arrow-right"></i></span>
                        <span>ফর্ম জমা দিন &rarr;</span>
                    </a>
                </div>
                <div class="consult-step-img-wrap">
                    <img src="<?= asset('assets/images/general/consult_step1_phone.png') ?>" alt="তথ্য ও ছবি দিন">
                </div>
            </div>
        </div>

        <!-- Step 2 Card -->
        <div class="col-lg-4 col-md-6 col-12">
            <div class="consult-step-card step-green">
                <div class="consult-step-content">
                    <div class="consult-step-top">
                        <div class="consult-step-icon-box">
                            <i class="bi bi-person-check-fill"></i>
                        </div>
                        <span class="consult-step-pill">02</span>
                    </div>
                    <h3 class="consult-step-title">বিশেষজ্ঞ টিমের মতামত</h3>
                    <p class="consult-step-desc">
                        আমাদের নিবন্ধিত মৎস্য বিশেষজ্ঞ আপনার সমস্যাটি সূক্ষ্ম পর্যালোচনা করবেন।
                    </p>
                    <a href="<?= url('consultation/create.php') ?>" class="step-btn-green">
                        <span class="step-circle-icon"><i class="bi bi-arrow-right"></i></span>
                        <span>পরামর্শের জন্য অপেক্ষা করুন &rarr;</span>
                    </a>
                </div>
                <div class="consult-step-img-wrap">
                    <img src="<?= asset('assets/images/general/consult_step2_expert.png') ?>" alt="বিশেষজ্ঞ টিমের মতামত">
                </div>
            </div>
        </div>

        <!-- Step 3 Card -->
        <div class="col-lg-4 col-md-6 col-12">
            <div class="consult-step-card step-purple">
                <div class="consult-step-content">
                    <div class="consult-step-top">
                        <div class="consult-step-icon-box">
                            <i class="bi bi-file-earmark-medical-fill"></i>
                        </div>
                        <span class="consult-step-pill">03</span>
                    </div>
                    <h3 class="consult-step-title">প্রেসক্রিপশন ও সমাধান</h3>
                    <p class="consult-step-desc">
                        আপনার সমস্যার নির্দিষ্ট সমাধান ও প্রেসক্রিপশন প্রদান করা হবে।
                    </p>
                    <a href="<?= url('account/consultations.php') ?>" class="step-btn-purple">
                        <span class="step-circle-icon"><i class="bi bi-arrow-right"></i></span>
                        <span>সমাধান দেখুন &rarr;</span>
                    </a>
                </div>
                <div class="consult-step-img-wrap">
                    <img src="<?= asset('assets/images/general/consult_step3_prescription.png') ?>" alt="প্রেসক্রিপশন ও সমাধান">
                </div>
            </div>
        </div>
    </div>

    <!-- Direct Contact Channels Section (Added after 'কীভাবে কাজ করে?') -->
    <div class="consult-direct-contact-section">
        <div class="row g-4 align-items-stretch">
            <!-- Left: Main Contact Info & 4 Channel Cards -->
            <div class="col-xl-8 col-lg-7 col-12">
                <div class="consult-contact-card">
                    <div>
                        <span class="contact-pill-badge">
                            <i class="bi bi-headset"></i>
                            <span>সরাসরি যোগাযোগ</span>
                        </span>
                        <h2 class="contact-card-title">আমাদের সাথে <span class="text-teal">যোগাযোগ করুন</span></h2>
                        <p class="contact-card-desc">
                            যেকোনো প্রশ্ন, পরামর্শ বা সহায়তার জন্য আমাদের সাথে সরাসরি যোগাযোগ করুন।<br class="d-none d-md-inline">
                            আমরা সবসময় আপনার পাশে আছি।
                        </p>
                    </div>

                    <!-- 4 Channel Cards Row -->
                    <div class="row g-3">
                        <!-- Channel 1: কল করুন -->
                        <div class="col-xl-3 col-md-6 col-12">
                            <div class="contact-channel-item">
                                <div class="channel-top">
                                    <div class="channel-icon-circle bg-green">
                                        <i class="bi bi-telephone-fill"></i>
                                    </div>
                                    <div>
                                        <div class="channel-name">কল করুন</div>
                                        <div class="channel-sub">সরাসরি কথা বলুন আমাদের বিশেষজ্ঞদের সাথে</div>
                                    </div>
                                </div>
                                <a href="tel:<?= e($phone) ?>" class="channel-pill-link pill-green">
                                    <i class="bi bi-telephone-fill"></i>
                                    <span><?= e($phone) ?></span>
                                    <i class="bi bi-arrow-right ms-auto"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Channel 2: WhatsApp করুন -->
                        <div class="col-xl-3 col-md-6 col-12">
                            <div class="contact-channel-item">
                                <div class="channel-top">
                                    <div class="channel-icon-circle bg-whatsapp">
                                        <i class="bi bi-whatsapp"></i>
                                    </div>
                                    <div>
                                        <div class="channel-name">WhatsApp করুন</div>
                                        <div class="channel-sub">দ্রুত ও সহজে মেসেজ করুন</div>
                                    </div>
                                </div>
                                <a href="<?= e(setting('whatsapp_url', 'https://wa.me/8801979606212')) ?>" target="_blank" rel="noopener noreferrer" class="channel-pill-link pill-whatsapp">
                                    <i class="bi bi-whatsapp"></i>
                                    <span><?= e($phone) ?></span>
                                    <i class="bi bi-arrow-right ms-auto"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Channel 3: ইমেইল করুন -->
                        <div class="col-xl-3 col-md-6 col-12">
                            <div class="contact-channel-item">
                                <div class="channel-top">
                                    <div class="channel-icon-circle bg-blue">
                                        <i class="bi bi-envelope-fill"></i>
                                    </div>
                                    <div>
                                        <div class="channel-name">ইমেইল করুন</div>
                                        <div class="channel-sub">আপনার বিস্তারিত লিখে পাঠান</div>
                                    </div>
                                </div>
                                <a href="mailto:<?= e($email) ?>" class="channel-pill-link pill-blue">
                                    <i class="bi bi-envelope-fill"></i>
                                    <span><?= e($email) ?></span>
                                    <i class="bi bi-arrow-right ms-auto"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Channel 4: অফিসের ঠিকানা -->
                        <div class="col-xl-3 col-md-6 col-12">
                            <div class="contact-channel-item">
                                <div class="channel-top">
                                    <div class="channel-icon-circle bg-purple">
                                        <i class="bi bi-geo-alt-fill"></i>
                                    </div>
                                    <div>
                                        <div class="channel-name">অফিসের ঠিকানা</div>
                                        <div class="channel-sub">সরাসরি সাক্ষাতের জন্য আমাদের অফিসে আসুন</div>
                                    </div>
                                </div>
                                <a href="<?= url('contact/') ?>" class="channel-pill-link pill-purple">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <span><?= e($address) ?></span>
                                    <i class="bi bi-arrow-right ms-auto"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Support Agent Card -->
            <div class="col-xl-4 col-lg-5 col-12">
                <div class="consult-support-agent-card">
                    <div class="consult-support-content">
                        <div class="consult-support-icon">
                            <i class="bi bi-headset"></i>
                        </div>
                        <h3 class="consult-support-title">দ্রুত সহায়তা চাই?</h3>
                        <p class="consult-support-sub">আমাদের বিশেষজ্ঞ টিম আপনার জন্য প্রস্তুত।</p>
                        
                        <a href="<?= e(setting('whatsapp_url', 'https://wa.me/8801979606212')) ?>" target="_blank" rel="noopener noreferrer" class="consult-support-btn" title="হোয়াটসঅ্যাপ / কল করুন">
                            <div class="btn-icon">
                                <i class="bi bi-whatsapp"></i>
                            </div>
                            <div class="btn-text">
                                <span class="phone-num"><?= e($phone) ?></span>
                                <span class="phone-label">এখনই কল / মেসেজ করুন</span>
                            </div>
                            <i class="bi bi-arrow-right btn-arrow"></i>
                        </a>
                    </div>
                    <img src="<?= asset('assets/images/general/consult_agent_clean.png') ?>" alt="Support Agent" class="consult-support-agent-img">
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Account CTA Banner -->
    <div class="consult-cta-banner">
        <div class="consult-cta-left">
            <div class="consult-cta-headset">
                <i class="bi bi-headset"></i>
            </div>
            <div>
                <h3 class="consult-cta-title">আপনার কি একটি সক্রিয় অ্যাকাউন্ট আছে?</h3>
                <p class="consult-cta-sub">পার্সোনালাইজড সেবা পেতে ও আরও দ্রুত সাপোর্ট পেতে লগইন করুন।</p>
            </div>
        </div>
        <div class="consult-cta-actions">
            <?php if (is_logged_in()): ?>
                <a href="<?= url('account/consultations.php') ?>" class="btn-cta-white">
                    <span>আমার পরামর্শসমূহ</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
                <a href="<?= url('consultation/create.php') ?>" class="btn-cta-outline">
                    <span>নতুন পরামর্শ পাঠান</span>
                </a>
            <?php else: ?>
                <a href="<?= url('auth/login.php') ?>" class="btn-cta-white">
                    <span>লগইন করুন</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
                <a href="<?= url('auth/register.php') ?>" class="btn-cta-outline">
                    <span>নিবন্ধন করুন</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
