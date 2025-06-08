<?php
session_start();
require_once 'db.php';

// Check admin session
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            $stmt = $con->prepare("INSERT INTO reports (user_id, week_number, start_date, end_date, description, skills_gained, challenges_faced) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssss",
                $_POST['user_id'],
                $_POST['week_number'],
                $_POST['start_date'],
                $_POST['end_date'],
                $_POST['description'],
                $_POST['skills_gained'],
                $_POST['challenges_faced']
            );
            
            if ($stmt->execute()) {
                $success_message = "Report added successfully!";
            } else {
                $error_message = "Error adding report: " . $con->error;
            }
            $stmt->close();
            break;
            
        case 'update':
            $stmt = $con->prepare("UPDATE reports SET user_id=?, week_number=?, start_date=?, end_date=?, description=?, skills_gained=?, challenges_faced=? WHERE id=?");
            $stmt->bind_param("iisssssi",
                $_POST['user_id'],
                $_POST['week_number'],
                $_POST['start_date'],
                $_POST['end_date'],
                $_POST['description'],
                $_POST['skills_gained'],
                $_POST['challenges_faced'],
                $_POST['report_id']
            );
            
            if ($stmt->execute()) {
                $success_message = "Report updated successfully!";
            } else {
                $error_message = "Error updating report: " . $con->error;
            }
            $stmt->close();
            break;
            
        case 'delete':
            $stmt = $con->prepare("DELETE FROM reports WHERE id = ?");
            $stmt->bind_param("i", $_POST['report_id']);
            
            if ($stmt->execute()) {
                $success_message = "Report deleted successfully!";
            } else {
                $error_message = "Error deleting report: " . $con->error;
            }
            $stmt->close();
            break;
    }
}

// Pagination & search setup
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build search conditions
$search_conditions = '';
$search_params = [];
$param_types = '';

if (!empty($search)) {
    $search_conditions = "WHERE u.name LIKE ? OR r.description LIKE ? OR r.skills_gained LIKE ?";
    $search_params = ["%$search%", "%$search%", "%$search%"];
    $param_types = 'sss';
}

// Count total records
$count_sql = "SELECT COUNT(*) AS total FROM reports r
              LEFT JOIN users u ON r.user_id = u.id
              $search_conditions";

$count_stmt = $con->prepare($count_sql);
if (!empty($search_params)) {
    $count_stmt->bind_param($param_types, ...$search_params);
}
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);
$count_stmt->close();

// Fetch report data
$sql = "SELECT r.*, u.name AS user_name, u.email AS user_email,
               s.full_name AS student_name, s.reg_number
        FROM reports r
        LEFT JOIN users u ON r.user_id = u.id
        LEFT JOIN students s ON r.user_id = s.user_id
        $search_conditions
        ORDER BY r.created_at DESC, r.id DESC
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
$reports = [];
while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}
$stmt->close();

// Get users for dropdown (both regular users and students)
$users_sql = "SELECT u.id, u.name, u.email, s.full_name AS student_name, s.reg_number 
              FROM users u 
              LEFT JOIN students s ON u.id = s.user_id 
              ORDER BY COALESCE(s.full_name, u.name)";
