/**
 * app-core.js
 * Core UI utilities for Spark X Dashboard
 */

(function() {
    // === Toast Notification System ===
    function ensureToastStyles() {
        if (document.getElementById('appToastStyles')) return;
        const style = document.createElement('style');
        style.id = 'appToastStyles';
        style.textContent = `
            .app-toast-container{position:fixed;top:18px;left:50%;transform:translateX(-50%);z-index:100000;pointer-events:none;width:calc(100% - 32px);max-width:520px;}
            .app-toast{pointer-events:none;display:flex;align-items:center;gap:12px;padding:14px 16px;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.25);font-weight:600;line-height:1.35;}
            .app-toast__icon{width:34px;height:34px;border-radius:999px;display:flex;align-items:center;justify-content:center;flex:0 0 auto;background:rgba(255,255,255,.18);}
            .app-toast__icon i{color:#fff;font-size:16px;}
            .app-toast__text{color:#fff;font-size:14px;}
            .app-toast--success{background:#FFB21E;}
            .app-toast--error{background:#ff4444;}
            .app-toast--info{background:#6366F1;}
            .app-toast-enter{animation:appToastIn .25s ease-out;}
            .app-toast-exit{animation:appToastOut .25s ease-in forwards;}
            @keyframes appToastIn{from{transform:translateY(-8px);opacity:0}to{transform:translateY(0);opacity:1}}
            @keyframes appToastOut{from{transform:translateY(0);opacity:1}to{transform:translateY(-8px);opacity:0}}
        `;
        document.head.appendChild(style);
    }

    function getContainer() {
        let container = document.getElementById('appToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'appToastContainer';
            container.className = 'app-toast-container';
            document.body.appendChild(container);
        }
        return container;
    }

    window.showAppMessage = function(message, type = 'success') {
        ensureToastStyles();
        const container = getContainer();
        container.innerHTML = '';

        const toast = document.createElement('div');
        toast.className = `app-toast app-toast--${type} app-toast-enter`;

        const icon = document.createElement('div');
        icon.className = 'app-toast__icon';
        const iconI = document.createElement('i');
        iconI.className = type === 'success' ? 'fas fa-check' : (type === 'error' ? 'fas fa-times' : 'fas fa-info');
        icon.appendChild(iconI);

        const text = document.createElement('div');
        text.className = 'app-toast__text';
        text.textContent = message;

        toast.appendChild(icon);
        toast.appendChild(text);
        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.remove('app-toast-enter');
            toast.classList.add('app-toast-exit');
            setTimeout(() => {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 260);
        }, 6000);
    };

    window.showSuccessMessage = function(message) { window.showAppMessage(message, 'success'); };
    window.showErrorMessage = function(message) { window.showAppMessage(message, 'error'); };
    window.showInfoMessage = function(message) { window.showAppMessage(message, 'info'); };

    // Override browser alert
    if (typeof window.alert === 'function') {
        const originalAlert = window.alert;
        window.alert = function(message) {
            window.showInfoMessage(String(message ?? ''));
        };
    }

    // === Confirmation Dialog System ===
    window.showConfirmDialog = function(message, onConfirm, onCancel) {
        ensureToastStyles();
        const existing = document.getElementById('appConfirmOverlay');
        if (existing) existing.remove();

        const overlay = document.createElement('div');
        overlay.id = 'appConfirmOverlay';
        overlay.style.cssText = 'position:fixed;inset:0;z-index:100001;background:rgba(0,0,0,.55);display:flex;align-items:center;justify-content:center;padding:16px;';

        const modal = document.createElement('div');
        modal.style.cssText = 'width:100%;max-width:420px;border-radius:16px;background:#1f2433;border:1px solid rgba(255,255,255,.08);box-shadow:0 18px 50px rgba(0,0,0,.45);padding:18px;';

        const title = document.createElement('div');
        title.textContent = 'Confirm';
        title.style.cssText = 'font-weight:800;color:#fff;font-size:16px;margin-bottom:10px;';

        const body = document.createElement('div');
        body.textContent = message;
        body.style.cssText = 'color:rgba(255,255,255,.85);font-size:14px;line-height:1.5;margin-bottom:14px;white-space:pre-line;';

        const actions = document.createElement('div');
        actions.style.cssText = 'display:flex;gap:10px;justify-content:flex-end;';

        const cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.textContent = 'Cancel';
        cancelBtn.style.cssText = 'padding:10px 14px;border-radius:12px;border:1px solid rgba(255,255,255,.12);background:transparent;color:#fff;font-weight:700;cursor:pointer;';

        const okBtn = document.createElement('button');
        okBtn.type = 'button';
        okBtn.textContent = 'OK';
        okBtn.style.cssText = 'padding:10px 14px;border-radius:12px;border:none;background:#FFB21E;color:#fff;font-weight:800;cursor:pointer;';

        function close() { overlay.remove(); }

        cancelBtn.addEventListener('click', function() { close(); if (typeof onCancel === 'function') onCancel(); });
        okBtn.addEventListener('click', function() { close(); if (typeof onConfirm === 'function') onConfirm(); });
        overlay.addEventListener('click', function(e) { if (e.target === overlay) { close(); if (typeof onCancel === 'function') onCancel(); } });

        document.addEventListener('keydown', function escHandler(e) {
            if (e.key === 'Escape') {
                document.removeEventListener('keydown', escHandler);
                close();
                if (typeof onCancel === 'function') onCancel();
            }
        });

        actions.appendChild(cancelBtn);
        actions.appendChild(okBtn);
        modal.appendChild(title);
        modal.appendChild(body);
        modal.appendChild(actions);
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
    };

    // Auto-confirm for forms with data-confirm
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form && form.matches && form.matches('form[data-confirm]')) {
            e.preventDefault();
            const msg = form.getAttribute('data-confirm') || 'Are you sure?';
            window.showConfirmDialog(msg, function() {
                form.submit();
            });
        }
    }, true);

    // === Navigation Handlers ===
    window.toggleMobileSidebar = function(e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        
        const sidebar = document.getElementById('dashboardSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        if (!sidebar) return false;
        
        const isActive = sidebar.classList.contains('active');
        if (isActive) {
            sidebar.classList.remove('active');
            if (sidebarOverlay) sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        } else {
            sidebar.classList.add('active');
            if (sidebarOverlay) sidebarOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        return false;
    };

    window.toggleMobileNavVisibility = function() {
        const mobileNav = document.getElementById('mobileBottomNav');
        if (mobileNav) {
            if (window.innerWidth > 768) {
                mobileNav.style.display = 'none';
                mobileNav.style.visibility = 'hidden';
                mobileNav.style.opacity = '0';
            } else {
                mobileNav.style.display = '';
                mobileNav.style.visibility = '';
                mobileNav.style.opacity = '';
            }
        }
    };

    window.goBack = function() {
        if (window.history.length > 1) {
            const referrer = document.referrer;
            if (referrer && (referrer.includes('/user/dashboard') || referrer.includes('/user/dashboard.html'))) {
                window.history.back();
            } else {
                window.location.href = '../user/dashboard.html';
            }
        } else {
            window.location.href = '../user/dashboard.html';
        }
    };

    // Initialize core UI on DOM load
    document.addEventListener('DOMContentLoaded', function() {
        // Setup sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.removeAttribute('onclick');
            sidebarToggle.addEventListener('click', window.toggleMobileSidebar, true);
            sidebarToggle.addEventListener('touchend', function(e) { e.preventDefault(); window.toggleMobileSidebar(e); }, true);
        }

        // Setup mobile nav visibility
        window.toggleMobileNavVisibility();
        window.addEventListener('resize', window.toggleMobileNavVisibility);
    });
})();
/**
 * app-notifications.js
 * Notification management for Spark X Dashboard
 */

