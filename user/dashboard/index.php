<?php 
    require_once '../../includes/auth_check.php';
    $title = "Sparkx - Dashboard";
    $base_url = "../..";

    // Fetch plan earnings dynamically
    $earning_q = mysqli_query($conn, "SELECT SUM(amount) as total FROM transactions WHERE user_id = '$user_id' AND type = 'profit' AND status = 'completed'");
    $plan_earning = mysqli_fetch_assoc($earning_q)['total'] ?? 0;

    // Calculate MLM team active investments for dashboard goals rank
    $all_team_ids = [];
    $ref_q = mysqli_query($conn, "SELECT referee_id FROM referrals WHERE referrer_id = '$user_id' AND level <= 5");
    while ($r = mysqli_fetch_assoc($ref_q)) {
        $all_team_ids[] = $r['referee_id'];
    }
    $total_team_users = count($all_team_ids);

    $total_team_investment = 0.00;
    if ($total_team_users > 0) {
        $ids_str = implode(',', $all_team_ids);
        $inv_q = mysqli_query($conn, "SELECT SUM(amount) as total FROM investments WHERE user_id IN ($ids_str) AND status = 'active'");
        $total_team_investment = mysqli_fetch_assoc($inv_q)['total'] ?? 0.00;
    }

    // --- Calculate dynamic Manager Ranks from salary_ranks ---
    $direct_ids = [];
    $indirect_ids = [];
    $ref_lvl_q = mysqli_query($conn, "SELECT referee_id, level FROM referrals WHERE referrer_id = '$user_id' AND level <= 5");
    while ($r = mysqli_fetch_assoc($ref_lvl_q)) {
        if ((int)$r['level'] === 1) {
            $direct_ids[] = $r['referee_id'];
        } else {
            $indirect_ids[] = $r['referee_id'];
        }
    }

    $total_direct_active = 0;
    if (count($direct_ids) > 0) {
        $direct_ids_str = implode(',', $direct_ids);
        $active_dir_q = mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) as total FROM investments WHERE user_id IN ($direct_ids_str) AND status = 'active'");
        $total_direct_active = mysqli_fetch_assoc($active_dir_q)['total'] ?? 0;
    }

    $total_indirect_active = 0;
    if (count($indirect_ids) > 0) {
        $indirect_ids_str = implode(',', $indirect_ids);
        $active_indir_q = mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) as total FROM investments WHERE user_id IN ($indirect_ids_str) AND status = 'active'");
        $total_indirect_active = mysqli_fetch_assoc($active_indir_q)['total'] ?? 0;
    }

    $self_active_q = mysqli_query($conn, "SELECT SUM(amount) as total FROM investments WHERE user_id = '$user_id' AND status = 'active'");
    $self_active_investment = mysqli_fetch_assoc($self_active_q)['total'] ?? 0;

    $ranks_q = mysqli_query($conn, "SELECT * FROM salary_ranks ORDER BY id ASC");
    $ranks_list = [];
    while ($row = mysqli_fetch_assoc($ranks_q)) {
        $ranks_list[] = $row;
    }

    $current_rank_name = "No Rank";
    $next_rank_name = "";
    if (count($ranks_list) > 0) {
        $next_rank_name = $ranks_list[0]['rank_name'];
    }

    foreach ($ranks_list as $index => $rank) {
        $req_self = $rank['self_invest'];
        $req_direct = $rank['direct_active'];
        $req_indirect = $rank['indirect_active'];
        
        if ($self_active_investment >= $req_self && $total_direct_active >= $req_direct && $total_indirect_active >= $req_indirect) {
            $current_rank_name = $rank['rank_name'];
            if (isset($ranks_list[$index + 1])) {
                $next_rank_name = $ranks_list[$index + 1]['rank_name'];
            } else {
                $next_rank_name = "Max Rank Reached 🏆";
            }
        }
    }

    // Goal levels
    $levels = [
        1  => ['name' => 'Team Builder',   'target' => 15],
        2  => ['name' => 'Team Leader',    'target' => 40],
        3  => ['name' => 'Team Director',  'target' => 100],
        4  => ['name' => 'Team Master',    'target' => 200],
        5  => ['name' => 'Team Chief',     'target' => 500],
        6  => ['name' => 'Team Executive', 'target' => 1000],
        7  => ['name' => 'Team Captain',   'target' => 2000],
        8  => ['name' => 'Team Commander', 'target' => 3500],
        9  => ['name' => 'Team Head',      'target' => 5000],
        10 => ['name' => 'Team President', 'target' => 10000],
    ];

    $current_level_num = 0;
    $current_level_name = "No Level";
    $next_level_name = "Team Builder";

    foreach ($levels as $num => $lvl) {
        if ($total_team_investment >= $lvl['target']) {
            $current_level_num = $num;
            $current_level_name = $lvl['name'];
            if (isset($levels[$num + 1])) {
                $next_level_name = $levels[$num + 1]['name'];
            } else {
                $next_level_name = "Max Level Reached 🏆";
            }
        } else {
            break;
        }
    }

    // Day Restrictions Logic
    $today_day = date('l');
    $deposit_day_config = get_setting('deposit_day', 'Sunday');
    $deposit_restriction_config = get_setting('deposit_restriction', 'disabled');
    $is_disabled_deposit = ($deposit_restriction_config === 'enabled' && strcasecmp($today_day, $deposit_day_config) !== 0);

    $withdraw_day_config = get_setting('withdrawal_day', 'Saturday');
    $withdraw_restriction_config = get_setting('withdraw_restriction', 'disabled');
    $is_disabled_withdraw = ($withdraw_restriction_config === 'enabled' && strcasecmp($today_day, $withdraw_day_config) !== 0);

    include('../../components/layout_top.php'); 
