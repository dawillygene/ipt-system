<?php
session_start();
require_once 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Add new feedback
                $stmt = $con->prepare("INSERT INTO feedback (report_id, supervisor_id, feedback, rating) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iisi", 
                    $_POST['report_id'],
                    $_POST['supervisor_id'],
                    $_POST['feedback'],
                    $_POST['rating']
                );
                
                if ($stmt->execute()) {
                    $success_message = "Feedback added successfully!";
                } else {
                    $error_message = "Error adding feedback: " . $con->error;
                }
                $stmt->close();
                break;
                
            case 'update':
                // Update feedback
                $stmt = $con->prepare("UPDATE feedback SET report_id=?, supervisor_id=?, feedback=?, rating=? WHERE id=?");
                $stmt->bind_param("iisii",
                    $_POST['report_id'],
                    $_POST['supervisor_id'],
                    $_POST['feedback'],
                    $_POST['rating'],
                    $_POST['feedback_id']
                );
                
                if ($stmt->execute()) {
                    $success_message = "Feedback updated successfully!";
                } else {
                    $error_message = "Error updating feedback: " . $con->error;
                }
                $stmt->close();
                break;
                
            case 'delete':
                // Delete feedback
                $stmt = $con->prepare("DELETE FROM feedback WHERE id = ?");
                $stmt->bind_param("i", $_POST['feedback_id']);
                
                if ($stmt->execute()) {
                    $success_message = "Feedback deleted successfully!";
                } else {
                    $error_message = "Error deleting feedback: " . $con->error;
                }
                $stmt->close();
                break;
        }
    }
}

// Get all feedback with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
$search_params = [];
$param_types = '';

if (!empty($search)) {
    $search_condition = "WHERE f.feedback LIKE ? OR s.department LIKE ? OR r.week_number LIKE ?";
    $search_params = ["%$search%", "%$search%", "%$search%"];
    $param_types = 'sss';
}

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM feedback f 
              LEFT JOIN supervisors s ON f.supervisor_id = s.id 
              LEFT JOIN reports r ON f.report_id = r.id 
              $search_condition";
