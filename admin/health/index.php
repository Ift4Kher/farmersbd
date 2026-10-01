<?php
// ============================================================
// FarmersBD — Site Health Diagnostic
// ============================================================
$base = dirname(dirname(__DIR__));
require_once $base . '/config/config.php';
require_once $base . '/config/database.php';
require_once $base . '/includes/functions.php';
require_once $base . '/includes/admin-auth.php';

require_admin();

$diagnostics = [];

// 1. PHP Version
$diagnostics[] = [
    'name' => 'PHP Version',
    'status' => version_compare(PHP_VERSION, '8.0.0', '>=') ? 'OK' : 'Warning',
    'message' => 'Current: ' . PHP_VERSION . ' (Recommended: 8.0+)'
];

// 2. Database Connection
try {
    $pdo = get_db_connection();
    $db_status = 'OK';
    $db_message = 'Connected successfully';
} catch (Exception $e) {
    $db_status = 'Error';
    $db_message = 'Connection failed: ' . $e->getMessage();
}
$diagnostics[] = [
    'name' => 'Database Connection',
    'status' => $db_status,
    'message' => $db_message
];

// 3. Writable Directories
$dirs_to_check = [
    '/uploads' => $base . '/uploads',
    '/logs' => $base . '/logs'
];
foreach ($dirs_to_check as $label => $path) {
    if (!is_dir($path)) {
        @mkdir($path, 0755, true);
    }
    $is_writable = is_writable($path);
    $diagnostics[] = [
        'name' => "Directory $label",
        'status' => $is_writable ? 'OK' : 'Error',
        'message' => $is_writable ? 'Writable' : 'Not writable! Please check permissions (0755).'
    ];
}

// 4. PDO Extension
$diagnostics[] = [
    'name' => 'PDO Extension',
    'status' => extension_loaded('pdo_mysql') ? 'OK' : 'Error',
    'message' => extension_loaded('pdo_mysql') ? 'Loaded' : 'pdo_mysql extension is missing.'
];

// 5. GD Library (for image processing)
$diagnostics[] = [
    'name' => 'GD Library (Image Processing)',
    'status' => extension_loaded('gd') ? 'OK' : 'Warning',
    'message' => extension_loaded('gd') ? 'Loaded' : 'GD extension is missing. Image uploads might fail.'
];

// 6. MBString
$diagnostics[] = [
    'name' => 'MBString Extension',
    'status' => extension_loaded('mbstring') ? 'OK' : 'Warning',
    'message' => extension_loaded('mbstring') ? 'Loaded' : 'mbstring extension is missing. Bengali text might not process correctly.'
];

$page_title = "সিস্টেম হেলথ ডায়াগনস্টিক — এডমিন";
require_once $base . '/admin/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-heart-pulse text-primary me-2"></i> সিস্টেম হেলথ ডায়াগনস্টিক</h3>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 30%">Check</th>
                        <th style="width: 20%">Status</th>
                        <th style="width: 50%">Message / Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($diagnostics as $d): ?>
                        <tr>
                            <td class="fw-bold text-secondary"><?= htmlspecialchars($d['name']) ?></td>
                            <td>
                                <?php if ($d['status'] === 'OK'): ?>
                                    <span class="badge bg-success rounded-pill px-3"><i class="bi bi-check-circle me-1"></i> OK</span>
                                <?php elseif ($d['status'] === 'Warning'): ?>
                                    <span class="badge bg-warning text-dark rounded-pill px-3"><i class="bi bi-exclamation-triangle me-1"></i> Warning</span>
                                <?php else: ?>
                                    <span class="badge bg-danger rounded-pill px-3"><i class="bi bi-x-circle me-1"></i> Error</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted"><?= htmlspecialchars($d['message']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once $base . '/admin/includes/footer.php'; ?>
