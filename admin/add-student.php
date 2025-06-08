<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require_once 'db.php';
require_once 'includes/layout.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $student_id = $_POST['student_id'] ?? '';
    $college_name = $_POST['college_name'] ?? '';
    $course_name = $_POST['course_name'] ?? '';
    $year_of_study = $_POST['year_of_study'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $address = $_POST['address'] ?? '';
    
    // Validate required fields
    if (empty($user_id) || empty($student_id) || empty($college_name) || empty($course_name) || empty($year_of_study)) {
        $error_message = 'All required fields must be filled.';
    } else {
        // Check if user exists and is not already a student
        $stmt = $con->prepare("SELECT id FROM users WHERE id = ? AND role = 'student'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error_message = 'Selected user does not exist or is not a student.';
        } else {
            // Check if student profile already exists for this user
            $stmt = $con->prepare("SELECT id FROM students WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error_message = 'Student profile already exists for this user.';
            } else {
                // Check if student ID is unique
                $stmt = $con->prepare("SELECT id FROM students WHERE student_id = ?");
                $stmt->bind_param("s", $student_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $error_message = 'Student ID already exists. Please use a unique student ID.';
                } else {
                    // Insert the student profile
                    $stmt = $con->prepare("INSERT INTO students (user_id, student_id, college_name, course_name, year_of_study, phone_number, address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->bind_param("issssss", $user_id, $student_id, $college_name, $course_name, $year_of_study, $phone_number, $address);
                    
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

// Get users with student role who don't have student profiles yet
$users_query = "SELECT u.id, u.name, u.email 
                FROM users u 
                LEFT JOIN students s ON u.id = s.user_id 
                WHERE u.role = 'student' AND s.id IS NULL 
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
    <?php 
    $headerContent = '
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Add Student Profile</h1>
            <a href="admin_students.php" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Students
            </a>
        </div>
    ';
    echo $headerContent;
    ?>
    
    <?php if ($success_message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Select User <span class="text-red-500">*</span>
                    </label>
                    <select name="user_id" id="user_id" required 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
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
                    <p class="text-sm text-gray-500 mt-1">Only users with 'student' role who don't have profiles yet are shown</p>
                </div>

                <div>
                    <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Student ID <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="student_id" id="student_id" required
                           value="<?php echo htmlspecialchars($student_id ?? ''); ?>"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., ST2024001">
                </div>

                <div>
                    <label for="college_name" class="block text-sm font-medium text-gray-700 mb-2">
                        College Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="college_name" id="college_name" required
                           value="<?php echo htmlspecialchars($college_name ?? ''); ?>"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Enter college name">
                </div>

                <div>
                    <label for="course_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Course Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="course_name" id="course_name" required
                           value="<?php echo htmlspecialchars($course_name ?? ''); ?>"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Enter course name">
                </div>

                <div>
                    <label for="year_of_study" class="block text-sm font-medium text-gray-700 mb-2">
                        Year of Study <span class="text-red-500">*</span>
                    </label>
                    <select name="year_of_study" id="year_of_study" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
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
                        Phone Number
                    </label>
                    <input type="text" name="phone_number" id="phone_number"
                           value="<?php echo htmlspecialchars($phone_number ?? ''); ?>"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Enter phone number">
                </div>
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                    Address
                </label>
                <textarea name="address" id="address" rows="3"
                          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Enter address"><?php echo htmlspecialchars($address ?? ''); ?></textarea>
            </div>

            <div class="flex items-center justify-between pt-4">
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Add Student Profile
                </button>
                <a href="admin_students.php" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php echo renderAdminFooter(); ?>
