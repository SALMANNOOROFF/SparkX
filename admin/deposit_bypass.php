<?php
// admin/deposit_bypass.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'Deposit Bypass (Test Mode)';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'bypass_deposit') {
        $user_id = (int)$_POST['user_id'];
        $amount = (float)$_POST['amount'];

        if ($user_id > 0 && $amount > 0) {
            try {
                $pdo->beginTransaction();

                // 1. Update Wallets (deposit_balance)
                $stmt_wallet = $pdo->prepare("SELECT user_id FROM wallets WHERE user_id = ?");
                $stmt_wallet->execute([$user_id]);
                if ($stmt_wallet->fetch()) {
                    $pdo->prepare("UPDATE wallets SET deposit_balance = deposit_balance + ? WHERE user_id = ?")->execute([$amount, $user_id]);
                } else {
                    $pdo->prepare("INSERT INTO wallets (user_id, deposit_balance) VALUES (?, ?)")->execute([$user_id, $amount]);
                }

                // 2. Create Transaction Log
                $stmt_tx = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, status) VALUES (?, 'deposit', ?, 'completed')");
                $stmt_tx->execute([$user_id, $amount]);

                $pdo->commit();
                $message = "Deposit of " . format_usd($amount) . " successfully bypassed for User ID: " . $user_id;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Error: " . $e->getMessage();
            }
        } else {
            $error = "Please select a valid user and enter an amount.";
        }
    } elseif ($_POST['action'] === 'bypass_plan') {
        $user_id = (int)$_POST['user_id'];
        $plan_id = (int)$_POST['plan_id'];

        if ($user_id > 0 && $plan_id > 0) {
            try {
                $pdo->beginTransaction();

                // Fetch plan details
                $stmt_plan = $pdo->prepare("SELECT * FROM plans WHERE id = ?");
                $stmt_plan->execute([$plan_id]);
                $plan = $stmt_plan->fetch();

                if ($plan) {
                    $amount = (float)$plan['min_investment'];

                    // 1. Insert into investments
                    $stmt_inv = $pdo->prepare("INSERT INTO investments (user_id, plan_id, amount, status) VALUES (?, ?, ?, 'active')");
                    $stmt_inv->execute([$user_id, $plan_id, $amount]);

                    // 2. Log Transaction
                    $stmt_tx = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, status) VALUES (?, 'investment', ?, 'completed')");
                    $stmt_tx->execute([$user_id, $amount]);

                    $pdo->commit();
                    $message = "Plan '" . $plan['name'] . "' successfully activated for User ID: " . $user_id;
                } else {
                    $error = "Plan details not found.";
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Error: " . $e->getMessage();
            }
        } else {
            $error = "Please select a valid user and plan.";
        }
    }
}

$users = $pdo->query("SELECT id, name, email, phone FROM users ORDER BY name ASC")->fetchAll();
$plans_list = $pdo->query("SELECT id, name, min_investment FROM plans WHERE status = 'active' ORDER BY min_investment ASC")->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="admin-page-heading py-3 mb-4">
                <h4 class="fw-bold"><span class="text-muted fw-light">Finance /</span> Deposit Bypass</h4>
                <p class="text-muted">Use this tool to manually add balance to any user's wallet for testing or adjustment purposes.</p>
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

            <div class="row">
                <div class="col-md-6">
                    <!-- Deposit Bypass Card -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Manual Recharge Form</h5>
                            <small class="text-primary fw-bold">Balance Only</small>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="bypass_deposit">
                                
                                <div class="mb-3">
                                    <label class="form-label">Select User</label>
                                    <select name="user_id" class="form-select select2" required>
                                        <option value="">-- Choose Customer --</option>
                                        <?php foreach ($users as $u): ?>
                                            <option value="<?php echo $u['id']; ?>">
                                                <?php echo htmlspecialchars($u['name']); ?> 
                                                (<?php echo htmlspecialchars($u['email'] ?: $u['phone']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Amount (PKR Base)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><?php echo get_setting($pdo, 'currency_symbol', 'RS'); ?></span>
                                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Admin Note</label>
                                    <textarea name="note" class="form-control" rows="2" placeholder="Reason for bypass"></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">PROCESS BYPASS RECHARGE</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <!-- Plan Bypass Card -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Manual Plan Activation</h5>
                            <small class="text-success fw-bold">Direct Plan</small>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="bypass_plan">
                                
                                <div class="mb-3">
                                    <label class="form-label">Select User</label>
                                    <select name="user_id" class="form-select select2" required>
                                        <option value="">-- Choose Customer --</option>
                                        <?php foreach ($users as $u): ?>
                                            <option value="<?php echo $u['id']; ?>">
                                                <?php echo htmlspecialchars($u['name']); ?> 
                                                (<?php echo htmlspecialchars($u['email'] ?: $u['phone']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Select Plan</label>
                                    <select name="plan_id" class="form-select" required>
                                        <option value="">-- Choose Plan --</option>
                                        <?php foreach ($plans_list as $p): ?>
                                            <option value="<?php echo $p['id']; ?>">
                                                <?php echo htmlspecialchars($p['name']); ?> 
                                                (<?php echo format_usd($p['min_investment']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="alert alert-info border-0 small mb-4">
                                    <i class="bx bx-info-circle me-1"></i> 
                                    This will directly activate the selected plan for the user without deducting balance.
                                </div>

                                <button type="submit" class="btn btn-success w-100">ACTIVATE PLAN NOW</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Bypass System Instructions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="bx bx-wallet text-primary me-2"></i>Recharge Bypass</h6>
                                    <ul class="small text-muted">
                                        <li>Directly adds balance to user wallet.</li>
                                        <li>Creates a 'deposit' transaction record.</li>
                                        <li>Adds a payment record marked as 'admin_bypass'.</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="bx bx-bolt-circle text-success me-2"></i>Plan Bypass</h6>
                                    <ul class="small text-muted">
                                        <li>Directly inserts into <code>user_investments</code>.</li>
                                        <li>User starts receiving profit immediately.</li>
                                        <li>Creates an 'investment' transaction record for audit.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
