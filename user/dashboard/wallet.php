<?php
$title = "Sparkx - Wallet";
$base_url = "../..";

require_once '../../includes/auth_check.php';

// Fetch lifetime aggregates
$earning_q = mysqli_query($conn, "SELECT SUM(amount) as total FROM transactions WHERE user_id = '$user_id' AND type = 'profit' AND status = 'completed'");
$plan_earning = mysqli_fetch_assoc($earning_q)['total'] ?? 0;

$ref_q = mysqli_query($conn, "SELECT SUM(amount) as total FROM transactions WHERE user_id = '$user_id' AND type = 'referral_bonus' AND status = 'completed'");
$referral_earning = mysqli_fetch_assoc($ref_q)['total'] ?? 0;

$dep_q = mysqli_query($conn, "SELECT SUM(amount) as total FROM transactions WHERE user_id = '$user_id' AND type = 'deposit' AND status = 'completed'");
$total_deposit = mysqli_fetch_assoc($dep_q)['total'] ?? 0;

$with_q = mysqli_query($conn, "SELECT SUM(amount) as total FROM transactions WHERE user_id = '$user_id' AND type = 'withdrawal' AND status = 'completed'");
$total_withdrawn = mysqli_fetch_assoc($with_q)['total'] ?? 0;

// Fetch transactions
$transactions_q = mysqli_query($conn, "SELECT * FROM transactions WHERE user_id = '$user_id' ORDER BY created_at DESC");
$transactions = [];
while ($row = mysqli_fetch_assoc($transactions_q)) {
    $transactions[] = $row;
}

include('../../components/layout_top.php');
?>

