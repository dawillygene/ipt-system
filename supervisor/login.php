<?php
session_start();
require_once 'includes/supervisor_db.php';

$errors = [];
$success = '';

// Redirect if already logged in
if (isset($_SESSION['supervisor_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($password)) $errors[] = 'Password is required';
    
    if (empty($errors)) {
        // Check supervisor credentials in users table
        $stmt = $con->prepare("SELECT id, name, email, password, status FROM users WHERE email = ? AND role = 'Supervisor'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $supervisor = $result->fetch_assoc();
            
            if (password_verify($password, $supervisor['password'])) {
                if ($supervisor['status'] !== 'inactive') {
                    // Start session
                    $_SESSION['supervisor_id'] = $supervisor['id'];
                    $_SESSION['supervisor_name'] = $supervisor['name'];
                    $_SESSION['supervisor_email'] = $supervisor['email'];
                    $_SESSION['user_type'] = 'supervisor';
                    
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $errors[] = 'Your account is not active. Please contact the administrator.';
                }
            } else {
                $errors[] = 'Invalid email or password';
            }
        } else {
            $errors[] = 'Invalid email or password';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Login - IPT System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#07442d',
                        'secondary': '#206f56',
                        'accent': '#0f7b5a',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <div class="mx-auto h-20 w-20 bg-primary rounded-full flex items-center justify-center">
                <i class="fas fa-chalkboard-teacher text-white text-3xl"></i>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Supervisor Portal
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Sign in to your supervisor account
            </p>
        </div>

        <!-- Login Form -->
        <form class="mt-8 space-y-6" method="POST" action="">
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-primary"></i>Email Address
                    </label>
                    <input id="email" name="email" type="email" required
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                           placeholder="Enter your email address">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-primary"></i>Password
                    </label>
                    <input id="password" name="password" type="password" required
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                           placeholder="Enter your password">
                </div>
            </div>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Login failed</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800"><?php echo htmlspecialchars($success); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Submit Button -->
            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-sign-in-alt text-secondary group-hover:text-primary"></i>
                    </span>
                    Sign in to Supervisor Portal
                </button>
            </div>

            <!-- Additional Links -->
            <div class="flex items-center justify-between">
                <div class="text-sm">
                    <a href="register.php" class="font-medium text-primary hover:text-secondary">
                        Need an account? Register here
                    </a>
                </div>
                <div class="text-sm">
                    <a href="forgot-password.php" class="font-medium text-primary hover:text-secondary">
                        Forgot password?
                    </a>
                </div>
            </div>

            <!-- Back to Main -->
            <div class="text-center">
                <a href="../index.php" class="text-sm text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left mr-1"></i>Back to Main Site
                </a>
            </div>
        </form>
    </div>
</body>
</html>
