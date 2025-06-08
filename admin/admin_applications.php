<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

require_once 'db.php';
require_once 'includes/layout.php';

// Function to fetch user data by user ID
function getUserData($userId, $con) {
    $userDataSql = "SELECT * FROM users WHERE id = '$userId'";
    $userDataResult = mysqli_query($con, $userDataSql);
    return mysqli_fetch_assoc($userDataResult);
}

// If an action (approve or reject) is submitted for a request, update the request status
if (isset($_POST['action']) && isset($_POST['request_id'])) {
    $action = $_POST['action'];
    $request_id = $_POST['request_id'];
    $status = $action === 'approve' ? 'approved' : 'rejected';

    $update_sql = "UPDATE applications SET status = '$status' WHERE id = '$request_id'";
    mysqli_query($con, $update_sql);
    
    // Redirect to prevent form resubmission
    header('Location: admin_applications.php');
    exit;
}

// Retrieve applications from the database with user information
$applications_sql = "SELECT a.*, u.name as user_name, u.email as user_email 
                     FROM applications a 
                     LEFT JOIN users u ON a.user_id = u.id 
                     ORDER BY a.created_at DESC";
$applications_result = mysqli_query($con, $applications_sql);

$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => 'admin_dashboard.php'],
    ['name' => 'Applications']
];
$pageTitle = 'Applications Management';

echo renderAdminLayout($pageTitle, $breadcrumbs);
?>

<!-- Applications Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Applications Management</h1>
        <p class="text-gray-600">Monitor and manage all student applications</p>
    </div>
    <div class="mt-4 sm:mt-0">
        <a href="add-application.php" 
           class="inline-flex items-center px-4 py-2 bg-admin-primary text-white rounded-lg hover:bg-admin-secondary transition-colors duration-200 shadow-lg hover:shadow-xl">
            <i class="fas fa-plus mr-2"></i>
            Add New Application
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <?php
    // Get application stats
    $total_apps = mysqli_num_rows($applications_result);
    mysqli_data_seek($applications_result, 0); // Reset pointer
    
    $pending_count = 0;
    $approved_count = 0;
    $rejected_count = 0;
    
    while ($app = mysqli_fetch_assoc($applications_result)) {
        switch($app['status']) {
            case 'pending': $pending_count++; break;
            case 'approved': $approved_count++; break;
            case 'rejected': $rejected_count++; break;
        }
    }
    mysqli_data_seek($applications_result, 0); // Reset pointer again
    ?>
    
    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-200">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-full">
                <i class="fas fa-file-alt text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Applications</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $total_apps; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-200">
        <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-full">
                <i class="fas fa-clock text-yellow-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Pending</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $pending_count; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-200">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-full">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Approved</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $approved_count; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-200">
        <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-full">
                <i class="fas fa-times-circle text-red-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Rejected</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $rejected_count; ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Applications Table -->
<?php 
$tableContent = '';
if (mysqli_num_rows($applications_result) > 0) {
    $tableContent .= '
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applicant</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Application Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Submitted</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">';
    
    while ($application = mysqli_fetch_assoc($applications_result)) {
        $statusClass = '';
        $statusIcon = '';
        switch($application['status']) {
            case 'pending':
                $statusClass = 'bg-yellow-100 text-yellow-800';
                $statusIcon = 'fas fa-clock';
                break;
            case 'approved':
                $statusClass = 'bg-green-100 text-green-800';
                $statusIcon = 'fas fa-check-circle';
                break;
            case 'rejected':
                $statusClass = 'bg-red-100 text-red-800';
                $statusIcon = 'fas fa-times-circle';
                break;
            default:
                $statusClass = 'bg-gray-100 text-gray-800';
                $statusIcon = 'fas fa-question-circle';
        }
        
        $tableContent .= '
                <tr class="hover:bg-gray-50 transition-colors duration-200">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        #' . htmlspecialchars($application['id']) . '
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-admin-primary text-white rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">' . 
                                    htmlspecialchars($application['user_name'] ?? $application['name'] ?? 'N/A') . 
                                '</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ' . htmlspecialchars($application['user_email'] ?? $application['email'] ?? 'N/A') . '
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ' . htmlspecialchars($application['request_text'] ?? $application['application_type'] ?? 'General Application') . '
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $statusClass . '">
                            <i class="' . $statusIcon . ' mr-1"></i>
                            ' . ucfirst(htmlspecialchars($application['status'])) . '
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ' . date('M j, Y', strtotime($application['created_at'])) . '
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">';
        
        if ($application['status'] == 'pending') {
            $tableContent .= '
                            <form method="POST" class="inline-block">
                                <input type="hidden" name="request_id" value="' . $application['id'] . '">
                                <button type="submit" name="action" value="approve" 
                                        class="bg-green-500 text-white px-3 py-1 rounded-md text-xs hover:bg-green-600 transition-colors duration-200 flex items-center">
                                    <i class="fas fa-check mr-1"></i>Approve
                                </button>
                            </form>
                            <form method="POST" class="inline-block">
                                <input type="hidden" name="request_id" value="' . $application['id'] . '">
                                <button type="submit" name="action" value="reject" 
                                        class="bg-red-500 text-white px-3 py-1 rounded-md text-xs hover:bg-red-600 transition-colors duration-200 flex items-center">
                                    <i class="fas fa-times mr-1"></i>Reject
                                </button>
                            </form>';
        }
        
        $tableContent .= '
                            <a href="view_user.php?user_id=' . ($application['user_id'] ?? $application['id']) . '" 
                               class="bg-blue-500 text-white px-3 py-1 rounded-md text-xs hover:bg-blue-600 transition-colors duration-200 flex items-center">
                                <i class="fas fa-eye mr-1"></i>View
                            </a>
                        </div>
                    </td>
                </tr>';
    }
    
    $tableContent .= '
            </tbody>
        </table>
    </div>';
} else {
    $tableContent = '
    <div class="text-center py-12">
        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-file-alt text-gray-400 text-3xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Applications Found</h3>
        <p class="text-gray-500 mb-6">There are currently no applications in the system.</p>
        <a href="add-application.php" 
           class="inline-flex items-center px-4 py-2 bg-admin-primary text-white rounded-lg hover:bg-admin-secondary transition-colors duration-200">
            <i class="fas fa-plus mr-2"></i>
            Add First Application
        </a>
    </div>';
}

echo renderAdminCard('All Applications', $tableContent, 'fas fa-file-alt', [
    ['label' => 'Export', 'url' => '#', 'icon' => 'fas fa-download'],
    ['label' => 'Filter', 'url' => '#', 'icon' => 'fas fa-filter']
]);
?>

<script>
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

// Add confirmation for approve/reject actions
document.querySelectorAll('form button[name="action"]').forEach(button => {
    button.addEventListener('click', function(e) {
        const action = this.value;
        const actionText = action === 'approve' ? 'approve' : 'reject';
        if (!confirm(`Are you sure you want to ${actionText} this application?`)) {
            e.preventDefault();
        }
    });
});
</script>

<?php echo renderAdminLayoutEnd(); ?>
