</main>
<footer class="app-footer text-center text-muted py-3 small">
  &copy; <?= date('Y') ?> <?= e($config['app_name'] ?? 'ACCEZZ') ?>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
