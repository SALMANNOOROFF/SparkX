<?php
// login.php (Root directory)
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    
    if ($user = mysqli_fetch_assoc($result)) {
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Update last login
            $user_id = $user['id'];
            mysqli_query($conn, "UPDATE users SET last_login = NOW() WHERE id = $user_id");

            // Redirect to dashboard
            header("Location: user/dashboard/index.php");
            exit();
        } else {
            header("Location: auth/login.php?error=Invalid password");
            exit();
        }
    } else {
        header("Location: auth/login.php?error=User not found");
        exit();
    }
} else {
    // If not POST, show the login UI
    $base_url = "."; // Path fix for root
    include 'auth/login.php';
}
?>
