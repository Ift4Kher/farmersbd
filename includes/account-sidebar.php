<?php
$user = current_user();
?>
<div class="modern-card p-0 overflow-hidden">
    <div class="p-4 border-bottom bg-light bg-opacity-50">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                 style="width:48px;height:48px;font-size:1.2rem; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%) !important;">
                <?= mb_substr($user['name'] ?? 'U', 0, 1) ?>
            </div>
            <div>
                <div class="fw-bold small"><?= e($user['name'] ?? 'User') ?></div>
                <div class="text-muted" style="font-size:0.75rem"><?= e($user['mobile'] ?? '') ?></div>
            </div>
        </div>
    </div>
    <nav class="d-flex flex-column py-2">
        <?php
        $currentFile = basename($_SERVER['PHP_SELF']);
        $navItems = [
            ['file' => 'dashboard.php', 'icon' => 'bi-speedometer2', 'label' => 'ড্যাশবোর্ড'],
            ['file' => 'orders.php',    'icon' => 'bi-bag',          'label' => 'অর্ডার সমূহ'],
            ['file' => 'ai-history.php','icon' => 'bi-clock-history','label' => 'AI ইতিহাস'],
            ['file' => 'consultations.php','icon' => 'bi-chat-text', 'label' => 'পরামর্শ'],
            ['file' => 'profile.php',   'icon' => 'bi-gear',         'label' => 'প্রোফাইল সম্পাদনা'],
        ];
        foreach ($navItems as $n): ?>
        <a href="<?= url('account/' . $n['file']) ?>"
           class="nav-link px-4 py-3 border-bottom text-dark <?= $currentFile === $n['file'] ? 'fw-bold text-primary bg-light' : '' ?>" style="border-bottom: 1px solid rgba(0,0,0,0.05) !important;">
            <i class="bi <?= e($n['icon']) ?> me-2 <?= $currentFile === $n['file'] ? 'text-primary' : 'text-muted' ?>"></i><?= e($n['label']) ?>
        </a>
        <?php endforeach; ?>
        <a href="<?= url('auth/logout.php') ?>" class="nav-link px-4 py-3 text-danger fw-bold">
            <i class="bi bi-box-arrow-right me-2 text-danger"></i>লগআউট
        </a>
    </nav>
</div>
