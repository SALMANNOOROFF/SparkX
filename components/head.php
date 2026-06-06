<?php
/**
 * Layout Head Component
 * Centralizes meta tags, fonts, and global stylesheets
 */

// Set defaults if variables are not provided
$title = isset($title) ? $title : "Sparkx - Dashboard";
$base_url = isset($base_url) ? $base_url : "..";
$extra_css = isset($extra_css) ? $extra_css : [];
$csrf_token = isset($csrf_token) ? $csrf_token : "8c7BwEZOfQtpnaM8OQ0i2p8H4BUwPgx3HbDVFjyO";
$user_id = isset($user_id) ? $user_id : "997";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $csrf_token; ?>">
    <meta name="user-id" content="<?php echo $user_id; ?>">
    <title><?php echo $title; ?></title>

    <!-- Meta Tags -->
    <?php if (isset($meta_tags)) echo $meta_tags; ?>

    <!-- UI Core & Design System (Consolidated) -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/dashboard/css/main-bundle.css?v=<?php echo time(); ?>">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Mobile Web App Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?php echo htmlspecialchars(get_setting('site_name', 'Spark X')); ?>">
</head>
