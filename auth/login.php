<?php
// ============================================================
// FarmersBD — Modern User Login (Bangladeshi Fisherman Boat Banner)
// Supports: email + password OR mobile + password
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/flash.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/validation.php';
require_once dirname(__DIR__) . '/includes/seo.php';

if (is_logged_in()) redirect(BASE_URL . '/account/dashboard.php');

$errors   = [];
$loginVal = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $loginInput = trim($_POST['login'] ?? '');
    $password   = $_POST['password']  ?? '';
    $loginVal   = e($loginInput);

    if (empty($loginInput) || empty($password)) {
        $errors[] = 'মোবাইল/ইমেইল এবং পাসওয়ার্ড আবশ্যক।';
    } else {
        // Try to find user by email or mobile
        $mobile   = preg_replace('/\D/', '', $loginInput);
        $isEmail  = filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        
        if ($isEmail) {
            $user = db_query_one("SELECT * FROM users WHERE email = ?", [strtolower($loginInput)]);
        } else {
            $user = db_query_one("SELECT * FROM users WHERE mobile = ?", [$mobile]);
        }

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'মোবাইল/ইমেইল বা পাসওয়ার্ড সঠিক নয়।';
        } elseif (!$user['is_active']) {
            $errors[] = 'আপনার অ্যাকাউন্টটি নিষ্ক্রিয় করা হয়েছে। সহায়তার জন্য যোগাযোগ করুন।';
        } else {
            login_user($user);
            flash('স্বাগতম, ' . $user['name'] . '!', FLASH_SUCCESS);

            // Redirect to intended page or dashboard
            $redirect = filter_var($_GET['redirect'] ?? '', FILTER_SANITIZE_URL);
            $redirect = (str_starts_with($redirect, '/') && !str_starts_with($redirect, '//')) 
                        ? $redirect 
                        : BASE_URL . '/account/dashboard.php';
            redirect($redirect);
        }
    }
}

$page_seo = ['title' => 'লগইন | ' . setting('site_name', 'FarmersBD')];
include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/navbar.php';
?>

