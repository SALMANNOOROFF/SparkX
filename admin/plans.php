<?php
// admin/plans.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'Manage Investment Plans';
$message = '';
$error = '';

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    try {
        if ($action === 'add' || $action === 'edit') {
            $name = $_POST['name'];
            $min_investment = (float)$_POST['min_investment'];
            $max_investment = (float)$_POST['max_investment'];
            $daily_roi_min = (float)$_POST['daily_roi_min'];
            $daily_roi_max = (float)$_POST['daily_roi_max'];
            $hourly_rate = (float)$_POST['hourly_rate'];
            $status = $_POST['status'];

            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO plans (name, min_investment, max_investment, daily_roi_min, daily_roi_max, hourly_rate, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $min_investment, $max_investment, $daily_roi_min, $daily_roi_max, $hourly_rate, $status]);
                $message = "Plan added successfully!";
            } else {
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("UPDATE plans SET name = ?, min_investment = ?, max_investment = ?, daily_roi_min = ?, daily_roi_max = ?, hourly_rate = ?, status = ? WHERE id = ?");
                $stmt->execute([$name, $min_investment, $max_investment, $daily_roi_min, $daily_roi_max, $hourly_rate, $status, $id]);
                $message = "Plan updated successfully!";
            }
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            $pdo->prepare("DELETE FROM plans WHERE id = ?")->execute([$id]);
            $message = "Plan deleted successfully!";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

$plans = $pdo->query("SELECT * FROM plans ORDER BY min_investment ASC")->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Architecture /</span> Investment Plans</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#planModal" onclick="clearPlanModal()">
                    <i class="bx bx-plus me-1"></i> Add New Plan
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <?php foreach ($plans as $plan): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0" style="border-radius: 15px; border: 1px solid rgba(0,0,0,0.05);">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title fw-bold mb-0 text-primary"><?php echo htmlspecialchars($plan['name']); ?></h5>
                                <?php if ($plan['status'] === 'active'): ?>
                                    <span class="badge bg-label-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-label-secondary">Inactive</span>
                                <?php endif; ?>
                            </div>
                            <div class="mb-4">
                                <div class="h3 fw-bold text-dark mb-1">
                                    <?php echo number_format($plan['daily_roi_min'], 1); ?>% - <?php echo number_format($plan['daily_roi_max'], 1); ?>%
                                </div>
                                <span class="text-muted small" style="font-size: 0.8rem;">Guaranteed Daily ROI Range</span>
                                <div class="mt-2 text-muted small">Hourly Rate: <strong><?php echo number_format($plan['hourly_rate'], 4); ?>%</strong></div>
                            </div>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2 d-flex justify-content-between">
                                    <span class="text-muted">Min Investment:</span>
                                    <span class="fw-bold text-success">$<?php echo number_format($plan['min_investment'], 2); ?></span>
                                </li>
                                <li class="mb-2 d-flex justify-content-between">
                                    <span class="text-muted">Max Investment:</span>
                                    <span class="fw-bold text-danger">$<?php echo number_format($plan['max_investment'], 2); ?></span>
                                </li>
                            </ul>
                            <div class="d-flex gap-2 mt-auto">
                                <button class="btn btn-outline-primary btn-sm flex-grow-1" onclick='editPlan(<?php echo json_encode($plan); ?>)'>
                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="deletePlan(<?php echo $plan['id']; ?>)">
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

<!-- Plan Modal -->
<div class="modal fade" id="planModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <input type="hidden" name="action" id="modal-action" value="add">
            <input type="hidden" name="id" id="plan-id">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">Add New Investment Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Plan Name</label>
                    <input type="text" name="name" id="plan-name" class="form-control" placeholder="e.g. Platinum" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Min Investment ($)</label>
                        <input type="number" step="0.01" name="min_investment" id="plan-min" class="form-control" placeholder="3.00" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Max Investment ($)</label>
                        <input type="number" step="0.01" name="max_investment" id="plan-max" class="form-control" placeholder="500.00" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Daily ROI Min (%)</label>
                        <input type="number" step="0.01" name="daily_roi_min" id="plan-roi-min" class="form-control" placeholder="4.50" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Daily ROI Max (%)</label>
                        <input type="number" step="0.01" name="daily_roi_max" id="plan-roi-max" class="form-control" placeholder="5.50" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Hourly Rate (%)</label>
                        <input type="number" step="0.000001" name="hourly_rate" id="plan-hourly" class="form-control" placeholder="0.1875" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" id="plan-status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Plan</button>
            </div>
        </form>
    </div>
</div>

<script>
function clearPlanModal() {
    document.getElementById('modal-action').value = 'add';
    document.getElementById('modal-title').innerText = 'Add New Investment Plan';
    document.getElementById('plan-id').value = '';
    document.getElementById('plan-name').value = '';
    document.getElementById('plan-min').value = '';
    document.getElementById('plan-max').value = '';
    document.getElementById('plan-roi-min').value = '';
    document.getElementById('plan-roi-max').value = '';
    document.getElementById('plan-hourly').value = '';
    document.getElementById('plan-status').value = 'active';
}

function editPlan(plan) {
    document.getElementById('modal-action').value = 'edit';
    document.getElementById('modal-title').innerText = 'Edit Plan: ' + plan.name;
    document.getElementById('plan-id').value = plan.id;
    document.getElementById('plan-name').value = plan.name;
    document.getElementById('plan-min').value = plan.min_investment;
    document.getElementById('plan-max').value = plan.max_investment;
    document.getElementById('plan-roi-min').value = plan.daily_roi_min;
    document.getElementById('plan-roi-max').value = plan.daily_roi_max;
    document.getElementById('plan-hourly').value = plan.hourly_rate;
    document.getElementById('plan-status').value = plan.status;
    new bootstrap.Modal(document.getElementById('planModal')).show();
}

function deletePlan(id) {
    if (confirm('Are you sure you want to delete this plan?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="${id}">`;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
