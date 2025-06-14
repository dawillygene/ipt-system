<?php
session_start();
require_once 'includes/supervisor_db.php';

checkSupervisorSession();

$supervisor_id = $_SESSION['supervisor_id'];
// Use supervisor_name from session, consistent with other pages
$supervisor_name = $_SESSION['supervisor_name']; 
$supervisor = getSupervisorInfo($con, $supervisor_id); // Can be kept if other specific info is needed

$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['schedule_meeting'])) {
        $student_id = (int)$_POST['student_id'];
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $meeting_date = $_POST['meeting_date'];
        $meeting_time = $_POST['meeting_time'];
        $duration = (int)$_POST['duration'];
        $location = trim($_POST['location']);
        $meeting_type = $_POST['meeting_type'];
        $meeting_link = trim($_POST['meeting_link']);
        $agenda = trim($_POST['agenda']);
        
        // Validation
        if (empty($title) || empty($meeting_date) || empty($meeting_time)) {
            $error = 'Meeting title, date, and time are required.';
        } else {
            // Schedule meeting
            $stmt = $con->prepare("
                INSERT INTO supervisor_meetings 
                (supervisor_id, student_id, meeting_title, meeting_description, meeting_date, meeting_time,
                 duration_minutes, location, meeting_type, meeting_link, agenda)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iisssssisss", $supervisor_id, $student_id, $title, $description, $meeting_date, 
                            $meeting_time, $duration, $location, $meeting_type, $meeting_link, $agenda);
            
            if ($stmt->execute()) {
                $success = 'Meeting scheduled successfully!';
            } else {
                $error = 'Failed to schedule meeting. Please try again.';
            }
            $stmt->close(); // Close statement
        }
    } elseif (isset($_POST['update_meeting'])) {
        $meeting_id = (int)$_POST['meeting_id'];
        $status = $_POST['status'];
        $notes = trim($_POST['notes']);
        $action_items = trim($_POST['action_items']);
        
        $stmt = $con->prepare("
            UPDATE supervisor_meetings 
            SET status = ?, meeting_notes = ?, action_items = ?, updated_at = CURRENT_TIMESTAMP
            WHERE meeting_id = ? AND supervisor_id = ?
        ");
        $stmt->bind_param("sssii", $status, $notes, $action_items, $meeting_id, $supervisor_id);
        
        if ($stmt->execute()) {
            $success = 'Meeting updated successfully!';
        } else {
            $error = 'Failed to update meeting. Please try again.';
        }
        $stmt->close(); // Close statement
    }
}

// Get assigned students
$assigned_students = getAssignedStudents($con, $supervisor_id);

// Get meetings with filters
$filter_status = $_GET['status'] ?? '';
$filter_date = $_GET['date'] ?? '';

$meetings_query = "
    SELECT sm.*, s.full_name as student_name, u_supervisor.name as supervisor_name
    FROM supervisor_meetings sm
    JOIN students s ON sm.student_id = s.student_id
    JOIN users u_supervisor ON sm.supervisor_id = u_supervisor.id AND u_supervisor.role = 'Supervisor'
    WHERE sm.supervisor_id = ?
";

$params = [$supervisor_id];
$param_types = "i";

if ($filter_status) {
    $meetings_query .= " AND sm.status = ?";
    $params[] = $filter_status;
    $param_types .= "s";
}

if ($filter_date) {
    $meetings_query .= " AND DATE(sm.meeting_date) = ?";
    $params[] = $filter_date;
    $param_types .= "s";
}

$meetings_query .= " ORDER BY sm.meeting_date DESC, sm.meeting_time DESC";

