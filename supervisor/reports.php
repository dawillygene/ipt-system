<?php
session_start();
require_once 'includes/supervisor_db.php';

// Check if user is logged in as supervisor
checkSupervisorSession();

$supervisor_id = $_SESSION['supervisor_id'];
$supervisor_name = $_SESSION['supervisor_name'];
$success = '';
$errors = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'review_report') {
        $report_id = (int)($_POST['report_id'] ?? 0);
        $review_status = $_POST['review_status'] ?? 'reviewed';
        $feedback_content = trim($_POST['feedback_content'] ?? '');
        $grade = trim($_POST['grade'] ?? '');
        
        // Validation
        if (empty($feedback_content)) {
            $errors[] = 'Feedback content is required';
        }
        
        if (empty($errors)) {
            // Check if review already exists
            $check_stmt = $con->prepare("SELECT review_id FROM report_reviews WHERE report_id = ? AND supervisor_id = ?");
            $check_stmt->bind_param("ii", $report_id, $supervisor_id);
            $check_stmt->execute();
            $existing_review = $check_stmt->get_result()->fetch_assoc();
            $check_stmt->close();
            
            if ($existing_review) {
                // Update existing review
                $update_stmt = $con->prepare("UPDATE report_reviews SET review_status = ?, feedback_content = ?, grade = ?, review_date = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE review_id = ?");
                $update_stmt->bind_param("sssi", $review_status, $feedback_content, $grade, $existing_review['review_id']);
                
                if ($update_stmt->execute()) {
                    $success = 'Report review updated successfully!';
                } else {
                    $errors[] = 'Failed to update review. Please try again.';
                }
                $update_stmt->close();
            } else {
                // Insert new review
                $insert_stmt = $con->prepare("INSERT INTO report_reviews (report_id, supervisor_id, review_status, feedback_content, grade, review_date) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
                $insert_stmt->bind_param("iisss", $report_id, $supervisor_id, $review_status, $feedback_content, $grade);
                
                if ($insert_stmt->execute()) {
                    $success = 'Report reviewed successfully!';
                } else {
                    $errors[] = 'Failed to submit review. Please try again.';
                }
                $insert_stmt->close();
            }
            
            // Update report status in student_reports table if needed
            if ($review_status === 'approved') {
                $con->prepare("UPDATE student_reports SET status = 'approved' WHERE report_id = ?")->execute([$report_id]);
            } elseif ($review_status === 'needs_revision') {
                $con->prepare("UPDATE student_reports SET status = 'needs_revision' WHERE report_id = ?")->execute([$report_id]);
            }
        }
    }
}

