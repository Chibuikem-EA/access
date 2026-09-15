<?php
/** @var string $active */
$link = static function (string $href, string $key, string $label, string $icon) use ($active): void {
    $cls = $active === $key ? 'nav-link active' : 'nav-link';
    echo '<li class="nav-item"><a class="' . $cls . '" href="' . e(url($href)) . '"><i class="bi ' . $icon . ' me-1"></i>' . e($label) . '</a></li>';
};
$link('views/student/dashboard.php', 'dashboard', 'Dashboard', 'bi-speedometer2');
$link('views/student/vehicles.php', 'vehicles', 'My Vehicles', 'bi-car-front');
$link('views/student/otp.php', 'otp', 'Request OTP', 'bi-key');
$link('views/student/history.php', 'history', 'Entry/Exit', 'bi-journal-text');
$link('views/student/profile.php', 'profile', 'Profile', 'bi-person');
