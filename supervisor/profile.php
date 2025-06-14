<?php
session_start();
require_once 'includes/supervisor_db.php';

checkSupervisorSession();

$supervisor_id = $_SESSION['supervisor_id'];
$supervisor = getSupervisorInfo($con, $supervisor_id);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $department = trim($_POST['department']);
    $title = trim($_POST['title']); // This is now institution
    $experience_years = (int)$_POST['experience_years'];
    $specialization = trim($_POST['specialization']);
    
    // Validation
    if (empty($name) || empty($email)) {
        $error = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';        } else {
            // Check if email is taken by another supervisor
            $stmt = $con->prepare("SELECT supervisor_id FROM supervisors WHERE email = ? AND supervisor_id != ?");
            $stmt->bind_param("si", $email, $supervisor_id);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'Email address is already taken by another supervisor.';
            } else {
                // Update supervisor profile
                $stmt = $con->prepare("
                    UPDATE supervisors 
                    SET supervisor_name = ?, email = ?, phone_number = ?, department = ?, institution = ?, 
                        years_experience = ?, specialization = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE supervisor_id = ?
                ");
                $stmt->bind_param("sssssssi", $name, $email, $phone, $department, $title, $experience_years, $specialization, $supervisor_id);
                
                if ($stmt->execute()) {
                    $success = 'Profile updated successfully!';
                    // Update session with new name
                    $_SESSION['supervisor_name'] = $name;
                    // Refresh supervisor data
                    $supervisor = getSupervisorInfo($con, $supervisor_id);
                } else {
                    $error = 'Failed to update profile. Please try again.';
                }
            }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Supervisor Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'supervisor-primary': '#07442d',
                        'supervisor-secondary': '#206f56',
                        'supervisor-accent': '#0f7b5a',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-supervisor-primary shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-user-tie text-white text-xl"></i>
                    <span class="text-white font-semibold text-xl">Supervisor Dashboard</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-white">Welcome, <?php echo htmlspecialchars($supervisor['supervisor_name']); ?></span>
                    <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg min-h-screen">
            <div class="p-4">
                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center space-x-3 text-gray-700 p-2 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="students.php" class="flex items-center space-x-3 text-gray-700 p-2 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-user-graduate"></i>
                        <span>My Students</span>
                    </a>
                    <a href="reports.php" class="flex items-center space-x-3 text-gray-700 p-2 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-file-alt"></i>
                        <span>Reports</span>
                    </a>
                    <a href="evaluations.php" class="flex items-center space-x-3 text-gray-700 p-2 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Evaluations</span>
                    </a>
                    <a href="meetings.php" class="flex items-center space-x-3 text-gray-700 p-2 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Meetings</span>
                    </a>
                    <a href="messages.php" class="flex items-center space-x-3 text-gray-700 p-2 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-envelope"></i>
                        <span>Messages</span>
                    </a>
                    <a href="profile.php" class="flex items-center space-x-3 text-supervisor-primary p-2 rounded-lg bg-supervisor-primary bg-opacity-10">
                        <i class="fas fa-user"></i>
                        <span>My Profile</span>
                    </a>
                    <a href="settings.php" class="flex items-center space-x-3 text-gray-700 p-2 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="max-w-4xl mx-auto">
                <!-- Page Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
                    <p class="text-gray-600 mt-2">Manage your profile information and preferences</p>
                </div>

                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <!-- Profile Form -->
                <div class="bg-white shadow-lg rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Profile Information</h2>
                    </div>
                    
                    <form method="POST" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                                <input type="text" id="name" name="name" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary focus:border-transparent"
                                       value="<?php echo htmlspecialchars($supervisor['supervisor_name']); ?>">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                                <input type="email" id="email" name="email" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary focus:border-transparent"
                                       value="<?php echo htmlspecialchars($supervisor['email']); ?>">
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="tel" id="phone" name="phone"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary focus:border-transparent"
                                       value="<?php echo htmlspecialchars($supervisor['phone_number'] ?? ''); ?>">
                            </div>

                            <div>
                                <label for="department" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                                <input type="text" id="department" name="department"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary focus:border-transparent"
                                       value="<?php echo htmlspecialchars($supervisor['department'] ?? ''); ?>">
                            </div>

                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Institution</label>
                                <input type="text" id="title" name="title"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary focus:border-transparent"
                                       value="<?php echo htmlspecialchars($supervisor['institution'] ?? ''); ?>">
                            </div>

                            <div>
                                <label for="experience_years" class="block text-sm font-medium text-gray-700 mb-2">Years of Experience</label>
                                <select id="experience_years" name="experience_years"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary focus:border-transparent">
                                    <?php for ($i = 0; $i <= 40; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo ($supervisor['years_experience'] == $i) ? 'selected' : ''; ?>>
                                            <?php echo $i; ?> <?php echo $i == 1 ? 'year' : 'years'; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="specialization" class="block text-sm font-medium text-gray-700 mb-2">Specialization/Expertise</label>
                            <textarea id="specialization" name="specialization" rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary focus:border-transparent"
                                      placeholder="Describe your areas of expertise and specialization"><?php echo htmlspecialchars($supervisor['specialization'] ?? ''); ?></textarea>
                        </div>

                        <!-- Profile Statistics -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Profile Statistics</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-supervisor-primary">
                                        <?php 
                                        // Get assigned students count
                                        $stmt = $con->prepare("SELECT COUNT(*) as count FROM supervisor_assignments WHERE supervisor_id = ? AND status = 'active'");
                                        $stmt->bind_param("i", $supervisor_id);
                                        $stmt->execute();
                                        $assigned_students = $stmt->get_result()->fetch_assoc()['count'];
                                        echo $assigned_students;
                                        ?>
                                    </div>
                                    <div class="text-sm text-gray-600">Assigned Students</div>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-supervisor-primary">
                                        <?php 
                                        // Get evaluations count
                                        $stmt = $con->prepare("SELECT COUNT(*) as count FROM student_evaluations WHERE supervisor_id = ?");
                                        $stmt->bind_param("i", $supervisor_id);
                                        $stmt->execute();
                                        $evaluations_done = $stmt->get_result()->fetch_assoc()['count'];
                                        echo $evaluations_done;
                                        ?>
                                    </div>
                                    <div class="text-sm text-gray-600">Evaluations Completed</div>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-gray-600">
                                        <?php echo date('M Y', strtotime($supervisor['created_at'])); ?>
                                    </div>
                                    <div class="text-sm text-gray-600">Member Since</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end space-x-4">
                            <a href="dashboard.php" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-supervisor-primary text-white rounded-lg hover:bg-supervisor-secondary transition">
                                <i class="fas fa-save mr-2"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
