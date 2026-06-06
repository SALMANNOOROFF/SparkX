<?php
// admin/settings.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'Site Settings';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Handle Regular Settings
    if (isset($_POST['settings'])) {
        foreach ($_POST['settings'] as $key => $value) {
            $stmt = $pdo->prepare("SELECT * FROM settings WHERE name = ?");
            $stmt->execute([$key]);
            if ($stmt->fetch()) {
                $pdo->prepare("UPDATE settings SET value = ? WHERE name = ?")->execute([$value, $key]);
            } else {
                $pdo->prepare("INSERT INTO settings (name, value) VALUES (?, ?)")->execute([$key, $value]);
            }
        }
    }

    // 2. Handle Logo Upload
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] == 0) {
        $upload_dir = __DIR__ . '/../assets/images/logoIcon/';
        if (!is_dir($upload_dir)) {
            if (!@mkdir($upload_dir, 0777, true)) {
                $message = 'Error: Could not create logo directory. Check folder permissions.';
            }
        }
        
        if (is_dir($upload_dir)) {
            // Remove old logo files to prevent clutter
            $old_logo = get_setting($pdo, 'site_logo', '');
            if ($old_logo && file_exists(__DIR__ . '/../' . $old_logo)) {
                @unlink(__DIR__ . '/../' . $old_logo);
            }

            $ext = pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION);
            $new_name = 'logo_' . time() . '.' . $ext;
            $dest = $upload_dir . $new_name;
            
            if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $dest)) {
                $logo_url = 'assets/images/logoIcon/' . $new_name;
                $stmt = $pdo->prepare("SELECT * FROM settings WHERE name = 'site_logo'");
                $stmt->execute();
                if ($stmt->fetch()) {
                    $pdo->prepare("UPDATE settings SET value = ? WHERE name = 'site_logo'")->execute([$logo_url]);
                } else {
                    $pdo->prepare("INSERT INTO settings (name, value) VALUES ('site_logo', ?)")->execute([$logo_url]);
                }
                $settings['site_logo'] = $logo_url;
                $message = 'Logo updated successfully!';
            } else {
                $message = 'Error: Failed to move uploaded logo file.';
            }
        }
    }

    // 3. Handle System Status Rows JSON
    if (isset($_POST['system_status_rows'])) {
        $rows = [];
        foreach ($_POST['system_status_rows'] as $row) {
            if (!empty($row['title']) || !empty($row['value'])) {
                $rows[] = [
                    'title' => trim($row['title']),
                    'value' => trim($row['value'])
                ];
            }
        }
        $json_val = json_encode($rows);
        $stmt = $pdo->prepare("SELECT * FROM settings WHERE name = 'system_status_rows'");
        $stmt->execute();
        if ($stmt->fetch()) {
            $pdo->prepare("UPDATE settings SET value = ? WHERE name = 'system_status_rows'")->execute([$json_val]);
        } else {
            $pdo->prepare("INSERT INTO settings (name, value) VALUES ('system_status_rows', ?)")->execute([$json_val]);
        }
    }

    // 4. Handle Default Avatar Upload
    if (isset($_FILES['default_user_avatar']) && $_FILES['default_user_avatar']['error'] == 0) {
        $upload_dir = __DIR__ . '/../assets/images/';
        if (!is_dir($upload_dir)) {
            if (!@mkdir($upload_dir, 0777, true)) {
                $message = 'Error: Could not create images directory.';
            }
        }

        if (is_dir($upload_dir)) {
            // Remove old avatar files
            $old_avatar = get_setting($pdo, 'default_user_avatar', '');
            if ($old_avatar && file_exists(__DIR__ . '/../' . $old_avatar) && strpos($old_avatar, 'user-avatar.png') === false) {
                @unlink(__DIR__ . '/../' . $old_avatar);
            }

            $ext = pathinfo($_FILES['default_user_avatar']['name'], PATHINFO_EXTENSION);
            $new_name = 'default_avatar_' . time() . '.' . $ext;
            $dest = $upload_dir . $new_name;
            
            if (move_uploaded_file($_FILES['default_user_avatar']['tmp_name'], $dest)) {
                $avatar_url = 'assets/images/' . $new_name;
                $stmt = $pdo->prepare("SELECT * FROM settings WHERE name = 'default_user_avatar'");
                $stmt->execute();
                if ($stmt->fetch()) {
                    $pdo->prepare("UPDATE settings SET value = ? WHERE name = 'default_user_avatar'")->execute([$avatar_url]);
                } else {
                    $pdo->prepare("INSERT INTO settings (name, value) VALUES ('default_user_avatar', ?)")->execute([$avatar_url]);
                }
                $settings['default_user_avatar'] = $avatar_url;
                $message = 'Default avatar updated successfully!';
            } else {
                $message = 'Error: Failed to move default avatar file.';
            }
        }
    }

    $message = 'Settings updated successfully!';
}

