<?php
include('db.php');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Handle success and error messages from save_user.php
$message = '';
$message_type = '';
if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['msg']);
    $message_type = htmlspecialchars($_GET['type']);
}

// Function to fetch user data by user ID
function getUserData($userId, $con) {
    $userDataSql = "SELECT * FROM users WHERE id = '$userId'";
    $userDataResult = mysqli_query($con, $userDataSql);
    return mysqli_fetch_assoc($userDataResult);
}

// If an action (approve or reject) is submitted for a request, update the request status
if (isset($_POST['action']) && isset($_POST['request_id'])) {
    $action = $_POST['action'];
    $request_id = $_POST['request_id'];
    $status = $action === 'approve' ? 'approved' : 'rejected';

    $update_sql = "UPDATE user_requests SET status = '$status' WHERE id = '$request_id'";
    mysqli_query($con, $update_sql);
}

// Retrieve user requests from the database
$applications_sql = "SELECT applications.* FROM applications";
$applications_result = mysqli_query($con, $applications_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Add User</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'admin-primary': '#065f46',
                        'admin-secondary': '#047857',
                        'admin-accent': '#059669',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans">
    <div class="min-h-screen">
        <!-- Include existing sidebar -->
        <?php include('./includes/sidebar.php'); ?>

        <!-- Main Content Area -->
        <div class="lg:ml-64 min-h-screen">
            <!-- Top Header Space -->
            <div class="h-16"></div>
            
            <!-- Main Content -->
            <main class="p-4 lg:p-8">
                <!-- Page Header -->
                <div class="mb-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">Add New User</h1>
                            <p class="text-gray-600">Create a new user account for the IPT system</p>
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <a href="./admin_students.php" 
                               class="inline-flex items-center px-4 py-2 bg-admin-primary text-white rounded-lg hover:bg-admin-secondary transition-colors duration-200 shadow-md">
                                <i class="fas fa-users mr-2"></i>
                                View All Users
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Success or Error Message -->
                <?php if ($message): ?>
                    <div class="mb-4 p-4 rounded-lg 
                                <?php echo $message_type === 'success' ? 'bg-green-50 border-l-4 border-green-400 text-green-700' : 'bg-red-50 border-l-4 border-red-400 text-red-700'; ?>">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle fa-lg"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm"><?php echo $message; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Add User Form -->
                <div class="max-w-4xl mx-auto">
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <!-- Form Header -->
                        <div class="bg-gradient-to-r from-admin-primary to-admin-secondary px-6 py-4">
                            <h2 class="text-xl font-semibold text-white flex items-center">
                                <i class="fas fa-user-plus mr-3"></i>
                                User Registration Form
                            </h2>
                        </div>

                        <!-- Form Content -->
                        <div class="p-6">
                            <form action="save_user.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                                <!-- Personal Information Section -->
                                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <i class="fas fa-user-circle mr-2 text-admin-primary"></i>
                                        Personal Information
                                    </h3>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Full Name -->
                                        <div class="md:col-span-2">
                                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                                Full Name <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-user text-gray-400"></i>
                                                </div>
                                                <input type="text" 
                                                       name="name" 
                                                       id="name"
                                                       class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200" 
                                                       placeholder="Enter full name"
                                                       required>
                                            </div>
                                        </div>

                                        <!-- Email -->
                                        <div>
                                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                                Email Address <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-envelope text-gray-400"></i>
                                                </div>
                                                <input type="email" 
                                                       name="email" 
                                                       id="email"
                                                       class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200" 
                                                       placeholder="Enter email address"
                                                       required>
                                            </div>
                                        </div>

                                        <!-- Phone -->
                                        <div>
                                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                                Phone Number
                                            </label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-phone text-gray-400"></i>
                                                </div>
                                                <input type="text" 
                                                       name="phone" 
                                                       id="phone"
                                                       class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200" 
                                                       placeholder="Enter phone number starting with +255 (e.g., +255753225961)">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Account Information Section -->
                                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <i class="fas fa-key mr-2 text-admin-primary"></i>
                                        Account Information
                                    </h3>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Password -->
                                        <div>
                                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                                Password <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-lock text-gray-400"></i>
                                                </div>
                                                <input type="password" 
                                                       name="password" 
                                                       id="password"
                                                       class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200" 
                                                       placeholder="Enter password"
                                                       required>
                                            </div>
                                        </div>

                                        <!-- Role -->
                                        <div>
                                            <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                                                User Role <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-user-tag text-gray-400"></i>
                                                </div>
                                                <select name="role" 
                                                        id="role"
                                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200 appearance-none bg-white" 
                                                        required>
                                                    <option value="">-- Select Role --</option>
                                                    <option value="Supervisor">Supervisor</option>
                                                    <option value="Invigilator">Invigilator</option>
                                                </select>
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Information Section -->
                                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <i class="fas fa-info-circle mr-2 text-admin-primary"></i>
                                        Additional Information
                                    </h3>
                                    
                                    <div class="space-y-6">
                                        <!-- Address -->
                                        <div>
                                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                                Address
                                            </label>
                                            <div class="relative">
                                                <div class="absolute top-3 left-3 pointer-events-none">
                                                    <i class="fas fa-map-marker-alt text-gray-400"></i>
                                                </div>
                                                <textarea name="address" 
                                                          id="address"
                                                          rows="3"
                                                          class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200 resize-none" 
                                                          placeholder="Enter full address"></textarea>
                                            </div>
                                        </div>

                                        <!-- Profile Photo -->
                                        <div>
                                            <label for="profile_photo" class="block text-sm font-medium text-gray-700 mb-2">
                                                Profile Photo
                                            </label>
                                            <div class="flex items-center space-x-4">
                                                <div class="w-20 h-20 bg-gray-200 rounded-full flex items-center justify-center border-2 border-dashed border-gray-300">
                                                    <i class="fas fa-camera text-gray-400 text-xl"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <input type="file" 
                                                           name="profile_photo" 
                                                           id="profile_photo"
                                                           accept="image/*"
                                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-admin-primary file:text-white hover:file:bg-admin-secondary transition-colors duration-200 cursor-pointer">
                                                    <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF up to 10MB</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pt-6 border-t border-gray-200">
                                    <div class="flex items-center space-x-4 mb-4 sm:mb-0">
                                        <button type="submit" 
                                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-admin-primary to-admin-secondary text-white font-medium rounded-lg hover:from-admin-secondary hover:to-admin-accent transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                                            <i class="fas fa-user-plus mr-2"></i>
                                            Register User
                                        </button>
                                        
                                        <button type="reset" 
                                                class="inline-flex items-center px-6 py-3 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition-colors duration-200">
                                            <i class="fas fa-undo mr-2"></i>
                                            Reset Form
                                        </button>
                                    </div>
                                    
                                    <div class="text-sm text-gray-500">
                                        <span class="text-red-500">*</span> Required fields
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Enhanced JavaScript -->
    <script>
        // Prevent back navigation
        window.history.forward();
        function noBack() {
            window.history.forward();
        }
        setTimeout("noBack()", 0);
        window.onunload = function() {null};

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Form enhancements
        document.addEventListener('DOMContentLoaded', function() {
            // Password strength indicator
            const passwordInput = document.getElementById('password');
            const strengthIndicator = document.createElement('div');
            strengthIndicator.className = 'mt-2 text-xs';
            passwordInput.parentNode.appendChild(strengthIndicator);

            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                let feedback = '';

                if (password.length >= 8) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;

                switch(strength) {
                    case 0:
                    case 1:
                        feedback = '<span class="text-red-500">Weak password</span>';
                        break;
                    case 2:
                        feedback = '<span class="text-yellow-500">Fair password</span>';
                        break;
                    case 3:
                        feedback = '<span class="text-blue-500">Good password</span>';
                        break;
                    case 4:
                        feedback = '<span class="text-green-500">Strong password</span>';
                        break;
                }
                strengthIndicator.innerHTML = feedback;
            });

            // File upload preview
            const fileInput = document.getElementById('profile_photo');
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.querySelector('.w-20.h-20');
                        preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover rounded-full">`;
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Form validation
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('border-red-500');
                        field.classList.remove('border-gray-300');
                    } else {
                        field.classList.remove('border-red-500');
                        field.classList.add('border-gray-300');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                }
            });
        });
    </script>

    <style>
        /* Custom animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Custom focus styles */
        input:focus, select:focus, textarea:focus {
            box-shadow: 0 0 0 3px rgba(6, 95, 70, 0.1);
        }

        /* File input styling */
        input[type="file"]::-webkit-file-upload-button {
            transition: all 0.2s ease;
        }

        /* Smooth transitions */
        * {
            transition-property: border-color, box-shadow, transform;
            transition-duration: 0.2s;
            transition-timing-function: ease-in-out;
        }
    </style>
</body>
</html>
