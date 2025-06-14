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

// Create feedback table if it doesn't exist
$con->query("CREATE TABLE IF NOT EXISTS student_feedback (
    feedback_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    feedback_type ENUM('supervisor', 'industrial', 'academic', 'system') NOT NULL DEFAULT 'supervisor',
    feedback_title VARCHAR(255) NOT NULL,
    feedback_content TEXT NOT NULL,
    supervisor_name VARCHAR(255),
    company_name VARCHAR(255),
    feedback_date DATE NOT NULL,
    rating INT(1) DEFAULT NULL CHECK (rating >= 1 AND rating <= 5),
    status ENUM('pending', 'read', 'responded') DEFAULT 'pending',
    response_content TEXT NULL,
    response_date TIMESTAMP NULL,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
)");

// Create notifications table if it doesn't exist
$con->query("CREATE TABLE IF NOT EXISTS student_notifications (
    notification_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    notification_type ENUM('feedback', 'report', 'application', 'system') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    related_id INT(11) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
)");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'submit_feedback') {
        // Get form data
        $feedback_type = $_POST['feedback_type'] ?? 'supervisor';
        $feedback_title = trim($_POST['feedback_title'] ?? '');
        $feedback_content = trim($_POST['feedback_content'] ?? '');
        $supervisor_name = trim($_POST['supervisor_name'] ?? '');
        $company_name = trim($_POST['company_name'] ?? '');
        $feedback_date = $_POST['feedback_date'] ?? '';
        $rating = !empty($_POST['rating']) ? (int)$_POST['rating'] : NULL;
        $is_public = isset($_POST['is_public']) ? 1 : 0;
        
        // Validation
        if (empty($feedback_title)) $errors[] = 'Feedback title is required';
        if (empty($feedback_content)) $errors[] = 'Feedback content is required';
        if (empty($feedback_date)) $errors[] = 'Feedback date is required';
        if ($rating && ($rating < 1 || $rating > 5)) $errors[] = 'Rating must be between 1 and 5';
        
        // Insert feedback
        if (empty($errors)) {
            $stmt = $con->prepare("INSERT INTO student_feedback 
                (student_id, feedback_type, feedback_title, feedback_content, 
                 supervisor_name, company_name, feedback_date, rating, is_public) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssii", $student_id, $feedback_type, $feedback_title, 
                $feedback_content, $supervisor_name, $company_name, $feedback_date, $rating, $is_public);
            
            if ($stmt->execute()) {
                $success = 'Feedback submitted successfully!';
            } else {
                $errors[] = 'Failed to submit feedback. Please try again.';
            }
            $stmt->close();
        }
    }
    
    if ($action === 'mark_read') {
        $notification_id = (int)($_POST['notification_id'] ?? 0);
        if ($notification_id > 0) {
            $stmt = $con->prepare("UPDATE student_notifications SET is_read = 1 WHERE notification_id = ? AND student_id = ?");
            $stmt->bind_param("ii", $notification_id, $student_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Get all feedback for this student
$feedback_stmt = $con->prepare("SELECT * FROM student_feedback WHERE student_id = ? ORDER BY feedback_date DESC, created_at DESC");
$feedback_stmt->bind_param("i", $student_id);
$feedback_stmt->execute();
$feedbacks = $feedback_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$feedback_stmt->close();

// Get notifications for this student
$notifications_stmt = $con->prepare("SELECT * FROM student_notifications WHERE student_id = ? ORDER BY created_at DESC LIMIT 10");
$notifications_stmt->bind_param("i", $student_id);
$notifications_stmt->execute();
$notifications = $notifications_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$notifications_stmt->close();

// Count unread notifications
$unread_stmt = $con->prepare("SELECT COUNT(*) as unread_count FROM student_notifications WHERE student_id = ? AND is_read = 0");
$unread_stmt->bind_param("i", $student_id);
$unread_stmt->execute();
$unread_count = $unread_stmt->get_result()->fetch_assoc()['unread_count'];
$unread_stmt->close();
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
                    <div class="relative">
                        <button id="notifications-btn" class="px-3 py-2 rounded-md text-sm font-medium bg-secondary hover:bg-accent transition-colors relative">
                            <i class="fas fa-bell mr-1"></i>Notifications
                            <?php if ($unread_count > 0): ?>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                    <?php echo $unread_count; ?>
                                </span>
                            <?php endif; ?>
                        </button>
                    </div>
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
            <h1 class="text-3xl font-bold text-gray-900">Feedback & Communication</h1>
            <p class="text-gray-600 mt-2">Submit feedback and view communication from supervisors</p>
        </div>

        <!-- Tab Navigation -->
        <div class="mb-6">
            <nav class="flex space-x-8">
                <a href="#submit" id="tab-submit" class="tab-link border-primary text-primary border-b-2 py-2 px-1 text-sm font-medium">
                    <i class="fas fa-comment-medical mr-2"></i>Submit Feedback
                </a>
                <a href="#history" id="tab-history" class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-2 px-1 text-sm font-medium">
                    <i class="fas fa-history mr-2"></i>Feedback History
                </a>
                <a href="#notifications" id="tab-notifications" class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-2 px-1 text-sm font-medium">
                    <i class="fas fa-bell mr-2"></i>Notifications
                    <?php if ($unread_count > 0): ?>
                        <span class="bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center ml-1"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
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

        <!-- Submit Feedback Tab -->
        <div id="content-submit" class="tab-content">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Submit New Feedback</h3>
                
                <form method="POST" action="" class="space-y-6">
                    <input type="hidden" name="action" value="submit_feedback">
                    
                    <!-- Feedback Type and Basic Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="feedback_type" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-tag mr-1 text-primary"></i>Feedback Type
                            </label>
                            <select id="feedback_type" name="feedback_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="supervisor">Supervisor Feedback</option>
                                <option value="industrial">Industrial Supervisor</option>
                                <option value="academic">Academic Supervisor</option>
                                <option value="system">System Feedback</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="feedback_date" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar mr-1 text-primary"></i>Feedback Date
                            </label>
                            <input type="date" id="feedback_date" name="feedback_date" required
                                   value="<?php echo date('Y-m-d'); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <!-- Supervisor/Company Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="supervisor_name" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user mr-1 text-primary"></i>Supervisor Name (Optional)
                            </label>
                            <input type="text" id="supervisor_name" name="supervisor_name"
                                   placeholder="e.g., Dr. John Doe"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-building mr-1 text-primary"></i>Company/Institution Name (Optional)
                            </label>
                            <input type="text" id="company_name" name="company_name"
                                   placeholder="e.g., ABC Technologies Ltd."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <!-- Feedback Title -->
                    <div>
                        <label for="feedback_title" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-heading mr-1 text-primary"></i>Feedback Title
                        </label>
                        <input type="text" id="feedback_title" name="feedback_title" required
                               placeholder="e.g., Weekly Performance Feedback"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Rating -->
                    <div>
                        <label for="rating" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-star mr-1 text-primary"></i>Overall Rating (Optional)
                        </label>
                        <select id="rating" name="rating" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select Rating</option>
                            <option value="5">⭐⭐⭐⭐⭐ Excellent (5/5)</option>
                            <option value="4">⭐⭐⭐⭐ Good (4/5)</option>
                            <option value="3">⭐⭐⭐ Average (3/5)</option>
                            <option value="2">⭐⭐ Poor (2/5)</option>
                            <option value="1">⭐ Very Poor (1/5)</option>
                        </select>
                    </div>

                    <!-- Feedback Content -->
                    <div>
                        <label for="feedback_content" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-comment-alt mr-1 text-primary"></i>Feedback Content
                        </label>
                        <textarea id="feedback_content" name="feedback_content" rows="8" required
                                  placeholder="Please provide detailed feedback about your training experience, supervisor interaction, learning outcomes, challenges faced, suggestions for improvement, etc."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                    </div>

                    <!-- Public Feedback Option -->
                    <div class="flex items-center">
                        <input type="checkbox" id="is_public" name="is_public" class="rounded border-gray-300 text-primary shadow-sm focus:border-primary focus:ring-primary">
                        <label for="is_public" class="ml-2 block text-sm text-gray-700">
                            <i class="fas fa-globe mr-1 text-primary"></i>Make this feedback public (can be viewed by other students)
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-6">
                        <button type="submit" class="px-6 py-3 bg-primary text-white rounded-md hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-primary transition-colors">
                            <i class="fas fa-paper-plane mr-2"></i>Submit Feedback
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Feedback History Tab -->
        <div id="content-history" class="tab-content hidden">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Feedback History</h3>
                
                <?php if (empty($feedbacks)): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-comment-alt text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-500">No feedback submitted yet. Submit your first feedback above.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($feedbacks as $feedback): ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($feedback['feedback_title']); ?></h4>
                                        <div class="flex items-center space-x-4 text-sm text-gray-600 mt-2">
                                            <span><i class="fas fa-calendar mr-1"></i><?php echo date('M d, Y', strtotime($feedback['feedback_date'])); ?></span>
                                            <span><i class="fas fa-tag mr-1"></i><?php echo ucfirst($feedback['feedback_type']); ?></span>
                                            <?php if ($feedback['rating']): ?>
                                                <span><i class="fas fa-star mr-1 text-yellow-400"></i><?php echo $feedback['rating']; ?>/5</span>
                                            <?php endif; ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                <?php echo $feedback['status'] === 'responded' ? 'bg-green-100 text-green-800' : 
                                                          ($feedback['status'] === 'read' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'); ?>">
                                                <?php echo ucfirst($feedback['status']); ?>
                                            </span>
                                            <?php if ($feedback['is_public']): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    <i class="fas fa-globe mr-1"></i>Public
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ($feedback['supervisor_name'] || $feedback['company_name']): ?>
                                            <div class="text-sm text-gray-600 mt-2">
                                                <?php if ($feedback['supervisor_name']): ?>
                                                    <span><i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($feedback['supervisor_name']); ?></span>
                                                <?php endif; ?>
                                                <?php if ($feedback['company_name']): ?>
                                                    <span class="ml-4"><i class="fas fa-building mr-1"></i><?php echo htmlspecialchars($feedback['company_name']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <p class="text-gray-700 mt-2"><?php echo htmlspecialchars(substr($feedback['feedback_content'], 0, 200)) . '...'; ?></p>
                                    </div>
                                </div>
                                
                                <?php if ($feedback['response_content']): ?>
                                    <div class="mt-4 pt-4 border-t border-gray-200 bg-gray-50 -mx-4 -mb-4 px-4 pb-4 rounded-b-lg">
                                        <p class="text-sm text-gray-600 mb-2">
                                            <strong><i class="fas fa-reply mr-1"></i>Response</strong>
                                            <span class="text-gray-500 ml-2"><?php echo date('M d, Y H:i', strtotime($feedback['response_date'])); ?></span>
                                        </p>
                                        <p class="text-sm text-gray-700"><?php echo htmlspecialchars($feedback['response_content']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Notifications Tab -->
        <div id="content-notifications" class="tab-content hidden">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Notifications</h3>
                
                <?php if (empty($notifications)): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-bell text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-500">No notifications yet.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="border border-gray-200 rounded-lg p-4 <?php echo !$notification['is_read'] ? 'bg-blue-50 border-blue-200' : ''; ?>">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900">
                                            <?php if (!$notification['is_read']): ?>
                                                <span class="w-2 h-2 bg-blue-500 rounded-full inline-block mr-2"></span>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($notification['title']); ?>
                                        </h4>
                                        <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                                        <p class="text-xs text-gray-500 mt-2"><?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?></p>
                                    </div>
                                    <?php if (!$notification['is_read']): ?>
                                        <form method="POST" class="ml-4">
                                            <input type="hidden" name="action" value="mark_read">
                                            <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                            <button type="submit" class="text-blue-600 hover:text-blue-800 text-sm">
                                                <i class="fas fa-check-circle mr-1"></i>Mark as Read
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
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
        });
    </script>
</body>
</html>
