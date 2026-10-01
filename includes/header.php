<?php
// ============================================================
// FarmersBD — HTML <head> Component
// Usage: include this at the top of every public page.
// $page_seo = ['title' => '...', 'description' => '...'] (optional)
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/flash.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/seo.php';
$page_seo = $page_seo ?? [];

if (defined('MAINTENANCE_MODE') && MAINTENANCE_MODE && strpos($_SERVER['SCRIPT_NAME'], 'maintenance.php') === false && !is_admin()) {
    redirect(BASE_URL . '/maintenance.php');
}

// Log live visitor traffic for admin analytics
log_traffic_visit();

?>
<!DOCTYPE html>
<html lang="bn" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <?php render_seo($page_seo); ?>

    <!-- Favicon -->
    <?php $favicon = setting('site_favicon'); ?>
    <?php if ($favicon): ?>
    <link rel="icon" href="<?= asset('uploads/general/' . e($favicon)) ?>">
    <?php else: ?>
    <link rel="icon" href="<?= asset('assets/images/logo/favicon.ico') ?>" type="image/x-icon">
    <?php endif; ?>

    <!-- Google Fonts: Noto Sans Bengali -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- FarmersBD Stylesheet -->
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>?v=<?= filemtime(dirname(__DIR__) . '/assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/responsive.css') ?>?v=<?= filemtime(dirname(__DIR__) . '/assets/css/responsive.css') ?>">

    <!-- Dynamic Typography from DB -->
    <?= typography_css() ?>

    <?php if (isset($extra_css)) echo $extra_css; ?>
</head>
<body>
