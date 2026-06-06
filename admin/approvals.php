<?php
// admin/approvals.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'Approvals Console';
$message = '';
$error = '';

$admin_id = $_SESSION['admin_id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'], $_POST['type'])) {
    $action = $_POST['action']; // approve or reject
    $id = (int)$_POST['id'];
    $type = $_POST['type']; // deposit, withdrawal, or investment

    $pdo->beginTransaction();
    try {
        if ($type === 'deposit') {
            // 1. Fetch Deposit Request
            $stmt = $pdo->prepare("SELECT d.*, u.name as user_name FROM deposits d JOIN users u ON d.user_id = u.id WHERE d.id = ? AND d.status = 'pending'");
            $stmt->execute([$id]);
            $deposit = $stmt->fetch();

            if (!$deposit) {
                throw new Exception("Deposit request not found or already processed.");
            }

            $user_id = $deposit['user_id'];
            $amount = (float)$deposit['amount'];
            $method = $deposit['method'];
            $trx_id = $deposit['proof_image'];

            if ($action === 'approve') {
                // Update Deposit Status
                $pdo->prepare("UPDATE deposits SET status = 'approved' WHERE id = ?")->execute([$id]);

                // Update Transaction Status
                $pdo->prepare("UPDATE transactions SET status = 'completed' WHERE user_id = ? AND type = 'deposit' AND amount = ? AND status = 'pending'")->execute([$user_id, $amount]);

                // Credit User Wallet
                $pdo->prepare("UPDATE wallets SET deposit_balance = deposit_balance + ? WHERE user_id = ?")->execute([$amount, $user_id]);

                // Send Notification
                $notif_title = "Recharge Approved";
                $notif_msg = "Your deposit of $" . number_format($amount, 2) . " via " . htmlspecialchars($method) . " (Trx ID: " . htmlspecialchars($trx_id) . ") has been approved and credited.";
                $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)")->execute([$user_id, $notif_title, $notif_msg]);

                $message = "Recharge request approved successfully!";
            } elseif ($action === 'reject') {
                // Update Deposit Status
                $pdo->prepare("UPDATE deposits SET status = 'rejected' WHERE id = ?")->execute([$id]);

                // Update Transaction Status
                $pdo->prepare("UPDATE transactions SET status = 'rejected' WHERE user_id = ? AND type = 'deposit' AND amount = ? AND status = 'pending'")->execute([$user_id, $amount]);

                // Send Notification
                $notif_title = "Recharge Rejected";
                $notif_msg = "Your deposit request of $" . number_format($amount, 2) . " via " . htmlspecialchars($method) . " (Trx ID: " . htmlspecialchars($trx_id) . ") was rejected by administrator.";
                $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)")->execute([$user_id, $notif_title, $notif_msg]);

                $message = "Recharge request rejected successfully.";
            }

        } elseif ($type === 'withdrawal') {
            // 2. Fetch Withdrawal Request
            $stmt = $pdo->prepare("SELECT w.*, u.name as user_name FROM withdrawals w JOIN users u ON w.user_id = u.id WHERE w.id = ? AND w.status = 'pending'");
            $stmt->execute([$id]);
            $withdraw = $stmt->fetch();

            if (!$withdraw) {
                throw new Exception("Withdrawal request not found or already processed.");
            }

            $user_id = $withdraw['user_id'];
            $amount = (float)$withdraw['amount'];
            $method = $withdraw['method'];
            $acc_num = $withdraw['account_number'];

            if ($action === 'approve') {
                // Update Withdrawal Status
                $pdo->prepare("UPDATE withdrawals SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?")->execute([$admin_id, $id]);

                // Update Transaction Status
                $pdo->prepare("UPDATE transactions SET status = 'completed' WHERE user_id = ? AND type = 'withdrawal' AND reference_id = ?")->execute([$user_id, $id]);

                // Send Notification
                $notif_title = "Payout Released";
                $notif_msg = "Your withdrawal request of $" . number_format($amount, 2) . " via " . htmlspecialchars($method) . " (A/C: " . htmlspecialchars($acc_num) . ") has been approved and processed.";
                $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)")->execute([$user_id, $notif_title, $notif_msg]);

                $message = "Payout request approved and released!";
            } elseif ($action === 'reject') {
                // Update Withdrawal Status
                $pdo->prepare("UPDATE withdrawals SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?")->execute([$admin_id, $id]);

                // Update Transaction Status
                $pdo->prepare("UPDATE transactions SET status = 'rejected' WHERE user_id = ? AND type = 'withdrawal' AND reference_id = ?")->execute([$user_id, $id]);

                // Refund Wallet (Deducted on submit, so refund back to earning_balance)
                $pdo->prepare("UPDATE wallets SET earning_balance = earning_balance + ?, total_withdrawn = total_withdrawn - ? WHERE user_id = ?")->execute([$amount, $amount, $user_id]);

                // Send Notification
                $notif_title = "Payout Rejected";
                $notif_msg = "Your withdrawal request of $" . number_format($amount, 2) . " was rejected. The full amount has been refunded to your available balance.";
                $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)")->execute([$user_id, $notif_title, $notif_msg]);

                $message = "Payout request rejected and refunded.";
            }

        } elseif ($type === 'investment') {
            // 3. Fetch Investment Request
            $stmt = $pdo->prepare("SELECT i.*, p.name as plan_name, u.name as user_name FROM investments i JOIN plans p ON i.plan_id = p.id JOIN users u ON i.user_id = u.id WHERE i.id = ? AND i.status = 'pending'");
            $stmt->execute([$id]);
            $investment = $stmt->fetch();

            if (!$investment) {
                throw new Exception("Investment plan request not found or already processed.");
            }

            $user_id = $investment['user_id'];
            $amount = (float)$investment['amount'];
            $plan_name = $investment['plan_name'];
            $plan_id = $investment['plan_id'];

            if ($action === 'approve') {
                // Activate Investment status
                $pdo->prepare("UPDATE investments SET status = 'active' WHERE id = ?")->execute([$id]);

                // Update Transaction Status
                $pdo->prepare("UPDATE transactions SET status = 'completed', description = ? WHERE user_id = ? AND type = 'investment' AND status = 'pending'")->execute([
                    "Invested $" . number_format($amount, 2) . " in " . $plan_name,
                    $user_id
                ]);

                // Increment total_invested
                $pdo->prepare("UPDATE wallets SET total_invested = total_invested + ? WHERE user_id = ?")->execute([$amount, $user_id]);

                // --- 5-Level MLM Referral Commission Distribution ---
                $ref_stmt = $pdo->prepare("SELECT referrer_id, level FROM referrals WHERE referee_id = ? AND level <= 5 ORDER BY level ASC");
                $ref_stmt->execute([$user_id]);
                $referrers = $ref_stmt->fetchAll();

                $comm_settings = $pdo->query("SELECT level, commission_pct FROM referral_settings")->fetchAll();
                $comms_pct = [];
                foreach ($comm_settings as $row) {
                    $comms_pct[$row['level']] = (float)$row['commission_pct'];
                }

                foreach ($referrers as $ref) {
                    $referrer_id = $ref['referrer_id'];
                    $level = (int)$ref['level'];
                    
                    $pct = isset($comms_pct[$level]) ? $comms_pct[$level] : ($level == 1 ? 10.0 : ($level == 2 ? 5.0 : ($level == 3 ? 3.0 : ($level == 4 ? 2.0 : 1.0))));
                    $commission_amount = $amount * ($pct / 100.0);

                    if ($commission_amount > 0) {
                        // Credit Referrer
                        $pdo->prepare("UPDATE wallets SET earning_balance = earning_balance + ? WHERE user_id = ?")->execute([$commission_amount, $referrer_id]);

                        // Log Transaction
                        $bonus_desc = "Referral Bonus of $" . number_format($commission_amount, 2) . " from Level {$level} referral (" . htmlspecialchars($investment['user_name']) . ") investing in " . $plan_name;
                        $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description, status) VALUES (?, ?, 'referral_bonus', ?, 'completed')")->execute([
                            $referrer_id, $commission_amount, $bonus_desc
                        ]);

                        // Notify Referrer
                        $ref_title = "Affiliate Commission!";
                        $ref_msg = "You earned a $" . number_format($commission_amount, 2) . " affiliate reward from Level {$level} downline referee plan purchase.";
                        $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)")->execute([$referrer_id, $ref_title, $ref_msg]);
                    }
                }

                // Notify Purchasing User
                $notif_title = "Plan Activated!";
                $notif_msg = "Your investment of $" . number_format($amount, 2) . " in plan '" . htmlspecialchars($plan_name) . "' has been approved and activated. Daily returns have begun!";
                $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)")->execute([$user_id, $notif_title, $notif_msg]);

                $message = "Plan Investment approved and activated successfully!";
            } elseif ($action === 'reject') {
                // Reject Investment status
                $pdo->prepare("UPDATE investments SET status = 'rejected' WHERE id = ?")->execute([$id]);

                // Update Transaction Status
                $pdo->prepare("UPDATE transactions SET status = 'rejected', description = ? WHERE user_id = ? AND type = 'investment' AND status = 'pending'")->execute([
                    "Plan Investment in " . $plan_name . " - Rejected",
                    $user_id
                ]);

                // Refund the amount to user's deposit wallet
                $pdo->prepare("UPDATE wallets SET deposit_balance = deposit_balance + ? WHERE user_id = ?")->execute([$amount, $user_id]);

                // Notify User
                $notif_title = "Investment Rejected";
                $notif_msg = "Your plan purchase request for '" . htmlspecialchars($plan_name) . "' of $" . number_format($amount, 2) . " was rejected by admin. The amount has been refunded to your deposit balance.";
                $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)")->execute([$user_id, $notif_title, $notif_msg]);

                $message = "Plan Investment request rejected and purchase cost refunded.";
            }
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Transaction Error: " . $e->getMessage();
    }
}

