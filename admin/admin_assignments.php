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
                // Add new training assignment
                $stmt = $con->prepare("INSERT INTO student_training_assignments (student_id, organization, training_area, start_date, end_date, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("isssss", 
                    $_POST['student_id'],
                    $_POST['organization'],
                    $_POST['training_area'],
                    $_POST['start_date'],
                    $_POST['end_date'],
                    $_POST['status']
                );
                
                if ($stmt->execute()) {
                    $success_message = "Training assignment added successfully!";
                } else {
                    $error_message = "Error adding assignment: " . $con->error;
                }
                $stmt->close();
                break;
                
            case 'update':
                // Update training assignment
                $stmt = $con->prepare("UPDATE student_training_assignments SET student_id=?, organization=?, training_area=?, start_date=?, end_date=?, status=? WHERE id=?");
                $stmt->bind_param("isssssi",
                    $_POST['student_id'],
                    $_POST['organization'],
                    $_POST['training_area'],
                    $_POST['start_date'],
                    $_POST['end_date'],
                    $_POST['status'],
                    $_POST['assignment_id']
                );
                
                if ($stmt->execute()) {
                    $success_message = "Assignment updated successfully!";
                } else {
                    $error_message = "Error updating assignment: " . $con->error;
                }
                $stmt->close();
                break;
                
            case 'delete':
                // Delete training assignment
                $stmt = $con->prepare("DELETE FROM student_training_assignments WHERE id = ?");
                $stmt->bind_param("i", $_POST['assignment_id']);
                
                if ($stmt->execute()) {
                    $success_message = "Assignment deleted successfully!";
                } else {
                    $error_message = "Error deleting assignment: " . $con->error;
                }
                $stmt->close();
                break;
        }
    }
}

// Get all assignments with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
$search_params = [];
$param_types = '';

if (!empty($search)) {
    $search_condition = " WHERE (s.full_name LIKE ? OR s.reg_number LIKE ? OR sta.organization LIKE ? OR sta.training_area LIKE ?)";
    $search_term = "%$search%";
    $search_params = [$search_term, $search_term, $search_term, $search_term];
    $param_types = 'ssss';
}

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM student_training_assignments sta 
              LEFT JOIN students s ON sta.student_id = s.student_id" . $search_condition;
if (!empty($search_params)) {
    $count_stmt = $con->prepare($count_sql);
    $count_stmt->bind_param($param_types, ...$search_params);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
} else {
    $count_result = $con->query($count_sql);
}
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Get assignments with student details
$assignments_sql = "SELECT sta.*, s.full_name, s.reg_number, s.college_name, s.department 
                   FROM student_training_assignments sta 
                   LEFT JOIN students s ON sta.student_id = s.student_id" . 
                   $search_condition . " ORDER BY sta.created_at DESC LIMIT ? OFFSET ?";

