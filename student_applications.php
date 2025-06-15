<?php
session_start();
require_once 'db.php';

// Check if user is logged in as student
if (!isset($_SESSION['student_id'])) {
    header('Location: student_login.php');
    exit;
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'] ?? 'Student';
$success = '';
$errors = [];

// Get student information
$stmt = $con->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Set variables required by the reusable sidebar
$student_data = $student;

// Create applications table if it doesn't exist
$con->query("CREATE TABLE IF NOT EXISTS applications (
    application_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    company_location VARCHAR(255) NOT NULL,
    position_title VARCHAR(255) NOT NULL,
    training_duration INT(11) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    training_area VARCHAR(255) NOT NULL,
    skills_to_acquire TEXT,
    motivation_letter TEXT NOT NULL,
    preferred_company1 VARCHAR(255),
    preferred_company2 VARCHAR(255),
    preferred_company3 VARCHAR(255),
    status ENUM('draft', 'submitted', 'approved', 'rejected', 'in_review') DEFAULT 'draft',
    submitted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
)");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Get form data
    $company_name = trim($_POST['company_name'] ?? '');
    $company_location = trim($_POST['company_location'] ?? '');
    $position_title = trim($_POST['position_title'] ?? '');
    $training_duration = (int)($_POST['training_duration'] ?? 0);
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $training_area = trim($_POST['training_area'] ?? '');
    $skills_to_acquire = trim($_POST['skills_to_acquire'] ?? '');
    $motivation_letter = trim($_POST['motivation_letter'] ?? '');
    $preferred_company1 = trim($_POST['preferred_company1'] ?? '');
    $preferred_company2 = trim($_POST['preferred_company2'] ?? '');
    $preferred_company3 = trim($_POST['preferred_company3'] ?? '');

    // Validation for submitted applications
    if ($action === 'submit') {
        if (empty($company_name)) $errors[] = 'Company name is required';
        if (empty($company_location)) $errors[] = 'Company location is required';
        if (empty($position_title)) $errors[] = 'Position title is required';
        if ($training_duration < 1) $errors[] = 'Training duration must be at least 1 week';
        if (empty($start_date)) $errors[] = 'Start date is required';
        if (empty($end_date)) $errors[] = 'End date is required';
        if (empty($training_area)) $errors[] = 'Training area is required';
        if (empty($motivation_letter)) $errors[] = 'Motivation letter is required';
        if (strlen($motivation_letter) < 100) $errors[] = 'Motivation letter must be at least 100 characters';

        // Date validation
        if ($start_date && $end_date) {
            $start = new DateTime($start_date);
            $end = new DateTime($end_date);
            $today = new DateTime();
            
            if ($start <= $today) {
                $errors[] = 'Start date must be in the future';
            }
            if ($end <= $start) {
                $errors[] = 'End date must be after start date';
            }
        }
    }

    // Save application
    if (empty($errors) || $action === 'save_draft') {
        $status = ($action === 'submit') ? 'submitted' : 'draft';
        $submitted_at = ($action === 'submit') ? date('Y-m-d H:i:s') : null;

        // Check if application already exists for this student
        $check_stmt = $con->prepare("SELECT application_id FROM applications WHERE student_id = ?");
        $check_stmt->bind_param("i", $student_id);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();

        if ($existing) {
            // Update existing application
            $update_stmt = $con->prepare("UPDATE applications SET company_name = ?, company_location = ?, position_title = ?, training_duration = ?, start_date = ?, end_date = ?, training_area = ?, skills_to_acquire = ?, motivation_letter = ?, preferred_company1 = ?, preferred_company2 = ?, preferred_company3 = ?, status = ?, submitted_at = ? WHERE student_id = ?");
            $update_stmt->bind_param("sssississsssssi", $company_name, $company_location, $position_title, $training_duration, $start_date, $end_date, $training_area, $skills_to_acquire, $motivation_letter, $preferred_company1, $preferred_company2, $preferred_company3, $status, $submitted_at, $student_id);
            
            if ($update_stmt->execute()) {
                $success = ($action === 'submit') ? 'Application submitted successfully!' : 'Application saved as draft';
            } else {
                $errors[] = 'Failed to save application. Please try again.';
            }
            $update_stmt->close();
        } else {
            // Create new application
            $insert_stmt = $con->prepare("INSERT INTO applications (student_id, company_name, company_location, position_title, training_duration, start_date, end_date, training_area, skills_to_acquire, motivation_letter, preferred_company1, preferred_company2, preferred_company3, status, submitted_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("isssisssssssss", $student_id, $company_name, $company_location, $position_title, $training_duration, $start_date, $end_date, $training_area, $skills_to_acquire, $motivation_letter, $preferred_company1, $preferred_company2, $preferred_company3, $status, $submitted_at);
            
            if ($insert_stmt->execute()) {
                $success = ($action === 'submit') ? 'Application submitted successfully!' : 'Application saved as draft';
            } else {
                $errors[] = 'Failed to save application. Please try again.';
            }
            $insert_stmt->close();
        }
    }
}

// Get existing application data based on current table structure
$existing_application = null;
$stmt = $con->prepare("SELECT * FROM applications WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $existing_application = $result->fetch_assoc();
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Application - IPT System</title>
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
    <style>
        /* Enhanced responsive design with proper layout */
        .main-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar-container {
            flex-shrink: 0;
            width: 256px; /* w-64 in Tailwind */
        }
        
        .content-container {
            flex: 1;
            min-width: 0; /* Prevents flex child from overflowing */
            display: flex;
            flex-direction: column;
        }
        
        /* Mobile responsive adjustments */
        @media (max-width: 768px) {
            .sidebar-container {
                display: none; /* Hide desktop sidebar on mobile */
            }
            .content-container {
                width: 100% !important;
                margin-left: 0 !important;
            }
            .main-content {
                padding: 0.75rem;
                margin-left: 0 !important;
                width: 100% !important;
            }
            .navbar-brand {
                font-size: 0.9rem;
            }
            /* Compact navbar */
            nav .h-16 {
                height: 2.75rem;
            }
        }

        @media (max-width: 640px) {
            .main-content {
                padding: 0.5rem;
            }
            /* Ultra compact navbar */
            nav .h-16 {
                height: 2.5rem;
            }
            nav .px-6 {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 0.375rem;
            }
            /* Very compact navbar */
            nav .h-16 {
                height: 2.25rem;
            }
            .navbar-brand {
                font-size: 0.8rem;
            }
        }

        /* Improved scrollbar for mobile sidebar */
        #mobile-sidebar {
            scrollbar-width: thin;
            scrollbar-color: rgba(7, 68, 45, 0.3) transparent;
        }

        #mobile-sidebar::-webkit-scrollbar {
            width: 4px;
        }

        #mobile-sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        #mobile-sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(7, 68, 45, 0.3);
            border-radius: 2px;
        }

        /* Smooth transitions for sidebar */
        .sidebar-transition {
            transition: transform 0.3s ease-in-out;
        }

        /* Mobile sidebar overlay */
        @media (max-width: 768px) {
            #mobile-sidebar {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                z-index: 50 !important;
                width: 280px !important;
                height: 100vh !important;
                overflow-y: auto !important;
            }

            #mobile-sidebar:not(.hidden) .sidebar-panel {
                transform: translateX(0);
            }

            /* Navbar gradient */
            .navbar-gradient {
                background: linear-gradient(135deg, #07442d 0%, #206f56 50%, #0f7b5a 100%);
            }

            /* Profile glow effect */
            .profile-glow {
                box-shadow: 0 0 20px rgba(7, 68, 45, 0.3);
            }

            /* Focus styles for accessibility */
            a:focus, button:focus {
                outline: 2px solid #07442d;
                outline-offset: 2px;
            }

            /* Loading states */
            .loading {
                opacity: 0.6;
                pointer-events: none;
            }

            /* Card animations */
            @keyframes fadeInUp {
                0% { transform: translateY(20px); opacity: 0; }
                100% { transform: translateY(0); opacity: 1; }
            }
            
            .card-animation {
                animation: fadeInUp 0.5s ease-out;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Enhanced Static Navigation Bar - Project Colors -->
    <nav class="bg-gradient-to-r from-slate-800 via-slate-700 to-slate-900 shadow-2xl border-b border-slate-600 static top-0 z-50">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Left side - Logo and Brand -->
                <div class="flex items-center space-x-4">
                    <!-- Mobile sidebar toggle -->
                    <label for="mobile-sidebar-toggle" class="md:hidden text-slate-300 hover:text-white focus:outline-none p-2 rounded-lg hover:bg-slate-600/50 transition-all duration-200 cursor-pointer">
                        <i class="fas fa-bars text-lg"></i>
                    </label>
                    
                    <!-- Brand -->
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-primary to-secondary rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-graduation-cap text-white text-lg"></i>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="text-xl font-bold text-white">IPT System</h1>
                            <p class="text-sm text-slate-300 hidden lg:block">Industrial Practical Training</p>
                        </div>
                        <h1 class="sm:hidden text-lg font-bold text-white">IPT</h1>
                    </div>
                </div>

                <!-- Center - Page breadcrumb -->
                <div class="hidden lg:flex items-center space-x-2 text-slate-300">
                    <i class="fas fa-home text-sm"></i>
                    <span class="text-sm">/</span>
                    <span class="text-sm font-medium text-white">Applications</span>
                </div>

                <!-- Right side - User menu and actions -->
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <div class="hidden sm:flex items-center space-x-3">
                        <button class="relative p-2 text-slate-300 hover:text-white rounded-lg hover:bg-slate-600/50 transition-colors">
                            <i class="fas fa-bell text-lg"></i>
                            <span class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-xs font-bold">2</span>
                            </span>
                        </button>
                    </div>

                    <!-- User Profile Section -->
                    <div class="flex items-center space-x-3">
                        <?php 
                        $profile_photo = $student['profile_photo'] ?? '';
                        $reg_number = $student['reg_number'] ?? '';
                        ?>
                        
                        <div class="flex items-center space-x-3 text-white">
                            <div class="hidden md:block text-right">
                                <div class="text-sm font-semibold">
                                    <?php echo htmlspecialchars($student_name); ?>
                                </div>
                                <?php if (!empty($reg_number)): ?>
                                    <div class="text-xs text-slate-300">
                                        <?php echo htmlspecialchars($reg_number); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($profile_photo) && file_exists($profile_photo)): ?>
                                <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile" 
                                     class="w-10 h-10 rounded-full object-cover border-2 border-slate-600 hover:border-primary/50 transition-all duration-200 shadow-sm">
                            <?php else: ?>
                                <div class="w-10 h-10 bg-gradient-to-br from-primary/30 to-secondary/30 rounded-full flex items-center justify-center border-2 border-slate-600 hover:border-primary/50 transition-all duration-200 shadow-sm">
                                    <i class="fas fa-user text-white text-sm"></i>
                                </div>
                            <?php endif; ?>
                            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-slate-800 rounded-full shadow-sm"></div>
                        </div>

                        <!-- Logout -->
                        <a href="student_logout.php" class="flex items-center space-x-2 px-4 py-2 bg-red-500/20 hover:bg-red-500/30 text-red-300 rounded-lg transition-all duration-200 border border-red-500/30 hover:border-red-500/50">
                            <i class="fas fa-sign-out-alt text-sm"></i>
                            <span class="hidden xl:inline text-sm font-medium">Logout</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Mobile menu dropdown -->
            <div id="mobile-menu" class="md:hidden hidden bg-slate-800 border-t border-slate-600 rounded-b-lg shadow-lg">
                <div class="px-4 py-3 space-y-3">
                    <!-- User info -->
                    <div class="flex items-center space-x-3 pb-3 border-b border-slate-600">
                        <?php if (!empty($profile_photo) && file_exists($profile_photo)): ?>
                            <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile" 
                                 class="w-12 h-12 rounded-full object-cover border-2 border-slate-600">
                        <?php else: ?>
                            <div class="w-12 h-12 bg-gradient-to-br from-primary/30 to-secondary/30 rounded-full flex items-center justify-center border-2 border-slate-600">
                                <i class="fas fa-user text-white text-lg"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <div class="text-sm font-semibold text-white">
                                <?php echo htmlspecialchars($student_name); ?>
                            </div>
                            <?php if (!empty($reg_number)): ?>
                                <div class="text-xs text-slate-300">
                                    <?php echo htmlspecialchars($reg_number); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quick actions -->
                    <div class="space-y-2">
                        <a href="student_profile.php" class="flex items-center space-x-3 px-3 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-lg transition-colors">
                            <i class="fas fa-user w-5 text-center text-sm"></i>
                            <span class="text-sm font-medium">Profile</span>
                        </a>
                        <a href="student_dashboard.php" class="flex items-center space-x-3 px-3 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-lg transition-colors">
                            <i class="fas fa-tachometer-alt w-5 text-center text-sm"></i>
                            <span class="text-sm font-medium">Dashboard</span>
                        </a>
                        <a href="change_password.php" class="flex items-center space-x-3 px-3 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-lg transition-colors">
                            <i class="fas fa-lock w-5 text-center text-sm"></i>
                            <span class="text-sm font-medium">Change Password</span>
                        </a>
                        
                        <div class="border-t border-slate-600 pt-2">
                            <a href="student_logout.php" class="flex items-center space-x-3 px-3 py-2 text-red-300 hover:bg-red-500/20 rounded-lg transition-colors">
                                <i class="fas fa-sign-out-alt w-5 text-center text-sm"></i>
                                <span class="text-sm font-medium">Sign Out</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Layout Container -->
    <div class="flex h-screen bg-gray-50 overflow-hidden">
        <?php include 'includes/student_sidebar.php'; ?>

        <!-- Main Content Area with scrollable content -->
        <div class="flex flex-col flex-1 min-h-0">
            <main class="flex-1 overflow-y-auto bg-gray-50">
                <!-- Page Header -->
                <div class="flex-shrink-0 main-content px-3 sm:px-4 lg:px-6 py-3 sm:py-4 bg-white border-b border-gray-200">
                    <div class="page-header">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div class="mb-3 sm:mb-0">
                                <div class="flex items-center">
                                    <div>
                                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">
                                            Training Application
                                        </h1>
                                        <p class="text-xs sm:text-sm text-gray-600">
                                            Apply for Industrial Practical Training placement
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scrollable Content Area -->
                <div class="flex-1 overflow-y-auto bg-gray-50">
                    <div class="main-content px-3 sm:px-4 lg:px-6 py-3 sm:py-4">
                        <!-- Main Content -->
                        <div class="max-w-4xl mx-auto">

                        <!-- Application Status -->
                        <?php if ($existing_application): ?>
                            <div class="mb-6 bg-white rounded-lg shadow p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">Application Status</h3>
                                        <p class="text-sm text-gray-600">Current status of your training application</p>
                                    </div>
                                    <div class="flex items-center">
                                        <?php
                                        $status = $existing_application['status'];
                                        $status_colors = [
                                            'draft' => 'bg-gray-100 text-gray-800',
                                            'submitted' => 'bg-blue-100 text-blue-800',
                                            'in_review' => 'bg-yellow-100 text-yellow-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800'
                                        ];
                                        $status_icons = [
                                            'draft' => 'fas fa-edit',
                                            'submitted' => 'fas fa-paper-plane',
                                            'in_review' => 'fas fa-clock',
                                            'approved' => 'fas fa-check-circle',
                                            'rejected' => 'fas fa-times-circle'
                                        ];
                                        ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $status_colors[$status]; ?>">
                                            <i class="<?php echo $status_icons[$status]; ?> mr-2"></i>
                                            <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php if ($existing_application['submitted_at']): ?>
                                    <p class="mt-2 text-sm text-gray-500">
                                        Submitted on <?php echo date('F j, Y \a\t g:i A', strtotime($existing_application['submitted_at'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Error/Success Messages -->
                        <?php if (!empty($errors)): ?>
                            <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
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

                        <?php if ($success): ?>
                            <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4">
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

                        <!-- Application Form -->
                        <form method="POST" action="" id="applicationForm" class="bg-white shadow-lg rounded-lg p-6">
            <!-- Company Information -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Company Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-building mr-1 text-primary"></i>Company Name *
                        </label>
                        <input type="text" id="company_name" name="company_name" required
                               value="<?php echo htmlspecialchars($existing_application['company_name'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="company_location" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt mr-1 text-primary"></i>Company Location *
                        </label>
                        <input type="text" id="company_location" name="company_location" required
                               value="<?php echo htmlspecialchars($existing_application['company_location'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="position_title" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-briefcase mr-1 text-primary"></i>Position/Role *
                        </label>
                        <input type="text" id="position_title" name="position_title" required
                               value="<?php echo htmlspecialchars($existing_application['position_title'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="training_area" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tools mr-1 text-primary"></i>Training Area *
                        </label>
                        <select id="training_area" name="training_area" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Select Training Area</option>
                            <option value="Software Development" <?php echo ($existing_application['training_area'] ?? '') === 'Software Development' ? 'selected' : ''; ?>>Software Development</option>
                            <option value="Network Administration" <?php echo ($existing_application['training_area'] ?? '') === 'Network Administration' ? 'selected' : ''; ?>>Network Administration</option>
                            <option value="Database Management" <?php echo ($existing_application['training_area'] ?? '') === 'Database Management' ? 'selected' : ''; ?>>Database Management</option>
                            <option value="Cyber Security" <?php echo ($existing_application['training_area'] ?? '') === 'Cyber Security' ? 'selected' : ''; ?>>Cyber Security</option>
                            <option value="Web Development" <?php echo ($existing_application['training_area'] ?? '') === 'Web Development' ? 'selected' : ''; ?>>Web Development</option>
                            <option value="Mobile App Development" <?php echo ($existing_application['training_area'] ?? '') === 'Mobile App Development' ? 'selected' : ''; ?>>Mobile App Development</option>
                            <option value="IT Support" <?php echo ($existing_application['training_area'] ?? '') === 'IT Support' ? 'selected' : ''; ?>>IT Support</option>
                            <option value="Data Analysis" <?php echo ($existing_application['training_area'] ?? '') === 'Data Analysis' ? 'selected' : ''; ?>>Data Analysis</option>
                            <option value="Other" <?php echo ($existing_application['training_area'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Training Period -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Training Period</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="training_duration" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-1 text-primary"></i>Duration (weeks) *
                        </label>
                        <select id="training_duration" name="training_duration" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Select Duration</option>
                            <?php for ($i = 1; $i <= 52; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($existing_application['training_duration'] ?? '') == $i ? 'selected' : ''; ?>><?php echo $i; ?> week<?php echo $i > 1 ? 's' : ''; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-plus mr-1 text-primary"></i>Start Date *
                        </label>
                        <input type="date" id="start_date" name="start_date" required
                               value="<?php echo htmlspecialchars($existing_application['start_date'] ?? ''); ?>"
                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-minus mr-1 text-primary"></i>End Date *
                        </label>
                        <input type="date" id="end_date" name="end_date" required
                               value="<?php echo htmlspecialchars($existing_application['end_date'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                </div>
            </div>

            <!-- Skills and Motivation -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Skills & Motivation</h3>
                <div class="space-y-6">
                    <div>
                        <label for="skills_to_acquire" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lightbulb mr-1 text-primary"></i>Skills You Want to Acquire
                        </label>
                        <textarea id="skills_to_acquire" name="skills_to_acquire" rows="3"
                                  placeholder="List the specific skills and knowledge you hope to gain during this training..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"><?php echo htmlspecialchars($existing_application['skills_to_acquire'] ?? ''); ?></textarea>
                    </div>

                    <div>
                        <label for="motivation_letter" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-heart mr-1 text-primary"></i>Motivation Letter *
                        </label>
                        <textarea id="motivation_letter" name="motivation_letter" rows="6" required
                                  placeholder="Explain why you want to do your industrial training at this company, what you hope to achieve, and how it relates to your career goals... (minimum 100 characters)"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"><?php echo htmlspecialchars($existing_application['motivation_letter'] ?? ''); ?></textarea>
                        <p class="mt-1 text-sm text-gray-500">
                            Character count: <span id="char-count">0</span> / 100 minimum
                        </p>
                    </div>
                </div>
            </div>

            <!-- Alternative Companies -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Alternative Company Preferences</h3>
                <p class="text-sm text-gray-600 mb-4">List alternative companies in case your first choice is not available</p>
                <div class="space-y-4">
                    <div>
                        <label for="preferred_company1" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-star mr-1 text-primary"></i>Second Choice Company
                        </label>
                        <input type="text" id="preferred_company1" name="preferred_company1"
                               value="<?php echo htmlspecialchars($existing_application['preferred_company1'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="preferred_company2" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-star-half-alt mr-1 text-primary"></i>Third Choice Company
                        </label>
                        <input type="text" id="preferred_company2" name="preferred_company2"
                               value="<?php echo htmlspecialchars($existing_application['preferred_company2'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="preferred_company3" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-star mr-1 text-gray-400"></i>Fourth Choice Company
                        </label>
                        <input type="text" id="preferred_company3" name="preferred_company3"
                               value="<?php echo htmlspecialchars($existing_application['preferred_company3'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-6 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-4">
                    <a href="student_dashboard.php" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors text-center">
                        Cancel
                    </a>
                    <button type="submit" name="action" value="save_draft" 
                            class="px-6 py-2 border border-secondary text-secondary rounded-md hover:bg-secondary hover:text-white transition-colors">
                        <i class="fas fa-save mr-2"></i>Save as Draft
                    </button>
                    <button type="submit" name="action" value="submit" 
                            class="px-6 py-2 bg-primary text-white rounded-md hover:bg-secondary transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Application
                    </button>
                </div>
            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- JavaScript for form functionality -->
    <script>
        // Form enhancements
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const mobileMenuButton = document.querySelector('label[for="mobile-sidebar-toggle"]');
            const mobileMenu = document.getElementById('mobile-menu');
            
            // Add click event to toggle mobile menu (for demonstration, though sidebar is preferred)
            if (mobileMenuButton) {
                mobileMenuButton.addEventListener('click', function() {
                    // The sidebar functionality is handled by the checkbox
                });
            }
            const motivationLetter = document.getElementById('motivation_letter');
            const charCount = document.getElementById('char-count');
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            const duration = document.getElementById('training_duration');

            // Character counter
            function updateCharCount() {
                const count = motivationLetter.value.length;
                charCount.textContent = count;
                charCount.className = count >= 100 ? 'text-green-600' : 'text-red-600';
            }

            motivationLetter.addEventListener('input', updateCharCount);
            updateCharCount();

            // Auto-calculate end date based on start date and duration
            function calculateEndDate() {
                if (startDate.value && duration.value) {
                    const start = new Date(startDate.value);
                    const weeks = parseInt(duration.value);
                    const end = new Date(start.getTime() + (weeks * 7 * 24 * 60 * 60 * 1000));
                    endDate.value = end.toISOString().split('T')[0];
                }
            }

            startDate.addEventListener('change', calculateEndDate);
            duration.addEventListener('change', calculateEndDate);

            // Form validation before submission
            document.getElementById('applicationForm').addEventListener('submit', function(e) {
                const action = e.submitter.value;
                if (action === 'submit') {
                    if (motivationLetter.value.length < 100) {
                        e.preventDefault();
                        alert('Motivation letter must be at least 100 characters long.');
                        motivationLetter.focus();
                        return false;
                    }
                }
            });
        });
    </script>
</body>
</html>
