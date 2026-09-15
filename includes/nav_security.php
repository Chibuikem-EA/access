<?php
/** @var string $active */
$link = static function (string $href, string $key, string $label, string $icon) use ($active): void {
    $cls = $active === $key ? 'nav-link active' : 'nav-link';
    echo '<li class="nav-item"><a class="' . $cls . '" href="' . e(url($href)) . '"><i class="bi ' . $icon . ' me-1"></i>' . e($label) . '</a></li>';
};
$link('views/security/dashboard.php', 'dashboard', 'Dashboard', 'bi-speedometer2');
$link('views/security/verify_otp.php', 'verify', 'Verify OTP', 'bi-shield-check');
$link('views/security/exit.php', 'exit', 'Record Exit', 'bi-box-arrow-right');
$link('views/security/visitors.php', 'visitors', 'Visitors', 'bi-person-plus');
$link('views/security/history.php', 'history', 'Today / History', 'bi-clock-history');
