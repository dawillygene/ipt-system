<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require_once 'db.php';
require_once 'includes/layout.php';

// Handle delete action
if (isset($_POST['delete']) && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    
    // Use prepared statement for security
    $stmt = $con->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    
    if ($stmt->execute()) {
        $message = "User deleted successfully.";
        $messageType = "success";
    } else {
        $message = "Error deleting user.";
        $messageType = "error";
    }
    
    // Redirect to prevent form resubmission
    header('Location: admin_users.php?msg=' . urlencode($message) . '&type=' . $messageType);
    exit;
}

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$whereClause = '';
$params = [];
$types = '';

if (!empty($search)) {
    $whereClause = "WHERE name LIKE ? OR email LIKE ? OR role LIKE ?";
    $searchParam = "%$search%";
    $params = [$searchParam, $searchParam, $searchParam];
    $types = 'sss';
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

// Get total count
$countSql = "SELECT COUNT(*) as total FROM users $whereClause";
if (!empty($params)) {
    $countStmt = $con->prepare($countSql);
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $totalRecords = $countStmt->get_result()->fetch_assoc()['total'];
} else {
    $totalRecords = $con->query($countSql)->fetch_assoc()['total'];
}

$totalPages = ceil($totalRecords / $recordsPerPage);

// Get users data
$sql = "SELECT * FROM users $whereClause ORDER BY name ASC LIMIT ? OFFSET ?";
$params[] = $recordsPerPage;
$params[] = $offset;
$types .= 'ii';

$stmt = $con->prepare($sql);
if (!empty($whereClause)) {
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param('ii', $recordsPerPage, $offset);
}
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => 'admin_dashboard.php'],
    ['name' => 'Users']
];
$pageTitle = 'Users Management';

echo renderAdminLayout($pageTitle, $breadcrumbs);
?>

<?php if (isset($_GET['msg'])): ?>
    <div class="mb-6">
        <div class="alert alert-<?php echo $_GET['type'] === 'success' ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($_GET['msg']); ?>
        </div>
    </div>
<?php endif; ?>

<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
    <div class="mb-4 lg:mb-0">
        <h1 class="text-2xl font-bold text-gray-800">Users Management</h1>
        <p class="text-gray-600">Manage system users and their access</p>
    </div>
    <div class="flex flex-col sm:flex-row gap-3">
        <a href="add-user.php" class="bg-admin-primary text-white px-6 py-3 rounded-lg hover:bg-admin-secondary transition-colors duration-200 text-center">
            <i class="fas fa-plus mr-2"></i>Add User
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="bg-gradient-to-r from-admin-primary to-admin-secondary px-6 py-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h3 class="text-lg font-semibold text-white flex items-center mb-3 md:mb-0">
                <i class="fas fa-users mr-3"></i>
                All Users (<?php echo number_format($totalRecords); ?>)
            </h3>
            <form method="GET" class="flex">
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search users..." 
                           class="pl-10 pr-4 py-2 border border-white/20 rounded-l-lg bg-white/10 text-white placeholder-white/70 focus:outline-none focus:bg-white/20">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-white/70"></i>
                </div>
                <button type="submit" class="bg-white/20 text-white px-4 py-2 rounded-r-lg hover:bg-white/30 transition-colors duration-200">
                    Search
                </button>
            </form>
        </div>
    </div>

    <?php if (empty($users)): ?>
        <div class="p-8 text-center">
            <i class="fas fa-users text-gray-300 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No Users Found</h3>
            <p class="text-gray-500 mb-4">
                <?php echo !empty($search) ? 'No users match your search criteria.' : 'No users have been added yet.'; ?>
            </p>
            <?php if (!empty($search)): ?>
                <a href="admin_users.php" class="text-admin-primary hover:text-admin-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>View All Users
                </a>
            <?php else: ?>
                <a href="add-user.php" class="bg-admin-primary text-white px-6 py-3 rounded-lg hover:bg-admin-secondary transition-colors duration-200 inline-block">
                    <i class="fas fa-plus mr-2"></i>Add First User
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-admin-primary to-admin-secondary flex items-center justify-center">
                                            <span class="text-white font-medium text-sm">
                                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($user['name']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            ID: <?php echo $user['id']; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                                <div class="text-sm text-gray-500">
                                    <?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'No phone'; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                $roleClass = 'bg-gray-100 text-gray-800';
                                if ($user['role'] === 'admin') $roleClass = 'bg-red-100 text-red-800';
                                elseif ($user['role'] === 'supervisor') $roleClass = 'bg-blue-100 text-blue-800';
                                elseif ($user['role'] === 'student') $roleClass = 'bg-green-100 text-green-800';
                                ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $roleClass; ?>">
                                    <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <a href="view_user.php?user_id=<?php echo $user['id']; ?>" 
                                   class="text-admin-primary hover:text-admin-secondary transition-colors duration-200">
                                    <i class="fas fa-eye mr-1"></i>View
                                </a>
                                <a href="edit-user.php?id=<?php echo $user['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-800 transition-colors duration-200">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </a>
                                <button onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')" 
                                        class="text-red-600 hover:text-red-800 transition-colors duration-200">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                           class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to 
                            <span class="font-medium"><?php echo min($offset + $recordsPerPage, $totalRecords); ?></span> of 
                            <span class="font-medium"><?php echo $totalRecords; ?></span> results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                                   class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            
                            for ($i = $startPage; $i <= $endPage; $i++):
                            ?>
                                <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                                   class="relative inline-flex items-center px-4 py-2 border text-sm font-medium 
                                          <?php echo $i === $page ? 'z-10 bg-admin-primary border-admin-primary text-white' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                                   class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">Delete User</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Are you sure you want to delete user <span id="userName" class="font-medium"></span>? 
                    This action cannot be undone.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <form id="deleteForm" method="POST" class="inline">
                    <input type="hidden" name="request_id" id="deleteUserId">
                    <button type="submit" name="delete" 
                            class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Delete
                    </button>
                </form>
                <button onclick="closeDeleteModal()" 
                        class="px-4 py-2 bg-gray-600 text-white text-base font-medium rounded-md w-24 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(userId, userName) {
    document.getElementById('deleteUserId').value = userId;
    document.getElementById('userName').textContent = userName;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// Prevent browser back button
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
