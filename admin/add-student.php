<?php
// Add error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Test database connection with error handling
try {
    require_once 'db.php';
    
    // Test if database connection is working
    if (!isset($con) || !$con) {
        throw new Exception('Database connection not established');
    }
    
    // Test a simple query
    $test_query = $con->query("SELECT 1");
    if (!$test_query) {
        throw new Exception('Database query failed: ' . $con->error);
    }
    
} catch (Exception $e) {
    die('Database Error: ' . $e->getMessage());
}

require_once 'includes/layout.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = trim($_POST['user_id'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $college_name = trim($_POST['college_name'] ?? '');
    $course_name = trim($_POST['course_name'] ?? '');
    $year_of_study = $_POST['year_of_study'] ?? '';
    $phone_number = trim($_POST['phone_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Validate required fields
    if (empty($user_id) || empty($student_id) || empty($college_name) || empty($course_name) || empty($year_of_study)) {
        $error_message = 'All required fields must be filled.';
    } else {
        // Check if user exists
        $stmt = $con->prepare("SELECT id, name FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error_message = 'Selected user does not exist.';
        } else {
            $user_data = $result->fetch_assoc();
            
            // Check if student profile already exists for this user
            $stmt = $con->prepare("SELECT student_id FROM students WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error_message = 'Student profile already exists for this user.';
            } else {
                // Check if student ID/reg_number is unique
                $stmt = $con->prepare("SELECT student_id FROM students WHERE reg_number = ?");
                $stmt->bind_param("s", $student_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $error_message = 'Student ID already exists. Please use a unique student ID.';
                } else {
                    // Convert year of study to number
                    $year_number = (int) filter_var($year_of_study, FILTER_SANITIZE_NUMBER_INT);
                    
                    // Insert the student profile with the correct table structure
                    $stmt = $con->prepare("INSERT INTO students (user_id, full_name, reg_number, gender, college_name, department, course_name, program, level, year_of_study, phone_number, address, email, created_at) VALUES (?, ?, ?, 'Other', ?, ?, ?, ?, '6', ?, ?, ?, NULL, NOW())");
                    $stmt->bind_param("isssssssss", $user_id, $user_data['name'], $student_id, $college_name, $course_name, $course_name, $course_name, $year_number, $phone_number, $address);
                    
                    if ($stmt->execute()) {
                        $success_message = 'Student profile added successfully!';
                        // Clear form data on success
                        $user_id = $student_id = $college_name = $course_name = $year_of_study = $phone_number = $address = '';
                    } else {
                        $error_message = 'Error adding student profile: ' . $con->error;
                    }
                }
            }
        }
    }
}

// Get users who don't have student profiles yet
$users_query = "SELECT u.id, u.name, u.email 
                FROM users u 
                LEFT JOIN students s ON u.id = s.user_id 
                WHERE s.student_id IS NULL 
                ORDER BY u.name";
$users_result = $con->query($users_query);

$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => 'admin_dashboard.php'],
    ['name' => 'Students', 'url' => 'admin_students.php'],
    ['name' => 'Add Student']
];
$pageTitle = 'Add Student';

echo renderAdminLayout($pageTitle, $breadcrumbs);
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gradient-to-r from-admin-primary to-admin-secondary px-6 py-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Add Student Profile</h1>
                    <p class="mt-2 text-white/80">Create a new student profile for an existing user</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="admin_students.php" class="bg-white/20 text-white px-4 py-2 rounded-lg hover:bg-white/30 transition-colors duration-200 inline-flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Students
                    </a>
                </div>
            </div>
        </div>
    </div>
    
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
                <i class="fas fa-user-graduate mr-3"></i>
                Student Information
            </h3>
        </div>
        
        <form method="POST" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-admin-primary"></i>
                        Select User <span class="text-red-500">*</span>
                    </label>
                    <select name="user_id" id="user_id" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200">
                        <option value="">-- Select a User --</option>
                        <?php if ($users_result && $users_result->num_rows > 0): ?>
                            <?php while ($user = $users_result->fetch_assoc()): ?>
                                <option value="<?php echo $user['id']; ?>" <?php echo (isset($user_id) && $user_id == $user['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['name'] . ' (' . $user['email'] . ')'); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <option value="">No available users</option>
                        <?php endif; ?>
                    </select>
                    <p class="text-sm text-gray-500 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Only users with 'student' role who don't have profiles yet are shown
                    </p>
                </div>

                <div>
                    <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-id-card mr-2 text-admin-primary"></i>
                        Student ID <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="student_id" id="student_id" required
                           value="<?php echo htmlspecialchars($student_id ?? ''); ?>"
                           placeholder="e.g., ST2024001"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200">
                </div>

                <div>
                    <label for="college_name" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-university mr-2 text-admin-primary"></i>
                        College Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="college_name" id="college_name" required
                           value="<?php echo htmlspecialchars($college_name ?? ''); ?>"
                           placeholder="Enter college name"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200">
                </div>

                <div>
                    <label for="course_name" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-book mr-2 text-admin-primary"></i>
                        Course Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="course_name" id="course_name" required
                           value="<?php echo htmlspecialchars($course_name ?? ''); ?>"
                           placeholder="Enter course name"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200">
                </div>

                <div>
                    <label for="year_of_study" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-2 text-admin-primary"></i>
                        Year of Study <span class="text-red-500">*</span>
                    </label>
                    <select name="year_of_study" id="year_of_study" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200">
                        <option value="">-- Select Year --</option>
                        <option value="1st Year" <?php echo (isset($year_of_study) && $year_of_study == '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                        <option value="2nd Year" <?php echo (isset($year_of_study) && $year_of_study == '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                        <option value="3rd Year" <?php echo (isset($year_of_study) && $year_of_study == '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                        <option value="4th Year" <?php echo (isset($year_of_study) && $year_of_study == '4th Year') ? 'selected' : ''; ?>>4th Year</option>
                        <option value="5th Year" <?php echo (isset($year_of_study) && $year_of_study == '5th Year') ? 'selected' : ''; ?>>5th Year</option>
                    </select>
                </div>

                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-phone mr-2 text-admin-primary"></i>
                        Phone Number
                    </label>
                    <input type="tel" name="phone_number" id="phone_number"
                           value="<?php echo htmlspecialchars($phone_number ?? ''); ?>"
                           placeholder="Enter phone number"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200">
                </div>
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-map-marker-alt mr-2 text-admin-primary"></i>
                    Address
                </label>
                <textarea name="address" id="address" rows="3"
                          placeholder="Enter address"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200"><?php echo htmlspecialchars($address ?? ''); ?></textarea>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
                <button type="submit" 
                        class="bg-admin-primary text-white px-6 py-3 rounded-lg hover:bg-admin-secondary transition-colors duration-200 flex items-center justify-center font-medium">
                    <i class="fas fa-plus mr-2"></i>
                    Add Student Profile
                </button>
                <a href="admin_students.php" 
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

// Phone number formatting
document.getElementById('phone_number').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 10) {
        value = value.slice(0, 10);
    }
    e.target.value = value;
});

// Form validation feedback
document.getElementById('student_id').addEventListener('blur', function(e) {
    const value = e.target.value;
    if (value && !/^[A-Z]{2}\d{4,}$/.test(value)) {
        e.target.classList.add('border-yellow-500');
        e.target.classList.remove('border-gray-300');
    } else {
        e.target.classList.remove('border-yellow-500');
        e.target.classList.add('border-gray-300');
    }
});
</script>

<?php echo renderAdminLayoutEnd(); ?>
