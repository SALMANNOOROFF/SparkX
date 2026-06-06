<?php 
    $title = "Sparkx - My Profile";
    $base_url = "../..";

    require_once '../../includes/auth_check.php';

    // Handle profile update form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
        $name = mysqli_real_escape_string($conn, trim($_POST['name']));
        $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
        
        if (!empty($name) && !empty($phone)) {
            $update_q = mysqli_query($conn, "UPDATE users SET name = '$name', phone = '$phone' WHERE id = '$user_id'");
            if ($update_q) {
                // Re-fetch user data to reflect changes immediately
                $user_query = mysqli_query($conn, "SELECT u.*, w.deposit_balance, w.earning_balance, w.total_invested, w.total_withdrawn 
                                                   FROM users u 
                                                   LEFT JOIN wallets w ON u.id = w.user_id 
                                                   WHERE u.id = '$user_id'");
                $user_data = mysqli_fetch_assoc($user_query);
                
                // Update session name just in case it is used elsewhere
                $_SESSION['user_name'] = $name;
                
                $success_msg = "Account details saved successfully!";
            } else {
                $error_msg = "Database update failed. Please try again.";
            }
        } else {
            $error_msg = "All fields (Full Name, Phone) are required.";
        }
    }

    $custom_js = <<<'EOD'
    <script>
        const updatePasswordRoute = 'update_password.php';
        
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching logic
            const tabs = document.querySelectorAll('.profile-tab-modern');
            const tabContents = document.querySelectorAll('.profile-tab-content-modern');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const targetTab = this.getAttribute('data-tab');
                    
                    // Remove active class from all tabs and contents
                    tabs.forEach(t => t.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to clicked tab and corresponding content
                    this.classList.add('active');
                    const targetContent = document.getElementById(targetTab + 'Tab');
                    if (targetContent) {
                        targetContent.classList.add('active');
                    }
                });
            });

            // Password visibility toggles
            const passwordToggles = document.querySelectorAll('.profile-password-toggle');
            passwordToggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const passwordInput = document.getElementById(targetId);
                    const icon = this.querySelector('i');
                    
                    if (passwordInput && icon) {
                        if (passwordInput.type === 'password') {
                            passwordInput.type = 'text';
                            icon.classList.remove('fa-eye');
                            icon.classList.add('fa-eye-slash');
                        } else {
                            passwordInput.type = 'password';
                            icon.classList.remove('fa-eye-slash');
                            icon.classList.add('fa-eye');
                        }
                    }
                });
            });

            // Avatar container and AJAX upload
            const avatarContainer = document.getElementById('avatarContainer');
            const fileInput = document.getElementById('profileImageInput');
            const avatarImage = document.getElementById('avatarImage');
            
            if (avatarContainer && fileInput) {
                avatarContainer.addEventListener('click', function() {
                    fileInput.click();
                });
                
                fileInput.addEventListener('change', function() {
                    if (fileInput.files.length === 0) return;
                    
                    const file = fileInput.files[0];
                    const formData = new FormData();
                    formData.append('avatar', file);
                    
                    // Show a temporary spinner or opacity to show loading
                    avatarContainer.style.opacity = '0.5';
                    
                    fetch('upload_avatar.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        avatarContainer.style.opacity = '1';
                        if (data.success) {
                            // Update avatar image source
                            avatarImage.src = data.avatar_url;
                            
                            // Update header avatar too
                            const headerAvatars = document.querySelectorAll('.header-user-avatar');
                            headerAvatars.forEach(img => {
                                img.src = data.avatar_url;
                            });
                            
                            // Show premium notification
                            showNotification('Profile picture updated successfully!', 'success');
                        } else {
                            showNotification(data.message || 'Upload failed', 'error');
                        }
                    })
                    .catch(error => {
                        avatarContainer.style.opacity = '1';
                        console.error('Error uploading avatar:', error);
                        showNotification('An error occurred during upload.', 'error');
                    });
                });
            }

            // Save Password handler
            const savePasswordBtn = document.getElementById('savePasswordBtn');
            if (savePasswordBtn) {
                savePasswordBtn.addEventListener('click', function() {
                    const currentPassword = document.getElementById('currentPassword');
                    const newPassword = document.getElementById('newPassword');
                    const confirmPassword = document.getElementById('confirmPassword');
                    
                    if (!currentPassword || !currentPassword.value) {
                        showNotification('Please enter your current password', 'error');
                        return;
                    }
                    
                    if (!newPassword || !newPassword.value) {
                        showNotification('Please enter a new password', 'error');
                        return;
                    }
                    
                    if (!confirmPassword || !confirmPassword.value) {
                        showNotification('Please confirm your new password', 'error');
                        return;
                    }
                    
                    if (newPassword.value !== confirmPassword.value) {
                        showNotification('New passwords do not match', 'error');
                        return;
                    }
                    
                    if (newPassword.value.length < 8) {
                        showNotification('Password must be at least 8 characters', 'error');
                        return;
                    }
                    
                    const originalText = this.innerHTML;
                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Updating...</span>';
                    
                    fetch(updatePasswordRoute, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            current_password: currentPassword.value,
                            new_password: newPassword.value,
                            new_password_confirmation: confirmPassword.value
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.disabled = false;
                        this.innerHTML = originalText;
                        
                        if (data.success) {
                            currentPassword.value = '';
                            newPassword.value = '';
                            confirmPassword.value = '';
                            showNotification('Password updated successfully!', 'success');
                        } else {
                            showNotification(data.message || 'Failed to update password', 'error');
                        }
                    })
                    .catch(error => {
                        this.disabled = false;
                        this.innerHTML = originalText;
                        console.error('Error:', error);
                        showNotification('An error occurred while updating password', 'error');
                    });
                });
            }
        });

        // Safe helper to show alert notifications in modern style
        function showNotification(message, type = 'success') {
            const existing = document.querySelector('.profile-notification');
            if (existing) {
                existing.remove();
            }
            
            const notification = document.createElement('div');
            notification.className = `profile-notification profile-notification-${type}`;
            notification.textContent = message;
            
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                background: ${type === 'success' ? '#2ecc71' : '#e74c3c'};
                color: white;
                border-radius: 10px;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
                z-index: 10000;
                animation: slideInRight 0.3s ease-out;
                font-weight: 500;
                font-size: 0.875rem;
                font-family: 'Poppins', sans-serif;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 4000);
        }
    </script>
