<?php
// Calculate total pending approvals (recharges, payouts, plan investments)
$total_pending_approvals = 0;
if (isset($pdo)) {
    $total_pending_approvals = (int)$pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM deposits WHERE status = 'pending') + 
            (SELECT COUNT(*) FROM withdrawals WHERE status = 'pending') + 
            (SELECT COUNT(*) FROM investments WHERE status = 'pending')
    ")->fetchColumn();
}

$current_page = basename($_SERVER['PHP_SELF']);
if (!function_exists('is_sidebar_active')) {
    function is_sidebar_active($pages) {
        global $current_page;
        if (is_array($pages)) {
            return in_array($current_page, $pages) ? 'active' : '';
        }
        return ($current_page === $pages) ? 'active' : '';
    }
}
?>
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="<?php echo ADMIN_URL; ?>" class="app-brand-link">
            <img src="<?php echo SITE_URL . '/' . get_setting($pdo, 'site_logo', 'assets/images/logoIcon/logo.png'); ?>" alt="Logo" style="height: 30px;" class="me-2">
            <span class="app-brand-text demo menu-text fw-bolder ms-2"><?php echo htmlspecialchars(get_setting($pdo, 'site_name', 'Sparkx')); ?></span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none" onclick="toggleAdminMenu()">
            <i class="bx bx-x d-block d-xl-none bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <li class="menu-item <?php echo is_sidebar_active('index.php'); ?>">
            <a href="<?php echo ADMIN_URL; ?>/index.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div class="menu-label">Dashboard</div>
            </a>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Users</span>
        </li>
        <li class="menu-item <?php echo is_sidebar_active('users.php'); ?>">
            <a href="<?php echo ADMIN_URL; ?>/users.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div class="menu-label">All Customers</div>
            </a>
        </li>
        <li class="menu-item <?php echo is_sidebar_active('users_active.php'); ?>">
            <a href="<?php echo ADMIN_URL; ?>/users_active.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user-check"></i>
                <div class="menu-label">Active Customers</div>
            </a>
        </li>
        <li class="menu-item <?php echo is_sidebar_active('users_disabled.php'); ?>">
            <a href="<?php echo ADMIN_URL; ?>/users_disabled.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user-x"></i>
                <div class="menu-label">Disabled Customers</div>
            </a>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Finance</span>
        </li>
        <li class="menu-item <?php echo is_sidebar_active('deposits.php'); ?>">
            <a href="<?php echo ADMIN_URL; ?>/deposits.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-wallet"></i>
                <div class="menu-label">Recharges</div>
            </a>
        </li>
        <li class="menu-item <?php echo is_sidebar_active('deposit_bypass.php'); ?>">
            <a href="<?php echo ADMIN_URL; ?>/deposit_bypass.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-shield-quarter"></i>
                <div class="menu-label">Deposit Bypass</div>
            </a>
        </li>
        <li class="menu-item <?php echo is_sidebar_active('investments.php'); ?>">
            <a href="<?php echo ADMIN_URL; ?>/investments.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-trending-up"></i>
                <div class="menu-label">Investments</div>
            </a>
        </li>
        <li class="menu-item <?php echo is_sidebar_active('withdrawals.php'); ?>">
            <a href="<?php echo ADMIN_URL; ?>/withdrawals.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-money"></i>
                <div class="menu-label">Payouts</div>
            </a>
        </li>
        <li class="menu-item <?php echo is_sidebar_active('salary_claims.php'); ?>">
            <a href="<?php echo ADMIN_URL; ?>/salary_claims.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-gift"></i>
                <div class="menu-label">Salary Claims</div>
            </a>
        </li>
        <li class="menu-item <?php echo is_sidebar_active('approvals.php'); ?>">
            <a href="<?php echo ADMIN_URL; ?>/approvals.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-check-shield text-warning"></i>
                <div class="menu-label text-warning">Approvals Console</div>
                <?php if ($total_pending_approvals > 0): ?>
                    <span class="badge rounded-pill bg-danger ms-auto" style="font-size: 0.72rem; font-weight: 600; padding: 0.28em 0.65em; animation: pulse-badge 2s infinite;"><?php echo $total_pending_approvals; ?></span>
                <?php endif; ?>
            </a>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">System Architecture</span>
        </li>
        <li class="menu-item <?php echo is_sidebar_active(['plans.php', 'node_edit.php']); ?>">
            <a href="<?php echo ADMIN_URL; ?>/plans.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-layer"></i>
                <div class="menu-label">Investment Plans</div>
            </a>
        </li>
        <li class="menu-item <?php echo is_sidebar_active('commissions.php'); ?>">
            <a href="<?php echo ADMIN_URL; ?>/commissions.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-share-alt"></i>
                <div class="menu-label">Level Commissions</div>
            </a>
        </li>
        <li class="menu-item <?php echo is_sidebar_active('salary_ranks.php'); ?>">
            <a href="<?php echo ADMIN_URL; ?>/salary_ranks.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-trophy"></i>
                <div class="menu-label">Manager Ranks</div>
            </a>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Settings</span>
        </li>
        <li class="menu-item <?php echo is_sidebar_active('settings.php'); ?>">
            <a href="<?php echo ADMIN_URL; ?>/settings.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div class="menu-label">Site Settings</div>
            </a>
        </li>
        <li class="menu-item <?php echo is_sidebar_active('approval_settings.php'); ?>">
            <a href="<?php echo ADMIN_URL; ?>/approval_settings.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-lock-open-alt"></i>
                <div class="menu-label">Approval Policies</div>
            </a>
        </li>
        <li class="menu-item <?php echo is_sidebar_active('gateways.php'); ?>">
            <a href="<?php echo ADMIN_URL; ?>/gateways.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-credit-card"></i>
                <div class="menu-label">Payment Gateways</div>
            </a>
        </li>
        <li class="menu-item <?php echo is_sidebar_active('payout_settings.php'); ?>">
            <a href="<?php echo ADMIN_URL; ?>/payout_settings.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-list-ul"></i>
                <div class="menu-label">Payout Settings</div>
            </a>
        </li>
        
        <li class="menu-item mt-4">
            <a href="<?php echo ADMIN_URL; ?>/logout.php" class="menu-link text-danger">
                <i class="menu-icon tf-icons bx bx-power-off"></i>
                <div class="menu-label">Logout</div>
            </a>
        </li>
    </ul>
</aside>
