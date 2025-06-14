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
        /* Custom animations and effects */
        @keyframes slideInFromLeft {
            0% { transform: translateX(-100%); opacity: 0; }
            100% { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes fadeInUp {
            0% { transform: translateY(20px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .sidebar-animation {
            animation: slideInFromLeft 0.3s ease-out;
        }

        .card-animation {
            animation: fadeInUp 0.5s ease-out;
        }

        /* Smooth scrollbar for sidebar */
        #sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        #sidebar::-webkit-scrollbar-track {
            background: rgba(71, 85, 105, 0.1);
            border-radius: 3px;
        }
        
        #sidebar::-webkit-scrollbar-thumb {
            background: rgba(71, 85, 105, 0.3);
            border-radius: 3px;
        }
        
        #sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(71, 85, 105, 0.5);
        }

        /* Custom gradient backgrounds */
        .bg-gradient-primary {
            background: linear-gradient(135deg, #07442d 0%, #206f56 100%);
        }
        
        .bg-gradient-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        /* Glassmorphism effect */
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Profile photo glow effect */
        .profile-glow {
            box-shadow: 0 0 20px rgba(7, 68, 45, 0.3);
        }

        /* Notification badges */
        .notification-badge {
            position: relative;
        }
        
        .notification-badge::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        /* Enhanced hover effects */
        .nav-link-hover {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .nav-link-hover::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }
        
        .nav-link-hover:hover::before {
            left: 100%;
        }

        /* Enhanced responsive design with better mobile optimization */
        @media (max-width: 1024px) {
            .main-content {
                padding: 1rem;
            }
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 1rem;
            }
            .stats-card .p-6 {
                padding: 1rem;
            }
        }

        @media (max-width: 768px) {
            #sidebar {
                overflow-y: auto;
                height: 100vh;
                width: 280px; /* Slightly narrower on tablets */
            }
            .main-content {
                padding: 0.75rem;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
            .navbar-brand {
                font-size: 0.9rem;
            }
            .page-header h1 {
                font-size: 1.75rem;
            }
            .stats-card {
                padding: 0.75rem;
            }
            .stats-card .p-6 {
                padding: 0.75rem;
            }
            /* Compact navbar */
            nav .h-16 {
                height: 3.5rem;
            }
        }

        @media (max-width: 640px) {
            #sidebar {
                width: 260px; /* Even narrower on mobile */
            }
            .main-content {
                padding: 0.5rem;
            }
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }
            .page-header h1 {
                font-size: 1.5rem;
                margin-bottom: 0.25rem;
            }
            .page-header p {
                font-size: 0.875rem;
            }
            .stats-card {
                padding: 0.75rem;
            }
            .stats-card .p-6 {
                padding: 0.75rem;
            }
            /* Ultra compact navbar */
            nav .h-16 {
                height: 3rem;
            }
            nav .px-6 {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
            /* Compact quick actions */
            .quick-actions-mobile {
                padding: 0.75rem;
            }
            .quick-actions-mobile .grid {
                gap: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            #sidebar {
                width: 240px; /* Smallest sidebar for very small screens */
            }
            .main-content {
                padding: 0.375rem;
            }
            .page-header {
                margin-bottom: 1rem;
            }
            .page-header h1 {
                font-size: 1.25rem;
            }
            .stats-card .text-2xl {
                font-size: 1.5rem;
            }
            /* Very compact navbar */
            nav .h-16 {
                height: 2.75rem;
            }
            .navbar-brand {
                font-size: 0.8rem;
            }
        }

        /* Navbar enhancements */
        .navbar-gradient {
            background: linear-gradient(135deg, #07442d 0%, #206f56 50%, #0f7b5a 100%);
        }

        .navbar-blur {
            backdrop-filter: blur(10px);
            background: rgba(7, 68, 45, 0.95);
        }

        /* Content responsiveness */
        .content-container {
            max-width: 100%;
            transition: margin-left 0.3s ease;
        }

        @media (min-width: 768px) {
            .content-container {
                margin-left: 0;
            }
        }
        
        /* Focus styles for accessibility */
        a:focus, button:focus {
            outline: 2px solid #07442d;
            outline-offset: 2px;
        }

        /* Touch-friendly targets for mobile */
        @media (max-width: 768px) {
            a, button {
                min-height: 44px; /* Apple's recommended touch target size */
                min-width: 44px;
            }
            
            .touch-target {
                padding: 12px;
            }
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Prevent horizontal scroll on small screens */
        body {
            overflow-x: hidden;
        }

        /* Enhanced sidebar for mobile */
        @media (max-width: 768px) {
            #sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
            }
            
            #sidebar.open {
                transform: translateX(0);
            }
        }

        /* Optimize text readability on mobile */
        @media (max-width: 640px) {
            body {
                font-size: 14px;
                line-height: 1.5;
            }
        }

        /* Loading states */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        /* Animation optimizations */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Enhanced Navigation with Better Mobile Optimization -->
    <nav class="navbar-gradient shadow-2xl border-b border-primary/20 sticky top-0 z-50">
        <div class="max-w-full mx-auto px-3 sm:px-4 lg:px-6">
            <div class="flex justify-between items-center h-12 sm:h-14 md:h-16">
                <!-- Left side - Logo and Brand -->
                <div class="flex items-center space-x-2 sm:space-x-3 md:space-x-4">
                    <!-- Mobile sidebar toggle -->
                    <button id="sidebar-menu-btn" class="md:hidden text-white hover:text-gray-200 focus:outline-none p-1.5 sm:p-2 rounded-lg hover:bg-white/10 transition-all duration-200">
                        <i class="fas fa-bars text-base sm:text-lg"></i>
                    </button>
                    
                    <!-- Brand -->
                    <div class="flex items-center space-x-1.5 sm:space-x-2">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 md:w-10 md:h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-white text-sm sm:text-lg md:text-xl"></i>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="navbar-brand text-white font-bold text-sm sm:text-base lg:text-xl">IPT System</h1>
                            <p class="text-xs text-gray-200 hidden lg:block">Industrial Practical Training</p>
                        </div>
                        <h1 class="sm:hidden text-white font-bold text-xs">IPT</h1>
                    </div>
                </div>

                <!-- Center - Page breadcrumb (hidden on mobile) -->
                <div class="hidden lg:flex items-center space-x-2 text-white/80">
                    <i class="fas fa-home text-sm"></i>
                    <span class="text-sm">/</span>
                    <span class="text-sm font-medium">Dashboard</span>
                </div>

                <!-- Right side - User menu and actions -->
                <div class="flex items-center space-x-1 sm:space-x-2 md:space-x-4">
                    <!-- Notifications (hidden on small mobile) -->
                    <div class="hidden sm:flex items-center space-x-1 sm:space-x-2">
                        <?php if ($stats['pending_feedback'] > 0): ?>
                        <button class="relative p-1.5 sm:p-2 text-white hover:text-gray-200 hover:bg-white/10 rounded-lg transition-all duration-200">
                            <i class="fas fa-bell text-sm sm:text-lg"></i>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 sm:w-5 sm:h-5 flex items-center justify-center animate-pulse text-xs">
                                <?php echo $stats['pending_feedback']; ?>
                            </span>
                        </button>
                        <?php endif; ?>
                        
                        <!-- Quick access to profile -->
                        <a href="student_profile.php" class="hidden md:flex items-center space-x-2 px-2 sm:px-3 py-1.5 sm:py-2 bg-white/10 hover:bg-white/20 rounded-lg transition-all duration-200">
                            <?php if (!empty($profile_photo) && file_exists($profile_photo)): ?>
                                <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile" class="w-5 h-5 sm:w-6 sm:h-6 rounded-full object-cover">
                            <?php else: ?>
                                <div class="w-5 h-5 sm:w-6 sm:h-6 bg-white/20 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-white text-xs"></i>
                                </div>
                            <?php endif; ?>
                            <span class="text-xs sm:text-sm text-white hidden lg:inline">Profile</span>
                        </a>
                    </div>

                    <!-- Mobile user menu button -->
                    <button id="mobile-menu-btn" class="md:hidden flex items-center space-x-1.5 px-1.5 py-1 bg-white/10 hover:bg-white/20 rounded-lg transition-all duration-200">
                        <?php if (!empty($profile_photo) && file_exists($profile_photo)): ?>
                            <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile" class="w-5 h-5 rounded-full object-cover">
                        <?php else: ?>
                            <div class="w-5 h-5 bg-white/20 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white text-xs"></i>
                            </div>
                        <?php endif; ?>
                        <i class="fas fa-chevron-down text-white text-xs"></i>
                    </button>

                    <!-- Desktop user menu -->
                    <div class="hidden md:flex items-center space-x-2 sm:space-x-3">
                        <!-- User info -->
                        <div class="text-right hidden lg:block">
                            <div class="text-xs sm:text-sm font-medium text-white">
                                <?php echo htmlspecialchars($student_name); ?>
                            </div>
                            <?php if (!empty($reg_number)): ?>
                                <div class="text-xs text-gray-200">
                                    <?php echo htmlspecialchars($reg_number); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Profile picture -->
                        <div class="relative">
                            <?php if (!empty($profile_photo) && file_exists($profile_photo)): ?>
                                <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile" 
                                     class="w-6 h-6 sm:w-8 sm:h-8 rounded-full object-cover border-2 border-white/30 hover:border-white/60 transition-all duration-200">
                            <?php else: ?>
                                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gradient-to-br from-white/20 to-white/10 rounded-full flex items-center justify-center border-2 border-white/30 hover:border-white/60 transition-all duration-200">
                                    <i class="fas fa-user text-white text-xs sm:text-sm"></i>
                                </div>
                            <?php endif; ?>
                            <div class="absolute -bottom-0.5 -right-0.5 w-2 h-2 sm:w-3 sm:h-3 bg-green-400 border-2 border-white rounded-full"></div>
                        </div>

                        <!-- Logout -->
                        <a href="student_logout.php" class="flex items-center space-x-1 sm:space-x-2 px-2 sm:px-3 py-1.5 sm:py-2 bg-red-500/20 hover:bg-red-500/30 text-white rounded-lg transition-all duration-200 border border-red-500/30 hover:border-red-500/50">
                            <i class="fas fa-sign-out-alt text-xs sm:text-sm"></i>
                            <span class="hidden xl:inline text-xs sm:text-sm">Logout</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Mobile menu dropdown -->
            <div id="mobile-menu" class="md:hidden hidden bg-primary/95 backdrop-blur-sm border-t border-white/10 rounded-b-lg">
                <div class="px-3 py-2 space-y-2">
                    <!-- User info -->
                    <div class="flex items-center space-x-2 pb-2 border-b border-white/10">
                        <?php if (!empty($profile_photo) && file_exists($profile_photo)): ?>
                            <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile" 
                                 class="w-8 h-8 rounded-full object-cover border-2 border-white/30">
                        <?php else: ?>
                            <div class="w-8 h-8 bg-gradient-to-br from-white/20 to-white/10 rounded-full flex items-center justify-center border-2 border-white/30">
                                <i class="fas fa-user text-white text-sm"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <div class="text-sm font-medium text-white">
                                <?php echo htmlspecialchars($student_name); ?>
                            </div>
                            <?php if (!empty($reg_number)): ?>
                                <div class="text-xs text-gray-200">
                                    <?php echo htmlspecialchars($reg_number); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quick actions -->
                    <div class="space-y-1">
                        <a href="student_profile.php" class="flex items-center space-x-2 px-2 py-1.5 text-white hover:bg-white/10 rounded-lg transition-colors">
                            <i class="fas fa-user-edit w-4 text-sm"></i>
                            <span class="text-sm">Edit Profile</span>
                        </a>
                        
                        <?php if ($stats['pending_feedback'] > 0): ?>
                        <a href="student_feedback.php" class="flex items-center space-x-2 px-2 py-1.5 text-white hover:bg-white/10 rounded-lg transition-colors">
                            <i class="fas fa-bell w-4 text-sm"></i>
                            <span class="text-sm">Notifications</span>
                            <span class="ml-auto bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full">
                                <?php echo $stats['pending_feedback']; ?>
                            </span>
                        </a>
                        <?php endif; ?>
                        
                        <a href="change_password.php" class="flex items-center space-x-2 px-2 py-1.5 text-white hover:bg-white/10 rounded-lg transition-colors">
                            <i class="fas fa-lock w-4 text-sm"></i>
                            <span class="text-sm">Change Password</span>
                        </a>
                        
                        <div class="border-t border-white/10 pt-1">
                            <a href="student_logout.php" class="flex items-center space-x-2 px-2 py-1.5 text-red-300 hover:bg-red-500/10 rounded-lg transition-colors">
                                <i class="fas fa-sign-out-alt w-4 text-sm"></i>
                                <span class="text-sm">Sign Out</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Layout Container -->
    <div class="flex flex-col md:flex-row min-h-screen bg-gray-50">
        <!-- Enhanced Sidebar - Hidden on mobile, shown as overlay when menu is open -->
        <div id="sidebar" class="fixed md:relative inset-y-0 left-0 z-40 w-72 bg-gradient-to-br from-slate-800 via-slate-700 to-slate-900 shadow-2xl transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out md:block overflow-y-auto">
            <!-- Close button for mobile -->
            <div class="md:hidden flex justify-end p-4">
                <button id="close-sidebar" class="text-gray-300 hover:text-white transition-colors p-2 rounded-lg hover:bg-white/10">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Enhanced Profile Section -->
            <div class="p-6 bg-gradient-to-r from-primary/30 to-secondary/30 border-b border-slate-600">
                <div class="text-center mb-6">
                    <div class="relative inline-block mb-4">
                        <?php if (!empty($profile_photo) && file_exists($profile_photo)): ?>
                            <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile Photo" 
                                 class="w-20 h-20 rounded-full border-4 border-primary/60 object-cover shadow-xl hover:scale-105 transition-transform duration-300 profile-glow">
                        <?php else: ?>
                            <div class="w-20 h-20 rounded-full border-4 border-primary/60 bg-gradient-to-br from-primary via-secondary to-accent flex items-center justify-center shadow-xl hover:scale-105 transition-transform duration-300 profile-glow">
                                <i class="fas fa-user-graduate text-white text-2xl"></i>
                            </div>
                        <?php endif; ?>
                        <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-400 border-4 border-slate-800 rounded-full animate-pulse shadow-lg">
                            <i class="fas fa-check text-green-800 text-xs absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"></i>
                        </div>
                    </div>
                    
                    <div class="text-white">
                        <h3 class="text-lg font-bold text-slate-100 mb-1"><?php echo htmlspecialchars($student_name); ?></h3>
                        <?php if (!empty($reg_number)): ?>
                            <p class="text-sm text-slate-300 mb-1"><?php echo htmlspecialchars($reg_number); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($student_email)): ?>
                            <p class="text-xs text-slate-400 mb-3"><?php echo htmlspecialchars($student_email); ?></p>
                        <?php endif; ?>
                        <div class="inline-flex items-center bg-gradient-to-r from-primary to-secondary text-white px-3 py-1 rounded-full text-xs font-medium shadow-lg">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            <span>Active Student</span>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-slate-700/50 backdrop-blur-sm rounded-xl p-3 text-center hover:bg-slate-600/50 transition-all duration-300 hover:scale-105">
                        <div class="text-primary mb-1">
                            <i class="fas fa-file-alt text-lg"></i>
                        </div>
                        <div class="text-white text-sm font-semibold"><?php echo $stats['applications']; ?></div>
                        <div class="text-slate-300 text-xs">Applications</div>
                    </div>
                    <div class="bg-slate-700/50 backdrop-blur-sm rounded-xl p-3 text-center hover:bg-slate-600/50 transition-all duration-300 hover:scale-105">
                        <div class="text-secondary mb-1">
                            <i class="fas fa-clipboard-list text-lg"></i>
                        </div>
                        <div class="text-white text-sm font-semibold"><?php echo $stats['reports']; ?></div>
                        <div class="text-slate-300 text-xs">Reports</div>
                    </div>
                </div>
                
                <!-- Progress Indicator -->
                <?php if (!empty($college_name) && !empty($department)): ?>
                <div class="bg-slate-700/30 rounded-lg p-3">
                    <div class="text-slate-300 text-xs mb-1">Current Training</div>
                    <div class="text-white text-sm font-medium"><?php echo htmlspecialchars($college_name); ?></div>
                    <div class="text-slate-400 text-xs"><?php echo htmlspecialchars($department); ?></div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Navigation Menu -->
            <div class="p-4">
                <nav class="space-y-2">
                    <!-- Dashboard -->
                    <a href="student_dashboard.php" class="flex items-center px-4 py-3 text-white bg-gradient-to-r from-primary to-secondary rounded-xl hover:from-secondary hover:to-accent transition-all duration-300 shadow-lg group">
                        <div class="flex items-center justify-center w-10 h-10 bg-white/20 rounded-lg mr-3 group-hover:bg-white/30 transition-colors">
                            <i class="fas fa-tachometer-alt text-white"></i>
                        </div>
                        <span class="font-medium">Dashboard</span>
                        <i class="fas fa-chevron-right ml-auto opacity-60 group-hover:opacity-100 transition-opacity"></i>
                    </a>
                    
                    <!-- Profile -->
                    <a href="student_profile.php" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700/50 rounded-xl transition-all duration-300 group">
                        <div class="flex items-center justify-center w-10 h-10 bg-slate-600/50 rounded-lg mr-3 group-hover:bg-primary/50 transition-colors">
                            <i class="fas fa-user text-slate-300 group-hover:text-white"></i>
                        </div>
                        <span class="font-medium">My Profile</span>
                        <i class="fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-60 transition-opacity"></i>
                    </a>
                    
                    <!-- Applications -->
                    <a href="student_applications.php" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700/50 rounded-xl transition-all duration-300 group">
                        <div class="flex items-center justify-center w-10 h-10 bg-slate-600/50 rounded-lg mr-3 group-hover:bg-blue-500/50 transition-colors">
                            <i class="fas fa-file-contract text-slate-300 group-hover:text-white"></i>
                        </div>
                        <span class="font-medium">Applications</span>
                        <?php if ($stats['applications'] > 0): ?>
                            <span class="ml-auto bg-blue-500 text-white text-xs px-2 py-1 rounded-full"><?php echo $stats['applications']; ?></span>
                        <?php endif; ?>
                        <i class="fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-60 transition-opacity"></i>
                    </a>
                    
                    <!-- Reports -->
                    <a href="student_reports.php" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700/50 rounded-xl transition-all duration-300 group">
                        <div class="flex items-center justify-center w-10 h-10 bg-slate-600/50 rounded-lg mr-3 group-hover:bg-green-500/50 transition-colors">
                            <i class="fas fa-chart-line text-slate-300 group-hover:text-white"></i>
                        </div>
                        <span class="font-medium">Reports</span>
                        <?php if ($stats['reports'] > 0): ?>
                            <span class="ml-auto bg-green-500 text-white text-xs px-2 py-1 rounded-full"><?php echo $stats['reports']; ?></span>
                        <?php endif; ?>
                        <i class="fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-60 transition-opacity"></i>
                    </a>
                    
                    <!-- Feedback -->
                    <a href="student_feedback.php" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700/50 rounded-xl transition-all duration-300 group">
                        <div class="flex items-center justify-center w-10 h-10 bg-slate-600/50 rounded-lg mr-3 group-hover:bg-purple-500/50 transition-colors">
                            <i class="fas fa-comments text-slate-300 group-hover:text-white"></i>
                        </div>
                        <span class="font-medium">Feedback</span>
                        <?php if ($stats['pending_feedback'] > 0): ?>
                            <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full animate-pulse"><?php echo $stats['pending_feedback']; ?></span>
                        <?php endif; ?>
                        <i class="fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-60 transition-opacity"></i>
                    </a>
                    
                    <!-- Documents -->
                    <a href="student_documents.php" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700/50 rounded-xl transition-all duration-300 group">
                        <div class="flex items-center justify-center w-10 h-10 bg-slate-600/50 rounded-lg mr-3 group-hover:bg-indigo-500/50 transition-colors">
                            <i class="fas fa-folder-open text-slate-300 group-hover:text-white"></i>
                        </div>
                        <span class="font-medium">Documents</span>
                        <i class="fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-60 transition-opacity"></i>
                    </a>
                    
                    <!-- Divider -->
                    <div class="border-t border-slate-600 my-4"></div>
                    
                    <!-- Settings -->
                    <a href="change_password.php" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700/50 rounded-xl transition-all duration-300 group">
                        <div class="flex items-center justify-center w-10 h-10 bg-slate-600/50 rounded-lg mr-3 group-hover:bg-orange-500/50 transition-colors">
                            <i class="fas fa-cog text-slate-300 group-hover:text-white"></i>
                        </div>
                        <span class="font-medium">Settings</span>
                        <i class="fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-60 transition-opacity"></i>
                    </a>
                    
                    <!-- Help -->
                    <a href="#" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700/50 rounded-xl transition-all duration-300 group">
                        <div class="flex items-center justify-center w-10 h-10 bg-slate-600/50 rounded-lg mr-3 group-hover:bg-cyan-500/50 transition-colors">
                            <i class="fas fa-question-circle text-slate-300 group-hover:text-white"></i>
                        </div>
                        <span class="font-medium">Help & Support</span>
                        <i class="fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-60 transition-opacity"></i>
                    </a>
                </nav>
            </div>
            
            <!-- Bottom Section -->
            <div class="absolute bottom-0 left-0 right-0 p-4 bg-slate-800/50 border-t border-slate-600">
                <a href="student_logout.php" class="flex items-center px-4 py-3 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-xl transition-all duration-300 group">
                    <div class="flex items-center justify-center w-10 h-10 bg-red-500/20 rounded-lg mr-3 group-hover:bg-red-500/30 transition-colors">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <span class="font-medium">Sign Out</span>
                    <i class="fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-60 transition-opacity"></i>
                </a>
            </div>
        </div>

        <!-- Sidebar overlay for mobile -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-30 md:hidden hidden transition-opacity duration-300"></div>
        
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col content-container">
            <!-- Page Header -->
            <div class="main-content px-3 sm:px-4 lg:px-6 py-3 sm:py-4 lg:py-6">
                <div class="page-header mb-4 sm:mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div class="mb-3 sm:mb-0">
                            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 mb-1 sm:mb-2">
                                Student Dashboard
                            </h1>
                            <p class="text-xs sm:text-sm text-gray-600">
                                Welcome to your IPT management portal
                            </p>
                        </div>
                        
                        <!-- Quick actions for larger screens -->
                        <div class="hidden sm:flex items-center space-x-2 sm:space-x-3">
                            <a href="student_applications.php" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-primary text-white rounded-lg hover:bg-secondary transition-colors text-xs sm:text-sm font-medium">
                                <i class="fas fa-plus mr-1 sm:mr-2 text-xs"></i>
                                <span class="hidden sm:inline">New </span>Application
                            </a>
                            <a href="student_reports.php" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-xs sm:text-sm font-medium">
                                <i class="fas fa-chart-line mr-1 sm:mr-2 text-xs"></i>
                                <span class="hidden sm:inline">Add </span>Report
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Stats Cards with Better Mobile Layout -->
                <div class="stats-grid grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4 mb-4 sm:mb-6">
                    <!-- Applications Card -->
                    <div class="stats-card bg-gradient-to-br from-blue-50 to-blue-100 overflow-hidden shadow-lg rounded-lg sm:rounded-xl border border-blue-200 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <div class="p-3 sm:p-4 lg:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg sm:rounded-xl flex items-center justify-center shadow-lg">
                                        <i class="fas fa-file-contract text-white text-sm sm:text-base lg:text-xl"></i>
                                    </div>
                                </div>
                                <div class="ml-3 sm:ml-4 lg:ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-xs sm:text-sm font-medium text-blue-600 truncate">Applications</dt>
                                        <dd class="text-lg sm:text-xl lg:text-2xl font-bold text-blue-900"><?php echo $stats['applications']; ?></dd>
                                        <dd class="text-xs text-blue-500 mt-0.5 sm:mt-1 hidden sm:block">Submitted & Pending</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-blue-50 px-3 sm:px-4 lg:px-6 py-1.5 sm:py-2">
                            <div class="text-xs text-blue-600">
                                <i class="fas fa-arrow-up mr-1"></i>
                                <a href="student_applications.php" class="hover:text-blue-800 transition-colors">View All</a>
                            </div>
                        </div>
                    </div>

                    <!-- Reports Card -->
                    <div class="stats-card bg-gradient-to-br from-green-50 to-green-100 overflow-hidden shadow-lg rounded-lg sm:rounded-xl border border-green-200 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <div class="p-3 sm:p-4 lg:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-lg sm:rounded-xl flex items-center justify-center shadow-lg">
                                        <i class="fas fa-chart-line text-white text-sm sm:text-base lg:text-xl"></i>
                                    </div>
                                </div>
                                <div class="ml-3 sm:ml-4 lg:ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-xs sm:text-sm font-medium text-green-600 truncate">Reports</dt>
                                        <dd class="text-lg sm:text-xl lg:text-2xl font-bold text-green-900"><?php echo $stats['reports']; ?></dd>
                                        <dd class="text-xs text-green-500 mt-0.5 sm:mt-1 hidden sm:block">Weekly & Monthly</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-green-50 px-3 sm:px-4 lg:px-6 py-1.5 sm:py-2">
                            <div class="text-xs text-green-600">
                                <i class="fas fa-plus mr-1"></i>
                                <a href="student_reports.php" class="hover:text-green-800 transition-colors">Create New</a>
                            </div>
                        </div>
                    </div>

                    <!-- Feedback Card -->
                    <div class="stats-card bg-gradient-to-br from-purple-50 to-purple-100 overflow-hidden shadow-lg rounded-lg sm:rounded-xl border border-purple-200 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <div class="p-3 sm:p-4 lg:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg sm:rounded-xl flex items-center justify-center shadow-lg">
                                        <i class="fas fa-comments text-white text-sm sm:text-base lg:text-xl"></i>
                                    </div>
                                </div>
                                <div class="ml-3 sm:ml-4 lg:ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-xs sm:text-sm font-medium text-purple-600 truncate">Feedback</dt>
                                        <dd class="text-lg sm:text-xl lg:text-2xl font-bold text-purple-900"><?php echo $stats['pending_feedback']; ?></dd>
                                        <dd class="text-xs text-purple-500 mt-0.5 sm:mt-1 hidden sm:block">Awaiting Response</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-purple-50 px-3 sm:px-4 lg:px-6 py-1.5 sm:py-2">
                            <div class="text-xs text-purple-600">
                                <i class="fas fa-bell mr-1"></i>
                                <a href="student_feedback.php" class="hover:text-purple-800 transition-colors">View Feedback</a>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Status Card -->
                    <div class="stats-card bg-gradient-to-br from-orange-50 to-orange-100 overflow-hidden shadow-lg rounded-lg sm:rounded-xl border border-orange-200 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <div class="p-3 sm:p-4 lg:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg sm:rounded-xl flex items-center justify-center shadow-lg">
                                        <i class="fas fa-user-check text-white text-sm sm:text-base lg:text-xl"></i>
                                    </div>
                                </div>
                                <div class="ml-3 sm:ml-4 lg:ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-xs sm:text-sm font-medium text-orange-600 truncate">Profile</dt>
                                        <dd class="text-lg sm:text-xl lg:text-2xl font-bold text-orange-900">Active</dd>
                                        <dd class="text-xs text-orange-500 mt-0.5 sm:mt-1 hidden sm:block">Complete & Verified</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-orange-50 px-3 sm:px-4 lg:px-6 py-1.5 sm:py-2">
                            <div class="text-xs text-orange-600">
                                <i class="fas fa-edit mr-1"></i>
                                <a href="student_profile.php" class="hover:text-orange-800 transition-colors">Update Profile</a>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- Mobile Quick Actions (visible only on mobile) -->
            <div class="sm:hidden mb-4">
                <div class="bg-white shadow rounded-lg p-3">
                    <h2 class="text-sm font-medium text-gray-900 mb-3">Quick Actions</h2>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="student_applications.php" class="inline-flex items-center justify-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-primary hover:bg-secondary transition-colors">
                            <i class="fas fa-plus mr-1 text-xs"></i>
                            New App
                        </a>
                        <a href="student_reports.php" class="inline-flex items-center justify-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-secondary hover:bg-accent transition-colors">
                            <i class="fas fa-edit mr-1 text-xs"></i>
                            Report
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions for larger screens -->
            <div class="hidden sm:block bg-white shadow rounded-lg p-4 md:p-6 mb-4 sm:mb-6">
                <h2 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Quick Actions</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">
                    <a href="student_applications.php" class="inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-secondary transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        New Application
                    </a>
                    <a href="student_reports.php" class="inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-secondary hover:bg-accent transition-colors">
                        <i class="fas fa-edit mr-2"></i>
                        Submit Report
                    </a>
                    <a href="student_profile.php" class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-user-edit mr-2"></i>
                        Update Profile
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white shadow rounded-lg p-3 sm:p-4 md:p-6">
                <h2 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Recent Activity</h2>
                <div class="flow-root">
                    <ul class="-mb-6 sm:-mb-8">
                        <li>
                            <div class="relative pb-6 sm:pb-8">
                                <div class="relative flex space-x-2 sm:space-x-3">
                                    <div>
                                        <span class="h-6 w-6 sm:h-8 sm:w-8 rounded-full bg-green-500 flex items-center justify-center ring-4 sm:ring-8 ring-white">
                                            <i class="fas fa-check text-white text-xs sm:text-sm"></i>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1 sm:pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-xs sm:text-sm text-gray-500">
                                                Profile created successfully. Complete your profile to start applying for training.
                                            </p>
                                        </div>
                                        <div class="text-right text-xs sm:text-sm whitespace-nowrap text-gray-500">
                                            <time datetime="<?php echo date('Y-m-d'); ?>">Today</time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <!-- Additional activity items could be added here -->
                        <li>
                            <div class="relative">
                                <div class="relative flex space-x-2 sm:space-x-3">
                                    <div>
                                        <span class="h-6 w-6 sm:h-8 sm:w-8 rounded-full bg-blue-500 flex items-center justify-center ring-4 sm:ring-8 ring-white">
                                            <i class="fas fa-user text-white text-xs sm:text-sm"></i>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1 sm:pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-xs sm:text-sm text-gray-500">
                                                Account registered and ready for IPT applications.
                                            </p>
                                        </div>
                                        <div class="text-right text-xs sm:text-sm whitespace-nowrap text-gray-500">
                                            <time datetime="<?php echo date('Y-m-d'); ?>">Today</time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const sidebarMenuBtn = document.getElementById('sidebar-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            const closeSidebarBtn = document.getElementById('close-sidebar');

            // Toggle mobile navigation menu (user menu)
            mobileMenuBtn.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });

            // Toggle sidebar on mobile
            sidebarMenuBtn.addEventListener('click', function() {
                sidebar.classList.remove('-translate-x-full');
                sidebarOverlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden'; // Prevent scrolling
            });

            // Close sidebar function
            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
                document.body.style.overflow = ''; // Restore scrolling
            }

            // Close sidebar when clicking links on mobile
            const sidebarLinks = document.querySelectorAll('#sidebar a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 768) {
                        setTimeout(closeSidebar, 150); // Small delay for better UX
                    }
                });
            });

            // Close sidebar overlay
            sidebarOverlay.addEventListener('click', closeSidebar);
            closeSidebarBtn.addEventListener('click', closeSidebar);

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    sidebar.classList.remove('-translate-x-full');
                    sidebarOverlay.classList.add('hidden');
                    mobileMenu.classList.add('hidden');
                    document.body.style.overflow = '';
                } else {
                    sidebar.classList.add('-translate-x-full');
                }
            });

            // Add loading animation to cards
            const cards = document.querySelectorAll('.bg-white.overflow-hidden.shadow');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease-out';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Add hover effects to sidebar links
            const navLinks = document.querySelectorAll('#sidebar nav a');
            navLinks.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(8px)';
                });
                
                link.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                });
            });

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                // Close sidebar with Escape key
                if (e.key === 'Escape') {
                    closeSidebar();
                }
            });

            // Add focus trap for accessibility when sidebar is open on mobile
            function trapFocus(e) {
                if (window.innerWidth < 768 && !sidebar.classList.contains('-translate-x-full')) {
                    const focusableElements = sidebar.querySelectorAll('button, a, input, select, textarea, [tabindex]:not([tabindex="-1"])');
                    const firstFocusableElement = focusableElements[0];
                    const lastFocusableElement = focusableElements[focusableElements.length - 1];

                    if (e.key === 'Tab') {
                        if (e.shiftKey) {
                            if (document.activeElement === firstFocusableElement) {
                                lastFocusableElement.focus();
                                e.preventDefault();
                            }
                        } else {
                            if (document.activeElement === lastFocusableElement) {
                                firstFocusableElement.focus();
                                e.preventDefault();
                            }
                        }
                    }
                }
            }

            document.addEventListener('keydown', trapFocus);
        });

        // Animate stats counters
        function animateStats() {
            const statNumbers = document.querySelectorAll('dd.text-2xl.font-bold');
            
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
    </script>
</body>
</html>
