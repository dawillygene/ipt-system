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
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id <= 0) {
    header('Location: admin_users.php');
    exit;
}

// Get user data
$stmt = $con->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header('Location: admin_users.php?msg=' . urlencode('User not found') . '&type=error');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($role)) {
        $error_message = 'Name, email, and role are required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // Check if email exists for other users
        $email_check_stmt = $con->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $email_check_stmt->bind_param("si", $email, $user_id);
        $email_check_stmt->execute();
        $email_result = $email_check_stmt->get_result();
        
        if ($email_result->num_rows > 0) {
            $error_message = 'Email address is already in use by another user.';
        } else {
            // Update user
            if (!empty($password)) {
                // Update with new password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = $con->prepare("UPDATE users SET name = ?, email = ?, role = ?, phone = ?, address = ?, password = ? WHERE id = ?");
                $update_stmt->bind_param("ssssssi", $name, $email, $role, $phone, $address, $hashed_password, $user_id);
            } else {
                // Update without changing password
                $update_stmt = $con->prepare("UPDATE users SET name = ?, email = ?, role = ?, phone = ?, address = ? WHERE id = ?");
                $update_stmt->bind_param("sssssi", $name, $email, $role, $phone, $address, $user_id);
            }
            
            if ($update_stmt->execute()) {
                $success_message = 'User updated successfully!';
                // Refresh user data
                $stmt = $con->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
            } else {
                $error_message = 'Error updating user: ' . $con->error;
            }
        }
    }
}

$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => 'admin_dashboard.php'],
    ['name' => 'Users', 'url' => 'admin_users.php'],
    ['name' => 'Edit User']
];
$pageTitle = 'Edit User';

echo renderAdminLayout($pageTitle, $breadcrumbs);
?>

<div class="max-w-4xl mx-auto">
    <?php 
    $headerContent = '
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit User</h1>
                <p class="mt-2 text-gray-600">Update user information and permissions</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="admin_users.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors duration-200 inline-flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Users
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
                <i class="fas fa-user-edit mr-3"></i>
                User Information
            </h3>
        </div>
        
        <form method="POST" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-admin-primary"></i>
                        Full Name
                    </label>
                    <input type="text" name="name" id="name" required
                           value="<?php echo htmlspecialchars($user['name']); ?>"
                           placeholder="Enter full name"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-admin-primary"></i>
                        Email Address
                    </label>
                    <input type="email" name="email" id="email" required
                           value="<?php echo htmlspecialchars($user['email']); ?>"
                           placeholder="Enter email address"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user-tag mr-2 text-admin-primary"></i>
                        Role
                    </label>
                    <select name="role" id="role" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200">
                        <option value="">Select role...</option>
                        <option value="Supervisor" <?php echo ($user['role'] === 'Supervisor') ? 'selected' : ''; ?>>Supervisor</option>
                        <option value="Invigilator" <?php echo ($user['role'] === 'Invigilator') ? 'selected' : ''; ?>>Invigilator</option>
                    </select>
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-phone mr-2 text-admin-primary"></i>
                        Phone Number
                    </label>
                    <input type="tel" name="phone" id="phone"
                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
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
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2 text-admin-primary"></i>
                    New Password
                </label>
                <input type="password" name="password" id="password"
                       placeholder="Leave blank to keep current password"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-admin-primary focus:border-admin-primary transition-colors duration-200">
                <p class="mt-2 text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Leave empty to keep the current password unchanged
                </p>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                    User Details
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">User ID:</span>
                        <span class="text-gray-900 font-medium">#<?php echo $user['id']; ?></span>
                    </div>
                    <div>
                        <span class="text-gray-500">Created:</span>
                        <span class="text-gray-900 font-medium"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                    </div>
                    <div>
                        <span class="text-gray-500">Status:</span>
                        <span class="text-gray-900 font-medium"><?php echo ucfirst($user['status'] ?? 'Pending'); ?></span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
                <button type="submit"
                        class="bg-admin-primary text-white px-6 py-3 rounded-lg hover:bg-admin-secondary transition-colors duration-200 flex items-center justify-center font-medium">
                    <i class="fas fa-save mr-2"></i>
                    Update User
                </button>
                <a href="admin_users.php"
                   class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition-colors duration-200 flex items-center justify-center font-medium">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </a>
                <a href="view_user.php?user_id=<?php echo $user['id']; ?>"
                   class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition-colors duration-200 flex items-center justify-center font-medium">
                    <i class="fas fa-eye mr-2"></i>
                    View Details
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

// Form validation
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 10) {
        value = value.slice(0, 10);
    }
    e.target.value = value;
});

// Email validation
document.getElementById('email').addEventListener('blur', function(e) {
    const email = e.target.value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (email && !emailRegex.test(email)) {
        e.target.classList.add('border-red-500');
        e.target.classList.remove('border-gray-300');
    } else {
        e.target.classList.remove('border-red-500');
        e.target.classList.add('border-gray-300');
    }
});

// Password strength indicator
document.getElementById('password').addEventListener('input', function(e) {
    const password = e.target.value;
    if (password.length > 0 && password.length < 6) {
        e.target.classList.add('border-yellow-500');
        e.target.classList.remove('border-gray-300');
    } else if (password.length >= 6) {
        e.target.classList.add('border-green-500');
        e.target.classList.remove('border-gray-300', 'border-yellow-500');
    } else {
        e.target.classList.remove('border-yellow-500', 'border-green-500');
        e.target.classList.add('border-gray-300');
    }
});
</script>

<?php echo renderAdminLayoutEnd(); ?>