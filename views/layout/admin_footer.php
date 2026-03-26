</main>
<!-- End Admin Main Content -->

<!-- Footer -->
<footer class="admin-footer text-center text-muted py-3 border-top">
    <small>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. Admin Panel.</small>
</footer>

</div>
<!-- End Admin Content Area -->

<!-- Bootstrap 5 JS Bundle (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
        crossorigin="anonymous"></script>

<!-- Custom JS -->
<script src="<?= asset('js/app.js') ?>?v=<?= filemtime(__DIR__ . '/../../assets/js/app.js') ?>"></script>
<script src="<?= asset('js/ai-generate.js') ?>?v=<?= filemtime(__DIR__ . '/../../assets/js/ai-generate.js') ?>"></script>
</body>
</html>