(function() {
    document.addEventListener('DOMContentLoaded', function() {
        const notificationIcon = document.getElementById('notificationIcon');
        const notificationPanel = document.getElementById('notificationPanel');
        const notificationPanelBody = document.getElementById('notificationPanelBody');
        const notificationBadge = document.getElementById('notificationBadge');
        const base = window.location.pathname.substring(0, window.location.pathname.indexOf('/user/dashboard'));
        const apiBase = `${base}/user/dashboard/notifications`;

        function isMobile() { return window.innerWidth <= 768; }

        window.loadUnreadCount = function() {
            fetch(`${apiBase}/unread-count.php`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && notificationBadge) {
                        if (data.count > 0) {
                            notificationBadge.textContent = data.count > 99 ? '99+' : data.count;
                            notificationBadge.style.display = 'flex';
                        } else {
                            notificationBadge.style.display = 'none';
                        }
                    }
                })
                .catch(error => console.error('Error loading unread count:', error));
        };

        window.loadNotifications = function() {
            if (!notificationPanelBody) return;
            
            notificationPanelBody.innerHTML = '<div class="notification-loading" style="text-align: center; padding: 1rem; color: var(--text-secondary);"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
            
            fetch(`${apiBase}/index.php?ajax=1`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.success || !data.notifications || data.notifications.length === 0) {
                        notificationPanelBody.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--text-secondary);">No notifications</div>';
                        return;
                    }
                    
                    let htmlContent = '';
                    data.notifications.slice(0, 5).forEach(notification => {
                        const isUnread = !parseInt(notification.is_read);
                        htmlContent += `
                            <div class="notification-item ${isUnread ? 'unread' : ''}" data-notification-id="${notification.id}" onclick="markNotificationAsRead(${notification.id})">
                                <div class="notification-icon-wrapper">
                                    <i class="fas fa-bell"></i>
                                    ${isUnread ? '<span class="notification-dot"></span>' : ''}
                                </div>
                                <div class="notification-content">
                                    <div class="notification-greeting">${notification.title}</div>
                                    <div class="notification-message">${notification.message}</div>
                                    <div class="notification-time">${notification.created_at}</div>
                                </div>
                            </div>
                        `;
                    });
                    notificationPanelBody.innerHTML = htmlContent;
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                    notificationPanelBody.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--text-secondary);">Error loading notifications</div>';
                });
        };

        window.markNotificationAsRead = function(notificationId) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch(`${apiBase}/read.php?id=${notificationId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ id: notificationId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
                    if (item) {
                        item.classList.remove('unread');
                        const dot = item.querySelector('.notification-dot');
                        if (dot) dot.remove();
                    }
                    loadUnreadCount();
                }
            })
            .catch(error => console.error('Error marking notification as read:', error));
        };
        const markAllReadHeaderBtn = document.getElementById('markAllReadHeaderBtn');
        if (markAllReadHeaderBtn) {
            markAllReadHeaderBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                fetch(`${apiBase}/read.php?id=all`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: 'all' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelectorAll('.notification-item.unread').forEach(item => {
                            item.classList.remove('unread');
                            const dot = item.querySelector('.notification-dot');
                            if (dot) dot.remove();
                        });
                        loadUnreadCount();
                    }
                })
                .catch(err => console.error('Error marking all as read:', err));
            });
        }

        if (notificationIcon) {
            notificationIcon.addEventListener('click', function(e) {
                e.stopPropagation();
                if (isMobile()) {
                    window.location.href = `${apiBase}`;
                    return;
                }
                if (notificationPanel) {
                    const isActive = notificationPanel.classList.contains('active');
                    notificationPanel.classList.toggle('active');
                    if (!isActive) loadNotifications();
                }
            });

            if (notificationPanel) {
                document.addEventListener('click', function(e) {
                    if (!isMobile()) {
                        const isClickInside = notificationPanel.contains(e.target) || notificationIcon.contains(e.target);
                        if (!isClickInside && notificationPanel.classList.contains('active')) {
                            notificationPanel.classList.remove('active');
                        }
                    }
                });
                document.addEventListener('keydown', function(e) {
                    if (!isMobile() && e.key === 'Escape' && notificationPanel.classList.contains('active')) {
                        notificationPanel.classList.remove('active');
                    }
                });
            }
        }

        // Initial load and periodic refresh
        loadUnreadCount();
        setInterval(loadUnreadCount, 30000);
    });
})();
/**
 * Dashboard - JavaScript
 * Handles sidebar, navigation, and interactive elements
 */

(function() {
    'use strict';

    // DOM Elements (assigned on DOMContentLoaded)
    let sidebar;
    let sidebarToggle;
    let balanceToggle;
    let balanceAmount;
    let eyeIcon;
    let eyeSlashIcon;

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        sidebar = document.getElementById('dashboardSidebar');
        sidebarToggle = document.getElementById('sidebarToggle');
        balanceToggle = document.getElementById('balanceToggle');
        balanceAmount = document.getElementById('balanceAmount');
        eyeIcon = document.getElementById('eyeIcon');
        eyeSlashIcon = document.getElementById('eyeSlashIcon');

        // Initialize sidebar toggle first
        if (sidebarToggle && sidebar) {
            initSidebarToggle();
        } else {
            console.warn('Sidebar toggle elements not found');
        }
        highlightActiveNavItem();
        // Only initialize balance toggle if on dashboard page
        if (balanceToggle) {
            initBalanceToggle();
        }
        initSmoothScroll();
        // Only initialize charts if on dashboard page
        if (document.getElementById('investmentChart') || document.getElementById('profitChart')) {
            initCharts();
        }
    });

    /**
     * Highlight Active Navigation Item
     */
    function highlightActiveNavItem() {
        // Normalize current path: remove trailing slash and convert to lowercase
        const currentPath = window.location.pathname.replace(/\/$/, '').toLowerCase();
        
        // 1. Highlight Desktop Sidebar Nav
        const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
        navLinks.forEach(link => {
            const navItem = link.closest('.nav-item');
            if (navItem) {
                navItem.classList.remove('active');
                const href = link.getAttribute('href');
                if (href) {
                    try {
                        const linkUrl = new URL(href, window.location.origin);
                        const linkPath = linkUrl.pathname.replace(/\/$/, '').toLowerCase();
                        if (currentPath === linkPath || currentPath === linkPath + '/index') {
                            navItem.classList.add('active');
                        }
                    } catch (e) {
                        const normalizedHref = href.split('?')[0].split('#')[0].replace(/\/$/, '').toLowerCase();
                        if (currentPath === normalizedHref || currentPath.endsWith(normalizedHref)) {
                            navItem.classList.add('active');
                        }
                    }
                }
            }
        });

        // 2. Highlight Mobile Bottom Navigation Items
        const mobileNavItems = document.querySelectorAll('.mobile-bottom-nav .mobile-nav-item');
        mobileNavItems.forEach(item => {
            item.classList.remove('active');
            const href = item.getAttribute('href');
            if (href) {
                try {
                    const linkUrl = new URL(href, window.location.origin);
                    const linkPath = linkUrl.pathname.replace(/\/$/, '').toLowerCase();
                    if (currentPath === linkPath || currentPath === linkPath + '/index') {
                        item.classList.add('active');
                    }
                } catch (e) {
                    const normalizedHref = href.split('?')[0].split('#')[0].replace(/\/$/, '').toLowerCase();
                    if (currentPath === normalizedHref || currentPath.endsWith(normalizedHref)) {
                        item.classList.add('active');
                    }
                }
            }
        });
    }

    /**
     * Sidebar Toggle (Mobile)
     */
    function initSidebarToggle() {
        if (!sidebarToggle || !sidebar) {
            console.warn('Sidebar toggle: Missing elements', { sidebarToggle, sidebar });
            return;
        }

        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        // Ensure button is clickable - multiple approaches
        sidebarToggle.style.pointerEvents = 'auto';
        sidebarToggle.style.cursor = 'pointer';
        sidebarToggle.style.zIndex = '10002';
        sidebarToggle.style.position = 'relative';
        sidebarToggle.setAttribute('tabindex', '0');
        sidebarToggle.setAttribute('role', 'button');
        sidebarToggle.setAttribute('aria-label', 'Toggle navigation menu');
        
        // Remove any existing event listeners by cloning the element
        const newToggle = sidebarToggle.cloneNode(true);
        sidebarToggle.parentNode.replaceChild(newToggle, sidebarToggle);
        const toggle = document.getElementById('sidebarToggle');

        // Toggle function
        function toggleSidebar(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
            }
            
            const isActive = sidebar.classList.contains('active');
            if (isActive) {
                sidebar.classList.remove('active');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.remove('active');
                }
                document.body.style.overflow = '';
            } else {
                sidebar.classList.add('active');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.add('active');
                }
                document.body.style.overflow = 'hidden';
            }
            
            return false;
        }

        // Handle click events - multiple event types
        toggle.addEventListener('click', toggleSidebar, true);
        toggle.addEventListener('mousedown', function(e) {
            e.preventDefault();
            toggleSidebar(e);
        }, true);
        
        // Handle touch events for better mobile support
        toggle.addEventListener('touchend', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleSidebar(e);
        }, true);
        
        toggle.addEventListener('touchstart', function(e) {
            e.stopPropagation();
        }, true);

        // Close sidebar when clicking overlay (mobile)
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        }

        // Close sidebar when clicking outside (mobile)
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (sidebar.classList.contains('active')) {
                    if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target) && !sidebarOverlay.contains(e.target)) {
                        sidebar.classList.remove('active');
                        if (sidebarOverlay) {
                            sidebarOverlay.classList.remove('active');
                        }
                        document.body.style.overflow = '';
                    }
                }
            }
        });

        // Ensure nav links work on mobile - use both click and touchstart
        const navLinks = document.querySelectorAll('.dashboard-sidebar .nav-link');
        navLinks.forEach(link => {
            // Handle click events
            link.addEventListener('click', function(e) {
                e.stopPropagation();
                // Allow navigation to proceed normally
                if (window.innerWidth <= 768) {
                    // Close sidebar after navigation on mobile
                    setTimeout(() => {
                        sidebar.classList.remove('active');
                        if (sidebarOverlay) {
                            sidebarOverlay.classList.remove('active');
                        }
                        document.body.style.overflow = '';
                    }, 300);
                }
            }, { passive: false });

            // Also handle touch events for better mobile support
            link.addEventListener('touchend', function(e) {
                e.stopPropagation();
                // Trigger click if it's a valid tap (not a scroll)
                if (!this.dataset.touchMoved) {
                    this.click();
                }
                delete this.dataset.touchMoved;
            }, { passive: true });

            let touchStartY = 0;
            link.addEventListener('touchstart', function(e) {
                touchStartY = e.touches[0].clientY;
                this.dataset.touchMoved = 'false';
            }, { passive: true });

            link.addEventListener('touchmove', function(e) {
                const touchY = e.touches[0].clientY;
                if (Math.abs(touchY - touchStartY) > 10) {
                    this.dataset.touchMoved = 'true';
                }
            }, { passive: true });
        });
    }

    /**
     * Balance Toggle (Show/Hide Balance)
     */
    function initBalanceToggle() {
        if (!balanceToggle || !balanceAmount) return;

        let isVisible = true;

        balanceToggle.addEventListener('click', function() {
            isVisible = !isVisible;

            if (isVisible) {
                balanceAmount.style.opacity = '1';
                eyeIcon.style.display = 'block';
                eyeSlashIcon.style.display = 'none';
            } else {
                balanceAmount.style.opacity = '0.3';
                eyeIcon.style.display = 'none';
                eyeSlashIcon.style.display = 'block';
            }
        });
    }


    /**
     * Smooth Scroll
     */
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href !== '') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
    }

    /**
     * Handle Window Resize
     */
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && sidebar) {
            sidebar.classList.remove('active');
        }
    });

    /**
     * Chat Widget Interaction
     */
    const chatWidget = document.querySelector('.chat-icon-container');
    if (chatWidget) {
        chatWidget.addEventListener('click', function() {
            // Placeholder for chat widget integration
            console.log('Chat widget clicked');
            // You can integrate with Tawk.to or other chat services here
        });
    }

    /**
     * Card Hover Effects
     */
    const cards = document.querySelectorAll('.summary-card, .balance-card, .live-earning-card, .active-investment-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    /**
     * Button Click Animations
     */
    const buttons = document.querySelectorAll('button, .social-btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Create ripple effect
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');

            this.appendChild(ripple);

            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });

    /**
     * Add Ripple Effect Styles
     */
    const style = document.createElement('style');
    style.textContent = `
        button, .social-btn {
            position: relative;
            overflow: hidden;
        }
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: scale(0);
            animation: ripple-animation 0.6s ease-out;
            pointer-events: none;
        }
        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

    /**
     * Notification Badge Animation
     */
    const notificationBadge = document.querySelector('.notification-badge');
    if (notificationBadge) {
        setInterval(() => {
            notificationBadge.style.animation = 'none';
            setTimeout(() => {
                notificationBadge.style.animation = 'pulse 1s ease-in-out';
            }, 10);
        }, 3000);
    }

    /**
     * Add Pulse Animation
     */
    const pulseStyle = document.createElement('style');
    pulseStyle.textContent = `
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
    `;
    document.head.appendChild(pulseStyle);

    /**
     * Live Earning Counter (Demo)
     */
    const earningAmount = document.querySelector('.earning-amount .amount');
    if (earningAmount) {
        let currentValue = 0;
        const targetValue = 0.000000;
        
        // Simulate live updates (demo)
        setInterval(() => {
            if (currentValue < targetValue) {
                currentValue += 0.000001;
                earningAmount.textContent = currentValue.toFixed(6);
            }
        }, 1000);
    }

    /**
     * Initialize Charts
     */
    function initCharts() {
        // Investment Overview Chart (Line Chart)
        const investmentCtx = document.getElementById('investmentChart');
        if (investmentCtx) {
            new Chart(investmentCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [
                        {
                            label: 'Earning',
                            data: [],
                            borderColor: '#00FF88',
                            backgroundColor: 'rgba(0, 255, 136, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Investment',
                            data: [],
                            borderColor: '#00D977',
                            backgroundColor: 'rgba(0, 217, 119, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 4,
                            ticks: {
                                stepSize: 1,
                                color: '#A0A0B0'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#A0A0B0'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)'
                            }
                        }
                    }
                }
            });
        }

        // Profit Analysis Chart (Bar Chart)
        const profitCtx = document.getElementById('profitChart');
        if (profitCtx) {
            new Chart(profitCtx, {
                type: 'bar',
                data: {
                    labels: ['2025-02', '2025-04', '2025-06', '2025-08', '2025-10', '2025-12'],
                    datasets: [{
                        label: 'Profit',
                        data: [],
                        backgroundColor: 'rgba(0, 255, 136, 0.6)',
                        borderColor: '#00FF88',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 4,
                            ticks: {
                                stepSize: 1,
                                color: '#A0A0B0'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#A0A0B0'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)'
                            }
                        }
                    }
                }
            });
        }
    }

})();

/**
 * User-side Chat Functionality
 */

