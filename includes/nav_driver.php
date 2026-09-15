<?php
/** @var string $active */
$link = static function (string $href, string $key, string $label, string $icon) use ($active): void {
    $cls = $active === $key ? 'nav-link active' : 'nav-link';
    echo '<li class="nav-item"><a class="' . $cls . '" href="' . e(url($href)) . '"><i class="bi ' . $icon . ' me-1"></i>' . e($label) . '</a></li>';
};
$link('views/driver/dashboard.php', 'dashboard', 'Dashboard', 'bi-speedometer2');
$link('views/driver/vehicles.php', 'vehicles', 'Vehicle', 'bi-bus-front');
$link('views/driver/otp.php', 'otp', 'Request OTP', 'bi-key');
$link('views/driver/shuttle.php', 'shuttle', 'Shuttle Status', 'bi-signpost-2');
$link('views/driver/history.php', 'history', 'OTP History', 'bi-clock-history');
$link('views/driver/profile.php', 'profile', 'Profile', 'bi-person');
