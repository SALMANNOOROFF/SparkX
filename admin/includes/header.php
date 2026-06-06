<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Dashboard'; ?> - <?php echo htmlspecialchars(get_setting($pdo, 'site_name', 'Sparkx')); ?></title>
    <link rel="stylesheet" href="<?php echo ADMIN_URL; ?>/assets/vendor/css/core.css">
    <link rel="stylesheet" href="<?php echo ADMIN_URL; ?>/assets/css/demo.css">
    <link rel="stylesheet" href="<?php echo ADMIN_URL; ?>/assets/css/admin-custom.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .menu-item.open > .menu-sub {
            display: block !important;
        }
        .menu-item:not(.open) > .menu-sub {
            display: none !important;
        }
        .menu-toggle {
            cursor: pointer;
        }
    </style>
</head>
<body class="admin-panel-body">
    <div class="layout-wrapper layout-content-navbar" id="admin-layout-wrapper">
        <div class="layout-container">
            <!-- Mobile Toggle Navbar -->
            <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme d-xl-none" id="layout-navbar">
                <div class="container-fluid d-flex align-items-center justify-content-between px-0">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center">
                        <a class="nav-item nav-link px-0 me-3" href="javascript:void(0)" onclick="toggleAdminMenu()" aria-label="Open admin menu">
                            <i class="bx bx-menu bx-sm"></i>
                        </a>
                    </div>
                    <div class="navbar-brand d-flex align-items-center admin-mobile-navbar-brand flex-grow-1">
                        <span class="fw-bold text-dark admin-mobile-navbar-title" style="font-size: 1.1rem; letter-spacing: -0.5px;"><?php echo $page_title ?? 'Admin Dashboard'; ?></span>
                    </div>
                    <div class="navbar-nav align-items-center">
                        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                            <div class="avatar avatar-online" style="width: 38px; height: 38px;">
                                <img src="<?php echo SITE_URL; ?>/admin/assets/img/avatars/1.png" alt class="w-100 h-auto rounded-circle" />
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="<?php echo ADMIN_URL; ?>/logout.php">
                                    <i class="bx bx-power-off me-2"></i>
                                    <span class="align-middle">Log Out</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            
            <div class="layout-overlay" id="layout-overlay" onclick="toggleAdminMenu()"></div>
