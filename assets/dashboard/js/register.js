/**
 * Dashboard Register Page - JavaScript
 * Handles form interactions, validation, and UX enhancements
 */

(function() {
    'use strict';

    // DOM Elements
    const registerForm = document.getElementById('registerForm');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const passwordInput = document.getElementById('password');
    const passwordConfirmationInput = document.getElementById('password_confirmation');
    const passwordToggle = document.getElementById('passwordToggle');
    const passwordConfirmationToggle = document.getElementById('passwordConfirmationToggle');
    const signupButton = document.getElementById('signupButton');
    const termsCheckbox = document.getElementById('terms');

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        initPasswordToggles();
        initFormValidation();
        initFormSubmission();
        initInputAnimations();
        initPhoneFormatting();
    });

    /**
     * Password Toggle Functionality
     */
    function initPasswordToggles() {
        // Password toggle
        if (passwordToggle && passwordInput) {
            passwordToggle.addEventListener('click', function() {
                togglePasswordVisibility(passwordInput, passwordToggle);
            });
        }

        // Password confirmation toggle
        if (passwordConfirmationToggle && passwordConfirmationInput) {
            passwordConfirmationToggle.addEventListener('click', function() {
                togglePasswordVisibility(passwordConfirmationInput, passwordConfirmationToggle);
            });
        }
    }

    /**
     * Toggle Password Visibility
     */
    function togglePasswordVisibility(input, toggle) {
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        
        // Update icon
        const icon = toggle.querySelector('svg');
        if (type === 'text') {
            // Show eye-off icon
            icon.innerHTML = `
                <path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
            `;
        } else {
            // Show eye icon
            icon.innerHTML = `
                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
            `;
        }
    }

    /**
     * Real-time Form Validation
     */
    function initFormValidation() {
        // Name validation
        if (nameInput) {
            nameInput.addEventListener('blur', function() {
                validateName(this);
            });

            nameInput.addEventListener('input', function() {
                if (this.classList.contains('input-error')) {
                    validateName(this);
                }
            });
        }

        // Email validation
        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                validateEmail(this);
            });

            emailInput.addEventListener('input', function() {
                if (this.classList.contains('input-error')) {
                    validateEmail(this);
                }
            });
        }

        // Phone validation (required)
        if (phoneInput) {
            phoneInput.addEventListener('blur', function() {
                validatePhone(this);
            });

            phoneInput.addEventListener('input', function() {
                if (this.classList.contains('input-error')) {
                    validatePhone(this);
                }
            });
        }

        // Password validation
        if (passwordInput) {
            passwordInput.addEventListener('blur', function() {
                validatePassword(this);
            });

            passwordInput.addEventListener('input', function() {
                if (this.classList.contains('input-error')) {
                    validatePassword(this);
                }
                // Check password match when typing
                if (passwordConfirmationInput && passwordConfirmationInput.value) {
                    validatePasswordMatch();
                }
            });
        }

        // Password confirmation validation
        if (passwordConfirmationInput) {
            passwordConfirmationInput.addEventListener('blur', function() {
                validatePasswordMatch();
            });

            passwordConfirmationInput.addEventListener('input', function() {
                if (this.classList.contains('input-error')) {
                    validatePasswordMatch();
                }
            });
        }
    }

    /**
     * Validate Name
     */
    function validateName(input) {
        const isValid = input.value.trim().length >= 2;

        if (input.value.trim() && !isValid) {
            input.classList.add('input-error');
            showFieldError(input, 'Name must be at least 2 characters');
            return false;
        } else {
            input.classList.remove('input-error');
            hideFieldError(input);
            return true;
        }
    }

    /**
     * Validate Email
     */
    function validateEmail(input) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const isValid = emailRegex.test(input.value.trim());

        if (input.value.trim() && !isValid) {
            input.classList.add('input-error');
            showFieldError(input, 'Please enter a valid email address');
            return false;
        } else {
            input.classList.remove('input-error');
            hideFieldError(input);
            return true;
        }
    }

    /**
     * Validate Phone (Pakistani format)
     */
    function validatePhone(input) {
        if (!input.value.trim()) {
            // Phone is required
            input.classList.add('input-error');
            showFieldError(input, 'Phone number is required');
            return false;
        }

        let phone = input.value.trim();
        // Remove spaces, dashes, and parentheses
        let cleanPhone = phone.replace(/[\s\-()]/g, '');
        // Remove +92 country code if present
        cleanPhone = cleanPhone.replace(/^\+?92/, '');
        
        // Pakistani phone: 11 digits starting with 0, or 10 digits without 0
        const phoneRegex = /^(0[0-9]{10}|[0-9]{10})$/;
        const isValid = phoneRegex.test(cleanPhone);

        if (!isValid) {
            input.classList.add('input-error');
            showFieldError(input, 'Please enter a valid Pakistani phone number (e.g., 03001234567 or +92 300 1234567)');
            return false;
        } else {
            input.classList.remove('input-error');
            hideFieldError(input);
            return true;
        }
    }

    /**
     * Validate Password
     */
    function validatePassword(input) {
        const isValid = input.value.length >= 6;

        if (input.value && !isValid) {
            input.classList.add('input-error');
            showFieldError(input, 'Password must be at least 6 characters');
            return false;
        } else {
            input.classList.remove('input-error');
            hideFieldError(input);
            return true;
        }
    }

    /**
     * Validate Password Match
     */
    function validatePasswordMatch() {
        if (!passwordInput || !passwordConfirmationInput) return;

        const passwordsMatch = passwordInput.value === passwordConfirmationInput.value;
        const hasValue = passwordConfirmationInput.value.length > 0;

        if (hasValue && !passwordsMatch) {
            passwordConfirmationInput.classList.add('input-error');
            showFieldError(passwordConfirmationInput, 'Passwords do not match');
            return false;
        } else {
            passwordConfirmationInput.classList.remove('input-error');
            hideFieldError(passwordConfirmationInput);
            return true;
        }
    }

    /**
     * Show Field Error
     */
    function showFieldError(input, message) {
        hideFieldError(input);
        
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        errorElement.textContent = message;
        errorElement.style.cssText = `
            color: var(--error-color);
            font-size: 0.75rem;
            margin-top: 0.25rem;
            animation: slideDown 0.3s ease-out;
        `;
        
        input.parentElement.parentElement.appendChild(errorElement);
    }

    /**
     * Hide Field Error
     */
    function hideFieldError(input) {
        const errorElement = input.parentElement.parentElement.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
    }

    /**
     * Form Submission Handler
     */
    function initFormSubmission() {
        if (!registerForm) return;

        registerForm.addEventListener('submit', function(e) {
            // Validate all fields before submission
            
            // Validate all fields
            const isNameValid = nameInput ? validateName(nameInput) : true;
            const isEmailValid = emailInput ? validateEmail(emailInput) : true;
            const isPhoneValid = phoneInput ? validatePhone(phoneInput) : true;
            const isPasswordValid = passwordInput ? validatePassword(passwordInput) : true;
            const isPasswordMatchValid = validatePasswordMatch();
            const isTermsAccepted = termsCheckbox ? termsCheckbox.checked : false;

            if (!isNameValid || !isEmailValid || !isPhoneValid || !isPasswordValid || !isPasswordMatchValid) {
                return false;
            }

            if (!isTermsAccepted) {
                e.preventDefault();
                showFieldError(termsCheckbox, 'You must accept the terms and conditions');
                return false;
            }

            // If validation fails, prevent submission
            if (!isNameValid || !isEmailValid || !isPhoneValid || !isPasswordValid || !isPasswordMatchValid) {
                e.preventDefault();
                return false;
            }

            // Show loading state
            setLoadingState(true);

            // Allow form to submit to backend
            // The form will submit normally, and the backend will handle registration
        });
    }

    /**
     * Set Loading State
     */
    function setLoadingState(loading) {
        if (!signupButton) return;

        if (loading) {
            signupButton.classList.add('loading');
            signupButton.disabled = true;
        } else {
            signupButton.classList.remove('loading');
            signupButton.disabled = false;
        }
    }

    /**
     * Input Animations
     */
    function initInputAnimations() {
        const inputs = document.querySelectorAll('.form-input');
        
        inputs.forEach(input => {
            // Add focus animation
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.01)';
            });

            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    }

    /**
     * Phone Number Formatting (Pakistani format)
     */
    function initPhoneFormatting() {
        if (!phoneInput) return;

        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            // Handle Pakistani phone number formatting
            if (value.length > 0) {
                // If starts with 92 (country code), format as +92 XXX XXXXXXX
                if (value.startsWith('92') && value.length > 2) {
                    if (value.length <= 5) {
                        value = '+92 ' + value.substring(2);
                    } else if (value.length <= 8) {
                        value = '+92 ' + value.substring(2, 5) + ' ' + value.substring(5);
                    } else {
                        value = '+92 ' + value.substring(2, 5) + ' ' + value.substring(5, 8) + ' ' + value.substring(8, 13);
                    }
                }
                // If starts with 0, format as 0XXX-XXXXXXX (11 digits)
                else if (value.startsWith('0') && value.length <= 11) {
                    if (value.length <= 4) {
                        value = value;
                    } else if (value.length <= 7) {
                        value = value.substring(0, 4) + '-' + value.substring(4);
                    } else {
                        value = value.substring(0, 4) + '-' + value.substring(4, 11);
                    }
                }
                // Otherwise, format as 3XX-XXXXXXX (10 digits)
                else if (value.length <= 10) {
                    if (value.length <= 3) {
                        value = value;
                    } else if (value.length <= 6) {
                        value = value.substring(0, 3) + '-' + value.substring(3);
                    } else {
                        value = value.substring(0, 3) + '-' + value.substring(3, 10);
                    }
                }
            }
            
            e.target.value = value;
        });
    }

    /**
     * Handle Form Errors from Server
     */
    const alertError = document.querySelector('.alert-error');
    if (alertError) {
        // Auto-hide error after 5 seconds
        setTimeout(() => {
            alertError.style.opacity = '0';
            setTimeout(() => {
                alertError.style.display = 'none';
            }, 300);
        }, 5000);
    }

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
     * Keyboard Navigation Enhancement
     */
    document.addEventListener('keydown', function(e) {
        // Submit form on Enter key (if not in textarea)
        if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
            if (registerForm && !signupButton.disabled && document.activeElement !== termsCheckbox) {
                // Don't submit if focus is on checkbox (let it toggle instead)
                if (document.activeElement === termsCheckbox) {
                    return;
                }
                registerForm.dispatchEvent(new Event('submit'));
            }
        }
    });

    /**
     * Terms Checkbox Enhancement
     */
    if (termsCheckbox) {
        termsCheckbox.addEventListener('change', function() {
            hideFieldError(this);
        });
    }

})();

