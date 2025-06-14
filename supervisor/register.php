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
    // Get form data
    $supervisor_name = trim($_POST['supervisor_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone_number = trim($_POST['phone_number'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $institution = trim($_POST['institution'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $years_experience = (int)($_POST['years_experience'] ?? 0);
    
    // Validation
    if (empty($supervisor_name)) $errors[] = 'Full name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
    if (empty($password)) $errors[] = 'Password is required';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';
    if ($password !== $confirm_password) $errors[] = 'Passwords do not match';
    if (empty($phone_number)) $errors[] = 'Phone number is required';
    if (empty($department)) $errors[] = 'Department is required';
    if (empty($institution)) $errors[] = 'Institution is required';
    if ($years_experience < 0) $errors[] = 'Years of experience cannot be negative';
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $con->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Email already registered';
        }
        $stmt->close();
    }
    
    // Insert new supervisor
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Start transaction
        $con->autocommit(FALSE);
        
        try {
            // Insert into users table
            $stmt = $con->prepare("INSERT INTO users (name, email, password, phone, role, status) VALUES (?, ?, ?, ?, 'Supervisor', 'active')");
            $stmt->bind_param("ssss", $supervisor_name, $email, $hashed_password, $phone_number);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to create user account');
            }
            
            $user_id = $con->insert_id;
            $stmt->close();
            
            // Insert into supervisors table
            $stmt = $con->prepare("INSERT INTO supervisors (user_id, department, contact_info) VALUES (?, ?, ?)");
            $contact_info = $department . ' | ' . $phone_number;
            $stmt->bind_param("iss", $user_id, $department, $contact_info);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to create supervisor profile');
            }
            
            $stmt->close();
            
            // Commit transaction
            $con->commit();
            $con->autocommit(TRUE);
            
            $success = 'Registration successful! You can now login to your account.';
            // Clear form data on success
            $_POST = [];
            
        } catch (Exception $e) {
            // Rollback transaction
            $con->rollback();
            $con->autocommit(TRUE);
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Registration - IPT System</title>
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
<body class="bg-gray-50 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="mx-auto h-20 w-20 bg-primary rounded-full flex items-center justify-center">
                <i class="fas fa-chalkboard-teacher text-white text-3xl"></i>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Supervisor Registration
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Create your supervisor account to manage student training
            </p>
        </div>

        <!-- Registration Form -->
        <div class="bg-white py-8 px-6 shadow rounded-lg">
            <form method="POST" action="" class="space-y-6">
                <!-- Personal Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="supervisor_name" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user mr-1 text-primary"></i>Full Name
                            </label>
                            <input type="text" id="supervisor_name" name="supervisor_name" required
                                   value="<?php echo htmlspecialchars($_POST['supervisor_name'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="Dr. John Doe">
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-envelope mr-1 text-primary"></i>Email Address
                            </label>
                            <input type="email" id="email" name="email" required
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="john.doe@university.edu">
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-1 text-primary"></i>Password
                            </label>
                            <input type="password" id="password" name="password" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="Minimum 6 characters">
                        </div>
                        
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-1 text-primary"></i>Confirm Password
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="Re-enter password">
                        </div>
                        
                        <div>
                            <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-phone mr-1 text-primary"></i>Phone Number
                            </label>
                            <input type="text" id="phone_number" name="phone_number" required
                                   value="<?php echo htmlspecialchars($_POST['phone_number'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="Enter phone number starting with +255 (e.g., +255753225961)">
                        </div>
                        
                        <div>
                            <label for="years_experience" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-1 text-primary"></i>Years of Experience
                            </label>
                            <input type="number" id="years_experience" name="years_experience" min="0" max="50"
                                   value="<?php echo htmlspecialchars($_POST['years_experience'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="0">
                        </div>
                    </div>
                </div>

                <!-- Academic Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Academic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="institution" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-university mr-1 text-primary"></i>Institution
                            </label>
                            <input type="text" id="institution" name="institution" required
                                   value="<?php echo htmlspecialchars($_POST['institution'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="University Name">
                        </div>
                        
                        <div>
                            <label for="department" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-building mr-1 text-primary"></i>Department
                            </label>
                            <input type="text" id="department" name="department" required
                                   value="<?php echo htmlspecialchars($_POST['department'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="Computer Science">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="specialization" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-star mr-1 text-primary"></i>Specialization (Optional)
                            </label>
                            <textarea id="specialization" name="specialization" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                      placeholder="Describe your area of specialization, research interests, or expertise..."><?php echo htmlspecialchars($_POST['specialization'] ?? ''); ?></textarea>
                        </div>
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
                                <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
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
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200">
                        <i class="fas fa-user-plus mr-2"></i>
                        Create Supervisor Account
                    </button>
                </div>

                <!-- Additional Links -->
                <div class="flex items-center justify-between">
                    <div class="text-sm">
                        <a href="login.php" class="font-medium text-primary hover:text-secondary">
                            Already have an account? Sign in
                        </a>
                    </div>
                    <div class="text-sm">
                        <a href="../index.php" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-arrow-left mr-1"></i>Back to Main Site
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
