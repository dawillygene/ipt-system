<?php
session_start();
require_once 'includes/supervisor_db.php';

checkSupervisorSession();

$supervisor_id = $_SESSION['supervisor_id'];
$supervisor = getSupervisorInfo($con, $supervisor_id);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if (!password_verify($current_password, $supervisor['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters long.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } else {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $con->prepare("UPDATE supervisors SET password = ? WHERE supervisor_id = ?");
            $stmt->bind_param("si", $hashed_password, $supervisor_id);
            
            if ($stmt->execute()) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password. Please try again.';
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
    <title>Settings - Supervisor Dashboard</title>
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
                    <a href="profile.php" class="flex items-center space-x-3 text-gray-700 p-2 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-user"></i>
                        <span>My Profile</span>
                    </a>
                    <a href="settings.php" class="flex items-center space-x-3 text-supervisor-primary p-2 rounded-lg bg-supervisor-primary bg-opacity-10">
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
                    <h1 class="text-3xl font-bold text-gray-900">Settings</h1>
                    <p class="text-gray-600 mt-2">Manage your account settings and preferences</p>
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

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Password Change -->
                    <div class="bg-white shadow-lg rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Change Password</h2>
                            <p class="text-sm text-gray-600 mt-1">Update your account password</p>
                        </div>
                        
                        <form method="POST" class="p-6">
                            <input type="hidden" name="change_password" value="1">
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary focus:border-transparent">
                                </div>

                                <div>
                                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                    <input type="password" id="new_password" name="new_password" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary focus:border-transparent">
                                </div>

                                <div>
                                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary focus:border-transparent">
                                </div>
                            </div>

                            <div class="mt-6">
                                <button type="submit" class="w-full px-4 py-2 bg-supervisor-primary text-white rounded-lg hover:bg-supervisor-secondary transition">
                                    <i class="fas fa-key mr-2"></i>Change Password
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Account Information -->
                    <div class="bg-white shadow-lg rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Account Information</h2>
                            <p class="text-sm text-gray-600 mt-1">Your account details</p>
                        </div>
                        
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="font-medium text-gray-700">Email:</span>
                                    <span class="text-gray-600"><?php echo htmlspecialchars($supervisor['email']); ?></span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="font-medium text-gray-700">Department:</span>
                                    <span class="text-gray-600"><?php echo htmlspecialchars($supervisor['department'] ?? 'Not specified'); ?></span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="font-medium text-gray-700">Institution:</span>
                                    <span class="text-gray-600"><?php echo htmlspecialchars($supervisor['institution'] ?? 'Not specified'); ?></span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="font-medium text-gray-700">Experience:</span>
                                    <span class="text-gray-600"><?php echo $supervisor['years_experience']; ?> years</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="font-medium text-gray-700">Status:</span>
                                    <span class="text-green-600 capitalize"><?php echo htmlspecialchars($supervisor['status']); ?></span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="font-medium text-gray-700">Member Since:</span>
                                    <span class="text-gray-600"><?php echo date('M d, Y', strtotime($supervisor['created_at'])); ?></span>
                                </div>
                            </div>

                            <div class="mt-6">
                                <a href="profile.php" class="w-full inline-block text-center px-4 py-2 border border-supervisor-primary text-supervisor-primary rounded-lg hover:bg-supervisor-primary hover:text-white transition">
                                    <i class="fas fa-edit mr-2"></i>Edit Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Overview -->
                <div class="mt-8 bg-white shadow-lg rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Account Statistics</h2>
                        <p class="text-sm text-gray-600 mt-1">Overview of your activity</p>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-supervisor-primary">
                                    <?php 
                                    $stmt = $con->prepare("SELECT COUNT(*) as count FROM supervisor_assignments WHERE supervisor_id = ? AND status = 'active'");
                                    $stmt->bind_param("i", $supervisor_id);
                                    $stmt->execute();
                                    echo $stmt->get_result()->fetch_assoc()['count'];
                                    ?>
                                </div>
                                <div class="text-sm text-gray-600 mt-1">Assigned Students</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-supervisor-primary">
                                    <?php 
                                    $stmt = $con->prepare("SELECT COUNT(*) as count FROM report_reviews WHERE supervisor_id = ?");
                                    $stmt->bind_param("i", $supervisor_id);
                                    $stmt->execute();
                                    echo $stmt->get_result()->fetch_assoc()['count'];
                                    ?>
                                </div>
                                <div class="text-sm text-gray-600 mt-1">Reports Reviewed</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-supervisor-primary">
                                    <?php 
                                    $stmt = $con->prepare("SELECT COUNT(*) as count FROM student_evaluations WHERE supervisor_id = ?");
                                    $stmt->bind_param("i", $supervisor_id);
                                    $stmt->execute();
                                    echo $stmt->get_result()->fetch_assoc()['count'];
                                    ?>
                                </div>
                                <div class="text-sm text-gray-600 mt-1">Evaluations Done</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-supervisor-primary">
                                    <?php 
                                    $stmt = $con->prepare("SELECT COUNT(*) as count FROM supervisor_meetings WHERE supervisor_id = ?");
                                    $stmt->bind_param("i", $supervisor_id);
                                    $stmt->execute();
                                    echo $stmt->get_result()->fetch_assoc()['count'];
                                    ?>
                                </div>
                                <div class="text-sm text-gray-600 mt-1">Meetings Scheduled</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password strength indicator
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const indicator = document.getElementById('password-strength');
            
            if (password.length < 6) {
                this.classList.remove('border-green-300');
                this.classList.add('border-red-300');
            } else {
                this.classList.remove('border-red-300');
                this.classList.add('border-green-300');
            }
        });

        // Confirm password match
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('new_password').value;
            const confirm = this.value;
            
            if (password === confirm && password.length >= 6) {
                this.classList.remove('border-red-300');
                this.classList.add('border-green-300');
            } else {
                this.classList.remove('border-green-300');
                this.classList.add('border-red-300');
            }
        });
    </script>
</body>
</html>
