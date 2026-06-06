<?php 
    $title = "Sparkx - Salary System";
    $base_url = "../..";

    require_once '../../includes/auth_check.php';

    // Helper to fetch settings from database or return default
    function get_user_setting($conn, $key, $default = '') {
        $stmt = mysqli_prepare($conn, "SELECT value FROM settings WHERE name = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $key);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                return $row['value'];
            }
        }
        return $default;
    }

    // Fetch dynamic configurations
    $salary_days = (int)get_user_setting($conn, 'salary_days', '15');
    $default_guidelines = "Salary is distributed every [DAYS] days based on your active rank at the time of payout.\nBoth direct and indirect active members must remain active throughout the [DAYS]-day period.\nSelf investment must be maintained at the required level to stay eligible for the rank salary.\nIf any condition is not met at the time of payout, the salary for that cycle will not be credited.\nRanks are re-evaluated at the start of every new [DAYS]-day cycle.\nHigher ranks include all benefits of lower ranks and unlock greater salary rewards.";
    $guidelines_str = get_user_setting($conn, 'salary_guidelines', $default_guidelines);
    $guidelines_str = str_replace('[DAYS]', $salary_days, $guidelines_str);
    $guidelines_arr = explode("\n", $guidelines_str);

    // 1. Build the MLM network (Level 1 to Level 5) using the referrals closure table
    $direct_ids = [];
    $indirect_ids = [];
    $ref_q = mysqli_query($conn, "SELECT referee_id, level FROM referrals WHERE referrer_id = '$user_id' AND level <= 5");
    while ($r = mysqli_fetch_assoc($ref_q)) {
        if ((int)$r['level'] === 1) {
            $direct_ids[] = $r['referee_id'];
        } else {
            $indirect_ids[] = $r['referee_id'];
        }
    }
    $all_team_ids = array_merge($direct_ids, $indirect_ids);

    // 2. Fetch User Stats
    // a. Total Users (referred overall in 5 levels)
    $total_team_users = count($all_team_ids);

    // b. Total Active Users (users in network with at least 1 active investment)
    $total_active_users = 0;
    if ($total_team_users > 0) {
        $ids_str = implode(',', $all_team_ids);
        $active_q = mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) as total FROM investments WHERE user_id IN ($ids_str) AND status = 'active'");
        $total_active_users = mysqli_fetch_assoc($active_q)['total'] ?? 0;
    }

    // c. Total Direct Active (Level 1 users with at least 1 active investment)
    $total_direct_active = 0;
    if (count($direct_ids) > 0) {
        $direct_ids_str = implode(',', $direct_ids);
        $active_dir_q = mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) as total FROM investments WHERE user_id IN ($direct_ids_str) AND status = 'active'");
        $total_direct_active = mysqli_fetch_assoc($active_dir_q)['total'] ?? 0;
    }

    // d. Total Indirect Active (Level 2-5 users with at least 1 active investment)
    $total_indirect_active = 0;
    if (count($indirect_ids) > 0) {
        $indirect_ids_str = implode(',', $indirect_ids);
        $active_indir_q = mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) as total FROM investments WHERE user_id IN ($indirect_ids_str) AND status = 'active'");
        $total_indirect_active = mysqli_fetch_assoc($active_indir_q)['total'] ?? 0;
    }

    // e. Total Team Deposit (Sum of completed deposits of all referrals in 5 levels)
    $total_team_deposit = 0.00;
    if ($total_team_users > 0) {
        $ids_str = implode(',', $all_team_ids);
        $dep_q = mysqli_query($conn, "SELECT SUM(amount) as total FROM transactions WHERE user_id IN ($ids_str) AND type = 'deposit' AND status = 'completed'");
        $total_team_deposit = mysqli_fetch_assoc($dep_q)['total'] ?? 0.00;
    }

    // f. Total Team Investment (Sum of active investments of all referrals in 5 levels)
    $total_team_investment = 0.00;
    if ($total_team_users > 0) {
        $ids_str = implode(',', $all_team_ids);
        $inv_q = mysqli_query($conn, "SELECT SUM(amount) as total FROM investments WHERE user_id IN ($ids_str) AND status = 'active'");
        $total_team_investment = mysqli_fetch_assoc($inv_q)['total'] ?? 0.00;
    }

    // g. Self Active Investment (own active investments sum)
    $self_active_q = mysqli_query($conn, "SELECT SUM(amount) as total FROM investments WHERE user_id = '$user_id' AND status = 'active'");
    $self_active_investment = mysqli_fetch_assoc($self_active_q)['total'] ?? 0;

    // 3. Process Claim Salary Request
    $claim_success = '';
    $claim_error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_rank_id'])) {
        $rank_id = (int)$_POST['claim_rank_id'];
        
        $r_q = mysqli_query($conn, "SELECT * FROM salary_ranks WHERE id = '$rank_id'");
        $rank_details = mysqli_fetch_assoc($r_q);
        
        if ($rank_details) {
            $req_self = $rank_details['self_invest'];
            $req_direct = $rank_details['direct_active'];
            $req_indirect = $rank_details['indirect_active'];
            
            if ($self_active_investment >= $req_self && $total_direct_active >= $req_direct && $total_indirect_active >= $req_indirect) {
                // Check if already claimed/pending
                $existing_q = mysqli_query($conn, "SELECT status FROM salary_claims WHERE user_id = '$user_id' AND rank_id = '$rank_id' AND status = 'pending'");
                if (mysqli_num_rows($existing_q) === 0) {
                    $rank_name = mysqli_real_escape_string($conn, $rank_details['rank_name']);
                    $amount = $rank_details['salary_amount'];
                    
                    mysqli_query($conn, "INSERT INTO salary_claims (user_id, rank_id, rank_name, amount, status) 
                                         VALUES ('$user_id', '$rank_id', '$rank_name', '$amount', 'pending')");
                    
                    $claim_success = "Your claim for " . htmlspecialchars($rank_name) . " has been submitted successfully! Admin will review and credit the balance.";
                } else {
                    $claim_error = "You already have a pending claim for this rank.";
                }
            } else {
                $claim_error = "You do not meet the criteria to claim this salary.";
            }
        }
    }

    // Fetch dynamic manager ranks from database
    $ranks_q = mysqli_query($conn, "SELECT * FROM salary_ranks ORDER BY id ASC");
    $ranks = [];
    while ($row = mysqli_fetch_assoc($ranks_q)) {
        $ranks[] = $row;
    }

    include('../../components/layout_top.php'); 
