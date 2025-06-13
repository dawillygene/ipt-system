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

// Get student information
$stmt = $con->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get existing application data (using current table structure)
$existing_application = null;
$stmt = $con->prepare("SELECT * FROM applications WHERE user_id = ?");
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
    <title>Student Applications - IPT System</title>
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
                        <i class="fas fa-arrow-left mr-1"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Training Applications</h1>
                <p class="mt-1 text-sm text-gray-600">Submit and manage your industrial training applications</p>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="text-center py-12">
                    <i class="fas fa-file-alt text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Training Applications</h3>
                    
                    <?php if ($existing_application): ?>
                        <div class="bg-green-50 border border-green-200 rounded-md p-4 max-w-md mx-auto mb-4">
                            <div class="flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-400 mr-2"></i>
                                <p class="text-sm text-green-800">You have an existing application on record</p>
                            </div>
                            <div class="mt-2 text-xs text-green-600">
                                <p><strong>Status:</strong> <?php echo htmlspecialchars($existing_application['status']); ?></p>
                                <p><strong>Department:</strong> <?php echo htmlspecialchars($existing_application['department']); ?></p>
                                <p><strong>Applied:</strong> <?php echo htmlspecialchars($existing_application['application_date']); ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4 max-w-md mx-auto mb-4">
                            <div class="flex items-center justify-center">
                                <i class="fas fa-info-circle text-blue-400 mr-2"></i>
                                <p class="text-sm text-blue-800">No applications found</p>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <p class="text-gray-500 mb-4">The application form is being updated to work with the new system structure.</p>
                    <p class="text-sm text-gray-400">Coming soon with enhanced features!</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
