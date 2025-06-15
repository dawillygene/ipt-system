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

// Check for session-based success message
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Get current student data
$stmt = $con->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Set variables required by the reusable sidebar
$student_data = $student;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update_profile';
    
    if ($action === 'update_profile') {
        // Sanitize input
        $full_name = trim($_POST['full_name'] ?? '');
        $reg_number = trim($_POST['reg_number'] ?? '');
        $gender = $_POST['gender'] ?? '';
        $college_name = trim($_POST['college_name'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $course_name = trim($_POST['course_name'] ?? '');
        $program = trim($_POST['program'] ?? '');
        $level = trim($_POST['level'] ?? '');
        $year_of_study = (int)($_POST['year_of_study'] ?? 0);
        $phone_number = trim($_POST['phone_number'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $email = trim($_POST['email'] ?? '');

        // Comprehensive validation with detailed messages
        if (empty($full_name)) $errors[] = 'Full name is required - Please enter your complete name';
        if (empty($reg_number)) $errors[] = 'Registration number is required - This is your student registration number';
        if (empty($gender)) $errors[] = 'Gender is required - Please select your gender';
        if (empty($college_name)) $errors[] = 'College/Institution name is required - Enter your educational institution';
        if (empty($department)) $errors[] = 'Department is required - Enter your academic department';
        if (empty($course_name)) $errors[] = 'Course name is required - Enter your course/program name';
        if (empty($program)) $errors[] = 'Program type is required - Select Certificate, Diploma, Bachelor, etc.';
        if (empty($level)) $errors[] = 'Academic level is required - Enter your current level (e.g., Level 6, Year 3)';
        if ($year_of_study < 1 || $year_of_study > 8) $errors[] = 'Year of study must be between 1-8 - Select your current year';
        if (empty($phone_number)) $errors[] = 'Phone number is required - Enter a valid contact number';
        if (empty($address)) $errors[] = 'Address is required - Enter your complete residential address';
        if (empty($email)) $errors[] = 'Email address is required - Enter a valid email for communication';
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format - Please enter a valid email address (e.g., student@example.com)';
        
        // Additional phone number validation
        if (!empty($phone_number) && !preg_match('/^[0-9+\-\s\(\)]{10,15}$/', $phone_number)) {
            $errors[] = 'Phone number format is invalid - Please enter a valid phone number';
        }

        // Handle file uploads
        $profile_photo_path = $student['profile_photo'] ?? null;
        $academic_transcript_path = $student['academic_transcript'] ?? null;
        $id_document_path = $student['id_document'] ?? null;
        $cv_document_path = $student['cv_document'] ?? null;
        $upload_dir = __DIR__ . '/uploads/students/';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Handle profile photo upload
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($_FILES['profile_photo']['type'], $allowed_types)) {
                if ($_FILES['profile_photo']['size'] <= 2 * 1024 * 1024) { // 2MB limit
                    $file_extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
                    $filename = 'profile_' . $student_id . '_' . time() . '.' . $file_extension;
                    $profile_photo_path = 'uploads/students/' . $filename;
                    
                    if (!move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_dir . $filename)) {
                        $errors[] = 'Failed to upload profile photo';
                    }
                } else {
                    $errors[] = 'Profile photo size must be less than 2MB';
                }
            } else {
                $errors[] = 'Profile photo must be JPEG, PNG, or GIF format';
            }
        }

        // Handle academic transcript upload
        if (isset($_FILES['academic_transcript']) && $_FILES['academic_transcript']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (in_array($_FILES['academic_transcript']['type'], $allowed_types)) {
                if ($_FILES['academic_transcript']['size'] <= 5 * 1024 * 1024) { // 5MB limit
                    $file_extension = pathinfo($_FILES['academic_transcript']['name'], PATHINFO_EXTENSION);
                    $filename = 'transcript_' . $student_id . '_' . time() . '.' . $file_extension;
                    $academic_transcript_path = 'uploads/students/' . $filename;
                    
                    if (!move_uploaded_file($_FILES['academic_transcript']['tmp_name'], $upload_dir . $filename)) {
                        $errors[] = 'Failed to upload academic transcript';
                    }
                } else {
                    $errors[] = 'Academic transcript size must be less than 5MB';
                }
            } else {
                $errors[] = 'Academic transcript must be PDF, DOC, or DOCX format';
            }
        }

        // Handle ID document upload
        if (isset($_FILES['id_document']) && $_FILES['id_document']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
            if (in_array($_FILES['id_document']['type'], $allowed_types)) {
                if ($_FILES['id_document']['size'] <= 5 * 1024 * 1024) { // 5MB limit
                    $file_extension = pathinfo($_FILES['id_document']['name'], PATHINFO_EXTENSION);
                    $filename = 'id_' . $student_id . '_' . time() . '.' . $file_extension;
                    $id_document_path = 'uploads/students/' . $filename;
                    
                    if (!move_uploaded_file($_FILES['id_document']['tmp_name'], $upload_dir . $filename)) {
                        $errors[] = 'Failed to upload ID document';
                    }
                } else {
                    $errors[] = 'ID document size must be less than 5MB';
                }
            } else {
                $errors[] = 'ID document must be PDF, JPG, or PNG format';
            }
        }

        // Handle CV document upload
        if (isset($_FILES['cv_document']) && $_FILES['cv_document']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (in_array($_FILES['cv_document']['type'], $allowed_types)) {
                if ($_FILES['cv_document']['size'] <= 5 * 1024 * 1024) { // 5MB limit
                    $file_extension = pathinfo($_FILES['cv_document']['name'], PATHINFO_EXTENSION);
                    $filename = 'cv_' . $student_id . '_' . time() . '.' . $file_extension;
                    $cv_document_path = 'uploads/students/' . $filename;
                    
                    if (!move_uploaded_file($_FILES['cv_document']['tmp_name'], $upload_dir . $filename)) {
                        $errors[] = 'Failed to upload CV document';
                    }
                } else {
                    $errors[] = 'CV document size must be less than 5MB';
                }
            } else {
                $errors[] = 'CV document must be PDF, DOC, or DOCX format';
            }
        }

    // Check if registration number is taken by another student
    if (empty($errors) && $reg_number !== $student['reg_number']) {
        $check_stmt = $con->prepare("SELECT student_id FROM students WHERE reg_number = ? AND student_id != ?");
        $check_stmt->bind_param("si", $reg_number, $student_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $errors[] = 'Registration number already exists';
        }
        $check_stmt->close();
    }

    // Update if no errors
    if (empty($errors)) {
        // First, check if document columns exist, if not add them
        $columns_to_add = [
            'profile_photo' => 'VARCHAR(500) NULL',
            'academic_transcript' => 'VARCHAR(500) NULL',
            'id_document' => 'VARCHAR(500) NULL',
            'cv_document' => 'VARCHAR(500) NULL'
        ];
        
        foreach ($columns_to_add as $column => $definition) {
            $col_check = $con->query("SHOW COLUMNS FROM students LIKE '$column'");
            if ($col_check->num_rows == 0) {
                $con->query("ALTER TABLE students ADD COLUMN $column $definition");
            }
        }
        
        $update_stmt = $con->prepare("UPDATE students SET full_name = ?, reg_number = ?, gender = ?, college_name = ?, department = ?, course_name = ?, program = ?, level = ?, year_of_study = ?, phone_number = ?, address = ?, email = ?, profile_photo = ?, academic_transcript = ?, id_document = ?, cv_document = ? WHERE student_id = ?");
        $update_stmt->bind_param("ssssssssisssssssi", $full_name, $reg_number, $gender, $college_name, $department, $course_name, $program, $level, $year_of_study, $phone_number, $address, $email, $profile_photo_path, $academic_transcript_path, $id_document_path, $cv_document_path, $student_id);
        
        if ($update_stmt->execute()) {
            // Update session with new name
            $_SESSION['student_name'] = $full_name;
            $_SESSION['success_message'] = 'Profile updated successfully!';
            header('Location: student_profile.php');
            exit;
        } else {
            $errors[] = 'Failed to update profile. Please try again.';
        }
        $update_stmt->close();
    }
    }
}

