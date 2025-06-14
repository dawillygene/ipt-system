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
                // Add new student
                $stmt = $con->prepare("INSERT INTO students (user_id, full_name, reg_number, gender, college_name, department, course_name, program, level, year_of_study, phone_number, address, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssssssisss", 
                    $_POST['user_id'],
                    $_POST['full_name'],
                    $_POST['reg_number'],
                    $_POST['gender'],
                    $_POST['college_name'],
                    $_POST['department'],
                    $_POST['course_name'],
                    $_POST['program'],
                    $_POST['level'],
                    $_POST['year_of_study'],
                    $_POST['phone_number'],
                    $_POST['address'],
                    $_POST['email']
                );
                
                if ($stmt->execute()) {
                    $success_message = "Student added successfully!";
                } else {
                    $error_message = "Error adding student: " . $con->error;
                }
                $stmt->close();
                break;
                
            case 'update':
                // Update student
                $stmt = $con->prepare("UPDATE students SET full_name=?, reg_number=?, gender=?, college_name=?, department=?, course_name=?, program=?, level=?, year_of_study=?, phone_number=?, address=?, email=? WHERE student_id=?");
                $stmt->bind_param("ssssssssisssi",
                    $_POST['full_name'],
                    $_POST['reg_number'],
                    $_POST['gender'],
                    $_POST['college_name'],
                    $_POST['department'],
                    $_POST['course_name'],
                    $_POST['program'],
                    $_POST['level'],
                    $_POST['year_of_study'],
                    $_POST['phone_number'],
                    $_POST['address'],
                    $_POST['email'],
                    $_POST['student_id']
                );
                
                if ($stmt->execute()) {
                    $success_message = "Student updated successfully!";
                } else {
                    $error_message = "Error updating student: " . $con->error;
                }
                $stmt->close();
                break;
                
            case 'delete':
                // Delete student
                $stmt = $con->prepare("DELETE FROM students WHERE student_id = ?");
                $stmt->bind_param("i", $_POST['student_id']);
                
                if ($stmt->execute()) {
                    $success_message = "Student deleted successfully!";
                } else {
                    $error_message = "Error deleting student: " . $con->error;
                }
                $stmt->close();
                break;
        }
    }
}

// Get all students with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
$search_params = [];
$param_types = '';

