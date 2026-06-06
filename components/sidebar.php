<?php
$site_logo_db = get_setting('site_logo', 'assets/dashboard/images/meta/WhatsApp Image 2026-03-22 at 8.04.31 AM.jpeg');
$logo_path = (strpos($site_logo_db, 'http') === 0) ? $site_logo_db : SITE_URL . '/' . $site_logo_db;
$site_name_db = get_setting('site_name', 'Spark X');
?>
<aside class="dashboard-sidebar" id="dashboardSidebar">
    <div class="sidebar-content">
        <div class="sidebar-logo">
            <div class="logo">
                <div class="logo-icon-wrapper">
                    <img class="logo-icon"
                        src="<?php echo htmlspecialchars($logo_path); ?>"
                        alt="logo" style="width:24px;height:24px;object-fit:contain;">
                </div>
                <span class="logo-text"><?php echo htmlspecialchars($site_name_db); ?></span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <ul class="nav-menu">
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/user/dashboard" class="nav-link"><span
                            class="nav-icon-wrapper"><i class="fas fa-th-large"></i></span><span
                            class="nav-text">Dashboard</span><span class="nav-indicator"></span></a></li>
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/user/dashboard/wallet" class="nav-link"><span
                            class="nav-icon-wrapper"><i class="fas fa-coins"></i></span><span
                            class="nav-text">Wallet</span><span class="nav-indicator"></span></a></li>
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/user/dashboard/salary" class="nav-link"><span
                            class="nav-icon-wrapper"><i class="fas fa-money-check-alt"></i></span><span
                            class="nav-text">Salary</span><span class="nav-indicator"></span></a></li>
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/user/dashboard/deposit" class="nav-link"><span
                            class="nav-icon-wrapper"><i class="fas fa-money-bill-wave"></i></span><span
                            class="nav-text">Deposit</span><span class="nav-indicator"></span></a></li>
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/user/dashboard/withdraw" class="nav-link"><span
                            class="nav-icon-wrapper"><i class="fas fa-hand-holding-usd"></i></span><span
                            class="nav-text">Withdraw</span><span class="nav-indicator"></span></a></li>
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/user/dashboard/plans" class="nav-link"><span
                            class="nav-icon-wrapper"><i class="fas fa-gem"></i></span><span
                            class="nav-text">Plans</span><span class="nav-indicator"></span></a></li>
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/user/dashboard/goals" class="nav-link"><span
                            class="nav-icon-wrapper"><i class="fas fa-bullseye"></i></span><span
                            class="nav-text">Ranking Rewards</span><span class="nav-indicator"></span></a></li>
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/user/dashboard/referrals.php" class="nav-link"><span
                            class="nav-icon-wrapper"><i class="fas fa-user-plus"></i></span><span class="nav-text">Refer
                            to</span><span class="nav-indicator"></span></a></li>
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/user/dashboard/transactions" class="nav-link"><span
                            class="nav-icon-wrapper"><i class="fas fa-exchange-alt"></i></span><span
                            class="nav-text">Transaction History</span><span class="nav-indicator"></span></a></li>
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/user/dashboard/profile" class="nav-link"><span
                            class="nav-icon-wrapper"><i class="far fa-user-circle"></i></span><span
                            class="nav-text">Profile</span><span class="nav-indicator"></span></a></li>
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/user/dashboard/support" class="nav-link"><span
                            class="nav-icon-wrapper"><i class="fas fa-headset"></i></span><span
                            class="nav-text">Support</span><span class="nav-indicator"></span></a></li>
                <li class="nav-item logout-item">
                    <a href="<?php echo SITE_URL; ?>/logout" class="nav-link logout-link">
                        <span class="nav-icon-wrapper"><i class="fas fa-sign-out-alt"></i></span>
                        <span class="nav-text">Logout</span>
                        <span class="nav-indicator"></span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>