(function() {
    let currentChatId = null;
    let echo = null;

    // Initialize Echo if available
    if (typeof window.Echo !== 'undefined') {
        echo = window.Echo;
    }

    // Chat Button and Modal
    const chatButton = document.getElementById('chatButton');
    const chatNotificationBadge = document.getElementById('chatNotificationBadge');
    const startChatModal = document.getElementById('startChatModal');
    const closeStartChatModal = document.getElementById('closeStartChatModal');
    const startChatForm = document.getElementById('startChatForm');
    const liveChatWindow = document.getElementById('liveChatWindow');
    const closeLiveChat = document.getElementById('closeLiveChat');
    const chatMessages = document.getElementById('chatMessages');
    const chatMessageInput = document.getElementById('chatMessageInput');
    const sendChatMessage = document.getElementById('sendChatMessage');
    const chatStatus = document.getElementById('chatStatus');

    // Open start chat modal or existing chat
    if (chatButton) {
        chatButton.addEventListener('click', function() {
            // Check for existing active chat first
            checkForActiveChat();
        });
    }

    function checkForActiveChat() {
        // Check if user is authenticated by checking for auth token or user data
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        const isAuthenticated = csrfToken && window.location.pathname.includes('/user/dashboard');
        
        let url = '/chat/active';
        
        // For guest users, try to get email from form if it exists
        if (!isAuthenticated) {
            const emailInput = document.getElementById('chatEmail');
            if (emailInput && emailInput.value) {
                url += '?email=' + encodeURIComponent(emailInput.value);
            } else {
                // No email yet, just show form
                if (startChatModal) {
                    startChatModal.style.display = 'flex';
                }
                return;
            }
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.has_active_chat) {
                    // Open existing chat
                    currentChatId = data.chat.id;
                    liveChatWindow.style.display = 'flex';
                    loadChatMessages();
                    subscribeToChat();
                    // Mark admin messages as read when opening chat
                    markAdminMessagesAsRead();
                } else {
                    // Open start chat form
                    if (startChatModal) {
                        startChatModal.style.display = 'flex';
                    }
                }
            })
            .catch(error => {
                console.error('Error checking for active chat:', error);
                // On error, open the form
                if (startChatModal) {
                    startChatModal.style.display = 'flex';
                }
            });
    }

    // Close start chat modal
    if (closeStartChatModal) {
        closeStartChatModal.addEventListener('click', function() {
            if (startChatModal) {
                startChatModal.style.display = 'none';
            }
        });
    }

    // Close modal on overlay click
    if (startChatModal) {
        startChatModal.addEventListener('click', function(e) {
            if (e.target === startChatModal) {
                startChatModal.style.display = 'none';
            }
        });
    }

    // Handle start chat form submission
    if (startChatForm) {
        let isSubmitting = false;
        
        startChatForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Prevent multiple submissions
            if (isSubmitting) {
                return;
            }

            const startChatBtn = document.getElementById('startChatBtn');
            const originalButtonText = startChatBtn ? startChatBtn.innerHTML : 'Start Chat';
            
            // Disable button and show loading state
            isSubmitting = true;
            if (startChatBtn) {
                startChatBtn.disabled = true;
                startChatBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Starting Chat...';
                startChatBtn.style.opacity = '0.7';
                startChatBtn.style.cursor = 'not-allowed';
            }

            const formData = new FormData(startChatForm);
            const email = formData.get('email');
            
            // Helper function to re-enable button
            const reEnableButton = function() {
                isSubmitting = false;
                if (startChatBtn) {
                    startChatBtn.disabled = false;
                    startChatBtn.innerHTML = originalButtonText;
                    startChatBtn.style.opacity = '1';
                    startChatBtn.style.cursor = 'pointer';
                }
            };

            // Helper function to handle success
            const handleSuccess = function(chatId) {
                currentChatId = chatId;
                startChatModal.style.display = 'none';
                liveChatWindow.style.display = 'flex';
                startChatForm.reset();
                
                // Load chat messages
                loadChatMessages();
                
                // Subscribe to chat channel
                subscribeToChat();
                
                // Mark admin messages as read when opening chat
                markAdminMessagesAsRead();
            };

            // Helper function to handle error
            const handleError = function(message) {
                reEnableButton();
                if (typeof window.showErrorMessage === 'function') {
                    window.showErrorMessage(message || 'An error occurred. Please try again.');
                }
            };
            
            // First check if there's an existing active chat for this email
            const checkUrl = '/chat/active' + (email ? '?email=' + encodeURIComponent(email) : '');
            
            fetch(checkUrl)
                .then(response => response.json())
                .then(checkData => {
                    if (checkData.success && checkData.has_active_chat) {
                        // Use existing chat
                        handleSuccess(checkData.chat.id);
                    } else {
                        // Create new chat
                        const data = {
                            name: formData.get('name'),
                            email: email,
                            message: formData.get('message'),
                        };

                        fetch('/chat/start', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(data)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                handleSuccess(data.chat.id);
                            } else {
                                handleError('Failed to start chat. Please try again.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            handleError('An error occurred. Please try again.');
                        });
                    }
                })
                .catch(error => {
                    console.error('Error checking for active chat:', error);
                    // On error, proceed with creating new chat
                    const data = {
                        name: formData.get('name'),
                        email: email,
                        message: formData.get('message'),
                    };

                    fetch('/chat/start', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            handleSuccess(data.chat.id);
                        } else {
                            handleError('Failed to start chat. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        handleError('An error occurred. Please try again.');
                    });
                });
        });
    }

    // Close live chat
    if (closeLiveChat) {
        closeLiveChat.addEventListener('click', function() {
            if (liveChatWindow) {
                liveChatWindow.style.display = 'none';
            }
            if (echo && currentChatId) {
                echo.leave(`chat.${currentChatId}`);
            }
            currentChatId = null;
        });
    }

    // Send message - use event delegation to ensure it works even if element is added dynamically
    document.addEventListener('click', function(e) {
        if (e.target && (e.target.id === 'sendChatMessage' || e.target.closest('#sendChatMessage'))) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Also attach directly to button if it exists
    if (sendChatMessage) {
        sendChatMessage.addEventListener('click', function(e) {
            e.preventDefault();
            sendMessage();
        });
    }

    if (chatMessageInput) {
        chatMessageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }

    // Image upload button
    const imageChatBtn = document.getElementById('imageChatBtn');
    const chatImageInput = document.getElementById('chatImageInput');
    
    if (imageChatBtn && chatImageInput) {
        imageChatBtn.addEventListener('click', function() {
            chatImageInput.click();
        });

        chatImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    if (typeof window.showErrorMessage === 'function') {
                        window.showErrorMessage('Please select a valid image file (JPEG, PNG, GIF, or WEBP)');
                    }
                    chatImageInput.value = '';
                    return;
                }

                // Validate file size (5MB max)
                const maxSize = 5 * 1024 * 1024; // 5MB in bytes
                if (file.size > maxSize) {
                    if (typeof window.showErrorMessage === 'function') {
                        window.showErrorMessage('Image size must be less than 5MB');
                    }
                    chatImageInput.value = '';
                    return;
                }

                // Send image immediately
                sendImage(file);
            }
        });
    }

    function sendImage(file) {
        if (!currentChatId) return;

        // Disable send button while sending
        if (sendChatMessage) {
            sendChatMessage.disabled = true;
            sendChatMessage.style.opacity = '0.6';
            sendChatMessage.style.cursor = 'not-allowed';
        }

        const formData = new FormData();
        formData.append('image', file);
        formData.append('message', ''); // Empty message for image-only

        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('CSRF token not found');
            if (sendChatMessage) {
                sendChatMessage.disabled = false;
                sendChatMessage.style.opacity = '1';
                sendChatMessage.style.cursor = 'pointer';
            }
            if (typeof window.showErrorMessage === 'function') {
                window.showErrorMessage('Security token not found. Please refresh the page.');
            }
            return;
        }

        fetch(`/chat/${currentChatId}/message`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content')
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'Failed to send image');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                chatImageInput.value = '';
                addMessageToUI(data.message);
            } else {
                if (typeof window.showErrorMessage === 'function') {
                    window.showErrorMessage(data.message || 'Failed to send image');
                }
            }
        })
        .catch(error => {
            console.error('Error sending image:', error);
            if (typeof window.showErrorMessage === 'function') {
                window.showErrorMessage('Error: ' + (error.message || 'Failed to send image. Please try again.'));
            }
        })
        .finally(() => {
            // Re-enable send button
            if (sendChatMessage) {
                sendChatMessage.disabled = false;
                sendChatMessage.style.opacity = '1';
                sendChatMessage.style.cursor = 'pointer';
            }
        });
    }

    function sendMessage() {
        if (!currentChatId || !chatMessageInput) return;

        const message = chatMessageInput.value.trim();
        const imageFile = chatImageInput && chatImageInput.files[0];
        
        // If there's an image but no message, use sendImage instead
        if (imageFile && !message) {
            sendImage(imageFile);
            return;
        }
        
        if (!message && !imageFile) return;

        // Disable send button while sending
        if (sendChatMessage) {
            sendChatMessage.disabled = true;
            sendChatMessage.style.opacity = '0.6';
            sendChatMessage.style.cursor = 'not-allowed';
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('CSRF token not found');
            if (sendChatMessage) {
                sendChatMessage.disabled = false;
                sendChatMessage.style.opacity = '1';
                sendChatMessage.style.cursor = 'pointer';
            }
            if (typeof window.showErrorMessage === 'function') {
                window.showErrorMessage('Security token not found. Please refresh the page.');
            }
            return;
        }

        // Use FormData if there's an image, otherwise use JSON
        let requestBody;
        let headers = {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content')
        };

        if (imageFile) {
            const formData = new FormData();
            formData.append('message', message);
            formData.append('image', imageFile);
            requestBody = formData;
            // Don't set Content-Type for FormData, browser will set it with boundary
        } else {
            headers['Content-Type'] = 'application/json';
            requestBody = JSON.stringify({ message: message });
        }

        fetch(`/chat/${currentChatId}/message`, {
            method: 'POST',
            headers: headers,
            body: requestBody
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'Failed to send message');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                chatMessageInput.value = '';
                if (chatImageInput) {
                    chatImageInput.value = '';
                }
                addMessageToUI(data.message);
            } else {
                if (typeof window.showErrorMessage === 'function') {
                    window.showErrorMessage(data.message || 'Failed to send message');
                }
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            if (typeof window.showErrorMessage === 'function') {
                window.showErrorMessage('Error: ' + (error.message || 'Failed to send message. Please try again.'));
            }
        })
        .finally(() => {
            // Re-enable send button
            if (sendChatMessage) {
                sendChatMessage.disabled = false;
                sendChatMessage.style.opacity = '1';
                sendChatMessage.style.cursor = 'pointer';
            }
        });
    }

    function loadChatMessages() {
        if (!currentChatId) return;

        fetch(`/chat/${currentChatId}/messages`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load messages');
                }
                return response.json();
            })
            .then(data => {
                if (data.success && chatMessages) {
                    chatMessages.innerHTML = '';
                    data.messages.forEach(message => {
                        addMessageToUI(message);
                    });
                    scrollToBottom();
                }
            })
            .catch(error => {
                console.error('Error loading messages:', error);
            });
    }

    function addMessageToUI(message) {
        if (!chatMessages) return;

        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${message.sender_type}`;
        messageDiv.setAttribute('data-message-id', message.id);
        
        const bubble = document.createElement('div');
        bubble.className = 'chat-message-bubble';
        
        // Display image if present
        if (message.image_path || message.image_url) {
            const imageUrl = message.image_url || (message.image_path ? `/storage/${message.image_path}` : null);
            if (imageUrl) {
                const imgContainer = document.createElement('div');
                imgContainer.className = 'chat-image-container';
                imgContainer.style.marginBottom = message.message ? '8px' : '0';
                
                const img = document.createElement('img');
                img.src = imageUrl;
                img.className = 'chat-message-image';
                img.style.maxWidth = '100%';
                img.style.maxHeight = '300px';
                img.style.borderRadius = '8px';
                img.style.cursor = 'pointer';
                img.alt = 'Chat image';
                
                // Click to view full size
                img.addEventListener('click', function() {
                    window.open(imageUrl, '_blank');
                });
                
                imgContainer.appendChild(img);
                bubble.appendChild(imgContainer);
            }
        }
        
        // Display text message if present
        if (message.message) {
            const textDiv = document.createElement('div');
            textDiv.textContent = message.message;
            bubble.appendChild(textDiv);
        }
        
        const timeDiv = document.createElement('div');
        timeDiv.className = 'chat-message-time';
        const time = new Date(message.created_at);
        
        // Show single tick (unread) or double tick (read) for user messages
        let statusIcon = '';
        if (message.sender_type === 'user') {
            if (message.is_read) {
                // Double tick - message read by admin
                statusIcon = '<span class="chat-message-status read"><i class="fas fa-check-double"></i></span>';
            } else {
                // Single tick - message sent but not read
                statusIcon = '<span class="chat-message-status unread"><i class="fas fa-check"></i></span>';
            }
        }
        
        timeDiv.innerHTML = `
            ${time.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}
            ${statusIcon}
        `;
        
        messageDiv.appendChild(bubble);
        messageDiv.appendChild(timeDiv);
        chatMessages.appendChild(messageDiv);
        
        scrollToBottom();
    }

    function updateMessageReadStatus(messageId) {
        if (!chatMessages) return;
        
        const messageDiv = chatMessages.querySelector(`[data-message-id="${messageId}"]`);
        if (messageDiv) {
            const statusSpan = messageDiv.querySelector('.chat-message-status');
            if (statusSpan) {
                statusSpan.className = 'chat-message-status read';
                statusSpan.innerHTML = '<i class="fas fa-check-double"></i>';
            }
        }
    }

    function markAllUserMessagesAsRead() {
        if (!chatMessages) return;
        
        const userMessages = chatMessages.querySelectorAll('.chat-message.user');
        userMessages.forEach(messageDiv => {
            const statusSpan = messageDiv.querySelector('.chat-message-status');
            if (statusSpan && statusSpan.classList.contains('unread')) {
                statusSpan.className = 'chat-message-status read';
                statusSpan.innerHTML = '<i class="fas fa-check-double"></i>';
            }
        });
    }

    function scrollToBottom() {
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }

    function subscribeToChat() {
        if (!echo || !currentChatId) return;

        echo.private(`chat.${currentChatId}`)
            .listen('.message.sent', (e) => {
                addMessageToUI(e.message);
                
                // Update status if admin replied
                if (e.message.sender_type === 'admin') {
                    if (chatStatus) {
                        chatStatus.textContent = 'Agent is typing...';
                        setTimeout(() => {
                            if (chatStatus) {
                                chatStatus.textContent = 'Agent is online';
                            }
                        }, 2000);
                    }
                    
                    // Mark all user messages as read when admin replies
                    markAllUserMessagesAsRead();
                    
                    // Update badge - show if chat window is not open
                    const isChatOpen = liveChatWindow && liveChatWindow.style.display !== 'none';
                    if (!isChatOpen) {
                        updateUnreadBadge();
                    } else {
                        // Mark admin messages as read if chat is open
                        markAdminMessagesAsRead();
                    }
                }
            })
            .listen('.messages.read', (e) => {
                // Mark all user messages as read
                markAllUserMessagesAsRead();
            })
            .listen('.chat.assigned', (e) => {
                if (chatStatus) {
                    chatStatus.textContent = 'Agent is online';
                }
            });
    }

    // Refresh chat button
    const refreshChatBtn = document.getElementById('refreshChatBtn');
    if (refreshChatBtn) {
        refreshChatBtn.addEventListener('click', function() {
            if (currentChatId) {
                loadChatMessages();
            }
        });
    }

    // Check for unread admin messages and update badge
    function updateUnreadBadge() {
        // Check if user is authenticated
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        const isAuthenticated = csrfToken && window.location.pathname.includes('/user/dashboard');
        
        let url = '/chat/unread-count';
        
        // For guest users, try to get email from form if it exists
        if (!isAuthenticated) {
            const emailInput = document.getElementById('chatEmail');
            if (emailInput && emailInput.value) {
                url += '?email=' + encodeURIComponent(emailInput.value);
            }
        }
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success && chatNotificationBadge) {
                    // Only show badge if chat window is not open
                    const isChatOpen = liveChatWindow && liveChatWindow.style.display !== 'none';
                    
                    if (data.unread_count > 0 && !isChatOpen) {
                        chatNotificationBadge.style.display = 'block';
                    } else {
                        chatNotificationBadge.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Error checking unread count:', error);
            });
    }

    // Mark admin messages as read when chat is opened
    function markAdminMessagesAsRead() {
        if (!currentChatId) return;

        fetch(`/chat/${currentChatId}/mark-read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateUnreadBadge();
            }
        })
        .catch(error => {
            console.error('Error marking messages as read:', error);
        });
    }

    // Update badge when chat window opens/closes
    if (liveChatWindow) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    const isOpen = liveChatWindow.style.display !== 'none';
                    if (isOpen && currentChatId) {
                        // Mark admin messages as read when chat is opened
                        markAdminMessagesAsRead();
                    } else if (!isOpen) {
                        // Update badge when chat is closed
                        updateUnreadBadge();
                    }
                }
            });
        });
        
        observer.observe(liveChatWindow, {
            attributes: true,
            attributeFilter: ['style']
        });
    }

    // Subscribe to user-specific channel for badge updates (even when chat is closed)
    function subscribeToUserChannel() {
        if (!echo) return;
        
        // Get user ID from meta tag (only for authenticated users)
        const userIdMeta = document.querySelector('meta[name="user-id"]');
        if (!userIdMeta) return; // Guest users will rely on polling
        
        const userId = userIdMeta.getAttribute('content');
        if (!userId) return;
        
        // Subscribe to user-specific channel
        echo.private(`user.${userId}.chats`)
            .listen('.message.sent', (e) => {
                // Only handle admin messages
                if (e.message && e.message.sender_type === 'admin') {
                    // Update badge if chat window is not open
                    const isChatOpen = liveChatWindow && liveChatWindow.style.display !== 'none';
                    if (!isChatOpen && chatNotificationBadge) {
                        // Show badge immediately
                        chatNotificationBadge.style.display = 'block';
                        // Also update count
                        updateUnreadBadge();
                    }
                }
            });
    }

    // Initialize user channel subscription on page load
    subscribeToUserChannel();

    // Update badge periodically and on page load
    if (chatNotificationBadge) {
        // Check immediately on load
        updateUnreadBadge();
        
        // Check every 30 seconds
        setInterval(updateUnreadBadge, 30000);
    }
})();