?>

<div class="spx-page">
    <div class="spx-topbar">
        <a href="../../user/dashboard/wallet" class="spx-back">&#8592;</a>
        <h1>Spark X Salary System</h1>
    </div>

    <!-- Feedback Alerts -->
    <?php if ($claim_success): ?>
        <div style="background: rgba(46, 204, 113, 0.15); border: 1px solid #2ecc71; color: #2ecc71; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 500; max-width: 540px; margin-left: auto; margin-right: auto;">
            <i class="fas fa-check-circle"></i> <?php echo $claim_success; ?>
        </div>
    <?php endif; ?>
    <?php if ($claim_error): ?>
        <div style="background: rgba(231, 76, 60, 0.15); border: 1px solid #e74c3c; color: #e74c3c; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 500; max-width: 540px; margin-left: auto; margin-right: auto;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $claim_error; ?>
        </div>
    <?php endif; ?>

    <div class="spx-grid">
        <a href="../../totalusers" class="spx-card">
            <div class="spx-card-label">Total Users</div>
            <p class="spx-card-value val-dark"><?php echo $total_team_users; ?></p>
            <span class="spx-card-arrow">&#8599;</span>
        </a>

        <a href="../../totalactiveinvesteduser" class="spx-card">
            <div class="spx-card-label">Total Active Users</div>
            <p class="spx-card-value val-purple"><?php echo $total_active_users; ?></p>
            <span class="spx-card-arrow">&#8599;</span>
        </a>

        <a href="../../directinvested" class="spx-card">
            <div class="spx-card-label">Total Direct Active</div>
            <p class="spx-card-value val-purple"><?php echo $total_direct_active; ?></p>
            <span class="spx-card-arrow">&#8599;</span>
        </a>

        <a href="../../indirect" class="spx-card">
            <div class="spx-card-label">Total Indirect Active</div>
            <p class="spx-card-value val-red"><?php echo $total_indirect_active; ?></p>
            <span class="spx-card-arrow">&#8599;</span>
        </a>

        <a href="../../totaldeposits" class="spx-card">
            <div class="spx-card-label">Total Team Deposit</div>
            <p class="spx-card-value val-green">$<?php echo number_format($total_team_deposit, 2); ?></p>
            <span class="spx-card-arrow">&#8599;</span>
        </a>

        <a href="../../totalinvested" class="spx-card">
            <div class="spx-card-label">Total Team Investment</div>
            <p class="spx-card-value val-purple">$<?php echo number_format($total_team_investment, 2); ?></p>
            <span class="spx-card-arrow">&#8599;</span>
        </a>
    </div>

    <div class="rank-section">
        <div class="rank-section-header">
            <h2>&#127942; Spark X Manager Rank System</h2>
            <p>Achieve ranks based on your self-investment, direct &amp; indirect active members to unlock <?php echo $salary_days; ?>-day salary rewards.</p>
        </div>

        <div class="rank-cards-list">
            <?php foreach ($ranks as $rank): 
                $req_self = $rank['self_invest'];
                $req_direct = $rank['direct_active'];
                $req_indirect = $rank['indirect_active'];
                
                $qualifies = ($self_active_investment >= $req_self && $total_direct_active >= $req_direct && $total_indirect_active >= $req_indirect);
            ?>
                <div class="rank-card">
                    <div class="rank-card-head">
                        <span class="rank-badge">&#127775; <?php echo htmlspecialchars($rank['rank_name']); ?></span>
                        <span class="rank-salary-tag">&#128176; $<?php echo number_format($rank['salary_amount'], 0); ?> / <?php echo $salary_days; ?> Days</span>
                    </div>
                    <div class="rank-card-body">
                        <div class="rank-stat">
                            <div class="rank-stat-label">Self Invest</div>
                            <div class="rank-stat-value">$<?php echo number_format($req_self, 0); ?></div>
                        </div>
                        <div class="rank-stat">
                            <div class="rank-stat-label">Direct Active</div>
                            <div class="rank-stat-value"><?php echo $req_direct; ?></div>
                        </div>
                        <div class="rank-stat">
                            <div class="rank-stat-label">Indirect Active</div>
                            <div class="rank-stat-value"><?php echo $req_indirect; ?></div>
                        </div>
                    </div>
                    
                    <?php
                    // Check if claim exists
                    $claim_q = mysqli_query($conn, "SELECT * FROM salary_claims WHERE user_id = '$user_id' AND rank_id = '{$rank['id']}' ORDER BY id DESC LIMIT 1");
                    $claim = mysqli_fetch_assoc($claim_q);
                    
                    if ($claim || $qualifies):
                    ?>
                        <div class="rank-card-footer" style="padding: 14px 16px; border-top: 1px solid var(--purple-border); display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                            <div class="rank-status-text">
                                <?php if ($claim): ?>
                                    <?php if ($claim['status'] === 'pending'): ?>
                                        <span style="color: #D97706; font-weight: 700; font-size: 0.82rem;"><i class="fas fa-hourglass-half"></i> Claim Pending Review</span>
                                    <?php elseif ($claim['status'] === 'approved'): ?>
                                        <span style="color: #059669; font-weight: 700; font-size: 0.82rem;"><i class="fas fa-check-circle"></i> Payout Credited</span>
                                    <?php else: ?>
                                        <span style="color: #EF4444; font-weight: 700; font-size: 0.82rem;"><i class="fas fa-times-circle"></i> Claim Rejected</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: #059669; font-weight: 700; font-size: 0.82rem;"><i class="fas fa-check-circle"></i> Criteria Met!</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="rank-action-button">
                                <?php
                                if ($claim) {
                                    if ($claim['status'] === 'pending') {
                                        echo '<button class="btn btn-sm" disabled style="background: #FEF3C7; border: 1px solid #FCD34D; color: #D97706; padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 0.8rem; cursor: not-allowed;">Pending</button>';
                                    } elseif ($claim['status'] === 'approved') {
                                        echo '<button class="btn btn-sm" disabled style="background: #D1FAE5; border: 1px solid #A7F3D0; color: #059669; padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 0.8rem; cursor: not-allowed;">Claimed</button>';
                                    } else {
                                        // Rejected: Allow re-claim
                                        if ($qualifies) {
                                            echo '<form method="POST" style="margin: 0;">';
                                            echo '<input type="hidden" name="claim_rank_id" value="' . $rank['id'] . '">';
                                            echo '<button type="submit" style="background: var(--purple-main); border: none; color: #fff; padding: 5px 14px; border-radius: 20px; font-weight: 800; font-size: 0.8rem; cursor: pointer; transition: all 0.2s;">Claim Again</button>';
                                            echo '</form>';
                                        } else {
                                            echo '<button class="btn btn-sm" disabled style="background: #FEE2E2; border: 1px solid #FCA5A5; color: #EF4444; padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 0.8rem; cursor: not-allowed;">Rejected</button>';
                                        }
                                    }
                                } else {
                                    if ($qualifies) {
                                        echo '<form method="POST" style="margin: 0;">';
                                        echo '<input type="hidden" name="claim_rank_id" value="' . $rank['id'] . '">';
                                        echo '<button type="submit" style="background: var(--purple-main); border: none; color: #fff; padding: 5px 14px; border-radius: 20px; font-weight: 800; font-size: 0.8rem; cursor: pointer; transition: all 0.2s;">Claim Payout</button>';
                                        echo '</form>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="rank-guidelines">
                <div class="rank-guidelines-title">
                    <span class="dot"></span>
                    Important Guidelines for the <?php echo $salary_days; ?> Days Salary System
                </div>
                <ul>
                    <?php foreach ($guidelines_arr as $line): 
                        $line = trim($line);
                        if (!empty($line)): ?>
                            <li><?php echo htmlspecialchars($line); ?></li>
                        <?php endif; 
                    endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div> <!-- spx-page -->

<?php include('../../components/layout_bottom.php'); ?>
