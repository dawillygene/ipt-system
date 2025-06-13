<?php
session_start();
require_once 'db.php';

// Check if user is logged in as student
if (!isset($_SESSION['student_id'])) {
    header('Location: student_login.php');
    exit;
}

$student_id = $_SESSION['student_id'];

// Get all applications for this student
$stmt = $con->prepare("SELECT * FROM applications WHERE student_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - IPT System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#07442d',
                        'secondary': '#206f56',
                        'accent': '#0f7b5a',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-primary text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-lg sm:text-xl font-bold">
                            <i class="fas fa-graduation-cap mr-2"></i>IPT System
                        </h1>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="student_dashboard.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-secondary transition-colors">
                        <i class="fas fa-arrow-left mr-1"></i>Back to Dashboard
                    </a>
                    <a href="student_logout.php" class="px-3 py-2 rounded-md text-sm font-medium bg-secondary hover:bg-accent transition-colors">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 sm:px-0">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">My Applications</h1>
                    <p class="text-gray-600">Track your training application status</p>
                </div>
                <a href="student_applications.php" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-secondary transition-colors">
                    <i class="fas fa-plus mr-2"></i>New Application
                </a>
            </div>
        </div>

        <?php if (empty($applications)): ?>
            <!-- No Applications -->
            <div class="bg-white shadow-lg rounded-lg p-8 text-center">
                <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-file-alt text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Applications Yet</h3>
                <p class="text-gray-600 mb-6">You haven't submitted any training applications. Start by creating your first application.</p>
                <a href="student_applications.php" class="bg-primary text-white px-6 py-3 rounded-md hover:bg-secondary transition-colors">
                    <i class="fas fa-plus mr-2"></i>Create First Application
                </a>
            </div>
        <?php else: ?>
            <!-- Applications List -->
            <div class="space-y-6">
                <?php foreach ($applications as $app): ?>
                    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                        <div class="p-6">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <h3 class="text-lg font-medium text-gray-900 mr-3">
                                            <?php echo htmlspecialchars($app['company_name']); ?>
                                        </h3>
                                        <?php
                                        $status = $app['status'];
                                        $status_colors = [
                                            'draft' => 'bg-gray-100 text-gray-800',
                                            'submitted' => 'bg-blue-100 text-blue-800',
                                            'in_review' => 'bg-yellow-100 text-yellow-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800'
                                        ];
                                        $status_icons = [
                                            'draft' => 'fas fa-edit',
                                            'submitted' => 'fas fa-paper-plane',
                                            'in_review' => 'fas fa-clock',
                                            'approved' => 'fas fa-check-circle',
                                            'rejected' => 'fas fa-times-circle'
                                        ];
                                        ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $status_colors[$status]; ?>">
                                            <i class="<?php echo $status_icons[$status]; ?> mr-1"></i>
                                            <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm text-gray-600">
                                        <div>
                                            <i class="fas fa-map-marker-alt mr-1 text-primary"></i>
                                            <strong>Location:</strong> <?php echo htmlspecialchars($app['company_location']); ?>
                                        </div>
                                        <div>
                                            <i class="fas fa-briefcase mr-1 text-primary"></i>
                                            <strong>Position:</strong> <?php echo htmlspecialchars($app['position_title']); ?>
                                        </div>
                                        <div>
                                            <i class="fas fa-calendar-alt mr-1 text-primary"></i>
                                            <strong>Duration:</strong> <?php echo $app['training_duration']; ?> weeks
                                        </div>
                                        <div>
                                            <i class="fas fa-tools mr-1 text-primary"></i>
                                            <strong>Area:</strong> <?php echo htmlspecialchars($app['training_area']); ?>
                                        </div>
                                    </div>

                                    <?php if ($app['start_date'] && $app['end_date']): ?>
                                        <div class="mt-2 text-sm text-gray-600">
                                            <i class="fas fa-calendar mr-1 text-primary"></i>
                                            <strong>Period:</strong> 
                                            <?php echo date('M j, Y', strtotime($app['start_date'])); ?> - 
                                            <?php echo date('M j, Y', strtotime($app['end_date'])); ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mt-3 text-xs text-gray-500">
                                        <i class="fas fa-clock mr-1"></i>
                                        Created: <?php echo date('M j, Y \a\t g:i A', strtotime($app['created_at'])); ?>
                                        <?php if ($app['submitted_at']): ?>
                                            | Submitted: <?php echo date('M j, Y \a\t g:i A', strtotime($app['submitted_at'])); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mt-4 lg:mt-0 lg:ml-6 flex flex-col sm:flex-row lg:flex-col space-y-2 sm:space-y-0 sm:space-x-2 lg:space-x-0 lg:space-y-2">
                                    <button onclick="viewApplication(<?php echo $app['application_id']; ?>)" 
                                            class="px-4 py-2 bg-secondary text-white rounded-md hover:bg-accent transition-colors text-sm">
                                        <i class="fas fa-eye mr-1"></i>View Details
                                    </button>
                                    
                                    <?php if ($app['status'] === 'draft'): ?>
                                        <a href="student_applications.php" 
                                           class="px-4 py-2 bg-primary text-white rounded-md hover:bg-secondary transition-colors text-sm text-center">
                                            <i class="fas fa-edit mr-1"></i>Edit & Submit
                                        </a>
                                    <?php elseif ($app['status'] === 'submitted' || $app['status'] === 'in_review'): ?>
                                        <button class="px-4 py-2 bg-gray-300 text-gray-500 rounded-md cursor-not-allowed text-sm" disabled>
                                            <i class="fas fa-lock mr-1"></i>Under Review
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Application Progress -->
                        <div class="px-6 py-4 bg-gray-50 border-t">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Application Progress</span>
                                <span class="text-gray-600">
                                    <?php
                                    $progress = [
                                        'draft' => 25,
                                        'submitted' => 50,
                                        'in_review' => 75,
                                        'approved' => 100,
                                        'rejected' => 100
                                    ];
                                    echo $progress[$status] . '%';
                                    ?>
                                </span>
                            </div>
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-primary h-2 rounded-full transition-all duration-300" 
                                     style="width: <?php echo $progress[$status]; ?>%"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Application Details Modal -->
    <div id="applicationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Application Details</h3>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <div id="modalContent">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewApplication(applicationId) {
            // Find the application data
            const applications = <?php echo json_encode($applications); ?>;
            const app = applications.find(a => a.application_id == applicationId);
            
            if (!app) return;

            const modalContent = document.getElementById('modalContent');
            modalContent.innerHTML = `
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Company Information</h4>
                            <dl class="space-y-2 text-sm">
                                <div><dt class="inline font-medium">Company:</dt> <dd class="inline">${app.company_name}</dd></div>
                                <div><dt class="inline font-medium">Location:</dt> <dd class="inline">${app.company_location}</dd></div>
                                <div><dt class="inline font-medium">Position:</dt> <dd class="inline">${app.position_title}</dd></div>
                                <div><dt class="inline font-medium">Training Area:</dt> <dd class="inline">${app.training_area}</dd></div>
                            </dl>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Training Period</h4>
                            <dl class="space-y-2 text-sm">
                                <div><dt class="inline font-medium">Duration:</dt> <dd class="inline">${app.training_duration} weeks</dd></div>
                                <div><dt class="inline font-medium">Start Date:</dt> <dd class="inline">${new Date(app.start_date).toLocaleDateString()}</dd></div>
                                <div><dt class="inline font-medium">End Date:</dt> <dd class="inline">${new Date(app.end_date).toLocaleDateString()}</dd></div>
                            </dl>
                        </div>
                    </div>
                    
                    ${app.skills_to_acquire ? `
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Skills to Acquire</h4>
                        <p class="text-sm text-gray-700">${app.skills_to_acquire}</p>
                    </div>
                    ` : ''}
                    
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Motivation Letter</h4>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">${app.motivation_letter}</p>
                    </div>
                    
                    ${app.preferred_company1 || app.preferred_company2 || app.preferred_company3 ? `
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Alternative Company Preferences</h4>
                        <ol class="text-sm text-gray-700 space-y-1">
                            ${app.preferred_company1 ? `<li>2. ${app.preferred_company1}</li>` : ''}
                            ${app.preferred_company2 ? `<li>3. ${app.preferred_company2}</li>` : ''}
                            ${app.preferred_company3 ? `<li>4. ${app.preferred_company3}</li>` : ''}
                        </ol>
                    </div>
                    ` : ''}
                </div>
            `;
            
            document.getElementById('applicationModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('applicationModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('applicationModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