/* --- Wrapped Asset: assets/dashboard/js/dashboard-ui.js --- */
(function() { 
    if (!document.querySelector('.mining-dashboard')) return;
    console.log('Initializing script for: .mining-dashboard');

/**
 * dashboard-ui.js
 * Specific UI logic for user/dashboard.html
 */

document.addEventListener('DOMContentLoaded', function() {
    // Helpline Modal close
    const helplineModal = document.getElementById('helplineModal');
    const closeHelplineModal = document.getElementById('closeHelplineModal');
    const joinNowBtn = document.getElementById('joinNowBtn');

    // Show modal on page load after short delay
    if (helplineModal) {
        setTimeout(function() {
            helplineModal.style.display = 'flex';
        }, 1500);
    }

    // Close button click
    if (closeHelplineModal) {
        closeHelplineModal.addEventListener('click', function() {
            helplineModal.style.display = 'none';
        });
    }

    // Close on overlay click (outside modal)
    if (helplineModal) {
        helplineModal.addEventListener('click', function(e) {
            if (e.target === helplineModal) {
                helplineModal.style.display = 'none';
            }
        });
    }

    // Join Now button - close modal 
    if (joinNowBtn) {
        joinNowBtn.addEventListener('click', function() {
            if (helplineModal) helplineModal.style.display = 'none';
        });
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && helplineModal && helplineModal.style.display === 'flex') {
            helplineModal.style.display = 'none';
        }
    });

    // Handle wallet action buttons redirect
    const actionBtns = document.querySelectorAll('.wallet-action-btn');
    actionBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            if (action) {
                // Since we are already in /user/dashboard/ (index.php), simple redirect to action works
                window.location.href = action;
            }
        });
    });
});

})();

