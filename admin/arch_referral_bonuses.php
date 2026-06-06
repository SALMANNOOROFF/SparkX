<?php
// admin/arch_referral_bonuses.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'One-time Referral Bonuses';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'save_bonuses') {
            $node_id = (int)$_POST['node_id'];
            $direct = (float)$_POST['direct_bonus_pct'];
            $indirect = (float)$_POST['indirect_bonus_pct'];

            $stmt = $pdo->prepare("SELECT id FROM node_referral_bonuses WHERE node_id = ?");
            $stmt->execute([$node_id]);
            if ($stmt->fetch()) {
                $pdo->prepare("UPDATE node_referral_bonuses SET direct_bonus_pct = ?, indirect_bonus_pct = ? WHERE node_id = ?")
                    ->execute([$direct, $indirect, $node_id]);
            } else {
                $pdo->prepare("INSERT INTO node_referral_bonuses (node_id, direct_bonus_pct, indirect_bonus_pct) VALUES (?, ?, ?)")
                    ->execute([$node_id, $direct, $indirect]);
            }
            $message = "Referral bonuses updated successfully!";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

$nodes = $pdo->query("SELECT * FROM nodes ORDER BY node_number ASC")->fetchAll();
$all_bonuses = $pdo->query("SELECT * FROM node_referral_bonuses")->fetchAll();
$node_bonuses = [];
foreach ($all_bonuses as $b) {
    $node_bonuses[$b['node_id']] = $b;
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">System Architecture /</span> One-time Referral Bonuses</h4>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible" role="alert"><?php echo $message; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible" role="alert"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <div class="row">
                <?php foreach ($nodes as $n): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center border-bottom mb-3">
                                <h5 class="mb-0"><?php echo htmlspecialchars($n['name']); ?> (RS <?php echo number_format($n['amount']); ?>)</h5>
                                <span class="badge bg-label-info">Instant Bonus</span>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="save_bonuses">
                                    <input type="hidden" name="node_id" value="<?php echo $n['id']; ?>">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Direct Referral Bonus (%)</label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" name="direct_bonus_pct" class="form-control" value="<?php echo $node_bonuses[$n['id']]['direct_bonus_pct'] ?? '0.00'; ?>">
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <small class="text-muted">Paid when a direct referral invests.</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Indirect Referral Bonus (%)</label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" name="indirect_bonus_pct" class="form-control" value="<?php echo $node_bonuses[$n['id']]['indirect_bonus_pct'] ?? '0.00'; ?>">
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <small class="text-muted">Paid to upline (L2+) on investment.</small>
                                        </div>
                                    </div>
                                    <div class="mt-4 text-end">
                                        <button type="submit" class="btn btn-primary">Save Bonus Settings</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
