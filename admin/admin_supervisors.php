<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require_once 'db.php';
require_once 'includes/layout.php';

// Handle delete action
if (isset($_POST['delete']) && isset($_POST['supervisor_id'])) {
    $supervisor_id = $_POST['supervisor_id'];
    
    $stmt = $con->prepare("DELETE FROM supervisors WHERE id = ?");
    $stmt->bind_param("i", $supervisor_id);
    
    if ($stmt->execute()) {
        $message = "Supervisor deleted successfully.";
        $messageType = "success";
    } else {
        $message = "Error deleting supervisor.";
        $messageType = "error";
    }
    
    header('Location: admin_supervisors.php?msg=' . urlencode($message) . '&type=' . $messageType);
    exit;
}

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$whereClause = '';
$params = [];
$types = '';

if (!empty($search)) {
    $whereClause = "WHERE u.name LIKE ? OR u.email LIKE ? OR s.department LIKE ?";
    $searchParam = "%$search%";
    $params = [$searchParam, $searchParam, $searchParam];
    $types = 'sss';
}

// Get supervisors data with proper JOIN
$sql = "SELECT s.*, u.name, u.email, u.phone, u.created_at, u.status 
        FROM supervisors s 
        INNER JOIN users u ON s.user_id = u.id 
        $whereClause 
        ORDER BY u.name ASC";

if (!empty($whereClause)) {
    $stmt = $con->prepare($sql);
    $stmt->bind_param($types, ...$params);
} else {
    $stmt = $con->prepare($sql);
}
$stmt->execute();
$supervisors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => 'admin_dashboard.php'],
    ['name' => 'Supervisors']
];
$pageTitle = 'Supervisors Management';

echo renderAdminLayout($pageTitle, $breadcrumbs);
?>

<?php if (isset($_GET['msg'])): ?>
    <div class="mb-6 bg-<?php echo $_GET['type'] === 'success' ? 'green' : 'red'; ?>-50 border border-<?php echo $_GET['type'] === 'success' ? 'green' : 'red'; ?>-200 text-<?php echo $_GET['type'] === 'success' ? 'green' : 'red'; ?>-700 px-4 py-3 rounded-lg flex items-center">
        <i class="fas fa-<?php echo $_GET['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?> mr-3"></i>
        <?php echo htmlspecialchars($_GET['msg']); ?>
    </div>
<?php endif; ?>

<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
    <div class="mb-4 lg:mb-0">
        <h1 class="text-2xl font-bold text-gray-800">Supervisors Management</h1>
        <p class="text-gray-600">Manage training supervisors and their information</p>
    </div>
    <div class="flex flex-col sm:flex-row gap-3">
        <a href="add-supervisor.php" class="bg-admin-primary text-white px-6 py-3 rounded-lg hover:bg-admin-secondary transition-colors duration-200 text-center">
            <i class="fas fa-plus mr-2"></i>Add Supervisor
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="bg-gradient-to-r from-admin-primary to-admin-secondary px-6 py-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <h3 class="text-lg font-semibold text-white flex items-center mb-3 md:mb-0">
                <i class="fas fa-user-tie mr-3"></i>
                All Supervisors (<?php echo count($supervisors); ?>)
            </h3>
            <form method="GET" class="flex">
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search supervisors..." 
                           class="pl-10 pr-4 py-2 border border-white/20 rounded-l-lg bg-white/10 text-white placeholder-white/70 focus:outline-none focus:bg-white/20">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-white/70"></i>
                </div>
                <button type="submit" class="bg-white/20 text-white px-4 py-2 rounded-r-lg hover:bg-white/30 transition-colors duration-200">
                    Search
                </button>
            </form>
        </div>
    </div>

    <?php if (empty($supervisors)): ?>
        <div class="p-8 text-center">
            <i class="fas fa-user-tie text-gray-300 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No Supervisors Found</h3>
            <p class="text-gray-500 mb-4">
                <?php echo !empty($search) ? 'No supervisors match your search criteria.' : 'No supervisors have been added yet.'; ?>
            </p>
            <?php if (!empty($search)): ?>
                <a href="admin_supervisors.php" class="text-admin-primary hover:text-admin-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>View All Supervisors
                </a>
            <?php else: ?>
                <a href="add-supervisor.php" class="bg-admin-primary text-white px-6 py-3 rounded-lg hover:bg-admin-secondary transition-colors duration-200 inline-block">
                    <i class="fas fa-plus mr-2"></i>Add First Supervisor
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supervisor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($supervisors as $supervisor): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-admin-primary to-admin-secondary flex items-center justify-center">
                                            <span class="text-white font-medium text-sm">
                                                <?php echo strtoupper(substr($supervisor['name'], 0, 1)); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($supervisor['name']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            ID: <?php echo $supervisor['id']; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($supervisor['email']); ?></div>
                                <div class="text-sm text-gray-500">
                                    <?php echo !empty($supervisor['phone']) ? htmlspecialchars($supervisor['phone']) : 'No phone'; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php echo !empty($supervisor['department']) ? htmlspecialchars($supervisor['department']) : 'Not specified'; ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?php echo !empty($supervisor['contact_info']) ? htmlspecialchars($supervisor['contact_info']) : 'No contact info'; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                $statusClass = 'bg-gray-100 text-gray-800';
                                $status = $supervisor['status'] ?? 'Pending';
                                if (strtolower($status) === 'active') $statusClass = 'bg-green-100 text-green-800';
                                elseif (strtolower($status) === 'pending') $statusClass = 'bg-yellow-100 text-yellow-800';
                                elseif (strtolower($status) === 'inactive') $statusClass = 'bg-red-100 text-red-800';
                                ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <a href="view_user.php?user_id=<?php echo $supervisor['user_id']; ?>" 
                                   class="text-admin-primary hover:text-admin-secondary transition-colors duration-200">
                                    <i class="fas fa-eye mr-1"></i>View
                                </a>
                                <button onclick="confirmDelete(<?php echo $supervisor['id']; ?>, '<?php echo htmlspecialchars($supervisor['name']); ?>')" 
                                        class="text-red-600 hover:text-red-800 transition-colors duration-200">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">Delete Supervisor</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Are you sure you want to delete supervisor <span id="supervisorName" class="font-medium"></span>? 
                    This action cannot be undone.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <form id="deleteForm" method="POST" class="inline">
                    <input type="hidden" name="supervisor_id" id="deleteSupervisorId">
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
function confirmDelete(supervisorId, supervisorName) {
    document.getElementById('deleteSupervisorId').value = supervisorId;
    document.getElementById('supervisorName').textContent = supervisorName;
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