EOD;
    include('../../components/layout_top.php'); 
?>

                <div class="profile-page-modern">
                    <!-- Hero Section (Desktop Only) -->
                    <div class="profile-hero-section profile-hero-desktop">
                        <div class="profile-hero-content">
                            <h1 class="profile-hero-title">My Profile</h1>
                            <p class="profile-hero-subtitle">Manage your account settings and personal information</p>
                        </div>
                    </div>

                    <!-- Profile Card -->
                    <div class="profile-main-card">
                        <!-- Profile Header (Desktop Only) -->
                        <div class="profile-header-modern profile-header-desktop">
                            <div class="profile-header-left-modern">
                                <div class="profile-avatar-modern" id="avatarContainer" style="cursor: pointer; position: relative;">
                                    <?php 
                                        $avatar_src = 'https://ui-avatars.com/api/?name=' . urlencode($user_data['name']) . '&background=CC44FF&color=fff&size=128&bold=true';
                                        if (!empty($user_data['profile_image'])) {
                                            $avatar_src = $base_url . '/' . $user_data['profile_image'];
                                        }
                                    ?>
                                    <img src="<?php echo $avatar_src; ?>" alt="Profile Avatar" id="avatarImage" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                    <div class="profile-avatar-badge">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                    <input type="file" id="profileImageInput" accept="image/*" style="display: none;">
                                </div>
                                <div class="profile-info-modern">
                                    <h2 class="profile-name-modern"><?php echo htmlspecialchars($user_data['name']); ?></h2>
                                    <p class="profile-email-modern"><?php echo htmlspecialchars($user_data['email']); ?></p>
                                    <div class="profile-rank-modern">
                                        <div class="profile-rank-icon">
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <span class="profile-rank-text"><?php echo ($user_data['role'] === 'admin') ? 'Admin' : 'Member'; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Header -->
                        <div class="profile-mobile-header">
                            <h2 class="profile-mobile-title">Account</h2>
                        </div>

                        <!-- Navigation Tabs -->
                        <div class="profile-tabs-modern">
                            <button class="profile-tab-modern active" data-tab="account">
                                <span class="profile-tab-text">Account</span>
                            </button>
                            <button class="profile-tab-modern" data-tab="password">
                                <span class="profile-tab-text">Change Password</span>
                            </button>
                        </div>

                        <!-- Account Tab Content -->
                        <div class="profile-tab-content-modern active" id="accountTab">
                            <div class="profile-tab-header-modern profile-tab-header-desktop">
                                <h3 class="profile-tab-title-modern">Account Information</h3>
                                <p class="profile-tab-subtitle-modern">Update your personal details below to keep your profile accurate</p>
                            </div>

                            <?php if (isset($success_msg)): ?>
                                <div style="background: rgba(46, 204, 113, 0.15); border: 1px solid rgba(46, 204, 113, 0.3); color: #2ecc71; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                                    <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i>
                                    <div style="font-size: 0.9rem; font-weight: 500;"><?php echo $success_msg; ?></div>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($error_msg)): ?>
                                <div style="background: rgba(231, 76, 60, 0.15); border: 1px solid rgba(231, 76, 60, 0.3); color: #e74c3c; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                                    <i class="fas fa-times-circle" style="font-size: 1.2rem;"></i>
                                    <div style="font-size: 0.9rem; font-weight: 500;"><?php echo $error_msg; ?></div>
                                </div>
                            <?php endif; ?>

                            <form class="profile-form-modern" method="POST" action="">
                                <div class="profile-form-grid-modern">
                                    <div class="profile-form-group-modern">
                                        <label class="profile-form-label-modern"><span>Full Name</span></label>
                                        <div class="profile-input-wrapper">
                                            <i class="fas fa-user profile-input-icon"></i>
                                            <input type="text" name="name" class="profile-form-input-modern" value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
                                        </div>
                                    </div>

                                    <div class="profile-form-group-modern">
                                        <label class="profile-form-label-modern"><span>Email</span></label>
                                        <div class="profile-input-wrapper">
                                            <i class="fas fa-envelope profile-input-icon"></i>
                                            <input type="email" class="profile-form-input-modern" value="<?php echo htmlspecialchars($user_data['email']); ?>" readonly style="background: rgba(255,255,255,0.02); opacity: 0.6; cursor: not-allowed;">
                                        </div>
                                    </div>

                                    <div class="profile-form-group-modern">
                                        <label class="profile-form-label-modern"><span>Phone</span></label>
                                        <div class="profile-input-wrapper">
                                            <i class="fas fa-phone profile-input-icon"></i>
                                            <input type="tel" name="phone" class="profile-form-input-modern" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>" required placeholder="Enter phone number">
                                        </div>
                                    </div>

                                    <div class="profile-form-group-modern">
                                        <label class="profile-form-label-modern"><span>Referral Code (UID)</span></label>
                                        <div class="profile-input-wrapper">
                                            <i class="fas fa-id-card profile-input-icon"></i>
                                            <input type="text" class="profile-form-input-modern" value="<?php echo htmlspecialchars($user_data['referral_code'] ?? ''); ?>" readonly style="background: rgba(255,255,255,0.02); opacity: 0.6; cursor: not-allowed;">
                                        </div>
                                    </div>
                                </div>

                                <div class="profile-form-actions-modern">
                                    <button type="submit" name="update_profile" class="profile-save-btn-modern">
                                        <i class="fas fa-save"></i>
                                        <span>Save Changes</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Change Password Tab Content -->
                        <div class="profile-tab-content-modern" id="passwordTab">
                            <div class="profile-tab-header-modern profile-tab-header-desktop">
                                <h3 class="profile-tab-title-modern">Change Password</h3>
                                <p class="profile-tab-subtitle-modern">Update your password to keep your account secure</p>
                            </div>

                            <div class="profile-form-modern">
                                <div class="profile-form-grid-modern">
                                    <div class="profile-form-group-modern">
                                        <label class="profile-form-label-modern">
                                            <i class="fas fa-lock"></i>
                                            <span>Current Password</span>
                                        </label>
                                        <div class="profile-input-wrapper">
                                            <input type="password" class="profile-form-input-modern" id="currentPassword" placeholder="Enter current password">
                                            <button type="button" class="profile-password-toggle" data-target="currentPassword"><i class="fas fa-eye"></i></button>
                                        </div>
                                    </div>

                                    <div class="profile-form-group-modern">
                                        <label class="profile-form-label-modern">
                                            <i class="fas fa-lock"></i>
                                            <span>New Password</span>
                                        </label>
                                        <div class="profile-input-wrapper">
                                            <input type="password" class="profile-form-input-modern" id="newPassword" placeholder="Enter new password (min. 8 characters)">
                                            <button type="button" class="profile-password-toggle" data-target="newPassword"><i class="fas fa-eye"></i></button>
                                        </div>
                                    </div>

                                    <div class="profile-form-group-modern">
                                        <label class="profile-form-label-modern">
                                            <i class="fas fa-lock"></i>
                                            <span>Confirm New Password</span>
                                        </label>
                                        <div class="profile-input-wrapper">
                                            <input type="password" class="profile-form-input-modern" id="confirmPassword" placeholder="Confirm new password">
                                            <button type="button" class="profile-password-toggle" data-target="confirmPassword"><i class="fas fa-eye"></i></button>
                                        </div>
                                    </div>
                                </div>

                                <div class="profile-form-actions-modern">
                                    <button class="profile-save-btn-modern" id="savePasswordBtn">
                                        <i class="fas fa-save"></i>
                                        <span>Update Password</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

<?php include('../../components/layout_bottom.php'); ?>
