<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('driver');
require_active_user();
$vehicles = (new Vehicle($pdo))->forUser((int)current_user()['id']);
$showShuttleFields = true;
$page = ['title' => 'Vehicle', 'active' => 'vehicles'];
require dirname(__DIR__, 2) . '/includes/header.php';
echo '<h1 class="h3 mb-3">Register shuttle vehicle</h1>';
require dirname(__DIR__) . '/shared/vehicles_panel.php';
require dirname(__DIR__, 2) . '/includes/footer.php';
