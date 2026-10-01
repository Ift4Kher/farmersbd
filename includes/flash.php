<?php
// ============================================================
// FarmersBD — Flash Messages
// ============================================================

/**
 * Set a flash message to be shown on the next request.
 */
function flash(string $message, string $type = FLASH_SUCCESS): void {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type']    = $type;
}

function set_flash(string $typeOrMessage, string $messageOrType = ''): void {
    if (in_array($typeOrMessage, ['success', 'danger', 'error', 'warning', 'info'])) {
        $type = $typeOrMessage === 'error' ? FLASH_ERROR : $typeOrMessage;
        flash($messageOrType, $type);
    } else {
        flash($typeOrMessage, $messageOrType ?: FLASH_SUCCESS);
    }
}

/**
 * Render flash messages if any exist and clear them.
 */
function show_flash(): string {
    if (empty($_SESSION['flash_message'])) return '';

    $message = $_SESSION['flash_message'];
    $type    = $_SESSION['flash_type'] ?? FLASH_SUCCESS;

    unset($_SESSION['flash_message'], $_SESSION['flash_type']);

    $icons = [
        FLASH_SUCCESS => 'check-circle-fill',
        FLASH_ERROR   => 'exclamation-triangle-fill',
        FLASH_WARNING => 'exclamation-circle-fill',
        FLASH_INFO    => 'info-circle-fill',
    ];
    $icon = $icons[$type] ?? 'info-circle-fill';

    return sprintf(
        '<div class="alert alert-%s alert-dismissible fade show d-flex align-items-center" role="alert" aria-live="assertive">
            <i class="bi bi-%s me-2 fs-5"></i>
            <div>%s</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>',
        e($type),
        e($icon),
        e($message)
    );
}

if (!function_exists('display_flash')) {
    function display_flash(): void {
        echo show_flash();
    }
}
