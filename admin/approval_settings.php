<?php
// admin/approval_settings.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'Approval Settings';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approval'])) {
        $keys = ['approval_required_deposit', 'approval_required_withdrawal', 'approval_required_investment'];
        foreach ($keys as $key) {
            $value = isset($_POST['approval'][$key]) ? '1' : '0';
            
            $stmt = $pdo->prepare("SELECT * FROM settings WHERE name = ?");
            $stmt->execute([$key]);
            if ($stmt->fetch()) {
                $pdo->prepare("UPDATE settings SET value = ? WHERE name = ?")->execute([$value, $key]);
            } else {
                $pdo->prepare("INSERT INTO settings (name, value) VALUES (?, ?)")->execute([$key, $value]);
            }
        }
        $message = 'Approval settings updated successfully!';
    }
}

// Fetch current values
$approval_settings = [
    'approval_required_deposit' => get_setting($pdo, 'approval_required_deposit', '1'),
    'approval_required_withdrawal' => get_setting($pdo, 'approval_required_withdrawal', '1'),
    'approval_required_investment' => get_setting($pdo, 'approval_required_investment', '0')
];

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="admin-page-heading py-3 mb-4">
                <h4 class="fw-bold"><span class="text-muted fw-light">Settings /</span> Approval Settings</h4>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8 col-sm-12">
                    <form method="POST">
                        <div class="card mb-4">
                            <h5 class="card-header">Approval Gate Policies</h5>
                            <div class="card-body">
                                <p class="text-muted">Configure which financial transactions require manual review by administrators before being finalized for the user.</p>
                                
                                <hr class="my-4">

                                <!-- Deposit Switch -->
                                <div class="mb-4 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 fw-bold">Deposit Approvals</h6>
                                        <p class="text-muted mb-0 small">If enabled, deposit requests will remain pending until approved in the admin console.</p>
                                    </div>
                                    <div class="form-check form-switch form-switch-lg">
                                        <input class="form-check-input" type="checkbox" name="approval[approval_required_deposit]" id="approval_required_deposit" value="1" <?php echo $approval_settings['approval_required_deposit'] === '1' ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <hr class="my-3">

                                <!-- Withdrawal Switch -->
                                <div class="mb-4 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 fw-bold">Withdrawal Approvals</h6>
                                        <p class="text-muted mb-0 small">If enabled, payouts will go through the pending review queue before being released.</p>
                                    </div>
                                    <div class="form-check form-switch form-switch-lg">
                                        <input class="form-check-input" type="checkbox" name="approval[approval_required_withdrawal]" id="approval_required_withdrawal" value="1" <?php echo $approval_settings['approval_required_withdrawal'] === '1' ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <hr class="my-3">

                                <!-- Investment Switch -->
                                <div class="mb-4 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 fw-bold">Plan Investment Approvals</h6>
                                        <p class="text-muted mb-0 small">If enabled, new node purchases will stay pending and won't earn or pay commissions until approved.</p>
                                    </div>
                                    <div class="form-check form-switch form-switch-lg">
                                        <input class="form-check-input" type="checkbox" name="approval[approval_required_investment]" id="approval_required_investment" value="1" <?php echo $approval_settings['approval_required_investment'] === '1' ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-lg px-5">SAVE APPROVAL POLICIES</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
