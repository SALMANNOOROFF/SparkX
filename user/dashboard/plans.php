<?php 
    $title = "Sparkx - Investment Plans";
    $base_url = "../..";

    require_once '../../includes/auth_check.php';
    include('../../components/layout_top.php'); 

    // Fetch dynamic stats across active plans for the Hero section
    $stat_q = mysqli_query($conn, "SELECT MIN(min_investment) as min_inv, MIN(daily_roi_min) as min_roi, MAX(daily_roi_max) as max_roi FROM plans WHERE status = 'active'");
    $stat_data = mysqli_fetch_assoc($stat_q);
    $hero_min_inv = $stat_data['min_inv'] ?? 3.00;
    $hero_min_roi = $stat_data['min_roi'] ?? 4.50;
    $hero_max_roi = $stat_data['max_roi'] ?? 5.50;

    // Fetch user's active investments
    $active_investments = [];
    $total_active_investment = 0;
    $active_q = mysqli_query($conn, "SELECT plan_id, SUM(amount) as plan_total FROM investments WHERE user_id = '$user_id' AND status = 'active' GROUP BY plan_id");
    if ($active_q) {
        while ($row = mysqli_fetch_assoc($active_q)) {
            $active_investments[$row['plan_id']] = $row['plan_total'];
            $total_active_investment += $row['plan_total'];
        }
    }
?>

<style>
/* Glowing border and animations for selected active plans */
.plan-active-selected-card {
    border: 2px solid #ea580c !important;
    box-shadow: 0 0 25px rgba(234, 88, 12, 0.2) !important;
    transform: translateY(-4px) !important;
    background: linear-gradient(180deg, rgba(234, 88, 12, 0.02), rgba(0, 0, 0, 0)) !important;
}