if (!empty($search_params)) {
    $stmt = $con->prepare($assignments_sql);
    $all_params = array_merge($search_params, [$limit, $offset]);
    $all_param_types = $param_types . 'ii';
    $stmt->bind_param($all_param_types, ...$all_params);
    $stmt->execute();
    $assignments_result = $stmt->get_result();
} else {
    $stmt = $con->prepare($assignments_sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $assignments_result = $stmt->get_result();
}

$assignments = [];
while ($row = $assignments_result->fetch_assoc()) {
    $assignments[] = $row;
}

// Get students for dropdowns
$students_query = "SELECT student_id, full_name, reg_number FROM students ORDER BY full_name";
$students_result = $con->query($students_query);
$students = [];
while ($row = $students_result->fetch_assoc()) {
    $students[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Training Assignments - IPT System</title>
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
                        <h1 class="text-3xl font-bold text-gray-900">Training Assignments</h1>
                        <p class="text-gray-600 mt-1">Manage student training assignments and placements</p>
                    </div>
                    <button type="button" 
                            onclick="openModal('addAssignmentModal')"
                            class="inline-flex items-center px-4 py-2 bg-admin-primary text-white rounded-lg hover:bg-admin-secondary transition-colors duration-200 shadow-md">
                        <i class="fas fa-plus mr-2"></i>
                        Add New Assignment
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
                                   placeholder="Search assignments..." 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-admin-primary text-white rounded-lg hover:bg-admin-secondary transition-colors duration-200">
                            Search
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="admin_assignments.php" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200">
                                Clear
                            </a>
                        <?php endif; ?>
                    </form>
                    <div class="text-sm text-gray-600">
                        <span class="bg-admin-primary text-white px-3 py-1 rounded-full">
                            Total Assignments: <?php echo $total_records; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Assignments Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Organization</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Training Area</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($assignments)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-briefcase text-4xl mb-4 block text-gray-300"></i>
                                        <p class="text-lg font-medium">No training assignments found</p>
                                        <p class="text-sm">Add your first assignment to get started</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($assignments as $assignment): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            #<?php echo htmlspecialchars($assignment['id']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm">
                                                <div class="font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($assignment['full_name'] ?: 'Unknown Student'); ?>
                                                </div>
                                                <div class="text-gray-500">
                                                    <?php echo htmlspecialchars($assignment['reg_number'] ?: 'No reg number'); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($assignment['organization'] ?: 'Not specified'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($assignment['training_area'] ?: 'Not specified'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php 
                                            $start_date = $assignment['start_date'] ? date('M d, Y', strtotime($assignment['start_date'])) : 'Not set';
                                            $end_date = $assignment['end_date'] ? date('M d, Y', strtotime($assignment['end_date'])) : 'Not set';
                                            echo $start_date . ' - ' . $end_date;
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                <?php 
                                                $status = $assignment['status'];
                                                echo $status == 'completed' ? 'bg-green-100 text-green-800' : 
                                                    ($status == 'active' ? 'bg-blue-100 text-blue-800' : 
                                                    ($status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')); 
                                                ?>">
                                                <?php echo ucfirst(htmlspecialchars($assignment['status'])); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button type="button" 
                                                        onclick="viewAssignment(<?php echo htmlspecialchars(json_encode($assignment)); ?>)"
                                                        class="text-admin-primary hover:text-admin-secondary transition-colors duration-200"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" 
                                                        onclick="editAssignment(<?php echo htmlspecialchars(json_encode($assignment)); ?>)"
                                                        class="text-green-600 hover:text-green-900 transition-colors duration-200"
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" 
                                                        onclick="deleteAssignment(<?php echo $assignment['id']; ?>, '<?php echo htmlspecialchars($assignment['full_name'] ?: 'Assignment'); ?>')"
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
                            <a href="?page=<?php echo ($page - 1) . ($search ? '&search=' . urlencode($search) : ''); ?>" 
                               class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Previous
                            </a>
                        <?php endif; ?>
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo ($page + 1) . ($search ? '&search=' . urlencode($search) : ''); ?>" 
                               class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to 
                                <span class="font-medium"><?php echo min($offset + $limit, $total_records); ?></span> of 
                                <span class="font-medium"><?php echo $total_records; ?></span> results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i . ($search ? '&search=' . urlencode($search) : ''); ?>" 
                                       class="<?php echo $i == $page ? 'bg-admin-primary text-white' : 'bg-white text-gray-500 hover:bg-gray-50'; ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                            </nav>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Add Assignment Modal -->
    <div id="addAssignmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Add New Training Assignment</h3>
                <button type="button" onclick="closeModal('addAssignmentModal')" class="text-gray-400 hover:text-gray-600">
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
                        <label for="organization" class="block text-sm font-medium text-gray-700 mb-2">Organization</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="organization" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="training_area" class="block text-sm font-medium text-gray-700 mb-2">Training Area</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="training_area" required>
                    </div>
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                        <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="start_date" required>
                    </div>
                    <div class="mb-4">
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                        <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="end_date" required>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeModal('addAssignmentModal')" class="px-4 py-2 text-gray-600 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-admin-primary text-white rounded-md hover:bg-admin-secondary transition-colors">
                        Add Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Assignment Modal -->
    <div id="editAssignmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Edit Training Assignment</h3>
                <button type="button" onclick="closeModal('editAssignmentModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form method="POST" class="mt-4">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="assignment_id" id="edit_assignment_id">
                
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
                        <label for="edit_organization" class="block text-sm font-medium text-gray-700 mb-2">Organization</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="organization" id="edit_organization" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="edit_training_area" class="block text-sm font-medium text-gray-700 mb-2">Training Area</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="training_area" id="edit_training_area" required>
                    </div>
                    <div class="mb-4">
                        <label for="edit_status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="status" id="edit_status" required>
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="edit_start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                        <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="start_date" id="edit_start_date" required>
                    </div>
                    <div class="mb-4">
                        <label for="edit_end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                        <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="end_date" id="edit_end_date" required>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeModal('editAssignmentModal')" class="px-4 py-2 text-gray-600 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-admin-primary text-white rounded-md hover:bg-admin-secondary transition-colors">
                        Update Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Assignment Modal -->
    <div id="viewAssignmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Assignment Details</h3>
                <button type="button" onclick="closeModal('viewAssignmentModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="assignmentDetails" class="mt-4">
                <!-- Assignment details will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        function editAssignment(assignment) {
            document.getElementById('edit_assignment_id').value = assignment.id;
            document.getElementById('edit_student_id').value = assignment.student_id;
            document.getElementById('edit_organization').value = assignment.organization || '';
            document.getElementById('edit_training_area').value = assignment.training_area || '';
            document.getElementById('edit_status').value = assignment.status;
            document.getElementById('edit_start_date').value = assignment.start_date;
            document.getElementById('edit_end_date').value = assignment.end_date;
            openModal('editAssignmentModal');
        }

        function viewAssignment(assignment) {
            const detailsContainer = document.getElementById('assignmentDetails');
            detailsContainer.innerHTML = `
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-2">Student Information</h4>
                            <p><span class="font-medium">Name:</span> ${assignment.full_name || 'Unknown'}</p>
                            <p><span class="font-medium">Registration:</span> ${assignment.reg_number || 'N/A'}</p>
                            <p><span class="font-medium">College:</span> ${assignment.college_name || 'N/A'}</p>
                            <p><span class="font-medium">Department:</span> ${assignment.department || 'N/A'}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-2">Assignment Details</h4>
                            <p><span class="font-medium">Organization:</span> ${assignment.organization || 'Not specified'}</p>
                            <p><span class="font-medium">Training Area:</span> ${assignment.training_area || 'Not specified'}</p>
                            <p><span class="font-medium">Status:</span> <span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-800">${assignment.status}</span></p>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">Timeline</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <p><span class="font-medium">Start Date:</span> ${assignment.start_date ? new Date(assignment.start_date).toLocaleDateString() : 'Not set'}</p>
                            <p><span class="font-medium">End Date:</span> ${assignment.end_date ? new Date(assignment.end_date).toLocaleDateString() : 'Not set'}</p>
                        </div>
                        <p class="mt-2"><span class="font-medium">Created:</span> ${assignment.created_at ? new Date(assignment.created_at).toLocaleDateString() : 'Unknown'}</p>
                    </div>
                </div>
            `;
            openModal('viewAssignmentModal');
        }

        function deleteAssignment(assignmentId, studentName) {
            if (confirm(`Are you sure you want to delete the training assignment for ${studentName}? This action cannot be undone.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="assignment_id" value="${assignmentId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const modals = ['addAssignmentModal', 'editAssignmentModal', 'viewAssignmentModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target === modal) {
                    closeModal(modalId);
                }
            });
        }
    </script>
</body>
</html>