if (!empty($search)) {
    $search_condition = "WHERE s.full_name LIKE ? OR s.reg_number LIKE ? OR s.email LIKE ? OR s.college_name LIKE ?";
    $search_params = ["%$search%", "%$search%", "%$search%", "%$search%"];
    $param_types = 'ssss';
}

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM students s $search_condition";
$count_stmt = $con->prepare($count_sql);
if (!empty($search_params)) {
    $count_stmt->bind_param($param_types, ...$search_params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);
$count_stmt->close();

// Get students data
$sql = "SELECT s.*, u.name as username FROM students s 
        LEFT JOIN users u ON s.user_id = u.id 
        $search_condition 
        ORDER BY s.created_at DESC 
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
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
$stmt->close();

// Get all users for dropdown (users who don't have student records yet)
$users_sql = "SELECT u.id, u.name, u.email FROM users u WHERE u.id NOT IN (SELECT user_id FROM students WHERE user_id IS NOT NULL)";
$users_result = mysqli_query($con, $users_sql);
$available_users = [];
while ($row = mysqli_fetch_assoc($users_result)) {
    $available_users[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - IPT System</title>
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
                        <h1 class="text-3xl font-bold text-gray-900">Manage Students</h1>
                        <p class="text-gray-600 mt-1">Add, edit, and manage student records</p>
                    </div>
                    <button type="button" 
                            onclick="openModal('addStudentModal')"
                            class="inline-flex items-center px-4 py-2 bg-admin-primary text-white rounded-lg hover:bg-admin-secondary transition-colors duration-200 shadow-md">
                        <i class="fas fa-plus mr-2"></i>
                        Add New Student
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
                                   placeholder="Search students..." 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-admin-primary text-white rounded-lg hover:bg-admin-secondary transition-colors duration-200">
                            Search
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="admin_students.php" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200">
                                Clear
                            </a>
                        <?php endif; ?>
                    </form>
                    <div class="text-sm text-gray-600">
                        <span class="bg-admin-primary text-white px-3 py-1 rounded-full">
                            Total Students: <?php echo $total_records; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Students Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">College</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-user-graduate text-4xl mb-4 block text-gray-300"></i>
                                        <p class="text-lg font-medium">No students found</p>
                                        <p class="text-sm">Add your first student to get started</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($students as $student): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <?php if ($student['profile_photo']): ?>
                                                        <img class="h-10 w-10 rounded-full object-cover" 
                                                             src="../<?php echo htmlspecialchars($student['profile_photo']); ?>" 
                                                             alt="Profile">
                                                    <?php else: ?>
                                                        <div class="h-10 w-10 rounded-full bg-admin-primary flex items-center justify-center">
                                                            <i class="fas fa-user text-white"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars($student['full_name']); ?>
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        <?php echo htmlspecialchars($student['email'] ?: 'No email'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($student['reg_number']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                <?php echo $student['gender'] == 'Male' ? 'bg-blue-100 text-blue-800' : 
                                                    ($student['gender'] == 'Female' ? 'bg-pink-100 text-pink-800' : 'bg-gray-100 text-gray-800'); ?>">
                                                <?php echo htmlspecialchars($student['gender']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($student['college_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($student['department']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($student['course_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            Year <?php echo htmlspecialchars($student['year_of_study']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($student['phone_number']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button type="button" 
                                                        onclick="viewStudent(<?php echo $student['student_id']; ?>)"
                                                        class="text-admin-primary hover:text-admin-secondary transition-colors duration-200"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" 
                                                        onclick="editStudent(<?php echo htmlspecialchars(json_encode($student)); ?>)"
                                                        class="text-green-600 hover:text-green-900 transition-colors duration-200"
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" 
                                                        onclick="deleteStudent(<?php echo $student['student_id']; ?>, '<?php echo htmlspecialchars($student['full_name']); ?>')"
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

    <!-- Add Student Modal -->
    <div id="addStudentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Add New Student</h3>
                <button type="button" onclick="closeModal('addStudentModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form method="POST" class="mt-4">
                <input type="hidden" name="action" value="add">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">Select User Account</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="user_id" required>
                            <option value="">Choose user account...</option>
                            <?php foreach ($available_users as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['name'] . ' (' . $user['email'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="full_name" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="reg_number" class="block text-sm font-medium text-gray-700 mb-2">Registration Number</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="reg_number" required>
                    </div>
                    <div class="mb-4">
                        <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="gender" required>
                            <option value="">Choose gender...</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="college_name" class="block text-sm font-medium text-gray-700 mb-2">College Name</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="college_name" required>
                    </div>
                    <div class="mb-4">
                        <label for="department" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="department" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="course_name" class="block text-sm font-medium text-gray-700 mb-2">Course Name</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="course_name" required>
                    </div>
                    <div class="mb-4">
                        <label for="program" class="block text-sm font-medium text-gray-700 mb-2">Program</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="program" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="mb-4">
                        <label for="level" class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="level" required>
                    </div>
                    <div class="mb-4">
                        <label for="year_of_study" class="block text-sm font-medium text-gray-700 mb-2">Year of Study</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="year_of_study" required>
                            <option value="">Choose year...</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                            <option value="5">5th Year</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="phone_number" placeholder="Enter phone number starting with +255 (e.g., +255753225961)" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="address" rows="2" required></textarea>
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="email">
                </div>

                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeModal('addStudentModal')" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-admin-primary text-white rounded-md hover:bg-admin-secondary transition-colors duration-200">Add Student</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div id="editStudentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Edit Student</h3>
                <button type="button" onclick="closeModal('editStudentModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form method="POST" id="editStudentForm" class="mt-4">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="student_id" id="edit_student_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="edit_full_name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="full_name" id="edit_full_name" required>
                    </div>
                    <div class="mb-4">
                        <label for="edit_reg_number" class="block text-sm font-medium text-gray-700 mb-2">Registration Number</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="reg_number" id="edit_reg_number" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="edit_gender" class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="gender" id="edit_gender" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="edit_college_name" class="block text-sm font-medium text-gray-700 mb-2">College Name</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="college_name" id="edit_college_name" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="edit_department" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="department" id="edit_department" required>
                    </div>
                    <div class="mb-4">
                        <label for="edit_course_name" class="block text-sm font-medium text-gray-700 mb-2">Course Name</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="course_name" id="edit_course_name" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="edit_program" class="block text-sm font-medium text-gray-700 mb-2">Program</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="program" id="edit_program" required>
                    </div>
                    <div class="mb-4">
                        <label for="edit_level" class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="level" id="edit_level" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="edit_year_of_study" class="block text-sm font-medium text-gray-700 mb-2">Year of Study</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="year_of_study" id="edit_year_of_study" required>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                            <option value="5">5th Year</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="edit_phone_number" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="phone_number" id="edit_phone_number" placeholder="Enter phone number starting with +255 (e.g., +255753225961)" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="edit_address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="address" id="edit_address" rows="2" required></textarea>
                </div>

                <div class="mb-4">
                    <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-admin-primary" name="email" id="edit_email">
                </div>

                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeModal('editStudentModal')" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">Update Student</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Student Modal -->
    <div id="viewStudentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Student Details</h3>
                <button type="button" onclick="closeModal('viewStudentModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="mt-4" id="studentDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editStudent(student) {
            document.getElementById('edit_student_id').value = student.student_id;
            document.getElementById('edit_full_name').value = student.full_name;
            document.getElementById('edit_reg_number').value = student.reg_number;
            document.getElementById('edit_gender').value = student.gender;
            document.getElementById('edit_college_name').value = student.college_name;
            document.getElementById('edit_department').value = student.department;
            document.getElementById('edit_course_name').value = student.course_name;
            document.getElementById('edit_program').value = student.program;
            document.getElementById('edit_level').value = student.level;
            document.getElementById('edit_year_of_study').value = student.year_of_study;
            document.getElementById('edit_phone_number').value = student.phone_number;
            document.getElementById('edit_address').value = student.address;
            document.getElementById('edit_email').value = student.email || '';
            
            openModal('editStudentModal');
        }

        function deleteStudent(studentId, studentName) {
            if (confirm(`Are you sure you want to delete student "${studentName}"? This action cannot be undone.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="student_id" value="${studentId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function viewStudent(studentId) {
            const students = <?php echo json_encode($students); ?>;
            const student = students.find(s => s.student_id == studentId);
            
            if (student) {
                let content = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900 mb-2">Personal Information</h4>
                                <div class="space-y-2 text-sm">
                                    <p><span class="font-medium">Full Name:</span> ${student.full_name}</p>
                                    <p><span class="font-medium">Registration Number:</span> ${student.reg_number}</p>
                                    <p><span class="font-medium">Gender:</span> ${student.gender}</p>
                                    <p><span class="font-medium">Email:</span> ${student.email || 'N/A'}</p>
                                    <p><span class="font-medium">Phone:</span> ${student.phone_number}</p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900 mb-2">Academic Information</h4>
                                <div class="space-y-2 text-sm">
                                    <p><span class="font-medium">College:</span> ${student.college_name}</p>
                                    <p><span class="font-medium">Department:</span> ${student.department}</p>
                                    <p><span class="font-medium">Course:</span> ${student.course_name}</p>
                                    <p><span class="font-medium">Program:</span> ${student.program}</p>
                                    <p><span class="font-medium">Level:</span> ${student.level}</p>
                                    <p><span class="font-medium">Year of Study:</span> ${student.year_of_study}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">Address</h4>
                        <p class="text-sm text-gray-700">${student.address}</p>
                    </div>
                `;
                document.getElementById('studentDetailsContent').innerHTML = content;
                openModal('viewStudentModal');
            }
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
            const modals = ['addStudentModal', 'editStudentModal', 'viewStudentModal'];
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
                const modals = ['addStudentModal', 'editStudentModal', 'viewStudentModal'];
                modals.forEach(modalId => {
                    const modal = document.getElementById(modalId);
                    if (!modal.classList.contains('hidden')) {
                        closeModal(modalId);
                    }
                });
            }
        });
    </script>
</body>
</html>
