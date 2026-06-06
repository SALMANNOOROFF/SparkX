<?php
// admin/login.php
require_once __DIR__ . '/../config/config.php';

if (admin_is_logged_in()) {
    redirect(ADMIN_URL . '/index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_role'] = $admin['role'];
        
        $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);
        redirect(ADMIN_URL . '/index.php');
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo htmlspecialchars(get_setting($pdo, 'site_name', 'Sparkx')); ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.12) 0%, transparent 45%),
                        radial-gradient(circle at 90% 80%, rgba(147, 51, 234, 0.12) 0%, transparent 45%),
                        #0f172a;
            font-family: 'Outfit', 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow-x: hidden;
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
            position: relative;
        }

        /* Decorative glowing circles behind card */
        .login-wrapper::before {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.25;
            top: -20px;
            left: -20px;
            z-index: -1;
        }

        .login-wrapper::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            background: linear-gradient(135deg, #ec4899, #3b82f6);
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.2;
            bottom: -30px;
            right: -20px;
            z-index: -1;
        }

        .glass-card {
            background: rgba(30, 41, 59, 0.45);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 28px;
            padding: 45px 35px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 100%;
        }

        .logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-img {
            max-height: 80px;
            border-radius: 18px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.12);
            margin-bottom: 20px;
            transition: transform 0.5s ease;
        }

        .logo-img:hover {
            transform: rotate(5deg) scale(1.05);
        }

        .brand-title {
            color: #ffffff;
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-subtitle {
            color: rgba(255, 255, 255, 0.45);
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 6px;
        }

        .form-label-custom {
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 8px;
            display: block;
        }

        .input-wrapper {
            position: relative;
            margin-bottom: 24px;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 1.1rem;
            transition: color 0.3s ease;
            pointer-events: none;
        }

        .form-control-glass {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #ffffff !important;
            border-radius: 16px;
            padding: 15px 16px 15px 48px;
            font-size: 0.95rem;
            width: 100%;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
            font-family: 'Inter', sans-serif;
        }

        .form-control-glass::placeholder {
            color: #475569;
        }

        .form-control-glass:focus {
            background: rgba(15, 23, 42, 0.85);
            border-color: #6366f1;
            outline: none;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2), 
                        inset 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .form-control-glass:focus ~ .input-icon {
            color: #6366f1;
        }

        .btn-brand-glow {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border: none;
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            border-radius: 16px;
            padding: 16px;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 20px -10px rgba(99, 102, 241, 0.5);
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
        }

        .btn-brand-glow:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -10px rgba(99, 102, 241, 0.7);
            background: linear-gradient(135deg, #5d53eb 0%, #8b4df5 100%);
        }

        .btn-brand-glow:active {
            transform: translateY(0);
            box-shadow: 0 10px 15px -10px rgba(99, 102, 241, 0.5);
        }

        .btn-brand-glow::after {
            content: '';
            position: absolute;
            top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            transition: all 0.8s ease;
        }

        .btn-brand-glow:hover::after {
            left: 100%;
        }

        .footer-links {
            text-align: center;
            margin-top: 30px;
        }

        .brand-link-custom {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease, transform 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .brand-link-custom:hover {
            color: #ffffff;
            transform: translateX(-2px);
        }

        .alert-danger-glass {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #fca5a5;
            border-radius: 16px;
            padding: 14px 18px;
            font-size: 0.88rem;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="glass-card">
            <div class="logo-container">
                <img src="<?php echo SITE_URL . '/' . get_setting($pdo, 'site_logo', 'assets/images/logoIcon/logo.png'); ?>" alt="Logo" class="logo-img">
                <h2 class="brand-title"><?php echo htmlspecialchars(get_setting($pdo, 'site_name', 'Sparkx')); ?></h2>
                <p class="brand-subtitle">Admin Control Panel</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-danger-glass">
                    <i class="fas fa-exclamation-circle" style="font-size: 1.1rem; color: #ef4444;"></i> 
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-wrapper">
                    <label class="form-label-custom" for="username">Username / Email</label>
                    <input type="text" id="username" name="username" class="form-control-glass" placeholder="Enter username or email" required autofocus autocomplete="username">
                    <i class="fas fa-user-shield input-icon"></i>
                </div>
                
                <div class="input-wrapper" style="margin-bottom: 30px;">
                    <label class="form-label-custom" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control-glass" placeholder="Enter password" required autocomplete="current-password">
                    <i class="fas fa-key input-icon"></i>
                </div>

                <button type="submit" class="btn-brand-glow">AUTHENTICATE</button>
            </form>
            
            <div class="footer-links">
                <a href="<?php echo SITE_URL; ?>" class="brand-link-custom">
                    <i class="fas fa-arrow-left"></i> Back to Site
                </a>
            </div>
        </div>
    </div>
</body>
</html>
