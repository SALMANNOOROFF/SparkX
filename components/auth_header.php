<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $csrf_token ?? 'piVsFK3xU2lEYOX81jppo4MlnGUCNow8PdUIsub5'; ?>">
    <title><?php echo $title ?? 'Sparkx'; ?></title>
    
    <!-- Meta Tags -->
    <?php if (isset($meta_tags)) echo $meta_tags; ?>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url ?? '..'; ?>/assets/dashboard/css/<?php echo $css_file ?? 'login.css'; ?>">
    <?php if (isset($extra_css)): ?>
        <?php foreach ($extra_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $base_url ?? '..'; ?>/assets/dashboard/css/<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Mobile Web App Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Spark X">
</head>
<body>
    <div class="<?php echo $container_class ?? 'login-container'; ?>">
        <!-- Background gradient overlay -->
        <div class="background-gradient"></div>

        <!-- Logo -->
        <div class="logo-container">
            <?php if (isset($logo_is_link) && $logo_is_link): ?>
                <a href="<?php echo $logo_link ?? '../login'; ?>" class="logo">
            <?php else: ?>
                <div class="logo">
            <?php endif; ?>
                <img src="<?php echo $base_url ?? '..'; ?>/assets/dashboard/images/meta/WhatsApp Image 2026-03-22 at 8.04.31 AM.jpeg" alt="Logo" class="logo-icon" style="object-fit: contain; border-radius: 8px;">
                <span class="logo-text">Spark X</span>
            <?php if (isset($logo_is_link) && $logo_is_link): ?>
                </a>
            <?php else: ?>
                </div>
            <?php endif; ?>
        </div>
