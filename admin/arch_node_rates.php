<?php
// admin/arch_node_rates.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'Node Weekly Rates Architecture';
$message = '';
$error = '';



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'delete_node') {
            $node_id = (int)$_POST['node_id'];
            $pdo->prepare("DELETE FROM nodes WHERE id = ?")->execute([$node_id]);
            $pdo->prepare("DELETE FROM node_weekly_rates WHERE node_id = ?")->execute([$node_id]);
            $pdo->prepare("DELETE FROM node_referral_bonuses WHERE node_id = ?")->execute([$node_id]);
            $message = "Node successfully deleted!";
        }
        elseif ($action === 'add_node') {
            $num = (int)$_POST['node_number'];
            $name = $_POST['name'];
            $amount = (float)$_POST['amount'];
            $levels = (int)$_POST['levels_unlocked'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            $pdo->prepare("INSERT INTO nodes (node_number, name, amount, levels_unlocked, is_active) VALUES (?, ?, ?, ?, ?)")
                ->execute([$num, $name, $amount, $levels, $is_active]);
            $message = "New node added successfully!";
        }
        elseif ($action === 'save_node_rates') {
            $node_id = (int)$_POST['node_id'];

            // Fetch the plan_id for this node, or create one if missing
            $stmt = $pdo->prepare("SELECT id FROM investment_plans WHERE node_id = ? LIMIT 1");
            $stmt->execute([$node_id]);
            $plan_id = (int)$stmt->fetchColumn();
            
            if (!$plan_id) {
                $pdo->prepare("INSERT INTO investment_plans (node_id, duration_weeks, week1_rate, rate_increment, is_active) VALUES (?, 50, 0, 0, 1)")->execute([$node_id]);
                $plan_id = (int)$pdo->lastInsertId();
            }

            // Delete existing rates for this node to update
            $pdo->prepare("DELETE FROM node_weekly_rates WHERE node_id = ?")->execute([$node_id]);
            
            if (isset($_POST['weeks'])) {
                foreach ($_POST['weeks'] as $week_num => $rate) {
                    if ($rate !== '') {
                        $pdo->prepare("INSERT INTO node_weekly_rates (node_id, plan_id, week_number, profit_pct) VALUES (?, ?, ?, ?)")
                            ->execute([$node_id, $plan_id, $week_num, $rate]);
                    }
                }
            }
            $message = "Weekly profit rates updated for the node!";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

$nodes = $pdo->query("SELECT * FROM nodes ORDER BY node_number ASC")->fetchAll();
$all_rates = $pdo->query("SELECT * FROM node_weekly_rates ORDER BY week_number ASC")->fetchAll();
$node_rates = [];
foreach ($all_rates as $r) {
    $node_rates[$r['node_id']][$r['week_number']] = $r['profit_pct'];
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">System Architecture /</span> Node Weekly Rates</h4>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible" role="alert"><?php echo $message; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible" role="alert"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header border-bottom">
                            <h5 class="mb-0">Select Node to Manage Rates</h5>
                        </div>
                        <div class="card-body py-3">
                            <div style="overflow-x: auto; white-space: nowrap; padding-bottom: 5px;">
                                <ul class="nav nav-pills flex-nowrap" role="tablist" style="display: inline-flex;">
                                    <?php foreach ($nodes as $index => $n): ?>
                                        <li class="nav-item me-2 mb-0">
                                            <button class="nav-link <?php echo $index === 0 ? 'active' : ''; ?> text-nowrap" data-bs-toggle="tab" data-bs-target="#node-tab-<?php echo $n['id']; ?>">
                                                <i class="bx bx-cube-alt me-1"></i> <?php echo htmlspecialchars($n['name']); ?>
                                            </button>
                                        </li>
                                    <?php endforeach; ?>
                                    <li class="nav-item mb-0">
                                        <button type="button" class="nav-link text-success text-nowrap fw-bold" style="border: 1px dashed #71dd37; background: rgba(113, 221, 55, 0.1);" data-bs-toggle="modal" data-bs-target="#addNodeModal">
                                            <i class="bx bx-plus me-1"></i> Add
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="tab-content p-0 shadow-none border-0">
                        <?php foreach ($nodes as $index => $n): ?>
                            <div class="tab-pane fade <?php echo $index === 0 ? 'show active' : ''; ?>" id="node-tab-<?php echo $n['id']; ?>">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center border-bottom mb-3 flex-wrap">
                                        <h5 class="mb-0 pe-2"><?php echo htmlspecialchars($n['name']); ?> — Weekly Profit Rates</h5>
                                        <div class="d-flex align-items-center mt-2 mt-sm-0">
                                            <span class="badge bg-label-primary">RS <?php echo number_format($n['amount']); ?></span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <input type="hidden" name="action" value="save_node_rates">
                                            <input type="hidden" name="node_id" value="<?php echo $n['id']; ?>">

                                            <h6 class="fw-bold mb-3 border-bottom pb-2 mt-3">Weekly Profit Distribution (%)</h6>
                                            <?php 
                                            $current_node_rates = $node_rates[$n['id']] ?? [];
                                            if (empty($current_node_rates)) {
                                                $current_node_rates[1] = '0.00';
                                            }
                                            ksort($current_node_rates);
                                            ?>
                                            <div class="row g-3" id="weekly-rates-container-<?php echo $n['id']; ?>">
                                                <?php foreach ($current_node_rates as $w => $rate): ?>
                                                    <div class="col-md-3 col-sm-6 week-box">
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text week-label">Week <?php echo $w; ?></span>
                                                            <input type="number" step="0.01" name="weeks[<?php echo $w; ?>]" class="form-control" value="<?php echo htmlspecialchars($rate); ?>">
                                                            <span class="input-group-text">%</span>
                                                            <button type="button" class="btn btn-outline-danger px-2" onclick="this.closest('.week-box').remove(); reindexWeeks(<?php echo $n['id']; ?>);"><i class="bx bx-x"></i></button>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            
                                            <div class="mt-3">
                                                <button type="button" class="btn btn-sm btn-outline-success" style="border: 1px dashed #71dd37;" onclick="addWeek(<?php echo $n['id']; ?>)">
                                                    <i class="bx bx-plus"></i> Add New Week
                                                </button>
                                            </div>
                                            
                                            <div class="mt-4 text-end">
                                                <button type="submit" class="btn btn-primary px-5">Save Rates for <?php echo htmlspecialchars($n['name']); ?></button>
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
    </div>
</div>

</div>

<!-- Add Node Modal -->
<div class="modal fade" id="addNodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <input type="hidden" name="action" value="add_node">
            <div class="modal-header">
                <h5 class="modal-title">Add New Node Quickly</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-4">
                        <label class="form-label">Order #</label>
                        <input type="number" name="node_number" class="form-control" required>
                    </div>
                    <div class="col-8">
                        <label class="form-label">Node Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Platinum Node" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Amount (RS)</label>
                        <input type="number" name="amount" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Levels Unlocked</label>
                        <input type="number" name="levels_unlocked" class="form-control" value="1" required>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" checked>
                            <label class="form-check-label">Enable Node Active</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary px-5">Add Node</button>
            </div>
        </form>
    </div>
</div>

<form id="del-node-rate-form" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete_node">
    <input type="hidden" name="node_id" id="del-nr-id">
</form>

<script>
function deleteNodeRate(id) {
    if (confirm('Are you sure you want to delete this node completely? This will delete all its rates and configuration!')) {
        document.getElementById('del-nr-id').value = id;
        document.getElementById('del-node-rate-form').submit();
    }
}

function reindexWeeks(nodeId) {
    let container = document.getElementById('weekly-rates-container-' + nodeId);
    let boxes = container.querySelectorAll('.week-box');
    boxes.forEach((box, index) => {
        let weekNum = index + 1;
        box.querySelector('.week-label').innerText = 'Week ' + weekNum;
        box.querySelector('input').name = 'weeks[' + weekNum + ']';
    });
}

function addWeek(nodeId) {
    let container = document.getElementById('weekly-rates-container-' + nodeId);
    let count = container.querySelectorAll('.week-box').length;
    let nextWeek = count + 1;
    let html = `
        <div class="col-md-3 col-sm-6 week-box">
            <div class="input-group input-group-sm">
                <span class="input-group-text week-label">Week ${nextWeek}</span>
                <input type="number" step="0.01" name="weeks[${nextWeek}]" class="form-control" value="0.00">
                <span class="input-group-text">%</span>
                <button type="button" class="btn btn-outline-danger px-2" onclick="this.closest('.week-box').remove(); reindexWeeks(${nodeId});"><i class="bx bx-x"></i></button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}
</script>

<?php include 'includes/footer.php'; ?>
