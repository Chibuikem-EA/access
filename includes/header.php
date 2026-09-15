<?php
declare(strict_types=1);
/** @var array $config */
/** @var array $page */
$user = current_user();
$pageTitle = ($page['title'] ?? 'Dashboard') . ' · ' . $config['app_name'];
$active = $page['active'] ?? '';
$role = $user['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= e(asset('css/style.css')) ?>" rel="stylesheet">
</head>
<body class="app-body">
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= e(url(dashboard_for_role($role))) ?>">
      <?= e($config['app_name']) ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="topNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php require __DIR__ . '/nav_' . $role . '.php'; ?>
      </ul>
      <div class="d-flex align-items-center gap-3 text-white-50 small">
        <span><i class="bi bi-person-circle me-1"></i><?= e($user['full_name'] ?? '') ?> · <?= e(role_label($role)) ?></span>
        <a class="btn btn-sm btn-outline-light" href="<?= e(url('logout.php')) ?>">Logout</a>
      </div>
    </div>
  </div>
</nav>
<main class="container-fluid py-4 px-3 px-lg-4">
  <?php if ($msg = flash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= e($msg) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>
  <?php if ($msg = flash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= e($msg) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>
  <?php if ($msg = flash('info')): ?>
    <div class="alert alert-info alert-dismissible fade show"><?= e($msg) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>
