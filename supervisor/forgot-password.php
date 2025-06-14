<?php
session_start();
require_once 'includes/supervisor_db.php';

$error = '';
$success = '';
$step = 'email'; // email, verify, reset

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['send_reset'])) {
        $email = trim($_POST['email']);
        
        // Validation
        if (empty($email)) {
            $error = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Check if supervisor exists
            $stmt = $con->prepare("SELECT supervisor_id, supervisor_name FROM supervisors WHERE email = ? AND status = 'active'");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $supervisor = $stmt->get_result()->fetch_assoc();
            
            if ($supervisor) {
                // Generate reset token
                $reset_token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store reset token (you could create a password_resets table for this)
                // For now, we'll store it in session for demonstration
                $_SESSION['reset_token'] = $reset_token;
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_expires'] = $expires_at;
                $_SESSION['supervisor_id_reset'] = $supervisor['supervisor_id'];
                
                // In production, you would send this via email
                $success = 'Password reset instructions have been sent to your email address. For demo purposes, your reset code is: ' . $reset_token;
                $step = 'verify';
            } else {
                $error = 'No active supervisor account found with that email address.';
            }
        }
    } elseif (isset($_POST['verify_token'])) {
        $token = trim($_POST['token']);
        
        if (empty($token)) {
            $error = 'Reset token is required.';
        } elseif (!isset($_SESSION['reset_token']) || $token !== $_SESSION['reset_token']) {
            $error = 'Invalid reset token.';
        } elseif (strtotime($_SESSION['reset_expires']) < time()) {
            $error = 'Reset token has expired. Please request a new one.';
            unset($_SESSION['reset_token'], $_SESSION['reset_email'], $_SESSION['reset_expires'], $_SESSION['supervisor_id_reset']);
        } else {
            $step = 'reset';
        }
    } elseif (isset($_POST['reset_password'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validation
        if (empty($new_password)) {
            $error = 'New password is required.';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif (!isset($_SESSION['supervisor_id_reset'])) {
            $error = 'Invalid reset session. Please start over.';
        } else {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $con->prepare("UPDATE supervisors SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE supervisor_id = ?");
            $stmt->bind_param("si", $hashed_password, $_SESSION['supervisor_id_reset']);
            
            if ($stmt->execute()) {
                $success = 'Password reset successfully! You can now login with your new password.';
                // Clear reset session
                unset($_SESSION['reset_token'], $_SESSION['reset_email'], $_SESSION['reset_expires'], $_SESSION['supervisor_id_reset']);
                $step = 'complete';
            } else {
                $error = 'Failed to update password. Please try again.';
            }
        }
    }
}

// Determine current step
if (isset($_SESSION['reset_token']) && $step === 'email') {
    if (isset($_POST['verify_token']) || isset($_GET['step']) && $_GET['step'] === 'verify') {
        $step = 'verify';
    } elseif (isset($_POST['reset_password']) || isset($_GET['step']) && $_GET['step'] === 'reset') {
        $step = 'reset';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Supervisor Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'supervisor-primary': '#07442d',
                        'supervisor-secondary': '#206f56',
                        'supervisor-accent': '#0f7b5a',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-supervisor-primary rounded-full flex items-center justify-center">
                    <i class="fas fa-lock text-white text-2xl"></i>
                </div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Reset Password
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Supervisor account password recovery
                </p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <!-- Step 1: Enter Email -->
            <?php if ($step === 'email'): ?>
                <form class="mt-8 space-y-6" method="POST">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input id="email" name="email" type="email" required 
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-supervisor-primary focus:border-supervisor-primary focus:z-10 sm:text-sm" 
                               placeholder="Enter your email address"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>

                    <div>
                        <button type="submit" name="send_reset"
                                class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-supervisor-primary hover:bg-supervisor-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-supervisor-primary transition duration-200">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <i class="fas fa-paper-plane text-supervisor-accent group-hover:text-supervisor-primary"></i>
                            </span>
                            Send Reset Instructions
                        </button>
                    </div>

                    <div class="text-center">
                        <p class="text-sm text-gray-600">
                            Remember your password? 
                            <a href="login.php" class="font-medium text-supervisor-primary hover:text-supervisor-secondary">
                                Sign in here
                            </a>
                        </p>
                    </div>
                </form>
            <?php endif; ?>

            <!-- Step 2: Verify Token -->
            <?php if ($step === 'verify'): ?>
                <form class="mt-8 space-y-6" method="POST">
                    <div>
                        <label for="token" class="block text-sm font-medium text-gray-700">Reset Code</label>
                        <input id="token" name="token" type="text" required 
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-supervisor-primary focus:border-supervisor-primary focus:z-10 sm:text-sm" 
                               placeholder="Enter the reset code sent to your email">
                        <p class="mt-2 text-sm text-gray-600">
                            Check your email for the reset code (valid for 1 hour)
                        </p>
                    </div>

                    <div>
                        <button type="submit" name="verify_token"
                                class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-supervisor-primary hover:bg-supervisor-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-supervisor-primary transition duration-200">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <i class="fas fa-check text-supervisor-accent group-hover:text-supervisor-primary"></i>
                            </span>
                            Verify Code
                        </button>
                    </div>

                    <div class="text-center">
                        <p class="text-sm text-gray-600">
                            Didn't receive the code? 
                            <a href="forgot-password.php" class="font-medium text-supervisor-primary hover:text-supervisor-secondary">
                                Try again
                            </a>
                        </p>
                    </div>
                </form>
            <?php endif; ?>

            <!-- Step 3: Reset Password -->
            <?php if ($step === 'reset'): ?>
                <form class="mt-8 space-y-6" method="POST">
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                        <input id="new_password" name="new_password" type="password" required 
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-supervisor-primary focus:border-supervisor-primary focus:z-10 sm:text-sm" 
                               placeholder="Enter new password (min. 6 characters)">
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                        <input id="confirm_password" name="confirm_password" type="password" required 
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-supervisor-primary focus:border-supervisor-primary focus:z-10 sm:text-sm" 
                               placeholder="Confirm your new password">
                    </div>

                    <div>
                        <button type="submit" name="reset_password"
                                class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-supervisor-primary hover:bg-supervisor-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-supervisor-primary transition duration-200">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <i class="fas fa-key text-supervisor-accent group-hover:text-supervisor-primary"></i>
                            </span>
                            Reset Password
                        </button>
                    </div>
                </form>
            <?php endif; ?>

            <!-- Step 4: Complete -->
            <?php if ($step === 'complete'): ?>
                <div class="text-center">
                    <div class="mx-auto h-16 w-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-check text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Password Reset Complete</h3>
                    <p class="text-gray-600 mb-6">Your password has been successfully updated.</p>
                    
                    <a href="login.php" 
                       class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-supervisor-primary hover:bg-supervisor-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-supervisor-primary transition duration-200">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Sign In Now
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Password strength indicator
        document.addEventListener('DOMContentLoaded', function() {
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (newPassword) {
                newPassword.addEventListener('input', function() {
                    const password = this.value;
                    
                    if (password.length < 6) {
                        this.classList.remove('border-green-300');
                        this.classList.add('border-red-300');
                    } else {
                        this.classList.remove('border-red-300');
                        this.classList.add('border-green-300');
                    }
                });
            }
            
            if (confirmPassword) {
                confirmPassword.addEventListener('input', function() {
                    const password = newPassword.value;
                    const confirm = this.value;
                    
                    if (password === confirm && password.length >= 6) {
                        this.classList.remove('border-red-300');
                        this.classList.add('border-green-300');
                    } else {
                        this.classList.remove('border-green-300');
                        this.classList.add('border-red-300');
                    }
                });
            }
        });
    </script>
</body>
</html>
