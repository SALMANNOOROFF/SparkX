<?php
// admin/arch_milestones.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'Weekly Milestones Architecture';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'save_milestones') {
            $node_id = (int)$_POST['node_id'];
            $pdo->prepare("DELETE FROM node_milestone_rewards WHERE node_id = ?")->execute([$node_id]);
            if (isset($_POST['milestones'])) {
                foreach ($_POST['milestones'] as $m) {
                    if (!empty($m['week']) && !empty($m['pct'])) {
                        $pdo->prepare("INSERT INTO node_milestone_rewards (node_id, at_week, reward_pct) VALUES (?, ?, ?)")
                            ->execute([$node_id, $m['week'], $m['pct']]);
                    }
                }
            }
            $message = "Milestones updated for node!";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

$nodes = $pdo->query("SELECT * FROM nodes ORDER BY node_number ASC")->fetchAll();
$all_milestones = $pdo->query("SELECT * FROM node_milestone_rewards ORDER BY at_week ASC")->fetchAll();
$node_milestones = [];
foreach ($all_milestones as $m) $node_milestones[$m['node_id']][] = $m;

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">System Architecture /</span> Weekly Milestones</h4>

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
                                <h5 class="mb-0"><?php echo htmlspecialchars($n['name']); ?> — Weekly Bonus Milestones</h5>
                                <span class="badge bg-label-success">Bonus Reward</span>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="save_milestones">
                                    <input type="hidden" name="node_id" value="<?php echo $n['id']; ?>">
                                    <div class="milestone-list-<?php echo $n['id']; ?>">
                                        <?php 
                                        $m_list = $node_milestones[$n['id']] ?? [];
                                        foreach ($m_list as $idx => $m): 
                                        ?>
                                            <div class="row g-2 mb-2 m-row">
                                                <div class="col-5">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">Week</span>
                                                        <input type="number" name="milestones[<?php echo $idx; ?>][week]" class="form-control" value="<?php echo $m['at_week']; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-5">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" step="0.01" name="milestones[<?php echo $idx; ?>][pct]" class="form-control" value="<?php echo $m['reward_pct']; ?>">
                                                        <span class="input-group-text">% Bonus</span>
                                                    </div>
                                                </div>
                                                <div class="col-2">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.m-row').remove()"><i class="bx bx-trash"></i></button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-info mt-2" onclick="addMRow(<?php echo $n['id']; ?>)"><i class="bx bx-plus me-1"></i> Add Milestone</button>
                                    
                                    <div class="mt-4 text-end">
                                        <button type="submit" class="btn btn-primary">Save Milestones</button>
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

<script>
function addMRow(nId) {
    const cont = document.querySelector('.milestone-list-' + nId);
    const count = cont.querySelectorAll('.m-row').length;
    const div = document.createElement('div');
    div.className = 'row g-2 mb-2 m-row';
    div.innerHTML = `
        <div class="col-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text">Week</span>
                <input type="number" name="milestones[${count}][week]" class="form-control">
            </div>
        </div>
        <div class="col-5">
            <div class="input-group input-group-sm">
                <input type="number" step="0.01" name="milestones[${count}][pct]" class="form-control">
                <span class="input-group-text">% Bonus</span>
            </div>
        </div>
        <div class="col-2">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.m-row').remove()"><i class="bx bx-trash"></i></button>
        </div>
    `;
    cont.appendChild(div);
}
</script>

<?php include 'includes/footer.php'; ?>
