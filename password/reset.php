<?php 
    $title = "Spark X - Forgot Password";
    $css_file = "login.css";
    $base_url = "..";
    $scripts = ["login.js"];
    $logo_is_link = true;
    $logo_link = "../login";
    include('../components/auth_header.php'); 
?>

        <!-- Forgot Password Form Card -->
        <div class="login-card">
            <div class="card-content">
                <!-- Page Header (Centered) -->
                <div class="login-header" style="margin-bottom: 10px">
                    <h1 class="welcome-title">Forgot Password</h1>
                    <p class="welcome-subtitle">Enter your email address and we'll send you a link to reset your password</p>
                </div>
                
                <form id="forgotPasswordForm" class="login-form" method="POST" action="../password/email">
                    <input type="hidden" name="_token" value="piVsFK3xU2lEYOX81jppo4MlnGUCNow8PdUIsub5" autocomplete="off">
                    
                    <!-- Error Messages -->
                    <div class="alert alert-error" id="forgotPasswordError" style="display: none;">
                        <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span id="forgotPasswordErrorText"></span>
                    </div>
                    
                    <!-- Email Field -->
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                            <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email address" required autocomplete="email" autofocus>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="login-button" id="forgotPasswordButton">
                        <span class="button-text">Send Password Reset Link</span>
                        <svg class="button-loader" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" stroke-opacity="0.25"/>
                            <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                        </svg>
                    </button>

                    <!-- Back to Login Link -->
                    <div class="signup-link">
                        <span class="signup-text">Remember your password?</span>
                        <a href="../login" class="signup-link-text">Back to Login</a>
                    </div>
                </form>

            </div>
        </div>

<?php include('../components/auth_footer.php'); ?>
