<?php
// admin/salary_ranks.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'Manager Ranks Settings';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update_ranks'])) {
            $pdo->beginTransaction();

            // 1. Update rank criteria
            foreach ($_POST['rank'] as $id => $data) {
                $stmt = $pdo->prepare("UPDATE salary_ranks SET 
                    salary_amount = ?, 
                    self_invest = ?, 
                    direct_active = ?, 
                    indirect_active = ? 
                    WHERE id = ?");
                $stmt->execute([
                    (float)$data['salary_amount'],
                    (float)$data['self_invest'],
                    (int)$data['direct_active'],
                    (int)$data['indirect_active'],
                    (int)$id
                ]);
            }

            // 2. Update dynamic days
            $days = (int)$_POST['salary_days'];
            $guidelines = trim($_POST['salary_guidelines']);

            // Save salary_days
            $stmt = $pdo->prepare("SELECT * FROM settings WHERE name = 'salary_days'");
            $stmt->execute();
            if ($stmt->fetch()) {
                $pdo->prepare("UPDATE settings SET value = ? WHERE name = 'salary_days'")->execute([$days]);
            } else {
                $pdo->prepare("INSERT INTO settings (name, value) VALUES ('salary_days', ?)")->execute([$days]);
            }

            // Save salary_guidelines
            $stmt = $pdo->prepare("SELECT * FROM settings WHERE name = 'salary_guidelines'");
            $stmt->execute();
            if ($stmt->fetch()) {
                $pdo->prepare("UPDATE settings SET value = ? WHERE name = 'salary_guidelines'")->execute([$guidelines]);
            } else {
                $pdo->prepare("INSERT INTO settings (name, value) VALUES ('salary_guidelines', ?)")->execute([$guidelines]);
            }

            $pdo->commit();
            $message = "Manager ranks and system settings updated successfully!";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

// Load dynamic ranks and configurations
$ranks = $pdo->query("SELECT * FROM salary_ranks ORDER BY id ASC")->fetchAll();

$salary_days = get_setting($pdo, 'salary_days', '15');
$default_guidelines = "Salary is distributed every [DAYS] days based on your active rank at the time of payout.\nBoth direct and indirect active members must remain active throughout the [DAYS]-day period.\nSelf investment must be maintained at the required level to stay eligible for the rank salary.\nIf any condition is not met at the time of payout, the salary for that cycle will not be credited.\nRanks are re-evaluated at the start of every new [DAYS]-day cycle.\nHigher ranks include all benefits of lower ranks and unlock greater salary rewards.";
$salary_guidelines = get_setting($pdo, 'salary_guidelines', $default_guidelines);

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Architecture /</span> Manager Ranks Settings</h4>

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
                <div class="col-md-12">
                    <form class="card" method="POST">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Global Salary System Configurations</h5>
                            <button type="submit" name="update_ranks" class="btn btn-primary">Save Settings</button>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label fw-bold text-primary">Salary Cycle Period (Days)</label>
                                    <input type="number" name="salary_days" class="form-control" value="<?php echo htmlspecialchars($salary_days); ?>" required>
                                    <small class="text-muted">Controls dynamic payout cycles and tags across the platform (e.g. 15, 30 days).</small>
                                </div>
                                <div class="mb-3 col-md-8">
                                    <label class="form-label fw-bold text-primary">Salary Guidelines (One point per line, use [DAYS] token)</label>
                                    <textarea name="salary_guidelines" class="form-control" rows="6" required><?php echo htmlspecialchars($salary_guidelines); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <hr class="m-0">

                        <div class="card-header">
                            <h5 class="mb-0">Manager Salary Ranks & Qualification Criteria</h5>
                        </div>
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Rank Name</th>
                                        <th>Salary Amount ($)</th>
                                        <th>Min Self-Invest ($)</th>
                                        <th>Min Direct Active</th>
                                        <th>Min Indirect Active</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ranks as $rank): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($rank['rank_name']); ?></strong></td>
                                        <td>
                                            <div class="input-group" style="max-width: 150px;">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.01" name="rank[<?php echo $rank['id']; ?>][salary_amount]" 
                                                       class="form-control" value="<?php echo $rank['salary_amount']; ?>" required>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group" style="max-width: 150px;">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.01" name="rank[<?php echo $rank['id']; ?>][self_invest]" 
                                                       class="form-control" value="<?php echo $rank['self_invest']; ?>" required>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="number" name="rank[<?php echo $rank['id']; ?>][direct_active]" 
                                                   class="form-control" style="max-width: 120px;" value="<?php echo $rank['direct_active']; ?>" required>
                                        </td>
                                        <td>
                                            <input type="number" name="rank[<?php echo $rank['id']; ?>][indirect_active]" 
                                                   class="form-control" style="max-width: 120px;" value="<?php echo $rank['indirect_active']; ?>" required>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
