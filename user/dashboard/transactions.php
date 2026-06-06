<?php 
    $title = "Sparkx - Transaction History";
    $base_url = "../..";

    require_once '../../includes/auth_check.php';
    include('../../components/layout_top.php'); 

    // Fetch lifetime aggregates
    $earning_q = mysqli_query($conn, "SELECT SUM(amount) as total FROM transactions WHERE user_id = '$user_id' AND type IN ('profit', 'referral_bonus') AND status = 'completed'");
    $total_earning = mysqli_fetch_assoc($earning_q)['total'] ?? 0;

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
?>

<style>
.transactions-summary-section-new {
    gap: 1rem !important;
}
.transactions-summary-card-new {
    padding: 1.5rem 1rem !important;
}
.transactions-summary-icon-new {
    width: 50px !important;
    height: 50px !important;
    font-size: 1.25rem !important;
}
.transactions-summary-value-new {
    font-size: 1.85rem !important;
    white-space: nowrap !important;
}
</style>

                <div class="transactions-new-page">
                    <!-- Hero Section -->
                    <div class="transactions-hero-new transactions-hero-desktop">
                        <div class="transactions-hero-content-new">
                            <h1 class="transactions-hero-title-new">Transaction History</h1>
                            <p class="transactions-hero-subtitle-new">Track all your mining activities and transactions in one place</p>
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="transactions-summary-section-new">
                        <!-- Total Earning -->
                        <div class="transactions-summary-card-new">
                            <div class="transactions-summary-icon-new transactions-summary-icon-earning-new text-success">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="transactions-summary-content-new">
                                <div class="transactions-summary-label-new">Total Earning</div>
                                <div class="transactions-summary-value-new">$<?php echo number_format($total_earning, 2); ?></div>
                            </div>
                        </div>

                        <!-- Referral Earning -->
                        <div class="transactions-summary-card-new">
                            <div class="transactions-summary-icon-new transactions-summary-icon-referral-new">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="transactions-summary-content-new">
                                <div class="transactions-summary-label-new">Referral Earning</div>
                                <div class="transactions-summary-value-new">$<?php echo number_format($referral_earning, 2); ?></div>
                            </div>
                        </div>

                        <!-- Total Deposit -->
                        <div class="transactions-summary-card-new">
                            <div class="transactions-summary-icon-new transactions-summary-icon-deposit-new">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                            <div class="transactions-summary-content-new">
                                <div class="transactions-summary-label-new">Total Deposit</div>
                                <div class="transactions-summary-value-new">$<?php echo number_format($total_deposit, 2); ?></div>
                            </div>
                        </div>

                        <!-- Total Withdrawn -->
                        <div class="transactions-summary-card-new">
                            <div class="transactions-summary-icon-new transactions-summary-icon-withdraw-new">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                            <div class="transactions-summary-content-new">
                                <div class="transactions-summary-label-new">Total Withdrawn</div>
                                <div class="transactions-summary-value-new">$<?php echo number_format($total_withdrawn, 2); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- History Section -->
                    <div class="transactions-history-section-new">
                        <div class="transactions-history-header-new">
                            <div class="transactions-history-title-section-new">
                                <h2 class="transactions-history-title-new">All Transactions</h2>
                                <p class="transactions-history-subtitle-new">View and filter your complete transaction history</p>
                            </div>
                            <div class="transactions-history-controls-new">
                                <div class="transactions-search-box-new">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="transactions-search-input-new" placeholder="Search transactions..." id="transactionSearch">
                                </div>
                                <button class="transactions-filter-btn-new" title="Filter">
                                    <i class="fas fa-filter"></i>
                                    <span>Filter</span>
                                </button>
                                <select class="transactions-date-filter-new" id="transactionDateFilter">
                                    <option value="all" selected>All Time</option>
                                    <option value="this_week">This Week</option>
                                    <option value="last_week">Last Week</option>
                                    <option value="7">Last 7 Days</option>
                                    <option value="30">Last 30 Days</option>
                                    <option value="90">Last 90 Days</option>
                                </select>
                            </div>
                        </div>

                        <div class="transactions-history-card-new">
                            <div class="transactions-table-wrapper-new">
                                <table class="transactions-table-new">
                                    <thead>
                                        <tr>
                                            <th>Transaction</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="transactionsTableBody">
                                        <?php foreach ($transactions as $tx): 
                                            // Map database type to JS filter type
                                            $js_type = $tx['type'];
                                            if ($tx['type'] === 'referral_bonus') {
                                                $js_type = 'referral_earning';
                                            } elseif ($tx['type'] === 'profit' || $tx['type'] === 'investment') {
                                                $js_type = 'mining_earning';
                                            }
                                            
                                            // Status class
                                            $status_class = 'transactions-status-completed-new';
                                            if ($tx['status'] === 'pending') {
                                                $status_class = 'transactions-status-pending-new';
                                            } elseif ($tx['status'] === 'rejected') {
                                                $status_class = 'transactions-status-failed-new';
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
                                                    <div class="transactions-type-cell-new">
                                                        <div class="transactions-type-icon-new <?php echo ($tx['type'] === 'withdrawal' || $tx['type'] === 'investment') ? 'transactions-type-icon-warning-new' : 'transactions-type-icon-success-new'; ?>">
                                                            <i class="fas <?php echo $icon; ?>"></i>
                                                        </div>
                                                        <div class="transactions-type-info-new">
                                                            <div class="transactions-type-name-new"><?php echo $title_text; ?></div>
                                                            <div class="transactions-type-date-new"><?php echo date('d M Y, H:i', strtotime($tx['created_at'])); ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="transactions-amount-cell-new">
                                                        <div class="transactions-amount-value-new <?php echo ($tx['type'] === 'withdrawal' || $tx['type'] === 'investment') ? 'transactions-amount-danger-new' : 'transactions-amount-success-new'; ?>">
                                                            <?php echo ($tx['type'] === 'withdrawal' || $tx['type'] === 'investment') ? '-' : '+'; ?>$<?php echo number_format($tx['amount'], 2); ?>
                                                        </div>
                                                        <div class="transactions-amount-wallet-new"><?php echo htmlspecialchars($tx['description'] ?? ''); ?></div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="transactions-status-cell-new">
                                                        <span class="transactions-status-badge-new <?php echo $status_class; ?>">
                                                            <?php echo ucfirst($tx['status']); ?>
                                                        </span>
                                                        <span class="transactions-status-time-new"><?php echo date('h:i A', strtotime($tx['created_at'])); ?></span>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($transactions)): ?>
                                            <tr>
                                                <td colspan="3" style="text-align: center; padding: 4rem 2rem;">
                                                    <div class="transactions-empty-state-new">
                                                        <i class="fas fa-inbox transactions-empty-icon-new"></i>
                                                        <div class="transactions-empty-text-new">No transactions found</div>
                                                        <div class="transactions-empty-subtext-new">Your transaction records will appear here once you start mining.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div> <!-- transactions-history-card-new -->
                    </div> <!-- transactions-history-section-new -->
                </div> <!-- transactions-new-page -->

<?php include('../../components/layout_bottom.php'); ?>