?>

                <div class="mining-dashboard">
                    <!-- Combined Balance and Actions Card -->
                    <div class="mining-wallet-card">
                        <!-- Balance Section -->
                        <div class="wallet-balance-section">
                            <div class="announcement-box">
                                <div class="announcement-top">
                                    <span class="announcement-icon">📢</span>
                                    <span class="announcement-heading">Important <span class="announcement-highlight">Announcement</span></span>
                                </div>
                                <p class="announcement-line"><?php echo htmlspecialchars(get_setting('announcement_line_1', 'The previous channel has been deleted ⚠️')); ?></p>
                                <p class="announcement-line"><?php echo htmlspecialchars(get_setting('announcement_line_2', 'Join the new official channel to stay updated 🚀')); ?></p>
                                <a href="<?php echo htmlspecialchars(get_setting('whatsapp_channel_url', 'https://whatsapp.com/channel/0029Vb8Wl8Q1t90U7qiFnB0j')); ?>" target="_blank" rel="noopener noreferrer" class="announcement-btn">
                                    <?php echo htmlspecialchars(get_setting('announcement_btn_text', '👉 Join Now Channel 🎁')); ?>
                                </a>
                                <p class="announcement-footer"><?php echo htmlspecialchars(get_setting('announcement_footer', 'Join the new channel & claim your bonus 🎁')); ?></p>
                            </div>

                            <div class="balance-header-row">
                                <div class="balance-label">
                                    <i class="fas fa-eye"></i>
                                    <span>Total Balance</span>
                                </div>
                                <div class="balance-actions">
                                    <i class="fas fa-arrow-up balance-trend-up"></i>
                                    <i class="fas fa-eye-slash balance-toggle-icon" id="balanceToggle"></i>
                                </div>
                            </div>
                            <div class="balance-amount-display" style="display: flex; align-items: center; justify-content: space-between; gap: 15px; flex-wrap: wrap;">
                                <span class="balance-amount-large" id="totalBalance"><?php echo format_amount_system($user_data['deposit_balance'] + $user_data['earning_balance'], 2); ?></span>
                            </div>
                            <div class="wallet-info-row">
                                <div class="deposit-wallet-info">
                                    <span class="deposit-wallet-label">Deposit Balance:</span>
                                    <span class="deposit-wallet-amount" id="fundWalletAmount"><?php echo format_amount_system($user_data['deposit_balance'], 2); ?></span>
                                    <i class="fas fa-arrow-down deposit-trend-down"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons Section -->
                        <div class="wallet-actions-grid">
                            <?php if ($is_disabled_deposit): ?>
                                <div class="wallet-action-btn disabled-action" data-tooltip="Deposit is only open on <?php echo htmlspecialchars($deposit_day_config); ?>s">
                                    <div class="wallet-action-icon">
                                        <i class="fas fa-arrow-up"></i>
                                    </div>
                                    <span class="wallet-action-label">Deposit</span>
                                </div>
                            <?php else: ?>
                                <a href="/sparkx1/user/dashboard/deposit" class="wallet-action-btn" style="text-decoration: none; color: inherit;">
                                    <div class="wallet-action-icon">
                                        <i class="fas fa-arrow-up"></i>
                                    </div>
                                    <span class="wallet-action-label">Deposit</span>
                                </a>
                            <?php endif; ?>

                            <?php if ($is_disabled_withdraw): ?>
                                <div class="wallet-action-btn disabled-action" data-tooltip="Withdrawal is only open on <?php echo htmlspecialchars($withdraw_day_config); ?>s">
                                    <div class="wallet-action-icon">
                                        <i class="fas fa-arrow-down"></i>
                                    </div>
                                    <span class="wallet-action-label">Withdraw</span>
                                </div>
                            <?php else: ?>
                                <a href="/sparkx1/user/dashboard/withdraw" class="wallet-action-btn" style="text-decoration: none; color: inherit;">
                                    <div class="wallet-action-icon">
                                        <i class="fas fa-arrow-down"></i>
                                    </div>
                                    <span class="wallet-action-label">Withdraw</span>
                                </a>
                            <?php endif; ?>

                            <a href="/sparkx1/user/dashboard/wallet" class="wallet-action-btn" style="text-decoration: none; color: inherit;">
                                <div class="wallet-action-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <span class="wallet-action-label">Wallet</span>
                            </a>
                            <a href="/sparkx1/user/dashboard/referrals.php" class="wallet-action-btn" style="text-decoration: none; color: inherit;">
                                <div class="wallet-action-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <span class="wallet-action-label">Refer to</span>
                            </a>
                        </div>
                    </div>

                    <!-- Live Earning Section -->
                    <div class="live-earning-section">
                        <div class="live-earning-container">
                            <div class="live-earning-animated-box">
                                <div class="animated-box-content">
                                     <img src="../../assets/dashboard/images/meta/start.jpeg" class="spinning-img" alt="">
                                </div>
                            </div>
                            <div class="live-earning-content">
                                <div class="live-earning-header">
                                    <div class="live-earning-title">
                                        <i class="fas fa-chart-line"></i>
                                        <span>Live Earning</span>
                                    </div>
                                    <div class="live-earning-subtitle">Real time updates</div>
                                </div>
                                <div class="live-earning-amount" id="liveEarningAmount">
                                    <?php echo format_amount_system($user_data['earning_balance'], 6); ?>
                                </div>
                                <div class="live-earning-actions">
                                    <button class="live-earning-btn activate-now-btn" onclick="window.location.href='plans'">
                                        <i class="fas fa-play"></i>
                                        <span>Activate Now</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mining Overview -->
                    <div class="mining-overview-section">
                        <div class="mining-stats-row">
                            <div class="mining-stat-item">
                                <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                                <div class="stat-content">
                                    <div class="stat-label">Plan Earning </div>
                                    <div class="stat-value"><?php echo format_amount_system($plan_earning, 2); ?></div>
                                </div>
                            </div>
                            <div class="mining-stat-item">
                                <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                                <div class="stat-content">
                                    <div class="stat-label">Total Invested</div>
                                    <div class="stat-value"><?php echo format_amount_system($user_data['total_invested'], 2); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mining-overview-section">
                        <div class="mining-stats-row">
                            <div class="mining-stat-item">
                                <div class="stat-icon"><i class="fas fa-arrow-up"></i></div>
                                <div class="stat-content">
                                    <div class="stat-label">Total Deposit</div>
                                    <div class="stat-value"><?php echo format_amount_system($user_data['deposit_balance'], 2); ?></div>
                                </div>
                            </div>
                            <div class="mining-stat-item">
                                <div class="stat-icon"><i class="fas fa-arrow-down"></i></div>
                                <div class="stat-content">
                                    <div class="stat-label">Total withdrawals </div>
                                    <div class="stat-value"><?php echo format_amount_system($user_data['total_withdrawn'], 2); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="mining-graph-section">
                           <div class="goals-status-card-new goals-rank-card-new goals-desktop-card-new">
                                <div class="goals-rank-icon-wrapper-new">
                                    <i class="fas fa-trophy goals-rank-icon-new"></i>
                                </div>
                                <div class="goals-rank-badge-new">
                                    <?php echo $user_data['role'] == 'admin' ? 'Admin' : (($current_level_num > 0) ? "Level " . $current_level_num : "No Rank"); ?>
                                </div>
                                <div class="goals-rank-label-new" style="margin-bottom: 8px;">
                                    <?php echo htmlspecialchars($current_level_name); ?>
                                </div>
                                <div class="goals-rank-hint-new" style="font-size: 12px; font-weight: 600; color: #c2410c; background: rgba(234, 88, 12, 0.08); padding: 5px 14px; border-radius: 30px; display: inline-flex; align-items: center; gap: 6px; border: 1px solid rgba(234, 88, 12, 0.12);">
                                    <span style="display: inline-block; width: 6px; height: 6px; background: #ea580c; border-radius: 50%;"></span>
                                    Next Goal: <strong style="color: #ea580c;"><?php echo htmlspecialchars($next_level_name); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Glassmorphic System Status Modal -->
                    <div class="helpline-modal-overlay" id="helplineModal">
                        <div class="glass-status-modal">
                            <button class="glass-modal-close" id="closeHelplineModal">
                                <i class="fas fa-times"></i>
                            </button>
                            <div class="glass-modal-header">
                                <div class="glass-modal-title">System Status</div>
                                <div class="glass-modal-subtitle">Live official statistics and parameters</div>
                            </div>
                            <div class="system-status-grid">
                                <?php
                                $status_rows = json_decode(get_setting('system_status_rows', '[]'), true);
                                if (empty($status_rows)) {
                                    $status_rows = [
                                        ['title' => 'Min Deposit', 'value' => '300 PKR'],
                                        ['title' => 'Min Withdraw', 'value' => '30 PKR'],
                                        ['title' => 'Withdraw Fee', 'value' => '3%'],
                                        ['title' => 'Withdraw Time', 'value' => '1 Hour To 24 Hour'],
                                        ['title' => 'Referral Bonus', 'value' => 'Upto 15%'],
                                        ['title' => 'Referral Earning Bonus', 'value' => 'Upto 19%']
                                    ];
                                }
                                foreach ($status_rows as $row):
                                ?>
                                <div class="system-status-card">
                                    <span class="status-card-title"><?php echo htmlspecialchars($row['title']); ?></span>
                                    <span class="status-card-value"><?php echo htmlspecialchars($row['value']); ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="glass-modal-footer">
                                <a href="<?php echo htmlspecialchars(get_setting('whatsapp_channel_url', 'https://whatsapp.com/channel/0029Vb8Wl8Q1t90U7qiFnB0j')); ?>" target="_blank" class="glass-action-btn" id="joinNowBtn">
                                    <i class="fab fa-whatsapp"></i> Join Official Channel
                                </a>
                            </div>
                        </div>
                    </div>
                </div> <!-- mining-dashboard -->

