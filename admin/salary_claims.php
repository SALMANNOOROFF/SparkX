<?php
// admin/salary_claims.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'Salary Payout Claims';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $claim_id = (int)$_POST['claim_id'];
    $remarks = htmlspecialchars($_POST['remarks'] ?? '');
    
    if (isset($_POST['approve_claim'])) {
        try {
            $pdo->beginTransaction();
            
            // 1. Get Claim details
            $stmt = $pdo->prepare("SELECT c.*, u.id as u_id FROM salary_claims c JOIN users u ON c.user_id = u.id WHERE c.id = ? AND c.status = 'pending'");
            $stmt->execute([$claim_id]);
            $claim = $stmt->fetch();
            
            if ($claim) {
                // 2. Update claim status
                $up_stmt = $pdo->prepare("UPDATE salary_claims SET status = 'approved', admin_remarks = ? WHERE id = ?");
                $up_stmt->execute([$remarks, $claim_id]);
                
                // 3. Credit user wallet
                $wallet_stmt = $pdo->prepare("UPDATE wallets SET earning_balance = earning_balance + ? WHERE user_id = ?");
                $wallet_stmt->execute([$claim['amount'], $claim['u_id']]);
                
                // 4. Log transaction
                $desc = "Salary Claim Approved for " . $claim['rank_name'];
                $trans_stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description, status) VALUES (?, ?, 'profit', ?, 'completed')");
                $trans_stmt->execute([$claim['u_id'], $claim['amount'], $desc]);
                
                // 5. Send Notification
                $notif_title = "Salary Claim Approved! 🎉";
                $notif_msg = "Congratulations! Your salary payout of $" . number_format($claim['amount'], 2) . " for " . $claim['rank_name'] . " has been approved and credited to your earning balance.";
                $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
                $notif_stmt->execute([$claim['u_id'], $notif_title, $notif_msg]);
                
                $pdo->commit();
                $message = "Salary claim approved and balance successfully credited to user's wallet!";
            } else {
                $pdo->rollBack();
                $error = "Claim not found or already processed.";
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Transaction failed: " . $e->getMessage();
        }
    } elseif (isset($_POST['reject_claim'])) {
        try {
            // Fetch claim details before rejecting to send notification
            $c_stmt = $pdo->prepare("SELECT user_id, rank_name, amount FROM salary_claims WHERE id = ? AND status = 'pending'");
            $c_stmt->execute([$claim_id]);
            $claim_data = $c_stmt->fetch();
            
            $stmt = $pdo->prepare("UPDATE salary_claims SET status = 'rejected', admin_remarks = ? WHERE id = ? AND status = 'pending'");
            $stmt->execute([$remarks, $claim_id]);
            
            if ($stmt->rowCount() > 0) {
                if ($claim_data) {
                    $notif_title = "Salary Claim Rejected ❌";
                    $notif_msg = "Your salary claim of $" . number_format($claim_data['amount'], 2) . " for " . $claim_data['rank_name'] . " has been rejected. Remarks: " . ($remarks ?: 'No remarks provided.');
                    $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
                    $notif_stmt->execute([$claim_data['user_id'], $notif_title, $notif_msg]);
                }
                $message = "Salary claim has been rejected.";
            } else {
                $error = "Claim not found or already processed.";
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Fetch all claims
$claims = $pdo->query("SELECT c.*, u.name as user_name, u.email as user_email 
                       FROM salary_claims c 
                       JOIN users u ON c.user_id = u.id 
                       ORDER BY c.id DESC")->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Management /</span> Salary Payout Claims</h4>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-close="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-close="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <h5 class="card-header">Submitted Salary Claims</h5>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Rank</th>
                                <th>Amount</th>
                                <th>Submitted At</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            <?php if (empty($claims)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No salary claims found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($claims as $claim): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($claim['user_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($claim['user_email']); ?></small>
                                    </td>
                                    <td><span class="badge bg-label-primary"><?php echo htmlspecialchars($claim['rank_name']); ?></span></td>
                                    <td><strong>$<?php echo number_format($claim['amount'], 2); ?></strong></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($claim['created_at'])); ?></td>
                                    <td>
                                        <?php if ($claim['status'] === 'pending'): ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php elseif ($claim['status'] === 'approved'): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($claim['admin_remarks'] ?: 'N/A'); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($claim['status'] === 'pending'): ?>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#claimModal<?php echo $claim['id']; ?>">
                                                Process
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
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
<?php foreach ($claims as $claim): ?>
    <?php if ($claim['status'] === 'pending'): ?>
        <div class="modal fade" id="claimModal<?php echo $claim['id']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form class="modal-content" method="POST">
                    <input type="hidden" name="claim_id" value="<?php echo $claim['id']; ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Process Salary Payout</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>You are about to process the salary claim of <strong>$<?php echo number_format($claim['amount'], 2); ?></strong> for <strong><?php echo htmlspecialchars($claim['user_name']); ?></strong> (<?php echo htmlspecialchars($claim['rank_name']); ?>).</p>
                        <div class="mb-3">
                            <label class="form-label">Admin Remarks</label>
                            <textarea class="form-control" name="remarks" rows="3" placeholder="Add remarks (optional)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="reject_claim" class="btn btn-danger">Reject</button>
                        <button type="submit" name="approve_claim" class="btn btn-success">Approve &amp; Pay</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<?php include 'includes/footer.php'; ?>
