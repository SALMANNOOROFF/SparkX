<?php
// admin/gateways.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'Payment Gateways';
$message = '';
$error = '';
$provider_env = ['paybost' => 'sandbox', 'nowpayments' => 'live']; // Defaulting for now

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add_gateway') {
            $name = $_POST['name'];
            $slug = $_POST['slug'];
            $type = $_POST['type'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            $image_url = null;
            if (isset($_FILES['gateway_image']) && $_FILES['gateway_image']['error'] == 0) {
                $upload_dir = __DIR__ . '/../assets/admin/images/payment-method/';
                if (!is_dir($upload_dir)) {
                    @mkdir($upload_dir, 0777, true);
                }
                $ext = pathinfo($_FILES['gateway_image']['name'], PATHINFO_EXTENSION);
                $new_name = 'pm_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $dest = $upload_dir . $new_name;
                if (move_uploaded_file($_FILES['gateway_image']['tmp_name'], $dest)) {
                    $image_url = 'assets/admin/images/payment-method/' . $new_name;
                }
            }
            
            $pdo->prepare("INSERT INTO payment_gateways (name, slug, type, is_active, image) VALUES (?, ?, ?, ?, ?)")
                ->execute([$name, $slug, $type, $is_active, $image_url]);
            $message = 'New gateway added successfully!';
        } 
        elseif ($action === 'save_provider_env') {
            $provider = $_POST['provider'] ?? '';
            $environment = $_POST['environment'] ?? 'sandbox';

            if (!in_array($provider, ['paybost', 'nowpayments'], true)) {
                throw new Exception('Invalid provider selected.');
            }

            if (!in_array($environment, ['sandbox', 'live'], true)) {
                throw new Exception('Invalid environment selected.');
            }

            $stmt = $pdo->prepare("INSERT INTO settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)");
            $stmt->execute(['gateway_env_' . $provider, $environment]);
            $provider_env[$provider] = $environment;
            $message = strtoupper($provider) . ' environment updated successfully!';
        }
        elseif ($action === 'update_gateway') {
            $id = (int)$_POST['id'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $name = $_POST['name'];
            $min_dep = (float)$_POST['min_deposit'];
            $max_dep = (float)$_POST['max_deposit'];
            $min_with = (float)$_POST['min_withdrawal'];
            $max_with = (float)$_POST['max_withdrawal'];
            $api_key = $_POST['api_key'];
            $api_secret = $_POST['api_secret'];
            $merchant_id = $_POST['api_merchant_id'] ?? '';
            $is_deposit = isset($_POST['is_deposit']) ? 1 : 0;
            $is_withdrawal = isset($_POST['is_withdrawal']) ? 1 : 0;

            // Handle image upload if provided
            $image_sql = "";
            $image_params = [];
            if (isset($_FILES['gateway_image']) && $_FILES['gateway_image']['error'] == 0) {
                $upload_dir = __DIR__ . '/../assets/admin/images/payment-method/';
                if (!is_dir($upload_dir)) {
                    @mkdir($upload_dir, 0777, true);
                }
                $ext = pathinfo($_FILES['gateway_image']['name'], PATHINFO_EXTENSION);
                $new_name = 'pm_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $dest = $upload_dir . $new_name;
                if (move_uploaded_file($_FILES['gateway_image']['tmp_name'], $dest)) {
                    $image_url = 'assets/admin/images/payment-method/' . $new_name;
                    $image_sql = ", image = ?";
                    $image_params[] = $image_url;
                }
            }

            $sql = "UPDATE payment_gateways SET 
                name = ?, is_active = ?, min_deposit = ?, max_deposit = ?, 
                min_withdrawal = ?, max_withdrawal = ?, api_key = ?, api_secret = ?, 
                api_merchant_id = ?, is_deposit = ?, is_withdrawal = ? $image_sql WHERE id = ?";
            
            $params = [
                $name, $is_active, $min_dep, $max_dep, 
                $min_with, $max_with, $api_key, $api_secret, 
                $merchant_id, $is_deposit, $is_withdrawal
            ];
            if (!empty($image_params)) {
                $params = array_merge($params, $image_params);
            }
            $params[] = $id;

            $pdo->prepare($sql)->execute($params);
            $message = 'Gateway updated successfully!';
        }
        elseif ($action === 'delete_gateway') {
            $id = (int)$_POST['id'];
            $pdo->prepare("DELETE FROM payment_gateways WHERE id = ?")->execute([$id]);
            $message = 'Gateway deleted successfully!';
        }
        elseif ($action === 'toggle_status') {
            $id = (int)$_POST['id'];
            $status = (int)$_POST['status'];
            $pdo->prepare("UPDATE payment_gateways SET is_active = ? WHERE id = ?")->execute([$status, $id]);
            echo json_encode(['success' => true]);
            exit;
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

$gateways = $pdo->query("SELECT * FROM payment_gateways ORDER BY sort_order ASC")->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Finance /</span> Payment Gateways</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGatewayModal">
                    <i class="bx bx-plus me-1"></i> Add New Gateway
                </button>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <form class="card" method="POST">
                        <input type="hidden" name="action" value="save_provider_env">
                        <input type="hidden" name="provider" value="paybost">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="mb-0">PayBost Mode</h5>
                                    <small class="text-muted">Easypaisa and JazzCash automatic payments</small>
                                </div>
                                <span class="badge bg-label-<?php echo $provider_env['paybost'] === 'live' ? 'success' : 'warning'; ?>">
                                    <?php echo strtoupper($provider_env['paybost']); ?>
                                </span>
                            </div>
                            <div class="input-group">
                                <select name="environment" class="form-select">
                                    <option value="sandbox" <?php echo $provider_env['paybost'] === 'sandbox' ? 'selected' : ''; ?>>Sandbox</option>
                                    <option value="live" <?php echo $provider_env['paybost'] === 'live' ? 'selected' : ''; ?>>Live</option>
                                </select>
                                <button class="btn btn-outline-primary" type="submit">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-6 mb-3">
                    <form class="card" method="POST">
                        <input type="hidden" name="action" value="save_provider_env">
                        <input type="hidden" name="provider" value="nowpayments">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="mb-0">NOWPayments Mode</h5>
                                    <small class="text-muted">USDT BEP20 / future crypto deposits</small>
                                </div>
                                <span class="badge bg-label-<?php echo $provider_env['nowpayments'] === 'live' ? 'success' : 'warning'; ?>">
                                    <?php echo strtoupper($provider_env['nowpayments']); ?>
                                </span>
                            </div>
                            <div class="input-group">
                                <select name="environment" class="form-select">
                                    <option value="sandbox" <?php echo $provider_env['nowpayments'] === 'sandbox' ? 'selected' : ''; ?>>Sandbox</option>
                                    <option value="live" <?php echo $provider_env['nowpayments'] === 'live' ? 'selected' : ''; ?>>Live</option>
                                </select>
                                <button class="btn btn-outline-primary" type="submit">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
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
                <?php foreach ($gateways as $g): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center pb-2">
                                <div class="d-flex align-items-center">
                                    <div class="avatar flex-shrink-0 me-3">
                                        <?php if (!empty($g['image'])): ?>
                                            <img src="../<?php echo htmlspecialchars($g['image']); ?>" alt="<?php echo htmlspecialchars($g['name']); ?>" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                        <?php else: ?>
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="bx <?php echo strpos($g['slug'], 'crypto') !== false || strpos($g['slug'], 'now') !== false ? 'bx-bitcoin' : 'bx-wallet'; ?> fs-4"></i>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 text-nowrap"><?php echo htmlspecialchars($g['name']); ?></h5>
                                        <small class="text-muted text-uppercase"><?php echo $g['slug']; ?></small>
                                    </div>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input status-toggle" type="checkbox" data-id="<?php echo $g['id']; ?>" <?php echo $g['is_active'] ? 'checked' : ''; ?>>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Type:</span>
                                    <span class="badge bg-label-info text-capitalize"><?php echo $g['type']; ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Deposit:</span>
                                    <span class="badge bg-label-<?php echo $g['is_deposit'] ? 'success' : 'danger'; ?>"><?php echo $g['is_deposit'] ? 'Active' : 'Off'; ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Withdraw:</span>
                                    <span class="badge bg-label-<?php echo $g['is_withdrawal'] ? 'success' : 'danger'; ?>"><?php echo $g['is_withdrawal'] ? 'Active' : 'Off'; ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                     <span class="text-muted small">Status:</span>
                                     <span class="badge status-badge bg-label-<?php echo $g['is_active'] ? 'success' : 'secondary'; ?>"><?php echo $g['is_active'] ? 'Active' : 'Inactive'; ?></span>
                                 </div>
                                <hr class="my-3">
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Min Deposit</small>
                                        <span class="fw-bold">RS <?php echo number_format($g['min_deposit']); ?></span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Max Deposit</small>
                                        <span class="fw-bold">RS <?php echo number_format($g['max_deposit']/1000); ?>K</span>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2 mt-4">
                                    <button class="btn btn-outline-primary btn-sm flex-grow-1" onclick="editGateway(<?php echo htmlspecialchars(json_encode($g)); ?>)">
                                        <i class="bx bx-edit-alt me-1"></i> Edit Details
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteGateway(<?php echo $g['id']; ?>)">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- MODALS -->
<div class="modal fade" id="addGatewayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_gateway">
            <div class="modal-header">
                <h5 class="modal-title">Add New Payment Gateway</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Gateway Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. JazzCash (PayBoost)" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Slug (Unique ID)</label>
                        <input type="text" name="slug" class="form-control" placeholder="e.g. jazzcash_payboost" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="automatic">Automatic (API)</option>
                            <option value="manual">Manual (Proof)</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Gateway Image / Logo</label>
                        <input type="file" name="gateway_image" class="form-control">
                    </div>
                    <div class="col-12 mt-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" checked>
                            <label class="form-check-label fw-bold">Enable immediately</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Gateway</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editGatewayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_gateway">
            <input type="hidden" name="id" id="edit-id">
            <div class="modal-header">
                <h5 class="modal-title">Edit Gateway: <span id="edit-title-name"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Display Name</label>
                        <input class="form-control" type="text" name="name" id="edit-name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Gateway Image / Logo</label>
                        <div class="d-flex align-items-center">
                            <img src="" id="edit-preview-image" alt="Preview" class="rounded me-3" style="max-height: 40px; display: none;">
                            <input class="form-control" type="file" name="gateway_image">
                        </div>
                    </div>
                    <div class="col-md-3 mb-3 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_deposit" id="edit-is-deposit">
                            <label class="form-check-label">Allow Deposit</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_withdrawal" id="edit-is-withdrawal">
                            <label class="form-check-label">Allow Withdraw</label>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label text-info">Min Deposit</label>
                        <input class="form-control" type="number" step="0.01" name="min_deposit" id="edit-min-dep">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label text-info">Max Deposit</label>
                        <input class="form-control" type="number" step="0.01" name="max_deposit" id="edit-max-dep">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label text-warning">Min Withdraw</label>
                        <input class="form-control" type="number" step="0.01" name="min_withdrawal" id="edit-min-with">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label text-warning">Max Withdraw</label>
                        <input class="form-control" type="number" step="0.01" name="max_withdrawal" id="edit-max-with">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">API Key / Merchant Key</label>
                        <input class="form-control" type="text" name="api_key" id="edit-api-key">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">API Secret / Hash Secret</label>
                        <input class="form-control" type="text" name="api_secret" id="edit-api-secret">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Merchant ID / Wallet Address (Optional)</label>
                        <input class="form-control" type="text" name="api_merchant_id" id="edit-merchant">
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="edit-is-active">
                            <label class="form-check-label fw-bold">MASTER ENABLE / DISABLE</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save Configuration</button>
            </div>
        </form>
    </div>
</div>

<form id="delete-gateway-form" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete_gateway">
    <input type="hidden" name="id" id="delete-id">
</form>

<script>
document.querySelectorAll('.status-toggle').forEach(el => {
    el.addEventListener('change', function() {
        const id = this.dataset.id;
        const status = this.checked ? 1 : 0;
        const card = this.closest('.card');
        const badge = card.querySelector('.status-badge');
        
        const formData = new FormData();
        formData.append('action', 'toggle_status');
        formData.append('id', id);
        formData.append('status', status);
        
        fetch('gateways.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (badge) {
                    if (status === 1) {
                        badge.className = 'badge status-badge bg-label-success';
                        badge.innerText = 'Active';
                    } else {
                        badge.className = 'badge status-badge bg-label-secondary';
                        badge.innerText = 'Inactive';
                    }
                }
            } else {
                this.checked = !this.checked;
                alert('Failed to update status.');
            }
        })
        .catch(err => {
            this.checked = !this.checked;
            alert('Error updating status.');
        });
    });
});

