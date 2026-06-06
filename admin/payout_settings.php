<?php
// admin/payout_settings.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'Payout Settings';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add_bank') {
            $name = $_POST['name'];
            $pdo->prepare("INSERT INTO payout_banks (name) VALUES (?)")->execute([$name]);
            $message = 'Bank added successfully!';
        } elseif ($action === 'edit_bank') {
            $id = (int)$_POST['id'];
            $name = $_POST['name'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $pdo->prepare("UPDATE payout_banks SET name = ?, is_active = ? WHERE id = ?")->execute([$name, $is_active, $id]);
            $message = 'Bank updated successfully!';
        } elseif ($action === 'delete_bank') {
            $id = (int)$_POST['id'];
            $pdo->prepare("DELETE FROM payout_banks WHERE id = ?")->execute([id]);
            $message = 'Bank deleted successfully!';
        } elseif ($action === 'add_merchant') {
            $name = $_POST['name'];
            $pdo->prepare("INSERT INTO payout_merchants (name) VALUES (?)")->execute([$name]);
            $message = 'Merchant added successfully!';
        } elseif ($action === 'edit_merchant') {
            $id = (int)$_POST['id'];
            $name = $_POST['name'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $pdo->prepare("UPDATE payout_merchants SET name = ?, is_active = ? WHERE id = ?")->execute([$name, $is_active, $id]);
            $message = 'Merchant updated successfully!';
        } elseif ($action === 'delete_merchant') {
            $id = (int)$_POST['id'];
            $pdo->prepare("DELETE FROM payout_merchants WHERE id = ?")->execute([id]);
            $message = 'Merchant deleted successfully!';
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

$banks = $pdo->query("SELECT * FROM payout_banks ORDER BY name ASC")->fetchAll();
$merchants = $pdo->query("SELECT * FROM payout_merchants ORDER BY name ASC")->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Settings /</span> Payout Banks & Merchants</h4>

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
                <!-- Banks Column -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Supported Banks</h5>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBankModal">Add Bank</button>
                        </div>
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($banks as $bank): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($bank['name']); ?></td>
                                        <td>
                                            <span class="badge bg-label-<?php echo $bank['is_active'] ? 'success' : 'danger'; ?>">
                                                <?php echo $bank['is_active'] ? 'Active' : 'Disabled'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-icon btn-outline-primary" onclick='editBank(<?php echo json_encode($bank); ?>)'>
                                                <i class="bx bx-edit-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Merchants Column -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Crypto Merchants</h5>
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#addMerchantModal">Add Merchant</button>
                        </div>
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($merchants as $merchant): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($merchant['name']); ?></td>
                                        <td>
                                            <span class="badge bg-label-<?php echo $merchant['is_active'] ? 'success' : 'danger'; ?>">
                                                <?php echo $merchant['is_active'] ? 'Active' : 'Disabled'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-icon btn-outline-primary" onclick='editMerchant(<?php echo json_encode($merchant); ?>)'>
                                                <i class="bx bx-edit-alt"></i>
                                            </button>
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
    </div>
</div>

<!-- Add Bank Modal -->
<div class="modal fade" id="addBankModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <input type="hidden" name="action" value="add_bank">
            <div class="modal-header">
                <h5 class="modal-title">Add New Bank</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Bank Modal -->
<div class="modal fade" id="editBankModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <input type="hidden" name="action" value="edit_bank">
            <input type="hidden" name="id" id="edit-bank-id">
            <div class="modal-header">
                <h5 class="modal-title">Edit Bank</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="name" id="edit-bank-name" class="form-control" required>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" id="edit-bank-active">
                    <label class="form-check-label">Is Active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="action" value="delete_bank" class="btn btn-danger me-auto">Delete</button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Merchant Modal -->
<div class="modal fade" id="addMerchantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <input type="hidden" name="action" value="add_merchant">
            <div class="modal-header">
                <h5 class="modal-title">Add New Crypto Merchant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Merchant Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Merchant Modal -->
<div class="modal fade" id="editMerchantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <input type="hidden" name="action" value="edit_merchant">
            <input type="hidden" name="id" id="edit-merchant-id">
            <div class="modal-header">
                <h5 class="modal-title">Edit Merchant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Merchant Name</label>
                    <input type="text" name="name" id="edit-merchant-name" class="form-control" required>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" id="edit-merchant-active">
                    <label class="form-check-label">Is Active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="action" value="delete_merchant" class="btn btn-danger me-auto">Delete</button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function editBank(bank) {
    document.getElementById('edit-bank-id').value = bank.id;
    document.getElementById('edit-bank-name').value = bank.name;
    document.getElementById('edit-bank-active').checked = parseInt(bank.is_active) === 1;
    new bootstrap.Modal(document.getElementById('editBankModal')).show();
}

function editMerchant(merchant) {
    document.getElementById('edit-merchant-id').value = merchant.id;
    document.getElementById('edit-merchant-name').value = merchant.name;
    document.getElementById('edit-merchant-active').checked = parseInt(merchant.is_active) === 1;
    new bootstrap.Modal(document.getElementById('editMerchantModal')).show();
}
</script>

<?php include 'includes/footer.php'; ?>
