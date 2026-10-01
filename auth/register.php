<?php
// ============================================================
// FarmersBD — Modern User Registration (Bangladeshi Fisherman Boat Banner)
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

// Already logged in?
if (is_logged_in()) redirect(BASE_URL . '/account/dashboard.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $data = [
        'name'             => trim($_POST['name']     ?? ''),
        'mobile'           => trim($_POST['mobile']   ?? ''),
        'email'            => strtolower(trim($_POST['email'] ?? '')),
        'password'         => $_POST['password']         ?? '',
        'password_confirm' => $_POST['password_confirm'] ?? '',
    ];

    $v = new Validator($data);
    $v->required('name',     'নাম')
      ->max_length('name', 150, 'নাম')
      ->required('mobile',   'মোবাইল নম্বর')
      ->mobile('mobile')
      ->required('email',    'ইমেইল')
      ->email('email')
      ->required('password', 'পাসওয়ার্ড')
      ->min_length('password', 8, 'পাসওয়ার্ড')
      ->password_strength('password')
      ->required('password_confirm', 'পাসওয়ার্ড নিশ্চিতকরণ')
      ->same('password_confirm', 'password', 'পাসওয়ার্ড নিশ্চিতকরণ')
      ->unique_in_db('email',  'users', 'email',  null, 'ইমেইল')
      ->unique_in_db('mobile', 'users', 'mobile', null, 'মোবাইল নম্বর');

    if ($v->passes()) {
        $hashedPw = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $mobile   = preg_replace('/\D/', '', $data['mobile']);

        $userId = db_insert(
            "INSERT INTO users (name, mobile, email, password) VALUES (?, ?, ?, ?)",
            [$data['name'], $mobile, $data['email'], $hashedPw]
        );

        $user = db_query_one("SELECT * FROM users WHERE id = ?", [$userId]);
        login_user($user);

        flash('স্বাগতম, ' . $data['name'] . '! আপনার অ্যাকাউন্ট সফলভাবে তৈরি হয়েছে।', FLASH_SUCCESS);
        redirect(BASE_URL . '/account/dashboard.php');
    }

    $errors = $v->errors();
}

