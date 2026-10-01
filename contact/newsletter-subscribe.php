<?php
// ============================================================
// FarmersBD — Newsletter Subscription Handler
// POST /contact/newsletter-subscribe.php — Handles both AJAX & Standard POST
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/flash.php';

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
       || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'অবৈধ অনুরোধ।']);
        exit;
    }
    redirect(url());
}

$email = strtolower(trim($_POST['email'] ?? ''));

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $msg = 'অনুগ্রহ করে একটি সঠিক ইমেইল ঠিকানা প্রদান করুন।';
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $msg]);
        exit;
    }
    set_flash('danger', $msg);
    redirect($_SERVER['HTTP_REFERER'] ?? url());
}

try {
    $pdo = db();

    // Check if email already subscribed
    $stmt = $pdo->prepare("SELECT id, is_active FROM newsletter_subscribers WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing) {
        if (!$existing['is_active']) {
            // Reactivate subscriber
            $update = $pdo->prepare("UPDATE newsletter_subscribers SET is_active = 1 WHERE id = ?");
            $update->execute([$existing['id']]);
            $msg = 'আপনার ইমেইল পুনরায় সাবস্ক্রাইব করা হয়েছে! ধন্যবাদ।';
        } else {
            $msg = 'এই ইমেইলটি ইতিপূর্বে সাবস্ক্রাইব করা হয়েছে।';
        }
    } else {
        // Insert new subscriber
        $insert = $pdo->prepare("INSERT INTO newsletter_subscribers (email, is_active, created_at) VALUES (?, 1, NOW())");
        $insert->execute([$email]);
        $msg = 'ধন্যবাদ! আপনি সফলভাবে আমাদের নিউজলেটারে সাবস্ক্রাইব করেছেন।';
    }

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'message' => $msg]);
        exit;
    }

    set_flash('success', $msg);
    redirect($_SERVER['HTTP_REFERER'] ?? url());

} catch (Exception $e) {
    error_log('[Newsletter Subscription Exception] ' . $e->getMessage());
    $errMsg = 'নিউজলেটার সাবস্ক্রিপশন সম্পন্ন হতে সমস্যা হয়েছে। পুনরায় চেষ্টা করুন।';
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $errMsg]);
        exit;
    }

    set_flash('danger', $errMsg);
    redirect($_SERVER['HTTP_REFERER'] ?? url());
}
