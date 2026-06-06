/**
 * Referrals Page - JavaScript
 * Handles referrals page interactions
 */

(function() {
    'use strict';

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        highlightActiveNavItem();
        initCopyButtons();
        initClaimButton();
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
                            (currentPath.includes('/user/dashboard/referrals') && linkPath.includes('/referrals'))) {
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
     * Initialize Copy Buttons
     */
    function initCopyButtons() {
        const copyButtons = document.querySelectorAll('.referrals-tool-copy-btn-modern, .referral-copy-btn');
        
        copyButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-copy');
                const input = document.getElementById(targetId);
                
                if (!input) return;
                
                // Select and copy text
                input.select();
                input.setSelectionRange(0, 99999); // For mobile devices
                
                try {
                    // Use modern Clipboard API if available
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(input.value).then(() => {
                            showCopySuccess(this);
                        }).catch(() => {
                            // Fallback to execCommand
                            document.execCommand('copy');
                            showCopySuccess(this);
                        });
                    } else {
                        // Fallback to execCommand
                        document.execCommand('copy');
                        showCopySuccess(this);
                    }
                } catch (err) {
                    console.error('Failed to copy:', err);
                    showNotification('Failed to copy', 'error');
                }
            });
        });
    }

    /**
     * Show Copy Success Feedback
     */
    function showCopySuccess(button) {
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i>';
        button.classList.add('copied');
        
        // Show notification
        showNotification('Copied to clipboard!', 'success');
        
        // Reset after 2 seconds
        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.classList.remove('copied');
        }, 2000);
    }

    /**
     * Initialize Claim Button
     */
    function initClaimButton() {
        const claimBtn = document.querySelector('.referrals-claim-btn-modern, .referral-claim-btn');
        
        if (claimBtn) {
            claimBtn.addEventListener('click', function() {
                if (this.disabled) return;
                
                // Placeholder for claim action
                console.log('Claim earnings clicked');
                // You can add modal or API call here
                showNotification('Claim functionality will be available soon', 'info');
            });
        }
    }

    /**
     * Show Notification
     */
    function showNotification(message, type = 'success') {
        // Remove existing notification if any
        const existing = document.querySelector('.referral-notification');
        if (existing) {
            existing.remove();
        }
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `referral-notification referral-notification-${type}`;
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

