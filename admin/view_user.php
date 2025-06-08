<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

require_once 'db.php';
require_once 'includes/layout.php';

if (!isset($_GET['user_id'])) {
    echo renderAdminLayout('User Details', [
        ['name' => 'Dashboard', 'url' => 'admin_dashboard.php'],
        ['name' => 'Users', 'url' => 'admin_users.php'],
        ['name' => 'User Details']
    ]);
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">User ID is not provided.</div>';
    echo renderAdminLayoutEnd();
    exit;
}

$user_id = intval($_GET['user_id']);

// Fetch user details
$user_sql = "SELECT * FROM users WHERE id = ?";
$stmt = $con->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    echo renderAdminLayout('User Details', [
        ['name' => 'Dashboard', 'url' => 'admin_dashboard.php'],
        ['name' => 'Users', 'url' => 'admin_users.php'],
        ['name' => 'User Details']
    ]);
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">User not found.</div>';
    echo renderAdminLayoutEnd();
    exit;
}

// Fetch personal details
$personal_details_sql = "SELECT * FROM personal_details WHERE user_id = ?";
$stmt = $con->prepare($personal_details_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$personal_details_result = $stmt->get_result();
$personal_details = $personal_details_result->fetch_assoc();

// Fetch contact details
$contact_details_sql = "SELECT * FROM contact_details WHERE user_id = ?";
$stmt = $con->prepare($contact_details_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$contact_details_result = $stmt->get_result();
$contact_details = $contact_details_result->fetch_assoc();

// Fetch academic qualifications
$academic_qualification_sql = "SELECT * FROM academic_qualification WHERE user_id = ?";
$stmt = $con->prepare($academic_qualification_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$academic_qualification_result = $stmt->get_result();

// Fetch other attachments
$other_attachments_sql = "SELECT * FROM other_attachments WHERE user_id = ?";
$stmt = $con->prepare($other_attachments_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$other_attachments_result = $stmt->get_result();

$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => 'admin_dashboard.php'],
    ['name' => 'Users', 'url' => 'admin_users.php'],
    ['name' => 'User Details']
];
$pageTitle = 'User Details - ' . htmlspecialchars($user['name']);

echo renderAdminLayout($pageTitle, $breadcrumbs);
?>

<!-- User Header -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
    <div class="flex items-center mb-4 lg:mb-0">
        <div class="w-16 h-16 bg-admin-primary text-white rounded-full flex items-center justify-center mr-4">
            <i class="fas fa-user text-2xl"></i>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($user['name']); ?></h1>
            <p class="text-gray-600"><?php echo htmlspecialchars($user['email']); ?></p>
        </div>
    </div>
    <div class="flex space-x-3">
        <a href="admin_users.php" 
           class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Users
        </a>
        <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>" 
           class="bg-admin-primary text-white px-4 py-2 rounded-lg hover:bg-admin-secondary transition-colors duration-200 flex items-center">
            <i class="fas fa-envelope mr-2"></i>
            Contact User
        </a>
    </div>
</div>

<!-- User Information Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Basic User Info -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gradient-to-r from-admin-primary to-admin-secondary px-6 py-4">
            <h3 class="text-lg font-semibold text-white flex items-center">
                <i class="fas fa-user mr-3"></i>
                Basic Information
            </h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-medium">Name:</span>
                    <span class="text-gray-900"><?php echo htmlspecialchars($user['name']); ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-medium">Email:</span>
                    <span class="text-gray-900"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-medium">Registration Date:</span>
                    <span class="text-gray-900"><?php echo isset($user['created_at']) ? date('M j, Y', strtotime($user['created_at'])) : 'N/A'; ?></span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-gray-600 font-medium">Status:</span>
                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Active</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Personal Details -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
            <h3 class="text-lg font-semibold text-white flex items-center">
                <i class="fas fa-address-card mr-3"></i>
                Personal Details
            </h3>
        </div>
        <div class="p-6">
            <?php if ($personal_details): ?>
            <div class="space-y-4">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-medium">Address:</span>
                    <span class="text-gray-900 text-right"><?php echo htmlspecialchars($personal_details['address']); ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-medium">Phone:</span>
                    <span class="text-gray-900"><?php echo htmlspecialchars($personal_details['phone']); ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-medium">Date of Birth:</span>
                    <span class="text-gray-900"><?php echo htmlspecialchars($personal_details['place_of_birth']); ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-medium">Region:</span>
                    <span class="text-gray-900"><?php echo htmlspecialchars($personal_details['resident_region']); ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-medium">District:</span>
                    <span class="text-gray-900"><?php echo htmlspecialchars($personal_details['district']); ?></span>
                </div>
            </div>
            <?php else: ?>
            <div class="text-center py-8">
                <i class="fas fa-info-circle text-gray-400 text-3xl mb-3"></i>
                <p class="text-gray-500">No personal details available</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Contact Details -->
<div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
    <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
        <h3 class="text-lg font-semibold text-white flex items-center">
            <i class="fas fa-phone mr-3"></i>
            Contact & Banking Details
        </h3>
    </div>
    <div class="p-6">
        <?php if ($contact_details): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-medium">Bank Number:</span>
                    <span class="text-gray-900"><?php echo htmlspecialchars($contact_details['bank_no']); ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-medium">Bank Name:</span>
                    <span class="text-gray-900"><?php echo htmlspecialchars($contact_details['bank_name']); ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-medium">Zanzibar ID:</span>
                    <span class="text-gray-900"><?php echo htmlspecialchars($contact_details['zan_id']); ?></span>
                </div>
            </div>
            <div class="space-y-4">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-medium">ZSSF Number:</span>
                    <span class="text-gray-900"><?php echo htmlspecialchars($contact_details['zssf_no']); ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-medium">License Number:</span>
                    <span class="text-gray-900"><?php echo htmlspecialchars($contact_details['license_no']); ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-gray-600 font-medium">Volume Number:</span>
                    <span class="text-gray-900"><?php echo htmlspecialchars($contact_details['vol_no']); ?></span>
                </div>
            </div>
        </div>
        
        <?php if (!empty($contact_details['upload_zan_id_photo'])): ?>
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h4 class="text-lg font-medium text-gray-900 mb-3">Zanzibar ID Photo</h4>
            <div class="bg-gray-50 p-4 rounded-lg">
                <a href="../uploads/<?php echo htmlspecialchars($contact_details['upload_zan_id_photo']); ?>" 
                   target="_blank" 
                   class="text-admin-primary hover:text-admin-secondary transition-colors duration-200 flex items-center">
                    <i class="fas fa-file-image mr-2"></i>
                    <?php echo htmlspecialchars($contact_details['upload_zan_id_photo']); ?>
                </a>
            </div>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="text-center py-8">
            <i class="fas fa-info-circle text-gray-400 text-3xl mb-3"></i>
            <p class="text-gray-500">No contact details available</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Academic Qualifications -->
<div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
    <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
        <h3 class="text-lg font-semibold text-white flex items-center">
            <i class="fas fa-graduation-cap mr-3"></i>
            Academic Qualifications
        </h3>
    </div>
    <div class="p-6">
        <?php if ($academic_qualification_result->num_rows > 0): ?>
        <div class="space-y-4">
            <?php while ($qualification = $academic_qualification_result->fetch_assoc()): ?>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <span class="text-sm text-gray-600 font-medium">Qualification</span>
                        <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($qualification['qualification']); ?></p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600 font-medium">Institution</span>
                        <p class="text-gray-900"><?php echo htmlspecialchars($qualification['institution']); ?></p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600 font-medium">Year of Completion</span>
                        <p class="text-gray-900"><?php echo htmlspecialchars($qualification['year_of_completion']); ?></p>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-8">
            <i class="fas fa-graduation-cap text-gray-400 text-3xl mb-3"></i>
            <p class="text-gray-500">No academic qualifications available</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Other Attachments -->
<div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
    <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4">
        <h3 class="text-lg font-semibold text-white flex items-center">
            <i class="fas fa-paperclip mr-3"></i>
            Other Attachments
        </h3>
    </div>
    <div class="p-6">
        <?php if ($other_attachments_result->num_rows > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php while ($attachment = $other_attachments_result->fetch_assoc()): ?>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-medium text-gray-900 mb-3"><?php echo htmlspecialchars($attachment['attachment_name']); ?></h4>
                <?php
                $file_extension = pathinfo($attachment['attachment_url'], PATHINFO_EXTENSION);
                $file_url = '../'.htmlspecialchars($attachment['file_path']);
                ?>
                
                <?php if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                <div class="mb-3">
                    <img src="<?php echo $file_url; ?>" alt="Attachment Image" class="w-full h-48 object-cover rounded">
                </div>
                <?php elseif ($file_extension === 'pdf'): ?>
                <div class="mb-3">
                    <embed src="<?php echo $file_url; ?>" type="application/pdf" class="w-full h-64 rounded">
                </div>
                <?php endif; ?>
                
                <a href="<?php echo $file_url; ?>" 
                   target="_blank" 
                   class="inline-flex items-center text-admin-primary hover:text-admin-secondary transition-colors duration-200">
                    <i class="fas fa-external-link-alt mr-2"></i>
                    View Attachment
                </a>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-8">
            <i class="fas fa-paperclip text-gray-400 text-3xl mb-3"></i>
            <p class="text-gray-500">No attachments available</p>
        </div>
        <?php endif; ?>
    </div>
</div>

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
</script>

<?php echo renderAdminLayoutEnd(); ?>
