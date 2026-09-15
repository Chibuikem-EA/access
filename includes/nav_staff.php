<?php
/** @var string $active */
$link = static function (string $href, string $key, string $label, string $icon) use ($active): void {
    $cls = $active === $key ? 'nav-link active' : 'nav-link';
    echo '<li class="nav-item"><a class="' . $cls . '" href="' . e(url($href)) . '"><i class="bi ' . $icon . ' me-1"></i>' . e($label) . '</a></li>';
};
$link('views/staff/dashboard.php', 'dashboard', 'Dashboard', 'bi-speedometer2');
$link('views/staff/vehicles.php', 'vehicles', 'My Vehicles', 'bi-car-front');
$link('views/staff/otp.php', 'otp', 'Request OTP', 'bi-key');
$link('views/staff/history.php', 'history', 'Entry/Exit', 'bi-journal-text');
$link('views/staff/profile.php', 'profile', 'Profile', 'bi-person');
