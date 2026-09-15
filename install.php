<?php
/**
 * One-time setup helper: verifies DB and re-hashes default admin/security passwords
 * using PHP's password_hash so login always works after import.
 *
 * Visit once after importing schema.sql, then delete this file.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$message = null;
$error = null;

if (is_post()) {
    verify_csrf();
    try {
        $adminHash = password_hash('Admin@123', PASSWORD_DEFAULT);
        $secHash = password_hash('Security@123', PASSWORD_DEFAULT);

        $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, status = \'active\' WHERE email = ?');
        $stmt->execute([$adminHash, 'admin@campus.edu']);
        $adminOk = $stmt->rowCount() > 0;

        $stmt->execute([$secHash, 'security@campus.edu']);
        $secOk = $stmt->rowCount() > 0;

        if (!$adminOk) {
            $ins = $pdo->prepare(
                'INSERT INTO users (full_name, email, password_hash, phone, role, status, department)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $ins->execute([
                'System Administrator',
                'admin@campus.edu',
                $adminHash,
                '0000000000',
                'admin',
                'active',
                'Security Office',
            ]);
        }
        if (!$secOk) {
            $ins = $pdo->prepare(
                'INSERT INTO users (full_name, email, password_hash, phone, role, status, department)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $ins->execute([
                'Gate Security Officer',
                'security@campus.edu',
                $secHash,
                '0000000001',
                'security',
                'active',
                'Security Office',
            ]);
        }

        $message = 'Default passwords refreshed with PHP password_hash. You can log in now. Delete install.php when done.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$dbOk = false;
try {
    $pdo->query('SELECT 1 FROM users LIMIT 1');
    $dbOk = true;
} catch (Throwable $e) {
    $error = $error ?: ('Database not ready: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Install · ACCEZZ</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:640px">
  <h1 class="h3">Setup helper</h1>
  <p class="text-muted">Import <code>database/schema.sql</code> first, then use this page to ensure default logins work.</p>

  <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <div class="card mb-3">
    <div class="card-body">
      <p class="mb-1"><strong>Database:</strong> <?= $dbOk ? 'Connected' : 'Not ready' ?></p>
      <p class="mb-0 small text-muted">DB name from .env: <code><?= htmlspecialchars($config['db']['name']) ?></code></p>
    </div>
  </div>

  <?php if ($dbOk): ?>
  <form method="post" class="card card-body">
    <?= csrf_field() ?>
    <p class="mb-3">This will set:</p>
    <ul>
      <li><code>admin@campus.edu</code> → <code>Admin@123</code></li>
      <li><code>security@campus.edu</code> → <code>Security@123</code></li>
    </ul>
    <button class="btn btn-primary" type="submit">Refresh default account passwords</button>
  </form>
  <?php endif; ?>

  <p class="mt-3"><a href="login.php">Go to login</a></p>
</div>
</body>
</html>
