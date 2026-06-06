<?php
// admin/withdrawals.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'Withdrawal Management';
$message = '';
$error = '';

$admin_id = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdrawal_id'])) {
    $withdrawal_id = (int)$_POST['withdrawal_id'];
    $remarks = htmlspecialchars($_POST['remarks'] ?? '');
    
    if (isset($_POST['approve_withdrawal'])) {
        try {
            $pdo->beginTransaction();
            
            // 1. Fetch details
            $stmt = $pdo->prepare("SELECT w.*, u.id as u_id FROM withdrawals w JOIN users u ON w.user_id = u.id WHERE w.id = ? AND w.status = 'pending'");
            $stmt->execute([$withdrawal_id]);
            $wdr = $stmt->fetch();
            
            if ($wdr) {
                // 2. Update withdrawal status
                $up_stmt = $pdo->prepare("UPDATE withdrawals SET status = 'approved', reviewed_by = ?, reviewed_at = NOW(), admin_remarks = ? WHERE id = ?");
                $up_stmt->execute([$admin_id, $remarks, $withdrawal_id]);
                
                // 3. Update Transaction status
                $trans_stmt = $pdo->prepare("UPDATE transactions SET status = 'completed', description = CONCAT(description, ' (Approved by Admin)') WHERE reference_id = ? AND type = 'withdrawal'");
                $trans_stmt->execute([$withdrawal_id]);
                
                // 4. Send Notification
                $notif_title = "Payout Released 🎉";
                $notif_msg = "Your withdrawal request of $" . number_format($wdr['amount'], 2) . " via " . htmlspecialchars($wdr['method']) . " has been approved and processed. Remarks: " . ($remarks ?: 'Processed successfully.');
                $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
                $notif_stmt->execute([$wdr['user_id'], $notif_title, $notif_msg]);
                
                $pdo->commit();
                $message = "Withdrawal request approved and payout released successfully!";
            } else {
                $pdo->rollBack();
                $error = "Withdrawal request not found or already processed.";
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Transaction failed: " . $e->getMessage();
        }
    } elseif (isset($_POST['reject_withdrawal'])) {
        try {
            $pdo->beginTransaction();
            
            // 1. Fetch details
            $stmt = $pdo->prepare("SELECT w.*, u.id as u_id FROM withdrawals w JOIN users u ON w.user_id = u.id WHERE w.id = ? AND w.status = 'pending'");
            $stmt->execute([$withdrawal_id]);
            $wdr = $stmt->fetch();
            
            if ($wdr) {
                $amount = (float)$wdr['amount'];
                
                // 2. Update withdrawal status
                $up_stmt = $pdo->prepare("UPDATE withdrawals SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW(), admin_remarks = ? WHERE id = ?");
                $up_stmt->execute([$admin_id, $remarks, $withdrawal_id]);
                
                // 3. Update Transaction status
                $trans_stmt = $pdo->prepare("UPDATE transactions SET status = 'rejected', description = CONCAT(description, ' (Rejected by Admin)') WHERE reference_id = ? AND type = 'withdrawal'");
                $trans_stmt->execute([$withdrawal_id]);
                
                // 4. Refund wallet
                $wallet_stmt = $pdo->prepare("UPDATE wallets SET earning_balance = earning_balance + ?, total_withdrawn = total_withdrawn - ? WHERE user_id = ?");
                $wallet_stmt->execute([$amount, $amount, $wdr['user_id']]);
                
                // 5. Send Notification
                $notif_title = "Payout Rejected ❌";
                $notif_msg = "Your withdrawal request of $" . number_format($amount, 2) . " has been rejected. The full amount has been refunded back to your available balance. Remarks: " . ($remarks ?: 'N/A');
                $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
                $notif_stmt->execute([$wdr['user_id'], $notif_title, $notif_msg]);
                
                $pdo->commit();
                $message = "Withdrawal request has been rejected and funds have been refunded to user's wallet.";
            } else {
                $pdo->rollBack();
                $error = "Withdrawal request not found or already processed.";
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Transaction failed: " . $e->getMessage();
        }
    }
}

$withdrawals = $pdo->query("
    SELECT w.*, u.name as user_name, u.email as user_email 
    FROM withdrawals w 
    JOIN users u ON w.user_id = u.id 
    ORDER BY w.created_at DESC
")->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="admin-page-heading py-3 mb-4">
                <h4 class="fw-bold"><span class="text-muted fw-light">Finance /</span> Withdrawals</h4>
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

            <div class="card admin-table-card">
                <h5 class="card-header">Withdrawal Requests</h5>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Amount</th>
                                <th>Payout Gateway</th>
                                <th>Account Details</th>
                                <th>Status</th>
                                <th>Requested At</th>
                                <th>Remarks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            <?php if (empty($withdrawals)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No withdrawal requests found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($withdrawals as $w): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-break d-block"><?php echo htmlspecialchars($w['user_name']); ?></strong><br>
                                            <small class="text-muted text-break"><?php echo htmlspecialchars($w['user_email']); ?></small>
                                        </td>
                                        <td>
                                            <span class="text-danger fw-bold">
                                                <?php echo function_exists('hk_format_dual_currency') ? hk_format_dual_currency($w['amount'], $pdo) : '$' . number_format($w['amount'], 2); ?>
                                            </span><br>
                                            <small class="text-muted">
                                                Net: <?php echo function_exists('hk_format_dual_currency') ? hk_format_dual_currency($w['net_amount'], $pdo) : '$' . number_format($w['net_amount'], 2); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-danger"><?php echo htmlspecialchars($w['method']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-info"><?php echo htmlspecialchars($w['account_title']); ?></span><br>
                                            <code><?php echo htmlspecialchars($w['account_number']); ?></code>
                                        </td>
                                        <td>
                                            <?php if ($w['status'] === 'pending'): ?>
                                                <span class="badge bg-label-warning">Pending</span>
                                            <?php elseif ($w['status'] === 'approved'): ?>
                                                <span class="badge bg-label-success">Approved</span>
                                            <?php else: ?>
                                                <span class="badge bg-label-danger">Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($w['created_at'])); ?></td>
                                        <td>
                                            <small class="text-muted"><?php echo htmlspecialchars($w['admin_remarks'] ?: 'N/A'); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($w['status'] === 'pending'): ?>
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#withdrawModal<?php echo $w['id']; ?>">
                                                    Process
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted fw-semibold">Processed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Render modals outside table to prevent transition conflicts -->
<?php foreach ($withdrawals as $w): ?>
    <?php if ($w['status'] === 'pending'): ?>
        <div class="modal fade" id="withdrawModal<?php echo $w['id']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form class="modal-content" method="POST">
                    <input type="hidden" name="withdrawal_id" value="<?php echo $w['id']; ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Process Payout Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>You are about to process the payout request of <strong>$<?php echo number_format($w['amount'], 2); ?> (Rs <?php echo number_format($w['amount'] * 280, 2); ?>)</strong> for <strong><?php echo htmlspecialchars($w['user_name']); ?></strong> via <strong><?php echo htmlspecialchars($w['method']); ?></strong>.</p>
                        
                        <div class="alert alert-warning p-2 small mb-3">
                            <strong>Receiving Details:</strong><br>
                            Account Title: <?php echo htmlspecialchars($w['account_title']); ?><br>
                            Account No / IBAN: <?php echo htmlspecialchars($w['account_number']); ?>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Admin Remarks / Notes</label>
                            <textarea class="form-control" name="remarks" rows="3" placeholder="Add txn ID, batch reference, or rejection reason..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="reject_withdrawal" class="btn btn-danger">Reject &amp; Refund</button>
                        <button type="submit" name="approve_withdrawal" class="btn btn-success">Approve &amp; Release</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<?php include 'includes/footer.php'; ?>
