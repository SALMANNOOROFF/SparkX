<?php
// admin/user_edit.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$id = (int)($_GET['id'] ?? 0);
$user = $pdo->prepare("SELECT u.*, w.deposit_balance as balance, w.earning_balance, w.total_invested as total_earned, r.name as referrer_name, r.id as referrer_id 
                       FROM users u 
                       LEFT JOIN wallets w ON u.id = w.user_id 
                       LEFT JOIN users r ON u.referred_by = r.id
                       WHERE u.id = ?");
$user->execute([$id]);
$user = $user->fetch();

if (!$user) {
    redirect('users.php');
}

$page_title = 'Edit User: ' . $user['name'];
$message = '';
$error = '';
$default_avatar = get_setting($pdo, 'default_user_avatar', 'assets/images/user-avatar.png');

// Handle status toggle via AJAX/POST
if (isset($_POST['action']) && $_POST['action'] === 'update_user') {
    try {
        $pdo->beginTransaction();
        
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $balance = (float)$_POST['balance'];
        $earning_balance = (float)$_POST['earning_balance'];
        $total_earned = (float)$_POST['total_earned'];
        $is_active = (int)$_POST['is_active'];
        $is_verified = (int)$_POST['is_verified'];
        $admin_remarks = $_POST['admin_remarks'];

        $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, is_active = ?, is_verified = ?, admin_remarks = ? WHERE id = ?")
            ->execute([$name, $email, $phone, $is_active, $is_verified, $admin_remarks, $id]);
            
        // Use an UPSERT approach for wallets to ensure it exists
        $stmt_check = $pdo->prepare("SELECT user_id FROM wallets WHERE user_id = ?");
        $stmt_check->execute([$id]);
        if ($stmt_check->fetch()) {
            $pdo->prepare("UPDATE wallets SET deposit_balance = ?, earning_balance = ?, total_invested = ? WHERE user_id = ?")
                ->execute([$balance, $earning_balance, $total_earned, $id]);
        } else {
            $pdo->prepare("INSERT INTO wallets (user_id, deposit_balance, earning_balance, total_invested) VALUES (?, ?, ?, ?)")
                ->execute([$id, $balance, $earning_balance, $total_earned]);
        }

        // Add a transaction record for the balance update if it changed
        if ($balance != (float)$user['balance']) {
            $diff = $balance - (float)$user['balance'];
            $note = "Admin manual adjustment: " . ($diff > 0 ? "+" : "") . $diff . " PKR";
            $stmt_tx = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, status, description) VALUES (?, 'deposit', ?, 'completed', ?)");
            $stmt_tx->execute([$id, abs($diff), $note]);
        }

        $pdo->commit();
        $message = "User details updated successfully!";
        
        // Refresh data
        $user = $pdo->prepare("SELECT u.*, w.deposit_balance as balance, w.earning_balance, w.total_invested as total_earned, r.name as referrer_name, r.id as referrer_id 
                               FROM users u 
                               LEFT JOIN wallets w ON u.id = w.user_id 
                               LEFT JOIN users r ON u.referred_by = r.id
                               WHERE u.id = ?");
        $user->execute([$id]);
        $user = $user->fetch();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Investment Cancellation & Refund
if (isset($_POST['action']) && $_POST['action'] === 'cancel_investment') {
    $inv_id = (int)$_POST['investment_id'];
    try {
        $pdo->beginTransaction();

        // 1. Fetch investment details from correct investments table
        $stmt_inv = $pdo->prepare("SELECT * FROM investments WHERE id = ? AND status = 'active' AND user_id = ?");
        $stmt_inv->execute([$inv_id, $id]);
        $inv = $stmt_inv->fetch();

        if ($inv) {
            $refund_amount = (float)$inv['amount'];

            // 2. Mark as completed (deactivated)
            $pdo->prepare("UPDATE investments SET status = 'completed' WHERE id = ?")->execute([$inv_id]);

            // 3. Refund to Main Wallet
            $pdo->prepare("UPDATE wallets SET deposit_balance = deposit_balance + ? WHERE user_id = ?")->execute([$refund_amount, $id]);

            // 4. Log Transaction with correct columns and enum types
            $note = "Investment Plan #" . $inv['plan_id'] . " cancelled by admin. Capital refunded to Main Wallet.";
            $pdo->prepare("INSERT INTO transactions (user_id, type, amount, status, description) VALUES (?, 'deposit', ?, 'completed', ?)")
                ->execute([$id, $refund_amount, $note]);

            $pdo->commit();
            $message = "Investment cancelled and " . format_pkr($refund_amount) . " refunded to Main Wallet.";
        } else {
            $pdo->rollBack();
            $error = "Investment not found or already inactive.";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}



// Fetch Referrals using the referrals closure table (joining referee_id to users)
$referrals = $pdo->prepare("SELECT u.id, u.name, u.email, u.created_at 
                            FROM referrals r
                            JOIN users u ON r.referee_id = u.id 
                            WHERE r.referrer_id = ? AND r.level = 1");
$referrals->execute([$id]);
$referrals = $referrals->fetchAll();

// Fetch Active Investments from correct 'investments' table
$investments = $pdo->prepare("SELECT i.*, i.amount as amount_invested, p.name as node_name 
                             FROM investments i 
                             JOIN plans p ON i.plan_id = p.id 
                             WHERE i.user_id = ? AND i.status = 'active'");
$investments->execute([$id]);
$investments = $investments->fetchAll();



include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="admin-page-heading py-3 mb-4">
                <h4 class="fw-bold"><span class="text-muted fw-light">Users /</span> <?php echo htmlspecialchars($user['name']); ?></h4>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row admin-user-panels">
                <!-- User Profile Card -->
                <div class="col-md-5">
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <?php 
                            $profile_image = (!empty($user['profile_image'])) ? $user['profile_image'] : $default_avatar;
                            if (strpos($profile_image, 'http') === false) {
                                $profile_image = SITE_URL . '/' . $profile_image;
                            }
                            ?>
                            <img src="<?php echo $profile_image; ?>" alt="avatar" class="rounded-circle img-fluid mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                            <h5 class="my-3"><?php echo htmlspecialchars($user['name']); ?></h5>
                            <p class="text-muted mb-1"><?php echo htmlspecialchars($user['email']); ?></p>
                            <p class="text-muted mb-4"><?php echo htmlspecialchars($user['phone']); ?></p>
                            <div class="d-flex justify-content-center mb-2 flex-wrap">
                                <?php if ($user['is_active']): ?>
                                    <span class="badge bg-success">ACCOUNT ACTIVE</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">ACCOUNT DISABLED</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="card mb-4">
                        <div class="card-body admin-summary-list">
                            <h6 class="card-title">Financial Summary</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Current Balance:</span>
                                <span class="fw-bold text-primary"><?php echo format_pkr($user['balance'] ?? 0); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Earning Balance:</span>
                                <span class="fw-bold text-warning"><?php echo format_pkr($user['earning_balance'] ?? 0); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Earned:</span>
                                <span class="fw-bold text-success"><?php echo format_pkr($user['total_earned'] ?? 0); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Last Login:</span>
                                <span class="text-muted"><?php echo !empty($user['last_login']) ? date('d M Y, H:i', strtotime($user['last_login'])) : 'Never'; ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Referred By:</span>
                                <span>
                                    <?php if ($user['referrer_id']): ?>
                                        <a href="user_edit.php?id=<?php echo $user['referrer_id']; ?>"><?php echo htmlspecialchars($user['referrer_name']); ?></a>
                                    <?php else: ?>
                                        <span class="text-muted">None</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Details Form -->
                <div class="col-md-7">
                    <div class="card mb-4">
                        <h5 class="card-header">Edit User Details</h5>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_user">
                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input class="form-control" type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Email Address</label>
                                        <input class="form-control" type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Phone Number</label>
                                        <input class="form-control" type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Main Wallet Balance (PKR)</label>
                                        <input class="form-control" type="number" step="0.01" name="balance" value="<?php echo $user['balance'] ?? 0; ?>" required>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Earning Wallet Balance (PKR)</label>
                                        <input class="form-control" type="number" step="0.01" name="earning_balance" value="<?php echo $user['earning_balance'] ?? 0; ?>" required>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Total Earned (Lifetime)</label>
                                        <input class="form-control" type="number" step="0.01" name="total_earned" value="<?php echo $user['total_earned'] ?? 0; ?>" required>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Account Status</label>
                                        <select name="is_active" class="form-select">
                                            <option value="1" <?php echo $user['is_active'] ? 'selected' : ''; ?>>Active (Enabled)</option>
                                            <option value="0" <?php echo !$user['is_active'] ? 'selected' : ''; ?>>Disabled (Banned)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Verification</label>
                                        <select name="is_verified" class="form-select">
                                            <option value="1" <?php echo !empty($user['is_verified']) ? 'selected' : ''; ?>>Verified</option>
                                            <option value="0" <?php echo empty($user['is_verified']) ? 'selected' : ''; ?>>Not Verified</option>
                                        </select>
                                    </div>
                                    <div class="mb-3 col-12">
                                        <label class="form-label">Admin Remarks (Internal)</label>
                                        <textarea name="admin_remarks" class="form-control" rows="2"><?php echo htmlspecialchars($user['admin_remarks'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Referrals & Investments Row -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <h5 class="card-header d-flex justify-content-between align-items-center">
                            Referrals (Team)
                            <span class="badge bg-primary"><?php echo count($referrals); ?> Total</span>
                        </h5>
                        <div class="table-responsive text-nowrap" style="max-height: 300px;">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Joined</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($referrals as $ref): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ref['name']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($ref['created_at'])); ?></td>
                                            <td><a href="user_edit.php?id=<?php echo $ref['id']; ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($referrals)): ?>
                                        <tr><td colspan="3" class="text-center py-3">No referrals yet.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-4">
                        <h5 class="card-header d-flex justify-content-between align-items-center">
                            Active Plans
                            <span class="badge bg-success"><?php echo count($investments); ?> Active</span>
                        </h5>
                        <div class="table-responsive text-nowrap" style="max-height: 300px;">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Node</th>
                                        <th>Amount</th>
                                        <th>Week</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($investments as $inv): 
                                        // Calculate elapsed weeks dynamically from created_at
                                        $days = ceil((time() - strtotime($inv['created_at'])) / (24 * 3600));
                                        $current_week = ceil($days / 7);
                                        if ($current_week <= 0) $current_week = 1;
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($inv['node_name']); ?></td>
                                            <td><?php echo format_pkr($inv['amount_invested']); ?></td>
                                            <td>W<?php echo $current_week; ?></td>
                                            <td>
                                                <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this investment and refund capital to the main wallet?');">
                                                    <input type="hidden" name="action" value="cancel_investment">
                                                    <input type="hidden" name="investment_id" value="<?php echo $inv['id']; ?>">
                                                    <button type="submit" class="btn btn-xs btn-danger">Cancel</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($investments)): ?>
                                        <tr><td colspan="3" class="text-center py-3">No active investments.</td></tr>
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
