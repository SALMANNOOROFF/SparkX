<?php 
    $title = "Sparkx - Ranking Reward";
    $base_url = "../..";

    require_once '../../includes/auth_check.php';

    // 1. Build the MLM network (Level 1 to Level 5) using the referrals closure table
    $all_team_ids = [];
    $ref_q = mysqli_query($conn, "SELECT referee_id FROM referrals WHERE referrer_id = '$user_id' AND level <= 5");
    while ($r = mysqli_fetch_assoc($ref_q)) {
        $all_team_ids[] = $r['referee_id'];
    }
    $total_team_users = count($all_team_ids);

    // 2. Calculate Total Team Active Investment
    $total_team_investment = 0.00;
    if ($total_team_users > 0) {
        $ids_str = implode(',', $all_team_ids);
        $inv_q = mysqli_query($conn, "SELECT SUM(amount) as total FROM investments WHERE user_id IN ($ids_str) AND status = 'active'");
        $total_team_investment = mysqli_fetch_assoc($inv_q)['total'] ?? 0.00;
    }

    // 3. Define levels, targets, rewards, and icons
    $levels = [
        1  => ['name' => 'Team Builder',   'target' => 15,    'reward' => 1,    'icon' => 'fa-user-tie'],
        2  => ['name' => 'Team Leader',    'target' => 40,    'reward' => 3,    'icon' => 'fa-user-graduate'],
        3  => ['name' => 'Team Director',  'target' => 100,   'reward' => 5,    'icon' => 'fa-briefcase'],
        4  => ['name' => 'Team Master',    'target' => 200,   'reward' => 12,   'icon' => 'fa-medal'],
        5  => ['name' => 'Team Chief',     'target' => 500,   'reward' => 30,   'icon' => 'fa-award'],
        6  => ['name' => 'Team Executive', 'target' => 1000,  'reward' => 80,   'icon' => 'fa-gem'],
        7  => ['name' => 'Team Captain',   'target' => 2000,  'reward' => 150,  'icon' => 'fa-star',       'premium' => true],
        8  => ['name' => 'Team Commander', 'target' => 3500,  'reward' => 300,  'icon' => 'fa-chess-king', 'premium' => true],
        9  => ['name' => 'Team Head',      'target' => 5000,  'reward' => 500,  'icon' => 'fa-crown',      'premium' => true],
        10 => ['name' => 'Team President', 'target' => 10000, 'reward' => 1000, 'icon' => 'fa-award',      'premium' => true],
    ];

    // Find user's current unlocked level and next goal
    $current_level_num = 0;
    $current_level_name = "No Level";
    
    foreach ($levels as $num => $lvl) {
        if ($total_team_investment >= $lvl['target']) {
            $current_level_num = $num;
            $current_level_name = $lvl['name'];
        } else {
            break;
        }
    }

    // Determine Next Goal
    $next_level_num = $current_level_num + 1;
    if (isset($levels[$next_level_num])) {
        $next_level_name = $levels[$next_level_num]['name'];
        $next_level_target = $levels[$next_level_num]['target'];
        $needed_amount = $next_level_target - $total_team_investment;
        
        // Progress percentage to the NEXT goal
        $previous_target = ($current_level_num > 0) ? $levels[$current_level_num]['target'] : 0;
        $progress_range = $next_level_target - $previous_target;
        $progress_done = $total_team_investment - $previous_target;
        if ($progress_done < 0) $progress_done = 0;
        
        $progress_percent = ($progress_done / $progress_range) * 100;
        if ($progress_percent > 100) $progress_percent = 100;
        if ($progress_percent < 0) $progress_percent = 0;
    } else {
        // User has unlocked the highest level (Level 10)!
        $next_level_name = "Max Level Reached 🏆";
        $next_level_target = $levels[10]['target'];
        $needed_amount = 0;
        $progress_percent = 100;
    }

    include('../../components/layout_top.php'); 
?>

