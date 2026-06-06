<?php
/**
 * Layout Top Component
 * Handles the opening structure of the dashboard pages
 */

// base_url should be set before including this
include('head.php');
?>
<body>
    <div class="dashboard-wrapper">
        <!-- Mobile Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Left Sidebar -->
        <?php include('sidebar.php'); ?>

        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Top Header -->
            <?php include('header.php'); ?>

            <!-- Page Content -->
            <div class="content-area">
