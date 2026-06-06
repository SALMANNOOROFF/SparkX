<?php
// admin/deposits.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'Deposit Management';

// Handle approval/rejection
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    $admin_id = $_SESSION['user_id']; // Current logged in admin

    if ($action === 'approve') {
        try {
            $pdo->beginTransaction();
            
            // 1. Update status
            $pdo->prepare("UPDATE deposits SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?")->execute([$admin_id, $id]);
            
            // 2. Get deposit details
            $stmt = $pdo->prepare("SELECT user_id, amount FROM deposits WHERE id = ?");
            $stmt->execute([$id]);
            $deposit = $stmt->fetch();
            
            if ($deposit) {
                // 3. Update wallet (deposit_balance)
                $pdo->prepare("UPDATE wallets SET deposit_balance = deposit_balance + ? WHERE user_id = ?")->execute([$deposit['amount'], $deposit['user_id']]);
                
                // 4. Create Transaction record
                $pdo->prepare("INSERT INTO transactions (user_id, type, amount, status) VALUES (?, 'deposit', ?, 'completed')")
                    ->execute([$deposit['user_id'], $deposit['amount']]);
            }
            
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
        }
    } elseif ($action === 'reject') {
        $pdo->prepare("UPDATE deposits SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?")->execute([$admin_id, $id]);
    }
    header("Location: deposits.php");
    exit;
}

$deposits = $pdo->query("
    SELECT d.*, u.name as user_name, u.email as user_email 
    FROM deposits d 
    JOIN users u ON d.user_id = u.id 
    ORDER BY d.created_at DESC
")->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="admin-page-heading py-3 mb-4">
                <h4 class="fw-bold"><span class="text-muted fw-light">Finance /</span> Deposits</h4>
            </div>

            <div class="card admin-table-card">
                <h5 class="card-header">Deposit Requests</h5>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Proof</th>
                                <th>Status</th>
                                <th>Requested At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            <?php foreach ($deposits as $d): ?>
                                <tr>
                                    <td>
                                        <strong class="text-break d-block"><?php echo htmlspecialchars($d['user_name']); ?></strong><br>
                                        <small class="text-muted text-break"><?php echo htmlspecialchars($d['user_email']); ?></small>
                                    </td>
                                    <td>
                                        <span class="text-success fw-bold"><?php echo format_usd($d['amount']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($d['method']); ?></td>
                                    <td>
                                        <?php if ($d['proof_image']): ?>
                                            <a href="<?php echo SITE_URL . '/' . $d['proof_image']; ?>" target="_blank">
                                                <img src="<?php echo SITE_URL . '/' . $d['proof_image']; ?>" alt="Proof" class="rounded" width="40" height="40" style="object-fit: cover;">
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($d['status'] === 'pending'): ?>
                                            <span class="badge bg-label-warning">Pending</span>
                                        <?php elseif ($d['status'] === 'approved'): ?>
                                            <span class="badge bg-label-success">Approved</span>
                                        <?php else: ?>
                                            <span class="badge bg-label-danger">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($d['created_at'])); ?></td>
                                    <td>
                                        <?php if ($d['status'] === 'pending'): ?>
                                            <a href="approvals.php#navs-deposits" class="badge bg-label-primary"><i class="bx bx-cog me-1"></i>Process Request</a>
                                        <?php else: ?>
                                            <span class="text-muted fw-semibold">Processed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
