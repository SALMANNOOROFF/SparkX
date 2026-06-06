    </div> <!-- .register-container or .login-container -->

    <!-- Scripts -->
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?php echo $base_url ?? '..'; ?>/assets/dashboard/js/<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
