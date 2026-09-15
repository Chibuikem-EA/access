<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('student');
require_active_user();
$uid = (int) current_user()['id'];
$vehicleModel = new Vehicle($pdo);
$otpModel = new Otp($pdo);
$approvedVehicles = $vehicleModel->approvedForUser($uid);
$activeOtp = $otpModel->activeForUser($uid);
$otpHistory = $otpModel->forUser($uid);
$expiryMinutes = (int) setting($pdo, 'otp_expiry_minutes', '5');
$page = ['title' => 'Request OTP', 'active' => 'otp'];
require dirname(__DIR__, 2) . '/includes/header.php';
echo '<h1 class="h3 mb-3">OTP for gate entry</h1>';
require dirname(__DIR__) . '/shared/otp_panel.php';
require dirname(__DIR__, 2) . '/includes/footer.php';