$count_stmt = $con->prepare($count_sql);
if (!empty($search_params)) {
    $count_stmt->bind_param($param_types, ...$search_params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);
$count_stmt->close();

// Get feedback data
$sql = "SELECT f.*, s.department as supervisor_name, CONCAT('Week ', r.week_number) as report_title 
        FROM feedback f 
        LEFT JOIN supervisors s ON f.supervisor_id = s.id 
        LEFT JOIN reports r ON f.report_id = r.id 
        $search_condition 
        ORDER BY f.created_at DESC 
        LIMIT ? OFFSET ?";
$stmt = $con->prepare($sql);

if (!empty($search_params)) {
    $param_types .= 'ii';
    $search_params[] = $limit;
    $search_params[] = $offset;
    $stmt->bind_param($param_types, ...$search_params);
} else {
    $stmt->bind_param('ii', $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
$feedback_list = [];
while ($row = $result->fetch_assoc()) {
    $feedback_list[] = $row;
}
$stmt->close();

// Get all supervisors for dropdown
$supervisors_sql = "SELECT id, department FROM supervisors ORDER BY department";
$supervisors_result = mysqli_query($con, $supervisors_sql);
$supervisors = [];
while ($row = mysqli_fetch_assoc($supervisors_result)) {
    $supervisors[] = $row;
}

// Get all reports for dropdown
$reports_sql = "SELECT id, week_number FROM reports ORDER BY week_number";
$reports_result = mysqli_query($con, $reports_sql);
$reports = [];
while ($row = mysqli_fetch_assoc($reports_result)) {
    $reports[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Feedback - IPT System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'admin-primary': '#07442d',
                        'admin-secondary': '#206f56',
                        'admin-accent': '#0f7b5a',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <?php include('./includes/sidebar.php'); ?>
    
    <!-- Main Content -->
    <main class="lg:ml-64 pt-16">
        <div class="p-6">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center flex-wrap gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Manage Feedback</h1>
                        <p class="text-gray-600 mt-1">View and manage feedback on reports</p>
                    </div>
                    <button type="button" 
                            onclick="openModal('addFeedbackModal')"
                            class="inline-flex items-center px-4 py-2 bg-admin-primary text-white rounded-lg hover:bg-admin-secondary transition-colors duration-200 shadow-md">
                        <i class="fas fa-plus mr-2"></i>
                        Add New Feedback
                    </button>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($success_message)): ?>
                <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo $success_message; ?>
                    <button type="button" onclick="this.parentElement.style.display='none'" class="ml-auto text-green-600 hover:text-green-800">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo $error_message; ?>
                    <button type="button" onclick="this.parentElement.style.display='none'" class="ml-auto text-red-600 hover:text-red-800">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Search and Stats -->
            <div class="mb-6 bg-white rounded-lg shadow-sm p-6">
                <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
                    <form method="GET" class="flex gap-2 flex-1 max-w-md">
                        <div class="relative flex-1">
                            <input type="text" 
                                   name="search" 
                                   placeholder="Search feedback..." 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-admin-primary text-white rounded-lg hover:bg-admin-secondary transition-colors duration-200">
                            Search
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="admin_feedback.php" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200">
                                Clear
                            </a>
                        <?php endif; ?>
                    </form>
                    <div class="text-sm text-gray-600">
                        <span class="bg-admin-primary text-white px-3 py-1 rounded-full">
                            Total Feedback: <?php echo $total_records; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Feedback Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supervisor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Feedback</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($feedback_list)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-comments text-4xl mb-4 block text-gray-300"></i>
                                        <p class="text-lg font-medium">No feedback found</p>
                                        <p class="text-sm">Add your first feedback to get started</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($feedback_list as $feedback): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            #<?php echo htmlspecialchars($feedback['id']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($feedback['report_title'] ?: 'Report #' . $feedback['report_id']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($feedback['supervisor_name'] ?: 'Supervisor #' . $feedback['supervisor_id']); ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                                            <div class="truncate" title="<?php echo htmlspecialchars($feedback['feedback']); ?>">
                                                <?php echo htmlspecialchars(substr($feedback['feedback'], 0, 50) . (strlen($feedback['feedback']) > 50 ? '...' : '')); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <?php 
                                                $rating = (int)$feedback['rating'];
                                                for ($i = 1; $i <= 5; $i++): 
                                                ?>
                                                    <i class="fas fa-star text-sm <?php echo $i <= $rating ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                                                <?php endfor; ?>
                                                <span class="ml-2 text-sm text-gray-600"><?php echo $rating; ?>/5</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo isset($feedback['created_at']) ? date('M d, Y', strtotime($feedback['created_at'])) : 'N/A'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button type="button" 
                                                        onclick="viewFeedback(<?php echo htmlspecialchars(json_encode($feedback)); ?>)"
                                                        class="text-admin-primary hover:text-admin-secondary transition-colors duration-200"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" 
                                                        onclick="editFeedback(<?php echo htmlspecialchars(json_encode($feedback)); ?>)"
                                                        class="text-green-600 hover:text-green-900 transition-colors duration-200"
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" 
                                                        onclick="deleteFeedback(<?php echo $feedback['id']; ?>)"
                                                        class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="mt-6 flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo ($page - 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                               class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Previous
                            </a>
                        <?php endif; ?>
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo ($page + 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                               class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing page <span class="font-medium"><?php echo $page; ?></span> of 
                                <span class="font-medium"><?php echo $total_pages; ?></span>
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo ($page - 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                                       class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                                       class="relative inline-flex items-center px-4 py-2 border text-sm font-medium 
                                       <?php echo ($i == $page) ? 'z-10 bg-admin-primary border-admin-primary text-white' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo ($page + 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                                       class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Add Feedback Modal -->
    <div id="addFeedbackModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Add New Feedback</h3>
                <button type="button" onclick="closeModal('addFeedbackModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form method="POST" class="mt-4">
                <input type="hidden" name="action" value="add">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="report_id" class="block text-sm font-medium text-gray-700 mb-2">Select Report</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="report_id" required>
                            <option value="">Choose report...</option>
                            <?php foreach ($reports as $report): ?>
                                <option value="<?php echo $report['id']; ?>">
                                    <?php echo 'Week ' . htmlspecialchars($report['week_number']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="supervisor_id" class="block text-sm font-medium text-gray-700 mb-2">Select Supervisor</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="supervisor_id" required>
                            <option value="">Choose supervisor...</option>
                            <?php foreach ($supervisors as $supervisor): ?>
                                <option value="<?php echo $supervisor['id']; ?>">
                                    <?php echo htmlspecialchars($supervisor['department']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="feedback" class="block text-sm font-medium text-gray-700 mb-2">Feedback</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="feedback" rows="4" required placeholder="Enter feedback comments..."></textarea>
                </div>

                <div class="mb-4">
                    <label for="rating" class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="rating" required>
                        <option value="">Choose rating...</option>
                        <option value="1">1 - Poor</option>
                        <option value="2">2 - Fair</option>
                        <option value="3">3 - Good</option>
                        <option value="4">4 - Very Good</option>
                        <option value="5">5 - Excellent</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeModal('addFeedbackModal')" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-admin-primary text-white rounded-md hover:bg-admin-secondary transition-colors duration-200">Add Feedback</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Feedback Modal -->
    <div id="editFeedbackModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Edit Feedback</h3>
                <button type="button" onclick="closeModal('editFeedbackModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form method="POST" id="editFeedbackForm" class="mt-4">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="feedback_id" id="edit_feedback_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="edit_report_id" class="block text-sm font-medium text-gray-700 mb-2">Select Report</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="report_id" id="edit_report_id" required>
                            <option value="">Choose report...</option>
                            <?php foreach ($reports as $report): ?>
                                <option value="<?php echo $report['id']; ?>">
                                    <?php echo 'Week ' . htmlspecialchars($report['week_number']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="edit_supervisor_id" class="block text-sm font-medium text-gray-700 mb-2">Select Supervisor</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="supervisor_id" id="edit_supervisor_id" required>
                            <option value="">Choose supervisor...</option>
                            <?php foreach ($supervisors as $supervisor): ?>
                                <option value="<?php echo $supervisor['id']; ?>">
                                    <?php echo htmlspecialchars($supervisor['department']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="edit_feedback" class="block text-sm font-medium text-gray-700 mb-2">Feedback</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="feedback" id="edit_feedback" rows="4" required></textarea>
                </div>

                <div class="mb-4">
                    <label for="edit_rating" class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="rating" id="edit_rating" required>
                        <option value="1">1 - Poor</option>
                        <option value="2">2 - Fair</option>
                        <option value="3">3 - Good</option>
                        <option value="4">4 - Very Good</option>
                        <option value="5">5 - Excellent</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeModal('editFeedbackModal')" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">Update Feedback</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Feedback Modal -->
    <div id="viewFeedbackModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Feedback Details</h3>
                <button type="button" onclick="closeModal('viewFeedbackModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="mt-4" id="feedbackDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>

    <script>
        function editFeedback(feedback) {
            document.getElementById('edit_feedback_id').value = feedback.id;
            document.getElementById('edit_report_id').value = feedback.report_id;
            document.getElementById('edit_supervisor_id').value = feedback.supervisor_id;
            document.getElementById('edit_feedback').value = feedback.feedback;
            document.getElementById('edit_rating').value = feedback.rating;
            
            openModal('editFeedbackModal');
        }

        function deleteFeedback(feedbackId) {
            if (confirm('Are you sure you want to delete this feedback? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="feedback_id" value="${feedbackId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function viewFeedback(feedback) {
            const ratingStars = Array.from({length: 5}, (_, i) => 
                `<i class="fas fa-star text-sm ${i < feedback.rating ? 'text-yellow-400' : 'text-gray-300'}"></i>`
            ).join('');
            
            let content = `
                <div class="space-y-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">Feedback Information</h4>
                        <div class="space-y-2 text-sm">
                            <p><span class="font-medium">ID:</span> #${feedback.id}</p>
                            <p><span class="font-medium">Report:</span> ${feedback.report_title || 'Report #' + feedback.report_id}</p>
                            <p><span class="font-medium">Supervisor:</span> ${feedback.supervisor_name || 'Supervisor #' + feedback.supervisor_id}</p>
                            <p><span class="font-medium">Rating:</span> 
                                <span class="inline-flex items-center ml-2">
                                    ${ratingStars}
                                    <span class="ml-2">${feedback.rating}/5</span>
                                </span>
                            </p>
                            ${feedback.created_at ? `<p><span class="font-medium">Date:</span> ${new Date(feedback.created_at).toLocaleDateString()}</p>` : ''}
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">Feedback Content</h4>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">${feedback.feedback}</p>
                    </div>
                </div>
            `;
            document.getElementById('feedbackDetailsContent').innerHTML = content;
            openModal('viewFeedbackModal');
        }

        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = ['addFeedbackModal', 'editFeedbackModal', 'viewFeedbackModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target === modal) {
                    closeModal(modalId);
                }
            });
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modals = ['addFeedbackModal', 'editFeedbackModal', 'viewFeedbackModal'];
                modals.forEach(modalId => {
                    const modal = document.getElementById(modalId);
                    if (!modal.classList.contains('hidden')) {
                        closeModal(modalId);
                    }
                });
            }
        });

        // Prevent browser back button issues
        window.history.forward();
        function noBack() {
            window.history.forward();
        }
        setTimeout("noBack()", 0);
        window.onunload = function() {null};

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>
