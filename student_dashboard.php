<?php
session_start();
require_once 'db.php';

// Check if user is logged in as student
if (!isset($_SESSION['student_id'])) {
    header('Location: student_login.php');
    exit;
}

// Get student information
$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'] ?? 'Student';
$student_email = $_SESSION['student_email'] ?? '';

// Get full student profile data including profile photo
$stmt = $con->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Use student data or fallback to session data
$student_name = $student_data['full_name'] ?? $student_name;
$student_email = $student_data['email'] ?? $student_email;
$profile_photo = $student_data['profile_photo'] ?? null;
$reg_number = $student_data['reg_number'] ?? '';
$college_name = $student_data['college_name'] ?? '';
$department = $student_data['department'] ?? '';

// Get student statistics
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - IPT System</title>
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
        /* Enhanced static layout styles */
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
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar-container {
                display: none;
            }
            .content-container {
                width: 100% !important;
                margin-left: 0 !important;
            }
        }
        
        /* Card animations */
        @keyframes fadeInUp {
            0% { transform: translateY(20px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        
        .card-animation {
            animation: fadeInUp 0.5s ease-out;
        }
        
        /* Hover effects for cards */
        .stats-card {
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        /* Loading states */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        /* Focus styles for accessibility */
        a:focus, button:focus {
            outline: 2px solid #07442d;
            outline-offset: 2px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Main Layout Container with Static Positioning -->
    <div class="main-layout">
        <!-- Sidebar Container -->
        <div class="sidebar-container">
            <?php include 'includes/student_sidebar.php'; ?>
        </div>
        
        <!-- Content Container -->
        <div class="content-container">
            <!-- Enhanced Static Navigation Bar - Project Colors -->
            <nav class="bg-gradient-to-r from-slate-800 via-slate-700 to-slate-900 shadow-2xl border-b border-slate-600 static top-0 z-50">
                <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <!-- Left side - Logo and Brand -->
                        <div class="flex items-center space-x-4">
                            <!-- Mobile sidebar toggle -->
                            <button id="sidebar-menu-btn" class="md:hidden text-slate-300 hover:text-white focus:outline-none p-2 rounded-lg hover:bg-slate-600/50 transition-all duration-200">
                                <i class="fas fa-bars text-lg"></i>
                            </button>
                            
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
                            <span class="text-sm font-medium text-white">Dashboard</span>
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
                                
                                <!-- Quick access to profile -->
                                <a href="student_profile.php" class="hidden md:flex items-center space-x-3 px-3 py-2 bg-slate-700/50 hover:bg-slate-600/50 rounded-lg transition-all duration-200 border border-slate-600">
                                    <?php if (!empty($profile_photo) && file_exists($profile_photo)): ?>
                                        <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover ring-2 ring-primary/20">
                                    <?php else: ?>
                                        <div class="w-8 h-8 bg-gradient-to-br from-primary/30 to-secondary/30 rounded-full flex items-center justify-center ring-2 ring-primary/20">
                                            <i class="fas fa-user text-white text-sm"></i>
                                        </div>
                                    <?php endif; ?>
                                    <span class="text-sm font-medium text-slate-200 hidden lg:inline">Profile</span>
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
                                <a href="student_profile.php" class="flex items-center space-x-3 px-3 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-lg transition-colors">
                                    <i class="fas fa-user-edit w-5 text-center text-sm"></i>
                                    <span class="text-sm font-medium">Edit Profile</span>
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
            
            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                <!-- Page Header -->
                <div class="bg-white border-b border-gray-200 px-4 py-6 sm:px-6 lg:px-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex items-center">
                                <div>
                                    <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">
                                        Student Dashboard
                                    </h1>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Welcome to your IPT management portal, <?php echo htmlspecialchars($student_name); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick actions for larger screens -->
                        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
                            <a href="student_applications.php" class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-secondary transition-colors text-sm font-medium shadow-sm">
                                <i class="fas fa-plus mr-2"></i>
                                New Application
                            </a>
                            <a href="student_reports.php" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium shadow-sm">
                                <i class="fas fa-chart-line mr-2"></i>
                                Add Report
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Content Padding Container -->
                <div class="px-4 py-6 sm:px-6 lg:px-8">
                    <!-- Enhanced Stats Cards with Better Layout -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                        <!-- Applications Card -->
                        <div class="stats-card card-animation bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:border-blue-200 transition-all duration-300">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                                            <i class="fas fa-file-contract text-white text-lg"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-600 truncate">Applications</dt>
                                            <dd class="text-2xl font-bold text-gray-900"><?php echo $stats['applications']; ?></dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-blue-50 px-6 py-3 border-t border-blue-100">
                                <div class="text-sm text-blue-700 font-medium">
                                    <a href="student_applications.php" class="hover:text-blue-900 transition-colors flex items-center">
                                        <i class="fas fa-arrow-right mr-2"></i>
                                        View All Applications
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Reports Card -->
                        <div class="stats-card card-animation bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:border-green-200 transition-all duration-300">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                                            <i class="fas fa-chart-line text-white text-lg"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-600 truncate">Reports</dt>
                                            <dd class="text-2xl font-bold text-gray-900"><?php echo $stats['reports']; ?></dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-green-50 px-6 py-3 border-t border-green-100">
                                <div class="text-sm text-green-700 font-medium">
                                    <a href="student_reports.php" class="hover:text-green-900 transition-colors flex items-center">
                                        <i class="fas fa-plus mr-2"></i>
                                        Create New Report
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Feedback Card -->
                        <div class="stats-card card-animation bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:border-purple-200 transition-all duration-300">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                                            <i class="fas fa-comments text-white text-lg"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-600 truncate">Pending Feedback</dt>
                                            <dd class="text-2xl font-bold text-gray-900"><?php echo $stats['pending_feedback']; ?></dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-purple-50 px-6 py-3 border-t border-purple-100">
                                <div class="text-sm text-purple-700 font-medium">
                                    <a href="student_feedback.php" class="hover:text-purple-900 transition-colors flex items-center">
                                        <i class="fas fa-bell mr-2"></i>
                                        View Feedback
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Status Card -->
                        <div class="stats-card card-animation bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:border-orange-200 transition-all duration-300">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                                            <i class="fas fa-user-check text-white text-lg"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-600 truncate">Profile Status</dt>
                                            <dd class="text-2xl font-bold text-gray-900">Active</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-orange-50 px-6 py-3 border-t border-orange-100">
                                <div class="text-sm text-orange-700 font-medium">
                                    <a href="student_profile.php" class="hover:text-orange-900 transition-colors flex items-center">
                                        <i class="fas fa-edit mr-2"></i>
                                        Update Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Quick Actions (visible only on mobile) -->
                    <div class="sm:hidden mb-6">
                        <div class="bg-white shadow-lg rounded-xl p-4 border border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                            <div class="grid grid-cols-2 gap-3">
                                <a href="student_applications.php" class="inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-primary hover:bg-secondary transition-colors shadow-sm">
                                    <i class="fas fa-plus mr-2"></i>
                                    New App
                                </a>
                                <a href="student_reports.php" class="inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-secondary hover:bg-accent transition-colors shadow-sm">
                                    <i class="fas fa-edit mr-2"></i>
                                    Report
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions for larger screens -->
                    <div class="hidden sm:block bg-white shadow-lg rounded-xl p-6 mb-8 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <a href="student_applications.php" class="inline-flex items-center justify-center px-6 py-4 border border-transparent text-sm font-medium rounded-lg text-white bg-primary hover:bg-secondary transition-colors shadow-sm hover:shadow-md">
                                <i class="fas fa-plus mr-3"></i>
                                New Application
                            </a>
                            <a href="student_reports.php" class="inline-flex items-center justify-center px-6 py-4 border border-transparent text-sm font-medium rounded-lg text-white bg-secondary hover:bg-accent transition-colors shadow-sm hover:shadow-md">
                                <i class="fas fa-edit mr-3"></i>
                                Submit Report
                            </a>
                            <a href="student_profile.php" class="inline-flex items-center justify-center px-6 py-4 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm hover:shadow-md">
                                <i class="fas fa-user-edit mr-3"></i>
                                Update Profile
                            </a>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">Recent Activity</h2>
                            <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full font-medium">Latest Updates</span>
                        </div>
                        
                        <div class="space-y-4">
                            <!-- Activity Item 1 -->
                            <div class="flex items-start space-x-4 p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-xl border-l-4 border-green-500 hover:shadow-md transition-all duration-200">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center shadow-sm">
                                        <i class="fas fa-check text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold text-green-800 mb-1">Profile Setup Complete</p>
                                            <p class="text-sm text-green-700">Your profile has been created successfully. You can now start applying for training programs.</p>
                                        </div>
                                        <span class="text-xs text-green-600 font-medium whitespace-nowrap ml-4 bg-green-200 px-2 py-1 rounded-full">Today</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Activity Item 2 -->
                            <div class="flex items-start space-x-4 p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl border-l-4 border-blue-500 hover:shadow-md transition-all duration-200">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center shadow-sm">
                                        <i class="fas fa-user-plus text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold text-blue-800 mb-1">Account Activated</p>
                                            <p class="text-sm text-blue-700">Your account has been registered and is ready for IPT applications.</p>
                                        </div>
                                        <span class="text-xs text-blue-600 font-medium whitespace-nowrap ml-4 bg-blue-200 px-2 py-1 rounded-full">Today</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Call to Action -->
                            <div class="pt-4 border-t border-gray-100">
                                <a href="student_applications.php" class="inline-flex items-center text-sm text-primary hover:text-secondary font-semibold transition-colors group">
                                    <span>Start your first application</span>
                                    <i class="fas fa-arrow-right ml-2 text-sm group-hover:translate-x-1 transition-transform"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Enhanced JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const sidebarMenuBtn = document.getElementById('sidebar-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            const mobileSidebar = document.getElementById('mobile-sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            const closeSidebarBtn = document.getElementById('close-sidebar');

            console.log('Dashboard JavaScript loaded');
            console.log('Elements found:', {
                mobileMenuBtn: !!mobileMenuBtn,
                sidebarMenuBtn: !!sidebarMenuBtn,
                mobileMenu: !!mobileMenu,
                mobileSidebar: !!mobileSidebar,
                sidebarOverlay: !!sidebarOverlay,
                closeSidebarBtn: !!closeSidebarBtn
            });

            // Manual test function for debugging
            window.testSidebar = function() {
                console.log('Testing sidebar manually...');
                const sidebar = document.getElementById('mobile-sidebar');
                if (sidebar) {
                    console.log('Sidebar found, current classes:', sidebar.className);
                    sidebar.classList.remove('hidden');
                    sidebar.style.display = 'flex';
                    sidebar.style.zIndex = '9999';
                    sidebar.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
                    console.log('Sidebar should now be visible');
                } else {
                    console.log('Sidebar NOT found!');
                }
            };

            // Toggle mobile navigation menu (user menu)
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', function() {
                    if (mobileMenu) {
                        mobileMenu.classList.toggle('hidden');
                    }
                });
            }

            // Toggle mobile sidebar
            if (sidebarMenuBtn) {
                console.log('Sidebar menu button found');
                sidebarMenuBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Sidebar toggle clicked');
                    
                    // Add a small delay to ensure DOM is ready
                    setTimeout(function() {
                        const sidebar = document.getElementById('mobile-sidebar');
                        if (sidebar) {
                            console.log('Mobile sidebar found, showing...');
                            console.log('Current sidebar classes before:', sidebar.className);
                            sidebar.classList.remove('hidden');
                            sidebar.style.display = 'flex';
                            sidebar.style.zIndex = '9999';
                            document.body.style.overflow = 'hidden';
                            console.log('Current sidebar classes after:', sidebar.className);
                        } else {
                            console.log('Mobile sidebar not found!');
                        }
                    }, 10);
                });
            } else {
                console.log('Sidebar menu button NOT found!');
            }

            // Close sidebar function
            function closeMobileSidebar() {
                const sidebar = document.getElementById('mobile-sidebar');
                if (sidebar) {
                    console.log('🔒 Closing sidebar');
                    sidebar.classList.add('hidden');
                    sidebar.style.display = 'none';
                    document.body.style.overflow = '';
                }
            }

            // Close sidebar when clicking overlay or close button
            document.addEventListener('click', function(e) {
                if (e.target && e.target.id === 'sidebar-overlay') {
                    closeMobileSidebar();
                }
                if (e.target && e.target.id === 'close-sidebar') {
                    closeMobileSidebar();
                }
                // Also check if clicked element is inside close button
                if (e.target && e.target.closest('#close-sidebar')) {
                    closeMobileSidebar();
                }
            });

            // Close sidebar when clicking links on mobile
            const mobileNavLinks = document.querySelectorAll('#mobile-sidebar a');
            mobileNavLinks.forEach(link => {
                link.addEventListener('click', function() {
                    setTimeout(closeMobileSidebar, 150);
                });
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    if (mobileSidebar) {
                        mobileSidebar.classList.add('hidden');
                    }
                    if (mobileMenu) {
                        mobileMenu.classList.add('hidden');
                    }
                    document.body.style.overflow = '';
                }
            });

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeMobileSidebar();
                    if (mobileMenu) {
                        mobileMenu.classList.add('hidden');
                    }
                }
            });

            // Close mobile menu when clicking outside
            document.addEventListener('click', function(e) {
                if (mobileMenu && !mobileMenu.contains(e.target) && !mobileMenuBtn?.contains(e.target)) {
                    mobileMenu.classList.add('hidden');
                }
            });
        });

        // Animate stats counters
        function animateStats() {
            const statNumbers = document.querySelectorAll('.text-2xl.font-bold');
            
            statNumbers.forEach(stat => {
                const text = stat.textContent;
                if (!isNaN(text)) {
                    const finalNumber = parseInt(text);
                    let currentNumber = 0;
                    const increment = Math.ceil(finalNumber / 30);
                    const timer = setInterval(() => {
                        currentNumber += increment;
                        if (currentNumber >= finalNumber) {
                            stat.textContent = finalNumber;
                            clearInterval(timer);
                        } else {
                            stat.textContent = currentNumber;
                        }
                    }, 50);
                }
            });
        }

        // Run counter animation after page load
        window.addEventListener('load', () => {
            setTimeout(animateStats, 500);
        });

        // Add smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add a manual test function for debugging
        window.testSidebar = function() {
            console.log('🧪 Manual sidebar test');
            const sidebar = document.getElementById('mobile-sidebar');
            if (sidebar) {
                console.log('✅ Sidebar found manually');
                sidebar.classList.remove('hidden');
                sidebar.style.display = 'flex';
                sidebar.style.position = 'fixed';
                sidebar.style.top = '0';
                sidebar.style.left = '0';
                sidebar.style.right = '0';
                sidebar.style.bottom = '0';
                sidebar.style.zIndex = '9999';
                document.body.style.overflow = 'hidden';
                console.log('✅ Sidebar should be visible with manual styles');
            } else {
                console.error('❌ Sidebar not found in manual test');
            }
        };

        console.log('🚀 Dashboard loaded. Use testSidebar() to test manually.');
    </script>
</body>
</html>