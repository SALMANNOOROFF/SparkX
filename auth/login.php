<?php 
    $title = "Login - SparkX";
    $css_file = "login.css";
    $base_url = $base_url ?? "..";
    $scripts = ["login.js"];
    include($base_url . '/components/auth_header.php'); 
?>

        <div class="login-header">
            <h1 class="welcome-title">Welcome Back</h1>
            <p class="welcome-subtitle">Enter your credentials to access your account</p>
        </div>

        <div class="login-card">
            <div class="card-content">
                <form id="loginForm" class="login-form" action="<?php echo $base_url; ?>/login" method="POST">
                    
                    <!-- Email Field -->
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email" required autofocus>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required>
                            <button type="button" class="password-toggle" id="passwordToggle" aria-label="Toggle password visibility">
                                <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px;">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="form-options">
                        <label class="checkbox-wrapper">
                            <input type="checkbox" id="remember" name="remember" class="checkbox-input">
                            <span class="checkbox-label">Remember me</span>
                        </label>
                        <a href="../password/reset" class="forgot-password-link">Forgot password?</a>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" id="loginButton" class="login-button">
                        <span class="button-text">Login</span>
                        <div class="button-loader" style="display: none;"><i class="fas fa-spinner fa-spin"></i></div>
                    </button>

                    <!-- Signup Link -->
                    <div class="signup-link">
                        <span class="signup-text">Don't have an account?</span>
                        <a href="<?php echo $base_url; ?>/register" class="signup-link-text">Sign Up</a>
                    </div>

                </form>
            </div>
        </div>
        
        <!-- Support Chat target -->
        <div class="chat-widget">
            <div class="chat-icon">
                <i class="fas fa-comment-dots"></i>
            </div>
            <span class="chat-text">Need Help?</span>
        </div>

<?php include('../components/auth_footer.php'); ?>
