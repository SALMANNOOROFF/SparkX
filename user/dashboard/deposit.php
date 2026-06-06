<?php 
    require_once '../../includes/auth_check.php';
    $title = "Sparkx - Deposit";
    $base_url = "../..";

    // Query global deposit instructions from settings table
    $settings_query = mysqli_query($conn, "SELECT value FROM settings WHERE name = 'deposit_instructions'");
    $settings_row = mysqli_fetch_assoc($settings_query);
    $deposit_instructions = $settings_row['value'] ?? '';

    $meta_tags = '<meta name="conversion-rate" content="280">';
    include('../../components/layout_top.php'); 
?>

                <div class="deposit-page">
                    <!-- Deposit Header -->
                    <div class="deposit-header">
                        <h1 class="deposit-title">Deposit</h1>
                    </div>

                    <!-- Main Content Grid -->
                    <div class="deposit-content-grid">
                        <!-- Left Panel - Deposit Form -->
                        <div class="deposit-form-section">
                            <!-- Payment Method Selection -->
                            <style>
                            .deposit-payment-method.disabled-gateway {
                                opacity: 0.65;
                                border: 1.5px dashed rgba(234, 88, 12, 0.4) !important;
                                background: rgba(234, 88, 12, 0.05) !important;
                                position: relative;
                            }
                            .deposit-payment-method.disabled-gateway::after {
                                content: 'OFFLINE';
                                position: absolute;
                                top: 5px;
                                right: 5px;
                                background: #ef4444;
                                color: white;
                                font-size: 0.65rem;
                                font-weight: bold;
                                padding: 2px 6px;
                                border-radius: 4px;
                                letter-spacing: 0.5px;
                            }
                            </style>
                            <div class="deposit-section-card">
                                <h2 class="deposit-section-title">Selected Payment Method</h2>
                                <div class="deposit-payment-methods">
                                    <?php
                                    $gateways = mysqli_query($conn, "SELECT * FROM payment_gateways WHERE is_deposit = 1 ORDER BY sort_order ASC");
                                    if ($gateways && mysqli_num_rows($gateways) > 0):
                                        while ($g = mysqli_fetch_assoc($gateways)):
                                            $gateway_status_class = ($g['is_active'] == 0) ? 'disabled-gateway' : '';
                                    ?>
                                    <div class="deposit-payment-method <?php echo $gateway_status_class; ?>" 
                                         data-method-id="<?php echo $g['id']; ?>" 
                                         data-method-name="<?php echo htmlspecialchars($g['name']); ?>" 
                                         data-method-type="<?php echo $g['type']; ?>" 
                                         data-min-deposit="<?php echo $g['min_deposit']; ?>" 
                                         data-max-deposit="<?php echo $g['max_deposit']; ?>"
                                         data-is-active="<?php echo $g['is_active']; ?>">
                                        <div class="deposit-payment-icon">
                                            <?php if (!empty($g['image'])): ?>
                                                <img src="../../<?php echo htmlspecialchars($g['image']); ?>" alt="<?php echo htmlspecialchars($g['name']); ?>">
                                            <?php else: ?>
                                                <i class="fas fa-wallet fa-2x text-primary"></i>
                                            <?php endif; ?>
                                        </div>
                                        <p class="deposit-payment-name"><?php echo htmlspecialchars($g['name']); ?></p>
                                    </div>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                        <p class="text-muted p-3">No active payment methods found. Please configure them in the Admin Panel.</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Deposit Amount -->
                            <div class="deposit-section-card deposit-amount-section">
                                <h2 class="deposit-section-title">Deposit Amount</h2>

                                <!-- Preset Amount Buttons -->
                                <div class="deposit-preset-amounts">
                                    <button type="button" class="deposit-preset-btn" data-amount="3">$3</button>
                                    <button type="button" class="deposit-preset-btn" data-amount="30">$30</button>
                                    <button type="button" class="deposit-preset-btn" data-amount="50">$50</button>
                                    <button type="button" class="deposit-preset-btn" data-amount="100">$100</button>
                                    <button type="button" class="deposit-preset-btn" data-amount="250">$250</button>
                                    <button type="button" class="deposit-preset-btn" data-amount="500">$500</button>
                                </div>

                                <!-- Custom Amount Input -->
                                <div class="deposit-amount-wrapper">
                                    <span class="deposit-amount-symbol">$</span>
                                    <input type="number" class="deposit-amount-input" id="deposit-amount-input" placeholder="Enter custom amount" min="2">
                                </div>
                                <!-- PKR Amount Display -->
                                <div class="deposit-pkr-amount" id="deposit-pkr-amount" style="display: none;">
                                    <span id="pkr-amount-text"></span>
                                </div>
                                <button class="deposit-continue-btn" id="deposit-continue-btn">
                                    Continue Deposit
                                </button>
                            </div>
                        </div>

                        <!-- Right Panel - Instructions and History -->
                        <div class="deposit-info-section">
                            <!-- Deposit Instructions -->
                            <div class="deposit-section-card">
                                <h2 class="deposit-section-title">Deposit Instructions</h2>
                                <ul class="deposit-instructions-list">
                                    <?php if (!empty($deposit_instructions)): 
                                        $lines = explode("\n", str_replace("\r", "", $deposit_instructions));
                                        foreach ($lines as $line):
                                            $line = trim($line);
                                            if ($line === '') continue;
                                    ?>
                                        <li class="deposit-instruction-item">
                                            <span class="deposit-instruction-bullet"></span>
                                            <span><?php echo htmlspecialchars($line); ?></span>
                                        </li>
                                    <?php 
                                        endforeach;
                                    else: 
                                    ?>
                                        <li class="deposit-instruction-item">
                                            <span class="deposit-instruction-bullet"></span>
                                            <span>If the transfer time is up, please fill out the deposit form again.</span>
                                        </li>
                                        <li class="deposit-instruction-item">
                                            <span class="deposit-instruction-bullet"></span>
                                            <span>The amount you send must be the same as your order.</span>
                                        </li>
                                        <li class="deposit-instruction-item">
                                            <span class="deposit-instruction-bullet"></span>
                                            <span>Note: Don't cancel the deposit after sending the money.</span>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <li class="deposit-instruction-item">
                                        <span class="deposit-instruction-bullet"></span>
                                        <span id="deposit-min-amount-text">Minimum deposit is $3</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Deposit History -->
                            <div class="deposit-section-card deposit-history-card">
                                <div class="deposit-history-header">
                                    <h2 class="deposit-section-title">Deposit History</h2>
                                    <div class="deposit-search-bar">
                                        <div class="deposit-search-wrapper">
                                            <i class="fas fa-search deposit-search-icon"></i>
                                            <input type="text" class="deposit-search-input" id="deposit-search-input" placeholder="Search transactions...">
                                            <button class="deposit-search-filter-btn" type="button">
                                                <i class="fas fa-filter"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="deposit-history-filters">
                                        <div class="deposit-filter-icon">
                                            <i class="fas fa-filter"></i>
                                        </div>
                                        <select class="deposit-filter-dropdown" id="deposit-date-filter">
                                            <option value="3">3 Days</option>
                                            <option value="7">7 Days</option>
                                            <option value="30">30 Days</option>
                                            <option value="all" selected>All Time</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="deposit-transactions-list" id="deposit-transactions-list">
                                    <?php 
                                    $history = mysqli_query($conn, "SELECT * FROM deposits WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT 10");
                                    if($history && mysqli_num_rows($history) > 0):
                                        while($h = mysqli_fetch_assoc($history)):
                                    ?>
                                    <div class="deposit-transaction-item" style="display: flex; justify-content: space-between; padding: 10px; border-bottom: 1px solid #eee;">
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($h['method'] ?: 'Manual'); ?></div>
                                            <div class="small text-muted"><?php echo date('d M, Y h:i A', strtotime($h['created_at'])); ?></div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold">$<?php echo number_format($h['amount'], 2); ?></div>
                                            <span class="badge bg-<?php echo ($h['status'] == 'approved') ? 'success' : (($h['status'] == 'rejected') ? 'danger' : 'warning'); ?>">
                                                <?php echo ucfirst($h['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php endwhile; else: ?>
                                    <div class="p-3 text-center text-muted" id="deposit-history-empty">No transaction history found!</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advance Search Modal -->
                <div class="deposit-advance-search-modal" id="deposit-advance-search-modal">
                    <div class="deposit-advance-search-content">
                        <div class="deposit-advance-search-header">
                            <h3 class="deposit-advance-search-title">Advance Search</h3>
                            <button class="deposit-advance-search-close" id="deposit-advance-search-close">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="deposit-advance-search-form">
                            <div class="deposit-advance-search-field">
                                <label class="deposit-advance-search-label">Select start & end date:</label>
                                <div class="deposit-advance-search-date-wrapper">
                                    <i class="fas fa-calendar deposit-advance-search-date-icon"></i>
                                    <input type="text" class="deposit-advance-search-date-input" id="deposit-date-range-input" placeholder="dd/mm/yyyy - dd/mm/yyyy" readonly>
                                    <input type="date" id="deposit-start-date" style="position: absolute; opacity: 0; width: 1px; height: 1px; pointer-events: none;">
                                    <input type="date" id="deposit-end-date" style="position: absolute; opacity: 0; width: 1px; height: 1px; pointer-events: none;">
                                </div>
                            </div>
                            <div class="deposit-advance-search-field">
                                <label class="deposit-advance-search-label">Sort:</label>
                                <div class="deposit-advance-search-sort-wrapper">
                                    <select class="deposit-advance-search-sort" id="deposit-advance-sort">
                                        <option value="newest">Newest</option>
                                        <option value="oldest">Oldest</option>
                                        <option value="amount-high">Amount: High to Low</option>
                                        <option value="amount-low">Amount: Low to High</option>
                                    </select>
                                </div>
                            </div>
                            <div class="deposit-advance-search-buttons">
                                <button type="button" class="deposit-advance-search-apply" id="deposit-advance-apply">
                                    Apply Filters
                                </button>
                                <button type="button" class="deposit-advance-search-clear" id="deposit-advance-clear">
                                    Clear Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedMethod = null;
    const continueBtn = document.getElementById('deposit-continue-btn');
    const amountInput = document.getElementById('deposit-amount-input');
    const minAmountText = document.getElementById('deposit-min-amount-text');

    document.querySelectorAll('.deposit-payment-method').forEach(method => {
        method.addEventListener('click', function() {
            if (this.dataset.isActive === '0') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Gateway Offline',
                        text: `${this.dataset.methodName} is temporarily offline. Please select another method or check back later!`,
                        icon: 'info',
                        confirmButtonText: 'Understood',
                        confirmButtonColor: '#ea580c',
                        background: '#1e1b4b',
                        color: '#fff'
                    });
                } else {
                    alert(`${this.dataset.methodName} is temporarily offline.`);
                }
                return;
            }
            
            document.querySelectorAll('.deposit-payment-method').forEach(m => m.classList.remove('active'));
            this.classList.add('active');
            selectedMethod = {
                id: this.dataset.methodId,
                name: this.dataset.methodName,
                min: parseFloat(this.dataset.minDeposit),
                max: parseFloat(this.dataset.maxDeposit)
            };
            minAmountText.innerText = `Minimum deposit is $${selectedMethod.min}`;
            
            // Show the amount selection and continue controls
            const amountSection = document.querySelector('.deposit-amount-section');
            if (amountSection) {
                amountSection.classList.add('show');
            }
        });
    });

    document.querySelectorAll('.deposit-preset-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            amountInput.value = btn.dataset.amount;
        });
    });

    continueBtn.addEventListener('click', function() {
        if (!selectedMethod) {
            alert('Please select a payment method.');
            return;
        }
        const amount = parseFloat(amountInput.value);
        if (isNaN(amount) || amount < selectedMethod.min || amount > selectedMethod.max) {
            alert(`Please enter a valid amount between $${selectedMethod.min} and $${selectedMethod.max}.`);
            return;
        }

        // Redirect to a payment submission page (e.g., submit_deposit.php)
        window.location.href = `submit_deposit.php?method_id=${selectedMethod.id}&amount=${amount}`;
    });
});
</script>

<?php include('../../components/layout_bottom.php'); ?>