$page_seo = ['title' => 'রেজিস্ট্রেশন | ' . setting('site_name', 'FarmersBD')];
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
                        আজই যোগ দিন <span>FarmersBD পরিবারে</span>
                    </h2>
                    <p class="auth-banner-desc lh-lg small mb-4">
                        ফ্রি অ্যাকাউন্ট তৈরি করে উপভোগ করুন ফ্রি AI মাছের রোগ নির্ণয়, বিশেষজ্ঞদের সরাসরি পরামর্শ এবং অর্ডারে বিশেষ ডিসকাউন্ট।
                    </p>

                    <ul class="auth-feature-list">
                        <li class="auth-feature-item">
                            <div class="auth-feature-icon"><i class="bi bi-check-lg"></i></div>
                            <span class="text-white">১ মিনিটে দ্রুত ও সহজ রেজিস্ট্রেশন</span>
                        </li>
                        <li class="auth-feature-item">
                            <div class="auth-feature-icon"><i class="bi bi-bag-check"></i></div>
                            <span class="text-white">অর্ডার ট্র্যাকিং ও হিস্ট্রি সুবিধা</span>
                        </li>
                        <li class="auth-feature-item">
                            <div class="auth-feature-icon"><i class="bi bi-chat-left-dots"></i></div>
                            <span class="text-white">অভিজ্ঞ মৎস্যবিজ্ঞানী পরামর্শ</span>
                        </li>
                    </ul>
                </div>

                <div class="pt-4 border-top border-white border-opacity-25 d-flex justify-content-between align-items-center">
                    <small class="text-white-50">© FarmersBD Aquaculture</small>
                    <small class="text-white fw-bold"><i class="bi bi-headset me-1 text-warning"></i>২৪/৭ সাপোর্ট</small>
                </div>
            </div>

            <!-- Right Register Form Panel -->
            <div class="col-lg-7 auth-form-side">
                <!-- Navigation Tabs -->
                <div class="auth-tab-pills">
                    <a href="<?= url('auth/login.php') ?>" class="auth-tab-pill">
                        <i class="bi bi-box-arrow-in-right me-1"></i> লগইন করুন
                    </a>
                    <a href="<?= url('auth/register.php') ?>" class="auth-tab-pill active">
                        <i class="bi bi-person-plus me-1"></i> নতুন অ্যাকাউন্ট
                    </a>
                </div>

                <div class="mb-4">
                    <h3 class="fw-bold text-dark mb-1">নতুন অ্যাকাউন্ট তৈরি করুন</h3>
                    <p class="text-muted small">নিচের তথ্যগুলো সঠিকভা‌বে পূরণ করুন</p>
                </div>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger border-0 rounded-3 shadow-sm py-2 px-3 mb-4 small">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form method="POST" action="" novalidate>
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="reg-name" class="form-label fw-semibold text-secondary small">পূর্ণ নাম <span class="text-danger">*</span></label>
                            <div class="input-group auth-input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                       id="reg-name" name="name" value="<?= old('name') ?>"
                                       placeholder="আপনার নাম" required autocomplete="name">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="reg-mobile" class="form-label fw-semibold text-secondary small">মোবাইল নম্বর <span class="text-danger">*</span></label>
                            <div class="input-group auth-input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="tel" class="form-control <?= isset($errors['mobile']) ? 'is-invalid' : '' ?>"
                                       id="reg-mobile" name="mobile" value="<?= old('mobile') ?>"
                                       placeholder="01XXXXXXXXX" required autocomplete="tel">
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="reg-email" class="form-label fw-semibold text-secondary small">ইমেইল <span class="text-danger">*</span></label>
                            <div class="input-group auth-input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                       id="reg-email" name="email" value="<?= old('email') ?>"
                                       placeholder="example@email.com" required autocomplete="email">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="reg-password" class="form-label fw-semibold text-secondary small">পাসওয়ার্ড <span class="text-danger">*</span></label>
                            <div class="input-group auth-input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                       id="reg-password" name="password" placeholder="কমপক্ষে ৮ অক্ষর"
                                       required autocomplete="new-password" minlength="8">
                                <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePwd" aria-label="পাসওয়ার্ড দেখুন" style="border-color: #cbd5e1; border-top-right-radius: 12px; border-bottom-right-radius: 12px;">
                                    <i class="bi bi-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="reg-password-confirm" class="form-label fw-semibold text-secondary small">পাসওয়ার্ড নিশ্চিত করুন <span class="text-danger">*</span></label>
                            <div class="input-group auth-input-group">
                                <span class="input-group-text"><i class="bi bi-check2-circle"></i></span>
                                <input type="password" class="form-control"
                                       id="reg-password-confirm" name="password_confirm"
                                       placeholder="পাসওয়ার্ড পুনরায় লিখুন" required autocomplete="new-password">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-auth-submit w-100 mt-4 mb-3">
                        <i class="bi bi-person-plus-fill me-2"></i>অ্যাকাউন্ট তৈরি করুন
                    </button>
                </form>

                <div class="text-center mt-3 pt-3 border-top">
                    <p class="text-muted small mb-0">
                        ইতিমধ্যে অ্যাকাউন্ট আছে?
                        <a href="<?= url('auth/login.php') ?>" class="fw-bold text-primary text-decoration-none">লগইন করুন</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('togglePwd')?.addEventListener('click', () => {
    const pwd  = document.getElementById('reg-password');
    const icon = document.getElementById('eyeIcon');
    if (pwd.type === 'password') { pwd.type = 'text'; icon.className = 'bi bi-eye-slash'; }
    else                         { pwd.type = 'password'; icon.className = 'bi bi-eye'; }
});
</script>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