/* --- Wrapped Asset: assets/dashboard/js/pages/deposit.js --- */
(function() { 
    if (!document.querySelector('.deposit-new-page')) return;
    console.log('Initializing script for: .deposit-new-page');

/**
 * Deposit Page Logic
 * Handles payment method selection, PKR conversion, search, and filtering.
 */

document.addEventListener('DOMContentLoaded', function() {
    let selectedPaymentMethod = null;
    const conversionRate = parseFloat(document.querySelector('meta[name="conversion-rate"]')?.content) || 280;

    // Function to update PKR amount display
    function updatePKRAmount() {
        const amountInput = document.getElementById('deposit-amount-input');
        const pkrAmountDisplay = document.getElementById('deposit-pkr-amount');
        const pkrAmountText = document.getElementById('pkr-amount-text');

        if (!amountInput || !pkrAmountDisplay || !pkrAmountText) return;

        const amount = parseFloat(amountInput.value) || 0;
        const rate = conversionRate;

        if (selectedPaymentMethod && amount > 0 && rate > 0) {
            const pkrAmount = amount * rate;
            const formattedUSD = amount.toLocaleString('en-US', {
                maximumFractionDigits: 2,
                minimumFractionDigits: 0
            });
            const formattedPKR = pkrAmount.toLocaleString('en-US', {
                maximumFractionDigits: 2,
                minimumFractionDigits: 0
            });
            pkrAmountText.textContent = `$${formattedUSD} = Rs ${formattedPKR}`;
            pkrAmountDisplay.style.display = 'block';
        } else {
            pkrAmountDisplay.style.display = 'none';
        }
    }

    // Payment method selection
    document.querySelectorAll('.deposit-payment-method').forEach(method => {
        method.addEventListener('click', function() {
            document.querySelectorAll('.deposit-payment-method').forEach(m => m.classList.remove('active'));
            this.classList.add('active');

            selectedPaymentMethod = {
                id: this.dataset.methodId,
                name: this.dataset.methodName,
                type: this.dataset.methodType || 'rast',
                minDeposit: parseFloat(this.dataset.minDeposit) || 2,
                maxDeposit: parseFloat(this.dataset.maxDeposit) || null
            };

            const depositAmountSection = document.querySelector('.deposit-amount-section');
            if (depositAmountSection) {
                depositAmountSection.classList.add('show');
            }

            const continueBtn = document.getElementById('deposit-continue-btn');
            if (continueBtn && selectedPaymentMethod) {
                continueBtn.textContent = `Continue Deposit with ${selectedPaymentMethod.name}`;
            }

            const minAmountText = document.getElementById('deposit-min-amount-text');
            if (minAmountText && selectedPaymentMethod) {
                const formattedMinDeposit = selectedPaymentMethod.minDeposit.toFixed(2);
                minAmountText.textContent = `Minimum deposit for ${selectedPaymentMethod.name} is $${formattedMinDeposit}`;
            }

            const amountInput = document.getElementById('deposit-amount-input');
            if (amountInput) {
                amountInput.setAttribute('min', selectedPaymentMethod.minDeposit);
                if (selectedPaymentMethod.maxDeposit) {
                    amountInput.setAttribute('max', selectedPaymentMethod.maxDeposit);
                } else {
                    amountInput.removeAttribute('max');
                }
                amountInput.value = '';
                document.querySelectorAll('.deposit-preset-btn').forEach(btn => btn.classList.remove('active'));
            }
            updatePKRAmount();
        });
    });

    // Preset amount buttons
    document.querySelectorAll('.deposit-preset-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.deposit-preset-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const amount = this.dataset.amount;
            const amountInput = document.getElementById('deposit-amount-input');
            if (amountInput) {
                amountInput.value = amount;
                updatePKRAmount();
                amountInput.dispatchEvent(new Event('input'));
            }
        });
    });

    // Custom amount input validation
    const amountInput = document.getElementById('deposit-amount-input');
    if (amountInput) {
        amountInput.addEventListener('input', function() {
            const value = parseFloat(this.value);
            const presetButtons = document.querySelectorAll('.deposit-preset-btn');
            let matchesPreset = false;

            presetButtons.forEach(btn => {
                const presetAmount = parseFloat(btn.dataset.amount);
                if (value === presetAmount) {
                    btn.classList.add('active');
                    matchesPreset = true;
                } else {
                    btn.classList.remove('active');
                }
            });

            if (!matchesPreset && this.value !== '') {
                presetButtons.forEach(btn => btn.classList.remove('active'));
            }
            updatePKRAmount();
        });
    }

    // Continue deposit button logic
    const continueBtn = document.getElementById('deposit-continue-btn');
    if (continueBtn) {
        continueBtn.addEventListener('click', function() {
            const amountInput = document.getElementById('deposit-amount-input');
            const amount = amountInput ? parseFloat(amountInput.value) : 0;

            if (!selectedPaymentMethod) {
                if (window.showAppMessage) window.showErrorMessage('Please select a payment method');
                else alert('Please select a payment method');
                return;
            }

            if (!amount || isNaN(amount) || amount < selectedPaymentMethod.minDeposit) {
                const msg = `Please enter a valid amount (minimum $${selectedPaymentMethod.minDeposit})`;
                if (window.showAppMessage) window.showErrorMessage(msg);
                else alert(msg);
                return;
            }

            if (selectedPaymentMethod.maxDeposit && amount > selectedPaymentMethod.maxDeposit) {
                const msg = `Maximum deposit amount is $${selectedPaymentMethod.maxDeposit}`;
                if (window.showAppMessage) window.showErrorMessage(msg);
                else alert(msg);
                return;
            }

            // Redirect to deposit confirmation page
            const confirmUrl = '../../user/dashboard/deposit/confirm' +
                '?method_id=' + encodeURIComponent(selectedPaymentMethod.id) +
                '&amount=' + encodeURIComponent(amount);

            window.location.href = confirmUrl;
        });
    }

    // --- Search and Filtering Logic ---
    const depositSearchInput = document.getElementById('deposit-search-input');
    const depositDateFilter = document.getElementById('deposit-date-filter');
    const depositTransactionsList = document.getElementById('deposit-transactions-list');
    const depositHistoryEmpty = document.getElementById('deposit-history-empty');

    const advanceSearchModal = document.getElementById('deposit-advance-search-modal');
    const advanceSearchClose = document.getElementById('deposit-advance-search-close');
    const advanceSearchApply = document.getElementById('deposit-advance-apply');
    const advanceSearchClear = document.getElementById('deposit-advance-clear');
    const dateRangeInput = document.getElementById('deposit-date-range-input');
    const advanceSortSelect = document.getElementById('deposit-advance-sort');

    let dateRangeFilter = null;
    let sortOrder = 'newest';
    let startDate = null;
    let endDate = null;
    const startDateInput = document.getElementById('deposit-start-date');
    const endDateInput = document.getElementById('deposit-end-date');

    function updateDateRangeDisplay() {
        if (startDate && endDate) {
            const formatDate = (date) => {
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${day}/${month}/${year}`;
            };
            dateRangeInput.value = `${formatDate(startDate)} - ${formatDate(endDate)}`;
            dateRangeFilter = {
                start: Math.floor(startDate.getTime() / 1000),
                end: Math.floor(endDate.getTime() / 1000) + 86400
            };
        } else {
            dateRangeInput.value = '';
            dateRangeFilter = null;
        }
    }

    if (dateRangeInput) {
        dateRangeInput.addEventListener('click', () => {
            if (startDateInput) startDateInput.showPicker?.() || startDateInput.click();
        });
    }

    if (startDateInput) {
        startDateInput.addEventListener('change', function() {
            startDate = new Date(this.value);
            if (endDateInput) {
                endDateInput.min = this.value;
                if (endDate && endDate < startDate) {
                    endDate = null;
                    endDateInput.value = '';
                }
                updateDateRangeDisplay();
                setTimeout(() => { endDateInput.showPicker?.() || endDateInput.click(); }, 100);
            } else updateDateRangeDisplay();
        });
    }

    if (endDateInput) {
        endDateInput.addEventListener('change', function() {
            endDate = new Date(this.value);
            updateDateRangeDisplay();
        });
    }

    function filterDeposits() {
        const searchTerm = depositSearchInput ? depositSearchInput.value.toLowerCase().trim() : '';
        const dateFilter = depositDateFilter ? depositDateFilter.value : 'all';
        const transactionCards = depositTransactionsList ? Array.from(depositTransactionsList.querySelectorAll('.deposit-transaction-card')) : [];

        let visibleCount = 0;
        const now = Math.floor(Date.now() / 1000);
        const daysInSeconds = { '3': 3*86400, '7': 7*86400, '30': 30*86400 };

        const filteredCards = transactionCards.filter(card => {
            const transactionDate = parseInt(card.dataset.date);
            const transactionId = (card.dataset.transactionId || '').toLowerCase();
            const cardText = card.textContent.toLowerCase();
            
            let dateMatch = true;
            if (dateFilter !== 'all' && !dateRangeFilter) {
                const daysAgo = daysInSeconds[dateFilter];
                dateMatch = transactionDate >= (now - daysAgo);
            }
            if (dateRangeFilter) {
                dateMatch = transactionDate >= dateRangeFilter.start && transactionDate <= dateRangeFilter.end;
            }

            let searchMatch = !searchTerm || cardText.includes(searchTerm) || transactionId.includes(searchTerm);
            return dateMatch && searchMatch;
        });

        filteredCards.sort((a, b) => {
            const dateA = parseInt(a.dataset.date), dateB = parseInt(b.dataset.date);
            const amtA = parseFloat(a.dataset.amount), amtB = parseFloat(b.dataset.amount);
            if (sortOrder === 'newest') return dateB - dateA;
            if (sortOrder === 'oldest') return dateA - dateB;
            if (sortOrder === 'amount-high') return amtB - amtA;
            if (sortOrder === 'amount-low') return amtA - amtB;
            return dateB - dateA;
        });

        transactionCards.forEach(card => card.style.display = 'none');
        filteredCards.forEach(card => {
            card.style.display = 'flex';
            depositTransactionsList.appendChild(card);
            visibleCount++;
        });

        if (depositHistoryEmpty) depositHistoryEmpty.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    if (advanceSearchApply) {
        advanceSearchApply.addEventListener('click', () => {
            sortOrder = advanceSortSelect ? advanceSortSelect.value : 'newest';
            filterDeposits();
            if (advanceSearchModal) advanceSearchModal.classList.remove('show');
            document.body.style.overflow = '';
        });
    }

    if (advanceSearchClear) {
        advanceSearchClear.addEventListener('click', () => {
            startDate = null; endDate = null; dateRangeFilter = null;
            if (dateRangeInput) dateRangeInput.value = '';
            if (advanceSortSelect) advanceSortSelect.value = 'newest';
            sortOrder = 'newest';
            filterDeposits();
            if (advanceSearchModal) advanceSearchModal.classList.remove('show');
            document.body.style.overflow = '';
        });
    }

    if (advanceSearchClose) {
        advanceSearchClose.addEventListener('click', () => {
            if (advanceSearchModal) advanceSearchModal.classList.remove('show');
            document.body.style.overflow = '';
        });
    }

    const filterIcons = document.querySelectorAll('.deposit-filter-icon, .deposit-search-filter-btn');
    filterIcons.forEach(icon => {
        icon.addEventListener('click', () => {
            if (advanceSearchModal) {
                advanceSearchModal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    if (depositSearchInput) depositSearchInput.addEventListener('input', filterDeposits);
    if (depositDateFilter) depositDateFilter.addEventListener('change', filterDeposits);

    filterDeposits();
});

})();

/* --- Wrapped Asset: assets/dashboard/js/pages/goals-ui.js --- */
(function() { 
    if (!document.querySelector('.goals-new-page')) return;
    console.log('Initializing script for: .goals-new-page');

/**
 * Spark X - Goals Page UI Logic
 * Handles reward claiming and specific UI interactions for the goals page.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Handle claim button clicks
    const claimButtons = document.querySelectorAll('.goals-claim-btn-new');

    claimButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (this.disabled) {
                return;
            }

            const levelId = this.getAttribute('data-level-id');
            const levelName = this.getAttribute('data-level-name');
            const rewardAmount = this.getAttribute('data-reward-amount');

            // Disable button and show loading state
            this.disabled = true;
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Claiming...</span>';

            // Make AJAX request
            const claimUrl = `/user/dashboard/goals/${levelId}/claim`;
            fetch(claimUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    if (typeof window.showSuccessMessage === 'function') {
                        window.showSuccessMessage('Success! ' + data.message);
                    } else {
                        alert('Success! ' + data.message);
                    }

                    // Reload page to update balances and UI
                    setTimeout(() => {
                        window.location.reload();
                    }, 6000);
                } else {
                    // Show error message
                    if (typeof window.showErrorMessage === 'function') {
                        window.showErrorMessage('Error: ' + (data.message || 'Failed to claim reward. Please try again.'));
                    } else {
                        alert('Error: ' + (data.message || 'Failed to claim reward. Please try again.'));
                    }

                    // Re-enable button
                    this.disabled = false;
                    this.innerHTML = originalHTML;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof window.showErrorMessage === 'function') {
                    window.showErrorMessage('An error occurred. Please try again.');
                } else {
                    alert('An error occurred. Please try again.');
                }

                // Re-enable button
                this.disabled = false;
                this.innerHTML = originalHTML;
            });
        });
    });
});

})();

/* --- Wrapped Asset: assets/dashboard/js/pages/profile.js --- */
(function() { 
    if (!document.querySelector('.profile-new-page')) return;
    console.log('Initializing script for: .profile-new-page');

/**
 * Profile Page Logic
 * Handles tab switching, password visibility, and password updates.
 */

document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.profile-tab-modern, .profile-tab');
    const tabContents = document.querySelectorAll('.profile-tab-content-modern, .profile-tab-content');
    const savePasswordBtn = document.getElementById('savePasswordBtn');
    const passwordToggles = document.querySelectorAll('.profile-password-toggle');

    // Tab Switching
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            this.classList.add('active');
            const targetContent = document.getElementById(targetTab + 'Tab');
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });

    // Password Visibility Toggle
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

    // Update Password via AJAX
    if (savePasswordBtn) {
        savePasswordBtn.addEventListener('click', function() {
            const currentPassword = document.getElementById('currentPassword');
            const newPassword = document.getElementById('newPassword');
            const confirmPassword = document.getElementById('confirmPassword');
            
            if (!currentPassword?.value) {
                if (window.showAppMessage) window.showErrorMessage('Please enter your current password');
                else alert('Please enter your current password');
                return;
            }
            
            if (!newPassword?.value) {
                if (window.showAppMessage) window.showErrorMessage('Please enter a new password');
                else alert('Please enter a new password');
                return;
            }
            
            if (newPassword.value !== confirmPassword?.value) {
                if (window.showAppMessage) window.showErrorMessage('New passwords do not match');
                else alert('New passwords do not match');
                return;
            }
            
            if (newPassword.value.length < 8) {
                if (window.showAppMessage) window.showErrorMessage('Password must be at least 8 characters');
                else alert('Password must be at least 8 characters');
                return;
            }
            
            const originalBtnContent = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Updating...</span>';
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const url = typeof updatePasswordRoute !== 'undefined' ? updatePasswordRoute : '../../user/dashboard/profile/update-password';
            
            fetch(url, {
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
                    currentPassword.value = '';
                    newPassword.value = '';
                    confirmPassword.value = '';
                    if (window.showAppMessage) window.showSuccessMessage('Password updated successfully!');
                    else alert('Password updated successfully!');
                } else {
                    if (window.showAppMessage) window.showErrorMessage(data.message || 'Failed to update password');
                    else alert(data.message || 'Failed to update password');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (window.showAppMessage) window.showErrorMessage('An error occurred while updating password');
                else alert('An error occurred while updating password');
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = originalBtnContent;
            });
        });
    }
});

})();

/* --- Wrapped Asset: assets/dashboard/js/pages/referrals.js --- */
(function() { 
    if (!document.querySelector('.referrals-new-page')) return;
    console.log('Initializing script for: .referrals-new-page');

/**
 * Referrals Page Logic
 * Handles referral network, filtering, statistics and claiming earnings
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips or other UI components if needed
    
    // Copy referral link and code
    const copyBtns = document.querySelectorAll('.referrals-tool-copy-btn-new');
    copyBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const inputId = this.getAttribute('data-copy');
            const input = document.getElementById(inputId);
            
            if (input) {
                input.select();
                input.setSelectionRange(0, 99999);
                document.execCommand('copy');
                
                // Show success message using shared notification module
                if (window.showSuccessMessage) {
                    window.showSuccessMessage('Copied to clipboard!');
                } else {
                    alert('Copied to clipboard!');
                }
                
                // Visual feedback on button
                const icon = this.querySelector('i');
                const originalClass = icon.className;
                icon.className = 'fas fa-check';
                setTimeout(() => {
                    icon.className = originalClass;
                }, 2000);
            }
        });
    });

    // Referral detail modal functionality
    document.querySelectorAll('.referral-row-clickable').forEach(row => {
        row.addEventListener('click', function(e) {
            // Don't trigger if clicking on links or buttons
            if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON') {
                return;
            }

            try {
                const referralData = JSON.parse(this.getAttribute('data-referral'));
                openReferralModal(referralData);
            } catch (err) {
                console.error('Error parsing referral data:', err);
            }
        });
    });

    // Claim Earnings functionality
    const claimBtn = document.getElementById('claimEarningsBtn');
    if (claimBtn) {
        claimBtn.addEventListener('click', function() {
            if (this.disabled) return;

            if (window.showConfirmDialog) {
                window.showConfirmDialog('Are you sure you want to claim your referral earnings and move them to your main balance?', function() {
                    performClaim();
                });
            } else if (confirm('Are you sure you want to claim your referral earnings?')) {
                performClaim();
            }
            
            function performClaim() {
                claimBtn.disabled = true;
                const originalHTML = claimBtn.innerHTML;
                claimBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Claiming...</span>';

                fetch('../../user/dashboard/referrals/claim', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (window.showSuccessMessage) {
                            window.showSuccessMessage('Success! ' + data.message);
                        } else {
                            alert('Success! ' + data.message);
                        }
                        
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        if (window.showErrorMessage) {
                            window.showErrorMessage('Error: ' + (data.message || 'Failed to claim earnings.'));
                        } else {
                            alert('Error: ' + (data.message || 'Failed to claim earnings.'));
                        }
                        claimBtn.disabled = false;
                        claimBtn.innerHTML = originalHTML;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (window.showErrorMessage) {
                        window.showErrorMessage('An error occurred. Please try again.');
                    } else {
                        alert('An error occurred. Please try again.');
                    }
                    claimBtn.disabled = false;
                    claimBtn.innerHTML = originalHTML;
                });
            }
        });
    }
});

/**
 * Handle Referral Network table updates via AJAX
 */
function updateReferralsTable(referrals, pagination) {
    const tableWrapper = document.querySelector('.referrals-network-table-wrapper-new');
    if (!tableWrapper) return;

    let html = '<table class="referrals-network-table-new">';
    html += '<thead><tr><th>User</th><th>Details</th><th class="referrals-network-table-desktop-header">Action</th></tr></thead>';

    if (referrals && referrals.length > 0) {
        html += '<tbody>';
        referrals.forEach(referral => {
            const initial = referral.name ? referral.name.charAt(0).toUpperCase() : 'U';
            const levelClass = referral.level ? `referral-level-badge-${referral.level}` : '';
            const referralData = JSON.stringify(referral).replace(/"/g, '&quot;');

            html += `
                <tr class="referral-row-clickable" data-referral="${referralData}">
                    <td class="referrals-network-user-cell-new">
                        <div class="referrals-network-user-avatar-new">${initial}</div>
                        <div class="referrals-network-user-info-new">
                            <h4 class="referrals-network-user-name-new">${referral.name || 'N/A'}</h4>
                            <span class="referrals-network-user-date-new">Joined: ${referral.joined_at || 'N/A'}</span>
                        </div>
                    </td>
                    <td class="referrals-network-detail-cell-new">
                        <div class="referrals-network-detail-item-new">
                            <span class="referral-level-badge-new ${levelClass}">${referral.level_name || 'L' + referral.level}</span>
                        </div>
                        <div class="referrals-network-detail-item-new">
                            <span class="referrals-network-earning-value-new">$${parseFloat(referral.referral_earning || 0).toFixed(2)}</span>
                        </div>
                    </td>
                    <td class="referrals-network-action-cell-new referrals-network-table-desktop-header">
                        <button class="referral-detail-btn-new">View Details</button>
                    </td>
                </tr>
            `;
        });
        html += '</tbody>';
    } else {
        html += '<tbody><tr><td colspan="3" class="referrals-network-empty-new">';
        html += '<div class="referrals-network-empty-content-new">';
        html += '<div class="referrals-network-empty-icon-new referrals-network-empty-icon-desktop"><i class="fas fa-users"></i></div>';
        html += '<div class="referrals-network-empty-icon-new referrals-network-empty-icon-mobile"><i class="fas fa-users"></i></div>';
        html += '<p class="referrals-network-empty-message-new">You don\'t have any referrals yet</p>';
        html += '<button class="referrals-network-invite-btn-new" onclick="copyReferralLink()"><i class="fas fa-share-alt"></i><span>Invite Now</span></button>';
        html += '</div></td></tr></tbody>';
    }

    html += '</table>';

    // Add pagination
    if (pagination && pagination.last_page > 1) {
        const currentPage = parseInt(pagination.current_page || 1);
        const lastPage = parseInt(pagination.last_page || 1);

        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(lastPage, currentPage + 2);

        if (startPage > 1) endPage = Math.min(lastPage, startPage + 4);
        if (endPage < lastPage) startPage = Math.max(1, endPage - 4);

        html += '<div class="wallet-pagination">';

        if (currentPage === 1) {
            html += '<button class="wallet-pagination-button" disabled><i class="fas fa-chevron-left"></i></button>';
        } else {
            html += `<a href="${pagination.previous_page_url}" class="pagination-link wallet-pagination-button"><i class="fas fa-chevron-left"></i></a>`;
        }

        html += '<div class="wallet-pagination-numbers">';

        if (startPage > 1) {
            html += `<a href="${pagination.url_range['1']}" class="pagination-link wallet-pagination-number">1</a>`;
            if (startPage > 2) html += '<span class="wallet-pagination-ellipsis">...</span>';
        }

        for (let page = startPage; page <= endPage; page++) {
            const pageUrl = pagination.url_range[String(page)];
            if (page === currentPage) {
                html += `<span class="wallet-pagination-number active">${page}</span>`;
            } else {
                html += `<a href="${pageUrl}" class="pagination-link wallet-pagination-number">${page}</a>`;
            }
        }

        if (endPage < lastPage) {
            if (endPage < lastPage - 1) html += '<span class="wallet-pagination-ellipsis">...</span>';
            html += `<a href="${pagination.url_range[String(lastPage)]}" class="pagination-link wallet-pagination-number">${lastPage}</a>`;
        }

        html += '</div>';

        if (pagination.has_more_pages) {
            html += `<a href="${pagination.next_page_url}" class="pagination-link wallet-pagination-button"><i class="fas fa-chevron-right"></i></a>`;
        } else {
            html += '<button class="wallet-pagination-button" disabled><i class="fas fa-chevron-right"></i></button>';
        }

        html += '</div>';
    }

    tableWrapper.innerHTML = html;

    // Re-attach click handlers
    document.querySelectorAll('.referral-row-clickable').forEach(row => {
        row.addEventListener('click', function(e) {
            if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON') return;
            try {
                const referralData = JSON.parse(this.getAttribute('data-referral'));
                openReferralModal(referralData);
            } catch (err) {
                console.error('Error parsing referral data:', err);
            }
        });
    });
}

function openReferralModal(referral) {
    const initial = referral.name ? referral.name.charAt(0).toUpperCase() : 'U';
    const initialEl = document.getElementById('modalUserInitial');
    const nameEl = document.getElementById('modalUserName');
    const dateEl = document.getElementById('modalUserDate');
    const phoneEl = document.getElementById('modalUserPhone');
    const levelEl = document.getElementById('modalUserLevel');
    const earningEl = document.getElementById('modalUserEarning');
    const investedEl = document.getElementById('modalUserInvested');

    if (initialEl) initialEl.textContent = initial;
    if (nameEl) nameEl.textContent = referral.name || 'N/A';

    let formattedDate = 'N/A';
    if (referral.created_at) {
        const dateStr = typeof referral.created_at === 'string' ? referral.created_at : (referral.created_at.date || referral.created_at);
        const date = new Date(dateStr);
        if (!isNaN(date.getTime())) {
            formattedDate = date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }
    }
    if (dateEl) dateEl.textContent = formattedDate;

    let phone = referral.phone || 'N/A';
    if (phone !== 'N/A' && phone && !phone.startsWith('+')) phone = '+' + phone;
    if (phoneEl) phoneEl.textContent = phone;

    if (levelEl) levelEl.textContent = referral.level_name || (referral.level ? 'Level ' + referral.level : 'N/A');
    if (earningEl) earningEl.textContent = '$' + parseFloat(referral.referral_earning || 0).toFixed(2);
    if (investedEl) investedEl.textContent = '$' + parseFloat(referral.invested_amount || 0).toFixed(2);

    const modal = document.getElementById('referralDetailModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeReferralModal() {
    const modal = document.getElementById('referralDetailModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Global scope functions if needed for inline onclick handles that haven't been removed yet
window.closeReferralModal = closeReferralModal;
window.copyReferralLink = function() {
    const linkInput = document.getElementById('referralLink');
    if (linkInput) {
        linkInput.select();
        document.execCommand('copy');
        if (window.showSuccessMessage) window.showSuccessMessage('Link copied!');
    }
};

/**
 * Handle filtering by level
 */
window.selectLevel = function(level) {
    const dropdown = document.querySelector('.referrals-network-dropdown-new');
    if (dropdown) dropdown.classList.remove('active');
    
    const levelLabel = document.getElementById('selectedLevelLabel');
    if (levelLabel) levelLabel.textContent = level === 'all' ? 'All Levels' : `Level ${level}`;
    
    // Perform AJAX filter
    const url = new URL(window.location.href);
    url.searchParams.set('level', level);
    url.searchParams.set('ajax', '1');
    
    const tableWrapper = document.querySelector('.referrals-network-table-wrapper-new');
    if (tableWrapper) {
        tableWrapper.innerHTML = '<div style="padding: 3rem; text-align: center; color: var(--text-secondary);"><i class="fas fa-spinner fa-spin" style="font-size: 2rem;"></i></div>';
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateReferralsTable(data.referrals, data.pagination);
                // Update browser URL without reload
                url.searchParams.delete('ajax');
                window.history.pushState({}, '', url);
            }
        })
        .catch(err => console.error('Error filtering:', err));
};

})();

/* --- Wrapped Asset: assets/dashboard/js/pages/support.js --- */
(function() { 
    if (!document.querySelector('.support-new-page')) return;
    console.log('Initializing script for: .support-new-page');

/**
 * Support Page Logic
 * Handles support channel interactions, copy buttons, and chat triggers
 */

document.addEventListener('DOMContentLoaded', function() {
    // 1. Copy functionality with visual feedback
    const copyBtns = document.querySelectorAll('[data-copy]');
    copyBtns.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-copy');
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                const text = targetElement.textContent || targetElement.innerText;
                
                // Use Clipboard API if available, fallback to textarea
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(() => {
                        provideCopyFeedback(this);
                    }).catch(err => {
                        console.error('Failed to copy text: ', err);
                        fallbackCopyToClipboard(text, this);
                    });
                } else {
                    fallbackCopyToClipboard(text, this);
                }
            }
        });
    });

    /**
     * Fallback copy method
     */
    function fallbackCopyToClipboard(text, button) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-9999px';
        textArea.style.top = '0';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                provideCopyFeedback(button);
            }
        } catch (err) {
            console.error('Fallback copy failed', err);
        }
        document.body.removeChild(textArea);
    }

    /**
     * Visual feedback for copy buttons
     */
    function provideCopyFeedback(button) {
        // Show success using shared notification module if available
        if (window.showSuccessMessage) {
            window.showSuccessMessage('Copied to clipboard!');
        } else {
            // Visual feedback on button itself as fallback
            const originalHTML = button.innerHTML;
            button.classList.add('copied');
            button.innerHTML = '<i class="fas fa-check"></i><span>Copied!</span>';
            
            setTimeout(() => {
                button.classList.remove('copied');
                button.innerHTML = originalHTML;
            }, 2000);
        }
    }

    // 2. 24/7 Support click handler
    const supportAction = document.getElementById('support247Action');
    if (supportAction) {
        supportAction.addEventListener('click', function() {
            const phoneNumber = '+92 323 9704664';
            
            // Try to copy phone number
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(phoneNumber).then(() => {
                    if (window.showInfoMessage) {
                        window.showInfoMessage('Support Phone: ' + phoneNumber + '\n(Copied to clipboard)');
                    } else {
                        alert('Support Phone: ' + phoneNumber + '\n(Copied to clipboard)');
                    }
                }).catch(() => {
                    alert('Support Phone: ' + phoneNumber);
                });
            } else {
                alert('Support Phone: ' + phoneNumber);
            }
        });
    }

    // 3. Live Chat click handler
    const liveChatAction = document.getElementById('liveChatAction');
    if (liveChatAction) {
        liveChatAction.addEventListener('click', function() {
            // Try to trigger the chat button if it exists
            const chatButton = document.getElementById('chatButton');
            if (chatButton) {
                chatButton.click();
            } else {
                // If chat button doesn't exist, try to show chat window directly
                const liveChatWindow = document.getElementById('liveChatWindow');
                if (liveChatWindow) {
                    liveChatWindow.style.display = 'flex';
                    // Trigger active chat check if the function exists (from chat.js)
                    if (typeof window.checkForActiveChat === 'function') {
                        window.checkForActiveChat();
                    }
                } else {
                    if (window.showInfoMessage) {
                        window.showInfoMessage('Chat feature is loading. Please wait a moment.');
                    } else {
                        alert('Chat feature is loading. Please wait a moment.');
                    }
                }
            }
        });
    }
});

})();

/* --- Wrapped Asset: assets/dashboard/js/pages/transactions.js --- */
(function() { 
    if (!document.querySelector('.transactions-new-page')) return;
    console.log('Initializing script for: .transactions-new-page');

/**
 * Transactions Page - JavaScript
 * Handles transactions page interactions
 */

(function() {
    'use strict';

    // DOM Elements
    const searchInput = document.getElementById('transactionSearch');
    const dateFilter = document.getElementById('transactionDateFilter');
    const filterBtn = document.querySelector('.transactions-filter-btn-new');
    const tableBody = document.getElementById('transactionsTableBody');
    const prevPageBtn = document.getElementById('prevPage');
    const nextPageBtn = document.getElementById('nextPage');
    const currentPageSpan = document.getElementById('currentPage');
    const totalPagesSpan = document.getElementById('totalPages');

    // State
    let currentPage = 1;
    let filteredTransactions = [];

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // highlightActiveNavItem(); // Called in the consolidated init block at the end
        initSearch();
        initFilters();
        initPagination();
    });

    // highlightActiveNavItem consolidated at the end of file

    /**
     * Initialize Search Functionality
     */
    function initSearch() {
        if (!searchInput) return;

        searchInput.addEventListener('input', function(e) {
            applyFilters();
        });
    }

    /**
     * Get start and end of week (Monday to Sunday)
     */
    function getWeekRange(weekType) {
        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        
        // Get Monday of current week
        const dayOfWeek = today.getDay();
        const diffToMonday = dayOfWeek === 0 ? -6 : 1 - dayOfWeek; // If Sunday, go back 6 days, else go to Monday
        
        if (weekType === 'this_week') {
            const weekStart = new Date(today);
            weekStart.setDate(today.getDate() + diffToMonday);
            weekStart.setHours(0, 0, 0, 0);
            
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekStart.getDate() + 6);
            weekEnd.setHours(23, 59, 59, 999);
            
            return {
                start: Math.floor(weekStart.getTime() / 1000),
                end: Math.floor(weekEnd.getTime() / 1000)
            };
        } else if (weekType === 'last_week') {
            const thisWeekStart = new Date(today);
            thisWeekStart.setDate(today.getDate() + diffToMonday);
            
            const lastWeekStart = new Date(thisWeekStart);
            lastWeekStart.setDate(thisWeekStart.getDate() - 7);
            lastWeekStart.setHours(0, 0, 0, 0);
            
            const lastWeekEnd = new Date(lastWeekStart);
            lastWeekEnd.setDate(lastWeekStart.getDate() + 6);
            lastWeekEnd.setHours(23, 59, 59, 999);
            
            return {
                start: Math.floor(lastWeekStart.getTime() / 1000),
                end: Math.floor(lastWeekEnd.getTime() / 1000)
            };
        }
        
        return null;
    }

    /**
     * Apply filters to transaction table
     */
    function applyFilters() {
        const dateFilterEl = document.getElementById('transactionDateFilter');
        const typeFilter = document.querySelector('input[name="transactionTypeFilter"]:checked');
        const searchInputEl = document.getElementById('transactionSearch');
        const tableRows = document.querySelectorAll('.transactions-table-new tbody tr');
        
        const selectedDays = dateFilterEl ? dateFilterEl.value : 'all';
        const selectedType = typeFilter ? typeFilter.value : 'all';
        const searchTerm = searchInputEl ? searchInputEl.value.toLowerCase().trim() : '';
        const now = Math.floor(Date.now() / 1000);
        
        // Calculate date range based on filter type
        let dateRange = null;
        if (selectedDays === 'this_week' || selectedDays === 'last_week') {
            dateRange = getWeekRange(selectedDays);
        } else if (selectedDays !== 'all') {
            const daysInSeconds = parseInt(selectedDays) * 24 * 60 * 60;
            dateRange = {
                start: now - daysInSeconds,
                end: now
            };
        }
        
        tableRows.forEach(row => {
            // Check date filter
            let passesDateFilter = true;
            if (dateRange) {
                const timestamp = parseInt(row.getAttribute('data-transaction-timestamp'));
                if (timestamp) {
                    passesDateFilter = timestamp >= dateRange.start && timestamp <= dateRange.end;
                } else {
                    passesDateFilter = false;
                }
            }
            
            // Check type filter
            let passesTypeFilter = true;
            if (selectedType !== 'all') {
                const rowType = row.getAttribute('data-transaction-type');
                passesTypeFilter = rowType === selectedType;
            }
            
            // Check search filter
            let passesSearchFilter = true;
            if (searchTerm) {
                const text = row.textContent.toLowerCase();
                passesSearchFilter = text.includes(searchTerm);
            }
            
            // Show row only if it passes all filters
            row.style.display = (passesDateFilter && passesTypeFilter && passesSearchFilter) ? '' : 'none';
        });
    }

    /**
     * Initialize Filter Functionality
     */
    function initFilters() {
        const dateFilterEl = document.getElementById('transactionDateFilter');
        const filterBtnEl = document.querySelector('.transactions-filter-btn-new');
        
        // Date filter functionality
        if (dateFilterEl) {
            dateFilterEl.addEventListener('change', function() {
                applyFilters();
            });
        }

        // Type filter button functionality
        if (filterBtnEl) {
            // Create filter dropdown/modal
            let filterDropdown = document.getElementById('transactionsFilterDropdown');
            if (!filterDropdown) {
                filterDropdown = document.createElement('div');
                filterDropdown.id = 'transactionsFilterDropdown';
                filterDropdown.className = 'transactions-filter-dropdown';
                
                const filterTypes = [
                    { value: 'all', label: 'All Types' },
                    { value: 'deposit', label: 'Deposits' },
                    { value: 'withdrawal', label: 'Withdrawals' },
                    { value: 'referral_earning', label: 'Referral Earnings' },
                    { value: 'mining_earning', label: 'Plan Earnings' },
                ];
                
                filterTypes.forEach(type => {
                    const label = document.createElement('label');
                    
                    const input = document.createElement('input');
                    input.type = 'radio';
                    input.name = 'transactionTypeFilter';
                    input.value = type.value;
                    if (type.value === 'all') {
                        input.checked = true;
                    }
                    
                    const span = document.createElement('span');
                    span.textContent = type.label;
                    
                    label.appendChild(input);
                    label.appendChild(span);
                    filterDropdown.appendChild(label);
                });
                
                // Insert after filter button's parent (controls container)
                const controlsContainer = filterBtnEl.closest('.transactions-history-controls-new');
                if (controlsContainer) {
                    controlsContainer.style.position = 'relative';
                    controlsContainer.appendChild(filterDropdown);
                }
            }
            
            // Explicitly hide dropdown on initialization (ensure it's hidden on page load)
            filterDropdown.style.display = 'none';
            
            // Toggle filter dropdown
            filterBtnEl.addEventListener('click', function(e) {
                e.stopPropagation();
                const isVisible = filterDropdown.style.display === 'block' || filterDropdown.style.display === 'flex';
                if (isVisible) {
                    filterDropdown.style.display = 'none';
                } else {
                    // Check if mobile view (window width <= 480px)
                    const isMobile = window.innerWidth <= 480;
                    filterDropdown.style.display = isMobile ? 'block' : 'block';
                }
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!filterBtnEl.contains(e.target) && !filterDropdown.contains(e.target)) {
                    filterDropdown.style.display = 'none';
                }
            });
            
            // Handle type filter selection
            const typeInputs = filterDropdown.querySelectorAll('input[name="transactionTypeFilter"]');
            typeInputs.forEach(input => {
                input.addEventListener('change', function() {
                    applyFilters();
                    filterDropdown.style.display = 'none';
                });
            });
        }
    }

    /**
     * Initialize Pagination
     */
    function initPagination() {
        if (prevPageBtn) {
            prevPageBtn.addEventListener('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    updatePagination();
                }
            });
        }

        if (nextPageBtn) {
            nextPageBtn.addEventListener('click', function() {
                currentPage++;
                updatePagination();
            });
        }
    }

    /**
     * Update Pagination UI
     */
    function updatePagination() {
        if (currentPageSpan) {
            currentPageSpan.textContent = currentPage;
        }

        if (totalPagesSpan) {
            // Calculate total pages based on visible rows
            const visibleRows = tableBody.querySelectorAll('tr:not([style*="display: none"])').length;
            const rowsPerPage = 10; // Adjust as needed
            const totalPages = Math.max(1, Math.ceil(visibleRows / rowsPerPage));
            totalPagesSpan.textContent = totalPages;
        }

        if (prevPageBtn) {
            prevPageBtn.disabled = currentPage === 1;
        }

        if (nextPageBtn) {
            const visibleRows = tableBody.querySelectorAll('tr:not([style*="display: none"])').length;
            const rowsPerPage = 10; // Adjust as needed
            const totalPages = Math.max(1, Math.ceil(visibleRows / rowsPerPage));
            nextPageBtn.disabled = currentPage >= totalPages;
        }
    }

})();


})();

/* --- Wrapped Asset: assets/dashboard/js/pages/wallet.js --- */
(function() { 
    if (!document.querySelector('.wallet-new-page')) return;
    console.log('Initializing script for: .wallet-new-page');

/**
 * Wallet Page - UI Logic
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // DOM Elements
    const balanceToggle = document.getElementById('balanceToggleWallet');
    const balanceToggleMobile = document.getElementById('balanceToggleWalletMobile');
    const balanceAmount = document.getElementById('balanceAmountWallet');
    const eyeIcon = document.getElementById('eyeIconWallet');
    const eyeSlashIcon = document.getElementById('eyeSlashIconWallet');
    const mainBalanceText = balanceAmount ? balanceAmount.querySelector('.wallet-balance-amount') : null;
    const detailValues = document.querySelectorAll('.wallet-detail-value');

    // Filter Elements
    const searchInput = document.getElementById('walletSearchInput');
    const dateFilter = document.getElementById('walletDateFilter');
    const filterBtn = document.querySelector('.wallet-filter-button');

    /**
     * Balance Visibility Management
     */
    function isBalanceHidden() {
        return localStorage.getItem('sparkx_wallet_balance_hidden') === 'true';
    }

    function toggleBalanceVisibility() {
        const currentlyHidden = isBalanceHidden();
        localStorage.setItem('sparkx_wallet_balance_hidden', !currentlyHidden);
        applyVisibility();
    }

    function applyVisibility() {
        const hidden = isBalanceHidden();
        
        if (!hidden) {
            // Show State
            if (balanceAmount) {
                balanceAmount.style.filter = 'none';
                balanceAmount.style.opacity = '1';
            }
            if (eyeIcon) eyeIcon.style.display = 'block';
            if (eyeSlashIcon) eyeSlashIcon.style.display = 'none';

            // Restore Main Balance
            if (mainBalanceText) {
                const original = mainBalanceText.getAttribute('data-original');
                if (original) {
                    mainBalanceText.textContent = original;
                }
            }

            // Restore Detail Values
            detailValues.forEach(el => {
                const original = el.getAttribute('data-original');
                if (original) {
                    el.innerHTML = original;
                }
            });
        } else {
            // Hide State
            if (balanceAmount) {
                balanceAmount.style.filter = 'blur(4px)';
                balanceAmount.style.opacity = '0.7';
            }
            if (eyeIcon) eyeIcon.style.display = 'none';
            if (eyeSlashIcon) eyeSlashIcon.style.display = 'block';

            // Mask Main Balance
            if (mainBalanceText) {
                if (!mainBalanceText.getAttribute('data-original')) {
                    mainBalanceText.setAttribute('data-original', mainBalanceText.textContent);
                }
                mainBalanceText.textContent = '••••••';
            }

            // Mask Detail Values
            detailValues.forEach(el => {
                if (!el.getAttribute('data-original')) {
                    el.setAttribute('data-original', el.innerHTML.trim());
                }
                el.innerHTML = '••••';
            });
        }
    }

    // Initialize Visibility
    applyVisibility();

    // Listeners
    if (balanceToggle) balanceToggle.addEventListener('click', toggleBalanceVisibility);
    if (balanceToggleMobile) balanceToggleMobile.addEventListener('click', toggleBalanceVisibility);

    /**
     * Transaction Search & Filters
     */
    function applyFilters() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const selectedDateRange = dateFilter ? dateFilter.value : 'all';
        const rows = document.querySelectorAll('.wallet-table tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const matchesSearch = text.includes(searchTerm);
            
            // Note: Date filtering would require timestamps in data attributes
            // For now, we search within the text
            row.style.display = matchesSearch ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (dateFilter) dateFilter.addEventListener('change', applyFilters);
});

})();
    let selectedPaymentMethod = null;
    const conversionRate = parseFloat(280) || 0;
    const hasPendingWithdrawal = false;
    
    // Disable form elements if user has pending withdrawal
    if (hasPendingWithdrawal) {
        document.addEventListener('DOMContentLoaded', function() {
            // Disable all payment method selections
            document.querySelectorAll('.withdraw-payment-method').forEach(method => {
                method.style.pointerEvents = 'none';
                method.style.opacity = '0.6';
                method.style.cursor = 'not-allowed';
            });
            
            // Disable amount input
            const amountInput = document.getElementById('withdraw-amount-input');
            if (amountInput) {
                amountInput.disabled = true;
                amountInput.style.cursor = 'not-allowed';
            }
            
            // Disable preset amount buttons
            document.querySelectorAll('.withdraw-preset-btn').forEach(btn => {
                btn.disabled = true;
                btn.style.cursor = 'not-allowed';
                btn.style.opacity = '0.6';
            });
            
            // Disable continue button
            const continueBtn = document.getElementById('withdraw-continue-btn');
            if (continueBtn) {
                continueBtn.disabled = true;
                continueBtn.style.cursor = 'not-allowed';
            }
        });
    }
    
    // Crypto wallets data for minimum withdrawal calculation
    const cryptoWallets = [{"id":1,"network":"bnb_smart_chain","network_display_name":"BNB Smart Chain","wallet_address":"0xcf7393C2eDea75F99C988926C9038Af27cC733b1","qr_code_image":"assets\/admin\/images\/crypto-wallets\/w3LXMKg4urTciLYvZ5NafJBtaOAJo0TxRDO3qUxi.jpeg","token":"USDT","is_active":true,"allowed_for_deposit":true,"allowed_for_withdrawal":true,"minimum_deposit":"5.00","maximum_deposit":"1000.00","minimum_withdrawal":"5.00","maximum_withdrawal":"100.00","created_at":"2026-01-25T19:25:35.000000Z","updated_at":"2026-04-02T09:54:08.000000Z"},{"id":2,"network":"tron","network_display_name":"TRON","wallet_address":"TCe6zfypPevALjoo5FiQNEN5k34dRMk3uD","qr_code_image":"assets\/admin\/images\/crypto-wallets\/528v2G8gIHLfnmOF5HSxDArQydXRLjuch2f9faVp.jpeg","token":"USDT","is_active":true,"allowed_for_deposit":true,"allowed_for_withdrawal":true,"minimum_deposit":"5.00","maximum_deposit":"1000.00","minimum_withdrawal":"5.00","maximum_withdrawal":"100.00","created_at":"2026-01-25T19:26:38.000000Z","updated_at":"2026-04-01T18:55:47.000000Z"},{"id":5,"network":"ethereum","network_display_name":"Ethirum","wallet_address":"0xe97cf15d41682cfa903359db3914966dfbddfac4","qr_code_image":"assets\/admin\/images\/crypto-wallets\/AJDbt3oAGa87vsU9H3Q5qPyabRYiZfwOC5qRYHIP.jpeg","token":"USDT","is_active":true,"allowed_for_deposit":true,"allowed_for_withdrawal":true,"minimum_deposit":"10.00","maximum_deposit":"500000.00","minimum_withdrawal":"10.00","maximum_withdrawal":"50000.00","created_at":"2026-04-01T04:41:04.000000Z","updated_at":"2026-04-01T04:41:04.000000Z"},{"id":6,"network":"solana","network_display_name":"Solana","wallet_address":"Fs1gmWDhYh5anJerENBfUUdjAHHN66V1RNeY5QhpeF7P","qr_code_image":"assets\/admin\/images\/crypto-wallets\/X3Dhn3tLslIpOrQdjb5mnSmmHmnPn4pr7Na1eekT.jpeg","token":"USDT","is_active":true,"allowed_for_deposit":true,"allowed_for_withdrawal":true,"minimum_deposit":"10.00","maximum_deposit":"50000.00","minimum_withdrawal":"10.00","maximum_withdrawal":"50000.00","created_at":"2026-04-01T06:24:47.000000Z","updated_at":"2026-04-01T06:24:47.000000Z"}];
    const cryptoFee = 1.00;

    // Function to update PKR amount display
    function updateWithdrawPKRAmount() {
        const amountInput = document.getElementById('withdraw-amount-input');
        const pkrAmountDisplay = document.getElementById('withdraw-pkr-amount');
        const pkrAmountText = document.getElementById('withdraw-pkr-amount-text');

        if (!amountInput || !pkrAmountDisplay || !pkrAmountText) {
            return;
        }

        const amount = parseFloat(amountInput.value) || 0;
        const rate = conversionRate;

        // Show PKR amount only if payment method is selected, amount is valid, and conversion rate exists
        if (selectedPaymentMethod && amount > 0 && rate > 0) {
            const pkrAmount = amount * rate;
            // Format dollar amount, remove trailing zeros
            const formattedUSD = amount.toLocaleString('en-US', {
                maximumFractionDigits: 2,
                minimumFractionDigits: 0
            });
            // Format PKR amount with commas, remove trailing zeros
            const formattedPKR = pkrAmount.toLocaleString('en-US', {
                maximumFractionDigits: 2,
                minimumFractionDigits: 0
            });
            pkrAmountText.textContent = `$${formattedUSD} = Rs ${formattedPKR}`;
            pkrAmountDisplay.style.display = 'block';
        } else {
            pkrAmountDisplay.style.display = 'none';
        }
    }

    // Payment method selection
    document.querySelectorAll('.withdraw-payment-method').forEach(method => {
        method.addEventListener('click', function() {
            // Remove active class from all methods
            document.querySelectorAll('.withdraw-payment-method').forEach(m => m.classList.remove('active'));

            // Add active class to clicked method
            this.classList.add('active');

            // Store selected payment method data
            let minWithdrawal = parseFloat(this.dataset.minWithdrawal) || 0;
            let maxWithdrawal = parseFloat(this.dataset.maxWithdrawal) || null;
            const methodType = this.dataset.methodType || 'rast';
            
            // For crypto payment methods, calculate minimum from crypto wallets (accounting for fee)
            if (methodType === 'crypto' && cryptoWallets.length > 0) {
                const cryptoMinWithdrawals = cryptoWallets
                    .filter(w => w.minimum_withdrawal)
                    .map(w => parseFloat(w.minimum_withdrawal));
                
                if (cryptoMinWithdrawals.length > 0) {
                    const minCryptoWithdrawal = Math.min(...cryptoMinWithdrawals);
                    // User needs to select minimum + fee to receive the minimum
                    minWithdrawal = minCryptoWithdrawal + cryptoFee;
                } else {
                    // No minimum set, but still need to account for fee
                    minWithdrawal = cryptoFee + 0.01; // At least $1.01 to receive $0.01
                }
                
                // For maximum, also account for fee if set
                if (cryptoWallets.some(w => w.maximum_withdrawal)) {
                    const cryptoMaxWithdrawals = cryptoWallets
                        .filter(w => w.maximum_withdrawal)
                        .map(w => parseFloat(w.maximum_withdrawal));
                    if (cryptoMaxWithdrawals.length > 0) {
                        const maxCryptoWithdrawal = Math.max(...cryptoMaxWithdrawals);
                        maxWithdrawal = maxCryptoWithdrawal + cryptoFee;
                    }
                }
            }
            
            selectedPaymentMethod = {
                id: this.dataset.methodId,
                name: this.dataset.methodName,
                type: methodType,
                minWithdrawal: minWithdrawal,
                maxWithdrawal: maxWithdrawal
            };

            // Show withdraw amount section with animation
            const withdrawAmountSection = document.querySelector('.withdraw-amount-section');
            if (withdrawAmountSection) {
                // Add show class
                withdrawAmountSection.classList.add('show');
                // Use inline styles to ensure visibility (overrides any CSS conflicts)
                withdrawAmountSection.style.display = 'block';
                withdrawAmountSection.style.opacity = '1';
                // Smooth scroll to the amount section for better UX
                setTimeout(() => {
                    withdrawAmountSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 300);
            }

            // Update limit info
            const limitInfo = document.getElementById('withdraw-limit-info');
            if (limitInfo) {
                let limitText = '';
                if (selectedPaymentMethod.minWithdrawal > 0) {
                    limitText += 'Min: $' + selectedPaymentMethod.minWithdrawal.toFixed(2);
                }
                if (selectedPaymentMethod.maxWithdrawal) {
                    if (limitText) limitText += ' | ';
                    limitText += 'Max: $' + selectedPaymentMethod.maxWithdrawal.toFixed(2);
                }
                limitInfo.textContent = limitText;
                limitInfo.style.display = limitText ? 'block' : 'none';
            }

            // Update minimum withdrawal instruction text
            const minAmountText = document.getElementById('withdraw-min-amount-text');
            if (minAmountText && selectedPaymentMethod) {
                let minWithdrawal = selectedPaymentMethod.minWithdrawal || 0;
                let instructionText = '';
                
                // For crypto payment methods, use crypto wallet minimum (accounting for fee)
                if (selectedPaymentMethod.type === 'crypto' && cryptoWallets.length > 0) {
                    // Get the minimum withdrawal from all active crypto wallets
                    // User needs to select: crypto_wallet_minimum + $1 fee
                    const cryptoMinWithdrawals = cryptoWallets
                        .filter(w => w.minimum_withdrawal)
                        .map(w => parseFloat(w.minimum_withdrawal));
                    
                    if (cryptoMinWithdrawals.length > 0) {
                        const minCryptoWithdrawal = Math.min(...cryptoMinWithdrawals);
                        // User needs to select minimum + fee to receive the minimum
                        minWithdrawal = minCryptoWithdrawal + cryptoFee;
                        instructionText = `Minimum withdrawal for ${selectedPaymentMethod.name} is $${minWithdrawal.toFixed(2)} (including $${cryptoFee.toFixed(2)} fee). You will receive $${minCryptoWithdrawal.toFixed(2)}.`;
                    } else {
                        // No minimum set, but still need to account for fee
                        minWithdrawal = cryptoFee + 0.01; // At least $1.01 to receive $0.01
                        instructionText = `Minimum withdrawal for ${selectedPaymentMethod.name} is $${minWithdrawal.toFixed(2)} (including $${cryptoFee.toFixed(2)} fee).`;
                    }
                } else {
                    // For non-crypto payment methods
                    const formattedMinWithdrawal = minWithdrawal > 0 
                        ? minWithdrawal.toFixed(2) 
                        : '0.00';
                    instructionText = `Minimum withdrawal for ${selectedPaymentMethod.name} is $${formattedMinWithdrawal}`;
                }
                
                minAmountText.textContent = instructionText;
            }

            // Update input min/max attributes and clear previous selections
            const amountInput = document.getElementById('withdraw-amount-input');
            if (amountInput) {
                amountInput.min = selectedPaymentMethod.minWithdrawal || 0.01;
                if (selectedPaymentMethod.maxWithdrawal) {
                    amountInput.max = selectedPaymentMethod.maxWithdrawal;
                } else {
                    amountInput.removeAttribute('max');
                }
                // Clear previous amount and preset selections
                amountInput.value = '';
                document.querySelectorAll('.withdraw-preset-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                // Update PKR amount display (will hide since amount is cleared)
                updateWithdrawPKRAmount();
            }

            // Update continue button text
            const continueBtn = document.getElementById('withdraw-continue-btn');
            if (continueBtn && selectedPaymentMethod) {
                continueBtn.textContent = `Continue Withdrawal with ${selectedPaymentMethod.name}`;
            }
        });
    });

    // Preset amount buttons
    document.querySelectorAll('.withdraw-preset-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all preset buttons
            document.querySelectorAll('.withdraw-preset-btn').forEach(b => b.classList.remove('active'));

            // Add active class to clicked button
            this.classList.add('active');

            // Set the amount in the input field
            const amount = this.dataset.amount;
            const amountInput = document.getElementById('withdraw-amount-input');
            if (amountInput) {
                amountInput.value = amount;
                // Update PKR amount display immediately
                updateWithdrawPKRAmount();
                // Trigger input event to validate
                amountInput.dispatchEvent(new Event('input'));
            }
        });
    });

    // Clear preset selection when user types custom amount
    const amountInput = document.getElementById('withdraw-amount-input');
    const continueBtn = document.getElementById('withdraw-continue-btn');

    if (amountInput && continueBtn) {
        amountInput.addEventListener('input', function() {
            // Check if the value matches any preset
            const value = parseFloat(this.value);
            const presetButtons = document.querySelectorAll('.withdraw-preset-btn');
            let matchesPreset = false;

            presetButtons.forEach(btn => {
                const presetAmount = parseFloat(btn.dataset.amount);
                if (value === presetAmount) {
                    btn.classList.add('active');
                    matchesPreset = true;
                } else {
                    btn.classList.remove('active');
                }
            });

            // If doesn't match any preset, clear all active states
            if (!matchesPreset && this.value !== '') {
                presetButtons.forEach(btn => btn.classList.remove('active'));
            }

            // Update PKR amount display
            updateWithdrawPKRAmount();

            const amount = parseFloat(this.value);
            // Note: Withdrawals can only use dynamic user earning balance
            const userBalance = typeof userEarningBalance !== 'undefined' ? parseFloat(userEarningBalance) : 0.00;

            // Get message elements
            const insufficientBalanceMsg = document.getElementById('withdraw-insufficient-balance-message');
            const insufficientBalanceText = document.getElementById('withdraw-insufficient-balance-text');

            if (!selectedPaymentMethod) {
                continueBtn.disabled = true;
                if (insufficientBalanceMsg) insufficientBalanceMsg.style.display = 'none';
                return;
            }

            if (!amount || amount <= 0) {
                continueBtn.disabled = true;
                if (insufficientBalanceMsg) insufficientBalanceMsg.style.display = 'none';
                return;
            }

            // Check minimum
            if (selectedPaymentMethod.minWithdrawal > 0 && amount < selectedPaymentMethod.minWithdrawal) {
                continueBtn.disabled = true;
                if (insufficientBalanceMsg && insufficientBalanceText) {
                    if (userBalance < selectedPaymentMethod.minWithdrawal) {
                        insufficientBalanceText.textContent = `Minimum withdrawal amount is $${selectedPaymentMethod.minWithdrawal.toFixed(2)}, but your available balance is only $${userBalance.toFixed(2)}. You can only withdraw from mining and referral earnings.`;
                        insufficientBalanceMsg.style.display = 'block';
                    } else {
                        insufficientBalanceMsg.style.display = 'none';
                    }
                }
                return;
            }

            // Check maximum
            if (selectedPaymentMethod.maxWithdrawal && amount > selectedPaymentMethod.maxWithdrawal) {
                continueBtn.disabled = true;
                if (insufficientBalanceMsg) insufficientBalanceMsg.style.display = 'none';
                return;
            }

            // Check user balance
            
            if (amount > userBalance) {
                continueBtn.disabled = true;
                if (insufficientBalanceMsg && insufficientBalanceText) {
                    const minRequired = selectedPaymentMethod.minWithdrawal || 0;
                    if (minRequired > 0 && userBalance < minRequired) {
                        insufficientBalanceText.textContent = `Minimum withdrawal amount is $${minRequired.toFixed(2)}, but your available balance is only $${userBalance.toFixed(2)}. You can only withdraw from mining and referral earnings.`;
                    } else {
                        insufficientBalanceText.textContent = `Insufficient balance. Your available withdrawal balance is $${userBalance.toFixed(2)}. You can only withdraw from mining and referral earnings.`;
                    }
                    insufficientBalanceMsg.style.display = 'block';
                }
                return;
            } else {
                if (insufficientBalanceMsg) {
                    insufficientBalanceMsg.style.display = 'none';
                }
            }

            continueBtn.disabled = false;
        });
    }

    // Continue button click
    if (continueBtn) {
        continueBtn.addEventListener('click', function() {
            if (this.disabled) return;

            const amount = parseFloat(amountInput.value);
            if (!selectedPaymentMethod || !amount) {
                alert('Please select a payment method and enter an amount.');
                return;
            }

            // Navigate to confirmation page
            const base = window.location.pathname.substring(0, window.location.pathname.indexOf('/user/dashboard'));
            window.location.href = `${base}/user/dashboard/withdraw_confirm.php?method_id=${selectedPaymentMethod.id}&amount=${amount}`;
        });
    }

    // Withdrawal History Filtering and Search
    const withdrawSearchInput = document.getElementById('withdraw-search-input');
    const withdrawDateFilter = document.getElementById('withdraw-date-filter');
    const withdrawTransactionsList = document.getElementById('withdraw-transactions-list');
    const withdrawHistoryEmpty = document.getElementById('withdraw-history-empty');

    // Advance Search Modal Elements
    const withdrawAdvanceSearchModal = document.getElementById('withdraw-advance-search-modal');
    const withdrawAdvanceSearchClose = document.getElementById('withdraw-advance-search-close');
    const withdrawAdvanceSearchApply = document.getElementById('withdraw-advance-apply');
    const withdrawAdvanceSearchClear = document.getElementById('withdraw-advance-clear');
    const withdrawDateRangeInput = document.getElementById('withdraw-date-range-input');
    const withdrawAdvanceSortSelect = document.getElementById('withdraw-advance-sort');

    // Filter state
    let withdrawDateRangeFilter = null;
    let withdrawSortOrder = 'newest';

    // Date range picker
    let withdrawStartDate = null;
    let withdrawEndDate = null;
    const withdrawStartDateInput = document.getElementById('withdraw-start-date');
    const withdrawEndDateInput = document.getElementById('withdraw-end-date');

    function updateWithdrawDateRangeDisplay() {
        if (withdrawStartDate && withdrawEndDate) {
            const formatDate = (date) => {
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${day}/${month}/${year}`;
            };
            withdrawDateRangeInput.value = `${formatDate(withdrawStartDate)} - ${formatDate(withdrawEndDate)}`;
            withdrawDateRangeFilter = {
                start: Math.floor(withdrawStartDate.getTime() / 1000),
                end: Math.floor(withdrawEndDate.getTime() / 1000) + 86400 // Add one day to include the end date
            };
        } else {
            withdrawDateRangeInput.value = '';
            withdrawDateRangeFilter = null;
        }
    }

    if (withdrawDateRangeInput) {
        withdrawDateRangeInput.addEventListener('click', function() {
            if (withdrawStartDateInput && typeof withdrawStartDateInput.showPicker === 'function') {
                withdrawStartDateInput.showPicker();
            } else {
                withdrawStartDateInput.click();
            }
        });
    }

    if (withdrawStartDateInput) {
        withdrawStartDateInput.addEventListener('change', function() {
            withdrawStartDate = new Date(this.value);
            if (withdrawEndDateInput) {
                withdrawEndDateInput.min = this.value;
                if (withdrawEndDate && withdrawEndDate < withdrawStartDate) {
                    withdrawEndDate = null;
                    withdrawEndDateInput.value = '';
                }
                updateWithdrawDateRangeDisplay();
                // Automatically open end date picker
                setTimeout(() => {
                    if (typeof withdrawEndDateInput.showPicker === 'function') {
                        withdrawEndDateInput.showPicker();
                    } else {
                        withdrawEndDateInput.click();
                    }
                }, 100);
            } else {
                updateWithdrawDateRangeDisplay();
            }
        });
    }

    if (withdrawEndDateInput) {
        withdrawEndDateInput.addEventListener('change', function() {
            withdrawEndDate = new Date(this.value);
            updateWithdrawDateRangeDisplay();
        });
    }

    function filterWithdrawals() {
        const searchTerm = withdrawSearchInput ? withdrawSearchInput.value.toLowerCase().trim() : '';
        const dateFilter = withdrawDateFilter ? withdrawDateFilter.value : 'all';
        const transactionCards = withdrawTransactionsList ? Array.from(withdrawTransactionsList.querySelectorAll('.withdraw-transaction-card')) : [];

        let visibleCount = 0;
        const now = Math.floor(Date.now() / 1000);
        const daysInSeconds = {
            '3': 3 * 24 * 60 * 60,
            '7': 7 * 24 * 60 * 60,
            '30': 30 * 24 * 60 * 60
        };

        // Filter cards
        const filteredCards = transactionCards.filter(card => {
            const transactionDate = parseInt(card.dataset.date);
            const transactionStatus = card.dataset.status.toLowerCase();
            const transactionAmount = parseFloat(card.dataset.amount);
            const transactionId = (card.dataset.transactionId || '').toLowerCase();
            const cardText = card.textContent.toLowerCase();

            // Date filter (from dropdown)
            let dateMatch = true;
            if (dateFilter !== 'all' && !withdrawDateRangeFilter) {
                const daysAgo = daysInSeconds[dateFilter];
                const cutoffDate = now - daysAgo;
                dateMatch = transactionDate >= cutoffDate;
            }

            // Date range filter (from advance search)
            if (withdrawDateRangeFilter) {
                dateMatch = transactionDate >= withdrawDateRangeFilter.start && transactionDate <= withdrawDateRangeFilter.end;
            }

            // Search filter
            let searchMatch = true;
            if (searchTerm) {
                searchMatch = cardText.includes(searchTerm) ||
                             transactionId.includes(searchTerm) ||
                             transactionAmount.toString().includes(searchTerm);
            }

            return dateMatch && searchMatch;
        });

        // Sort filtered cards
        filteredCards.sort((a, b) => {
            const dateA = parseInt(a.dataset.date);
            const dateB = parseInt(b.dataset.date);
            const amountA = parseFloat(a.dataset.amount);
            const amountB = parseFloat(b.dataset.amount);

            switch(withdrawSortOrder) {
                case 'newest':
                    return dateB - dateA;
                case 'oldest':
                    return dateA - dateB;
                case 'amount-high':
                    return amountB - amountA;
                case 'amount-low':
                    return amountA - amountB;
                default:
                    return dateB - dateA;
            }
        });

        // Show/hide cards
        transactionCards.forEach(card => {
            if (filteredCards.includes(card)) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Reorder visible cards
        filteredCards.forEach(card => {
            withdrawTransactionsList.appendChild(card);
        });

        // Show/hide empty state
        if (withdrawHistoryEmpty) {
            if (visibleCount === 0) {
                withdrawHistoryEmpty.classList.add('show');
            } else {
                withdrawHistoryEmpty.classList.remove('show');
            }
        }
    }

    // Open advance search modal
    function openWithdrawAdvanceSearchModal() {
        if (withdrawAdvanceSearchModal) {
            withdrawAdvanceSearchModal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }

    // Close advance search modal
    function closeWithdrawAdvanceSearchModal() {
        if (withdrawAdvanceSearchModal) {
            withdrawAdvanceSearchModal.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    // Apply filters from advance search
    if (withdrawAdvanceSearchApply) {
        withdrawAdvanceSearchApply.addEventListener('click', function() {
            withdrawSortOrder = withdrawAdvanceSortSelect ? withdrawAdvanceSortSelect.value : 'newest';
            filterWithdrawals();
            closeWithdrawAdvanceSearchModal();
        });
    }

    // Clear filters
    if (withdrawAdvanceSearchClear) {
        withdrawAdvanceSearchClear.addEventListener('click', function() {
            withdrawStartDate = null;
            withdrawEndDate = null;
            withdrawDateRangeFilter = null;
            if (withdrawDateRangeInput) {
                withdrawDateRangeInput.value = '';
            }
            if (withdrawStartDateInput) {
                withdrawStartDateInput.value = '';
            }
            if (withdrawEndDateInput) {
                withdrawEndDateInput.value = '';
            }
            if (withdrawAdvanceSortSelect) {
                withdrawAdvanceSortSelect.value = 'newest';
            }
            withdrawSortOrder = 'newest';
            filterWithdrawals();
            closeWithdrawAdvanceSearchModal();
        });
    }

    // Close modal handlers
    if (withdrawAdvanceSearchClose) {
        withdrawAdvanceSearchClose.addEventListener('click', closeWithdrawAdvanceSearchModal);
    }

    if (withdrawAdvanceSearchModal) {
        withdrawAdvanceSearchModal.addEventListener('click', function(e) {
            if (e.target === withdrawAdvanceSearchModal) {
                closeWithdrawAdvanceSearchModal();
            }
        });
    }

    // Open modal from filter icons
    const withdrawFilterIcons = document.querySelectorAll('.withdraw-filter-icon, .withdraw-search-filter-btn');
    withdrawFilterIcons.forEach(icon => {
        icon.addEventListener('click', openWithdrawAdvanceSearchModal);
    });

    // Add event listeners
    if (withdrawSearchInput) {
        withdrawSearchInput.addEventListener('input', filterWithdrawals);
    }

    if (withdrawDateFilter) {
        withdrawDateFilter.addEventListener('change', filterWithdrawals);
    }

    // Initial filter
    filterWithdrawals();

    // Proof Image Modal Functions
    function openProofModal(imageUrl) {
        const modal = document.getElementById('proof-modal');
        const modalImage = document.getElementById('proof-modal-image');
        if (modal && modalImage) {
            modalImage.src = imageUrl;
            modal.classList.add('show');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }
    }

    function closeProofModal() {
        const modal = document.getElementById('proof-modal');
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = ''; // Restore scrolling
        }
    }

    // Close modal when clicking outside the image
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('proof-modal');
        if (modal && event.target === modal) {
            closeProofModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeProofModal();
        }
    });
