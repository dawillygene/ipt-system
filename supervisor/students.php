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
    
    if ($action === 'assign_student') {
        $student_email = trim($_POST['student_email'] ?? '');
        $assignment_type = $_POST['assignment_type'] ?? 'academic';
        $notes = trim($_POST['notes'] ?? '');
        
        if (empty($student_email)) {
            $errors[] = 'Student email is required';
        } else {
            // Find student by email
            $student_stmt = $con->prepare("SELECT student_id, full_name FROM students WHERE email = ?");
            $student_stmt->bind_param("s", $student_email);
            $student_stmt->execute();
            $student = $student_stmt->get_result()->fetch_assoc();
            $student_stmt->close();
            
            if ($student) {
                // Check if already assigned
                $check_stmt = $con->prepare("SELECT assignment_id FROM supervisor_assignments WHERE supervisor_id = ? AND student_id = ? AND assignment_type = ? AND status = 'active'");
                $check_stmt->bind_param("iis", $supervisor_id, $student['student_id'], $assignment_type);
                $check_stmt->execute();
                
                if ($check_stmt->get_result()->num_rows === 0) {
                    // Assign student
                    $assign_stmt = $con->prepare("INSERT INTO supervisor_assignments (supervisor_id, student_id, assignment_type, assigned_date, notes) VALUES (?, ?, ?, CURDATE(), ?)");
                    $assign_stmt->bind_param("iiss", $supervisor_id, $student['student_id'], $assignment_type, $notes);
                    
                    if ($assign_stmt->execute()) {
                        $success = 'Student ' . htmlspecialchars($student['full_name']) . ' assigned successfully!';
                    } else {
                        $errors[] = 'Failed to assign student. Please try again.';
                    }
                    $assign_stmt->close();
                } else {
                    $errors[] = 'Student is already assigned to you for ' . $assignment_type . ' supervision.';
                }
                $check_stmt->close();
            } else {
                $errors[] = 'Student with this email address not found.';
            }
        }
    }
    
    if ($action === 'update_assignment') {
        $assignment_id = (int)($_POST['assignment_id'] ?? 0);
        $status = $_POST['assignment_status'] ?? 'active';
        $notes = trim($_POST['assignment_notes'] ?? '');
        
        $update_stmt = $con->prepare("UPDATE supervisor_assignments SET status = ?, notes = ?, updated_at = CURRENT_TIMESTAMP WHERE assignment_id = ? AND supervisor_id = ?");
        $update_stmt->bind_param("ssii", $status, $notes, $assignment_id, $supervisor_id);
        
        if ($update_stmt->execute()) {
            $success = 'Assignment updated successfully!';
        } else {
            $errors[] = 'Failed to update assignment.';
        }
        $update_stmt->close();
    }
}

