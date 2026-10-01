<?php
// ============================================================
// FarmersBD — Modern Clean Admin Portal Login
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/flash.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/admin-auth.php';

if (is_admin_logged_in()) redirect(BASE_URL . '/admin/');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        $error = 'অনুরোধটি যাচাই করা যায়নি। অনুগ্রহ করে পৃষ্ঠাটি রিফ্রেশ করুন।';
    } else {
        $email    = strtolower(trim($_POST['email']    ?? ''));
        $password = $_POST['password'] ?? '';

        $admin = db_query_one("SELECT * FROM admins WHERE email = ?", [$email]);

        if (!$admin || !password_verify($password, $admin['password'])) {
            $error = 'ইমেইল বা পাসওয়ার্ড সঠিক নয়।';
            error_log("[Admin Login] Failed attempt for: {$email} from " . ($_SERVER['REMOTE_ADDR'] ?? ''));
        } elseif (!$admin['is_active']) {
            $error = 'আপনার অ্যাডমিন অ্যাকাউন্টটি নিষ্ক্রিয় করা হয়েছে। প্রশাসনিক সহায়তায় যোগাযোগ করুন।';
        } else {
            admin_login($admin);
            redirect(BASE_URL . '/admin/');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন প্যানেল প্রবেশদ্বারে | FarmersBD</title>
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Google Fonts: Noto Sans Bengali -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        :root {
            --font-family: 'Noto Sans Bengali', system-ui, -apple-system, sans-serif;
            --admin-primary: #0f766e;
            --admin-secondary: #0284c7;
            --admin-dark: #0f172a;
        }

        body {
            font-family: var(--font-family);
            background-color: #f1f5f9;
            background-image: 
                radial-gradient(at 0% 0%, rgba(15, 118, 110, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(2, 132, 199, 0.08) 0px, transparent 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            margin: 0;
        }

        /* Main Container Card */
        .admin-login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 920px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        /* Left Side Showcase Panel (Pure Mesh Gradient Header - No Photo) */
        .admin-showcase-panel {
            background: linear-gradient(135deg, #0f766e 0%, #0369a1 50%, #0f172a 100%);
            padding: 3.5rem 2.5rem;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .admin-showcase-panel::before {
            content: '';
            position: absolute;
            top: 0; right: 0; bottom: 0; left: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.04' fill-rule='evenodd'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }

        .shield-icon-badge {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fbbf24;
            font-size: 1.6rem;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
        }

        .security-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.25);
            font-size: 0.8rem;
            font-weight: 600;
            color: #e0f2fe;
        }

        /* Right Form Panel */
        .admin-form-panel {
            padding: 3.5rem 2.75rem;
            background: #ffffff;
        }

        .form-label-custom {
            color: #1e293b;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .form-control-custom {
            background: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            color: #0f172a !important;
            border-radius: 10px !important;
            padding: 0.75rem 1rem 0.75rem 2.75rem !important;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-control-custom:focus {
            background: #ffffff !important;
            border-color: #0284c7 !important;
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.12) !important;
        }

        .form-control-custom::placeholder {
            color: #94a3b8;
        }

        .input-group-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            color: #64748b;
            font-size: 1.1rem;
            pointer-events: none;
        }

        .password-toggle-btn {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            background: none;
            border: none;
            color: #64748b;
            padding: 4px 8px;
            cursor: pointer;
            transition: color 0.2s;
        }

        .password-toggle-btn:hover {
            color: #0284c7;
        }

        .btn-admin-submit {
            background: linear-gradient(135deg, #0f766e 0%, #0284c7 100%);
            border: none;
            color: #ffffff;
            border-radius: 10px;
            padding: 0.85rem 1.5rem;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 6px 18px rgba(2, 132, 199, 0.25);
            transition: all 0.25s ease;
        }

        .btn-admin-submit:hover {
            background: linear-gradient(135deg, #0d5c56 0%, #0369a1 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(2, 132, 199, 0.35);
            color: #ffffff;
        }

        .btn-admin-submit:active {
            transform: translateY(0);
        }

        .back-to-site-link {
            color: #64748b;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: color 0.2s;
        }

        .back-to-site-link:hover {
            color: #0284c7;
        }
    </style>
</head>
<body>

<div class="admin-login-wrapper">
    <div class="row g-0">
        <!-- Left Showcase Panel (No Picture - Pure Clean Gradient) -->
        <div class="col-lg-5 admin-showcase-panel d-none d-lg-flex">
            <div>
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="shield-icon-badge">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div>
                        <h3 class="fw-extrabold text-white mb-0 fs-4">FarmersBD</h3>
                        <small class="text-warning fw-semibold">অ্যাডমিন কন্ট্রোল পোর্টাল</small>
                    </div>
                </div>

                <div class="mb-4">
                    <span class="security-chip">
                        <i class="bi bi-shield-check text-warning me-1"></i> ২৫৬-বিট এনক্রিপ্টেড সেশন
                    </span>
                </div>

                <h2 class="display-6 fw-bold text-white mb-3">
                    নিরাপদ নিয়ন্ত্রণ ও প্রশাসনিক ড্যাশবোর্ড
                </h2>
                <p class="text-white opacity-90 lh-lg small mb-0">
                    FarmersBD প্ল্যাটফর্মের ব্যবহারকারী ড্যাশবোর্ড, মৎস্য খাদ্য ক্যাটালগ, চিকিৎসা নির্দেশিকা ও নিরাপত্তা কন্ট্রোল নিয়ন্ত্রণ করুন।
                </p>
            </div>

            <div class="pt-4 border-top border-white border-opacity-20 d-flex justify-content-between align-items-center">
                <small class="text-white-50">© FarmersBD Control Center</small>
                <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 small">
                    <i class="bi bi-circle-fill me-1 text-success fs-6" style="font-size: 8px !important;"></i> সক্রিয়
                </span>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="col-lg-7 admin-form-panel">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 fw-bold text-dark mb-1">অ্যাডমিন প্রবেশদ্বার</h2>
                    <p class="text-muted small mb-0">আপনার প্রশাসনিক ইমেইল ও গোপন পাসওয়ার্ড দিন</p>
                </div>
                <div class="d-lg-none">
                    <div class="shield-icon-badge bg-primary text-white" style="width: 44px; height: 44px; font-size: 1.25rem;">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                </div>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger d-flex align-items-center gap-2 py-2.5 px-3 rounded-3 mb-4 small">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div><?= e($error) ?></div>
            </div>
            <?php endif; ?>

            <form method="POST" action="" autocomplete="off">
                <?= csrf_field() ?>

                <div class="mb-3.5 position-relative">
                    <label for="admin-email" class="form-label form-label-custom mb-1.5">অ্যাডমিন ইমেইল ঠিকানা</label>
                    <div class="position-relative">
                        <i class="bi bi-envelope input-group-icon"></i>
                        <input type="email" class="form-control form-control-custom" id="admin-email" name="email" placeholder="admin@farmersbd.com" required autocomplete="username" value="<?= e($_POST['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-4 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <label for="admin-password" class="form-label form-label-custom mb-0">গোপন পাসওয়ার্ড</label>
                    </div>
                    <div class="position-relative">
                        <i class="bi bi-key input-group-icon"></i>
                        <input type="password" class="form-control form-control-custom" id="admin-password" name="password" placeholder="••••••••" required autocomplete="current-password">
                        <button type="button" class="password-toggle-btn" id="togglePassword" aria-label="Toggle password visibility">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberSession" checked>
                        <label class="form-check-label text-secondary small" for="rememberSession">
                            নিরাপদ সেশন সচল রাখুন
                        </label>
                    </div>
                    <a href="<?= BASE_URL ?>/" class="back-to-site-link">
                        <i class="bi bi-house me-1"></i> ওয়েবসাইটে ফিরে যান
                    </a>
                </div>

                <button type="submit" class="btn btn-admin-submit w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-shield-check fs-5"></i>
                    <span>লগইন করুন</span>
                </button>
            </form>

            <div class="mt-4 pt-3 border-top border-slate-200 text-center">
                <small class="text-muted d-flex align-items-center justify-content-center gap-1">
                    <i class="bi bi-lock me-1"></i> সুরক্ষিত ও মনিটরকৃত প্রশাসনিক সিস্টেম • IP Logged
                </small>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Password Visibility Toggle
    const toggleBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('admin-password');
    const toggleIcon = document.getElementById('toggleIcon');

    toggleBtn.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        toggleIcon.classList.toggle('bi-eye');
        toggleIcon.classList.toggle('bi-eye-slash');
    });
</script>
</body>
</html>

            <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 text-center">
                <small class="text-secondary opacity-75 d-flex align-items-center justify-content-center gap-1">
                    <i class="bi bi-lock me-1"></i> সুরক্ষিত ও মনিটরকৃত প্রশাসনিক সিস্টেম • IP Logged
                </small>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Password Visibility Toggle
    const toggleBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('admin-password');
    const toggleIcon = document.getElementById('toggleIcon');

    toggleBtn.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        toggleIcon.classList.toggle('bi-eye');
        toggleIcon.classList.toggle('bi-eye-slash');
    });
</script>
</body>
</html>
