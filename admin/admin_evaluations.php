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
            $stmt = $con->prepare("INSERT INTO evaluations (student_id, supervisor_id, comments, evaluation_date, evaluation_score) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iissi",
                $_POST['student_id'],
                $_POST['supervisor_id'],
                $_POST['comments'],
                $_POST['evaluation_date'],
                $_POST['evaluation_score']
            );
            
            if ($stmt->execute()) {
                $success_message = "Evaluation added successfully!";
            } else {
                $error_message = "Error adding evaluation: " . $con->error;
            }
            $stmt->close();
            break;
            
        case 'update':
            $stmt = $con->prepare("UPDATE evaluations SET student_id=?, supervisor_id=?, comments=?, evaluation_date=?, evaluation_score=? WHERE id=?");
            $stmt->bind_param("iissii",
                $_POST['student_id'],
                $_POST['supervisor_id'],
                $_POST['comments'],
                $_POST['evaluation_date'],
                $_POST['evaluation_score'],
                $_POST['eval_id']
            );
            
            if ($stmt->execute()) {
                $success_message = "Evaluation updated successfully!";
            } else {
                $error_message = "Error updating evaluation: " . $con->error;
            }
            $stmt->close();
            break;
            
        case 'delete':
            $stmt = $con->prepare("DELETE FROM evaluations WHERE id = ?");
            $stmt->bind_param("i", $_POST['eval_id']);
            
            if ($stmt->execute()) {
                $success_message = "Evaluation deleted successfully!";
            } else {
                $error_message = "Error deleting evaluation: " . $con->error;
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
    $search_conditions = "WHERE s.full_name LIKE ? OR u2.name LIKE ? OR e.comments LIKE ?";
    $search_params = ["%$search%", "%$search%", "%$search%"];
    $param_types = 'sss';
}

// Count total records
$count_sql = "SELECT COUNT(*) AS total FROM evaluations e
              LEFT JOIN students s ON e.student_id = s.student_id
              LEFT JOIN supervisors sp ON e.supervisor_id = sp.id
              LEFT JOIN users u2 ON sp.user_id = u2.id
              $search_conditions";

$count_stmt = $con->prepare($count_sql);
if (!empty($search_params)) {
    $count_stmt->bind_param($param_types, ...$search_params);
}
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);
$count_stmt->close();

// Fetch evaluation data
$sql = "SELECT e.*, s.full_name AS student_name, s.reg_number, u2.name AS supervisor_name
        FROM evaluations e
        LEFT JOIN students s ON e.student_id = s.student_id
        LEFT JOIN supervisors sp ON e.supervisor_id = sp.id
        LEFT JOIN users u2 ON sp.user_id = u2.id
        $search_conditions
        ORDER BY e.evaluation_date DESC, e.id DESC
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
$evaluations = [];
while ($row = $result->fetch_assoc()) {
    $evaluations[] = $row;
}
$stmt->close();

// Get students for dropdown
$students_sql = "SELECT student_id, full_name, reg_number FROM students ORDER BY full_name";
$students_result = mysqli_query($con, $students_sql);
$students = [];
while ($row = mysqli_fetch_assoc($students_result)) {
    $students[] = $row;
}

// Get supervisors for dropdown
$supervisors_sql = "SELECT sp.id, u.name FROM supervisors sp 
                    LEFT JOIN users u ON sp.user_id = u.id 
                    ORDER BY u.name";
