<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('student');
require_active_user();
$userRow = (new User($pdo))->findById((int)current_user()['id']);
$page = ['title' => 'Profile', 'active' => 'profile'];
require dirname(__DIR__, 2) . '/includes/header.php';
echo '<h1 class="h3 mb-3">Edit profile</h1>';
require dirname(__DIR__) . '/shared/profile_form.php';
require dirname(__DIR__, 2) . '/includes/footer.php';