$stmt = $con->prepare($meetings_query);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$meetings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close(); // Close statement
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meetings - IPT Supervisor Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#07442d', // Standardized
                        'secondary': '#206f56', // Standardized
                        'accent': '#0f7b5a', // Standardized
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
                    <a href="dashboard.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
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
                    <a href="meetings.php" class="flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-lg"> {/* Active link style */}
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
        <div class="flex-1 p-6"> {/* Standardized padding */}
            <div class="container mx-auto"> {/* Standardized container */}
                <!-- Page Header -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-primary">Meetings</h1>
                    <button onclick="toggleMeetingForm()" class="bg-primary hover:bg-secondary text-white px-6 py-2 rounded-lg transition">
                        <i class="fas fa-plus mr-2"></i>Schedule Meeting
                    </button>
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

                <!-- Filters -->
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Meetings</h3>
                    <form method="GET" class="flex flex-wrap gap-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="status" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary">
                                <option value="">All Status</option>
                                <option value="scheduled" <?php echo ($filter_status === 'scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                                <option value="completed" <?php echo ($filter_status === 'completed') ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo ($filter_status === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="rescheduled" <?php echo ($filter_status === 'rescheduled') ? 'selected' : ''; ?>>Rescheduled</option>
                            </select>
                        </div>
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <input type="date" name="date" id="date" value="<?php echo htmlspecialchars($filter_date); ?>"
                                   class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="px-4 py-2 bg-supervisor-primary text-white rounded-lg hover:bg-supervisor-secondary transition">
                                <i class="fas fa-filter mr-2"></i>Filter
                            </button>
                            <a href="meetings.php" class="ml-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Meeting Form -->
                <div id="meeting-form" class="bg-white shadow-lg rounded-lg mb-8" style="display: none;">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Schedule New Meeting</h2>
                    </div>
                    
                    <form method="POST" class="p-6">
                        <input type="hidden" name="schedule_meeting" value="1">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Select Student *</label>
                                <select name="student_id" id="student_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary">
                                    <option value="">Choose a student...</option>
                                    <?php foreach ($assigned_students as $student): ?>
                                        <option value="<?php echo $student['student_id']; ?>">
                                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Meeting Title *</label>
                                <input type="text" name="title" id="title" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary"
                                       placeholder="e.g., Progress Review Meeting">
                            </div>

                            <div>
                                <label for="meeting_date" class="block text-sm font-medium text-gray-700 mb-2">Meeting Date *</label>
                                <input type="date" name="meeting_date" id="meeting_date" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary"
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div>
                                <label for="meeting_time" class="block text-sm font-medium text-gray-700 mb-2">Meeting Time *</label>
                                <input type="time" name="meeting_time" id="meeting_time" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary">
                            </div>

                            <div>
                                <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                                <select name="duration" id="duration" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary">
                                    <option value="30">30 minutes</option>
                                    <option value="60" selected>1 hour</option>
                                    <option value="90">1.5 hours</option>
                                    <option value="120">2 hours</option>
                                </select>
                            </div>

                            <div>
                                <label for="meeting_type" class="block text-sm font-medium text-gray-700 mb-2">Meeting Type</label>
                                <select name="meeting_type" id="meeting_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary">
                                    <option value="physical">In-Person</option>
                                    <option value="virtual">Virtual/Online</option>
                                    <option value="phone">Phone Call</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" id="description" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary"
                                      placeholder="Brief description of the meeting purpose..."></textarea>
                        </div>

                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Location/Venue</label>
                                <input type="text" name="location" id="location"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary"
                                       placeholder="e.g., Office 123 or Meeting Room A">
                            </div>

                            <div>
                                <label for="meeting_link" class="block text-sm font-medium text-gray-700 mb-2">Meeting Link (for virtual meetings)</label>
                                <input type="url" name="meeting_link" id="meeting_link"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary"
                                       placeholder="https://zoom.us/j/...">
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="agenda" class="block text-sm font-medium text-gray-700 mb-2">Meeting Agenda</label>
                            <textarea name="agenda" id="agenda" rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary"
                                      placeholder="Meeting agenda and topics to discuss..."></textarea>
                        </div>

                        <div class="mt-8 flex justify-end space-x-4">
                            <button type="button" onclick="toggleMeetingForm()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-2 bg-supervisor-primary text-white rounded-lg hover:bg-supervisor-secondary transition">
                                <i class="fas fa-calendar-plus mr-2"></i>Schedule Meeting
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Meetings List -->
                <div class="bg-white shadow-lg rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Scheduled Meetings</h2>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($meetings)): ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                            <i class="fas fa-calendar-alt text-4xl mb-4"></i>
                                            <p>No meetings scheduled yet. Schedule your first meeting above.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($meetings as $meeting): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($meeting['first_name'] . ' ' . $meeting['last_name']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    ID: <?php echo htmlspecialchars($meeting['student_number']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($meeting['meeting_title']); ?>
                                                </div>
                                                <?php if ($meeting['meeting_description']): ?>
                                                    <div class="text-sm text-gray-500">
                                                        <?php echo htmlspecialchars(substr($meeting['meeting_description'], 0, 50) . '...'); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    <?php echo date('M d, Y', strtotime($meeting['meeting_date'])); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    <?php echo date('g:i A', strtotime($meeting['meeting_time'])); ?>
                                                    (<?php echo $meeting['duration_minutes']; ?>min)
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 capitalize">
                                                    <?php echo htmlspecialchars($meeting['meeting_type']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs rounded-full capitalize
                                                    <?php 
                                                    switch($meeting['status']) {
                                                        case 'scheduled': echo 'bg-blue-100 text-blue-800'; break;
                                                        case 'completed': echo 'bg-green-100 text-green-800'; break;
                                                        case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                                        case 'rescheduled': echo 'bg-yellow-100 text-yellow-800'; break;
                                                    }
                                                    ?>">
                                                    <?php echo htmlspecialchars($meeting['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button onclick="viewMeeting(<?php echo $meeting['meeting_id']; ?>)" 
                                                        class="text-supervisor-primary hover:text-supervisor-secondary mr-3">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($meeting['status'] == 'scheduled'): ?>
                                                    <button onclick="updateMeetingStatus(<?php echo $meeting['meeting_id']; ?>, 'completed')"
                                                            class="text-green-600 hover:text-green-900 mr-3" title="Mark as completed">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button onclick="updateMeetingStatus(<?php echo $meeting['meeting_id']; ?>, 'cancelled')"
                                                            class="text-red-600 hover:text-red-900" title="Cancel meeting">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Meeting Update Modal -->
    <div id="meeting-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Update Meeting</h3>
                <form id="update-meeting-form" method="POST">
                    <input type="hidden" name="update_meeting" value="1">
                    <input type="hidden" name="meeting_id" id="modal-meeting-id">
                    <input type="hidden" name="status" id="modal-status">
                    
                    <div class="mb-4">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Meeting Notes</label>
                        <textarea name="notes" id="notes" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary"
                                  placeholder="Add notes about the meeting..."></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label for="action_items" class="block text-sm font-medium text-gray-700 mb-2">Action Items</label>
                        <textarea name="action_items" id="action_items" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary"
                                  placeholder="List action items and next steps..."></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeMeetingModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-supervisor-primary text-white rounded hover:bg-supervisor-secondary">
                            Update Meeting
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleMeetingForm() {
            const form = document.getElementById('meeting-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function viewMeeting(meetingId) {
            // Create modal or redirect to view page
            window.open(`view_meeting.php?id=${meetingId}`, '_blank', 'width=800,height=600');
        }

        function updateMeetingStatus(meetingId, status) {
            document.getElementById('modal-meeting-id').value = meetingId;
            document.getElementById('modal-status').value = status;
            document.getElementById('meeting-modal').classList.remove('hidden');
        }

        function closeMeetingModal() {
            document.getElementById('meeting-modal').classList.add('hidden');
        }

        // Auto-hide/show location and meeting link based on meeting type
        document.getElementById('meeting_type').addEventListener('change', function() {
            const type = this.value;
            const locationField = document.getElementById('location');
            const linkField = document.getElementById('meeting_link');
            
            if (type === 'virtual') {
                linkField.parentNode.style.display = 'block';
                locationField.placeholder = 'Online Platform';
            } else if (type === 'physical') {
                linkField.parentNode.style.display = 'none';
                locationField.placeholder = 'e.g., Office 123 or Meeting Room A';
            } else if (type === 'phone') {
                linkField.parentNode.style.display = 'none';
                locationField.placeholder = 'Phone number or conference line';
            }
        });
    </script>
</body>
</html>
