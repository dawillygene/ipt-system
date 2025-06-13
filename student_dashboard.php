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
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-primary text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-lg sm:text-xl font-bold">
                            <i class="fas fa-graduation-cap mr-2"></i>IPT System
                        </h1>
                    </div>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center space-x-2">
                    <button id="sidebar-menu-btn" class="text-white hover:text-gray-300 focus:outline-none focus:text-gray-300">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <button id="mobile-menu-btn" class="text-white hover:text-gray-300 focus:outline-none focus:text-gray-300">
                        <i class="fas fa-user text-xl"></i>
                    </button>
                </div>
                
                <!-- Desktop menu -->
                <div class="hidden md:flex items-center space-x-4">
                    <span class="text-sm">
                        <i class="fas fa-user mr-1"></i>
                        <span class="hidden lg:inline">Welcome, </span><?php echo htmlspecialchars($student_name); ?>
                    </span>
                    <a href="student_logout.php" class="px-3 py-2 rounded-md text-sm font-medium bg-secondary hover:bg-accent transition-colors">
                        <i class="fas fa-sign-out-alt mr-1"></i>
                        <span class="hidden lg:inline">Logout</span>
                        <span class="lg:hidden">
                            <i class="fas fa-sign-out-alt"></i>
                        </span>
                    </a>
                </div>
            </div>
            
            <!-- Mobile menu dropdown -->
            <div id="mobile-menu" class="md:hidden hidden bg-primary border-t border-secondary">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <div class="px-3 py-2 text-sm text-gray-200">
                        <i class="fas fa-user mr-1"></i>
                        Welcome, <?php echo htmlspecialchars($student_name); ?>
                    </div>
                    <a href="student_logout.php" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-secondary transition-colors">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Layout -->
    <div class="flex flex-col md:flex-row">
        <!-- Sidebar - Hidden on mobile, shown as overlay when menu is open -->
        <div id="sidebar" class="fixed md:relative inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out md:block">
            <!-- Close button for mobile -->
            <div class="md:hidden flex justify-end p-4">
                <button id="close-sidebar" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="p-4">
                <div class="space-y-2">
                    <a href="dashboard.php" class="flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-lg">
                        <i class="fas fa-tachometer-alt mr-3"></i>
                        Dashboard
                    </a>
                    <a href="student_profile.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-user mr-3"></i>
                        Profile
                    </a>
                    <a href="student_applications.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-file-alt mr-3"></i>
                        Applications
                    </a>
                    <a href="student_reports.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-clipboard-list mr-3"></i>
                        Reports
                    </a>
                    <a href="student_feedback.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-comments mr-3"></i>
                        Feedback
                    </a>
                    <a href="student_documents.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-file-download mr-3"></i>
                        Documents
                    </a>
                </div>
            </div>
        </div>

        <!-- Sidebar overlay for mobile -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-40 md:hidden hidden"></div>
        
        <!-- Main Content -->
        <div class="flex-1 min-h-screen p-4 md:p-6">
            <!-- Page Header -->
            <div class="mb-6">
                <h1 class="text-xl md:text-2xl font-bold text-gray-900">Student Dashboard</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Welcome to your Industrial Practical Training management portal
                </p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-file-alt text-primary text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Applications</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo $stats['applications']; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-clipboard-list text-secondary text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Reports Submitted</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo $stats['reports']; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-comments text-accent text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Pending Feedback</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo $stats['pending_feedback']; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-user-check text-green-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Profile Status</dt>
                                    <dd class="text-lg font-medium text-green-600">Active</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg p-4 md:p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h2>
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
            <div class="bg-white shadow rounded-lg p-4 md:p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h2>
                <div class="flow-root">
                    <ul class="-mb-8">
                        <li>
                            <div class="relative pb-8">
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                            <i class="fas fa-check text-white text-sm"></i>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm text-gray-500">
                                                Profile created successfully. Complete your profile to start applying for training.
                                            </p>
                                        </div>
                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
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

    <!-- Mobile Menu JavaScript -->
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
            });

            // Close sidebar when clicking links on mobile
            const sidebarLinks = document.querySelectorAll('#sidebar a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 768) {
                        sidebar.classList.add('-translate-x-full');
                        sidebarOverlay.classList.add('hidden');
                    }
                });
            });

            // Close sidebar overlay
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            });

            closeSidebarBtn.addEventListener('click', function() {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    sidebar.classList.remove('-translate-x-full');
                    sidebarOverlay.classList.add('hidden');
                    mobileMenu.classList.add('hidden');
                } else {
                    sidebar.classList.add('-translate-x-full');
                }
            });
        });
    </script>
</body>
</html>
