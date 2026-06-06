<?php
// user/dashboard/upload_avatar.php
require_once '../../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error occurred.']);
        exit();
    }
    
    $file = $_FILES['avatar'];
    
    // Validate file size (5MB limit)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File size is too large (maximum limit is 5MB).']);
        exit();
    }
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_mime = mime_content_type($file['tmp_name']);
    if (!in_array($file_mime, $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.']);
        exit();
    }
    
    // Get file extension
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (empty($ext)) {
        $ext = 'jpg';
    }
    
    // Define upload directory
    $upload_dir = '../../uploads/avatars/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Unique file name to prevent browser caching issues
    $file_name = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
    $target_path = $upload_dir . $file_name;
    $db_path = 'uploads/avatars/' . $file_name;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // Delete old avatar file if it exists
        $old_avatar_q = mysqli_query($conn, "SELECT profile_image FROM users WHERE id = '$user_id'");
        $old_avatar = mysqli_fetch_assoc($old_avatar_q)['profile_image'] ?? null;
        if ($old_avatar && file_exists('../../' . $old_avatar)) {
            @unlink('../../' . $old_avatar);
        }
        
        // Update database
        $update_q = mysqli_query($conn, "UPDATE users SET profile_image = '$db_path' WHERE id = '$user_id'");
        
        if ($update_q) {
            echo json_encode([
                'success' => true, 
                'message' => 'Profile picture updated successfully!', 
                'avatar_url' => '../../' . $db_path
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save profile picture in database.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload file to target directory.']);
    }
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}
