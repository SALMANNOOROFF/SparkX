<?php
// admin/index.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'Dashboard';

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="admin-page-heading py-3 mb-4">
                <h4 class="fw-bold">Admin Dashboard Overview</h4>
            </div>

            <?php
            // 1. User Stats
            $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            $active_users = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
            $inactive_users = $total_users - $active_users;

            // 2. Deposit Stats
            $today_deposit = $pdo->query("SELECT SUM(amount) FROM deposits WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?? 0;
            $today_approved = $pdo->query("SELECT SUM(amount) FROM deposits WHERE DATE(created_at) = CURDATE() AND status = 'approved'")->fetchColumn() ?? 0;
            $pending_deposits_count = $pdo->query("SELECT COUNT(*) FROM deposits WHERE status = 'pending'")->fetchColumn() ?? 0;
            $pending_deposits_amount = $pdo->query("SELECT SUM(amount) FROM deposits WHERE status = 'pending'")->fetchColumn() ?? 0;
            
            $total_deposits = $pdo->query("SELECT SUM(amount) FROM deposits")->fetchColumn() ?? 0;
            $total_approved_deposits = $pdo->query("SELECT SUM(amount) FROM deposits WHERE status = 'approved'")->fetchColumn() ?? 0;
            $all_time_deposit_count = $pdo->query("SELECT COUNT(*) FROM deposits")->fetchColumn() ?? 0;

            // Gateway specific deposits (Using 'method' column)
            $ep_deposit = $pdo->query("SELECT SUM(amount) FROM deposits WHERE method LIKE '%easy%paisa%' OR method LIKE '%easypaisa%'")->fetchColumn() ?? 0;
            $jc_deposit = $pdo->query("SELECT SUM(amount) FROM deposits WHERE method LIKE '%jazz%cash%' OR method LIKE '%jazzcash%'")->fetchColumn() ?? 0;
            $crypto_deposit = $pdo->query("SELECT SUM(amount) FROM deposits WHERE method LIKE '%crypto%' OR method LIKE '%usdt%'")->fetchColumn() ?? 0;

            // 3. Payout Stats
            $pending_payouts_count = $pdo->query("SELECT COUNT(*) FROM withdrawals WHERE status = 'pending'")->fetchColumn();
            $pending_payouts_amount = $pdo->query("SELECT SUM(amount) FROM withdrawals WHERE status = 'pending'")->fetchColumn() ?? 0;
            $approved_payouts_amount = $pdo->query("SELECT SUM(amount) FROM withdrawals WHERE status = 'approved'")->fetchColumn() ?? 0;

            // 4. Investment Stats
            $total_investment = $pdo->query("SELECT SUM(amount) FROM investments WHERE status = 'active'")->fetchColumn() ?? 0;

            // 5. Distribution Stats
            $today_roi_dist = $pdo->query("SELECT SUM(amount) FROM transactions WHERE type = 'profit' AND DATE(created_at) = CURDATE() AND status = 'completed'")->fetchColumn() ?? 0;
            $today_comm_dist = $pdo->query("SELECT SUM(amount) FROM transactions WHERE type = 'commission' AND DATE(created_at) = CURDATE() AND status = 'completed'")->fetchColumn() ?? 0;
            $total_dist_today = $today_roi_dist + $today_comm_dist;
            $today_dist_users = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM transactions WHERE DATE(created_at) = CURDATE() AND status = 'completed'")->fetchColumn() ?? 0;
            ?>

            <!-- Dashboard CSS Extensions -->
            <style>
                .stat-card { border: none; border-radius: 16px; overflow: hidden; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.08); position: relative; }
                .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
                .stat-card .card-body { padding: 1.5rem; position: relative; z-index: 1; }
                .stat-card::before { content: ""; position: absolute; top: -50%; right: -20%; width: 150px; height: 150px; background: rgba(255,255,255,0.15); border-radius: 50%; z-index: 0; }
                
                .bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
                .bg-gradient-warning { background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); }
                .bg-gradient-success { background: linear-gradient(135deg, #84fb95 0%, #00d2ff 100%); }
                .bg-gradient-info { background: linear-gradient(135deg, #2af598 0%, #009efd 100%); }
                .bg-gradient-dark { background: linear-gradient(135deg, #232526 0%, #414345 100%); }
                
                .node-mini-card { background: #fff; border: 1px solid #eee; border-radius: 12px; transition: all 0.3s ease; }
                .node-mini-card:hover { border-color: #667eea; background: #f8f9ff; }
                


                
                .icon-box { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: rgba(255,255,255,0.2); color: #fff; }
                .text-shadow { text-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            </style>

            <!-- Top Row: User & Payout Highlights -->
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="stat-card bg-gradient-primary text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white opacity-75 mb-2 fw-semibold">TOTAL USERS</h6>
                                    <h2 class="mb-0 fw-bold text-shadow"><?php echo number_format($total_users); ?></h2>
                                </div>
                                <div class="icon-box">
                                    <i class="bx bx-group fs-2"></i>
                                </div>
                            </div>
                            <div class="mt-4 pt-2 border-top border-white border-opacity-10 d-flex justify-content-between">
                                <span class="small"><i class="bx bxs-circle text-success me-1"></i> <?php echo $active_users; ?> Active</span>
                                <span class="small"><i class="bx bxs-circle text-danger me-1"></i> <?php echo $inactive_users; ?> Inactive</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-card bg-gradient-warning text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white opacity-75 mb-2 fw-semibold">PENDING RECHARGES</h6>
                                    <h2 class="mb-0 fw-bold text-shadow"><?php echo $pending_deposits_count; ?></h2>
                                </div>
                                <div class="icon-box">
                                    <i class="bx bx-wallet fs-2"></i>
                                </div>
                            </div>
                            <div class="mt-4 pt-2 border-top border-white border-opacity-10">
                                <div class="small fw-bold"><?php echo format_pkr($pending_deposits_amount); ?> Awaiting</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-card bg-gradient-danger text-white h-100" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee0979 100%);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white opacity-75 mb-2 fw-semibold">PENDING PAYOUTS</h6>
                                    <h2 class="mb-0 fw-bold text-shadow"><?php echo $pending_payouts_count; ?></h2>
                                </div>
                                <div class="icon-box">
                                    <i class="bx bx-timer fs-2"></i>
                                </div>
                            </div>
                            <div class="mt-4 pt-2 border-top border-white border-opacity-10">
                                <div class="small fw-bold"><?php echo format_pkr($pending_payouts_amount); ?> Needed</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-card bg-gradient-success text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white opacity-75 mb-2 fw-semibold">TODAY'S DEPOSIT</h6>
                                    <h2 class="mb-0 fw-bold text-shadow"><?php echo format_pkr($today_deposit); ?></h2>
                                </div>
                                <div class="icon-box">
                                    <i class="bx bx-plus-circle fs-2"></i>
                                </div>
                            </div>
                            <div class="mt-4 pt-2 border-top border-white border-opacity-10">
                                <span class="small">Approved: <?php echo format_pkr($today_approved); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Second Row: Deposit Breakdowns -->
            <div class="row">
                <div class="col-md-7 mb-4">
                    <div class="card shadow-sm border-0 h-100" style="border-radius: 16px;">
                        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-4 px-4">
                            <h5 class="mb-0 fw-bold text-dark">Overall Deposit Analysis</h5>
                            <span class="badge bg-label-primary"><?php echo $all_time_deposit_count; ?> Total Requests</span>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="p-3 border-0 rounded-4 text-center" style="background: #f0f7ff;">
                                        <div class="text-primary fw-bold small mb-1">EASYPAISA</div>
                                        <div class="h5 mb-0 fw-bold text-dark"><?php echo format_pkr($ep_deposit); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 border-0 rounded-4 text-center" style="background: #fff8f0;">
                                        <div class="text-warning fw-bold small mb-1">JAZZCASH</div>
                                        <div class="h5 mb-0 fw-bold text-dark"><?php echo format_pkr($jc_deposit); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 border-0 rounded-4 text-center" style="background: #f0fff4;">
                                        <div class="text-success fw-bold small mb-1">CRYPTO</div>
                                        <div class="h5 mb-0 fw-bold text-dark"><?php echo format_pkr($crypto_deposit); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 p-4 stat-card bg-gradient-dark text-white d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-1 text-white fw-bold">Total Deposit Volume</h4>
                                    <p class="mb-0 opacity-75 small">Approved + Pending requests</p>
                                </div>
                                <div class="text-end">
                                    <div class="h2 mb-0 text-success fw-bold"><?php echo format_pkr($total_deposits); ?></div>
                                    <div class="small opacity-75 mt-1">Approved: <span class="text-white fw-bold"><?php echo format_pkr($total_approved_deposits); ?></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 mb-4">
                    <div class="card shadow-sm border-0 h-100" style="border-radius: 16px; background: linear-gradient(135deg, #fdfcfb 0%, #e2d1c3 100%);">
                        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-4 px-4">
                            <h5 class="mb-0 fw-bold text-dark">Distribution Today</h5>
                            <span class="badge bg-white text-primary shadow-sm"><?php echo date('d M, Y'); ?></span>
                        </div>
                        <div class="card-body px-4">
                            <div class="d-flex align-items-center mb-4 p-3 bg-white rounded-4 shadow-sm">
                                <div class="icon-box bg-primary me-3">
                                    <i class="bx bx-gift fs-3"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-muted small">TOTAL DISTRIBUTED</h6>
                                    <h3 class="mb-0 fw-bold text-primary"><?php echo format_pkr($total_dist_today); ?></h3>
                                </div>
                            </div>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <div class="p-2 border rounded-3 bg-white">
                                        <div class="small text-muted mb-1">ROI Profit</div>
                                        <div class="fw-bold text-dark"><?php echo format_pkr($today_roi_dist); ?></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 border rounded-3 bg-white">
                                        <div class="small text-muted mb-1">Commissions</div>
                                        <div class="fw-bold text-dark"><?php echo format_pkr($today_comm_dist); ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 bg-white rounded-4 border">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="text-muted small fw-semibold d-block">ACTIVE BENEFICIARIES</label>
                                        <div class="h4 fw-bold text-dark mb-0"><?php echo $today_dist_users; ?> Users</div>
                                    </div>
                                    <div class="text-success small fw-bold">
                                        <i class="bx bx-trending-up me-1"></i> Paid Out
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Final Summary Card -->
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card stat-card bg-gradient-dark text-white p-4 d-flex justify-content-between align-items-center flex-row">
                        <div>
                            <h4 class="mb-1 text-white fw-bold">Total Investment Volume</h4>
                            <p class="mb-0 opacity-75 small">Active investments in SparkX1</p>
                        </div>
                        <div class="text-end">
                            <div class="h2 mb-0 text-success fw-bold"><?php echo format_usd($total_investment); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

