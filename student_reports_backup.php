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

// Create reports table if it doesn't exist
$con->query("CREATE TABLE IF NOT EXISTS student_reports (
    report_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    report_type ENUM('daily', 'weekly', 'monthly') NOT NULL DEFAULT 'daily',
    report_title VARCHAR(255) NOT NULL,
    report_content TEXT NOT NULL,
    report_date DATE NOT NULL,
    week_number INT(11) NULL,
    month_number INT(11) NULL,
    activities_completed TEXT,
    skills_acquired TEXT,
    challenges_faced TEXT,
    supervisor_comments TEXT NULL,
    status ENUM('draft', 'submitted', 'reviewed', 'approved', 'needs_revision') DEFAULT 'draft',
    attachment_path VARCHAR(500) NULL,
    submitted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
)");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'submit_report') {
        // Get form data
        $report_type = $_POST['report_type'] ?? 'daily';
        $report_title = trim($_POST['report_title'] ?? '');
        $report_content = trim($_POST['report_content'] ?? '');
        $report_date = $_POST['report_date'] ?? '';
        $week_number = !empty($_POST['week_number']) ? (int)$_POST['week_number'] : NULL;
        $month_number = !empty($_POST['month_number']) ? (int)$_POST['month_number'] : NULL;
        $activities_completed = trim($_POST['activities_completed'] ?? '');
        $skills_acquired = trim($_POST['skills_acquired'] ?? '');
        $challenges_faced = trim($_POST['challenges_faced'] ?? '');
        $submit_status = $_POST['submit_status'] ?? 'draft';
        
        // Validation
        if (empty($report_title)) $errors[] = 'Report title is required';
        if (empty($report_content)) $errors[] = 'Report content is required';
        if (empty($report_date)) $errors[] = 'Report date is required';
        if (empty($activities_completed)) $errors[] = 'Activities completed is required';
        
        // Handle file upload
        $attachment_path = NULL;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/reports/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $filename = 'report_' . $student_id . '_' . time() . '.' . $file_extension;
                $attachment_path = $upload_dir . $filename;
                
                if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $attachment_path)) {
                    $errors[] = 'Failed to upload attachment';
                    $attachment_path = NULL;
                }
            } else {
                $errors[] = 'Invalid file type. Allowed: PDF, DOC, DOCX, JPG, JPEG, PNG';
            }
        }
        
        // Insert or update report
        if (empty($errors)) {
            $report_id = $_POST['report_id'] ?? 0;
            
            if ($report_id > 0) {
                // Update existing report
                $stmt = $con->prepare("UPDATE student_reports SET 
                    report_type = ?, report_title = ?, report_content = ?, report_date = ?, 
                    week_number = ?, month_number = ?, activities_completed = ?, 
                    skills_acquired = ?, challenges_faced = ?, status = ?, 
                    attachment_path = COALESCE(?, attachment_path),
                    submitted_at = CASE WHEN ? = 'submitted' THEN CURRENT_TIMESTAMP ELSE submitted_at END,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE report_id = ? AND student_id = ?");
                $stmt->bind_param("ssssiisssssii", $report_type, $report_title, $report_content, 
                    $report_date, $week_number, $month_number, $activities_completed, 
                    $skills_acquired, $challenges_faced, $submit_status, $attachment_path, 
                    $submit_status, $report_id, $student_id);
            } else {
                // Insert new report
                $stmt = $con->prepare("INSERT INTO student_reports 
                    (student_id, report_type, report_title, report_content, report_date, 
                     week_number, month_number, activities_completed, skills_acquired, 
                     challenges_faced, status, attachment_path, submitted_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                            CASE WHEN ? = 'submitted' THEN CURRENT_TIMESTAMP ELSE NULL END)");
                $stmt->bind_param("issssiissssss", $student_id, $report_type, $report_title, 
                    $report_content, $report_date, $week_number, $month_number, 
                    $activities_completed, $skills_acquired, $challenges_faced, 
                    $submit_status, $attachment_path, $submit_status);
            }
            
            if ($stmt->execute()) {
                $success = $submit_status === 'submitted' ? 'Report submitted successfully!' : 'Report saved as draft!';
                if ($report_id === 0) {
                    $report_id = $con->insert_id;
                }
            } else {
                $errors[] = 'Failed to save report. Please try again.';
            }
            $stmt->close();
        }
    }
    
    if ($action === 'delete_report') {
        $report_id = (int)($_POST['report_id'] ?? 0);
        if ($report_id > 0) {
            $stmt = $con->prepare("DELETE FROM student_reports WHERE report_id = ? AND student_id = ?");
            $stmt->bind_param("ii", $report_id, $student_id);
            if ($stmt->execute()) {
                $success = 'Report deleted successfully!';
            } else {
                $errors[] = 'Failed to delete report.';
            }
            $stmt->close();
        }
    }
}

// Get all reports for this student
$reports_stmt = $con->prepare("SELECT * FROM student_reports WHERE student_id = ? ORDER BY report_date DESC, created_at DESC");
$reports_stmt->bind_param("i", $student_id);
$reports_stmt->execute();
$reports = $reports_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$reports_stmt->close();

// Get current edit report if specified
$edit_report = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_stmt = $con->prepare("SELECT * FROM student_reports WHERE report_id = ? AND student_id = ?");
    $edit_stmt->bind_param("ii", $edit_id, $student_id);
    $edit_stmt->execute();
    $edit_report = $edit_stmt->get_result()->fetch_assoc();
    $edit_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Reports - IPT System</title>
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
                <h1 class="text-2xl font-bold text-gray-900">Training Reports</h1>
                <p class="mt-1 text-sm text-gray-600">Submit and manage your industrial training reports</p>
            </div>

            <!-- Coming Soon Notice -->
            <div class="bg-blue-50 border border-blue-200 rounded-md p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-blue-800">Reports Module - Coming Soon</h3>
                        <p class="mt-2 text-sm text-blue-700">
                            The reports submission system is currently under development. You will be able to:
                        </p>
                        <ul class="mt-2 text-sm text-blue-700 list-disc pl-5">
                            <li>Submit weekly training reports</li>
                            <li>Upload report documents</li>
                            <li>Track report approval status</li>
                            <li>View supervisor feedback</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="student_dashboard.php" class="inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-secondary transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
                <a href="student_applications.php" class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-file-alt mr-2"></i>View Applications
                </a>
            </div>
        </div>
    </div>
</body>
</html>
