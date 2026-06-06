<?php
// admin/arch_free_plan.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'Free Plan Architecture';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'save_free_plan') {
            $amount = (float)$_POST['amount'];
            $weeks = (int)$_POST['duration_weeks'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $pdo->prepare("UPDATE free_plan SET amount = ?, duration_weeks = ?, is_active = ? WHERE id = 1")
                ->execute([$amount, $weeks, $is_active]);
            
            $pdo->prepare("DELETE FROM free_plan_weeks WHERE plan_id = 1")->execute();
            if (isset($_POST['weeks'])) {
                foreach ($_POST['weeks'] as $week => $pct) {
                    $pdo->prepare("INSERT INTO free_plan_weeks (plan_id, week_number, profit_pct) VALUES (1, ?, ?)")
                        ->execute([$week, $pct]);
                }
            }
            $message = "Free plan configuration updated!";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

$free_plan = $pdo->query("SELECT * FROM free_plan WHERE id = 1")->fetch();
$free_plan_weeks = $pdo->query("SELECT * FROM free_plan_weeks WHERE plan_id = 1 ORDER BY week_number ASC")->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">System Architecture /</span> Free Plan</h4>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible" role="alert"><?php echo $message; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible" role="alert"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Free Plan (Starter) Configuration</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="save_free_plan">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Plan Amount (RS)</label>
                                <input type="number" step="0.01" name="amount" class="form-control" value="<?php echo $free_plan['amount'] ?? '290'; ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Duration (Weeks)</label>
                                <input type="number" name="duration_weeks" class="form-control" value="<?php echo $free_plan['duration_weeks'] ?? '4'; ?>">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" <?php echo ($free_plan['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Plan Active Status</label>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3 border-bottom pb-2">Weekly Profit Distribution (%)</h6>
                        <div class="row g-3">
                            <?php 
                            $f_weeks = [];
                            foreach($free_plan_weeks as $fw) $f_weeks[$fw['week_number']] = $fw['profit_pct'];
                            for ($w = 1; $w <= ($free_plan['duration_weeks'] ?? 4); $w++): ?>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-text">Week <?php echo $w; ?></span>
                                        <input type="number" step="0.01" name="weeks[<?php echo $w; ?>]" class="form-control" value="<?php echo $f_weeks[$w] ?? '0.00'; ?>">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                        
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-5">Save Free Plan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
