<?php
session_start();
require_once 'db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($name)) $errors[] = 'Full name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
    if (empty($student_id)) $errors[] = 'Student ID is required';
    if (empty($password)) $errors[] = 'Password is required';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';
    if ($password !== $confirm_password) $errors[] = 'Passwords do not match';
    
    // Check if email already exists in students_auth table
    if (empty($errors)) {
        $stmt = $con->prepare("SELECT auth_id FROM students_auth WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Email already registered';
        }
        $stmt->close();
    }
    
    // Check if student ID already exists
    if (empty($errors)) {
        $stmt = $con->prepare("SELECT student_id FROM students WHERE reg_number = ?");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Student ID already registered';
        }
        $stmt->close();
    }
    
    // If no errors, create the user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Start transaction
        $con->begin_transaction();
        
        try {
            // Insert into students table with basic required fields
            $stmt = $con->prepare("INSERT INTO students (full_name, reg_number, email) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $student_id, $email);
            $stmt->execute();
            $new_student_id = $con->insert_id;
            $stmt->close();
            
            // Insert into students_auth table for authentication
            $stmt = $con->prepare("INSERT INTO students_auth (student_id, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $new_student_id, $email, $hashed_password);
            $stmt->execute();
            $stmt->close();
            
            $con->commit();
            $success = 'Registration successful! You can now login.';
            
        } catch (Exception $e) {
            $con->rollback();
            $errors[] = 'Registration failed. Please try again. Error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - IPT System</title>
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
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-primary text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-xl font-bold">
                            <i class="fas fa-graduation-cap mr-2"></i>IPT System
                        </h1>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="student_login.php" class="px-3 py-2 rounded-md text-sm font-medium bg-secondary hover:bg-accent transition-colors">
                        <i class="fas fa-sign-in-alt mr-1"></i>Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Registration Form -->
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-primary bg-opacity-10">
                    <i class="fas fa-user-graduate text-primary text-xl"></i>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Student Registration
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Create your account to access the IPT System
                </p>
            </div>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Registration Failed</h3>
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

            <!-- Registration Form -->
            <form class="mt-8 space-y-6" method="POST" action="">
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-user mr-1"></i>Full Name
                        </label>
                        <input id="name" name="name" type="text" required 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                               class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                               placeholder="Enter your full name">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-envelope mr-1"></i>Email Address
                        </label>
                        <input id="email" name="email" type="email" required 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                               placeholder="Enter your email address">
                    </div>

                    <div>
                        <label for="student_id" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-id-card mr-1"></i>Student ID
                        </label>
                        <input id="student_id" name="student_id" type="text" required 
                               value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>"
                               class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                               placeholder="Enter your student ID">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-lock mr-1"></i>Password
                        </label>
                        <input id="password" name="password" type="password" required 
                               class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                               placeholder="Enter your password">
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-lock mr-1"></i>Confirm Password
                        </label>
                        <input id="confirm_password" name="confirm_password" type="password" required 
                               class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                               placeholder="Confirm your password">
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-user-plus group-hover:text-white"></i>
                        </span>
                        Create Account
                    </button>
                </div>

                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Already have an account? 
                        <a href="login.php" class="font-medium text-primary hover:text-secondary">
                            Sign in here
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
