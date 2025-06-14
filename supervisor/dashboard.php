<?php
session_start();
require_once 'includes/supervisor_db.php';

// Check if user is logged in as supervisor
checkSupervisorSession();

$supervisor_id = $_SESSION['supervisor_id'];
$supervisor_name = $_SESSION['supervisor_name'];

// Get supervisor information
$supervisor = getSupervisorInfo($con, $supervisor_id);

// Get assigned students
$assigned_students = getAssignedStudents($con, $supervisor_id);

// Get dashboard statistics
$stats_query = "
    SELECT 
        (SELECT COUNT(*) FROM supervisor_assignments WHERE supervisor_id = ? AND status = 'active') as total_students,
        (SELECT COUNT(*) FROM student_reports sr 
         JOIN supervisor_assignments sa ON sr.student_id = sa.student_id 
         WHERE sa.supervisor_id = ? AND sr.status = 'submitted' 
         AND NOT EXISTS (SELECT 1 FROM report_reviews rr WHERE rr.report_id = sr.report_id AND rr.supervisor_id = ?)) as pending_reports,
        (SELECT COUNT(*) FROM report_reviews WHERE supervisor_id = ? AND review_status = 'reviewed') as reviewed_reports,
        (SELECT COUNT(*) FROM supervisor_meetings WHERE supervisor_id = ? AND meeting_date >= CURDATE() AND status = 'scheduled') as upcoming_meetings,
        (SELECT COUNT(*) FROM student_evaluations WHERE supervisor_id = ? AND status = 'draft') as pending_evaluations
";

