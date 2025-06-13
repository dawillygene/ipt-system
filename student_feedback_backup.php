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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Feedback - IPT System</title>
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
                    <h1 class="text-xl font-bold">
                        <i class="fas fa-graduation-cap mr-2"></i>IPT System
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm">Welcome, <?php echo htmlspecialchars($student_name); ?></span>
                    <a href="student_dashboard.php" class="px-3 py-2 rounded-md text-sm font-medium bg-secondary hover:bg-accent transition-colors">
                        <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                    </a>
                    <a href="student_logout.php" class="px-3 py-2 rounded-md text-sm font-medium bg-secondary hover:bg-accent transition-colors">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Feedback & Evaluation</h1>
                <p class="mt-1 text-sm text-gray-600">View feedback from supervisors and evaluation results</p>
            </div>

            <!-- Coming Soon Notice -->
            <div class="bg-amber-50 border border-amber-200 rounded-md p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-comments text-amber-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-amber-800">Feedback Module - Coming Soon</h3>
                        <p class="mt-2 text-sm text-amber-700">
                            The feedback and evaluation system is currently under development. You will be able to:
                        </p>
                        <ul class="mt-2 text-sm text-amber-700 list-disc pl-5">
                            <li>View supervisor feedback on your performance</li>
                            <li>Check evaluation scores and ratings</li>
                            <li>Respond to supervisor comments</li>
                            <li>Download evaluation certificates</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="student_dashboard.php" class="inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-secondary transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
                <a href="student_profile.php" class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-user mr-2"></i>Update Profile
                </a>
            </div>
        </div>
    </div>
</body>
</html>
