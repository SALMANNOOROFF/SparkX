<?php 
    $title = "Sparkx - Referral Program";
    $base_url = "../..";

    require_once '../../includes/auth_check.php';

    // 1. Self-Healing MLM Synchronizer
    // If any user has referred_by set but is missing from the referrals table, we self-heal their closure links automatically
    $missing_referrals_q = mysqli_query($conn, "SELECT id, referred_by FROM users WHERE referred_by != '' AND id NOT IN (SELECT referee_id FROM referrals)");
    if ($missing_referrals_q) {
        while ($m_ref = mysqli_fetch_assoc($missing_referrals_q)) {
            $referee_id = $m_ref['id'];
            $referred_by_code = $m_ref['referred_by'];
            
            $current_referred_by = $referred_by_code;
            $current_referee_id = $referee_id;
            $level = 1;
            
            while (!empty($current_referred_by) && $level <= 5) {
                $parent_q = mysqli_query($conn, "SELECT id, referral_code, referred_by FROM users WHERE referral_code = '$current_referred_by'");
                if ($parent_q && mysqli_num_rows($parent_q) > 0) {
                    $parent = mysqli_fetch_assoc($parent_q);
                    $parent_id = $parent['id'];
                    
                    mysqli_query($conn, "INSERT IGNORE INTO referrals (referrer_id, referee_id, level) VALUES ('$parent_id', '$current_referee_id', '$level')");
                    
                    $current_referred_by = $parent['referred_by'];
                    $level++;
                } else {
                    break;
                }
            }
        }
    }

    // Helper to fetch the referral network
    function get_referrals_network($conn, $user_id, $filter_level = 'all') {
        $level_cond = "";
        if ($filter_level !== 'all') {
            $l = (int)$filter_level;
            $level_cond = " AND r.level = '$l' ";
        }
        
        // Fetch levels commission rate from DB
        $settings_q = mysqli_query($conn, "SELECT level, commission_pct FROM referral_settings");
        $rates = [];
        if ($settings_q) {
            while ($row = mysqli_fetch_assoc($settings_q)) {
                $rates[$row['level']] = (float)$row['commission_pct'];
            }
        }
        // Fallback defaults
        for ($i = 1; $i <= 5; $i++) {
            if (!isset($rates[$i])) {
                $rates[$i] = ($i == 1) ? 10.0 : (($i == 2) ? 5.0 : (($i == 3) ? 3.0 : (($i == 4) ? 2.0 : 1.0)));
            }
        }
        
        $query = "SELECT r.referee_id, r.level, u.name, u.phone, u.created_at, w.total_invested as invested_amount 
                  FROM referrals r 
                  LEFT JOIN users u ON r.referee_id = u.id 
                  LEFT JOIN wallets w ON u.id = w.user_id 
                  WHERE r.referrer_id = '$user_id' AND r.level <= 5 $level_cond
                  ORDER BY r.level ASC, r.created_at DESC";
                  
        $res = mysqli_query($conn, $query);
        $referrals = [];
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $lvl = (int)$row['level'];
                $comm_pct = $rates[$lvl] ?? 1.0;
                
                $referee_invested = (float)($row['invested_amount'] ?? 0.0);
                $earning_generated = $referee_invested * ($comm_pct / 100.0);
                
                $referrals[] = [
                    'id' => $row['referee_id'],
                    'name' => htmlspecialchars($row['name'] ?? 'N/A'),
                    'phone' => htmlspecialchars($row['phone'] ?? 'N/A'),
                    'created_at' => $row['created_at'],
                    'joined_at' => date('M d, Y', strtotime($row['created_at'])),
                    'level' => $lvl,
                    'level_name' => "Level " . $lvl,
                    'referral_earning' => $earning_generated,
                    'invested_amount' => $referee_invested
                ];
            }
        }
        return $referrals;
    }

    // 2. Handle AJAX Requests
    if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
        header('Content-Type: application/json');
        $filter_level = $_GET['level'] ?? 'all';
        $referrals = get_referrals_network($conn, $user_id, $filter_level);
        
        echo json_encode([
            'success' => true,
            'referrals' => $referrals,
            'pagination' => [
                'current_page' => 1,
                'last_page' => 1,
                'has_more_pages' => false,
                'previous_page_url' => '#',
                'next_page_url' => '#',
                'url_range' => ['1' => '#']
            ]
        ]);
        exit();
    }

    // 3. Stats & Setup
    // Total Referrals Count
    $total_ref_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM referrals WHERE referrer_id = '$user_id' AND level <= 5");
    $total_referrals = mysqli_fetch_assoc($total_ref_q)['total'] ?? 0;

    // Referred By Upline Details
    $upline_user = null;
    if (!empty($user_data['referred_by'])) {
        $parent_code = mysqli_real_escape_string($conn, $user_data['referred_by']);
        $upline_q = mysqli_query($conn, "SELECT name, email, phone FROM users WHERE referral_code = '$parent_code'");
        if ($upline_q) {
            $upline_user = mysqli_fetch_assoc($upline_q);
        }
    }

    // Level Commission Rates (controlled from Admin settings)
    $settings_q = mysqli_query($conn, "SELECT level, commission_pct FROM referral_settings");
    $db_commission_rates = [];
    if ($settings_q) {
        while ($row = mysqli_fetch_assoc($settings_q)) {
            $db_commission_rates[$row['level']] = (float)$row['commission_pct'];
        }
    }
    $commission_rates = [
        1 => $db_commission_rates[1] ?? 10.00,
        2 => $db_commission_rates[2] ?? 5.00,
        3 => $db_commission_rates[3] ?? 3.00,
        4 => $db_commission_rates[4] ?? 2.00,
        5 => $db_commission_rates[5] ?? 1.00,
    ];

    // Dynamic Referral Link
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];
    $referral_link = $protocol . $domainName . "/sparkx1/register?ref=" . urlencode($user_data['referral_code']);

    include('../../components/layout_top.php'); 
