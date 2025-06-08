<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require_once 'db.php';
require_once 'includes/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['request_id'])) {
    $action = $_POST['action'];
    $request_id = $_POST['request_id'];
    $status = $action === 'approve' ? 'approved' : 'rejected';
    
    $stmt = $con->prepare("UPDATE user_requests SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $request_id);
    $stmt->execute();
    
    header('Location: admin_dashboard.php');
    exit;
}

function getStats($con) {
    $stats = [];
    
    $result = $con->query("SELECT COUNT(*) as total FROM users");
    $stats['users'] = $result->fetch_assoc()['total'];
    
    $result = $con->query("SELECT COUNT(*) as total FROM applications");
    $stats['applications'] = $result->fetch_assoc()['total'];
    
    $result = $con->query("SELECT COUNT(*) as total FROM students");
    $stats['students'] = $result->fetch_assoc()['total'];
    
    $result = $con->query("SELECT COUNT(*) as total FROM user_requests WHERE status = 'pending'");
    $stats['pending'] = $result->fetch_assoc()['total'];
    
    return $stats;
}

function getRecentRequests($con, $limit = 5) {
    $stmt = $con->prepare("SELECT ur.*, u.name, u.email 
                           FROM user_requests ur 
                           INNER JOIN users u ON ur.user_id = u.id 
                           ORDER BY ur.created_at DESC 
                           LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

$stats = getStats($con);
$recentRequests = getRecentRequests($con, 5);

$breadcrumbs = [['name' => 'Dashboard']];
$pageTitle = 'Dashboard';

echo renderAdminLayout($pageTitle, $breadcrumbs);
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Total Users</p>
                    <p class="text-3xl font-bold text-white"><?php echo number_format($stats['users']); ?></p>
                </div>
                <div class="bg-blue-400/30 p-3 rounded-full">
                    <i class="fas fa-users text-white text-xl"></i>
                </div>
            </div>
        </div>
        <div class="px-6 py-3">
            <a href="admin_users.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                View all users <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 overflow-hidden">
        <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Applications</p>
                    <p class="text-3xl font-bold text-white"><?php echo number_format($stats['applications']); ?></p>
                </div>
                <div class="bg-green-400/30 p-3 rounded-full">
                    <i class="fas fa-file-alt text-white text-xl"></i>
                </div>
            </div>
        </div>
        <div class="px-6 py-3">
            <a href="admin_applications.php" class="text-green-600 hover:text-green-800 text-sm font-medium flex items-center">
                View applications <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 overflow-hidden">
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">Students</p>
                    <p class="text-3xl font-bold text-white"><?php echo number_format($stats['students']); ?></p>
                </div>
                <div class="bg-purple-400/30 p-3 rounded-full">
                    <i class="fas fa-user-graduate text-white text-xl"></i>
                </div>
            </div>
        </div>
        <div class="px-6 py-3">
            <a href="admin_students.php" class="text-purple-600 hover:text-purple-800 text-sm font-medium flex items-center">
                View students <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 overflow-hidden">
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm">Pending</p>
                    <p class="text-3xl font-bold text-white"><?php echo number_format($stats['pending']); ?></p>
                </div>
                <div class="bg-orange-400/30 p-3 rounded-full">
                    <i class="fas fa-clock text-white text-xl"></i>
                </div>
            </div>
        </div>
        <div class="px-6 py-3">
            <span class="text-orange-600 text-sm font-medium">Requires attention</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2">
        <?php 
        $welcomeContent = '
            <div class="text-center lg:text-left">
                <h1 class="text-3xl font-bold text-gray-800 mb-4">Welcome Back, Admin!</h1>
                <p class="text-gray-600 mb-6">Here\'s what\'s happening with your IPT system today. Monitor applications, manage users, and keep track of student progress all from this dashboard.</p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="admin_applications.php" class="bg-admin-primary text-white px-6 py-3 rounded-lg hover:bg-admin-secondary transition-colors duration-200 text-center">
                        <i class="fas fa-plus mr-2"></i>Review Applications
                    </a>
                    <a href="admin_reports.php" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-colors duration-200 text-center">
                        <i class="fas fa-chart-bar mr-2"></i>View Reports
                    </a>
                </div>
            </div>
        ';
        echo renderAdminCard('Dashboard Overview', $welcomeContent, 'fas fa-tachometer-alt');
        ?>
    </div>

    <div>
        <?php 
        $actionsContent = '
            <div class="space-y-3">
                <a href="add-student.php" class="block p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                    <div class="flex items-center">
                        <i class="fas fa-user-plus text-admin-primary text-lg mr-3"></i>
                        <div>
                            <p class="font-medium text-gray-800">Add Student</p>
                            <p class="text-sm text-gray-500">Register new student</p>
                        </div>
                    </div>
                </a>
                <a href="add-supervisor.php" class="block p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                    <div class="flex items-center">
                        <i class="fas fa-user-tie text-admin-primary text-lg mr-3"></i>
                        <div>
                            <p class="font-medium text-gray-800">Add Supervisor</p>
                            <p class="text-sm text-gray-500">Add new supervisor</p>
                        </div>
                    </div>
                </a>
                <a href="add-evaluation.php" class="block p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                    <div class="flex items-center">
                        <i class="fas fa-clipboard-check text-admin-primary text-lg mr-3"></i>
                        <div>
                            <p class="font-medium text-gray-800">New Evaluation</p>
                            <p class="text-sm text-gray-500">Create evaluation</p>
                        </div>
                    </div>
                </a>
            </div>
        ';
        echo renderAdminCard('Quick Actions', $actionsContent, 'fas fa-bolt');
        ?>
    </div>
</div>

<?php if (!empty($recentRequests)): ?>
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="bg-gradient-to-r from-admin-primary to-admin-secondary px-6 py-4">
        <h3 class="text-lg font-semibold text-white flex items-center">
            <i class="fas fa-list mr-3"></i>
            Recent User Requests
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($recentRequests as $request): ?>
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900"><?php echo htmlspecialchars($request['name']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                            <?php echo htmlspecialchars($request['email']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                            <?php echo htmlspecialchars($request['request_type'] ?? 'General Request'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php 
                            $status = $request['status'];
                            $statusClass = $status === 'approved' ? 'bg-green-100 text-green-800' : 
                                          ($status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');
                            ?>
                            <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $statusClass; ?>">
                                <?php echo ucfirst(htmlspecialchars($status)); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?php if ($request['status'] === 'pending'): ?>
                                <form method="POST" class="flex space-x-2" style="display: inline;">
                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                    <button type="submit" name="action" value="approve" 
                                            class="bg-green-500 text-white px-3 py-1 rounded text-xs hover:bg-green-600 transition-colors duration-200">
                                        <i class="fas fa-check mr-1"></i>Approve
                                    </button>
                                    <button type="submit" name="action" value="reject" 
                                            class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600 transition-colors duration-200">
                                        <i class="fas fa-times mr-1"></i>Reject
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-gray-400 text-xs">Processed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script>
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

<?php echo renderAdminLayoutEnd(); ?>
