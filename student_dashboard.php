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
                        <h1 class="text-xl font-bold">
                            <i class="fas fa-graduation-cap mr-2"></i>IPT System
                        </h1>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm">
                        <i class="fas fa-user mr-1"></i>
                        Welcome, <?php echo htmlspecialchars($student_name); ?>
                    </span>
                    <a href="student_logout.php" class="px-3 py-2 rounded-md text-sm font-medium bg-secondary hover:bg-accent transition-colors">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="flex">
        <div class="w-64 bg-white shadow-lg min-h-screen">
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

        <!-- Main Content -->
        <div class="flex-1 p-6">
            <!-- Page Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Student Dashboard</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Welcome to your Industrial Practical Training management portal
                </p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
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
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <a href="student_applications.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-secondary transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        New Application
                    </a>
                    <a href="student_reports.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-secondary hover:bg-accent transition-colors">
                        <i class="fas fa-edit mr-2"></i>
                        Submit Report
                    </a>
                    <a href="student_profile.php" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-user-edit mr-2"></i>
                        Update Profile
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white shadow rounded-lg p-6">
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
</body>
</html>
