<?php
/**
 * Layout Scripts Component
 * Centralizes global and page-specific JavaScript files
 */

$base_url = isset($base_url) ? $base_url : "..";
$extra_scripts = isset($extra_scripts) ? $extra_scripts : [];
?>

    <!-- Core Scripts (Consolidated) -->
    <script src="<?php echo $base_url; ?>/assets/dashboard/js/main-bundle.js?v=<?php echo time(); ?>"></script>

    <!-- Page Specific Scripts (Now part of main-bundle.js) -->

    <!-- Custom Inline Scripts -->
    <?php if (isset($custom_js)) echo $custom_js; ?>
