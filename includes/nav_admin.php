<?php
/** @var string $active */
$link = static function (string $href, string $key, string $label, string $icon) use ($active): void {
    $cls = $active === $key ? 'nav-link active' : 'nav-link';
    echo '<li class="nav-item"><a class="' . $cls . '" href="' . e(url($href)) . '"><i class="bi ' . $icon . ' me-1"></i>' . e($label) . '</a></li>';
};
$link('views/admin/dashboard.php', 'dashboard', 'Dashboard', 'bi-speedometer2');
$link('views/admin/users.php', 'users', 'Users', 'bi-people');
$link('views/admin/vehicles.php', 'vehicles', 'Vehicles', 'bi-car-front');
$link('views/admin/otps.php', 'otps', 'OTP Requests', 'bi-key');
$link('views/admin/logs.php', 'logs', 'Entry/Exit', 'bi-journal-text');
$link('views/admin/visitors.php', 'visitors', 'Visitors', 'bi-person-badge');
$link('views/admin/shuttles.php', 'shuttles', 'Shuttles', 'bi-bus-front');
$link('views/admin/reports.php', 'reports', 'Reports', 'bi-bar-chart');
$link('views/admin/audit.php', 'audit', 'Audit', 'bi-clipboard-check');
$link('views/admin/settings.php', 'settings', 'Settings', 'bi-gear');
