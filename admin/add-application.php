<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Check if required files exist before including them
if (!file_exists('db.php')) {
    die('Database configuration file not found.');
}

if (!file_exists('includes/layout.php')) {
    die('Layout file not found.');
}

require_once 'db.php';
require_once 'includes/layout.php';

$success_message = '';
$error_message = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? '';
    $college = $_POST['college'] ?? '';
    $organization = $_POST['organization'] ?? '';
    $training_area = $_POST['training_area'] ?? '';
    $letter_date = $_POST['letter_date'] ?? '';
    $status = $_POST['status'] ?? 'pending';
    
    // Validate required fields
    if (empty($student_id) || empty($college) || empty($organization) || empty($training_area) || empty($letter_date)) {
        $error_message = 'All fields are required.';
    } else {
        // Check if student exists
        $stmt = $con->prepare("SELECT student_id FROM students WHERE student_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error_message = 'Student with ID ' . htmlspecialchars($student_id) . ' does not exist.';
        } else {
            // Insert the application - using user_id instead of student_id since applications table references users
            $stmt = $con->prepare("INSERT INTO applications (user_id, full_name, phone, reg_number, department, industrial, application_date, created_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
            
            // Get student details for the application
            $student_stmt = $con->prepare("SELECT s.user_id, s.full_name, s.phone_number, s.reg_number, s.department FROM students s WHERE s.student_id = ?");
            $student_stmt->bind_param("i", $student_id);
            $student_stmt->execute();
            $student_result = $student_stmt->get_result();
            $student_data = $student_result->fetch_assoc();
            
            // Truncate phone number to exactly 10 characters
            $phone_truncated = substr($student_data['phone_number'], 0, 10);
            
            $stmt->bind_param("isssssss", $student_data['user_id'], $student_data['full_name'], $phone_truncated, $student_data['reg_number'], $student_data['department'], $organization, $letter_date, $status);
            
            if ($stmt->execute()) {
                $success_message = 'Application added successfully!';
                // Clear form data on success
                $student_id = $college = $organization = $training_area = $letter_date = '';
                $status = 'pending';
            } else {
                $error_message = 'Error adding application: ' . $con->error;
            }
        }
    }
}

$students_query = "SELECT s.student_id, s.reg_number, s.full_name 
                   FROM students s 
                   ORDER BY s.full_name";
$students_result = $con->query($students_query);

$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => 'admin_dashboard.php'],
    ['name' => 'Applications', 'url' => 'admin_applications.php'],
    ['name' => 'Add Application']
];
$pageTitle = 'Add Application';

echo renderAdminLayout($pageTitle, $breadcrumbs);
?>

<div class="max-w-4xl mx-auto">
    <?php 
    $headerContent = '
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Add New Application</h1>
                <p class="mt-2 text-gray-600">Create a new training application for a student</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="admin_applications.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors duration-200 inline-flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Applications
                </a>
            </div>
        </div>
    ';
    echo renderAdminCard('', $headerContent, '', false);
    ?>

    <?php if ($success_message): ?>
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
            <i class="fas fa-check-circle mr-3 text-green-500"></i>
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
            <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gradient-to-r from-admin-primary to-admin-secondary px-6 py-4">
            <h3 class="text-lg font-semibold text-white flex items-center">
                <i class="fas fa-plus mr-3"></i>
                Application Details
            </h3>
        </div>
        
        <form method="POST" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user-graduate mr-2 text-admin-primary"></i>
                        Student
                    </label>
                    <select name="student_id" id="student_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200">
                        <option value="">Select a student...</option>
                        <?php while ($student = $students_result->fetch_assoc()): ?>
                            <option value="<?php echo $student['student_id']; ?>" 
                                    <?php echo (isset($student_id) && $student_id == $student['student_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($student['full_name']) . ' (Reg: ' . htmlspecialchars($student['reg_number']) . ')'; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-flag mr-2 text-admin-primary"></i>
                        Status
                    </label>
                    <select name="status" id="status" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200">
                        <option value="pending" <?php echo (isset($status) && $status === 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo (isset($status) && $status === 'approved') ? 'selected' : ''; ?>>Approved</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="college" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-university mr-2 text-admin-primary"></i>
                    College
                </label>
                <input type="text" name="college" id="college" required
                       value="<?php echo htmlspecialchars($college ?? ''); ?>"
                       placeholder="Enter college name"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200">
            </div>

            <div>
                <label for="organization" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-building mr-2 text-admin-primary"></i>
                    Organization
                </label>
                <input type="text" name="organization" id="organization" required
                       value="<?php echo htmlspecialchars($organization ?? ''); ?>"
                       placeholder="Enter training organization"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200">
            </div>

            <div>
                <label for="training_area" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-tools mr-2 text-admin-primary"></i>
                    Training Area
                </label>
                <input type="text" name="training_area" id="training_area" required
                       value="<?php echo htmlspecialchars($training_area ?? ''); ?>"
                       placeholder="Enter training area/field"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200">
            </div>

            <div>
                <label for="letter_date" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar mr-2 text-admin-primary"></i>
                    Application Letter Date
                </label>
                <input type="date" name="letter_date" id="letter_date" required
                       value="<?php echo htmlspecialchars($letter_date ?? ''); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200">
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
                <button type="submit"
                        class="bg-admin-primary text-white px-6 py-3 rounded-lg hover:bg-admin-secondary transition-colors duration-200 flex items-center justify-center font-medium">
                    <i class="fas fa-plus mr-2"></i>
                    Add Application
                </button>
                <a href="admin_applications.php"
                   class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition-colors duration-200 flex items-center justify-center font-medium">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Prevent back button
window.history.forward();
function noBack() {
    window.history.forward();
}
setTimeout("noBack()", 0);
window.onunload = function() { null };

if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
</script>

<?php echo renderAdminLayoutEnd(); ?>
