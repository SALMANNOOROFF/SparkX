<?php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'Pool Node Requirements';
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save_requirements') {
            $pool_id = (int)($_POST['pool_id'] ?? 0);
            $requirements = $_POST['requirements'] ?? [];

            if ($pool_id <= 0) {
                throw new Exception("Invalid pool.");
            }
            if (!is_array($requirements)) {
                $requirements = [];
            }

            $pdo->beginTransaction();

            $poolRow = $pdo->prepare("SELECT nodes_count FROM pools WHERE id = ?");
            $poolRow->execute([$pool_id]);
            $nodesCount = (int)($poolRow->fetchColumn() ?? 0);

            if ($nodesCount < 0) $nodesCount = 0;
            if ($nodesCount > 10) $nodesCount = 10;

            $allowedNodeIds = [];
            if ($nodesCount > 0) {
                $stmtNodes = $pdo->prepare("SELECT id FROM nodes ORDER BY node_number ASC LIMIT $nodesCount");
                $stmtNodes->execute();
                $allowedNodeIds = array_map('intval', $stmtNodes->fetchAll(PDO::FETCH_COLUMN));
            }

            $stmtUpsert = $pdo->prepare("
                INSERT INTO pool_node_requirements (pool_id, node_id, required_direct_referrals)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE required_direct_referrals = VALUES(required_direct_referrals)
            ");

            foreach ($allowedNodeIds as $nodeId) {
                $required = isset($requirements[$nodeId]) ? (int)$requirements[$nodeId] : 0;
                if ($required < 0) $required = 0;
                $stmtUpsert->execute([$pool_id, $nodeId, $required]);
            }

            $pdo->commit();
            $message = "Pool requirements saved!";
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Error: " . $e->getMessage();
    }
}

$pools = $pdo->query("SELECT * FROM pools ORDER BY pool_number ASC")->fetchAll();

$requirementsByPool = [];
$reqRows = $pdo->query("SELECT pool_id, node_id, required_direct_referrals FROM pool_node_requirements")->fetchAll();
foreach ($reqRows as $r) {
    $requirementsByPool[(int)$r['pool_id']][(int)$r['node_id']] = (int)$r['required_direct_referrals'];
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">System Architecture /</span> Pool Requirements</h4>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible" role="alert"><?php echo $message; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible" role="alert"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <div class="row">
                <?php foreach ($pools as $pool): ?>
                    <?php
                        $poolId = (int)$pool['id'];
                        $poolReqs = $requirementsByPool[$poolId] ?? [];
                        $nodesCount = (int)($pool['nodes_count'] ?? 0);
                        if ($nodesCount < 0) $nodesCount = 0;
                        if ($nodesCount > 10) $nodesCount = 10;
                        $nodes = [];
                        if ($nodesCount > 0) {
                            $nodes = $pdo->query("SELECT id, node_number, name, amount FROM nodes ORDER BY node_number ASC LIMIT " . (int)$nodesCount)->fetchAll();
                        }
                    ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center border-bottom mb-3">
                                <div>
                                    <h5 class="mb-0"><?php echo htmlspecialchars($pool['name']); ?> (Pool <?php echo (int)$pool['pool_number']; ?>)</h5>
                                    <div class="text-muted small">Bonus: $<?php echo number_format((float)$pool['bonus_amount'], 2); ?></div>
                                </div>
                                <span class="badge bg-label-<?php echo (int)$pool['is_active'] === 1 ? 'success' : 'danger'; ?>"><?php echo (int)$pool['is_active'] === 1 ? 'Active' : 'Disabled'; ?></span>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="save_requirements">
                                    <input type="hidden" name="pool_id" value="<?php echo $poolId; ?>">

                                    <?php if ($nodesCount === 0): ?>
                                        <div class="text-center text-muted py-4">No nodes configured for this pool. Set “Nodes in Pool” from Pools page.</div>
                                    <?php else: ?>
                                        <div class="row g-3">
                                            <?php foreach ($nodes as $node): ?>
                                                <?php $nodeId = (int)$node['id']; ?>
                                                <div class="col-md-6">
                                                    <div class="input-group">
                                                        <span class="input-group-text"><?php echo htmlspecialchars($node['name']); ?></span>
                                                        <input
                                                            type="number"
                                                            min="0"
                                                            class="form-control"
                                                            name="requirements[<?php echo $nodeId; ?>]"
                                                            value="<?php echo isset($poolReqs[$nodeId]) ? (int)$poolReqs[$nodeId] : 0; ?>"
                                                        >
                                                        <span class="input-group-text">refs</span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mt-3 text-end">
                                        <button type="submit" class="btn btn-primary">Save Requirements</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (count($pools) === 0): ?>
                    <div class="col-12">
                        <div class="alert alert-warning">
                            No pools found. Create pools first from “Pools” page.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