$settings_raw = $pdo->query("SELECT * FROM settings")->fetchAll();
$settings = [];
foreach ($settings_raw as $s) {
    $settings[$s['name']] = $s['value'];
}

$homeButtonSettings = [
    'home_show_deposit_button' => 'Show Recharge Button',
    'home_show_withdraw_button' => 'Show Payout Button',
    'home_show_deposit_logs' => 'Show Recharge Logs',
    'home_show_withdraw_logs' => 'Show Payout Logs',
    'home_show_transactions' => 'Show Transaction Button',
    'home_show_team' => 'Show My Team Button',
    'home_show_pools' => 'Show Pools Button',
    'home_show_fbr_verified' => 'Show FBR Verified Button',
    'home_show_scap_verified' => 'Show SCAP Verified Button',
    'home_show_live_chat' => 'Show Live Chat Button',
    'home_show_logout' => 'Show Logout Button',
    'home_show_whatsapp_channel' => 'Show WhatsApp Channel',
    'home_show_whatsapp_admin' => 'Show WhatsApp Admin',
    'home_show_whatsapp_group' => 'Show WhatsApp Group',
];

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="admin-page-heading py-3 mb-4">
                <h4 class="fw-bold"><span class="text-muted fw-light">Settings /</span> <?php echo htmlspecialchars($settings['site_name'] ?? 'Sparkx'); ?> Settings</h4>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-12">
                    <form method="POST" enctype="multipart/form-data">
                        <!-- General Settings Card -->
                        <div class="card mb-4">
                            <h5 class="card-header">General Information</h5>
                            <div class="card-body">
                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Site Logo</label>
                                        <div class="d-flex align-items-center admin-media-field">
                                            <img src="<?php echo SITE_URL . '/' . ($settings['site_logo'] ?? 'assets/images/logoIcon/logo.png'); ?>" alt="Logo" class="rounded me-3" style="max-height: 50px;">
                                            <input class="form-control" type="file" name="site_logo">
                                        </div>
                                    </div>

                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Default User Avatar</label>
                                        <div class="d-flex align-items-center admin-media-field">
                                            <img src="<?php echo SITE_URL . '/' . ($settings['default_user_avatar'] ?? 'assets/images/user-avatar.png'); ?>" alt="Default Avatar" class="rounded me-3" style="max-height: 50px; width: 50px; object-fit: cover;">
                                            <input class="form-control" type="file" name="default_user_avatar">
                                        </div>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Site Name</label>
                                        <input class="form-control" type="text" name="settings[site_name]" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'Sparkx'); ?>">
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Site Tagline</label>
                                        <input class="form-control" type="text" name="settings[site_tagline]" value="<?php echo htmlspecialchars($settings['site_tagline'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <label class="form-label">Currency Symbol (PKR Mode)</label>
                                        <input class="form-control" type="text" name="settings[currency_symbol]" value="<?php echo htmlspecialchars($settings['currency_symbol'] ?? 'RS'); ?>">
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <label class="form-label">System Currency Mode</label>
                                        <select class="form-select" name="settings[currency_mode]">
                                            <option value="pkr" <?php echo ($settings['currency_mode'] ?? 'pkr') == 'pkr' ? 'selected' : ''; ?>>PKR (Local Mode)</option>
                                            <option value="usdt" <?php echo ($settings['currency_mode'] ?? 'pkr') == 'usdt' ? 'selected' : ''; ?>>USDT (International Mode)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <label class="form-label">USDT to PKR Rate (Fixed)</label>
                                        <input class="form-control" type="number" step="0.01" name="settings[usdt_pkr_rate]" value="<?php echo htmlspecialchars($settings['usdt_pkr_rate'] ?? '290'); ?>">
                                        <small class="text-muted">Used for calculations in USDT mode.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Finance & Restrictions Card -->
                        <div class="card mb-4">
                            <h5 class="card-header">Finance & Restrictions</h5>
                            <div class="card-body">
                                <div class="row">
                                    <div class="mb-3 col-md-4">
                                        <label class="form-label">Recharge Fee (%)</label>
                                        <input class="form-control" type="number" step="0.01" name="settings[deposit_fee_pct]" value="<?php echo htmlspecialchars($settings['deposit_fee_pct'] ?? '0'); ?>">
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <label class="form-label">Payout Fee (%)</label>
                                        <input class="form-control" type="number" step="0.01" name="settings[withdrawal_fee_pct]" value="<?php echo htmlspecialchars($settings['withdrawal_fee_pct'] ?? '0'); ?>">
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <label class="form-label">Plan Cancel Fee (%)</label>
                                        <input class="form-control" type="number" step="0.1" name="settings[node_cancel_fee_pct]" value="<?php echo htmlspecialchars($settings['node_cancel_fee_pct'] ?? '25'); ?>">
                                        <small class="text-muted">Penalty if canceled before 4 weeks.</small>
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <label class="form-label">Profit Distribution Mode</label>
                                        <select class="form-select" name="settings[profit_distribution_mode]" id="profitDistributionMode">
                                            <option value="daily" <?php echo ($settings['profit_distribution_mode'] ?? 'daily') == 'daily' ? 'selected' : ''; ?>>Daily (Mon-Fri / 5 Days)</option>
                                            <option value="everyday" <?php echo ($settings['profit_distribution_mode'] ?? 'daily') == 'everyday' ? 'selected' : ''; ?>>Everyday (Mon-Sun / 7 Days)</option>
                                            <option value="weekly" <?php echo ($settings['profit_distribution_mode'] ?? 'daily') == 'weekly' ? 'selected' : ''; ?>>Weekly (Every Friday)</option>
                                            <option value="selected_day" <?php echo ($settings['profit_distribution_mode'] ?? 'daily') == 'selected_day' ? 'selected' : ''; ?>>Selected Day (Only on a specific day)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3 col-md-4" id="profitDistributionDayContainer" style="<?php echo ($settings['profit_distribution_mode'] ?? 'daily') === 'selected_day' ? '' : 'display: none;'; ?>">
                                        <label class="form-label">Profit Distribution Day</label>
                                        <select class="form-select" name="settings[profit_distribution_day]">
                                            <?php
                                            $days_list = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                            foreach ($days_list as $day) {
                                                $selected = ($settings['profit_distribution_day'] ?? 'Friday') == $day ? 'selected' : '';
                                                echo "<option value=\"$day\" $selected>$day</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Payout Day Restriction</label>
                                        <select class="form-select" name="settings[withdraw_restriction]">
                                            <option value="enabled" <?php echo ($settings['withdraw_restriction'] ?? 'enabled') == 'enabled' ? 'selected' : ''; ?>>Enabled (Only on selected day)</option>
                                            <option value="disabled" <?php echo ($settings['withdraw_restriction'] ?? 'enabled') == 'disabled' ? 'selected' : ''; ?>>Disabled (Any day)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Payout Day</label>
                                        <select class="form-select" name="settings[withdrawal_day]">
                                            <?php
                                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                            foreach ($days as $day) {
                                                $selected = ($settings['withdrawal_day'] ?? 'Saturday') == $day ? 'selected' : '';
                                                echo "<option value=\"$day\" $selected>$day</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Recharge Day Restriction</label>
                                        <select class="form-select" name="settings[deposit_restriction]">
                                            <option value="enabled" <?php echo ($settings['deposit_restriction'] ?? 'enabled') == 'enabled' ? 'selected' : ''; ?>>Enabled</option>
                                            <option value="disabled" <?php echo ($settings['deposit_restriction'] ?? 'enabled') == 'disabled' ? 'selected' : ''; ?>>Disabled</option>
                                        </select>
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Recharge Day</label>
                                        <select class="form-select" name="settings[deposit_day]">
                                            <?php
                                            foreach ($days as $day) {
                                                $selected = ($settings['deposit_day'] ?? 'Sunday') == $day ? 'selected' : '';
                                                echo "<option value=\"$day\" $selected>$day</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="mb-3 col-md-12">
                                        <label class="form-label fw-bold text-primary">Deposit Instructions (HTML allowed)</label>
                                        <textarea class="form-control" name="settings[deposit_instructions]" rows="6" placeholder="Write global deposit guidelines, custom wallet addresses, or rules here..."><?php echo htmlspecialchars($settings['deposit_instructions'] ?? ''); ?></textarea>
                                        <small class="text-muted">These instructions will be displayed dynamically on the User Deposit page.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Status Popup Configurator -->
                        <div class="card mb-4">
                            <h5 class="card-header">System Status Popup Configurator</h5>
                            <div class="card-body">
                                <p class="text-muted mb-3">Add, modify, or remove custom status rows that are displayed in the user dashboard announcement popup.</p>
                                <div id="status-rows-container">
                                    <?php
                                    $status_rows = json_decode($settings['system_status_rows'] ?? '[]', true);
                                    if (empty($status_rows)) {
                                        $status_rows = [
                                            ['title' => 'Min Deposit', 'value' => '300 PKR'],
                                            ['title' => 'Min Withdraw', 'value' => '30 PKR'],
                                            ['title' => 'Withdraw Fee', 'value' => '3%'],
                                            ['title' => 'Withdraw Time', 'value' => '1 Hour To 24 Hour'],
                                            ['title' => 'Referral Bonus', 'value' => 'Upto 15%'],
                                            ['title' => 'Referral Earning Bonus', 'value' => 'Upto 19%']
                                        ];
                                    }
                                    foreach ($status_rows as $i => $row):
                                    ?>
                                    <div class="row mb-3 status-row-item align-items-center">
                                        <div class="col-md-5">
                                            <label class="form-label">Row Title</label>
                                            <input type="text" class="form-control" name="system_status_rows[<?php echo $i; ?>][title]" value="<?php echo htmlspecialchars($row['title']); ?>" required>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">Row Value</label>
                                            <input type="text" class="form-control" name="system_status_rows[<?php echo $i; ?>][value]" value="<?php echo htmlspecialchars($row['value']); ?>" required>
                                        </div>
                                        <div class="col-md-2 mt-4 text-center">
                                            <button type="button" class="btn btn-danger btn-sm delete-status-row"><i class="bx bx-trash"></i> Remove</button>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" id="add-status-row" class="btn btn-secondary mt-2"><i class="bx bx-plus"></i> Add New Status Row</button>
                            </div>
                        </div>

                        <!-- Important Announcement Box Card -->
                        <div class="card mb-4">
                            <h5 class="card-header text-primary fw-bold">Important Announcement Box (User Dashboard)</h5>
                            <div class="card-body">
                                <p class="text-muted mb-3">Customize the texts displayed inside the "Important Announcement" box at the top of the user dashboard page.</p>
                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Announcement Line 1</label>
                                        <input class="form-control" type="text" name="settings[announcement_line_1]" value="<?php echo htmlspecialchars($settings['announcement_line_1'] ?? 'The previous channel has been deleted ⚠️'); ?>">
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Announcement Line 2</label>
                                        <input class="form-control" type="text" name="settings[announcement_line_2]" value="<?php echo htmlspecialchars($settings['announcement_line_2'] ?? 'Join the new official channel to stay updated 🚀'); ?>">
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Announcement Button Text</label>
                                        <input class="form-control" type="text" name="settings[announcement_btn_text]" value="<?php echo htmlspecialchars($settings['announcement_btn_text'] ?? '👉 Join Now Channel 🎁'); ?>">
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Announcement Footer Text</label>
                                        <input class="form-control" type="text" name="settings[announcement_footer]" value="<?php echo htmlspecialchars($settings['announcement_footer'] ?? 'Join the new channel & claim your bonus 🎁'); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Social & Support Card -->
                        <div class="card mb-4">
                            <h5 class="card-header">Social & Support</h5>
                            <div class="card-body">
                                <div class="row">
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">WhatsApp Channel URL</label>
                                        <input class="form-control" type="text" name="settings[whatsapp_channel_url]" value="<?php echo htmlspecialchars($settings['whatsapp_channel_url'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">WhatsApp Group URL</label>
                                        <input class="form-control" type="text" name="settings[whatsapp_group_url]" value="<?php echo htmlspecialchars($settings['whatsapp_group_url'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">WhatsApp Admin Link</label>
                                        <input class="form-control" type="text" name="settings[whatsapp_admin_url]" value="<?php echo htmlspecialchars($settings['whatsapp_admin_url'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">WhatsApp Support Link / Customer Service</label>
                                        <input class="form-control" type="text" name="settings[whatsapp_support_link]" value="<?php echo htmlspecialchars($settings['whatsapp_support_link'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">WhatsApp Support Number (Show in UI)</label>
                                        <input class="form-control" type="text" name="settings[whatsapp_number]" value="<?php echo htmlspecialchars($settings['whatsapp_number'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Support Phone</label>
                                        <input class="form-control" type="text" name="settings[support_phone]" value="<?php echo htmlspecialchars($settings['support_phone'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Facebook Page URL</label>
                                        <input class="form-control" type="text" name="settings[facebook_page_url]" value="<?php echo htmlspecialchars($settings['facebook_page_url'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Facebook Messenger Username / Link</label>
                                        <input class="form-control" type="text" name="settings[facebook_contact_url]" value="<?php echo htmlspecialchars($settings['facebook_contact_url'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-2 text-end admin-sticky-actions">
                            <button type="submit" class="btn btn-primary btn-lg px-5">SAVE ALL CHANGES</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add status row handler
    document.getElementById('add-status-row').addEventListener('click', function() {
        const container = document.getElementById('status-rows-container');
        const rowCount = container.getElementsByClassName('status-row-item').length;
        
        const newRow = document.createElement('div');
        newRow.className = 'row mb-3 status-row-item align-items-center';
        newRow.innerHTML = `
            <div class="col-md-5">
                <label class="form-label">Row Title</label>
                <input type="text" class="form-control" name="system_status_rows[${rowCount}][title]" placeholder="e.g. Min Deposit" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Row Value</label>
                <input type="text" class="form-control" name="system_status_rows[${rowCount}][value]" placeholder="e.g. 300 PKR" required>
            </div>
            <div class="col-md-2 mt-4 text-center">
                <button type="button" class="btn btn-danger btn-sm delete-status-row"><i class="bx bx-trash"></i> Remove</button>
            </div>
        `;
        container.appendChild(newRow);
        
        // Bind delete handler to new button
        newRow.querySelector('.delete-status-row').addEventListener('click', function() {
            newRow.remove();
        });
    });

    // Bind delete handlers to existing rows
    document.querySelectorAll('.delete-status-row').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.status-row-item').remove();
        });
    });

    // Profit Distribution Mode dynamic toggle selector
    const profitModeSelect = document.getElementById('profitDistributionMode');
    const profitDayContainer = document.getElementById('profitDistributionDayContainer');
    if (profitModeSelect && profitDayContainer) {
        profitModeSelect.addEventListener('change', function() {
            if (this.value === 'selected_day') {
                profitDayContainer.style.display = '';
            } else {
                profitDayContainer.style.display = 'none';
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
