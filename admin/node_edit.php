<?php
// admin/node_edit.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$id = (int)($_GET['id'] ?? 0);
$node = $pdo->prepare("SELECT * FROM nodes WHERE id = ?");
$node->execute([$id]);
$node = $node->fetch();

if (!$node) {
    redirect('architecture.php');
}

$page_title = 'Edit Node: ' . $node['name'];
$message = '';

// Fetch related data
$plan = $pdo->prepare("SELECT * FROM investment_plans WHERE node_id = ?");
$plan->execute([$id]);
$plan = $plan->fetch();

$milestones = $pdo->prepare("SELECT * FROM node_milestone_rewards WHERE node_id = ? ORDER BY at_week ASC");
$milestones->execute([$id]);
$milestones = $milestones->fetchAll();

$referral_bonus = $pdo->prepare("SELECT * FROM node_referral_bonuses WHERE node_id = ?");
$referral_bonus->execute([$id]);
$referral_bonus = $referral_bonus->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        // 1. Update Node
        $pdo->prepare("UPDATE nodes SET name = ?, amount = ?, levels_unlocked = ?, is_active = ? WHERE id = ?")
            ->execute([$_POST['name'], $_POST['amount'], $_POST['levels_unlocked'], $_POST['is_active'], $id]);
            
        // 2. Update Plan
        if ($plan) {
            $pdo->prepare("UPDATE investment_plans SET week1_rate = ?, rate_increment = ?, duration_weeks = ? WHERE node_id = ?")
                ->execute([$_POST['week1_rate'], $_POST['rate_increment'], $_POST['duration_weeks'], $id]);
        }
        
        // 3. Update Referral Bonus
        if ($referral_bonus) {
            $pdo->prepare("UPDATE node_referral_bonuses SET direct_bonus_pct = ?, indirect_bonus_pct = ? WHERE node_id = ?")
                ->execute([$_POST['direct_bonus_pct'], $_POST['indirect_bonus_pct'], $id]);
        }
        
        // 4. Update Milestones (Delete & Re-insert)
        $pdo->prepare("DELETE FROM node_milestone_rewards WHERE node_id = ?")->execute([$id]);
        if (isset($_POST['milestones'])) {
            foreach ($_POST['milestones'] as $m) {
                if (!empty($m['at_week']) && !empty($m['reward_pct'])) {
                    $pdo->prepare("INSERT INTO node_milestone_rewards (node_id, at_week, reward_pct) VALUES (?, ?, ?)")
                        ->execute([$id, $m['at_week'], $m['reward_pct']]);
                }
            }
        }
        
        $pdo->commit();
        $message = "Node data updated successfully!";
        
        // Refresh data
        $node = $pdo->prepare("SELECT * FROM nodes WHERE id = ?");
        $node->execute([$id]);
        $node = $node->fetch();
        
        $plan = $pdo->prepare("SELECT * FROM investment_plans WHERE node_id = ?");
        $plan->execute([$id]);
        $plan = $plan->fetch();
        
        $milestones = $pdo->prepare("SELECT * FROM node_milestone_rewards WHERE node_id = ? ORDER BY at_week ASC");
        $milestones->execute([$id]);
        $milestones = $milestones->fetchAll();
        
        $referral_bonus = $pdo->prepare("SELECT * FROM node_referral_bonuses WHERE node_id = ?");
        $referral_bonus->execute([$id]);
        $referral_bonus = $referral_bonus->fetch();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Architecture /</span> Edit Node: <?php echo $node['name']; ?></h4>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="row">
                    <!-- Node Details Card -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <h5 class="card-header">Node Basic Info</h5>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Node Name</label>
                                    <input class="form-control" type="text" name="name" value="<?php echo htmlspecialchars($node['name']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Investment Amount (RS)</label>
                                    <input class="form-control" type="number" step="0.01" name="amount" value="<?php echo htmlspecialchars($node['amount']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Levels Unlocked</label>
                                    <input class="form-control" type="number" name="levels_unlocked" value="<?php echo htmlspecialchars($node['levels_unlocked']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="is_active">
                                        <option value="1" <?php echo $node['is_active'] ? 'selected' : ''; ?>>Active</option>
                                        <option value="0" <?php echo !$node['is_active'] ? 'selected' : ''; ?>>Disabled</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Investment Plan Card -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <h5 class="card-header">Investment Plan (Earning Rates)</h5>
                            <div class="card-body">
                                <?php if ($plan): ?>
                                    <div class="mb-3">
                                        <label class="form-label">Week 1 Rate (%)</label>
                                        <input class="form-control" type="number" step="0.01" name="week1_rate" value="<?php echo htmlspecialchars($plan['week1_rate']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Weekly Increment (%)</label>
                                        <input class="form-control" type="number" step="0.01" name="rate_increment" value="<?php echo htmlspecialchars($plan['rate_increment']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Duration (Weeks)</label>
                                        <input class="form-control" type="number" name="duration_weeks" value="<?php echo htmlspecialchars($plan['duration_weeks']); ?>">
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning small">No investment plan linked to this node.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Referral Bonus Card -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <h5 class="card-header">Referral Bonuses</h5>
                            <div class="card-body">
                                <?php if ($referral_bonus): ?>
                                    <div class="mb-3">
                                        <label class="form-label">Direct Bonus (%)</label>
                                        <input class="form-control" type="number" step="0.01" name="direct_bonus_pct" value="<?php echo htmlspecialchars($referral_bonus['direct_bonus_pct']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Indirect Bonus (%)</label>
                                        <input class="form-control" type="number" step="0.01" name="indirect_bonus_pct" value="<?php echo htmlspecialchars($referral_bonus['indirect_bonus_pct']); ?>">
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning small">No referral bonus data for this node.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Milestone Rewards Card -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <h5 class="card-header">Milestone Rewards (Bonuses)</h5>
                            <div class="card-body">
                                <div id="milestone-container">
                                    <?php foreach ($milestones as $i => $m): ?>
                                        <div class="row mb-2 milestone-row">
                                            <div class="col-5">
                                                <input class="form-control" type="number" name="milestones[<?php echo $i; ?>][at_week]" value="<?php echo $m['at_week']; ?>" placeholder="Week #">
                                            </div>
                                            <div class="col-5">
                                                <input class="form-control" type="number" step="0.01" name="milestones[<?php echo $i; ?>][reward_pct]" value="<?php echo $m['reward_pct']; ?>" placeholder="Bonus %">
                                            </div>
                                            <div class="col-2">
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-milestone"><i class="bx bx-trash"></i></button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-milestone"><i class="bx bx-plus me-1"></i> Add Milestone</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-2 text-end">
                    <button type="submit" class="btn btn-primary btn-lg px-5">SAVE CHANGES</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let milestoneCount = <?php echo count($milestones); ?>;
    
    document.getElementById('add-milestone').addEventListener('click', function() {
        const container = document.getElementById('milestone-container');
        const row = document.createElement('div');
        row.className = 'row mb-2 milestone-row';
        row.innerHTML = `
            <div class="col-5">
                <input class="form-control" type="number" name="milestones[${milestoneCount}][at_week]" placeholder="Week #">
            </div>
            <div class="col-5">
                <input class="form-control" type="number" step="0.01" name="milestones[${milestoneCount}][reward_pct]" placeholder="Bonus %">
            </div>
            <div class="col-2">
                <button type="button" class="btn btn-outline-danger btn-sm remove-milestone"><i class="bx bx-trash"></i></button>
            </div>
        `;
        container.appendChild(row);
        milestoneCount++;
    });
    
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-milestone') || e.target.parentElement.classList.contains('remove-milestone')) {
            const row = e.target.closest('.milestone-row');
            if (row) row.remove();
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