$supervisors_result = mysqli_query($con, $supervisors_sql);
$supervisors = [];
while ($row = mysqli_fetch_assoc($supervisors_result)) {
    $supervisors[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Evaluations - IPT System</title>
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
                        <h1 class="text-3xl font-bold text-gray-900">Manage Evaluations</h1>
                        <p class="text-gray-600 mt-1">View and manage student evaluations</p>
                    </div>
                    <button type="button" 
                            onclick="openModal('addEvalModal')"
                            class="inline-flex items-center px-4 py-2 bg-admin-primary text-white rounded-lg hover:bg-admin-secondary transition-colors duration-200 shadow-md">
                        <i class="fas fa-plus mr-2"></i>
                        Add New Evaluation
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
                                   placeholder="Search evaluations..." 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-admin-primary text-white rounded-lg hover:bg-admin-secondary transition-colors duration-200">
                            Search
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="admin_evaluations.php" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200">
                                Clear
                            </a>
                        <?php endif; ?>
                    </form>
                    <div class="text-sm text-gray-600">
                        <span class="bg-admin-primary text-white px-3 py-1 rounded-full">
                            Total Evaluations: <?php echo $total_records; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Evaluations Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supervisor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comments</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($evaluations)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-clipboard-list text-4xl mb-4 block text-gray-300"></i>
                                        <p class="text-lg font-medium">No evaluations found</p>
                                        <p class="text-sm">Add your first evaluation to get started</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($evaluations as $evaluation): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            #<?php echo htmlspecialchars($evaluation['id']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($evaluation['student_name'] ?: 'Student #' . $evaluation['student_id']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($evaluation['reg_number'] ?: 'No reg number'); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($evaluation['supervisor_name'] ?: 'Supervisor #' . $evaluation['supervisor_id']); ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                                            <div class="truncate" title="<?php echo htmlspecialchars($evaluation['comments']); ?>">
                                                <?php echo htmlspecialchars(substr($evaluation['comments'], 0, 50) . (strlen($evaluation['comments']) > 50 ? '...' : '')); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($evaluation['evaluation_date'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php 
                                            $score = (int)$evaluation['evaluation_score'];
                                            $score_color = $score >= 80 ? 'text-green-600 bg-green-100' : 
                                                          ($score >= 60 ? 'text-yellow-600 bg-yellow-100' : 'text-red-600 bg-red-100');
                                            ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $score_color; ?>">
                                                <?php echo $score; ?>%
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button type="button" 
                                                        onclick="viewEvaluation(<?php echo htmlspecialchars(json_encode($evaluation)); ?>)"
                                                        class="text-admin-primary hover:text-admin-secondary transition-colors duration-200"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" 
                                                        onclick="editEvaluation(<?php echo htmlspecialchars(json_encode($evaluation)); ?>)"
                                                        class="text-green-600 hover:text-green-900 transition-colors duration-200"
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" 
                                                        onclick="deleteEvaluation(<?php echo $evaluation['id']; ?>)"
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

    <!-- Add Evaluation Modal -->
    <div id="addEvalModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Add New Evaluation</h3>
                <button type="button" onclick="closeModal('addEvalModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form method="POST" class="mt-4">
                <input type="hidden" name="action" value="add">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Select Student</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="student_id" required>
                            <option value="">Choose student...</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['student_id']; ?>">
                                    <?php echo htmlspecialchars($student['full_name'] . ' (' . $student['reg_number'] . ')'); ?>
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
                                    <?php echo htmlspecialchars($supervisor['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="evaluation_date" class="block text-sm font-medium text-gray-700 mb-2">Evaluation Date</label>
                        <input type="date" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" 
                               name="evaluation_date" 
                               value="<?php echo date('Y-m-d'); ?>"
                               required>
                    </div>
                    <div class="mb-4">
                        <label for="evaluation_score" class="block text-sm font-medium text-gray-700 mb-2">Score (%)</label>
                        <input type="number" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" 
                               name="evaluation_score" 
                               min="0" 
                               max="100" 
                               required 
                               placeholder="Enter score (0-100)">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="comments" class="block text-sm font-medium text-gray-700 mb-2">Comments</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" 
                              name="comments" 
                              rows="4" 
                              required 
                              placeholder="Enter evaluation comments..."></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeModal('addEvalModal')" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-admin-primary text-white rounded-md hover:bg-admin-secondary transition-colors duration-200">Add Evaluation</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Evaluation Modal -->
    <div id="editEvalModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Edit Evaluation</h3>
                <button type="button" onclick="closeModal('editEvalModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form method="POST" id="editEvalForm" class="mt-4">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="eval_id" id="edit_eval_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="edit_student_id" class="block text-sm font-medium text-gray-700 mb-2">Select Student</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="student_id" id="edit_student_id" required>
                            <option value="">Choose student...</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['student_id']; ?>">
                                    <?php echo htmlspecialchars($student['full_name'] . ' (' . $student['reg_number'] . ')'); ?>
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
                                    <?php echo htmlspecialchars($supervisor['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="edit_evaluation_date" class="block text-sm font-medium text-gray-700 mb-2">Evaluation Date</label>
                        <input type="date" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" 
                               name="evaluation_date" 
                               id="edit_evaluation_date"
                               required>
                    </div>
                    <div class="mb-4">
                        <label for="edit_evaluation_score" class="block text-sm font-medium text-gray-700 mb-2">Score (%)</label>
                        <input type="number" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" 
                               name="evaluation_score" 
                               id="edit_evaluation_score"
                               min="0" 
                               max="100" 
                               required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="edit_comments" class="block text-sm font-medium text-gray-700 mb-2">Comments</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" 
                              name="comments" 
                              id="edit_comments"
                              rows="4" 
                              required></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeModal('editEvalModal')" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">Update Evaluation</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Evaluation Modal -->
    <div id="viewEvalModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Evaluation Details</h3>
                <button type="button" onclick="closeModal('viewEvalModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="mt-4" id="evalDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>

    <script>
        function editEvaluation(evaluation) {
            document.getElementById('edit_eval_id').value = evaluation.id;
            document.getElementById('edit_student_id').value = evaluation.student_id;
            document.getElementById('edit_supervisor_id').value = evaluation.supervisor_id;
            document.getElementById('edit_evaluation_date').value = evaluation.evaluation_date;
            document.getElementById('edit_evaluation_score').value = evaluation.evaluation_score;
            document.getElementById('edit_comments').value = evaluation.comments;
            
            openModal('editEvalModal');
        }

        function deleteEvaluation(evalId) {
            if (confirm('Are you sure you want to delete this evaluation? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="eval_id" value="${evalId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function viewEvaluation(evaluation) {
            let scoreColor = '';
            const score = parseInt(evaluation.evaluation_score);
            if (score >= 80) {
                scoreColor = 'text-green-600 bg-green-100';
            } else if (score >= 60) {
                scoreColor = 'text-yellow-600 bg-yellow-100';
            } else {
                scoreColor = 'text-red-600 bg-red-100';
            }
            
            let content = `
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-2">Student Information</h4>
                            <div class="space-y-2 text-sm">
                                <p><span class="font-medium">Name:</span> ${evaluation.student_name || 'Student #' + evaluation.student_id}</p>
                                <p><span class="font-medium">Reg Number:</span> ${evaluation.reg_number || 'N/A'}</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-2">Evaluation Details</h4>
                            <div class="space-y-2 text-sm">
                                <p><span class="font-medium">Supervisor:</span> ${evaluation.supervisor_name || 'Supervisor #' + evaluation.supervisor_id}</p>
                                <p><span class="font-medium">Date:</span> ${new Date(evaluation.evaluation_date).toLocaleDateString()}</p>
                                <p><span class="font-medium">Score:</span> 
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${scoreColor}">
                                        ${evaluation.evaluation_score}%
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">Comments</h4>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">${evaluation.comments}</p>
                    </div>
                </div>
            `;
            document.getElementById('evalDetailsContent').innerHTML = content;
            openModal('viewEvalModal');
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
            const modals = ['addEvalModal', 'editEvalModal', 'viewEvalModal'];
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
                const modals = ['addEvalModal', 'editEvalModal', 'viewEvalModal'];
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
