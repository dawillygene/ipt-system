<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Include database connection
require_once 'db.php';

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error_message = 'All required fields (Name, Email, Password, Role) must be filled.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters long.';
    } elseif (!in_array($role, ['Supervisor', 'Invigilator'])) {
        $error_message = 'Please select a valid role.';
    } else {
        // Check if email already exists
        $check_email_stmt = $con->prepare("SELECT id FROM users WHERE email = ?");
        $check_email_stmt->bind_param("s", $email);
        $check_email_stmt->execute();
        $email_result = $check_email_stmt->get_result();
        
        if ($email_result->num_rows > 0) {
            $error_message = 'Email address already exists. Please use a different email.';
        } else {
            // Handle profile photo upload
            $profile_photo = null;
            $upload_dir = '../uploads/profiles/';
            
            // Create upload directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['profile_photo']['tmp_name'];
                $file_name = $_FILES['profile_photo']['name'];
                $file_size = $_FILES['profile_photo']['size'];
                $file_type = $_FILES['profile_photo']['type'];
                
                // Validate file type
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!in_array($file_type, $allowed_types)) {
                    $error_message = 'Invalid file type. Only JPG, JPEG, PNG and GIF files are allowed.';
                } elseif ($file_size > 10 * 1024 * 1024) { // 10MB limit
                    $error_message = 'File size too large. Maximum size is 10MB.';
                } else {
                    // Generate unique filename
                    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                    $new_filename = 'profile_' . time() . '_' . uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        $profile_photo = 'uploads/profiles/' . $new_filename;
                    } else {
                        $error_message = 'Failed to upload profile photo.';
                    }
                }
            }
            
            // If no errors, proceed with database insertion
            if (empty($error_message)) {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user into database
                $insert_stmt = $con->prepare("INSERT INTO users (name, email, password, role, phone, address, profile_photo, created_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'active')");
                $insert_stmt->bind_param("sssssss", $name, $email, $hashed_password, $role, $phone, $address, $profile_photo);
                
                if ($insert_stmt->execute()) {
                    $success_message = 'User has been successfully registered!';
                    
                    // Redirect with success message
                    header('Location: admin_users.php?msg=' . urlencode('User added successfully') . '&type=success');
                    exit;
                } else {
                    $error_message = 'Database error: Failed to register user. ' . $con->error;
                    
                    // Delete uploaded file if database insertion failed
                    if ($profile_photo && file_exists('../' . $profile_photo)) {
                        unlink('../' . $profile_photo);
                    }
                }
                
                $insert_stmt->close();
            }
        }
        
        $check_email_stmt->close();
    }
}

// If there's an error, redirect back to add-user.php with error message
if (!empty($error_message)) {
    header('Location: add-user.php?msg=' . urlencode($error_message) . '&type=error');
    exit;
}

// If accessed directly without POST data, redirect to add-user.php
header('Location: add-user.php');
exit;
?>