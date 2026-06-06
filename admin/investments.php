<?php
// admin/investments.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'Investment Management';

// Fetch all user investments joining user profiles and plan configurations
$investments = $pdo->query("
    SELECT i.*, u.name as user_name, u.email as user_email, p.name as plan_name 
    FROM investments i 
    JOIN users u ON i.user_id = u.id 
    JOIN plans p ON i.plan_id = p.id 
    ORDER BY i.created_at DESC
")->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="admin-page-heading py-3 mb-4">
                <h4 class="fw-bold"><span class="text-muted fw-light">Operations /</span> Investments</h4>
            </div>

            <!-- Summary Cards -->
            <?php
            $active_count = 0;
            $active_sum = 0;
            $completed_count = 0;
            $completed_sum = 0;

            foreach ($investments as $inv) {
                if ($inv['status'] === 'active') {
                    $active_count++;
                    $active_sum += $inv['amount'];
                } else {
                    $completed_count++;
                    $completed_sum += $inv['amount'];
                }
            }
            ?>
            <div class="row mb-4">
                <div class="col-md-6 mb-2">
                    <div class="card bg-label-success" style="border: none;">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 text-success fw-bold">ACTIVE INVESTMENTS</h6>
                                <h3 class="mb-0 fw-bold"><?php echo format_usd($active_sum); ?></h3>
                                <small class="text-muted"><?php echo $active_count; ?> Running Plans</small>
                            </div>
                            <span class="badge bg-success p-3 rounded-circle"><i class="bx bx-trending-up fs-3"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <div class="card bg-label-secondary" style="border: none;">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 text-muted fw-bold">COMPLETED PLANS</h6>
                                <h3 class="mb-0 fw-bold"><?php echo format_usd($completed_sum); ?></h3>
                                <small class="text-muted"><?php echo $completed_count; ?> Ended Plans</small>
                            </div>
                            <span class="badge bg-secondary p-3 rounded-circle"><i class="bx bx-check-double fs-3"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card admin-table-card">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom mb-2">
                    <h5 class="mb-0 fw-bold">Active User Investments</h5>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User Info</th>
                                <th>Mining Plan</th>
                                <th>Amount Invested</th>
                                <th>Daily ROI</th>
                                <th>Hourly Rate</th>
                                <th>Status</th>
                                <th>Activated At</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            <?php foreach ($investments as $inv): ?>
                                <tr>
                                    <td>
                                        <strong class="text-break d-block"><?php echo htmlspecialchars($inv['user_name']); ?></strong>
                                        <small class="text-muted text-break"><?php echo htmlspecialchars($inv['user_email']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-primary fw-semibold"><?php echo htmlspecialchars($inv['plan_name']); ?></span>
                                    </td>
                                    <td>
                                        <span class="text-success fw-bold"><?php echo format_usd($inv['amount']); ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold"><?php echo number_format($inv['daily_roi'], 2); ?>%</span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo number_format($inv['hourly_rate'], 6); ?>%</small>
                                    </td>
                                    <td>
                                        <?php if ($inv['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Completed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($inv['created_at'])); ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($investments)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bx bx-folder-open fs-1 mb-2"></i>
                                            <p class="mb-0">No investments found in the system yet.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
