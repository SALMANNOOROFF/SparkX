<?php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

redirect(ADMIN_URL . '/arch_pools_list.php');

$page_title = 'Pool Invitation Rewards';
$message = '';
$error = '';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS pools (
            id INT(11) NOT NULL AUTO_INCREMENT,
            pool_number INT(11) NOT NULL,
            name VARCHAR(100) NOT NULL,
            bonus_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_pool_number (pool_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save_pool') {
            $id = (int)($_POST['id'] ?? 0);
            $pool_number = (int)($_POST['pool_number'] ?? 0);
            $name = trim((string)($_POST['name'] ?? ''));
            $bonus_amount = (float)($_POST['bonus_amount'] ?? 0);
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if ($pool_number <= 0) {
                throw new Exception("Pool number is required.");
            }
            if ($name === '') {
                $name = "Pool " . $pool_number;
            }

            if ($id > 0) {
                $pdo->prepare("UPDATE pools SET pool_number = ?, name = ?, bonus_amount = ?, is_active = ? WHERE id = ?")
                    ->execute([$pool_number, $name, $bonus_amount, $is_active, $id]);
            } else {
                $pdo->prepare("INSERT INTO pools (pool_number, name, bonus_amount, is_active) VALUES (?, ?, ?, ?)")
                    ->execute([$pool_number, $name, $bonus_amount, $is_active]);
            }

            $message = "Pool saved successfully!";
        } elseif ($action === 'delete_pool') {
            $id = (int)($_POST['id'] ?? 0);
            $pdo->prepare("DELETE FROM pools WHERE id = ?")->execute([$id]);
            $message = "Pool deleted!";
        } elseif ($action === 'save_requirements') {
            $pool_id = (int)($_POST['pool_id'] ?? 0);
            $requirements = $_POST['requirements'] ?? [];

            if ($pool_id <= 0) {
                throw new Exception("Invalid pool.");
            }
            if (!is_array($requirements)) {
                $requirements = [];
            }

            $node_ids_to_keep = [];

            foreach ($requirements as $node_id_raw => $required_raw) {
                $node_id = (int)$node_id_raw;
                $required = (int)$required_raw;
                if ($node_id <= 0) continue;
                if ($required < 0) $required = 0;

                $node_ids_to_keep[] = $node_id;

                $pdo->prepare("
                    INSERT INTO pool_node_requirements (pool_id, node_id, required_direct_referrals)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE required_direct_referrals = VALUES(required_direct_referrals)
                ")->execute([$pool_id, $node_id, $required]);
            }

            if (count($node_ids_to_keep) > 0) {
                $placeholders = implode(',', array_fill(0, count($node_ids_to_keep), '?'));
                $params = array_merge([$pool_id], $node_ids_to_keep);
                $pdo->prepare("DELETE FROM pool_node_requirements WHERE pool_id = ? AND node_id NOT IN ($placeholders)")
                    ->execute($params);
            } else {
                $pdo->prepare("DELETE FROM pool_node_requirements WHERE pool_id = ?")->execute([$pool_id]);
            }

            $message = "Pool requirements updated!";
        } elseif ($action === 'generate_defaults') {
            $poolCount = 9;
            $maxNodes = 10;
            $baseNode1 = 5;
            $baseOther = 2;

            $pdo->beginTransaction();

            $existingPools = $pdo->query("SELECT pool_number FROM pools")->fetchAll(PDO::FETCH_COLUMN);
            $existingPools = array_map('intval', $existingPools);

            for ($p = 1; $p <= $poolCount; $p++) {
                if (in_array($p, $existingPools, true)) {
                    continue;
                }

                $bonus = ($p === 1) ? 7.00 : 0.00;
                $pdo->prepare("INSERT INTO pools (pool_number, name, bonus_amount, is_active) VALUES (?, ?, ?, 1)")
                    ->execute([$p, 'Pool ' . $p, $bonus]);
            }

            $pools = $pdo->query("SELECT id, pool_number FROM pools ORDER BY pool_number ASC")->fetchAll();
            $poolIdByNumber = [];
            foreach ($pools as $pool) {
                $poolIdByNumber[(int)$pool['pool_number']] = (int)$pool['id'];
            }

            for ($p = 1; $p <= $poolCount; $p++) {
                if (!isset($poolIdByNumber[$p])) continue;
                $pool_id = $poolIdByNumber[$p];
                $nodesInPool = min($maxNodes, $p + 1);

                for ($n = 1; $n <= $nodesInPool; $n++) {
                    $base = ($n === 1) ? $baseNode1 : $baseOther;
                    $required = $base + ($p - 1);

                    $pdo->prepare("
                        INSERT INTO pool_node_requirements (pool_id, node_id, required_direct_referrals)
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE required_direct_referrals = VALUES(required_direct_referrals)
                    ")->execute([$pool_id, $n, $required]);
                }
            }

            $pdo->commit();
            $message = "Default pools and requirements generated!";
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Error: " . $e->getMessage();
    }
}

$nodes = $pdo->query("SELECT id, node_number, name FROM nodes ORDER BY node_number ASC")->fetchAll();
$pools = $pdo->query("SELECT * FROM pools ORDER BY pool_number ASC")->fetchAll();

$requirementsByPool = [];
if (count($pools) > 0) {
    $reqRows = $pdo->query("
        SELECT r.pool_id, r.node_id, r.required_direct_referrals
        FROM pool_node_requirements r
    ")->fetchAll();

    foreach ($reqRows as $r) {
        $requirementsByPool[(int)$r['pool_id']][(int)$r['node_id']] = (int)$r['required_direct_referrals'];
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">System Architecture /</span> Pool Invitation Rewards</h4>
                <div class="d-flex gap-2">
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="generate_defaults">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bx bx-magic-wand me-1"></i> Generate Defaults
                        </button>
                    </form>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#poolModal" onclick="clearPoolModal()">
                        <i class="bx bx-plus me-1"></i> Add Pool
                    </button>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible" role="alert"><?php echo $message; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible" role="alert"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <div class="card">
                <h5 class="card-header">Pools</h5>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Pool</th>
                                <th>Name</th>
                                <th>Bonus (USD)</th>
                                <th>Status</th>
                                <th>Requirements</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pools as $pool): ?>
                                <?php
                                    $poolId = (int)$pool['id'];
                                    $reqCount = isset($requirementsByPool[$poolId]) ? count($requirementsByPool[$poolId]) : 0;
                                ?>
                                <tr>
                                    <td><strong><?php echo (int)$pool['pool_number']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($pool['name']); ?></td>
                                    <td>$<?php echo number_format((float)$pool['bonus_amount'], 2); ?></td>
                                    <td><span class="badge bg-label-<?php echo (int)$pool['is_active'] === 1 ? 'success' : 'danger'; ?>"><?php echo (int)$pool['is_active'] === 1 ? 'Active' : 'Disabled'; ?></span></td>
                                    <td><?php echo $reqCount; ?> nodes</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary me-2" onclick="editPool(<?php echo htmlspecialchars(json_encode($pool)); ?>)"><i class="bx bx-edit-alt"></i> Edit</button>
                                        <button class="btn btn-sm btn-outline-secondary me-2" onclick="openRequirements(<?php echo $poolId; ?>)"><i class="bx bx-list-check"></i> Requirements</button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deletePool(<?php echo $poolId; ?>)"><i class="bx bx-trash"></i> Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($pools) === 0): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No pools found. Click “Generate Defaults” to auto-create Pools 1–9.</td>
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
                        <label class="form-label">Pool Name</label>
                        <input type="text" name="name" id="pool-name" class="form-control" placeholder="e.g. Pool 1">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Bonus Amount (USD)</label>
                        <input type="number" step="0.01" name="bonus_amount" id="pool-bonus" class="form-control" value="0">
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

<div class="modal fade" id="requirementsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="POST">
            <input type="hidden" name="action" value="save_requirements">
            <input type="hidden" name="pool_id" id="req-pool-id">
            <div class="modal-header">
                <h5 class="modal-title">Pool Requirements</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Node</th>
                                <th>Required Direct Referrals</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($nodes as $node): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($node['name']); ?></td>
                                    <td style="max-width: 220px;">
                                        <input type="number" class="form-control" min="0" name="requirements[<?php echo (int)$node['id']; ?>]" id="req-node-<?php echo (int)$node['id']; ?>" value="">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($nodes) === 0): ?>
                                <tr><td colspan="2" class="text-center text-muted py-4">No nodes found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mt-3 mb-0">
                    Tip: Leave a node blank or set to 0 if it should not be required for this pool.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary px-5">Save Requirements</button>
            </div>
        </form>
    </div>
</div>

<form id="del-pool-form" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete_pool">
    <input type="hidden" name="id" id="del-pool-id">
</form>

<script>
const poolRequirements = <?php echo json_encode($requirementsByPool, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

function clearPoolModal() {
    document.getElementById('pool-id').value = '';
    document.getElementById('pool-title').innerText = 'Add Pool';
    document.getElementById('pool-number').value = '';
    document.getElementById('pool-name').value = '';
    document.getElementById('pool-bonus').value = '0';
    document.getElementById('pool-active').checked = true;
}

function editPool(pool) {
    document.getElementById('pool-id').value = pool.id;
    document.getElementById('pool-title').innerText = 'Edit Pool';
    document.getElementById('pool-number').value = pool.pool_number;
    document.getElementById('pool-name').value = pool.name;
    document.getElementById('pool-bonus').value = pool.bonus_amount;
    document.getElementById('pool-active').checked = parseInt(pool.is_active) === 1;
    new bootstrap.Modal(document.getElementById('poolModal')).show();
}

function deletePool(id) {
    if (confirm('Delete this pool? This will also delete its node requirements.')) {
        document.getElementById('del-pool-id').value = id;
        document.getElementById('del-pool-form').submit();
    }
}

function openRequirements(poolId) {
    document.getElementById('req-pool-id').value = poolId;

    const reqs = poolRequirements[poolId] || {};
    <?php foreach ($nodes as $node): ?>
        (function(){
            const el = document.getElementById('req-node-<?php echo (int)$node['id']; ?>');
            if (!el) return;
            const val = reqs[<?php echo (int)$node['id']; ?>];
            el.value = (val === undefined) ? '' : val;
        })();
    <?php endforeach; ?>

    new bootstrap.Modal(document.getElementById('requirementsModal')).show();
}
</script>

<?php include 'includes/footer.php'; ?>