.plan-btn-active-more {
    background: linear-gradient(135deg, #f97316, #ea580c) !important;
    border-color: #ea580c !important;
    box-shadow: 0 4px 15px rgba(234, 88, 12, 0.25) !important;
}

.plan-btn-active-more:hover {
    background: linear-gradient(135deg, #ea580c, #c2410c) !important;
    box-shadow: 0 6px 20px rgba(234, 88, 12, 0.4) !important;
}

@keyframes pulse-success {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

.active-badge {
    animation: pulse-success 3s infinite ease-in-out;
}

/* Custom Scrollbar Styles for Investment Modal */
.investment-modal {
    overflow-y: hidden !important; /* Remove outer scrollbar entirely */
}

/* Hide scrollbar for Webkit browsers (Chrome, Safari, Opera) */
.investment-modal::-webkit-scrollbar,
.investment-modal-body::-webkit-scrollbar {
    display: none !important;
}

/* Hide scrollbar for Firefox, IE, and Edge */
.investment-modal,
.investment-modal-body {
    -ms-overflow-style: none !important; /* IE and Edge */
    scrollbar-width: none !important; /* Firefox */
}

/* Success SweetAlert matching Deposit Success screen */
.investment-success-swal {
    background: rgba(30, 41, 59, 0.95) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 24px !important;
    padding: 35px !important;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35) !important;
    color: #fff !important;
    font-family: 'Poppins', sans-serif !important;
    max-width: 500px !important;
}

.success-screen-swal {
    text-align: center;
    padding: 10px 0;
}

.success-icon-wrapper-swal {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    width: 90px;
    height: 90px;
    background: rgba(16, 185, 129, 0.1);
    border: 2px solid #10b981;
    border-radius: 50%;
    color: #10b981;
    font-size: 2.75rem;
    margin-bottom: 25px;
    animation: scaleInSwal 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes scaleInSwal {
    0% { transform: scale(0); opacity: 0; }
    70% { transform: scale(1.1); }
    100% { transform: scale(1); opacity: 1; }
}

.success-title-swal {
    font-size: 1.8rem;
    font-weight: 700;
    color: #10b981;
    margin-bottom: 12px;
}

.success-desc-swal {
    color: rgba(255, 255, 255, 0.65);
    font-size: 0.95rem;
    margin-bottom: 30px;
    line-height: 1.6;
}

.btn-dashboard-swal {
    display: inline-block;
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.12);
    padding: 14px 28px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
    width: 100%;
    box-sizing: border-box;
}

.btn-dashboard-swal:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.25);
    color: #fff;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<div class="plans-new-page">
    <!-- Hero Section -->
    <div class="plans-hero-new">
        <div class="plans-hero-content-new">
            <h1 class="plans-hero-title-new">Investment Plans</h1>
            <p class="plans-hero-subtitle-new">Choose your Investment plan and start earning rewards with automated 24/7 Earning</p>
            <div class="plans-hero-stats-new">
                <div class="plans-hero-stat-new">
                    <div class="plans-hero-stat-label-new">Daily ROI</div>
                    <div class="plans-hero-stat-value-new"><?php echo number_format($hero_min_roi, 1); ?>%/<?php echo number_format($hero_max_roi, 1); ?>%</div>
                </div>
                <div class="plans-hero-stat-new">
                    <div class="plans-hero-stat-label-new">Minimum Investment</div>
                    <div class="plans-hero-stat-value-new">$<?php echo number_format($hero_min_inv, 0); ?></div>
                </div>
                <div class="plans-hero-stat-new">
                    <div class="plans-hero-stat-label-new">Active Investment</div>
                    <div class="plans-hero-stat-value-new">$<?php echo number_format($total_active_investment, 2); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Plan Cards Loop -->
    <?php 
    $plans_query = mysqli_query($conn, "SELECT * FROM plans WHERE status = 'active' ORDER BY min_investment ASC");
    while($plan = mysqli_fetch_assoc($plans_query)): 
        $isActive = isset($active_investments[$plan['id']]);
        $active_amount = $isActive ? $active_investments[$plan['id']] : 0;

        // Pre-calculate earnings based on reference amount ($100 or min investment if higher)
        $ref_amount = 100.00;
        if ($ref_amount < $plan['min_investment']) {
            $ref_amount = $plan['min_investment'];
        }
        if ($ref_amount > $plan['max_investment']) {
            $ref_amount = $plan['max_investment'];
        }
        
        $avg_roi = ($plan['daily_roi_min'] + $plan['daily_roi_max']) / 2;
        $daily_est = $ref_amount * ($avg_roi / 100);
        $monthly_est = $daily_est * 30;
    ?>
    <div class="plan-main-card-new <?php echo $isActive ? 'plan-active-selected-card' : ''; ?>" data-plan-id="<?php echo $plan['id']; ?>">
        <div class="plan-card-content-new">
            <div class="plan-header-new">
                <div class="plan-header-left-new">
                    <div class="plan-icon-large-new">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="plan-title-section-new">
                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.75rem;">
                            <?php if ($isActive): ?>
                                <div class="plan-badge-new active-badge" style="background: linear-gradient(135deg, #f97316, #ea580c); color: white; border: none; box-shadow: 0 0 10px rgba(234, 88, 12, 0.35);">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Currently Running ($<?php echo number_format($active_amount, 2); ?>)</span>
                                </div>
                            <?php else: ?>
                                <div class="plan-badge-new">
                                    <i class="fas fa-star"></i>
                                    <span>Mining Plan</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h2 class="plan-name-new"><?php echo htmlspecialchars($plan['name']); ?></h2>
                        <p class="plan-tagline-new">Premium <?php echo htmlspecialchars($plan['name']); ?> Mining Plan for High Returns</p>
                    </div>
                </div>
            </div>

            <!-- Mobile Layout -->
            <div class="plan-mobile-layout-new">
                <div class="plan-mobile-header-new">
                    <div class="plan-mobile-icon-wrapper-new">
                        <div class="plan-icon-large-new">
                            <i class="fas fa-layer-group"></i>
                        </div>
                    </div>
                    <div class="plan-mobile-title-section-new">
                        <h2 class="plan-mobile-name-new"><?php echo htmlspecialchars($plan['name']); ?></h2>
                        <p class="plan-mobile-subtitle-new">Earn through <?php echo htmlspecialchars($plan['name']); ?> plan</p>
                    </div>
                </div>
                <div class="plan-mobile-policy-new">
                    Principal Return Policy Will Be Returned
                </div>
                <div class="plan-mobile-details-new">
                    <div class="plan-mobile-detail-col-new">
                        <div class="plan-mobile-detail-label-new">Range</div>
                        <div class="plan-mobile-detail-value-new">$<?php echo number_format($plan['min_investment']); ?> - $<?php echo number_format($plan['max_investment']); ?></div>
                    </div>
                    <div class="plan-mobile-detail-col-new">
                        <div class="plan-mobile-detail-label-new">ROI <?php echo number_format($plan['daily_roi_min'], 1); ?>% - <?php echo number_format($plan['daily_roi_max'], 1); ?>% Daily</div>
                        <div class="plan-mobile-detail-value-new"><?php echo number_format($plan['hourly_rate'], 4); ?>% / Hourly</div>
                    </div>
                </div>
            </div>

            <!-- Desktop Layout -->
            <div class="plan-desktop-layout-new">
                <div class="plan-security-badge-new">
                    <div class="plan-security-icon-new">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="plan-security-text-new">
                        <div class="plan-security-title-new">Principal Return Guarantee</div>
                        <div class="plan-security-desc-new">Your initial investment will be returned at the end of the plan period</div>
                    </div>
                </div>
                <div class="plan-features-grid-new">
                    <div class="plan-feature-card-new">
                        <div class="plan-feature-icon-new">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="plan-feature-label-new">Investment Range</div>
                        <div class="plan-feature-value-new">$<?php echo number_format($plan['min_investment']); ?> - $<?php echo number_format($plan['max_investment']); ?></div>
                        <div class="plan-feature-hint-new">Minimum investment: $<?php echo number_format($plan['min_investment']); ?></div>
                    </div>
                    <div class="plan-feature-card-new highlight">
                        <div class="plan-feature-icon-new">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="plan-feature-label-new">Daily ROI</div>
                        <div class="plan-feature-value-new"><?php echo number_format($plan['daily_roi_min'], 1); ?>% - <?php echo number_format($plan['daily_roi_max'], 1); ?>%</div>
                        <div class="plan-feature-hint-new">Fixed daily returns guaranteed</div>
                    </div>
                    <div class="plan-feature-card-new">
                        <div class="plan-feature-icon-new">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="plan-feature-label-new">Hourly Rate</div>
                        <div class="plan-feature-value-new"><?php echo number_format($plan['hourly_rate'], 4); ?>%</div>
                        <div class="plan-feature-hint-new">Per hour earnings</div>
                    </div>
                </div>
            </div>

            <!-- In-card Accordion Calculator Section -->
            <div class="plan-calculator-section-new" id="calculatorSection<?php echo $plan['id']; ?>">
                <div class="plan-calculator-header-new">
                    <h3 class="plan-calculator-title-new">Estimated Returns</h3>
                    <button class="plan-calculator-toggle-new calculator-toggle" data-plan-id="<?php echo $plan['id']; ?>">
                        <i class="fas fa-calculator"></i>
                        <span>Open Calculator</span>
                    </button>
                </div>
                <div class="plan-calculator-grid-new" id="calculatorContent<?php echo $plan['id']; ?>" style="display: none;">
                    <div class="plan-calculator-item-new">
                        <div class="plan-calculator-label-new">Daily Earnings</div>
                        <div class="plan-calculator-value-new" style="color: #10b981; font-weight: 700;">$<?php echo number_format($daily_est, 2); ?></div>
                        <div class="plan-calculator-note-new">Based on $<?php echo number_format($ref_amount, 0); ?> investment</div>
                    </div>
                    <div class="plan-calculator-item-new">
                        <div class="plan-calculator-label-new">Monthly Earnings</div>
                        <div class="plan-calculator-value-new" style="color: #10b981; font-weight: 700;">$<?php echo number_format($monthly_est, 2); ?></div>
                        <div class="plan-calculator-note-new">30 days projection</div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="plan-actions-new">
                <button class="plan-action-btn-new plan-action-primary-new start-investing-btn <?php echo $isActive ? 'plan-btn-active-more' : ''; ?>" 
                        data-plan-id="<?php echo $plan['id']; ?>"
                        data-plan-name="<?php echo htmlspecialchars($plan['name']); ?>"
                        data-min="<?php echo $plan['min_investment']; ?>"
                        data-max="<?php echo $plan['max_investment']; ?>">
                    <i class="fas <?php echo $isActive ? 'fa-check-double' : 'fa-rocket'; ?>"></i>
                    <span><?php echo $isActive ? 'Invest More' : 'Start Investing'; ?></span>
                </button>
                <button class="plan-action-btn-new plan-action-secondary-new open-calculator-btn" 
                        data-plan-id="<?php echo $plan['id']; ?>" 
                        data-plan-name="<?php echo htmlspecialchars($plan['name']); ?>" 
                        data-plan-subtitle="Earn through <?php echo htmlspecialchars($plan['name']); ?> plan"
                        data-min-investment="<?php echo $plan['min_investment']; ?>" 
                        data-max-investment="<?php echo $plan['max_investment']; ?>" 
                        data-daily-roi-min="<?php echo $plan['daily_roi_min']; ?>" 
                        data-daily-roi-max="<?php echo $plan['daily_roi_max']; ?>" 
                        data-hourly-rate="<?php echo $plan['hourly_rate']; ?>">
                    <i class="fas fa-calculator"></i>
                    <span>Investment Calculator</span>
                </button>
            </div>

            <!-- Benefits Section -->
            <div class="plan-benefits-new plan-benefits-desktop-new">
                <div class="plan-benefit-item-new">
                    <div class="plan-benefit-icon-new">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="plan-benefit-text-new">24/7 automated mining</div>
                </div>
                <div class="plan-benefit-item-new">
                    <div class="plan-benefit-icon-new">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="plan-benefit-text-new">Instant withdrawals</div>
                </div>
                <div class="plan-benefit-item-new">
                    <div class="plan-benefit-icon-new">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="plan-benefit-text-new">Principal protection</div>
                </div>
                <div class="plan-benefit-item-new">
                    <div class="plan-benefit-icon-new">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="plan-benefit-text-new">Real-time mining tracking</div>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div> <!-- plans-new-page -->

<!-- Calculator Modal -->
<div class="calculator-modal-overlay" id="calculatorModalOverlay">
    <div class="calculator-modal" style="background: #fff; border-radius: 16px; width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
        <div class="calculator-modal-header" style="padding: 1.25rem; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center;">
            <h3 class="calculator-modal-title" id="calculatorModalTitle" style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #1e1e2d;">Investment Profit Calculator</h3>
            <button class="calculator-modal-close" id="closeCalculatorModal" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #a1a5b7;"><i class="fas fa-times"></i></button>
        </div>
        <div class="calculator-modal-body" style="padding: 1.25rem;">
            <div class="calculator-plan-section" style="margin-bottom: 1.25rem;">
                <h4 class="calculator-plan-name" id="calculatorPlanName" style="margin: 0 0 0.25rem 0; font-weight: 700; color: var(--purple-main);">Plan Name</h4>
                <p class="calculator-plan-description" id="calculatorPlanDescription" style="margin: 0; font-size: 0.85rem; color: #7e8299;">Plan tagline</p>
            </div>
            <div class="calculator-details-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.25rem; background: #F9F8FE; padding: 1rem; border-radius: 10px;">
                <div class="calculator-detail-item">
                    <div class="calculator-detail-label" style="font-size: 0.75rem; color: #7e8299;">Hourly Rate:</div>
                    <div class="calculator-detail-value" id="calculatorReturnRate" style="font-weight: 700; color: #1e1e2d;">-</div>
                </div>
                <div class="calculator-detail-item">
                    <div class="calculator-detail-label" style="font-size: 0.75rem; color: #7e8299;">Frequency:</div>
                    <div class="calculator-detail-value" id="calculatorFrequency" style="font-weight: 700; color: #1e1e2d;">Every hour</div>
                </div>
                <div class="calculator-detail-item">
                    <div class="calculator-detail-label" style="font-size: 0.75rem; color: #7e8299;">Price Type:</div>
                    <div class="calculator-detail-value" style="font-weight: 700; color: #1e1e2d;">Range</div>
                </div>
                <div class="calculator-detail-item">
                    <div class="calculator-detail-label" style="font-size: 0.75rem; color: #7e8299;">Investment Range:</div>
                    <div class="calculator-detail-value" id="calculatorInvestmentRange" style="font-weight: 700; color: #1e1e2d;">-</div>
                </div>
            </div>
            <div class="calculator-input-section" style="margin-bottom: 1.25rem;">
                <label class="calculator-input-label" style="display: block; font-weight: 700; font-size: 0.85rem; color: #1e1e2d; margin-bottom: 0.5rem;">Investment Amount</label>
                <div class="calculator-input-wrapper" style="position: relative; display: flex; align-items: center;">
                    <span class="calculator-input-prefix" style="position: absolute; left: 1rem; font-weight: 700; color: #4b5563;">$</span>
                    <input type="number" class="calculator-input" id="investmentAmount" placeholder="Enter investment amount" step="0.01" style="width: 100%; padding: 0.75rem 1rem 0.75rem 2rem; border: 1.5px solid #e4e6ef; border-radius: 8px; font-weight: 600; outline: none; transition: border-color 0.2s;">
                </div>
            </div>
            <div class="calculator-details-card" id="investmentDetailsCard" style="display: none; background: #fff; border: 1px solid #f1f1f4; padding: 1rem; border-radius: 10px; margin-bottom: 1rem;">
                <h4 class="calculator-card-title" style="margin: 0 0 0.75rem 0; font-size: 0.9rem; font-weight: 700; color: #1e1e2d;">Investment Details</h4>
                <div class="calculator-details-list">
                    <div class="calculator-detail-row" style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 0.5rem;">
                        <span class="calculator-detail-row-label" style="color: #7e8299;">Investment Amount:</span>
                        <span class="calculator-detail-row-value" id="calculatedAmount" style="font-weight: 700; color: #1e1e2d;">$0.00</span>
                    </div>
                    <div class="calculator-detail-row" style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 0.5rem;">
                        <span class="calculator-detail-row-label" style="color: #7e8299;">Return Rate:</span>
                        <span class="calculator-detail-row-value" id="calculatorReturnRateDetail" style="font-weight: 700; color: #1e1e2d;">-</span>
                    </div>
                    <div class="calculator-detail-row" style="display: flex; justify-content: space-between; font-size: 0.85rem;">
                        <span class="calculator-detail-row-label" style="color: #7e8299;">Profit Per Hour:</span>
                        <span class="calculator-detail-row-value calculator-profit-value" id="profitPerCycle" style="font-weight: 700; color: #10b981;">$0.00</span>
                    </div>
                </div>
            </div>
            <div class="calculator-details-card" id="profitBreakdownCard" style="display: none; background: #F9F8FE; padding: 1rem; border-radius: 10px;">
                <h4 class="calculator-card-title" style="margin: 0 0 0.75rem 0; font-size: 0.9rem; font-weight: 700; color: var(--purple-main);">
                    <i class="fas fa-arrow-trend-up calculator-trend-icon"></i>
                    Profit Breakdown by Time Period
                </h4>
                <div class="calculator-profit-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div class="calculator-profit-item" style="display: flex; flex-direction: column; background: #fff; padding: 0.5rem; border-radius: 6px; border: 1px solid #f1f1f4;">
                        <span class="calculator-profit-label" style="font-size: 0.75rem; color: #7e8299;">Hourly:</span>
                        <span class="calculator-profit-value" id="profitHourly" style="font-weight: 700; color: #10b981;">$0.00</span>
                    </div>
                    <div class="calculator-profit-item" style="display: flex; flex-direction: column; background: #fff; padding: 0.5rem; border-radius: 6px; border: 1px solid #f1f1f4;">
                        <span class="calculator-profit-label" style="font-size: 0.75rem; color: #7e8299;">Daily:</span>
                        <span class="calculator-profit-value" id="profitDaily" style="font-weight: 700; color: #10b981;">$0.00</span>
                    </div>
                    <div class="calculator-profit-item" style="display: flex; flex-direction: column; background: #fff; padding: 0.5rem; border-radius: 6px; border: 1px solid #f1f1f4;">
                        <span class="calculator-profit-label" style="font-size: 0.75rem; color: #7e8299;">Weekly:</span>
                        <span class="calculator-profit-value" id="profitWeekly" style="font-weight: 700; color: #10b981;">$0.00</span>
                    </div>
                    <div class="calculator-profit-item" style="display: flex; flex-direction: column; background: #fff; padding: 0.5rem; border-radius: 6px; border: 1px solid #f1f1f4;">
                        <span class="calculator-profit-label" style="font-size: 0.75rem; color: #7e8299;">Monthly:</span>
                        <span class="calculator-profit-value" id="profitMonthly" style="font-weight: 700; color: #10b981;">$0.00</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="calculator-modal-footer" style="padding: 1rem 1.25rem; border-top: 1px solid #f0f0f0; display: flex; justify-content: flex-end; gap: 0.5rem; background: #fafafa;">
            <button class="calculator-btn calculator-btn-reset" id="resetCalculator" style="background: #fff; border: 1px solid #e4e6ef; padding: 0.5rem 1.25rem; border-radius: 8px; font-weight: 600; cursor: pointer; color: #4b5563;">Reset</button>
            <button class="calculator-btn calculator-btn-close" id="closeCalculatorModalBtn" style="background: var(--purple-main); border: none; padding: 0.5rem 1.25rem; border-radius: 8px; font-weight: 600; cursor: pointer; color: #fff;">Close</button>
        </div>
    </div>
</div>
            <button class="calculator-btn calculator-btn-close" id="closeCalculatorModalBtn" style="background: #ea580c; border: none; padding: 0.5rem 1.25rem; border-radius: 8px; font-weight: 600; cursor: pointer; color: #fff;">Close</button>
        </div>
    </div>
</div>

<!-- Buy Investment Modal -->
<div class="calculator-modal-overlay" id="investmentModalOverlay" style="background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(12px); position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 1000; align-items: center; justify-content: center; padding: 1rem;">
    <div class="calculator-modal investment-modal" style="background: rgba(30, 41, 59, 0.95); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 24px; width: 100%; max-width: 500px; max-height: 90vh; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4); color: #fff; font-family: 'Poppins', sans-serif;">
        <div class="calculator-modal-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255, 255, 255, 0.08); display: flex; justify-content: space-between; align-items: center;">
            <h3 class="calculator-modal-title" style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #fff;">Buy Investment Plan: <span class="investment-plan-name-highlight" id="investmentPlanName" style="background: linear-gradient(135deg, #ff8c00, #fdba74); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 800;">-</span></h3>
            <button class="calculator-modal-close" id="closeInvestmentModal" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: rgba(255, 255, 255, 0.5); transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255, 255, 255, 0.5)'"><i class="fas fa-times"></i></button>
        </div>
        <div class="calculator-modal-body investment-modal-body" style="padding: 1.5rem;">
            <div id="investmentAlert" class="investment-alert" style="display: none; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 12px; padding: 0.75rem 1rem; color: #ef4444; font-size: 0.85rem; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem;">
                <i class="fas fa-exclamation-triangle"></i>
                <span id="investmentAlertMessage"></span>
                <button id="depositAmountBtn" class="investment-deposit-btn" style="margin-left: auto; background: #ef4444; color: #fff; border: none; padding: 4px 12px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.8rem; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">Deposit Amount</button>
            </div>
            <div class="investment-plan-name-section" style="margin-bottom: 0.75rem; text-align: center;">
                <h2 class="investment-plan-name-text" id="investmentPlanNameText" style="margin: 0; background: linear-gradient(135deg, #ff8c00, #fdba74); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 800; font-size: 1.75rem;">-</h2>
            </div>
            <div class="investment-range-section" style="text-align: center; margin-bottom: 1.25rem;">
                <div class="investment-range-amount" style="font-size: 1.4rem; font-weight: 700; color: #ea580c;">$<span id="investmentMinAmount">0</span> - $<span id="investmentMaxAmount">0</span></div>
                <div class="investment-range-label" style="font-size: 0.8rem; color: rgba(255, 255, 255, 0.4); margin-top: 0.25rem;">Investment Limits</div>
            </div>
            <div class="investment-principal-policy" style="text-align: center; font-size: 0.8rem; font-weight: 600; color: #ff9f43; background: rgba(234, 88, 12, 0.15); border: 1px solid rgba(234, 88, 12, 0.2); padding: 6px 12px; border-radius: 20px; display: inline-block; margin: 0 auto 1.25rem auto; width: 100%;">
                Principal Return Policy Will Be Returned
            </div>
            <div class="investment-balances-section" style="margin-bottom: 1.25rem;">
                <h4 class="investment-balances-title" style="margin: 0 0 0.75rem 0; font-size: 0.85rem; font-weight: 700; color: rgba(255, 255, 255, 0.7);">Current amount in deposit and earning wallet</h4>
                <div class="investment-balances-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div class="investment-balance-card investment-balance-fund" style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255, 255, 255, 0.05); padding: 0.85rem; border-radius: 12px; display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-wallet" style="color: #ea580c; font-size: 1.25rem;"></i>
                        <div class="investment-balance-info">
                            <div class="investment-balance-label" style="font-size: 0.7rem; color: rgba(255, 255, 255, 0.4);">Deposit Balance</div>
                            <div class="investment-balance-amount" id="fundBalanceDisplay" style="font-weight: 700; color: #f8fafc; font-size: 0.95rem;">$0.00</div>
                        </div>
                    </div>
                    <div class="investment-balance-card investment-balance-earning" style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255, 255, 255, 0.05); padding: 0.85rem; border-radius: 12px; display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-wallet" style="color: #f97316; font-size: 1.25rem;"></i>
                        <div class="investment-balance-info">
                            <div class="investment-balance-label" style="font-size: 0.7rem; color: rgba(255, 255, 255, 0.4);">Earning Balance</div>
                            <div class="investment-balance-amount" id="earningBalanceDisplay" style="font-weight: 700; color: #f8fafc; font-size: 0.95rem;">$0.00</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="investment-pay-from-section" style="margin-bottom: 1.25rem;">
                <label class="investment-pay-from-label" style="display: block; font-weight: 600; font-size: 0.85rem; color: rgba(255, 255, 255, 0.7); margin-bottom: 0.5rem;">Pay from:</label>
                <div class="investment-select-wrapper" style="position: relative;">
                    <select id="sourceBalanceSelect" class="investment-select" style="width: 100%; padding: 0.85rem 1rem; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; font-weight: 600; outline: none; background: rgba(15, 23, 42, 0.7); color: #fff; cursor: pointer; transition: border-color 0.2s;" onfocus="this.style.borderColor='#ea580c'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
                        <option value="fund_wallet" style="background: #1e293b; color: #fff;">Deposit Balance</option>
                        <option value="earning_balance" style="background: #1e293b; color: #fff;">Earning Balance</option>
                    </select>
                </div>
            </div>
            <div class="investment-amount-section" style="margin-bottom: 1.25rem;">
                <label class="investment-amount-label" style="display: block; font-weight: 600; font-size: 0.85rem; color: rgba(255, 255, 255, 0.7); margin-bottom: 0.5rem;">Investment Amount</label>
                <div class="investment-input-wrapper" style="position: relative; display: flex; align-items: center;">
                    <span class="investment-input-prefix" style="position: absolute; left: 1rem; font-weight: 700; color: rgba(255, 255, 255, 0.4);">$</span>
                    <input type="number" id="investmentAmountInput" class="investment-amount-input" placeholder="Enter amount" step="0.01" min="0" style="width: 100%; padding: 0.85rem 1rem 0.85rem 2rem; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; font-weight: 600; outline: none; background: rgba(15, 23, 42, 0.7); color: #fff; transition: all 0.2s;" onfocus="this.style.borderColor='#ea580c'; this.style.boxShadow='0 0 0 3px rgba(234, 88, 12, 0.15)';" onblur="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.boxShadow='none';">
                </div>
                <div class="investment-amount-hint" id="investmentAmountHint" style="font-size: 0.75rem; color: rgba(255, 255, 255, 0.4); margin-top: 0.35rem;">Min: $0 - Max: $0</div>
            </div>
        </div>
        <div class="calculator-modal-footer investment-modal-footer" style="padding: 1.25rem 1.5rem; border-top: 1px solid rgba(255, 255, 255, 0.08); display: flex; justify-content: flex-end; gap: 0.75rem; background: rgba(15, 23, 42, 0.3);">
            <button class="investment-btn investment-btn-cancel" id="cancelInvestmentBtn" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.12); padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 600; cursor: pointer; color: #fff; transition: all 0.2s;" onmouseover="this.style.background='rgba(255, 255, 255, 0.12)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.08)'">Cancel</button>
            <button class="investment-btn investment-btn-confirm" id="confirmInvestmentBtn" style="background: linear-gradient(135deg, #f97316, #ea580c); border: none; padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 600; cursor: pointer; color: #fff; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.2); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 8px 20px rgba(234, 88, 12, 0.4)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 12px rgba(234, 88, 12, 0.2)'"><i class="fas fa-bolt me-1"></i> Confirm Investment</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // -------------------------------------------------------------
    // ACCORDION IN-CARD CALCULATOR TOGGLE
    // -------------------------------------------------------------
    document.querySelectorAll('.calculator-toggle').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const planId = this.dataset.planId;
            const section = document.getElementById('calculatorSection' + planId);
            const content = document.getElementById('calculatorContent' + planId);
            
            const isOpen = section.classList.contains('show');
            if (isOpen) {
                section.classList.remove('show');
                content.style.display = 'none';
                this.querySelector('span').textContent = 'Open Calculator';
                this.querySelector('i').className = 'fas fa-calculator';
            } else {
                section.classList.add('show');
                content.style.display = 'grid';
                this.querySelector('span').textContent = 'Close Calculator';
                this.querySelector('i').className = 'fas fa-times';
            }
        });
    });

    // -------------------------------------------------------------
    // INTERACTIVE MODAL CALCULATOR
    // -------------------------------------------------------------
    const calculatorModalOverlay = document.getElementById('calculatorModalOverlay');
    const calcInput = document.getElementById('investmentAmount');
    const calcDetailsCard = document.getElementById('investmentDetailsCard');
    const calcBreakdownCard = document.getElementById('profitBreakdownCard');
    let activePlanData = null;

    // Open calculator modal and prefill data
    document.querySelectorAll('.open-calculator-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            activePlanData = {
                id: this.dataset.planId,
                name: this.dataset.planName,
                subtitle: this.dataset.planSubtitle,
                minInvestment: parseFloat(this.dataset.minInvestment),
                maxInvestment: parseFloat(this.dataset.maxInvestment),
                dailyRoiMin: parseFloat(this.dataset.dailyRoiMin),
                dailyRoiMax: parseFloat(this.dataset.dailyRoiMax),
                hourlyRate: parseFloat(this.dataset.hourlyRate)
            };

            // Set modal values
            document.getElementById('calculatorModalTitle').textContent = 'Investment Profit Calculator - ' + activePlanData.name;
            document.getElementById('calculatorPlanName').textContent = activePlanData.name;
            document.getElementById('calculatorPlanDescription').textContent = activePlanData.subtitle;
            document.getElementById('calculatorReturnRate').textContent = activePlanData.hourlyRate.toFixed(4) + '%';
            document.getElementById('calculatorReturnRateDetail').textContent = activePlanData.hourlyRate.toFixed(4) + '% every hour';
            document.getElementById('calculatorInvestmentRange').textContent = '$' + activePlanData.minInvestment + ' - $' + activePlanData.maxInvestment;

            calcInput.placeholder = `Enter amount between ${activePlanData.minInvestment} - ${activePlanData.maxInvestment}`;
            calcInput.value = '';
            
            calcDetailsCard.style.display = 'none';
            calcBreakdownCard.style.display = 'none';

            calculatorModalOverlay.classList.add('show');
        });
    });

    // Calculate real-time returns as the user types
    if (calcInput) {
        calcInput.addEventListener('input', function() {
            const amount = parseFloat(this.value) || 0;
            if (!activePlanData) return;

            if (amount < activePlanData.minInvestment || amount > activePlanData.maxInvestment) {
                calcDetailsCard.style.display = 'none';
                calcBreakdownCard.style.display = 'none';
                return;
            }

            // Show breakdown cards
            calcDetailsCard.style.display = 'block';
            calcBreakdownCard.style.display = 'block';

            // Math calculations based on Hourly Rate
            const rateDecimal = activePlanData.hourlyRate / 100;
            const profitHourly = amount * rateDecimal;
            const profitDaily = profitHourly * 24;
            const profitWeekly = profitDaily * 7;
            const profitMonthly = profitDaily * 30;

            // Render calculations
            document.getElementById('calculatedAmount').textContent = '$' + amount.toFixed(2);
            document.getElementById('profitPerCycle').textContent = '$' + profitHourly.toFixed(4);
            document.getElementById('profitHourly').textContent = '$' + profitHourly.toFixed(4);
            document.getElementById('profitDaily').textContent = '$' + profitDaily.toFixed(2);
            document.getElementById('profitWeekly').textContent = '$' + profitWeekly.toFixed(2);
            document.getElementById('profitMonthly').textContent = '$' + profitMonthly.toFixed(2);
        });
    }

    // Reset Calculator
    document.getElementById('resetCalculator').addEventListener('click', function(e) {
        e.preventDefault();
        calcInput.value = '';
        calcDetailsCard.style.display = 'none';
        calcBreakdownCard.style.display = 'none';
    });

    // Close Calculator Modal
    const closeCalc = () => { calculatorModalOverlay.classList.remove('show'); };
    document.getElementById('closeCalculatorModal').addEventListener('click', closeCalc);
    document.getElementById('closeCalculatorModalBtn').addEventListener('click', closeCalc);

    // -------------------------------------------------------------
    // START INVESTING / PURCHASE MODAL
    // -------------------------------------------------------------
    const investmentModalOverlay = document.getElementById('investmentModalOverlay');
    const confirmBtn = document.getElementById('confirmInvestmentBtn');
    const amountInput = document.getElementById('investmentAmountInput');
    const sourceSelect = document.getElementById('sourceBalanceSelect');
    const alertBox = document.getElementById('investmentAlert');
    const alertMsg = document.getElementById('investmentAlertMessage');
    const depositBtn = document.getElementById('depositAmountBtn');

    // Handle purchase modal opening
    document.querySelectorAll('.start-investing-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const planId = this.dataset.planId;
            const planName = this.dataset.planName;
            const min = parseFloat(this.dataset.min);
            const max = parseFloat(this.dataset.max);

            document.getElementById('investmentPlanName').innerText = planName;
            document.getElementById('investmentPlanNameText').innerText = planName;
            document.getElementById('investmentMinAmount').innerText = min.toFixed(2);
            document.getElementById('investmentMaxAmount').innerText = max.toFixed(2);
            document.getElementById('investmentAmountHint').innerText = `Min: $${min.toFixed(2)} - Max: $${max.toFixed(2)}`;
            
            // Set dynamic balances from authenticated PHP session
            document.getElementById('fundBalanceDisplay').innerText = '$' + Number(<?php echo $user_data['deposit_balance']; ?>).toFixed(2);
            document.getElementById('earningBalanceDisplay').innerText = '$' + Number(<?php echo $user_data['earning_balance']; ?>).toFixed(2);

            alertBox.style.display = 'none';
            amountInput.value = '';
            confirmBtn.dataset.planId = planId;
            confirmBtn.disabled = false;
            confirmBtn.innerText = 'Confirm Investment';
            
            investmentModalOverlay.classList.add('show');
        });
    });

    // Close Investment Modal
    const closeInvest = () => { investmentModalOverlay.classList.remove('show'); };
    document.getElementById('closeInvestmentModal').addEventListener('click', closeInvest);
    document.getElementById('cancelInvestmentBtn').addEventListener('click', closeInvest);

    // Trigger deposit page redirect if deposit balance insufficient
    depositBtn.addEventListener('click', function() {
        window.location.href = '../../user/dashboard/deposit';
    });

    // Process Purchase via Fetch API
    confirmBtn.addEventListener('click', function() {
        const planId = this.dataset.planId;
        const amount = parseFloat(amountInput.value) || 0;
        const wallet = sourceSelect.value;

        if (!amount || amount <= 0) {
            showAlert('Please enter a valid investment amount.');
            return;
        }

        confirmBtn.disabled = true;
        confirmBtn.innerText = 'Processing...';

        fetch('process_investment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `plan_id=${planId}&amount=${amount}&wallet_type=${wallet}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                investmentModalOverlay.classList.remove('show');
                
                // Get plan name from current UI context
                const planNameElement = document.getElementById('investmentPlanNameText');
                const planName = planNameElement ? planNameElement.innerText : 'Selected Plan';
                const formattedAmount = parseFloat(amountInput.value).toFixed(2);
                
                Swal.fire({
                    html: `
                        <div class="success-screen-swal">
                            <div class="success-icon-wrapper-swal">
                                <i class="fas fa-check"></i>
                            </div>
                            <h2 class="success-title-swal">Investment Successful!</h2>
                            <p class="success-desc-swal">
                                Your investment has been approved instantly.<br>
                                <strong>$${formattedAmount}</strong> has been credited to the <strong>${planName}</strong> plan.
                            </p>
                            <div style="display: flex; flex-direction: column; gap: 10px; justify-content: center; align-items: center; width: 100%;">
                                <a href="index.php" class="btn-dashboard-swal">Go to Dashboard</a>
                                <button onclick="window.location.reload();" style="background: transparent; border: none; font-size: 0.9rem; color: rgba(255,255,255,0.5); cursor: pointer; font-family: 'Poppins', sans-serif; text-decoration: underline; margin-top: 5px;">Invest Again</button>
                            </div>
                        </div>
                    `,
                    showConfirmButton: false,
                    background: 'rgba(30, 41, 59, 0.95)',
                    backdrop: 'rgba(15, 23, 42, 0.85)',
                    customClass: {
                        popup: 'investment-success-swal'
                    },
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });
            } else {
                showAlert(data.message);
                confirmBtn.disabled = false;
                confirmBtn.innerText = 'Confirm Investment';
            }
        })
        .catch(err => {
            showAlert('Something went wrong. Please try again.');
            confirmBtn.disabled = false;
            confirmBtn.innerText = 'Confirm Investment';
        });
    });

    function showAlert(msg) {
        alertMsg.innerText = msg;
        alertBox.style.display = 'flex';
    }
});
</script>

<?php include('../../components/layout_bottom.php'); ?>