// Get filter parameters
$filter_student = $_GET['student'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_type = $_GET['type'] ?? '';

// Base parameters for the query
$base_params = [];
$base_param_types = '';

// Parameter for LEFT JOIN rr.supervisor_id
$base_params[] = $supervisor_id;
$base_param_types .= 'i';

$where_conditions = [];
$filter_params = [];
$filter_param_types = '';

// Always filter by the logged-in supervisor's assignments
$where_conditions[] = 'sa.supervisor_id = ?';
$filter_params[] = $supervisor_id;
$filter_param_types .= 'i';

if (!empty($filter_student)) {
    $where_conditions[] = 'sr.student_id = ?';
    $filter_params[] = $filter_student;
    $filter_param_types .= 'i';
}

if (!empty($filter_status)) {
    if ($filter_status === 'pending_review') {
        // Reports that have no review entry OR have a review entry by THIS supervisor that is 'pending'
        // For the IS NULL case, rr.supervisor_id = ? in the LEFT JOIN handles the supervisor context
        $where_conditions[] = '(rr.review_id IS NULL OR rr.review_status = \'pending\')';
        // No additional parameter needed here as rr.supervisor_id = ? is already in base_params for the JOIN condition
    } elseif ($filter_status === 'reviewed_by_me') {
        $where_conditions[] = 'rr.supervisor_id = ? AND rr.review_status IS NOT NULL AND rr.review_status != \'pending\'';
        $filter_params[] = $supervisor_id;
        $filter_param_types .= 'i';
    } else {
        $where_conditions[] = 'rr.review_status = ?';
        $filter_params[] = $filter_status;
        $filter_param_types .= 's';
    }
}

if (!empty($filter_type)) {
    $where_conditions[] = 'sr.report_type = ?';
    $filter_params[] = $filter_type;
    $filter_param_types .= 's';
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

$final_params = array_merge($base_params, $filter_params);
$final_param_types = $base_param_types . $filter_param_types;


// Get reports for review
$reports_query = "
    SELECT sr.*, s.full_name as student_name, s.reg_number, s.email as student_email,
           rr.review_id, rr.review_status, rr.feedback_content, rr.grade, rr.review_date
    FROM student_reports sr 
    JOIN students s ON sr.student_id = s.student_id 
    JOIN supervisor_assignments sa ON s.student_id = sa.student_id 
    LEFT JOIN report_reviews rr ON sr.report_id = rr.report_id AND rr.supervisor_id = ? -- Param 1 (from base_params): supervisor_id for the review itself
    $where_clause -- Contains sa.supervisor_id = ? (Param 2 from filter_params) and other filters
    ORDER BY sr.created_at DESC
";

$reports_stmt = $con->prepare($reports_query);

if (!$reports_stmt) {
    // Log error and die gracefully
    error_log("Prepare failed for reports_query: (" . $con->errno . ") " . $con->error);
    die("An error occurred while preparing report data. Please try again later.");
}

if (!empty($final_param_types)) {
    if (count($final_params) !== strlen($final_param_types)) {
        error_log("Parameter count mismatch in reports.php: Params: " . count($final_params) . ", Types: " . strlen($final_param_types));
        die("An error occurred due to parameter mismatch. Please contact support.");
    }
    $reports_stmt->bind_param($final_param_types, ...$final_params);
}

$reports_stmt->execute();
$reports_result = $reports_stmt->get_result();
if (!$reports_result) {
    error_log("Execute failed for reports_query: (" . $reports_stmt->errno . ") " . $reports_stmt->error);
    die("An error occurred while fetching report data. Please try again later.");
}
$reports = $reports_result->fetch_all(MYSQLI_ASSOC);
$reports_stmt->close();

// Get students for filter dropdown
$students_filter_stmt = $con->prepare("
    SELECT DISTINCT s.student_id, s.full_name 
    FROM students s 
    JOIN supervisor_assignments sa ON s.student_id = sa.student_id 
    WHERE sa.supervisor_id = ? AND sa.status = 'active'
    ORDER BY s.full_name
");
$students_filter_stmt->bind_param("i", $supervisor_id);
$students_filter_stmt->execute();
$students_for_filter = $students_filter_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$students_filter_stmt->close();

// Get specific report for detailed review
$review_report = null;
if (isset($_GET['review']) && is_numeric($_GET['review'])) {
    $report_id = (int)$_GET['review'];
    
    $review_stmt = $con->prepare("
        SELECT sr.*, s.full_name as student_name, s.reg_number, s.email as student_email,
               rr.review_id, rr.review_status, rr.feedback_content, rr.grade, rr.review_date
        FROM student_reports sr 
        JOIN students s ON sr.student_id = s.student_id 
        JOIN supervisor_assignments sa ON s.student_id = sa.student_id 
        LEFT JOIN report_reviews rr ON sr.report_id = rr.report_id AND rr.supervisor_id = ?
        WHERE sr.report_id = ? AND sa.supervisor_id = ?
    ");
    $review_stmt->bind_param("iii", $supervisor_id, $report_id, $supervisor_id);
    $review_stmt->execute();
    $review_report = $review_stmt->get_result()->fetch_assoc();
    $review_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Reports - IPT Supervisor Portal</title>
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
                        <i class="fas fa-chalkboard-teacher mr-2"></i>IPT Supervisor Portal
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm">Welcome, <?php echo htmlspecialchars($supervisor_name); ?></span>
                    <div class="relative">
                        <button id="menu-button" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-primary focus:ring-white">
                            <i class="fas fa-user-circle text-2xl"></i>
                        </button>
                        <div id="dropdown-menu" class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>Profile
                                </a>
                                <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-2"></i>Settings
                                </a>
                                <hr class="my-1">
                                <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Layout -->
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg min-h-screen">
            <div class="p-4">
                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-tachometer-alt mr-3"></i>
                        Dashboard
                    </a>
                    <a href="students.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-users mr-3"></i>
                        My Students
                    </a>
                    <a href="reports.php" class="flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-lg">
                        <i class="fas fa-file-alt mr-3"></i>
                        Review Reports
                    </a>
                    <a href="evaluations.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-star mr-3"></i>
                        Evaluations
                    </a>
                    <a href="meetings.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-calendar mr-3"></i>
                        Meetings
                    </a>
                    <a href="messages.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-comments mr-3"></i>
                        Messages
                    </a>
                    <hr class="my-2">
                    <a href="profile.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-user-cog mr-3"></i>
                        My Profile
                    </a>
                    <a href="settings.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-cogs mr-3"></i>
                        Settings
                    </a>
                    <hr class="my-2">
                    <a href="logout.php" class="flex items-center px-4 py-2 text-red-600 hover:bg-red-100 rounded-lg">
                        <i class="fas fa-sign-out-alt mr-3"></i>
                        Logout
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            <div class="container mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-primary">Review Student Reports</h1>
                    <!-- Filters will go here -->
                </div>

                <?php if ($success): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-md shadow-sm" role="alert">
                        <p class="font-bold">Success</p>
                        <p><?php echo htmlspecialchars($success); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-md shadow-sm" role="alert">
                        <p class="font-bold">Error</p>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Filter Form -->
                <form method="GET" action="reports.php" class="mb-6 bg-white p-4 rounded-lg shadow">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label for="filter_student" class="block text-sm font-medium text-gray-700">Student:</label>
                            <select name="student" id="filter_student" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                                <option value="">All Students</option>
                                <?php foreach ($assigned_students_for_filter as $student_item): ?>
                                    <option value="<?php echo $student_item['student_id']; ?>" <?php if ($filter_student == $student_item['student_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($student_item['full_name']); ?> (<?php echo htmlspecialchars($student_item['reg_number']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="filter_status" class="block text-sm font-medium text-gray-700">Review Status:</label>
                            <select name="status" id="filter_status" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                                <option value="">All Statuses</option>
                                <option value="pending_review" <?php if ($filter_status == 'pending_review') echo 'selected'; ?>>Pending My Review</option>
                                <option value="reviewed_by_me" <?php if ($filter_status == 'reviewed_by_me') echo 'selected'; ?>>Reviewed By Me (Not Pending)</option>
                                <option value="approved" <?php if ($filter_status == 'approved') echo 'selected'; ?>>Approved</option>
                                <option value="needs_revision" <?php if ($filter_status == 'needs_revision') echo 'selected'; ?>>Needs Revision</option>
                                <option value="rejected" <?php if ($filter_status == 'rejected') echo 'selected'; ?>>Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label for="filter_type" class="block text-sm font-medium text-gray-700">Report Type:</label>
                            <select name="type" id="filter_type" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                                <option value="">All Types</option>
                                <option value="daily" <?php if ($filter_type == 'daily') echo 'selected'; ?>>Daily</option>
                                <option value="weekly" <?php if ($filter_type == 'weekly') echo 'selected'; ?>>Weekly</option>
                                <option value="monthly" <?php if ($filter_type == 'monthly') echo 'selected'; ?>>Monthly</option>
                                <option value="final" <?php if ($filter_type == 'final') echo 'selected'; ?>>Final</option>
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="w-full bg-primary hover:bg-secondary text-white font-semibold py-2 px-4 rounded-md shadow-sm transition duration-300">
                                <i class="fas fa-filter mr-2"></i>Filter Reports
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Reports Table -->
                <div class="bg-white shadow-lg rounded-lg overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($reports as $report): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($report['report_title']); ?></div>
                                            <div class="text-sm text-gray-500">
                                                Report Date: <?php echo date('M d, Y', strtotime($report['report_date'])); ?>
                                            </div>
                                            <?php if ($report['attachment_path']): ?>
                                                <div class="text-xs text-gray-400">
                                                    <i class="fas fa-paperclip mr-1"></i>Has attachment
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($report['student_name']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($report['reg_number']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <?php echo ucfirst($report['report_type']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo date('M d, Y', strtotime($report['created_at'])); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo date('H:i', strtotime($report['created_at'])); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($report['review_status']): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                <?php echo $report['review_status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                                                          ($report['review_status'] === 'needs_revision' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'); ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $report['review_status'])); ?>
                                            </span>
                                            <?php if ($report['grade']): ?>
                                                <div class="text-xs text-gray-500 mt-1">Grade: <?php echo htmlspecialchars($report['grade']); ?></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                Pending Review
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="reports.php?review=<?php echo $report['report_id']; ?>" 
                                           class="text-primary hover:text-secondary">
                                            <i class="fas fa-eye mr-1"></i>
                                            <?php echo $report['review_status'] ? 'Edit Review' : 'Review'; ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Report Modal -->
    <div id="reviewReportModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-lg max-w-3xl w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900" id="review_report_title">Review Report</h2>
                <button onclick="closeReviewModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mb-4">
                <strong class="text-sm text-gray-700">Student:</strong>
                <p class="text-sm text-gray-900" id="review_student_name"></p>
            </div>
            <div class="mb-4">
                <strong class="text-sm text-gray-700">Report Content:</strong>
                <div class="mt-1 p-3 border border-gray-300 rounded-md bg-gray-50">
                    <p class="text-sm text-gray-900 whitespace-pre-wrap" id="review_report_content"></p>
                </div>
            </div>
            <div class="mb-4" id="review_report_attachment" style="display:none;">
                <strong class="text-sm text-gray-700">Attachment:</strong>
                <div class="mt-1">
                    <a id="review_report_attachment_link" href="#" target="_blank" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-paperclip mr-2"></i>View Attachment
                    </a>
                    <span id="review_report_attachment_text" class="text-sm text-gray-500"></span>
                </div>
            </div>
            <div class="mb-4">
                <strong class="text-sm text-gray-700">Current Status:</strong>
                <p class="text-sm text-gray-900" id="review_current_status"></p>
            </div>
            <div class="mb-4">
                <strong class="text-sm text-gray-700">Current Feedback:</strong>
                <p class="text-sm text-gray-900" id="review_current_feedback"></p>
            </div>
            <div class="mb-4">
                <strong class="text-sm text-gray-700">Current Grade:</strong>
                <p class="text-sm text-gray-900" id="review_current_grade"></p>
            </div>
            <div>
                <label for="review_status" class="block text-sm font-medium text-gray-700 mb-2">Update Review Status</label>
                <select id="review_status" name="review_status" class="block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    <option value="reviewed">Reviewed</option>
                    <option value="approved">Approved</option>
                    <option value="needs_revision">Needs Revision</option>
                </select>
            </div>
            <div class="mt-4">
                <label for="review_feedback_content" class="block text-sm font-medium text-gray-700 mb-2">Feedback Content</label>
                <textarea id="review_feedback_content" name="feedback_content" rows="4" class="block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm" placeholder="Enter your feedback here..."></textarea>
            </div>
            <div class="mt-4">
                <label for="review_grade" class="block text-sm font-medium text-gray-700 mb-2">Grade</label>
                <input type="text" id="review_grade" name="grade" class="block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm" placeholder="Enter grade (optional)">
            </div>
            <div class="mt-6 flex justify-end">
                <button onclick="closeReviewModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition duration-150 mr-2">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
                <button id="save_review_btn" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-primary transition-colors">
                    <i class="fas fa-paper-plane mr-2"></i>Submit Review
                </button>
            </div>
        </div>
    </div>

    <script>
        // JavaScript for dropdown menu
        const menuButton = document.getElementById('menu-button');
        const dropdownMenu = document.getElementById('dropdown-menu');

        if(menuButton && dropdownMenu) {
            menuButton.addEventListener('click', () => {
                dropdownMenu.classList.toggle('hidden');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (event) => {
                if (!menuButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                    dropdownMenu.classList.add('hidden');
                }
            });
        }

        // Modal handling for review
        function openReviewModal(reportId, studentName, reportTitle, reportContent, attachmentPath, currentStatus, currentFeedback, currentGrade) {
            document.getElementById('review_report_id').value = reportId;
            document.getElementById('review_student_name').textContent = studentName;
            document.getElementById('review_report_title').textContent = reportTitle;
            document.getElementById('review_report_content').innerHTML = reportContent; // Use innerHTML if content has HTML
            
            const attachmentLink = document.getElementById('review_report_attachment_link');
            const attachmentText = document.getElementById('review_report_attachment_text');
            if (attachmentPath) {
                attachmentLink.href = '../' + attachmentPath; // Assuming uploads is one level up from supervisor folder
                attachmentLink.classList.remove('hidden');
                attachmentText.classList.add('hidden');
            } else {
                attachmentLink.classList.add('hidden');
                attachmentText.classList.remove('hidden');
            }

            document.getElementById('review_status').value = currentStatus || 'pending_review';
            document.getElementById('review_feedback_content').value = currentFeedback || '';
            document.getElementById('review_grade').value = currentGrade || '';
            
            document.getElementById('reviewReportModal').classList.remove('hidden');
        }

        function closeReviewModal() {
            document.getElementById('reviewReportModal').classList.add('hidden');
        }
    </script>
</body>
</html>
