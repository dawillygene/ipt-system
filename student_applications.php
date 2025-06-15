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

// Create applications table if it doesn't exist (or alter existing)
$create_table_sql = "CREATE TABLE IF NOT EXISTS applications (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    company_name VARCHAR(255),
    company_location VARCHAR(255),
    position_title VARCHAR(255),
    training_duration INT(11),
    start_date DATE,
    end_date DATE,
    training_area VARCHAR(255),
    skills_to_acquire TEXT,
    motivation_letter TEXT,
    preferred_company1 VARCHAR(255),
    preferred_company2 VARCHAR(255),
    preferred_company3 VARCHAR(255),
    status ENUM('draft', 'submitted', 'approved', 'rejected', 'in_review', 'pending') DEFAULT 'draft',
    submitted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
)";

// First check if table exists and has the right structure
$result = $con->query("SHOW TABLES LIKE 'applications'");
if ($result->num_rows > 0) {
    // Table exists, check if it has all required columns
    $columns_result = $con->query("DESCRIBE applications");
    $existing_columns = [];
    while ($row = $columns_result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    // Add missing columns if needed
    $required_columns = [
        'company_name' => 'VARCHAR(255)',
        'company_location' => 'VARCHAR(255)',
        'position_title' => 'VARCHAR(255)',
        'training_duration' => 'INT(11)',
        'start_date' => 'DATE',
        'end_date' => 'DATE',
        'training_area' => 'VARCHAR(255)',
        'skills_to_acquire' => 'TEXT',
        'motivation_letter' => 'TEXT',
        'preferred_company1' => 'VARCHAR(255)',
        'preferred_company2' => 'VARCHAR(255)',
        'preferred_company3' => 'VARCHAR(255)',
        'submitted_at' => 'TIMESTAMP NULL',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ];
    
    foreach ($required_columns as $column => $definition) {
        if (!in_array($column, $existing_columns)) {
            $con->query("ALTER TABLE applications ADD COLUMN $column $definition");
        }
    }
} else {
    // Create new table
    $con->query($create_table_sql);
}

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
        $check_stmt = $con->prepare("SELECT id FROM applications WHERE student_id = ?");
        $check_stmt->bind_param("i", $student_id);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();

        if ($existing) {
            // Update existing application
            $update_stmt = $con->prepare("UPDATE applications SET user_id = ?, company_name = ?, company_location = ?, position_title = ?, training_duration = ?, start_date = ?, end_date = ?, training_area = ?, skills_to_acquire = ?, motivation_letter = ?, preferred_company1 = ?, preferred_company2 = ?, preferred_company3 = ?, status = ?, submitted_at = ? WHERE student_id = ?");
            $update_stmt->bind_param("isssissssssssssi", $student_id, $company_name, $company_location, $position_title, $training_duration, $start_date, $end_date, $training_area, $skills_to_acquire, $motivation_letter, $preferred_company1, $preferred_company2, $preferred_company3, $status, $submitted_at, $student_id);
            
            if ($update_stmt->execute()) {
                $success = ($action === 'submit') ? 'Application submitted successfully!' : 'Application saved as draft';
            } else {
                $errors[] = 'Failed to save application. Please try again.';
            }
            $update_stmt->close();
        } else {
            // Create new application
            $insert_stmt = $con->prepare("INSERT INTO applications (user_id, student_id, company_name, company_location, position_title, training_duration, start_date, end_date, training_area, skills_to_acquire, motivation_letter, preferred_company1, preferred_company2, preferred_company3, status, submitted_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("iisssissssssssss", $student_id, $student_id, $company_name, $company_location, $position_title, $training_duration, $start_date, $end_date, $training_area, $skills_to_acquire, $motivation_letter, $preferred_company1, $preferred_company2, $preferred_company3, $status, $submitted_at);
            
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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Animate.css for better animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
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
        
        /* Desktop optimizations */
        @media (min-width: 1024px) {
            .main-content {
                padding-left: 2rem;
                padding-right: 2rem;
            }
            .content-container {
                margin-left: 0 !important;
            }
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
            /* Mobile form adjustments */
            .grid.lg\\:grid-cols-2 {
                grid-template-columns: 1fr !important;
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

        /* Form stability - no animations or movements */
        .form-section, 
        form,
        .bg-gray-50,
        .bg-white {
            position: static !important;
            transform: none !important;
            transition: none !important;
        }
        
        /* Remove all hover effects that cause movement */
        .form-section:hover,
        .bg-gray-50:hover,
        input:hover,
        select:hover,
        textarea:hover,
        button:hover {
            transform: none !important;
            box-shadow: none !important;
        }
        
        /* Stable focus states without movement */
        input:focus, select:focus, textarea:focus {
            outline: 2px solid #07442d;
            outline-offset: 1px;
            box-shadow: none !important;
            transform: none !important;
        }
        
        /* Completely disable all transitions */
        * {
            transition: none !important;
            animation: none !important;
        }

        /* Custom SweetAlert2 styling */
        .swal2-popup {
            border-radius: 12px;
            font-family: inherit;
        }

        .swal2-title {
            color: #1f2937;
            font-weight: 700;
        }

        .swal2-html-container {
            color: #374151;
        }

        /* Form validation styling */
        .field-valid {
            border-color: #10b981 !important;
            background-color: #f0fdf4 !important;
        }

        .field-invalid {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
        }

        /* Loading overlay */
        .form-loading {
            pointer-events: none;
            opacity: 0.7;
        }

        /* Success animation for character counter */
        .char-count-success {
            animation: pulse 0.5s ease-in-out;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
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
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Page Header - Fixed -->
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

                        <!-- Application Status -->
                        <?php if ($existing_application): ?>
                            <div class="mb-3 bg-white rounded-lg shadow p-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-base font-medium text-gray-900">Application Status</h3>
                                        <p class="text-xs text-gray-600">Current status of your training application</p>
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
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo $status_colors[$status]; ?>">
                                            <i class="<?php echo $status_icons[$status]; ?> mr-1"></i>
                                            <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php if ($existing_application['submitted_at']): ?>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Submitted on <?php echo date('M j, Y \a\t g:i A', strtotime($existing_application['submitted_at'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Error/Success Messages (Hidden - Using SweetAlert) -->
                        <?php if (!empty($errors)): ?>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    let errorMessages = <?php echo json_encode($errors); ?>;
                                    let errorHtml = '<ul style="text-align: left; margin: 0; padding-left: 20px;">';
                                    errorMessages.forEach(function(error) {
                                        errorHtml += '<li>' + error + '</li>';
                                    });
                                    errorHtml += '</ul>';
                                    
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Validation Errors',
                                        html: errorHtml,
                                        confirmButtonText: 'Fix Errors',
                                        confirmButtonColor: '#ef4444',
                                        showClass: {
                                            popup: 'animate__animated animate__fadeInDown'
                                        },
                                        hideClass: {
                                            popup: 'animate__animated animate__fadeOutUp'
                                        }
                                    });
                                });
                            </script>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: '<?php echo addslashes($success); ?>',
                                        confirmButtonText: 'Great!',
                                        confirmButtonColor: '#07442d',
                                        timer: 3000,
                                        timerProgressBar: true,
                                        showClass: {
                                            popup: 'animate__animated animate__fadeInDown'
                                        },
                                        hideClass: {
                                            popup: 'animate__animated animate__fadeOutUp'
                                        }
                                    });
                                });
                            </script>
                        <?php endif; ?>

                        <!-- Application Form -->
                        <form method="POST" action="" id="applicationForm" class="bg-white shadow-lg rounded-lg p-6">
                            <!-- Form Header -->
                            <div class="mb-6 pb-4 border-b border-gray-200">
                                <h2 class="text-2xl font-bold text-gray-900">Training Application</h2>
                                <p class="text-sm text-gray-600">Complete required fields to submit your IPT application</p>
                            </div>

                            <!-- Compact Single Column Layout -->
                            <div class="space-y-6">
                                <!-- Company & Training Details in One Section -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <i class="fas fa-building mr-2 text-primary"></i>
                                        Company & Training Details
                                    </h3>
                                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                        <div>
                                            <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">Company Name *</label>
                                            <input type="text" id="company_name" name="company_name" required
                                                   value="<?php echo htmlspecialchars($existing_application['company_name'] ?? ''); ?>"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                                        </div>
                                        <div>
                                            <label for="company_location" class="block text-sm font-medium text-gray-700 mb-1">Location *</label>
                                            <input type="text" id="company_location" name="company_location" required
                                                   value="<?php echo htmlspecialchars($existing_application['company_location'] ?? ''); ?>"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                                        </div>
                                        <div>
                                            <label for="position_title" class="block text-sm font-medium text-gray-700 mb-1">Position/Role *</label>
                                            <input type="text" id="position_title" name="position_title" required
                                                   value="<?php echo htmlspecialchars($existing_application['position_title'] ?? ''); ?>"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                                        </div>
                                    </div>
                                    
                                    <!-- Training Area and Period -->
                                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mt-4">
                                        <div>
                                            <label for="training_area" class="block text-sm font-medium text-gray-700 mb-1">Training Area *</label>
                                            <select id="training_area" name="training_area" required
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                                                <option value="">Select Area</option>
                                                <option value="Software Development" <?php echo ($existing_application['training_area'] ?? '') === 'Software Development' ? 'selected' : ''; ?>>Software Development</option>
                                                <option value="Web Development" <?php echo ($existing_application['training_area'] ?? '') === 'Web Development' ? 'selected' : ''; ?>>Web Development</option>
                                                <option value="Network Administration" <?php echo ($existing_application['training_area'] ?? '') === 'Network Administration' ? 'selected' : ''; ?>>Network Admin</option>
                                                <option value="Database Management" <?php echo ($existing_application['training_area'] ?? '') === 'Database Management' ? 'selected' : ''; ?>>Database</option>
                                                <option value="Cyber Security" <?php echo ($existing_application['training_area'] ?? '') === 'Cyber Security' ? 'selected' : ''; ?>>Cyber Security</option>
                                                <option value="Mobile App Development" <?php echo ($existing_application['training_area'] ?? '') === 'Mobile App Development' ? 'selected' : ''; ?>>Mobile Dev</option>
                                                <option value="IT Support" <?php echo ($existing_application['training_area'] ?? '') === 'IT Support' ? 'selected' : ''; ?>>IT Support</option>
                                                <option value="Data Analysis" <?php echo ($existing_application['training_area'] ?? '') === 'Data Analysis' ? 'selected' : ''; ?>>Data Analysis</option>
                                                <option value="Other" <?php echo ($existing_application['training_area'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="training_duration" class="block text-sm font-medium text-gray-700 mb-1">Duration (weeks) *</label>
                                            <select id="training_duration" name="training_duration" required
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                                                <option value="">Duration</option>
                                                <?php for ($i = 1; $i <= 24; $i++): ?>
                                                    <option value="<?php echo $i; ?>" <?php echo ($existing_application['training_duration'] ?? '') == $i ? 'selected' : ''; ?>><?php echo $i; ?> week<?php echo $i > 1 ? 's' : ''; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                                            <input type="date" id="start_date" name="start_date" required
                                                   value="<?php echo htmlspecialchars($existing_application['start_date'] ?? ''); ?>"
                                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                                        </div>
                                        <div>
                                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
                                            <input type="date" id="end_date" name="end_date" required
                                                   value="<?php echo htmlspecialchars($existing_application['end_date'] ?? ''); ?>"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                                        </div>
                                    </div>
                                </div>

                                <!-- Alternative Companies - Compact -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <i class="fas fa-list-alt mr-2 text-primary"></i>
                                        Alternative Company Preferences
                                    </h3>
                                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                        <div>
                                            <label for="preferred_company1" class="block text-sm font-medium text-gray-700 mb-1">Second Choice</label>
                                            <input type="text" id="preferred_company1" name="preferred_company1"
                                                   value="<?php echo htmlspecialchars($existing_application['preferred_company1'] ?? ''); ?>"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                                        </div>
                                        <div>
                                            <label for="preferred_company2" class="block text-sm font-medium text-gray-700 mb-1">Third Choice</label>
                                            <input type="text" id="preferred_company2" name="preferred_company2"
                                                   value="<?php echo htmlspecialchars($existing_application['preferred_company2'] ?? ''); ?>"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                                        </div>
                                        <div>
                                            <label for="preferred_company3" class="block text-sm font-medium text-gray-700 mb-1">Fourth Choice</label>
                                            <input type="text" id="preferred_company3" name="preferred_company3"
                                                   value="<?php echo htmlspecialchars($existing_application['preferred_company3'] ?? ''); ?>"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                                        </div>
                                    </div>
                                </div>

                                <!-- Skills and Motivation - Compact -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <i class="fas fa-lightbulb mr-2 text-primary"></i>
                                        Skills & Motivation
                                    </h3>
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                        <div>
                                            <label for="skills_to_acquire" class="block text-sm font-medium text-gray-700 mb-1">Skills to Acquire</label>
                                            <textarea id="skills_to_acquire" name="skills_to_acquire" rows="4"
                                                      placeholder="List skills you want to gain..."
                                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary resize-none"><?php echo htmlspecialchars($existing_application['skills_to_acquire'] ?? ''); ?></textarea>
                                        </div>
                                        <div>
                                            <label for="motivation_letter" class="block text-sm font-medium text-gray-700 mb-1">Motivation Letter *</label>
                                            <textarea id="motivation_letter" name="motivation_letter" rows="4" required
                                                      placeholder="Why this company? Your goals? (min 100 chars)"
                                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary resize-none"><?php echo htmlspecialchars($existing_application['motivation_letter'] ?? ''); ?></textarea>
                                            <p class="text-sm text-gray-500 mt-1">
                                                Character count: <span id="char-count" class="font-medium">0</span> / 100 minimum
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-8 pt-6 border-t border-gray-200">
                                <div class="flex flex-col sm:flex-row justify-between items-center">
                                    <div class="text-sm text-gray-500 mb-4 sm:mb-0">
                                        <i class="fas fa-shield-alt mr-1"></i>
                                        Your information is secure and will be reviewed by our team
                                    </div>
                                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                                        <a href="student_dashboard.php" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 bg-white text-center">
                                            <i class="fas fa-times mr-2"></i>Cancel
                                        </a>
                                        <button type="submit" name="action" value="save_draft" 
                                                class="px-6 py-2 border border-secondary text-secondary rounded-md bg-white">
                                            <i class="fas fa-save mr-2"></i>Save as Draft
                                        </button>
                                        <button type="submit" name="action" value="submit" 
                                                class="px-8 py-2 bg-primary text-white rounded-md font-medium">
                                            <i class="fas fa-paper-plane mr-2"></i>Submit Application
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for form functionality -->
    <script>
        // Form enhancements with SweetAlert2 integration
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
            const form = document.getElementById('applicationForm');

            // Character counter with real-time validation
            function updateCharCount() {
                const count = motivationLetter.value.length;
                charCount.textContent = count;
                
                if (count >= 100) {
                    charCount.className = 'font-medium text-green-600';
                    motivationLetter.classList.remove('border-red-300');
                    motivationLetter.classList.add('border-green-300');
                } else {
                    charCount.className = 'font-medium text-red-600';
                    motivationLetter.classList.remove('border-green-300');
                    motivationLetter.classList.add('border-red-300');
                }
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
                    
                    // Show success message for auto-calculation
                    Swal.fire({
                        icon: 'info',
                        title: 'End Date Calculated',
                        text: `Training will end on ${end.toLocaleDateString()}`,
                        timer: 2000,
                        timerProgressBar: true,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false
                    });
                }
            }

            startDate.addEventListener('change', calculateEndDate);
            duration.addEventListener('change', calculateEndDate);

            // Real-time field validation
            function validateField(field, validationFn, errorMessage) {
                field.addEventListener('blur', function() {
                    if (!validationFn(field.value)) {
                        field.classList.add('border-red-300', 'bg-red-50');
                        field.classList.remove('border-green-300', 'bg-green-50');
                        
                        // Show validation error toast
                        Swal.fire({
                            icon: 'warning',
                            title: 'Invalid Input',
                            text: errorMessage,
                            timer: 3000,
                            timerProgressBar: true,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false
                        });
                    } else {
                        field.classList.remove('border-red-300', 'bg-red-50');
                        field.classList.add('border-green-300', 'bg-green-50');
                    }
                });
            }

            // Apply validation to required fields
            validateField(document.getElementById('company_name'), 
                value => value.trim().length >= 2, 
                'Company name must be at least 2 characters long'
            );

            validateField(document.getElementById('company_location'), 
                value => value.trim().length >= 2, 
                'Company location must be at least 2 characters long'
            );

            validateField(document.getElementById('position_title'), 
                value => value.trim().length >= 2, 
                'Position title must be at least 2 characters long'
            );

            validateField(document.getElementById('training_area'), 
                value => value !== '', 
                'Please select a training area'
            );

            validateField(document.getElementById('training_duration'), 
                value => parseInt(value) >= 1, 
                'Please select a valid training duration'
            );

            validateField(startDate, 
                value => {
                    if (!value) return false;
                    const selected = new Date(value);
                    const tomorrow = new Date();
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    return selected >= tomorrow;
                }, 
                'Start date must be at least tomorrow'
            );

            validateField(motivationLetter, 
                value => value.trim().length >= 100, 
                'Motivation letter must be at least 100 characters long'
            );

            // Enhanced form validation before submission
            form.addEventListener('submit', function(e) {
                e.preventDefault(); // Always prevent default first
                
                const action = e.submitter.value;
                const formData = new FormData(form);
                
                // Collect validation errors
                let validationErrors = [];
                
                if (action === 'submit') {
                    // Check required fields for submission
                    if (!formData.get('company_name').trim()) {
                        validationErrors.push('Company name is required');
                    }
                    if (!formData.get('company_location').trim()) {
                        validationErrors.push('Company location is required');
                    }
                    if (!formData.get('position_title').trim()) {
                        validationErrors.push('Position title is required');
                    }
                    if (!formData.get('training_area')) {
                        validationErrors.push('Training area is required');
                    }
                    if (!formData.get('training_duration') || parseInt(formData.get('training_duration')) < 1) {
                        validationErrors.push('Training duration must be at least 1 week');
                    }
                    if (!formData.get('start_date')) {
                        validationErrors.push('Start date is required');
                    }
                    if (!formData.get('end_date')) {
                        validationErrors.push('End date is required');
                    }
                    if (!formData.get('motivation_letter').trim()) {
                        validationErrors.push('Motivation letter is required');
                    } else if (formData.get('motivation_letter').trim().length < 100) {
                        validationErrors.push('Motivation letter must be at least 100 characters long');
                    }

                    // Date validation
                    const startDateValue = formData.get('start_date');
                    const endDateValue = formData.get('end_date');
                    
                    if (startDateValue && endDateValue) {
                        const start = new Date(startDateValue);
                        const end = new Date(endDateValue);
                        const today = new Date();
                        
                        if (start <= today) {
                            validationErrors.push('Start date must be in the future');
                        }
                        if (end <= start) {
                            validationErrors.push('End date must be after start date');
                        }
                    }
                }

                // Show validation errors if any
                if (validationErrors.length > 0) {
                    let errorHtml = '<ul style="text-align: left; margin: 0; padding-left: 20px;">';
                    validationErrors.forEach(function(error) {
                        errorHtml += '<li>' + error + '</li>';
                    });
                    errorHtml += '</ul>';
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Please Fix These Issues',
                        html: errorHtml,
                        confirmButtonText: 'Fix Issues',
                        confirmButtonColor: '#ef4444',
                        showClass: {
                            popup: 'animate__animated animate__shakeX'
                        }
                    });
                    return false;
                }

                // Show confirmation dialog before submission
                if (action === 'submit') {
                    Swal.fire({
                        icon: 'question',
                        title: 'Submit Application?',
                        text: 'Are you sure you want to submit your training application? You can still edit it later if needed.',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Submit!',
                        cancelButtonText: 'Review Again',
                        confirmButtonColor: '#07442d',
                        cancelButtonColor: '#6b7280',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show loading state
                            Swal.fire({
                                title: 'Submitting Application...',
                                text: 'Please wait while we process your application',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                allowEnterKey: false,
                                showConfirmButton: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            
                            // Submit the form
                            form.submit();
                        }
                    });
                } else if (action === 'save_draft') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Save as Draft?',
                        text: 'Your application will be saved and you can continue editing it later.',
                        showCancelButton: true,
                        confirmButtonText: 'Save Draft',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#206f56',
                        cancelButtonColor: '#6b7280'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show loading state
                            Swal.fire({
                                title: 'Saving Draft...',
                                text: 'Please wait while we save your draft',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                allowEnterKey: false,
                                showConfirmButton: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            
                            // Submit the form
                            form.submit();
                        }
                    });
                }
            });

            // Show welcome message if first time visiting
            <?php if (!$existing_application): ?>
            setTimeout(function() {
                Swal.fire({
                    icon: 'info',
                    title: 'Welcome to Training Applications',
                    html: `
                        <div style="text-align: left;">
                            <p><strong>Steps to complete your application:</strong></p>
                            <ol style="margin: 10px 0; padding-left: 20px;">
                                <li>Fill in company and training details</li>
                                <li>Add alternative company preferences (optional)</li>
                                <li>Write your motivation letter (minimum 100 characters)</li>
                                <li>Save as draft or submit directly</li>
                            </ol>
                            <p style="color: #07442d; font-weight: bold;">💡 Tip: You can save drafts and edit them later!</p>
                        </div>
                    `,
                    confirmButtonText: 'Start Application',
                    confirmButtonColor: '#07442d',
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    }
                });
            }, 1000);
            <?php endif; ?>
        });
    </script>
</body>
</html>