// Get assigned students with detailed information
$students_stmt = $con->prepare("
    SELECT s.*, sa.assignment_id, sa.assignment_type, sa.assigned_date, sa.status as assignment_status, sa.notes,
           COUNT(sr.report_id) as total_reports,
           COUNT(CASE WHEN sr.status = 'submitted' THEN 1 END) as submitted_reports,
           COUNT(rr.review_id) as reviewed_reports,
           MAX(sr.created_at) as last_report_date
    FROM students s 
    JOIN supervisor_assignments sa ON s.student_id = sa.student_id 
    LEFT JOIN student_reports sr ON s.student_id = sr.student_id
    LEFT JOIN report_reviews rr ON sr.report_id = rr.report_id AND rr.supervisor_id = ?
    WHERE sa.supervisor_id = ?
    GROUP BY s.student_id, sa.assignment_id
    ORDER BY sa.assigned_date DESC
");
$students_stmt->bind_param("ii", $supervisor_id, $supervisor_id);
$students_stmt->execute();
$students = $students_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$students_stmt->close();

// Get specific student details if viewing
$view_student = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $student_id = (int)$_GET['view'];
    
    // Verify this student is assigned to the supervisor
    $verify_stmt = $con->prepare("SELECT COUNT(*) as count FROM supervisor_assignments WHERE supervisor_id = ? AND student_id = ?");
    $verify_stmt->bind_param("ii", $supervisor_id, $student_id);
    $verify_stmt->execute();
    $is_assigned = $verify_stmt->get_result()->fetch_assoc()['count'] > 0;
    $verify_stmt->close();
    
    if ($is_assigned) {
        // Get detailed student information
        $student_detail_stmt = $con->prepare("
            SELECT s.*, sa.assignment_type, sa.assigned_date, sa.status as assignment_status, sa.notes as assignment_notes,
                   sa.assignment_id
            FROM students s 
            JOIN supervisor_assignments sa ON s.student_id = sa.student_id 
            WHERE s.student_id = ? AND sa.supervisor_id = ?
        ");
        $student_detail_stmt->bind_param("ii", $student_id, $supervisor_id);
        $student_detail_stmt->execute();
        $view_student = $student_detail_stmt->get_result()->fetch_assoc();
        $student_detail_stmt->close();
        
        if ($view_student) {
            // Get student's reports
            $student_reports = getStudentReports($con, $student_id, $supervisor_id);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Students - IPT Supervisor Portal</title>
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
                    <a href="students.php" class="flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-lg">
                        <i class="fas fa-users mr-3"></i>
                        My Students
                    </a>
                    <a href="reports.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
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
                    <h1 class="text-3xl font-bold text-primary">My Students</h1>
                    <?php if (!$view_student): ?>
                    <button onclick="document.getElementById('assignStudentModal').classList.remove('hidden')" class="bg-primary hover:bg-secondary text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                        <i class="fas fa-plus-circle mr-2"></i>Assign New Student
                    </button>
                    <?php endif; ?>
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

                <?php if ($view_student): ?>
                    <?php include 'includes/view_student_details.php'; // You'll need to create this include ?>
                <?php else: ?>
                    <!-- Student List Table -->
                    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignment</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reports</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="h-10 w-10 bg-primary rounded-full flex items-center justify-center">
                                                        <span class="text-white text-sm font-medium">
                                                            <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($student['reg_number']); ?></div>
                                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($student['email']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <?php echo ucfirst($student['assignment_type']); ?>
                                                </span>
                                            </div>
                                            <div class="text-sm text-gray-500">Since <?php echo date('M d, Y', strtotime($student['assigned_date'])); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?php echo $student['submitted_reports']; ?>/<?php echo $student['total_reports']; ?> submitted
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo $student['reviewed_reports']; ?> reviewed
                                            </div>
                                            <?php if ($student['last_report_date']): ?>
                                                <div class="text-xs text-gray-400">
                                                    Last: <?php echo date('M d', strtotime($student['last_report_date'])); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                <?php echo $student['assignment_status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                                          ($student['assignment_status'] === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'); ?>">
                                                <?php echo ucfirst($student['assignment_status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <a href="students.php?view=<?php echo $student['student_id']; ?>" class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-eye mr-1"></i>View
                                            </a>
                                            <a href="reports.php?student=<?php echo $student['student_id']; ?>" class="text-green-600 hover:text-green-900">
                                                <i class="fas fa-file-alt mr-1"></i>Reports
                                            </a>
                                            <a href="evaluations.php?student=<?php echo $student['student_id']; ?>" class="text-purple-600 hover:text-purple-900">
                                                <i class="fas fa-star mr-1"></i>Evaluate
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Assign Student Modal -->
    <div id="assignStudentModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold text-gray-900">Assign New Student</h2>
                <button onclick="closeAssignStudentModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="assign_student">
                <div class="mb-4">
                    <label for="student_email" class="block text-sm font-medium text-gray-700 mb-2">Student Email</label>
                    <input type="email" id="student_email" name="student_email" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="student@university.edu">
                </div>
                <div class="mb-4">
                    <label for="assignment_type" class="block text-sm font-medium text-gray-700 mb-2">Supervision Type</label>
                    <select id="assignment_type" name="assignment_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="academic">Academic Supervision</option>
                        <option value="industrial">Industrial Supervision</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                    <input type="text" id="notes" name="notes"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="Assignment notes">
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-primary transition-colors">
                        <i class="fas fa-plus mr-2"></i>Assign Student
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Assignment Modal -->
    <div id="editAssignmentModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold text-gray-900">Edit Assignment</h2>
                <button onclick="closeEditAssignmentModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_assignment">
                <input type="hidden" id="edit_assignment_id" name="assignment_id" value="">
                <div class="mb-4">
                    <label for="edit_student_name" class="block text-sm font-medium text-gray-700 mb-2">Student Name</label>
                    <input type="text" id="edit_student_name" name="student_name" disabled
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                </div>
                <div class="mb-4">
                    <label for="edit_assignment_status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="edit_assignment_status" name="assignment_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="on_hold">On Hold</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="edit_assignment_notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea id="edit_assignment_notes" name="assignment_notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                              placeholder="Additional notes or instructions"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-primary transition-colors">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                </div>
            </form>
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

        // Modal handling
        function openAssignStudentModal() {
            document.getElementById('assignStudentModal').classList.remove('hidden');
        }
        function closeAssignStudentModal() {
            document.getElementById('assignStudentModal').classList.add('hidden');
        }

        function openEditAssignmentModal(assignmentId, studentName, currentStatus, currentNotes) {
            document.getElementById('edit_assignment_id').value = assignmentId;
            document.getElementById('edit_student_name_display').textContent = studentName;
            document.getElementById('edit_assignment_status').value = currentStatus;
            document.getElementById('edit_assignment_notes').value = currentNotes;
            document.getElementById('editAssignmentModal').classList.remove('hidden');
        }
        function closeEditAssignmentModal() {
            document.getElementById('editAssignmentModal').classList.add('hidden');
        }

    </script>
</body>
</html>
