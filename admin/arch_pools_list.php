<?php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'Pools Architecture';
$message = '';
$error = '';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS pools (
            id INT(11) NOT NULL AUTO_INCREMENT,
            pool_number INT(11) NOT NULL,
            name VARCHAR(100) NOT NULL,
            bonus_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            nodes_count INT(11) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_pool_number (pool_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $col = $pdo->query("SHOW COLUMNS FROM pools LIKE 'nodes_count'")->fetch();
    if (!$col) {
        $pdo->exec("ALTER TABLE pools ADD COLUMN nodes_count INT(11) NOT NULL DEFAULT 0 AFTER bonus_amount");
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS pool_node_requirements (
            id INT(11) NOT NULL AUTO_INCREMENT,
            pool_id INT(11) NOT NULL,
            node_id INT(11) NOT NULL,
            required_direct_referrals INT(11) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_pool_node (pool_id, node_id),
            KEY idx_pool_id (pool_id),
            KEY idx_node_id (node_id),
            CONSTRAINT fk_pool_node_pool FOREIGN KEY (pool_id) REFERENCES pools(id) ON DELETE CASCADE,
            CONSTRAINT fk_pool_node_node FOREIGN KEY (node_id) REFERENCES nodes(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}

function hk_sync_pool_nodes(PDO $pdo, int $poolId, int $nodesCount): void
{
    if ($nodesCount < 0) $nodesCount = 0;
    if ($nodesCount > 10) $nodesCount = 10;

    if ($nodesCount === 0) {
        $pdo->prepare("DELETE FROM pool_node_requirements WHERE pool_id = ?")->execute([$poolId]);
        return;
    }

    $nodes = $pdo->prepare("SELECT id FROM nodes ORDER BY node_number ASC LIMIT $nodesCount");
    $nodes->execute();
    $nodeIds = array_map('intval', $nodes->fetchAll(PDO::FETCH_COLUMN));

    if (count($nodeIds) === 0) {
        $pdo->prepare("DELETE FROM pool_node_requirements WHERE pool_id = ?")->execute([$poolId]);
        return;
    }

    $placeholders = implode(',', array_fill(0, count($nodeIds), '?'));
    $params = array_merge([$poolId], $nodeIds);
    $pdo->prepare("DELETE FROM pool_node_requirements WHERE pool_id = ? AND node_id NOT IN ($placeholders)")
        ->execute($params);

    $stmtInsert = $pdo->prepare("
        INSERT INTO pool_node_requirements (pool_id, node_id, required_direct_referrals)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE required_direct_referrals = required_direct_referrals
    ");
    foreach ($nodeIds as $nodeId) {
        $stmtInsert->execute([$poolId, $nodeId, 0]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save_pool') {
            $id = (int)($_POST['id'] ?? 0);
            $pool_number = (int)($_POST['pool_number'] ?? 0);
            $name = trim((string)($_POST['name'] ?? ''));
            $bonus_amount = (float)($_POST['bonus_amount'] ?? 0);
            $nodes_count = (int)($_POST['nodes_count'] ?? 0);
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if ($pool_number <= 0) {
                throw new Exception("Pool number is required.");
            }
            if ($name === '') {
                $name = "Pool " . $pool_number;
            }
            if ($nodes_count < 0) $nodes_count = 0;
            if ($nodes_count > 10) $nodes_count = 10;

            $pdo->beginTransaction();
            if ($id > 0) {
                $pdo->prepare("UPDATE pools SET pool_number = ?, name = ?, bonus_amount = ?, nodes_count = ?, is_active = ? WHERE id = ?")
                    ->execute([$pool_number, $name, $bonus_amount, $nodes_count, $is_active, $id]);
                hk_sync_pool_nodes($pdo, $id, $nodes_count);
            } else {
                $pdo->prepare("INSERT INTO pools (pool_number, name, bonus_amount, nodes_count, is_active) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$pool_number, $name, $bonus_amount, $nodes_count, $is_active]);
                $newId = (int)$pdo->lastInsertId();
                hk_sync_pool_nodes($pdo, $newId, $nodes_count);
            }
            $pdo->commit();

            $message = "Pool saved successfully!";
        } elseif ($action === 'delete_pool') {
            $id = (int)($_POST['id'] ?? 0);
            $pdo->prepare("DELETE FROM pools WHERE id = ?")->execute([$id]);
            $message = "Pool deleted!";
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Error: " . $e->getMessage();
    }
}

$pools = [];
if (!$error) {
    $pools = $pdo->query("SELECT * FROM pools ORDER BY pool_number ASC")->fetchAll();
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">System Architecture /</span> Pools</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#poolModal" onclick="clearPoolModal()">
                    <i class="bx bx-plus me-1"></i> Add Pool
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible" role="alert"><?php echo $message; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible" role="alert"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <div class="card">
                <h5 class="card-header">Pools Configuration</h5>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Pool #</th>
                                <th>Name</th>
                                <th>Bonus (USD)</th>
                                <th>Nodes</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pools as $p): ?>
                                <tr>
                                    <td><strong><?php echo (int)$p['pool_number']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                                    <td>$<?php echo number_format((float)$p['bonus_amount'], 2); ?></td>
                                    <td><?php echo (int)($p['nodes_count'] ?? 0); ?></td>
                                    <td><span class="badge bg-label-<?php echo (int)$p['is_active'] === 1 ? 'success' : 'danger'; ?>"><?php echo (int)$p['is_active'] === 1 ? 'Active' : 'Disabled'; ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary me-2" onclick="editPool(<?php echo htmlspecialchars(json_encode($p)); ?>)"><i class="bx bx-edit-alt"></i> Edit</button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deletePool(<?php echo (int)$p['id']; ?>)"><i class="bx bx-trash"></i> Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($pools) === 0): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No pools found. Add Pool to start.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="poolModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <input type="hidden" name="action" value="save_pool">
            <input type="hidden" name="id" id="pool-id">
            <div class="modal-header">
                <h5 class="modal-title" id="pool-title">Add Pool</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-4">
                        <label class="form-label">Pool #</label>
                        <input type="number" name="pool_number" id="pool-number" class="form-control" min="1" required>
                    </div>
                    <div class="col-8">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="pool-name" class="form-control" placeholder="e.g. Pool 1">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Bonus Amount (USD)</label>
                        <input type="number" step="0.01" name="bonus_amount" id="pool-bonus" class="form-control" value="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Nodes in Pool</label>
                        <input type="number" name="nodes_count" id="pool-nodes-count" class="form-control" min="0" max="10" value="0">
                    </div>
                    <div class="col-6 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="pool-active" checked>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary px-5">Save Pool</button>
            </div>
        </form>
    </div>
</div>

<form id="del-pool-form" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete_pool">
    <input type="hidden" name="id" id="del-pool-id">
</form>

<script>
function clearPoolModal() {
    document.getElementById('pool-id').value = '';
    document.getElementById('pool-title').innerText = 'Add Pool';
    document.getElementById('pool-number').value = '';
    document.getElementById('pool-name').value = '';
    document.getElementById('pool-bonus').value = '0';
    document.getElementById('pool-nodes-count').value = '0';
    document.getElementById('pool-active').checked = true;
}

function editPool(pool) {
    document.getElementById('pool-id').value = pool.id;
    document.getElementById('pool-title').innerText = 'Edit Pool';
    document.getElementById('pool-number').value = pool.pool_number;
    document.getElementById('pool-name').value = pool.name;
    document.getElementById('pool-bonus').value = pool.bonus_amount;
    document.getElementById('pool-nodes-count').value = pool.nodes_count || 0;
    document.getElementById('pool-active').checked = parseInt(pool.is_active) === 1;
    new bootstrap.Modal(document.getElementById('poolModal')).show();
}

function deletePool(id) {
    if (confirm('Delete this pool?')) {
        document.getElementById('del-pool-id').value = id;
        document.getElementById('del-pool-form').submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