<div class="wallet-new-page">
    <!-- Header -->
    <div class="wallet-new-header">
        <div class="wallet-new-title-section">
            <h1 class="wallet-new-title">Wallet</h1>
            <p class="wallet-new-subtitle">Manage your cryptocurrency mining earnings and transactions</p>
        </div>
        <button class="wallet-visibility-btn" id="balanceToggleWallet" title="Toggle balance visibility">
            <i class="fas fa-eye" id="eyeIconWallet"></i>
            <i class="fas fa-eye-slash" id="eyeSlashIconWallet" style="display: none;"></i>
        </button>
    </div>

    <!-- Main Balance Card -->
    <div class="wallet-main-balance-card">
        <!-- Mobile Visibility Button -->
        <button class="wallet-balance-mobile-visibility" id="balanceToggleWalletMobile"
            title="Toggle balance visibility" style="display: none;">
            <i class="fas fa-eye" id="eyeIconWalletMobile"></i>
            <i class="fas fa-eye-slash" id="eyeSlashIconWalletMobile" style="display: none;"></i>
        </button>

        <div class="wallet-balance-content">
            <div class="wallet-balance-label">
                <div class="wallet-balance-label-text">
                    <i class="fas fa-eye" id="balanceLabelEye"></i>
                    <span>Total Balance</span>
                </div>
                <div class="wallet-balance-header-actions">
                    <i class="fas fa-arrow-up wallet-balance-trend-up"></i>
                </div>
            </div>
            <div class="wallet-balance-amount-wrapper" id="balanceAmountWallet">
                <span class="wallet-balance-currency">$</span>
                <span class="wallet-balance-amount"><?php echo number_format($user_data['deposit_balance'] + $user_data['earning_balance'], 2); ?></span>
            </div>

            <div class="wallet-balance-details">
                <div class="wallet-balance-detail-item">
                    <div class="wallet-detail-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="wallet-detail-content">
                        <div class="wallet-detail-label">Deposit Balance:</div>
                        <div class="wallet-detail-value">
                            $<?php echo number_format($user_data['deposit_balance'], 2); ?>
                        </div>
                    </div>
                </div>
                <div class="wallet-balance-detail-item">
                    <div class="wallet-detail-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="wallet-detail-content">
                        <div class="wallet-detail-label">Plan Earning:</div>
                        <div class="wallet-detail-value">
                            $<?php echo number_format($plan_earning, 2); ?>
                        </div>
                    </div>
                </div>
                <div class="wallet-balance-detail-item">
                    <div class="wallet-detail-icon">
                        <i class="fas fa-gift"></i>
                    </div>
                    <div class="wallet-detail-content">
                        <div class="wallet-detail-label">Referral Earning:</div>
                        <div class="wallet-detail-value">
                            $<?php echo number_format($referral_earning, 2); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Wallet Action Buttons -->
    <div style="display: flex; gap: 15px; margin: 25px 0 15px 0; justify-content: center; flex-wrap: wrap;">
        <a href="/sparkx1/user/dashboard/deposit" style="flex: 1; min-width: 140px; max-width: 220px; display: flex; align-items: center; justify-content: center; gap: 8px; background: linear-gradient(135deg, var(--primary-gradient-start), var(--primary-gradient-end)); color: white; padding: 13px 20px; border-radius: 12px; font-weight: 700; text-decoration: none; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.25); transition: transform 0.2s; text-align: center;">
            <i class="fas fa-arrow-up"></i>
            <span>Deposit Funds</span>
        </a>
        <a href="/sparkx1/user/dashboard/withdraw" style="flex: 1; min-width: 140px; max-width: 220px; display: flex; align-items: center; justify-content: center; gap: 8px; background: rgba(234, 88, 12, 0.08); color: var(--primary-color); padding: 13px 20px; border-radius: 12px; font-weight: 700; text-decoration: none; border: 1px solid rgba(234, 88, 12, 0.15); transition: transform 0.2s; text-align: center;">
            <i class="fas fa-arrow-down"></i>
            <span>Withdraw Money</span>
        </a>
    </div>

    <!-- Wallet Cards Grid -->
    <div class="wallet-cards-grid">
        <div class="wallet-card">
            <div class="wallet-card-header">
                <div class="wallet-card-icon-wrapper">
                    <i class="fas fa-arrow-up wallet-card-icon"></i>
                </div>
                <div class="wallet-card-trend">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
            <div class="wallet-card-body">
                <div class="wallet-card-label">Total Deposits</div>
                <div class="wallet-card-value">$<?php echo number_format($total_deposit, 2); ?></div>
                <div class="wallet-card-description">All-time deposit amount</div>
            </div>
        </div>

        <div class="wallet-card">
            <div class="wallet-card-header">
                <div class="wallet-card-icon-wrapper">
                    <i class="fas fa-arrow-down wallet-card-icon"></i>
                </div>
                <div class="wallet-card-trend">
                    <i class="fas fa-minus"></i>
                </div>
            </div>
            <div class="wallet-card-body">
                <div class="wallet-card-label">Total Withdrawals</div>
                <div class="wallet-card-value">$<?php echo number_format($total_withdrawn, 2); ?></div>
                <div class="wallet-card-description">All-time withdrawal amount</div>
            </div>
        </div>
    </div>

    <!-- Transactions Section -->
    <div class="wallet-transactions-section">
        <div class="wallet-transactions-header">
            <div class="wallet-transactions-title-section">
                <h2 class="wallet-transactions-title">Transaction History</h2>
                <p class="wallet-transactions-subtitle">View and manage all your mining transactions</p>
            </div>
            <div class="wallet-transactions-controls">
                <div class="wallet-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="wallet-search-input" placeholder="Search" id="walletSearchInput">
                </div>
                <button class="wallet-filter-button" title="Filter">
                    <i class="fas fa-filter"></i>
                    <span>Filter</span>
                </button>
                <select class="wallet-date-select" id="walletDateFilter">
                    <option value="all" selected>All Time</option>
                    <option value="this_week">This Week</option>
                    <option value="last_week">Last Week</option>
                    <option value="7">Last 7 Days</option>
                    <option value="30">Last 30 Days</option>
                    <option value="90">Last 90 Days</option>
                </select>
            </div>
        </div>

        <div class="wallet-table-container">
            <table class="wallet-table">
                <thead>
                    <tr>
                        <th>Transaction</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $tx): 
                        // Map database type to JS filter type
                        $js_type = $tx['type'];
                        if ($tx['type'] === 'referral_bonus') {
                            $js_type = 'referral_earning';
                        } elseif ($tx['type'] === 'profit' || $tx['type'] === 'investment') {
                            $js_type = 'mining_earning';
                        }
                        
                        // Status class
                        $status_class = 'wallet-status-completed';
                        if ($tx['status'] === 'pending') {
                            $status_class = 'wallet-status-pending';
                        } elseif ($tx['status'] === 'rejected') {
                            $status_class = 'wallet-status-rejected';
                        }
                        
                        // Icon & title
                        $icon = 'fa-arrow-up';
                        $title_text = 'Deposit';
                        if ($tx['type'] === 'withdrawal') {
                            $icon = 'fa-arrow-down';
                            $title_text = 'Withdrawal';
                        } elseif ($tx['type'] === 'investment') {
                            $icon = 'fa-dollar-sign';
                            $title_text = 'Plan Investment';
                        } elseif ($tx['type'] === 'profit') {
                            $icon = 'fa-coins';
                            $title_text = 'Mining Profit';
                        } elseif ($tx['type'] === 'referral_bonus') {
                            $icon = 'fa-users';
                            $title_text = 'Referral Bonus';
                        }
                    ?>
                        <tr data-transaction-timestamp="<?php echo strtotime($tx['created_at']); ?>" data-transaction-type="<?php echo $js_type; ?>">
                            <td>
                                <div class="wallet-transaction-cell">
                                    <div class="wallet-transaction-icon <?php echo ($tx['type'] === 'withdrawal' || $tx['type'] === 'investment') ? 'danger' : 'success'; ?>">
                                        <i class="fas <?php echo $icon; ?>"></i>
                                    </div>
                                    <div class="wallet-transaction-info">
                                        <div class="wallet-transaction-name"><?php echo $title_text; ?></div>
                                        <div class="wallet-transaction-id"><?php echo htmlspecialchars($tx['description'] ?? ''); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="wallet-type-badge <?php echo ($tx['type'] === 'withdrawal' || $tx['type'] === 'investment') ? 'wallet-type-debit' : 'wallet-type-credit'; ?>">
                                    <?php echo str_replace('_', ' ', $tx['type']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="wallet-amount-cell">
                                    <div class="wallet-amount-value <?php echo ($tx['type'] === 'withdrawal' || $tx['type'] === 'investment') ? 'wallet-amount-negative' : 'wallet-amount-positive'; ?>">
                                        <?php echo ($tx['type'] === 'withdrawal' || $tx['type'] === 'investment') ? '-' : '+'; ?>$<?php echo number_format($tx['amount'], 2); ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="wallet-status-badge <?php echo $status_class; ?>">
                                    <?php echo ucfirst($tx['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="wallet-date-cell">
                                    <div class="wallet-date-main"><?php echo date('d M Y', strtotime($tx['created_at'])); ?></div>
                                    <div class="wallet-date-time"><?php echo date('h:i A', strtotime($tx['created_at'])); ?></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 3rem 1.5rem;">
                                <div style="color: var(--text-secondary);">
                                    <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 0.75rem; opacity: 0.5;"></i>
                                    <p style="margin: 0; font-size: 0.9rem;">No transactions found</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div> <!-- wallet-new-page -->

<?php include('../../components/layout_bottom.php'); ?>