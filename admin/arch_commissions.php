<?php
// admin/arch_commissions.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'Level Commissions Architecture';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'save_commissions') {
            $node_id = (int)$_POST['node_id'];
            $pdo->prepare("DELETE FROM node_level_commissions WHERE node_id = ?")->execute([$node_id]);
            if (isset($_POST['commissions'])) {
                foreach ($_POST['commissions'] as $level => $pct) {
                    if ($pct > 0) {
                        $pdo->prepare("INSERT INTO node_level_commissions (node_id, level, commission_pct) VALUES (?, ?, ?)")
                            ->execute([$node_id, $level, $pct]);
                    }
                }
            }
            $message = "Commissions updated for node!";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

$nodes = $pdo->query("SELECT * FROM nodes ORDER BY node_number ASC")->fetchAll();
$all_commissions = $pdo->query("SELECT * FROM node_level_commissions")->fetchAll();
$node_commissions = [];
foreach ($all_commissions as $c) $node_commissions[$c['node_id']][$c['level']] = $c['commission_pct'];

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">System Architecture /</span> Level Commissions</h4>

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
                                <span class="badge bg-label-primary">Level Referral Commission</span>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="save_commissions">
                                    <input type="hidden" name="node_id" value="<?php echo $n['id']; ?>">
                                    <div class="row g-3">
                                        <?php for ($l = 1; $l <= $n['levels_unlocked']; $l++): ?>
                                            <div class="col-md-6">
                                                <div class="input-group">
                                                    <span class="input-group-text">Level <?php echo $l; ?></span>
                                                    <input type="number" step="0.01" name="commissions[<?php echo $l; ?>]" class="form-control" value="<?php echo $node_commissions[$n['id']][$l] ?? '0.00'; ?>">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="mt-4 text-end">
                                        <button type="submit" class="btn btn-primary">Save Commissions</button>
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
