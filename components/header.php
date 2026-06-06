<?php
global $pdo;
if (!isset($user_data) && isset($_SESSION['user_id']) && isset($pdo)) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch();
}

// Fallback if user data still missing
$display_name = $user_data['name'] ?? 'User';
$display_email = $user_data['email'] ?? 'Not Logged In';

// Parse dynamic mobile page title
$mobile_page_title = 'Dashboard';
if (isset($title)) {
    $parts = explode(' - ', $title);
    if (count($parts) > 1) {
        $mobile_page_title = $parts[1];
    } else {
        $mobile_page_title = $title;
    }
}
$is_home_page = (strtolower($mobile_page_title) === 'dashboard');
?>

<style>
    .notification-item.unread {
        background: #f3e8ff !important;
    }

    .notification-item.unread:hover {
        background: #e9d5ff !important;
    }
</style>

<header class="dashboard-header">
    <div class="header-content">
        <div class="header-left">
            <?php if ($is_home_page): ?>
                <button class="sidebar-toggle" id="sidebarToggle" style="display: flex !important;">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="header-logo" style="display: block !important;">
                    <span class="logo-text">Spark X</span>
                </div>
            <?php else: ?>
                <div class="mobile-nav-header" style="display: flex !important;">
                    <button class="sidebar-toggle" id="sidebarToggle" style="display: flex !important; margin-right: 15px;">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2 class="mobile-page-title"><?php echo htmlspecialchars($mobile_page_title); ?></h2>
                </div>
            <?php endif; ?>
        </div>
        <div class="header-right">
            <div class="notification-wrapper">
                <div class="notification-icon" id="notificationIcon">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                </div>
                <div class="notification-panel" id="notificationPanel">
                    <div class="notification-panel-header"
                        style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
                        <h3 class="notification-panel-title" style="margin: 0; font-size: 1rem; font-weight: 600;">
                            Notifications</h3>
                        <button id="markAllReadHeaderBtn"
                            style="background: none; border: none; color: #a78bfa; font-size: 0.8rem; cursor: pointer; transition: color 0.3s; padding: 0;"
                            onmouseover="this.style.color='#c084fc'" onmouseout="this.style.color='#a78bfa'">Mark all as
                            read</button>
                    </div>
                    <div class="notification-panel-body" id="notificationPanelBody">
                        <div class="notification-loading">
                            <i class="fas fa-spinner fa-spin"></i> Loading...
                        </div>
                    </div>
                    <div class="notification-panel-footer">
                        <a href="<?php echo SITE_URL; ?>/user/dashboard/notifications" class="notification-see-all">See all
                            notifications</a>
                    </div>
                </div>
            </div>
            <div class="user-profile">
                <div class="user-avatar">
                    <?php
                    $header_avatar = 'https://ui-avatars.com/api/?name=' . urlencode($display_name) . '&background=CC44FF&color=fff&size=128&bold=true';
                    if (!empty($user_data['profile_image'])) {
                        $header_avatar = SITE_URL . '/' . $user_data['profile_image'];
                    }
                    ?>
                    <img src="<?php echo $header_avatar; ?>" alt="User Avatar" class="header-user-avatar"
                        style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo $display_name; ?></div>
                    <div class="user-email"><?php echo $display_email; ?></div>
                </div>
            </div>
        </div>
    </div>
</header>