<?php 
    require_once '../../includes/auth_check.php';
    $title = "Sparkx - Withdraw";
    $base_url = "../..";

    $meta_tags = '<meta name="conversion-rate" content="280">';
    include('../../components/layout_top.php'); 
?>

<script>
    const userEarningBalance = <?php echo (float)$user_data['earning_balance']; ?>;
</script>

                <div class="withdraw-page">
                    <!-- Withdraw Header -->
                    <div class="withdraw-header">
                        <h1 class="withdraw-title">Get Money</h1>
                    </div>

                    <!-- Main Content Grid -->
                    <div class="withdraw-content-grid">
                        <!-- Left Panel - Withdraw Form -->
                        <div class="withdraw-form-section">
                            <!-- Payment Method Selection -->
                            <div class="withdraw-section-card">
                                <h2 class="withdraw-section-title">Selected Payment Method</h2>
                                <div class="withdraw-payment-methods">
                                    <?php
                                    $gateways = mysqli_query($conn, "SELECT * FROM payment_gateways WHERE is_active = 1 AND is_withdrawal = 1 ORDER BY sort_order ASC");
                                    if ($gateways && mysqli_num_rows($gateways) > 0):
                                        while ($g = mysqli_fetch_assoc($gateways)):
                                    ?>
                                    <div class="withdraw-payment-method"
                                         data-method-id="<?php echo $g['id']; ?>"
                                         data-method-name="<?php echo htmlspecialchars($g['name']); ?>"
                                         data-method-type="<?php echo $g['type']; ?>"
                                         data-min-withdrawal="<?php echo $g['min_withdrawal']; ?>"
                                         data-max-withdrawal="<?php echo $g['max_withdrawal']; ?>">
                                        <div class="withdraw-payment-icon">
                                            <?php if (!empty($g['image'])): ?>
                                                <img src="../../<?php echo htmlspecialchars($g['image']); ?>" alt="<?php echo htmlspecialchars($g['name']); ?>">
                                            <?php else: ?>
                                                <i class="fas fa-wallet fa-2x text-primary"></i>
                                            <?php endif; ?>
                                        </div>
                                        <p class="withdraw-payment-name"><?php echo htmlspecialchars($g['name']); ?></p>
                                    </div>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                        <p class="text-muted p-3">No active withdrawal methods found. Please configure them in the Admin Panel.</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Withdraw Amount -->
                            <div class="withdraw-section-card withdraw-amount-section" style="display: none;">
                                <h2 class="withdraw-section-title">Withdraw Amount</h2>

                                <!-- Preset Amount Buttons -->
                                <div class="withdraw-preset-amounts">
                                    <button type="button" class="withdraw-preset-btn" data-amount="5">$5</button>
                                    <button type="button" class="withdraw-preset-btn" data-amount="10">$10</button>
                                    <button type="button" class="withdraw-preset-btn" data-amount="20">$20</button>
                                </div>

                                <!-- Withdraw Amount Input -->
                                <div class="withdraw-amount-wrapper">
                                    <span class="withdraw-amount-symbol">$</span>
                                    <input type="number" 
                                           class="withdraw-amount-input" 
                                           id="withdraw-amount-input" 
                                           placeholder="0.00" 
                                           step="0.01">
                                </div>

                                <!-- PKR Amount Display -->
                                <div class="withdraw-pkr-amount" id="withdraw-pkr-amount" style="display: none;">
                                    <span id="withdraw-pkr-amount-text"></span>
                                </div>

                                <!-- Limit Info -->
                                <div id="withdraw-limit-info" style="display: none; font-size: 0.85rem; color: #6b7280; margin-top: 0.5rem; text-align: center;">
                                </div>

                                <!-- Insufficient Balance Message -->
                                <div id="withdraw-insufficient-balance-message" style="display: none; font-size: 0.85rem; color: #ef4444; margin-top: 0.5rem; text-align: center;">
                                    <i class="fas fa-exclamation-triangle"></i> <span id="withdraw-insufficient-balance-text"></span>
                                </div>

                                <button class="withdraw-continue-btn" id="withdraw-continue-btn" disabled>
                                    Continue Withdrawal
                                </button>
                            </div>
                        </div>

                        <!-- Right Panel - Instructions and History -->
                        <div class="withdraw-info-section">
                            <!-- Withdrawal Instructions -->
                            <div class="withdraw-section-card">
                                <h2 class="withdraw-section-title">Withdrawal Instructions</h2>
                                <ul class="withdraw-instructions-list">
                                    <li class="withdraw-instruction-item">
                                        <span class="withdraw-instruction-bullet"></span>
                                        <span>Please ensure your account details are correct before submitting a withdrawal request.</span>
                                    </li>
                                    <li class="withdraw-instruction-item">
                                        <span class="withdraw-instruction-bullet"></span>
                                        <span>Withdrawal requests are processed within 24-48 hours during business days.</span>
                                    </li>
                                    <li class="withdraw-instruction-item">
                                        <span class="withdraw-instruction-bullet"></span>
                                        <span>Note: Don't cancel the withdrawal request after submission.</span>
                                    </li>
                                    <li class="withdraw-instruction-item">
                                        <span class="withdraw-instruction-bullet"></span>
                                        <span id="withdraw-min-amount-text">Minimum withdrawal amount varies by payment method.</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Withdrawal History -->
                            <div class="withdraw-section-card withdraw-history-card">
                                <div class="withdraw-history-header">
                                    <h2 class="withdraw-section-title">Withdrawal History</h2>
                                    <div class="withdraw-history-filters">
                                        <div class="withdraw-filter-icon">
                                            <i class="fas fa-filter"></i>
                                        </div>
                                        <select class="withdraw-filter-dropdown" id="withdraw-date-filter">
                                            <option value="3">3 Days</option>
                                            <option value="7">7 Days</option>
                                            <option value="30">30 Days</option>
                                            <option value="all" selected>All Time</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="withdraw-transactions-list" id="withdraw-transactions-list">
                                    <?php 
                                    $history = mysqli_query($conn, "SELECT * FROM withdrawals WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT 10");
                                    if($history && mysqli_num_rows($history) > 0):
                                        while($h = mysqli_fetch_assoc($history)):
                                    ?>
                                    <div class="withdraw-transaction-card" 
                                         data-date="<?php echo strtotime($h['created_at']); ?>" 
                                         data-amount="<?php echo $h['amount']; ?>" 
                                         data-transaction-id="<?php echo $h['id']; ?>"
                                         style="display: flex; justify-content: space-between; padding: 12px 15px; border-bottom: 1px solid rgba(255,255,255,0.05); align-items: center;">
                                        <div>
                                            <div style="font-weight: 600; color: #fff;"><?php echo htmlspecialchars($h['method'] ?: 'Withdrawal'); ?></div>
                                            <div style="font-size: 0.8rem; color: rgba(255,255,255,0.4); margin-top: 3px;">
                                                <?php echo date('d M, Y h:i A', strtotime($h['created_at'])); ?><br>
                                                <small style="color: rgba(255,255,255,0.3);">A/C: <?php echo htmlspecialchars($h['account_number']); ?></small>
                                            </div>
                                        </div>
                                        <div style="text-align: right;">
                                            <div style="font-weight: 700; color: #f87171;">-$<?php echo number_format($h['amount'], 2); ?></div>
                                            <span style="display: inline-block; font-size: 0.75rem; padding: 3px 8px; border-radius: 20px; font-weight: 500; margin-top: 5px;
                                                <?php echo ($h['status'] == 'approved') ? 'background: rgba(16, 185, 129, 0.15); color: #10b981;' : (($h['status'] == 'rejected') ? 'background: rgba(239, 68, 68, 0.15); color: #ef4444;' : 'background: rgba(245, 158, 11, 0.15); color: #f59e0b;'); ?>">
                                                <?php echo ucfirst($h['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                        <div class="p-3 text-center text-muted" id="withdraw-history-empty">No transaction history found!</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

<?php include('../../components/layout_bottom.php'); ?>