<style>
/* Glassmorphism System Status Modal */
.helpline-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(10, 10, 12, 0.7);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.helpline-modal-overlay.active {
    opacity: 1;
    visibility: visible;
}
.glass-status-modal {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 24px;
    padding: 30px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    color: #fff;
    position: relative;
    transform: scale(0.9);
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.helpline-modal-overlay.active .glass-status-modal {
    transform: scale(1);
}
.glass-modal-close {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: #fff;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}
.glass-modal-close:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: rotate(90deg);
}
.glass-modal-header {
    text-align: center;
    margin-bottom: 25px;
}
.glass-modal-title {
    font-size: 24px;
    font-weight: 700;
    background: linear-gradient(135deg, #ff8c00, #fdba74);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 8px;
}
.glass-modal-subtitle {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.6);
}
.system-status-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 25px;
}
@media (max-width: 480px) {
    .system-status-grid {
        grid-template-columns: 1fr;
    }
}
.system-status-card {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    transition: all 0.3s;
}
.system-status-card:hover {
    background: rgba(255, 255, 255, 0.08);
    transform: translateY(-2px);
    border-color: rgba(234, 88, 12, 0.4);
}
.status-card-title {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: rgba(255, 255, 255, 0.5);
}
.status-card-value {
    font-size: 16px;
    font-weight: 600;
    color: #f3e8ff;
}
.glass-modal-footer {
    display: flex;
    justify-content: center;
}
.glass-action-btn {
    background: linear-gradient(135deg, #f97316, #ea580c);
    border: none;
    border-radius: 30px;
    color: #fff;
    padding: 12px 35px;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 10px 20px rgba(234, 88, 12, 0.3);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.glass-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 25px rgba(234, 88, 12, 0.5);
}

/* Disabled Actions and CSS Tooltips */
.wallet-action-btn.disabled-action {
    opacity: 0.6;
    cursor: pointer;
    position: relative;
}
.wallet-action-btn.disabled-action .wallet-action-icon {
    background: rgba(224, 64, 251, 0.15) !important;
    color: #e040fb !important;
    border: 1.5px dashed rgba(224, 64, 251, 0.5) !important;
}
.wallet-action-btn.disabled-action .wallet-action-label {
    color: rgba(255, 255, 255, 0.5) !important;
}
@media (max-width: 768px) {
    .wallet-action-btn.disabled-action .wallet-action-label {
        color: rgba(0, 0, 0, 0.5) !important;
    }
}

[data-tooltip] {
    position: relative;
}
[data-tooltip]::before {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 125%;
    left: 50%;
    transform: translateX(-50%) translateY(5px);
    background: rgba(15, 15, 20, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #fff;
    padding: 8px 12px;
    font-size: 11px;
    border-radius: 8px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
    z-index: 1000;
    pointer-events: none;
}
[data-tooltip]::after {
    content: '';
    position: absolute;
    bottom: 115%;
    left: 50%;
    transform: translateX(-50%) translateY(5px);
    border-width: 6px;
    border-style: solid;
    border-color: rgba(15, 15, 20, 0.95) transparent transparent transparent;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    z-index: 1000;
    pointer-events: none;
}
[data-tooltip]:hover::before,
[data-tooltip]:hover::after {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(0);
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const modal = document.getElementById("helplineModal");
    if (modal) {
        // Simple click tracking & visual display
        const closeBtn = document.getElementById("closeHelplineModal");
        if (closeBtn) {
            closeBtn.addEventListener("click", () => {
                modal.classList.remove("active");
            });
        }
        
        // Show after 1 second if not closed in session
        if (!sessionStorage.getItem("status_modal_dismissed")) {
            setTimeout(() => {
                modal.classList.add("active");
            }, 1000);
        }
        
        // Save dismissal in session storage when closed
        if (closeBtn) {
            closeBtn.addEventListener("click", () => {
                sessionStorage.setItem("status_modal_dismissed", "true");
            });
        }
    }

    // Bind click listeners on disabled action buttons to show SweetAlert / Notification
    document.querySelectorAll('.disabled-action').forEach(btn => {
        btn.addEventListener('click', function() {
            const message = this.getAttribute('data-tooltip') || 'This feature is currently restricted.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'System Notice',
                    text: message,
                    icon: 'info',
                    confirmButtonText: 'Understood',
                    confirmButtonColor: '#ea580c',
                    background: '#1e1b4b',
                    color: '#fff'
                });
            } else {
                alert(message);
            }
        });
    });
});
</script>

<?php include('../../components/layout_bottom.php'); ?>

