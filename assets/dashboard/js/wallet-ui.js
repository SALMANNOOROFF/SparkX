/**
 * wallet-ui.js
 * Specific UI logic for user/dashboard/wallet.html
 */

document.addEventListener('DOMContentLoaded', function() {
    // Balance Visibility Toggle
    const balanceToggle = document.getElementById('balanceToggle');
    const balanceToggleMobile = document.getElementById('balanceToggleMobile');
    const balanceToggleMobileIcon = document.getElementById('balanceToggleMobileIcon');
    const balanceLabelEye = document.getElementById('balanceLabelEye');

    const balanceAmountEl = document.getElementById('totalBalance');
    const balanceText = document.querySelector('.wallet-balance-amount');

    const eyeIcon = document.getElementById('eyeIcon');
    const eyeSlashIcon = document.getElementById('eyeSlashIcon');
    const eyeIconMobile = document.getElementById('eyeIconMobile');
    const eyeSlashIconMobile = document.getElementById('eyeSlashIconMobile');

    function isBalanceHidden() {
        return localStorage.getItem('wallet_balance_hidden') === 'true';
    }

    function toggleBalanceVisibility() {
        const currentlyHidden = isBalanceHidden();
        localStorage.setItem('wallet_balance_hidden', !currentlyHidden);
        applyBalanceVisibility();
    }

    function applyBalanceVisibility() {
        const hidden = isBalanceHidden();
        
        if (!hidden) {
            // Show balance
            if (eyeIcon) eyeIcon.style.display = 'block';
            if (eyeSlashIcon) eyeSlashIcon.style.display = 'none';
            if (eyeIconMobile) eyeIconMobile.style.display = 'block';
            if (eyeSlashIconMobile) eyeSlashIconMobile.style.display = 'none';
            
            if (balanceLabelEye) {
                balanceLabelEye.classList.remove('fa-eye');
                balanceLabelEye.classList.add('fa-eye'); // Stays eye as per original logic
            }
            
            if (balanceToggleMobileIcon) {
                balanceToggleMobileIcon.classList.remove('fa-eye-slash');
                balanceToggleMobileIcon.style.color = '';
            }
            
            if (balanceAmountEl) balanceAmountEl.style.opacity = '1';
            
            // Restore original values
            const detailValues = document.querySelectorAll('.wallet-detail-value');
            detailValues.forEach(el => {
                const original = el.getAttribute('data-original');
                if (original) {
                    el.innerHTML = original;
                    el.removeAttribute('data-original');
                }
            });
            
            // For main balance
            if (balanceText) {
                const original = balanceText.getAttribute('data-original');
                if (original) {
                    balanceText.textContent = original;
                    balanceText.removeAttribute('data-original');
                }
            }
        } else {
            // Hide balance
            if (eyeIcon) eyeIcon.style.display = 'none';
            if (eyeSlashIcon) eyeSlashIcon.style.display = 'block';
            if (eyeIconMobile) eyeIconMobile.style.display = 'none';
            if (eyeSlashIconMobile) eyeSlashIconMobile.style.display = 'block';
            
            if (balanceAmountEl) balanceAmountEl.style.opacity = '0.3';
            
            if (balanceText) {
                if (!balanceText.getAttribute('data-original')) {
                    balanceText.setAttribute('data-original', balanceText.textContent);
                }
                balanceText.textContent = '••••••';
            }

            const detailValues = document.querySelectorAll('.wallet-detail-value');
            detailValues.forEach(el => {
                if (!el.getAttribute('data-original')) {
                    el.setAttribute('data-original', el.innerHTML);
                }
                el.innerHTML = '••••';
            });
        }
    }

    // Initial state
    applyBalanceVisibility();

    // Listeners
    if (balanceToggle) balanceToggle.addEventListener('click', toggleBalanceVisibility);
    if (balanceToggleMobile) balanceToggleMobile.addEventListener('click', toggleBalanceVisibility);
    if (balanceToggleMobileIcon) balanceToggleMobileIcon.addEventListener('click', toggleBalanceVisibility);
    if (balanceLabelEye) balanceLabelEye.addEventListener('click', toggleBalanceVisibility);
});