<div class="goals-new-page">
    <!-- Hero Section -->
    <div class="goals-hero-new">
        <div class="goals-hero-content-new">
            <h1 class="goals-hero-title-new">Ranking Reward</h1>
            <p class="goals-hero-subtitle-new">Unlock higher levels and earn bigger rewards through referrals and team building</p>
        </div>
    </div>

    <!-- Current Status Section -->
    <div class="goals-status-section-new">
        <div class="goals-status-header-new">
            <h2 class="goals-status-title-new">Your Current Status</h2>
            <p class="goals-status-subtitle-new">Track your progress towards the next level and unlock exclusive rewards</p>
        </div>

        <div class="goals-status-cards-new">
            <!-- Desktop Cards -->
            <div class="goals-status-card-new goals-progress-card-new goals-desktop-card-new">
                <div class="goals-progress-header-new">
                    <h3 class="goals-progress-title-new">Progress to Next Goal</h3>
                    <div class="goals-progress-goal-name-new"><?php echo htmlspecialchars($next_level_name); ?></div>
                </div>
                <div class="goals-progress-display-new">
                    <div class="goals-progress-bar-wrapper-new">
                        <div class="goals-progress-fill-new" style="width: <?php echo number_format($progress_percent, 1); ?>%"></div>
                        <div class="goals-progress-percentage-new"><?php echo number_format($progress_percent, 1); ?>%</div>
                    </div>
                    <div class="goals-progress-info-new">
                        <span class="goals-progress-current-new">$<?php echo number_format($total_team_investment, 2); ?></span>
                        <span class="goals-progress-separator-new">/</span>
                        <span class="goals-progress-target-new">$<?php echo number_format($next_level_target, 0); ?></span>
                    </div>
                    <div class="goals-progress-message-new">
                        <?php if ($needed_amount > 0): ?>
                            $<?php echo number_format($needed_amount, 2); ?> more needed to unlock next rank!
                        <?php else: ?>
                            Congratulations! You have unlocked all ranks! 🏆
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="goals-status-card-new goals-next-goal-card-new goals-desktop-card-new">
                <div class="goals-next-goal-header-new">
                    <h3 class="goals-next-goal-title-new">Next Goal</h3>
                </div>
                <div class="goals-next-goal-name-new"><?php echo htmlspecialchars($next_level_name); ?></div>
                <div class="goals-next-goal-requirements-new">
                    <div class="goals-requirement-item-new">
                        <i class="fas fa-users-cog"></i>
                        <span>Team Progress: <strong>$<?php echo number_format($total_team_investment, 2); ?></strong></span>
                    </div>
                </div>
                <div class="goals-next-progress-bar-wrapper-new">
                    <div class="goals-next-progress-fill-new" style="width: <?php echo number_format($progress_percent, 1); ?>%"></div>
                </div>
                <div class="goals-next-goal-needed-new">
                    <?php echo ($needed_amount > 0) ? "$" . number_format($needed_amount, 2) . " more needed" : "Unlocked!"; ?>
                </div>
            </div>

            <!-- Mobile Combined Card -->
            <div class="goals-status-card-new goals-combined-card-new goals-mobile-card-new">
                <div class="goals-rank-section-mobile-new">
                    <div class="goals-rank-icon-wrapper-new">
                        <i class="fas fa-trophy goals-rank-icon-new"></i>
                    </div>
                    <div class="goals-rank-text-new">
                        <?php echo ($current_level_num > 0) ? "Level " . $current_level_num : "No Rank"; ?>
                    </div>
                </div>
                <div class="goals-progress-section-mobile-new">
                    <div class="goals-progress-title-row-new">
                        <span class="goals-progress-title-new">Progress to next goal</span>
                        <span class="goals-progress-percent-new"><?php echo number_format($progress_percent, 1); ?>%</span>
                    </div>
                    <div class="goals-progress-goal-row-new">
                        <span class="goals-progress-goal-name-new"><?php echo htmlspecialchars($next_level_name); ?></span>
                        <span class="goals-progress-status-new"><?php echo ($needed_amount > 0) ? "In Progress" : "Completed"; ?></span>
                    </div>
                    <div class="goals-progress-bar-mobile-new">
                        <div class="goals-progress-fill-mobile-new" style="width: <?php echo number_format($progress_percent, 1); ?>%"></div>
                    </div>
                </div>
                <div class="goals-next-goal-section-mobile-new">
                    <div class="goals-next-goal-card-inner-new">
                        <div class="goals-next-goal-title-mobile-new">Next Goal: <?php echo htmlspecialchars($next_level_name); ?></div>
                        <div class="goals-next-goal-progress-row-new">
                            <div class="goals-next-goal-progress-label-new">
                                <i class="fas fa-chart-line"></i>
                                <span>Team Progress</span>
                            </div>
                            <span class="goals-next-goal-current-new">$<?php echo number_format($total_team_investment, 2); ?></span>
                        </div>
                        <div class="goals-next-progress-bar-mobile-new">
                            <div class="goals-next-progress-fill-mobile-new" style="width: <?php echo number_format($progress_percent, 1); ?>%"></div>
                        </div>
                        <div class="goals-next-goal-target-row-new">
                            <span class="goals-next-goal-needed-mobile-new"><?php echo ($needed_amount > 0) ? "$" . number_format($needed_amount, 2) . " more needed" : "Unlocked!"; ?></span>
                            <span class="goals-next-goal-target-new">$<?php echo number_format($next_level_target, 0); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rewards Level Section -->
    <div class="goals-all-levels-section-new">
        <div class="goals-all-levels-header-new">
            <h2 class="goals-all-levels-title-new">Rewards Level</h2>
            <p class="goals-all-levels-subtitle-new">Explore all available levels and their exclusive rewards</p>
        </div>
        
        <div class="goals-all-levels-grid-new">
            <?php foreach ($levels as $num => $lvl): 
                $is_unlocked = ($total_team_investment >= $lvl['target']);
                $is_current = ($current_level_num === $num);
                $is_premium = isset($lvl['premium']) && $lvl['premium'];

                $card_class = "goals-level-card-new";
                if ($is_current) $card_class .= " current";
                elseif ($is_unlocked) $card_class .= " unlocked";
                if ($is_premium) $card_class .= " premium";

                // Calculate progress percentage for this specific card
                $prev_tgt = ($num > 1) ? $levels[$num - 1]['target'] : 0;
                $card_range = $lvl['target'] - $prev_tgt;
                $card_done = $total_team_investment - $prev_tgt;
                if ($card_done < 0) $card_done = 0;
                
                $card_percent = ($card_done / $card_range) * 100;
                if ($card_percent > 100) $card_percent = 100;
                if ($card_percent < 0) $card_percent = 0;
            ?>
                <div class="<?php echo $card_class; ?>">
                    <?php 
                    if ($is_current) {
                        echo '<div class="goals-reward-badge-new" style="display: block; background: linear-gradient(135deg, #fb923c 0%, #ea580c 100%);">Current</div>';
                    } elseif ($is_unlocked) {
                        echo '<div class="goals-reward-badge-new" style="display: block; background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);">Unlocked</div>';
                    } elseif ($is_premium) {
                        echo '<div class="goals-level-badge-premium-new">Premium</div>';
                    }
                    ?>
                    
                    <div class="goals-level-icon-new <?php echo ($is_unlocked || $is_current) ? 'goals-level-icon-gold-new' : ''; ?>">
                        <i class="fas <?php echo $lvl['icon']; ?>"></i>
                    </div>
                    
                    <div class="goals-level-content-new">
                        <h3 class="goals-level-name-new"><?php echo htmlspecialchars($lvl['name']); ?></h3>
                        <div class="goals-level-number-new">Level <?php echo $num; ?></div>
                        
                        <div class="goals-level-details-new">
                            <div class="goals-level-detail-item-new">
                                <span class="goals-level-detail-label-new">Total Referral Investment</span>
                                <span class="goals-level-detail-value-new">$<?php echo number_format($lvl['target'], 0); ?></span>
                            </div>
                            <div class="goals-level-detail-item-new">
                                <span class="goals-level-detail-label-new">Reward</span>
                                <span class="goals-level-detail-value-new goals-level-reward-new">$<?php echo number_format($lvl['reward'], 0); ?></span>
                            </div>
                        </div>
                        
                        <div class="goals-level-progress-new">
                            <?php if (!$is_unlocked && !$is_current && $num === $next_level_num): ?>
                                <!-- Show active progress only for the 'In Progress' rank -->
                                <div class="goals-level-progress-bar-wrapper-new">
                                    <div class="goals-level-progress-fill-new" style="width: <?php echo number_format($card_percent, 1); ?>%"></div>
                                </div>
                                <div class="goals-level-progress-text-new"><?php echo number_format($card_percent, 1); ?>%</div>
                            <?php elseif ($is_unlocked || $is_current): ?>
                                <div style="color: #2ecc71; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-check-circle"></i> Unlocked
                                </div>
                            <?php else: ?>
                                <div style="color: #94a3b8; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-lock"></i> Locked
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div> <!-- goals-new-page -->

<?php include('../../components/layout_bottom.php'); ?>
