<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$flash = $_SESSION['flash'] ?? null;
if (!empty($flash) && !empty($flash['message'])) {
    unset($_SESSION['flash']);

    $type = $flash['type'] ?? 'info';
    $allowed = ['success', 'error', 'warning', 'info'];
    if (!in_array($type, $allowed, true)) {
        $type = 'info';
    }

    $message = $flash['message'];
    $details = $flash['details'] ?? null;

    $role = in_array($type, ['error', 'warning'], true) ? 'alert' : 'status';
    $ariaLive = $role === 'alert' ? 'assertive' : 'polite';

    $icons = [
        'success' => 'ph ph-check-circle',
        'error' => 'ph ph-x-circle',
        'warning' => 'ph ph-warning-circle',
        'info' => 'ph ph-info'
    ];
    $icon = $icons[$type] ?? $icons['info'];
    ?>
    <div class="flash flash--<?= htmlspecialchars($type) ?>" role="<?= htmlspecialchars($role) ?>" aria-live="<?= htmlspecialchars($ariaLive) ?>">
        <i class="flash__icon <?= htmlspecialchars($icon) ?>" aria-hidden="true"></i>
        <div class="flash__content">
            <span class="flash__message"><?= htmlspecialchars($message) ?></span>
            <?php if (!empty($details)): ?>
                <span class="flash__details"><?= htmlspecialchars($details) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
?>