<section class="auth-page-section">
    <div class="auth-card-wrapper shadow-lg">
        <div class="row g-0">
            <!-- Left Showcase Banner with Real Bangladeshi Fish Swimming Photo -->
            <div class="col-lg-5 auth-banner-side d-none d-lg-flex">
                <div>
                    <div class="d-flex align-items-center gap-2.5 mb-4">
                        <div style="width: 44px; height: 44px; border-radius: 50%; border: 2px solid #34d399; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(5, 150, 105, 0.4) 0%, rgba(2, 132, 199, 0.4) 100%); backdrop-filter: blur(8px); box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);">
                            <i class="bi bi-water text-white fs-4"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold auth-banner-brand mb-0">FarmersBD</h3>
                            <small class="auth-banner-subbrand fw-semibold">স্মার্ট অ্যাকোয়া টেকনোলজি</small>
                        </div>
                    </div>

                    <span class="badge auth-banner-badge px-3.5 py-2 rounded-pill shadow-sm mb-3">
                        <i class="bi bi-water me-1 text-warning"></i> দেশীয় মাছের তথ্য ও স্বাস্থ্য সেবামূলক ড্যাশবোর্ড
                    </span>

                    <h2 class="display-6 fw-bold auth-banner-heading mb-3">
                        স্মার্ট মৎস্য খামারের <span>বিশ্বস্ত সহযোগী</span>
                    </h2>
                    <p class="auth-banner-desc lh-lg small mb-4">
                        আপনার অ্যাকাউন্ট দিয়ে লগইন করে কৃত্রিম বুদ্ধিমত্তা চালিত মাছের রোগ নির্ণয়, সরাসরি বিশেষজ্ঞ পরামর্শ এবং প্রিমিয়াম অ্যাকোয়া পণ্য ব্যবহার করুন।
                    </p>

                    <ul class="auth-feature-list">
                        <li class="auth-feature-item">
                            <div class="auth-feature-icon"><i class="bi bi-check-lg"></i></div>
                            <span class="text-white">১০,০০০+ নিবন্ধিত সফল খামারি</span>
                        </li>
                        <li class="auth-feature-item">
                            <div class="auth-feature-icon"><i class="bi bi-cpu"></i></div>
                            <span class="text-white">AI-চালিত তাৎক্ষণিক রোগ নির্ণয়</span>
                        </li>
                        <li class="auth-feature-item">
                            <div class="auth-feature-icon"><i class="bi bi-shield-check"></i></div>
                            <span class="text-white">১০০% নিরাপদ অ্যাকাউন্ট ও সেবা</span>
                        </li>
                    </ul>
                </div>

                <div class="pt-4 border-top border-white border-opacity-25 d-flex justify-content-between align-items-center">
                    <small class="text-white-50">© FarmersBD Aquaculture</small>
                    <small class="text-white fw-bold"><i class="bi bi-headset me-1 text-warning"></i>২৪/৭ সাপোর্ট</small>
                </div>
            </div>

            <!-- Right Login Form Panel -->
            <div class="col-lg-7 auth-form-side">
                <!-- Navigation Tabs -->
                <div class="auth-tab-pills">
                    <a href="<?= url('auth/login.php') ?>" class="auth-tab-pill active">
                        <i class="bi bi-box-arrow-in-right me-1"></i> লগইন করুন
                    </a>
                    <a href="<?= url('auth/register.php') ?>" class="auth-tab-pill">
                        <i class="bi bi-person-plus me-1"></i> নতুন অ্যাকাউন্ট
                    </a>
                </div>

                <div class="mb-4">
                    <h3 class="fw-bold text-dark mb-1">অ্যাকাউন্টে লগইন করুন</h3>
                    <p class="text-muted small">আপনার নিবন্ধিত মোবাইল নম্বর বা ইমেইল এবং পাসওয়ার্ড দিন</p>
                </div>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger border-0 rounded-3 shadow-sm py-2.5 px-3 mb-4 small">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div>
                            <?php foreach ($errors as $err): ?>
                                <div><?= e($err) ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <form method="POST" action="" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-3.5">
                        <label for="login-input" class="form-label fw-semibold text-secondary small">মোবাইল নম্বর বা ইমেইল <span class="text-danger">*</span></label>
                        <div class="input-group auth-input-group">
                            <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
                            <input type="text" class="form-control"
                                   id="login-input" name="login"
                                   value="<?= $loginVal ?>"
                                   placeholder="01XXXXXXXXX বা example@email.com"
                                   required autocomplete="username">
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="login-password" class="form-label fw-semibold text-secondary small mb-0">পাসওয়ার্ড <span class="text-danger">*</span></label>
                        </div>
                        <div class="input-group auth-input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control"
                                   id="login-password" name="password"
                                   placeholder="পাসওয়ার্ড লিখুন"
                                   required autocomplete="current-password">
                            <button class="btn btn-outline-secondary border-start-0" type="button" id="toggleLoginPwd" aria-label="পাসওয়ার্ড দেখুন" style="border-color: #cbd5e1; border-top-right-radius: 12px; border-bottom-right-radius: 12px;">
                                <i class="bi bi-eye" id="loginEyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-auth-submit w-100 mb-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>লগইন করুন
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="text-muted small mb-0">
                        অ্যাকাউন্ট নেই?
                        <a href="<?= url('auth/register.php') ?>" class="fw-bold text-primary text-decoration-none">নতুন অ্যাকাউন্ট তৈরি করুন</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('toggleLoginPwd')?.addEventListener('click', () => {
    const pwd  = document.getElementById('login-password');
    const icon = document.getElementById('loginEyeIcon');
    if (pwd.type === 'password') { pwd.type = 'text'; icon.className = 'bi bi-eye-slash'; }
    else                         { pwd.type = 'password'; icon.className = 'bi bi-eye'; }
});
</script>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