function editGateway(g) {
    document.getElementById('edit-id').value = g.id;
    document.getElementById('edit-title-name').innerText = g.name;
    document.getElementById('edit-name').value = g.name;
    document.getElementById('edit-min-dep').value = g.min_deposit;
    document.getElementById('edit-max-dep').value = g.max_deposit;
    document.getElementById('edit-min-with').value = g.min_withdrawal;
    document.getElementById('edit-max-with').value = g.max_withdrawal;
    document.getElementById('edit-api-key').value = g.api_key || '';
    document.getElementById('edit-api-secret').value = g.api_secret || '';
    document.getElementById('edit-merchant').value = g.api_merchant_id || '';
    document.getElementById('edit-is-active').checked = parseInt(g.is_active) === 1;
    document.getElementById('edit-is-deposit').checked = parseInt(g.is_deposit) === 1;
    document.getElementById('edit-is-withdrawal').checked = parseInt(g.is_withdrawal) === 1;
    
    const previewImg = document.getElementById('edit-preview-image');
    if (g.image) {
        previewImg.src = '../' + g.image;
        previewImg.style.display = 'block';
    } else {
        previewImg.style.display = 'none';
    }
    
    new bootstrap.Modal(document.getElementById('editGatewayModal')).show();
}

function deleteGateway(id) {
    if (confirm('Are you sure you want to delete this payment gateway?')) {
        document.getElementById('delete-id').value = id;
        document.getElementById('delete-gateway-form').submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