// Fetch pending counts
$deposit_pending_count = $pdo->query("SELECT COUNT(*) FROM deposits WHERE status = 'pending'")->fetchColumn();
$withdraw_pending_count = $pdo->query("SELECT COUNT(*) FROM withdrawals WHERE status = 'pending'")->fetchColumn();
$investment_pending_count = $pdo->query("SELECT COUNT(*) FROM investments WHERE status = 'pending'")->fetchColumn();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="admin-page-heading py-3 mb-4">
                <h4 class="fw-bold"><span class="text-muted fw-light">Finance /</span> Approvals Console</h4>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Navigation Tabs -->
            <div class="nav-align-top mb-4">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-deposits" aria-controls="navs-deposits" aria-selected="true">
                            Recharges <span class="badge bg-warning badge-notifications ms-1"><?php echo $deposit_pending_count; ?></span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-withdrawals" aria-controls="navs-withdrawals" aria-selected="false">
                            Payouts <span class="badge bg-danger badge-notifications ms-1"><?php echo $withdraw_pending_count; ?></span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-investments" aria-controls="navs-investments" aria-selected="false">
                            Plan Investments <span class="badge bg-primary badge-notifications ms-1"><?php echo $investment_pending_count; ?></span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Deposits (Recharges) Tab -->
                    <div class="tab-pane fade show active" id="navs-deposits" role="tabpanel">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User Details</th>
                                        <th>Amount Requested</th>
                                        <th>Gateway Method</th>
                                        <th>Reference Trx ID</th>
                                        <th>Submitted Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $deposits = $pdo->query("SELECT d.*, u.name, u.email FROM deposits d JOIN users u ON d.user_id = u.id WHERE d.status = 'pending' ORDER BY d.created_at DESC")->fetchAll();
                                    if (count($deposits) > 0):
                                        foreach ($deposits as $dep):
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($dep['name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($dep['email']); ?></small>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">$<?php echo number_format($dep['amount'], 2); ?></span><br>
                                                <small class="text-muted">Rs <?php echo number_format($dep['amount'] * 280, 2); ?></small>
                                            </td>
                                            <td><span class="badge bg-label-info"><?php echo htmlspecialchars($dep['method']); ?></span></td>
                                            <td><code><?php echo htmlspecialchars($dep['proof_image']); ?></code></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($dep['created_at'])); ?></td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="type" value="deposit">
                                                    <input type="hidden" name="id" value="<?php echo $dep['id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-sm btn-success me-1"><i class="bx bx-check"></i> Approve</button>
                                                </form>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="type" value="deposit">
                                                    <input type="hidden" name="id" value="<?php echo $dep['id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-sm btn-danger"><i class="bx bx-x"></i> Reject</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php
                                        endforeach;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No pending recharge requests found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Withdrawals (Payouts) Tab -->
                    <div class="tab-pane fade" id="navs-withdrawals" role="tabpanel">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User Details</th>
                                        <th>Payout Gateway</th>
                                        <th>Requested Amount</th>
                                        <th>Net Amount</th>
                                        <th>Account Title</th>
                                        <th>Account Number</th>
                                        <th>Submitted Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $withdrawals = $pdo->query("SELECT w.*, u.name, u.email FROM withdrawals w JOIN users u ON w.user_id = u.id WHERE w.status = 'pending' ORDER BY w.created_at DESC")->fetchAll();
                                    if (count($withdrawals) > 0):
                                        foreach ($withdrawals as $wdr):
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($wdr['name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($wdr['email']); ?></small>
                                            </td>
                                            <td><span class="badge bg-label-danger"><?php echo htmlspecialchars($wdr['method']); ?></span></td>
                                            <td><span class="fw-bold text-danger">$<?php echo number_format($wdr['amount'], 2); ?></span></td>
                                            <td>
                                                <span class="fw-bold text-success">$<?php echo number_format($wdr['net_amount'], 2); ?></span><br>
                                                <small class="text-muted">Rs <?php echo number_format($wdr['net_amount'] * 280, 2); ?></small>
                                            </td>
                                            <td><strong><?php echo htmlspecialchars($wdr['account_title']); ?></strong></td>
                                            <td><code><?php echo htmlspecialchars($wdr['account_number']); ?></code></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($wdr['created_at'])); ?></td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="type" value="withdrawal">
                                                    <input type="hidden" name="id" value="<?php echo $wdr['id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-sm btn-success me-1"><i class="bx bx-check"></i> Approve</button>
                                                </form>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="type" value="withdrawal">
                                                    <input type="hidden" name="id" value="<?php echo $wdr['id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-sm btn-danger"><i class="bx bx-x"></i> Reject</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php
                                        endforeach;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">No pending payout requests found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Investments Tab -->
                    <div class="tab-pane fade" id="navs-investments" role="tabpanel">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User Details</th>
                                        <th>Investment Plan</th>
                                        <th>Amount</th>
                                        <th>ROI Parameters</th>
                                        <th>Submitted Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $investments = $pdo->query("SELECT i.*, p.name as plan_name, u.name as user_name, u.email as user_email FROM investments i JOIN plans p ON i.plan_id = p.id JOIN users u ON i.user_id = u.id WHERE i.status = 'pending' ORDER BY i.created_at DESC")->fetchAll();
                                    if (count($investments) > 0):
                                        foreach ($investments as $inv):
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($inv['user_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($inv['user_email']); ?></small>
                                            </td>
                                            <td><span class="badge bg-label-primary"><?php echo htmlspecialchars($inv['plan_name']); ?></span></td>
                                            <td><span class="fw-bold text-success">$<?php echo number_format($inv['amount'], 2); ?></span></td>
                                            <td>
                                                <small>ROI: <?php echo $inv['daily_roi']; ?>% / daily</small><br>
                                                <small>Hourly: <?php echo $inv['hourly_rate']; ?>%</small>
                                            </td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($inv['created_at'])); ?></td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="type" value="investment">
                                                    <input type="hidden" name="id" value="<?php echo $inv['id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-sm btn-success me-1"><i class="bx bx-check"></i> Approve</button>
                                                </form>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="type" value="investment">
                                                    <input type="hidden" name="id" value="<?php echo $inv['id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-sm btn-danger"><i class="bx bx-x"></i> Reject</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php
                                        endforeach;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No pending plan investments found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
