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
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/uploads/reports/';
                if (!is_dir($upload_dir)) {
                    if (!mkdir($upload_dir, 0755, true)) {
                        $errors[] = 'Failed to create upload directory. Please contact administrator.';
                    }
                }
                
                if (empty($errors)) {
                    $file_extension = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        // Check file size (5MB limit)
                        if ($_FILES['attachment']['size'] <= 5 * 1024 * 1024) {
                            $filename = 'report_' . $student_id . '_' . time() . '.' . $file_extension;
                            $attachment_path = 'uploads/reports/' . $filename; // Relative path for database
                            $full_path = $upload_dir . $filename; // Full path for file operations
                            
                            if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $full_path)) {
                                $errors[] = 'Failed to upload attachment. Please check file permissions.';
                                $attachment_path = NULL;
                            }
                        } else {
                            $errors[] = 'File size too large. Maximum allowed size is 5MB.';
                        }
                    } else {
                        $errors[] = 'Invalid file type. Allowed: PDF, DOC, DOCX, JPG, JPEG, PNG';
                    }
                }
            } else {
                // Handle other upload errors
                switch ($_FILES['attachment']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $errors[] = 'File size exceeds the maximum allowed limit.';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $errors[] = 'File upload was interrupted. Please try again.';
                        break;
                    default:
                        $errors[] = 'File upload failed. Please try again.';
                        break;
                }
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <a href="student_logout.php" class="px-3 py-2 rounded-md text-sm font-medium bg-red-600 hover:bg-red-700 transition-colors">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Training Reports</h1>
            <p class="text-gray-600 mt-2">Create and manage your daily, weekly, and monthly training reports</p>
        </div>

        <!-- Tab Navigation -->
        <div class="mb-6">
            <nav class="flex space-x-8">
                <a href="#create" id="tab-create" class="tab-link border-primary text-primary border-b-2 py-2 px-1 text-sm font-medium">
                    <i class="fas fa-plus-circle mr-2"></i>Create Report
                </a>
                <a href="#history" id="tab-history" class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-2 px-1 text-sm font-medium">
                    <i class="fas fa-history mr-2"></i>Report History
                </a>
            </nav>
        </div>

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

        <!-- Create Report Tab -->
        <div id="content-create" class="tab-content">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    <?php echo $edit_report ? 'Edit Report' : 'Create New Report'; ?>
                </h3>
                
                <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="submit_report">
                    <?php if ($edit_report): ?>
                        <input type="hidden" name="report_id" value="<?php echo $edit_report['report_id']; ?>">
                    <?php endif; ?>
                    
                    <!-- Report Type and Basic Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="report_type" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-1 text-primary"></i>Report Type
                            </label>
                            <select id="report_type" name="report_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="daily" <?php echo ($edit_report && $edit_report['report_type'] === 'daily') ? 'selected' : ''; ?>>Daily Report</option>
                                <option value="weekly" <?php echo ($edit_report && $edit_report['report_type'] === 'weekly') ? 'selected' : ''; ?>>Weekly Report</option>
                                <option value="monthly" <?php echo ($edit_report && $edit_report['report_type'] === 'monthly') ? 'selected' : ''; ?>>Monthly Report</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="report_date" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar mr-1 text-primary"></i>Report Date
                            </label>
                            <input type="date" id="report_date" name="report_date" required
                                   value="<?php echo $edit_report ? htmlspecialchars($edit_report['report_date']) : date('Y-m-d'); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <!-- Week/Month Number -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="period-fields">
                        <div id="week-field" style="display: none;">
                            <label for="week_number" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-hashtag mr-1 text-primary"></i>Week Number
                            </label>
                            <input type="number" id="week_number" name="week_number" min="1" max="52"
                                   value="<?php echo $edit_report ? htmlspecialchars($edit_report['week_number']) : ''; ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        
                        <div id="month-field" style="display: none;">
                            <label for="month_number" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-hashtag mr-1 text-primary"></i>Month Number
                            </label>
                            <input type="number" id="month_number" name="month_number" min="1" max="12"
                                   value="<?php echo $edit_report ? htmlspecialchars($edit_report['month_number']) : ''; ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <!-- Report Title -->
                    <div>
                        <label for="report_title" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-heading mr-1 text-primary"></i>Report Title
                        </label>
                        <input type="text" id="report_title" name="report_title" required
                               value="<?php echo $edit_report ? htmlspecialchars($edit_report['report_title']) : ''; ?>"
                               placeholder="e.g., Daily Training Report - Database Management"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Activities Completed -->
                    <div>
                        <label for="activities_completed" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tasks mr-1 text-primary"></i>Activities Completed
                        </label>
                        <textarea id="activities_completed" name="activities_completed" rows="4" required
                                  placeholder="Describe the activities you completed during this period..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"><?php echo $edit_report ? htmlspecialchars($edit_report['activities_completed']) : ''; ?></textarea>
                    </div>

                    <!-- Skills Acquired -->
                    <div>
                        <label for="skills_acquired" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lightbulb mr-1 text-primary"></i>Skills Acquired
                        </label>
                        <textarea id="skills_acquired" name="skills_acquired" rows="3"
                                  placeholder="What new skills or knowledge did you gain?"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"><?php echo $edit_report ? htmlspecialchars($edit_report['skills_acquired']) : ''; ?></textarea>
                    </div>

                    <!-- Challenges Faced -->
                    <div>
                        <label for="challenges_faced" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-exclamation-circle mr-1 text-primary"></i>Challenges Faced
                        </label>
                        <textarea id="challenges_faced" name="challenges_faced" rows="3"
                                  placeholder="Describe any challenges or difficulties encountered..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"><?php echo $edit_report ? htmlspecialchars($edit_report['challenges_faced']) : ''; ?></textarea>
                    </div>

                    <!-- Detailed Report Content -->
                    <div>
                        <label for="report_content" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-file-alt mr-1 text-primary"></i>Detailed Report Content
                        </label>
                        <textarea id="report_content" name="report_content" rows="10" required
                                  placeholder="Provide a detailed description of your training experience..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"><?php echo $edit_report ? htmlspecialchars($edit_report['report_content']) : ''; ?></textarea>
                    </div>

                    <!-- File Attachment -->
                    <div>
                        <label for="attachment" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-paperclip mr-1 text-primary"></i>Attachment (Optional)
                        </label>
                        <input type="file" id="attachment" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        <p class="text-sm text-gray-500 mt-1">Supported formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max: 5MB)</p>
                        <?php if ($edit_report && $edit_report['attachment_path']): ?>
                            <div class="mt-2 text-sm text-gray-600">
                                Current attachment: <a href="<?php echo htmlspecialchars($edit_report['attachment_path']); ?>" target="_blank" class="text-primary hover:underline">View File</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-6">
                        <button type="submit" name="submit_status" value="draft" 
                                class="px-6 py-3 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                            <i class="fas fa-save mr-2"></i>Save as Draft
                        </button>
                        <button type="submit" name="submit_status" value="submitted"
                                class="px-6 py-3 bg-primary text-white rounded-md hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-primary transition-colors">
                            <i class="fas fa-paper-plane mr-2"></i>Submit Report
                        </button>
                        <?php if ($edit_report): ?>
                            <a href="student_reports.php" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors text-center">
                                <i class="fas fa-times mr-2"></i>Cancel Edit
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report History Tab -->
        <div id="content-history" class="tab-content hidden">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Report History</h3>
                
                <?php if (empty($reports)): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-file-alt text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-500">No reports found. Create your first report above.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($reports as $report): ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($report['report_title']); ?></h4>
                                        <div class="flex items-center space-x-4 text-sm text-gray-600 mt-2">
                                            <span><i class="fas fa-calendar mr-1"></i><?php echo date('M d, Y', strtotime($report['report_date'])); ?></span>
                                            <span><i class="fas fa-tag mr-1"></i><?php echo ucfirst($report['report_type']); ?></span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                <?php echo $report['status'] === 'submitted' ? 'bg-blue-100 text-blue-800' : 
                                                          ($report['status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                                                          ($report['status'] === 'needs_revision' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')); ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $report['status'])); ?>
                                            </span>
                                        </div>
                                        <p class="text-gray-700 mt-2 line-clamp-2"><?php echo htmlspecialchars(substr($report['activities_completed'], 0, 150)) . '...'; ?></p>
                                    </div>
                                    <div class="flex space-x-2 ml-4">
                                        <a href="?edit=<?php echo $report['report_id']; ?>" class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                            <i class="fas fa-edit mr-1"></i>Edit
                                        </a>
                                        <?php if ($report['status'] === 'draft'): ?>
                                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this report?');">
                                                <input type="hidden" name="action" value="delete_report">
                                                <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                                <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm">
                                                    <i class="fas fa-trash mr-1"></i>Delete
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if ($report['attachment_path']): ?>
                                    <div class="mt-3 pt-3 border-t border-gray-200">
                                        <a href="<?php echo htmlspecialchars($report['attachment_path']); ?>" target="_blank" class="text-primary hover:underline text-sm">
                                            <i class="fas fa-paperclip mr-1"></i>View Attachment
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($report['supervisor_comments']): ?>
                                    <div class="mt-3 pt-3 border-t border-gray-200">
                                        <p class="text-sm text-gray-600"><strong>Supervisor Comments:</strong></p>
                                        <p class="text-sm text-gray-700 mt-1"><?php echo htmlspecialchars($report['supervisor_comments']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // SweetAlert notifications
        <?php if (!empty($errors)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Please fix the following errors:',
                html: '<?php echo "• " . implode("<br>• ", array_map(function($error) { return htmlspecialchars($error, ENT_QUOTES, "UTF-8"); }, $errors)); ?>',
                confirmButtonColor: '#dc2626'
            });
        <?php endif; ?>

        <?php if ($success): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?php echo addslashes(htmlspecialchars($success, ENT_QUOTES, "UTF-8")); ?>',
                confirmButtonColor: '#07442d'
            });
        <?php endif; ?>

        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active classes
                    tabLinks.forEach(l => {
                        l.classList.remove('border-primary', 'text-primary');
                        l.classList.add('border-transparent', 'text-gray-500');
                    });
                    
                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                    });
                    
                    // Add active classes
                    this.classList.remove('border-transparent', 'text-gray-500');
                    this.classList.add('border-primary', 'text-primary');
                    
                    // Show corresponding content
                    const targetId = this.getAttribute('href').substring(1);
                    const targetContent = document.getElementById('content-' + targetId);
                    if (targetContent) {
                        targetContent.classList.remove('hidden');
                    }
                });
            });
            
            // Handle report type changes
            const reportType = document.getElementById('report_type');
            const weekField = document.getElementById('week-field');
            const monthField = document.getElementById('month-field');
            
            function handleReportTypeChange() {
                const type = reportType.value;
                weekField.style.display = type === 'weekly' ? 'block' : 'none';
                monthField.style.display = type === 'monthly' ? 'block' : 'none';
            }
            
            reportType.addEventListener('change', handleReportTypeChange);
            handleReportTypeChange(); // Initial setup
        });
    </script>
</body>
</html>