?>

                <div class="referrals-new-page">
                    <!-- Hero Section -->
                    <div class="referrals-hero-new">
                        <div class="referrals-hero-content-new">
                            <h1 class="referrals-hero-title-new">Referral Program</h1>
                            <p class="referrals-hero-subtitle-new">Invite friends and earn commissions on their mining activities</p>
                        </div>
                    </div>

                    <!-- Stats Section -->
                    <div class="referrals-stats-section-new">
                        <div class="referrals-stat-card-new">
                            <div class="referrals-stat-icon-new referrals-stat-icon-users-new">
                                <i class="fas fa-gift"></i>
                            </div>
                            <div class="referrals-stat-content-new">
                                <div class="referrals-stat-label-new">
                                    <button class="anchortag" id="claimEarningsBtn" style="background: none; border: none; padding: 0; font: inherit; cursor: pointer; text-align: left;">Claim <br>Earnings</button>
                                </div>
                            </div>
                        </div>

                        <div class="referrals-stat-card-new">
                            <div class="referrals-stat-icon-new referrals-stat-icon-users-new">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="referrals-stat-content-new">
                                <div class="referrals-stat-label-new">Total Referrals</div>
                                <div class="referrals-stat-value-new"><?php echo $total_referrals; ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Referral Tools Section -->
                    <div class="referrals-tools-section-new">
                        <div class="referrals-tools-header-new">
                            <h2 class="referrals-tools-title-new">Your Referral Tools</h2>
                            <p class="referrals-tools-subtitle-new">Share your unique link or code to start earning</p>
                        </div>
                        <div class="referrals-tools-grid-new">
                            <!-- Referral Link Card -->
                            <div class="referrals-tool-card-new">
                                <div class="referrals-tool-header-new">
                                    <div class="referrals-tool-icon-new">
                                        <i class="fas fa-link"></i>
                                    </div>
                                    <h3 class="referrals-tool-title-new">Referral Link</h3>
                                </div>
                                <div class="referrals-tool-body-new">
                                    <div class="referrals-tool-input-wrapper-new">
                                        <input type="text" class="referrals-tool-input-new" id="referralLink" value="<?php echo htmlspecialchars($referral_link); ?>" readonly>
                                        <button class="referrals-tool-copy-btn-new" data-copy="referralLink" title="Copy Link">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                    <p class="referrals-tool-hint-new">Share this link with your friends</p>
                                </div>
                            </div>

                            <!-- Referral Code Card -->
                            <div class="referrals-tool-card-new">
                                <div class="referrals-tool-header-new">
                                    <div class="referrals-tool-icon-new">
                                        <i class="fas fa-barcode"></i>
                                    </div>
                                    <h3 class="referrals-tool-title-new">Referral Code</h3>
                                </div>
                                <div class="referrals-tool-body-new">
                                    <div class="referrals-tool-input-wrapper-new">
                                        <input type="text" class="referrals-tool-input-new" id="referralCode" value="<?php echo htmlspecialchars($user_data['referral_code']); ?>" readonly>
                                        <button class="referrals-tool-copy-btn-new" data-copy="referralCode" title="Copy Code">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                    <p class="referrals-tool-hint-new">Use this code during registration</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Referrer Info Section -->
                    <div class="referrals-referrer-section-new">
                        <div class="referrals-referrer-card-new">
                            <div class="referrals-referrer-header-new">
                                <div class="referrals-referrer-icon-new">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <h3 class="referrals-referrer-title-new">Referred By</h3>
                            </div>
                            <?php if ($upline_user): ?>
                                <div class="referrals-referrer-info-grid-new">
                                    <div class="referrals-referrer-info-item-new">
                                        <div class="referrals-referrer-info-label-new">
                                            <i class="fas fa-user"></i>
                                            <span>Name</span>
                                        </div>
                                        <div class="referrals-referrer-info-value-new"><?php echo htmlspecialchars($upline_user['name']); ?></div>
                                    </div>
                                    <div class="referrals-referrer-info-item-new">
                                        <div class="referrals-referrer-info-label-new">
                                            <i class="fas fa-envelope"></i>
                                            <span>Email</span>
                                        </div>
                                        <div class="referrals-referrer-info-value-new"><?php echo htmlspecialchars($upline_user['email']); ?></div>
                                    </div>
                                    <div class="referrals-referrer-info-item-new">
                                        <div class="referrals-referrer-info-label-new">
                                            <i class="fas fa-phone"></i>
                                            <span>Phone</span>
                                        </div>
                                        <div class="referrals-referrer-info-value-new"><?php echo htmlspecialchars($upline_user['phone'] ? $upline_user['phone'] : 'N/A'); ?></div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div style="padding: 1.5rem; text-align: center; color: var(--text-secondary); background: rgba(255, 255, 255, 0.02); border-radius: 12px; border: 1px dashed rgba(255, 255, 255, 0.05); font-weight: 500;">
                                    <i class="fas fa-user-shield" style="font-size: 1.5rem; margin-bottom: 0.5rem; color: var(--text-secondary); opacity: 0.7;"></i>
                                    <p style="margin: 0; font-size: 0.9rem;">Direct Registration (No Referrer associated with this account)</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Investment Commission Structure Section -->
                    <div class="referrals-investment-commission-section-new">
                        <div class="referrals-commission-header-new">
                            <h2 class="referrals-commission-title-new">Investment Commission Structure</h2>
                            <p class="referrals-commission-subtitle-new">Earn commissions on investments across 5 levels</p>
                        </div>
                        <div class="referrals-investment-commission-desktop">
                            <div class="referrals-commission-grid-new referrals-commission-desktop">
                                <div class="referrals-commission-card-new referrals-commission-level-1">
                                    <div class="referrals-commission-level-badge-new">Level 1</div>
                                    <div class="referrals-commission-level-icon-new"><i class="fas fa-trophy"></i></div>
                                    <div class="referrals-commission-level-name-new">Direct Referral</div>
                                    <div class="referrals-commission-rate-new">
                                        <span class="referrals-commission-rate-value-new"><?php echo number_format($commission_rates[1], 2); ?>%</span>
                                        <span class="referrals-commission-rate-label-new">Commission Rate</span>
                                    </div>
                                </div>
                                <div class="referrals-commission-card-new referrals-commission-level-2">
                                    <div class="referrals-commission-level-badge-new">Level 2</div>
                                    <div class="referrals-commission-level-icon-new"><i class="fas fa-medal"></i></div>
                                    <div class="referrals-commission-level-name-new">Second Level</div>
                                    <div class="referrals-commission-rate-new">
                                        <span class="referrals-commission-rate-value-new"><?php echo number_format($commission_rates[2], 2); ?>%</span>
                                        <span class="referrals-commission-rate-label-new">Commission Rate</span>
                                    </div>
                                </div>
                                <div class="referrals-commission-card-new referrals-commission-level-3">
                                    <div class="referrals-commission-level-badge-new">Level 3</div>
                                    <div class="referrals-commission-level-icon-new"><i class="fas fa-award"></i></div>
                                    <div class="referrals-commission-level-name-new">Third Level</div>
                                    <div class="referrals-commission-rate-new">
                                        <span class="referrals-commission-rate-value-new"><?php echo number_format($commission_rates[3], 2); ?>%</span>
                                        <span class="referrals-commission-rate-label-new">Commission Rate</span>
                                    </div>
                                </div>
                                <div class="referrals-commission-card-new referrals-commission-level-4">
                                    <div class="referrals-commission-level-badge-new">Level 4</div>
                                    <div class="referrals-commission-level-icon-new"><i class="fas fa-gem"></i></div>
                                    <div class="referrals-commission-level-name-new">Fourth Level</div>
                                    <div class="referrals-commission-rate-new">
                                        <span class="referrals-commission-rate-value-new"><?php echo number_format($commission_rates[4], 2); ?>%</span>
                                        <span class="referrals-commission-rate-label-new">Commission Rate</span>
                                    </div>
                                </div>
                                <div class="referrals-commission-card-new referrals-commission-level-5">
                                    <div class="referrals-commission-level-badge-new">Level 5</div>
                                    <div class="referrals-commission-level-icon-new"><i class="fas fa-crown"></i></div>
                                    <div class="referrals-commission-level-name-new">Fifth Level</div>
                                    <div class="referrals-commission-rate-new">
                                        <span class="referrals-commission-rate-value-new"><?php echo number_format($commission_rates[5], 2); ?>%</span>
                                        <span class="referrals-commission-rate-label-new">Commission Rate</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="referrals-investment-commission-mobile">
                            <div class="referrals-investment-mobile-grid-new">
                                <div class="referrals-investment-mobile-header-new">
                                    <h2 class="referrals-investment-mobile-header-title-new">Investment Commission Structure</h2>
                                    <p class="referrals-investment-mobile-header-subtitle-new">Earn commissions across 5 levels</p>
                                </div>
                                <div class="referrals-commission-mobile-item-new referrals-commission-mobile-level-1">
                                    <div class="referrals-commission-mobile-icon-wrapper-new">
                                        <div class="referrals-commission-mobile-icon-new"><i class="fas fa-trophy"></i></div>
                                        <div class="referrals-commission-mobile-badge-new">L1</div>
                                    </div>
                                    <div class="referrals-commission-mobile-content-new">
                                        <div class="referrals-commission-mobile-name-new">Direct Referral</div>
                                        <div class="referrals-commission-mobile-rate-new"><?php echo number_format($commission_rates[1], 2); ?>%</div>
                                    </div>
                                </div>
                                <div class="referrals-commission-mobile-item-new referrals-commission-mobile-level-2">
                                    <div class="referrals-commission-mobile-icon-wrapper-new">
                                        <div class="referrals-commission-mobile-icon-new"><i class="fas fa-medal"></i></div>
                                        <div class="referrals-commission-mobile-badge-new">L2</div>
                                    </div>
                                    <div class="referrals-commission-mobile-content-new">
                                        <div class="referrals-commission-mobile-name-new">Second Level</div>
                                        <div class="referrals-commission-mobile-rate-new"><?php echo number_format($commission_rates[2], 2); ?>%</div>
                                    </div>
                                </div>
                                <div class="referrals-commission-mobile-item-new referrals-commission-mobile-level-3">
                                    <div class="referrals-commission-mobile-icon-wrapper-new">
                                        <div class="referrals-commission-mobile-icon-new"><i class="fas fa-award"></i></div>
                                        <div class="referrals-commission-mobile-badge-new">L3</div>
                                    </div>
                                    <div class="referrals-commission-mobile-content-new">
                                        <div class="referrals-commission-mobile-name-new">Third Level</div>
                                        <div class="referrals-commission-mobile-rate-new"><?php echo number_format($commission_rates[3], 2); ?>%</div>
                                    </div>
                                </div>
                                <div class="referrals-commission-mobile-item-new referrals-commission-mobile-level-4">
                                    <div class="referrals-commission-mobile-icon-wrapper-new">
                                        <div class="referrals-commission-mobile-icon-new"><i class="fas fa-gem"></i></div>
                                        <div class="referrals-commission-mobile-badge-new">L4</div>
                                    </div>
                                    <div class="referrals-commission-mobile-content-new">
                                        <div class="referrals-commission-mobile-name-new">Fourth Level</div>
                                        <div class="referrals-commission-mobile-rate-new"><?php echo number_format($commission_rates[4], 2); ?>%</div>
                                    </div>
                                </div>
                                <div class="referrals-commission-mobile-item-new referrals-commission-mobile-level-5">
                                    <div class="referrals-commission-mobile-icon-wrapper-new">
                                        <div class="referrals-commission-mobile-icon-new"><i class="fas fa-crown"></i></div>
                                        <div class="referrals-commission-mobile-badge-new">L5</div>
                                    </div>
                                    <div class="referrals-commission-mobile-content-new">
                                        <div class="referrals-commission-mobile-name-new">Fifth Level</div>
                                        <div class="referrals-commission-mobile-rate-new"><?php echo number_format($commission_rates[5], 2); ?>%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Network Table Section -->
                    <div class="referrals-network-section-new">
                        <div class="referrals-network-header-new">
                            <div>
                                <h2 class="referrals-network-title-new">Your Network</h2>
                                <p class="referrals-network-subtitle-new">View all your referrals and their investments</p>
                            </div>
                            <div class="referrals-network-dropdown-new">
                                <button class="referrals-network-dropdown-btn-new" id="levelFilterBtn" onclick="toggleLevelDropdown()">
                                    <span id="selectedLevelLabel">All Levels</span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <div class="referrals-network-dropdown-menu-new" id="levelFilterMenu">
                                    <div class="referrals-network-dropdown-item-new active" onclick="selectLevel('all')">
                                        <span>All Levels</span>
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="referrals-network-dropdown-item-new" onclick="selectLevel('1')"><span>Level 1</span></div>
                                    <div class="referrals-network-dropdown-item-new" onclick="selectLevel('2')"><span>Level 2</span></div>
                                    <div class="referrals-network-dropdown-item-new" onclick="selectLevel('3')"><span>Level 3</span></div>
                                    <div class="referrals-network-dropdown-item-new" onclick="selectLevel('4')"><span>Level 4</span></div>
                                    <div class="referrals-network-dropdown-item-new" onclick="selectLevel('5')"><span>Level 5</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="referrals-network-card-new">
                            <div class="referrals-network-table-wrapper-new">
                                <table class="referrals-network-table-new">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Details</th>
                                            <th class="referrals-network-table-desktop-header">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="referralsTableBody">
                                        <?php 
                                        $initial_referrals = get_referrals_network($conn, $user_id, 'all');
                                        if (!empty($initial_referrals)): 
                                            foreach ($initial_referrals as $ref):
                                                $initial = !empty($ref['name']) ? strtoupper($ref['name'][0]) : 'U';
                                                $levelClass = "referral-level-badge-" . $ref['level'];
                                                $referralData = htmlspecialchars(json_encode($ref), ENT_QUOTES, 'UTF-8');
                                        ?>
                                                <tr class="referral-row-clickable" data-referral="<?php echo $referralData; ?>">
                                                    <td class="referrals-network-user-cell-new">
                                                        <div class="referrals-network-user-avatar-new"><?php echo $initial; ?></div>
                                                        <div class="referrals-network-user-info-new">
                                                            <h4 class="referrals-network-user-name-new"><?php echo $ref['name']; ?></h4>
                                                            <span class="referrals-network-user-date-new">Joined: <?php echo $ref['joined_at']; ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="referrals-network-detail-cell-new">
                                                        <div class="referrals-network-detail-item-new">
                                                            <span class="referral-level-badge-new <?php echo $levelClass; ?>"><?php echo $ref['level_name']; ?></span>
                                                        </div>
                                                        <div class="referrals-network-detail-item-new">
                                                            <span class="referrals-network-earning-value-new">$<?php echo number_format($ref['referral_earning'], 2); ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="referrals-network-action-cell-new referrals-network-table-desktop-header">
                                                        <button class="referral-detail-btn-new">View Details</button>
                                                    </td>
                                                </tr>
                                        <?php 
                                            endforeach;
                                        else: 
                                        ?>
                                            <tr>
                                                <td colspan="3" class="referrals-network-empty-new">
                                                    <div class="referrals-network-empty-content-new">
                                                        <div class="referrals-network-empty-icon-new"><i class="fas fa-users"></i></div>
                                                        <p class="referrals-network-empty-message-new">You don't have any referrals yet</p>
                                                        <button class="referrals-network-invite-btn-new" onclick="copyReferralLink()">
                                                            <i class="fas fa-share-alt"></i>
                                                            <span>Invite Now</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Referral User Detail Modal -->
                    <div id="referralDetailModal" class="referral-detail-modal" style="display: none;">
                        <div class="referral-detail-modal-overlay" onclick="closeReferralModal()"></div>
                        <div class="referral-detail-modal-content">
                            <div class="referral-detail-modal-title">
                                <span>Referral User Detail</span>
                                <button class="referral-detail-modal-close" onclick="closeReferralModal()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="referral-detail-header">
                                <div class="referral-detail-avatar"><span id="modalUserInitial">U</span></div>
                                <h3 class="referral-detail-name" id="modalUserName">User Name</h3>
                                <p class="referral-detail-date" id="modalUserDate">Jan 1, 2026</p>
                            </div>
                            <div class="referral-detail-body">
                                <div class="referral-detail-item">
                                    <span class="referral-detail-label">Phone Number:</span>
                                    <span class="referral-detail-value" id="modalUserPhone">N/A</span>
                                </div>
                                <div class="referral-detail-item">
                                    <span class="referral-detail-label">Level:</span>
                                    <span class="referral-detail-value" id="modalUserLevel">N/A</span>
                                </div>
                                <div class="referral-detail-item">
                                    <span class="referral-detail-label">Earning:</span>
                                    <span class="referral-detail-value" id="modalUserEarning">$0</span>
                                </div>
                                <div class="referral-detail-item">
                                    <span class="referral-detail-label">Invested Amount:</span>
                                    <span class="referral-detail-value" id="modalUserInvested">$0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- referrals-new-page -->

                <script>
                    function toggleLevelDropdown() {
                        const dropdownMenu = document.getElementById('levelFilterMenu');
                        if (dropdownMenu) {
                            dropdownMenu.classList.toggle('show');
                        }
                    }

                    // Close dropdown when clicking outside
                    window.addEventListener('click', function(e) {
                        const filterBtn = document.getElementById('levelFilterBtn');
                        const dropdownMenu = document.getElementById('levelFilterMenu');
                        if (filterBtn && dropdownMenu && !filterBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                            dropdownMenu.classList.remove('show');
                        }
                    });
                </script>

<?php include('../../components/layout_bottom.php'); ?>