// Get student statistics for navigation
function getStudentStats($con, $student_id) {
    $stats = [];
    
    // Get applications count (if applications table exists for students)
    try {
        $stmt = $con->prepare("SELECT COUNT(*) as total FROM applications WHERE student_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $stats['applications'] = $stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();
    } catch (Exception $e) {
        $stats['applications'] = 0; // Default if table doesn't exist
    }
    
    // Get reports count
    try {
        $stmt = $con->prepare("SELECT COUNT(*) as total FROM reports WHERE student_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $stats['reports'] = $stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();
    } catch (Exception $e) {
        $stats['reports'] = 0; // Default if table doesn't exist
    }
    
    // Get pending feedback count
    try {
        $stmt = $con->prepare("SELECT COUNT(*) as total FROM feedback WHERE student_id = ? AND status = 'pending'");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $stats['pending_feedback'] = $stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();
    } catch (Exception $e) {
        $stats['pending_feedback'] = 0; // Default if table doesn't exist
    }
    
    return $stats;
}

$stats = getStudentStats($con, $student_id);
$profile_photo = $student['profile_photo'] ?? null;
$reg_number = $student['reg_number'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - IPT System</title>
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
                font-size: 0.7rem;
            }
        }

        /* Desktop layout - ensure no margin issues */
        @media (min-width: 769px) {
            .content-container {
                margin-left: 0 !important;
            }
            .main-content {
                margin-left: 0 !important;
            }
        }

        /* Mobile sidebar overlay styling */
        #mobile-sidebar {
            z-index: 9999 !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            background-color: rgba(0, 0, 0, 0.5) !important;
        }

        #mobile-sidebar.hidden {
            display: none !important;
        }

        #mobile-sidebar:not(.hidden) {
            display: flex !important;
        }

        /* Mobile sidebar panel animation */
        #mobile-sidebar .sidebar-panel {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
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
                    <span class="text-sm font-medium text-white">Profile</span>
                </div>

                <!-- Right side - User menu and actions -->
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <div class="hidden sm:flex items-center space-x-3">
                        <?php if ($stats['pending_feedback'] > 0): ?>
                        <button class="relative p-2 text-slate-300 hover:text-white hover:bg-slate-600/50 rounded-lg transition-all duration-200">
                            <i class="fas fa-bell text-lg"></i>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center animate-pulse font-medium">
                                <?php echo $stats['pending_feedback']; ?>
                            </span>
                        </button>
                        <?php endif; ?>
                        
                        <!-- Quick access to dashboard -->
                        <a href="student_dashboard.php" class="hidden md:flex items-center space-x-3 px-3 py-2 bg-slate-700/50 hover:bg-slate-600/50 rounded-lg transition-all duration-200 border border-slate-600">
                            <i class="fas fa-tachometer-alt text-white text-sm"></i>
                            <span class="text-sm font-medium text-slate-200 hidden lg:inline">Dashboard</span>
                        </a>
                    </div>

                    <!-- Mobile user menu button -->
                    <button id="mobile-menu-btn" class="md:hidden flex items-center space-x-2 px-3 py-2 bg-slate-700/50 hover:bg-slate-600/50 rounded-lg transition-all duration-200 border border-slate-600">
                        <?php if (!empty($profile_photo) && file_exists($profile_photo)): ?>
                            <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile" class="w-6 h-6 rounded-full object-cover">
                        <?php else: ?>
                            <div class="w-6 h-6 bg-gradient-to-br from-primary/30 to-secondary/30 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white text-xs"></i>
                            </div>
                        <?php endif; ?>
                        <i class="fas fa-chevron-down text-slate-300 text-sm"></i>
                    </button>

                    <!-- Desktop user menu -->
                    <div class="hidden md:flex items-center space-x-4">
                        <!-- User info -->
                        <div class="text-right hidden lg:block">
                            <div class="text-sm font-semibold text-white">
                                <?php echo htmlspecialchars($student_name); ?>
                            </div>
                            <?php if (!empty($reg_number)): ?>
                                <div class="text-xs text-slate-300">
                                    <?php echo htmlspecialchars($reg_number); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Profile picture -->
                        <div class="relative">
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
                        <a href="student_dashboard.php" class="flex items-center space-x-3 px-3 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-lg transition-colors">
                            <i class="fas fa-tachometer-alt w-5 text-center text-sm"></i>
                            <span class="text-sm font-medium">Dashboard</span>
                        </a>
                        
                        <?php if ($stats['pending_feedback'] > 0): ?>
                        <a href="student_feedback.php" class="flex items-center space-x-3 px-3 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-lg transition-colors">
                            <i class="fas fa-bell w-5 text-center text-sm"></i>
                            <span class="text-sm font-medium">Notifications</span>
                            <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full font-medium">
                                <?php echo $stats['pending_feedback']; ?>
                            </span>
                        </a>
                        <?php endif; ?>
                        
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
                                        Profile Management
                                    </h1>
                                    <p class="text-xs sm:text-sm text-gray-600">
                                        Update your personal and academic information
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

        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <a href="#personal" id="tab-personal" class="tab-link border-primary text-primary border-b-2 py-2 px-1 text-sm font-medium">
                    <i class="fas fa-user mr-2"></i>Personal Details
                </a>
                <a href="#academic" id="tab-academic" class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-2 px-1 text-sm font-medium">
                    <i class="fas fa-graduation-cap mr-2"></i>Academic Details
                </a>
                <a href="#contact" id="tab-contact" class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-2 px-1 text-sm font-medium">
                    <i class="fas fa-address-book mr-2"></i>Contact Details
                </a>
                <a href="#documents" id="tab-documents" class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-2 px-1 text-sm font-medium">
                    <i class="fas fa-file-upload mr-2"></i>Documents
                </a>
            </nav>
        </div>

        <!-- Error/Success Messages -->
        <?php if (!empty($errors)): ?>
            <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4" style="display: none;">
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
            <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4" style="display: none;">
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

        <!-- Profile Form -->
        <form id="profileForm" method="POST" action="" enctype="multipart/form-data" class="bg-white shadow-lg rounded-lg p-6">
            <input type="hidden" name="action" value="update_profile">
            
            <!-- Completion Status will be inserted here by JavaScript -->
            
            <!-- Required Fields Notice -->
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Important:</strong> All fields marked with <span class="text-red-500 font-bold">*</span> are required. 
                            Please fill in all required information to save your profile successfully.
                        </p>
                    </div>
                </div>
            </div>
            <!-- Personal Details Tab -->
            <div id="content-personal" class="tab-content">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user mr-1 text-primary"></i>Full Name
                        </label>
                        <input type="text" id="full_name" name="full_name" required
                               value="<?php echo htmlspecialchars($student['full_name'] ?? ''); ?>"
                               placeholder="Enter your complete name (e.g., John Doe Smith)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="reg_number" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-id-card mr-1 text-primary"></i>Registration Number
                        </label>
                        <input type="text" id="reg_number" name="reg_number" required
                               value="<?php echo htmlspecialchars($student['reg_number'] ?? ''); ?>"
                               placeholder="Enter your student registration number (e.g., REG/2024/001)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-venus-mars mr-1 text-primary"></i>Gender
                        </label>
                        <select id="gender" name="gender" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo ($student['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($student['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($student['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Academic Details Tab -->
            <div id="content-academic" class="tab-content hidden">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Academic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="college_name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-university mr-1 text-primary"></i>College/Institution
                        </label>
                        <input type="text" id="college_name" name="college_name" required
                               value="<?php echo htmlspecialchars($student['college_name'] ?? ''); ?>"
                               placeholder="Enter your college or institution name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-building mr-1 text-primary"></i>Department
                        </label>
                        <input type="text" id="department" name="department" required
                               value="<?php echo htmlspecialchars($student['department'] ?? ''); ?>"
                               placeholder="Enter your department (e.g., Computer Science, Engineering)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="course_name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-book mr-1 text-primary"></i>Course Name
                        </label>
                        <input type="text" id="course_name" name="course_name" required
                               value="<?php echo htmlspecialchars($student['course_name'] ?? ''); ?>"
                               placeholder="Enter your course name (e.g., Software Engineering, Business Administration)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="program" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-certificate mr-1 text-primary"></i>Program
                        </label>
                        <select id="program" name="program" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Select Program</option>
                            <option value="Certificate" <?php echo ($student['program'] ?? '') === 'Certificate' ? 'selected' : ''; ?>>Certificate</option>
                            <option value="Diploma" <?php echo ($student['program'] ?? '') === 'Diploma' ? 'selected' : ''; ?>>Diploma</option>
                            <option value="Bachelor" <?php echo ($student['program'] ?? '') === 'Bachelor' ? 'selected' : ''; ?>>Bachelor's Degree</option>
                            <option value="Master" <?php echo ($student['program'] ?? '') === 'Master' ? 'selected' : ''; ?>>Master's Degree</option>
                            <option value="PhD" <?php echo ($student['program'] ?? '') === 'PhD' ? 'selected' : ''; ?>>PhD</option>
                        </select>
                    </div>

                    <div>
                        <label for="level" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-layer-group mr-1 text-primary"></i>Level
                        </label>
                        <input type="text" id="level" name="level" required
                               value="<?php echo htmlspecialchars($student['level'] ?? ''); ?>"
                               placeholder="e.g., Level 6, Year 3, etc."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="year_of_study" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-1 text-primary"></i>Year of Study
                        </label>
                        <select id="year_of_study" name="year_of_study" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Select Year</option>
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($student['year_of_study'] ?? '') == $i ? 'selected' : ''; ?>>Year <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Contact Details Tab -->
            <div id="content-contact" class="tab-content hidden">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-1 text-primary"></i>Email Address
                        </label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>"
                               placeholder="Enter your email address (e.g., student@example.com)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-phone mr-1 text-primary"></i>Phone Number
                        </label>
                        <input type="text" id="phone_number" name="phone_number" required
                               value="<?php echo htmlspecialchars($student['phone_number'] ?? ''); ?>"
                               placeholder="Enter your phone number starting with +255 (e.g., +255753225961)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt mr-1 text-primary"></i>Address
                        </label>
                        <textarea id="address" name="address" rows="3" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                                  placeholder="Enter your complete residential address (Street, City, State/Region, Country)"><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Documents Tab -->
            <div id="content-documents" class="tab-content hidden">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Documents & Profile Photo</h3>
                
                <!-- Profile Photo Section -->
                <div class="mb-6">
                    <h4 class="text-md font-medium text-gray-800 mb-3">Profile Photo</h4>
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex justify-center lg:justify-start">
                            <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center">
                                <?php if (!empty($student['profile_photo']) && file_exists($student['profile_photo'])): ?>
                                    <img src="<?php echo htmlspecialchars($student['profile_photo']); ?>" alt="Profile" class="w-20 h-20 rounded-full object-cover">
                                <?php else: ?>
                                    <i class="fas fa-user text-gray-400 text-xl"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="lg:col-span-3">
                            <label for="profile_photo" class="block text-sm font-medium text-gray-700 mb-2">
                                Upload Profile Photo
                            </label>
                            <input type="file" id="profile_photo" name="profile_photo" accept="image/*"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary file:text-white hover:file:bg-secondary">
                            <p class="mt-1 text-xs text-gray-500">PNG, JPG up to 2MB</p>
                        </div>
                    </div>
                </div>

                <!-- Required Documents Section -->
                <div>
                    <h4 class="text-md font-medium text-gray-800 mb-3">Required Documents</h4>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <!-- Academic Transcript -->
                        <div class="border border-gray-200 rounded-lg p-4 bg-white hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-3">
                                <label for="academic_transcript" class="flex items-center text-sm font-medium text-gray-700">
                                    <i class="fas fa-file-alt mr-2 text-primary"></i>Academic Transcript
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <?php if (!empty($student['academic_transcript'])): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>Uploaded
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Required
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($student['academic_transcript'])): ?>
                                <div class="mb-3 p-2 bg-gray-50 rounded-md">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-600">Current file:</span>
                                        <a href="<?php echo htmlspecialchars($student['academic_transcript']); ?>" target="_blank" 
                                           class="text-primary hover:text-secondary text-xs font-medium">
                                            <i class="fas fa-download mr-1"></i>View/Download
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <input type="file" id="academic_transcript" name="academic_transcript" accept=".pdf,.doc,.docx"
                                   class="block w-full text-sm text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-secondary file:text-white hover:file:bg-accent">
                            <p class="mt-1 text-xs text-gray-500">PDF, DOC, DOCX up to 5MB</p>
                        </div>

                        <!-- National ID / Passport -->
                        <div class="border border-gray-200 rounded-lg p-4 bg-white hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-3">
                                <label for="id_document" class="flex items-center text-sm font-medium text-gray-700">
                                    <i class="fas fa-id-card mr-2 text-primary"></i>National ID / Passport
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <?php if (!empty($student['id_document'])): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>Uploaded
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Required
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($student['id_document'])): ?>
                                <div class="mb-3 p-2 bg-gray-50 rounded-md">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-600">Current file:</span>
                                        <a href="<?php echo htmlspecialchars($student['id_document']); ?>" target="_blank" 
                                           class="text-primary hover:text-secondary text-xs font-medium">
                                            <i class="fas fa-download mr-1"></i>View/Download
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <input type="file" id="id_document" name="id_document" accept=".pdf,.jpg,.jpeg,.png"
                                   class="block w-full text-sm text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-secondary file:text-white hover:file:bg-accent">
                            <p class="mt-1 text-xs text-gray-500">PDF, JPG, PNG up to 5MB</p>
                        </div>

                        <!-- Curriculum Vitae (CV) -->
                        <div class="border border-gray-200 rounded-lg p-4 bg-white hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-3">
                                <label for="cv_document" class="flex items-center text-sm font-medium text-gray-700">
                                    <i class="fas fa-file-user mr-2 text-primary"></i>Curriculum Vitae (CV)
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <?php if (!empty($student['cv_document'])): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>Uploaded
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Required
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($student['cv_document'])): ?>
                                <div class="mb-3 p-2 bg-gray-50 rounded-md">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-600">Current file:</span>
                                        <a href="<?php echo htmlspecialchars($student['cv_document']); ?>" target="_blank" 
                                           class="text-primary hover:text-secondary text-xs font-medium">
                                            <i class="fas fa-download mr-1"></i>View/Download
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <input type="file" id="cv_document" name="cv_document" accept=".pdf,.doc,.docx"
                                   class="block w-full text-sm text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-secondary file:text-white hover:file:bg-accent">
                            <p class="mt-1 text-xs text-gray-500">PDF, DOC, DOCX up to 5MB</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-6 pt-4 border-t border-gray-200">
                <div class="flex justify-end space-x-4">
                    <a href="student_dashboard.php" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-primary text-white rounded-md hover:bg-secondary transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        <i class="fas fa-save mr-2"></i>Save Profile
                    </button>
                </div>
            </div>
        </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab functionality - restore the removed JavaScript for tabs
        document.addEventListener('DOMContentLoaded', function() {
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');
            
            // Initialize default tab (Personal Details)
            function showTab(tabName) {
                // Remove active classes from all tabs
                tabLinks.forEach(l => {
                    l.classList.remove('border-primary', 'text-primary');
                    l.classList.add('border-transparent', 'text-gray-500');
                });
                
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });
                
                // Add active classes to selected tab
                const activeLink = document.getElementById('tab-' + tabName);
                const activeContent = document.getElementById('content-' + tabName);
                
                if (activeLink && activeContent) {
                    activeLink.classList.remove('border-transparent', 'text-gray-500');
                    activeLink.classList.add('border-primary', 'text-primary');
                    activeContent.classList.remove('hidden');
                }
            }
            
            // Show default tab (Personal Details)
            showTab('personal');
            
            // Add click event listeners to tab links
            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);
                    showTab(targetId);
                });
            });
        });
    </script>
</body>
</html>