$users_result = mysqli_query($con, $users_sql);
$users = [];
while ($row = mysqli_fetch_assoc($users_result)) {
    $users[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reports - IPT System</title>
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
                        <h1 class="text-3xl font-bold text-gray-900">Manage Reports</h1>
                        <p class="text-gray-600 mt-1">View and manage student weekly reports</p>
                    </div>
                    <button type="button" 
                            onclick="openModal('addReportModal')"
                            class="inline-flex items-center px-4 py-2 bg-admin-primary text-white rounded-lg hover:bg-admin-secondary transition-colors duration-200 shadow-md">
                        <i class="fas fa-plus mr-2"></i>
                        Add New Report
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
                                   placeholder="Search reports..." 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-admin-primary text-white rounded-lg hover:bg-admin-secondary transition-colors duration-200">
                            Search
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="admin_reports.php" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200">
                                Clear
                            </a>
                        <?php endif; ?>
                    </form>
                    <div class="text-sm text-gray-600">
                        <span class="bg-admin-primary text-white px-3 py-1 rounded-full">
                            Total Reports: <?php echo $total_records; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Reports Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student/User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Week</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($reports)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-file-alt text-4xl mb-4 block text-gray-300"></i>
                                        <p class="text-lg font-medium">No reports found</p>
                                        <p class="text-sm">Add your first report to get started</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reports as $report): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            #<?php echo htmlspecialchars($report['id']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($report['student_name'] ?: $report['user_name']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($report['reg_number'] ?: $report['user_email']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Week <?php echo htmlspecialchars($report['week_number']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d', strtotime($report['start_date'])) . ' - ' . date('M d, Y', strtotime($report['end_date'])); ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                                            <div class="truncate" title="<?php echo htmlspecialchars($report['description']); ?>">
                                                <?php echo htmlspecialchars(substr($report['description'], 0, 50) . (strlen($report['description']) > 50 ? '...' : '')); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($report['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button type="button" 
                                                        onclick="viewReport(<?php echo htmlspecialchars(json_encode($report)); ?>)"
                                                        class="text-admin-primary hover:text-admin-secondary transition-colors duration-200"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" 
                                                        onclick="editReport(<?php echo htmlspecialchars(json_encode($report)); ?>)"
                                                        class="text-green-600 hover:text-green-900 transition-colors duration-200"
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" 
                                                        onclick="deleteReport(<?php echo $report['id']; ?>)"
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

    <!-- Add Report Modal -->
    <div id="addReportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-3xl shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Add New Report</h3>
                <button type="button" onclick="closeModal('addReportModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form method="POST" class="mt-4">
                <input type="hidden" name="action" value="add">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">Select User/Student</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="user_id" required>
                            <option value="">Choose user...</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars(($user['student_name'] ?: $user['name']) . ' (' . ($user['reg_number'] ?: $user['email']) . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="week_number" class="block text-sm font-medium text-gray-700 mb-2">Week Number</label>
                        <input type="number" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" 
                               name="week_number" 
                               min="1" 
                               max="52"
                               required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                        <input type="date" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" 
                               name="start_date" 
                               required>
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                        <input type="date" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" 
                               name="end_date" 
                               required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" 
                              name="description" 
                              rows="4" 
                              required 
                              placeholder="Describe the activities performed during this week..."></textarea>
                </div>

                <div class="mb-4">
                    <label for="skills_gained" class="block text-sm font-medium text-gray-700 mb-2">Skills Gained</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" 
                              name="skills_gained" 
                              rows="3" 
                              required 
                              placeholder="List the skills and knowledge gained..."></textarea>
                </div>

                <div class="mb-4">
                    <label for="challenges_faced" class="block text-sm font-medium text-gray-700 mb-2">Challenges Faced</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" 
                              name="challenges_faced" 
                              rows="3" 
                              placeholder="Describe any challenges encountered (optional)..."></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeModal('addReportModal')" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-admin-primary text-white rounded-md hover:bg-admin-secondary transition-colors duration-200">Add Report</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Report Modal -->
    <div id="editReportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-3xl shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Edit Report</h3>
                <button type="button" onclick="closeModal('editReportModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form method="POST" id="editReportForm" class="mt-4">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="report_id" id="edit_report_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="edit_user_id" class="block text-sm font-medium text-gray-700 mb-2">Select User/Student</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="user_id" id="edit_user_id" required>
                            <option value="">Choose user...</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars(($user['student_name'] ?: $user['name']) . ' (' . ($user['reg_number'] ?: $user['email']) . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="edit_week_number" class="block text-sm font-medium text-gray-700 mb-2">Week Number</label>
                        <input type="number" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" 
                               name="week_number" 
                               id="edit_week_number"
                               min="1" 
                               max="52"
                               required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="edit_start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                        <input type="date" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" 
                               name="start_date" 
                               id="edit_start_date"
                               required>
                    </div>
                    <div>
                        <label for="edit_end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                        <input type="date" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" 
                               name="end_date" 
                               id="edit_end_date"
                               required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="edit_description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" 
                              name="description" 
                              id="edit_description"
                              rows="4" 
                              required></textarea>
                </div>

                <div class="mb-4">
                    <label for="edit_skills_gained" class="block text-sm font-medium text-gray-700 mb-2">Skills Gained</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" 
                              name="skills_gained" 
                              id="edit_skills_gained"
                              rows="3" 
                              required></textarea>
                </div>

                <div class="mb-4">
                    <label for="edit_challenges_faced" class="block text-sm font-medium text-gray-700 mb-2">Challenges Faced</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" 
                              name="challenges_faced" 
                              id="edit_challenges_faced"
                              rows="3"></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeModal('editReportModal')" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">Update Report</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Report Modal -->
    <div id="viewReportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Report Details</h3>
                <button type="button" onclick="closeModal('viewReportModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="mt-4" id="reportDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>

    <script>
        function editReport(report) {
            document.getElementById('edit_report_id').value = report.id;
            document.getElementById('edit_user_id').value = report.user_id;
            document.getElementById('edit_week_number').value = report.week_number;
            document.getElementById('edit_start_date').value = report.start_date;
            document.getElementById('edit_end_date').value = report.end_date;
            document.getElementById('edit_description').value = report.description;
            document.getElementById('edit_skills_gained').value = report.skills_gained;
            document.getElementById('edit_challenges_faced').value = report.challenges_faced || '';
            
            openModal('editReportModal');
        }

        function deleteReport(reportId) {
            if (confirm('Are you sure you want to delete this report? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="report_id" value="${reportId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function viewReport(report) {
            let content = `
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-3">Student/User Information</h4>
                            <div class="space-y-2 text-sm">
                                <p><span class="font-medium">Name:</span> ${report.student_name || report.user_name}</p>
                                <p><span class="font-medium">ID/Email:</span> ${report.reg_number || report.user_email}</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-3">Report Details</h4>
                            <div class="space-y-2 text-sm">
                                <p><span class="font-medium">Week:</span> 
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Week ${report.week_number}
                                    </span>
                                </p>
                                <p><span class="font-medium">Period:</span> ${new Date(report.start_date).toLocaleDateString()} - ${new Date(report.end_date).toLocaleDateString()}</p>
                                <p><span class="font-medium">Created:</span> ${new Date(report.created_at).toLocaleDateString()}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-3">Description</h4>
                        <div class="prose max-w-none">
                            <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">${report.description}</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-3">Skills Gained</h4>
                        <div class="prose max-w-none">
                            <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">${report.skills_gained}</p>
                        </div>
                    </div>
                    ${report.challenges_faced ? `
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-3">Challenges Faced</h4>
                        <div class="prose max-w-none">
                            <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">${report.challenges_faced}</p>
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
            document.getElementById('reportDetailsContent').innerHTML = content;
            openModal('viewReportModal');
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
            const modals = ['addReportModal', 'editReportModal', 'viewReportModal'];
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
                const modals = ['addReportModal', 'editReportModal', 'viewReportModal'];
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
