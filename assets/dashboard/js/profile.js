/**
 * Profile Page - JavaScript
 * Handles profile page interactions
 */

(function() {
    'use strict';

    // DOM Elements
    const tabs = document.querySelectorAll('.profile-tab-modern, .profile-tab');
    const tabContents = document.querySelectorAll('.profile-tab-content-modern, .profile-tab-content');
    const savePasswordBtn = document.getElementById('savePasswordBtn');
    const passwordToggles = document.querySelectorAll('.profile-password-toggle');

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        highlightActiveNavItem();
        initTabs();
        initPasswordChange();
        initPasswordToggles();
    });

    /**
     * Highlight Active Navigation Item
     */
    function highlightActiveNavItem() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
        
        navLinks.forEach(link => {
            const navItem = link.closest('.nav-item');
            if (navItem) {
                navItem.classList.remove('active');
                const href = link.getAttribute('href');
                if (href) {
                    try {
                        const linkUrl = new URL(href, window.location.origin);
                        const linkPath = linkUrl.pathname;
                        
                        // Check if current path matches the link path
                        if (currentPath === linkPath || 
                            (currentPath.includes('/user/dashboard/profile') && linkPath.includes('/profile'))) {
                            navItem.classList.add('active');
                        }
                    } catch (e) {
                        // Fallback for relative URLs
                        if (href.startsWith('/')) {
                            if (currentPath === href || currentPath.startsWith(href + '/')) {
                                navItem.classList.add('active');
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Initialize Tab Switching
     */
    function initTabs() {
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
    }

    /**
     * Initialize Password Toggles
     */
    function initPasswordToggles() {
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
    }

    /**
     * Initialize Password Change
     */
    function initPasswordChange() {
        // Save password button handler
        if (savePasswordBtn) {
            savePasswordBtn.addEventListener('click', function() {
                const currentPassword = document.getElementById('currentPassword');
                const newPassword = document.getElementById('newPassword');
                const confirmPassword = document.getElementById('confirmPassword');
                
                // Basic validation
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
                
                // Disable button during submission
                const originalText = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Updating...</span>';
                
                // Get CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                // Update password via AJAX
                fetch(typeof updatePasswordRoute !== 'undefined' ? updatePasswordRoute : '/user/dashboard/profile/update-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        current_password: currentPassword.value,
                        new_password: newPassword.value,
                        new_password_confirmation: confirmPassword.value
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Clear password fields
                        currentPassword.value = '';
                        newPassword.value = '';
                        confirmPassword.value = '';
                        
                        // Show success message
                        showNotification('Password updated successfully!', 'success');
                    } else {
                        showNotification(data.message || 'Failed to update password', 'error');
                    }
                    
                    // Re-enable button
                    this.disabled = false;
                    this.innerHTML = originalText;
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while updating password', 'error');
                    
                    // Re-enable button
                    this.disabled = false;
                    this.innerHTML = originalText;
                });
            });
        }
    }

    /**
     * Show Notification
     */
    function showNotification(message, type = 'success') {
        // Remove existing notification if any
        const existing = document.querySelector('.profile-notification');
        if (existing) {
            existing.remove();
        }
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `profile-notification profile-notification-${type}`;
        notification.textContent = message;
        
        // Add styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            background: ${type === 'success' ? 'var(--success-color)' : type === 'error' ? 'var(--danger-color)' : 'var(--info-color)'};
            color: white;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
            z-index: 10000;
            animation: slideInRight 0.3s ease-out;
            font-weight: 500;
            font-size: 0.875rem;
        `;
        
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 6000);
    }

    // Add animation styles
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

})();

