<?php
// admin/users.php
require_once __DIR__ . '/../config/config.php';
requireAdminLogin();

$page_title = 'All Customers';

$default_avatar = get_setting($pdo, 'default_user_avatar', 'assets/images/user-avatar.png');
$users = $pdo->query("SELECT u.*, w.deposit_balance, w.earning_balance FROM users u LEFT JOIN wallets w ON u.id = w.user_id ORDER BY u.created_at DESC")->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="layout-page">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="admin-page-heading py-3 mb-4">
                <h4 class="fw-bold"><span class="text-muted fw-light">Users /</span> All Customers</h4>
            </div>

            <div class="card admin-table-card">
                <h5 class="card-header">Customer List</h5>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Referral Code</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center flex-shrink-1">
                                            <?php 
                                            $profile_image = (!empty($user['profile_image'])) ? $user['profile_image'] : $default_avatar;
                                            if (strpos($profile_image, 'http') === false) {
                                                $profile_image = SITE_URL . '/' . $profile_image;
                                            }
                                            ?>
                                            <img src="<?php echo $profile_image; ?>" alt="Avatar" class="rounded-circle me-2" width="30" height="30" style="object-fit: cover;">
                                            <strong class="text-break"><?php echo htmlspecialchars($user['name']); ?></strong>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td><code><?php echo htmlspecialchars($user['referral_code']); ?></code></td>
                                    <td>
                                        <?php if ($user['is_active']): ?>
                                            <span class="badge bg-label-success me-1">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-label-danger me-1">Disabled</span>
                                        <?php endif; ?>
                                    </td>

                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="user_edit.php?id=<?php echo $user['id']; ?>"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                                                <a class="dropdown-item" href="user_transactions.php?id=<?php echo $user['id']; ?>"><i class="bx bx-list-ul me-1"></i> Transactions</a>
                                            </div>
                                        </div>
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

<?php include 'includes/footer.php'; ?>
