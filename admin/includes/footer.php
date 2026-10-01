<?php require_once dirname(__DIR__, 2) . '/includes/functions.php'; ?>
    </div><!-- /.admin-content -->
</main><!-- /.admin-main -->
</div><!-- /.admin-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('assets/js/admin.js') ?>"></script>
<?php if (isset($extra_js)) echo $extra_js; ?>
</body>
</html>
