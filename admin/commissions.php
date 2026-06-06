<?php
// admin/commissions.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'Level Commissions';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update_commission'])) {
            foreach ($_POST['commission'] as $level => $pct) {
                $stmt = $pdo->prepare("UPDATE referral_settings SET commission_pct = ? WHERE level = ?");
                $stmt->execute([(float)$pct, (int)$level]);
            }
            $message = "Commissions updated successfully!";
        } elseif (isset($_POST['add_level'])) {
            $new_level = (int)$_POST['new_level'];
            $new_pct = (float)$_POST['new_pct'];
            $stmt = $pdo->prepare("INSERT INTO referral_settings (level, commission_pct) VALUES (?, ?)");
            $stmt->execute([$new_level, $new_pct]);
            $message = "New level added!";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

$commissions = $pdo->query("SELECT * FROM referral_settings ORDER BY level ASC")->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Architecture /</span> Level Commissions</h4>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <form class="card" method="POST">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Referral Commission Structure</h5>
                            <button type="submit" name="update_commission" class="btn btn-primary btn-sm">Update All</button>
                        </div>
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Level</th>
                                        <th>Commission Percentage (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($commissions as $comm): ?>
                                    <tr>
                                        <td><strong>Level <?php echo $comm['level']; ?></strong></td>
                                        <td>
                                            <div class="input-group" style="max-width: 150px;">
                                                <input type="number" step="0.01" name="commission[<?php echo $comm['level']; ?>]" 
                                                       class="form-control" value="<?php echo $comm['commission_pct']; ?>">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>

                <div class="col-md-4">
                    <form class="card" method="POST">
                        <div class="card-header">
                            <h5 class="mb-0">Add New Level</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Level Number</label>
                                <input type="number" name="new_level" class="form-control" placeholder="e.g. 6" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Commission (%)</label>
                                <input type="number" step="0.01" name="new_pct" class="form-control" placeholder="0.00" required>
                            </div>
                            <button type="submit" name="add_level" class="btn btn-info w-100">Add Level</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