$stats_stmt = $con->prepare($stats_query);
$stats_stmt->bind_param("iiiiii", $supervisor_id, $supervisor_id, $supervisor_id, $supervisor_id, $supervisor_id, $supervisor_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
$stats_stmt->close();

// Get recent reports
$recent_reports_stmt = $con->prepare("
    SELECT sr.*, s.full_name as student_name, rr.review_status 
    FROM student_reports sr 
    JOIN students s ON sr.student_id = s.student_id 
    JOIN supervisor_assignments sa ON s.student_id = sa.student_id 
    LEFT JOIN report_reviews rr ON sr.report_id = rr.report_id AND rr.supervisor_id = ?
    WHERE sa.supervisor_id = ? AND sa.status = 'active' 
    ORDER BY sr.created_at DESC 
    LIMIT 5
");
$recent_reports_stmt->bind_param("ii", $supervisor_id, $supervisor_id);
$recent_reports_stmt->execute();
$recent_reports = $recent_reports_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$recent_reports_stmt->close();

// Get upcoming meetings
$upcoming_meetings_stmt = $con->prepare("
    SELECT sm.*, s.full_name as student_name 
    FROM supervisor_meetings sm 
    JOIN students s ON sm.student_id = s.student_id 
    WHERE sm.supervisor_id = ? AND sm.meeting_date >= CURDATE() AND sm.status = 'scheduled'
    ORDER BY sm.meeting_date ASC, sm.meeting_time ASC 
    LIMIT 5
");
$upcoming_meetings_stmt->bind_param("i", $supervisor_id);
$upcoming_meetings_stmt->execute();
$upcoming_meetings = $upcoming_meetings_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$upcoming_meetings_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Dashboard - IPT System</title>
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
                    <h1 class="text-xl font-bold">
                        <i class="fas fa-chalkboard-teacher mr-2"></i>IPT Supervisor Portal
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm">Welcome, <?php echo htmlspecialchars($supervisor_name); ?></span>
                    <div class="relative">
                        <button id="menu-button" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-primary focus:ring-white">
                            <i class="fas fa-user-circle text-2xl"></i>
                        </button>
                        <div id="dropdown-menu" class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>Profile
                                </a>
                                <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-2"></i>Settings
                                </a>
                                <hr class="my-1">
                                <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Layout -->
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg min-h-screen">
            <div class="p-4">
                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-lg">
                        <i class="fas fa-tachometer-alt mr-3"></i>
                        Dashboard
                    </a>
                    <a href="students.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-users mr-3"></i>
                        My Students
                    </a>
                    <a href="reports.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-file-alt mr-3"></i>
                        Review Reports
                    </a>
                    <a href="evaluations.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-star mr-3"></i>
                        Evaluations
                    </a>
                    <a href="meetings.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-calendar mr-3"></i>
                        Meetings
                    </a>
                    <a href="messages.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-comments mr-3"></i>
                        Messages
                    </a>
                    <hr class="my-2">
                    <a href="profile.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-user-cog mr-3"></i>
                        My Profile
                    </a>
                    <a href="settings.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-cogs mr-3"></i>
                        Settings
                    </a>
                    <hr class="my-2">
                    <a href="logout.php" class="flex items-center px-4 py-2 text-red-600 hover:bg-red-100 rounded-lg">
                        <i class="fas fa-sign-out-alt mr-3"></i>
                        Logout
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            <div class="container mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-primary">Supervisor Dashboard</h1>
                </div>

                <!-- Dashboard Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-users text-blue-500 text-2xl"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Assigned Students</dt>
                                        <dd class="text-lg font-medium text-gray-900"><?php echo $stats['total_students']; ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-clock text-orange-500 text-2xl"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Pending Reports</dt>
                                        <dd class="text-lg font-medium text-gray-900"><?php echo $stats['pending_reports']; ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Reviewed Reports</dt>
                                        <dd class="text-lg font-medium text-gray-900"><?php echo $stats['reviewed_reports']; ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-calendar-check text-purple-500 text-2xl"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Upcoming Meetings</dt>
                                        <dd class="text-lg font-medium text-gray-900"><?php echo $stats['upcoming_meetings']; ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-star text-yellow-500 text-2xl"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Pending Evaluations</dt>
                                        <dd class="text-lg font-medium text-gray-900"><?php echo $stats['pending_evaluations']; ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Reports -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Recent Student Reports</h3>
                        </div>
                        <div class="p-6">
                            <?php if (empty($recent_reports)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-file-alt text-gray-300 text-3xl mb-2"></i>
                                    <p class="text-gray-500">No recent reports</p>
                            </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($recent_reports as $report): ?>
                                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                            <div class="flex-1">
                                                <h4 class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($report['report_title']); ?></h4>
                                                <p class="text-sm text-gray-600">by <?php echo htmlspecialchars($report['student_name']); ?></p>
                                                <p class="text-xs text-gray-500"><?php echo date('M d, Y', strtotime($report['report_date'])); ?></p>
                                            </div>
                                            <div class="ml-4">
                                                <?php if ($report['review_status']): ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Reviewed
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                        Pending
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-4">
                                    <a href="reports.php" class="text-primary hover:text-secondary text-sm font-medium">
                                        View all reports <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Assigned Students -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Assigned Students</h3>
                        </div>
                        <div class="p-6">
                            <?php if (empty($assigned_students)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-users text-gray-300 text-3xl mb-2"></i>
                                    <p class="text-gray-500">No students assigned</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach (array_slice($assigned_students, 0, 5) as $student): ?>
                                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center">
                                                        <span class="text-white text-sm font-medium">
                                                            <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <h4 class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($student['full_name']); ?></h4>
                                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($student['reg_number']); ?></p>
                                                    <p class="text-xs text-gray-500"><?php echo ucfirst($student['assignment_type']); ?> Supervision</p>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <a href="students.php?view=<?php echo $student['student_id']; ?>" class="text-primary hover:text-secondary text-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-4">
                                    <a href="students.php" class="text-primary hover:text-secondary text-sm font-medium">
                                        View all students <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Upcoming Meetings -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Upcoming Meetings</h3>
                        </div>
                        <div class="p-6">
                            <?php if (empty($upcoming_meetings)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-calendar text-gray-300 text-3xl mb-2"></i>
                                    <p class="text-gray-500">No upcoming meetings</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($upcoming_meetings as $meeting): ?>
                                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                            <div class="flex-1">
                                                <h4 class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($meeting['meeting_title']); ?></h4>
                                                <p class="text-sm text-gray-600">with <?php echo htmlspecialchars($meeting['student_name']); ?></p>
                                                <p class="text-xs text-gray-500">
                                                    <?php echo date('M d, Y', strtotime($meeting['meeting_date'])); ?> at 
                                                    <?php echo date('H:i', strtotime($meeting['meeting_time'])); ?>
                                                </p>
                                            </div>
                                            <div class="ml-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <?php echo ucfirst($meeting['meeting_type']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-4">
                                    <a href="meetings.php" class="text-primary hover:text-secondary text-sm font-medium">
                                        View all meetings <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-4">
                                <a href="reports.php" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-file-alt text-primary text-2xl mb-2"></i>
                                    <span class="text-sm font-medium text-gray-900">Review Reports</span>
                                </a>
                                <a href="evaluations.php?action=create" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-star text-primary text-2xl mb-2"></i>
                                    <span class="text-sm font-medium text-gray-900">Create Evaluation</span>
                                </a>
                                <a href="meetings.php?action=create" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-calendar-plus text-primary text-2xl mb-2"></i>
                                    <span class="text-sm font-medium text-gray-900">Schedule Meeting</span>
                                </a>
                                <a href="messages.php?action=compose" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-envelope-plus text-primary text-2xl mb-2"></i>
                                    <span class="text-sm font-medium text-gray-900">Send Message</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Dropdown menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const menuButton = document.getElementById('menu-button');
            const dropdownMenu = document.getElementById('dropdown-menu');
            
            menuButton.addEventListener('click', function() {
                dropdownMenu.classList.toggle('hidden');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!menuButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                    dropdownMenu.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>
