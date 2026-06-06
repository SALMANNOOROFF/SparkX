<?php
// register.php (Root directory)
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $referral_by = isset($_POST['referral_code']) ? mysqli_real_escape_string($conn, $_POST['referral_code']) : "";

    // Validation
    $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check_email) > 0) {
        die("Error: Email already exists!");
    }

    // Role Logic: Check if first user
    $check_users = mysqli_query($conn, "SELECT id FROM users LIMIT 1");
    $role = (mysqli_num_rows($check_users) == 0) ? 'admin' : 'user';

    // Generate Unique Referral Code for new user
    $my_referral_code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

    // Hash Password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert User
    $insert_user = "INSERT INTO users (name, email, phone, password, referral_code, referred_by, role) 
                    VALUES ('$name', '$email', '$phone', '$hashed_password', '$my_referral_code', '$referral_by', '$role')";

    if (mysqli_query($conn, $insert_user)) {
        $user_id = mysqli_insert_id($conn);

        // Create Wallet for the user
        mysqli_query($conn, "INSERT INTO wallets (user_id) VALUES ('$user_id')");

        // Populate 5 levels of referrals in referrals table
        if (!empty($referral_by)) {
            $current_referred_by = $referral_by;
            $current_referee_id = $user_id;
            $level = 1;
            
            while (!empty($current_referred_by) && $level <= 5) {
                $parent_q = mysqli_query($conn, "SELECT id, referral_code, referred_by FROM users WHERE referral_code = '$current_referred_by'");
                if ($parent_q && mysqli_num_rows($parent_q) > 0) {
                    $parent = mysqli_fetch_assoc($parent_q);
                    $parent_id = $parent['id'];
                    
                    mysqli_query($conn, "INSERT INTO referrals (referrer_id, referee_id, level) VALUES ('$parent_id', '$current_referee_id', '$level')");
                    
                    $current_referred_by = $parent['referred_by'];
                    $level++;
                } else {
                    break;
                }
            }
        }

        // Success: Redirect to login
        header("Location: auth/login.php?success=Account created successfully. Please login.");
        exit();
    } else {
        die("Error: " . mysqli_error($conn));
    }
} else {
    // If not POST, show the register UI
    $base_url = "."; // Path fix for root
    include 'auth/register.php';
}
?>
