<?php
// admin/arch_nodes.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'Investment Nodes Architecture';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'save_node') {
            $id = (int)($_POST['id'] ?? 0);
            $num = (int)$_POST['node_number'];
            $name = $_POST['name'];
            $amount = (float)$_POST['amount'];
            $levels = (int)$_POST['levels_unlocked'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if ($id > 0) {
                $pdo->prepare("UPDATE nodes SET node_number = ?, name = ?, amount = ?, levels_unlocked = ?, is_active = ? WHERE id = ?")
                    ->execute([$num, $name, $amount, $levels, $is_active, $id]);
            } else {
                $pdo->prepare("INSERT INTO nodes (node_number, name, amount, levels_unlocked, is_active) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$num, $name, $amount, $levels, $is_active]);
            }
            $message = "Node saved successfully!";
        }
        elseif ($action === 'delete_node') {
            $id = (int)$_POST['id'];
            $pdo->prepare("DELETE FROM nodes WHERE id = ?")->execute([$id]);
            $message = "Node removed!";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

$nodes = $pdo->query("SELECT * FROM nodes ORDER BY node_number ASC")->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">System Architecture /</span> Investment Nodes</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nodeModal" onclick="clearNodeModal()">
                    <i class="bx bx-plus me-1"></i> Add New Node
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible" role="alert"><?php echo $message; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible" role="alert"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <div class="card">
                <h5 class="card-header">Nodes & Plans Configuration</h5>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th># Order</th>
                                <th>Node Name</th>
                                <th>Amount (RS)</th>
                                <th>Levels Unlocked</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($nodes as $n): ?>
                                <tr>
                                    <td><strong><?php echo $n['node_number']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($n['name']); ?></td>
                                    <td>RS <?php echo number_format($n['amount']); ?></td>
                                    <td><?php echo $n['levels_unlocked']; ?></td>
                                    <td><span class="badge bg-label-<?php echo $n['is_active'] ? 'success' : 'danger'; ?>"><?php echo $n['is_active'] ? 'Active' : 'Disabled'; ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary me-2" onclick="editNode(<?php echo htmlspecialchars(json_encode($n)); ?>)"><i class="bx bx-edit-alt"></i> Edit</button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteNode(<?php echo $n['id']; ?>)"><i class="bx bx-trash"></i> Delete</button>
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

<!-- MODALS -->
<div class="modal fade" id="nodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <input type="hidden" name="action" value="save_node">
            <input type="hidden" name="id" id="n-id">
            <div class="modal-header">
                <h5 class="modal-title" id="n-title">Add New Node</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-4">
                        <label class="form-label">Order #</label>
                        <input type="number" name="node_number" id="n-num" class="form-control" required>
                    </div>
                    <div class="col-8">
                        <label class="form-label">Node Name</label>
                        <input type="text" name="name" id="n-name" class="form-control" placeholder="e.g. Gold Node" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Investment Amount (RS)</label>
                        <input type="number" name="amount" id="n-amount" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Levels Unlocked</label>
                        <input type="number" name="levels_unlocked" id="n-levels" class="form-control" value="1" required>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" id="n-active" checked>
                            <label class="form-check-label">Enable Node</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary px-5">Save Node Architecture</button>
            </div>
        </form>
    </div>
</div>

<form id="del-node-form" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete_node">
    <input type="hidden" name="id" id="del-n-id">
</form>

<script>
function clearNodeModal() {
    document.getElementById('n-id').value = '';
    document.getElementById('n-title').innerText = 'Add New Node';
    document.getElementById('n-num').value = '';
    document.getElementById('n-name').value = '';
    document.getElementById('n-amount').value = '';
    document.getElementById('n-levels').value = '1';
    document.getElementById('n-active').checked = true;
}

function editNode(n) {
    document.getElementById('n-id').value = n.id;
    document.getElementById('n-title').innerText = 'Edit Node Architecture';
    document.getElementById('n-num').value = n.node_number;
    document.getElementById('n-name').value = n.name;
    document.getElementById('n-amount').value = n.amount;
    document.getElementById('n-levels').value = n.levels_unlocked;
    document.getElementById('n-active').checked = parseInt(n.is_active) === 1;
    new bootstrap.Modal(document.getElementById('nodeModal')).show();
}

function deleteNode(id) {
    if (confirm('Are you sure you want to delete this node? This will remove associated plans!')) {
        document.getElementById('del-n-id').value = id;
        document.getElementById('del-node-form').submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
