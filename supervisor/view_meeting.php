<?php
session_start();
require_once 'includes/supervisor_db.php';

checkSupervisorSession();

$supervisor_id = $_SESSION['supervisor_id'];
$meeting_id = (int)$_GET['id'];

// Get meeting details
$stmt = $con->prepare("
    SELECT sm.*, s.first_name, s.last_name, s.student_id as student_number
    FROM supervisor_meetings sm
    JOIN students s ON sm.student_id = s.student_id
    WHERE sm.meeting_id = ? AND sm.supervisor_id = ?
");
$stmt->bind_param("ii", $meeting_id, $supervisor_id);
$stmt->execute();
$meeting = $stmt->get_result()->fetch_assoc();

if (!$meeting) {
    echo '<div class="p-4 text-center"><p class="text-red-600">Meeting not found or access denied.</p></div>';
    exit;
}

function getStatusColor($status) {
    switch($status) {
        case 'scheduled': return 'bg-blue-100 text-blue-800';
        case 'completed': return 'bg-green-100 text-green-800';
        case 'cancelled': return 'bg-red-100 text-red-800';
        case 'rescheduled': return 'bg-yellow-100 text-yellow-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Details</title>
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
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($meeting['meeting_title']); ?></h1>
                <p class="text-gray-600">
                    Meeting with <?php echo htmlspecialchars($meeting['first_name'] . ' ' . $meeting['last_name']); ?> 
                    (ID: <?php echo htmlspecialchars($meeting['student_number']); ?>)
                </p>
            </div>
            <button onclick="window.close()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="p-6">
            <!-- Meeting Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-700">Date</h3>
                    <p class="text-lg font-semibold text-gray-900">
                        <?php echo date('M d, Y', strtotime($meeting['meeting_date'])); ?>
                    </p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-700">Time</h3>
                    <p class="text-lg font-semibold text-gray-900">
                        <?php echo date('g:i A', strtotime($meeting['meeting_time'])); ?>
                    </p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-700">Duration</h3>
                    <p class="text-lg font-semibold text-gray-900">
                        <?php echo $meeting['duration_minutes']; ?> minutes
                    </p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-700">Type</h3>
                    <p class="text-lg font-semibold text-gray-900 capitalize">
                        <?php echo htmlspecialchars($meeting['meeting_type']); ?>
                    </p>
                </div>
            </div>

            <!-- Status -->
            <div class="mb-8 flex items-center justify-center">
                <span class="px-6 py-3 text-lg font-semibold rounded-full capitalize <?php echo getStatusColor($meeting['status']); ?>">
                    <i class="fas fa-circle mr-2"></i>
                    <?php echo htmlspecialchars($meeting['status']); ?>
                </span>
            </div>

            <!-- Description and Location -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <?php if ($meeting['meeting_description']): ?>
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-3">
                            <i class="fas fa-info-circle mr-2 text-blue-600"></i>Description
                        </h4>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($meeting['meeting_description'])); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-3">
                        <i class="fas fa-map-marker-alt mr-2 text-red-600"></i>Location & Details
                    </h4>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-2">
                        <?php if ($meeting['location']): ?>
                            <p class="text-gray-700">
                                <strong>Location:</strong> <?php echo htmlspecialchars($meeting['location']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($meeting['meeting_link']): ?>
                            <p class="text-gray-700">
                                <strong>Meeting Link:</strong> 
                                <a href="<?php echo htmlspecialchars($meeting['meeting_link']); ?>" 
                                   target="_blank" 
                                   class="text-supervisor-primary hover:text-supervisor-secondary">
                                    <?php echo htmlspecialchars($meeting['meeting_link']); ?>
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Agenda -->
            <?php if ($meeting['agenda']): ?>
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-gray-900 mb-3">
                        <i class="fas fa-list mr-2 text-green-600"></i>Meeting Agenda
                    </h4>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="prose max-w-none">
                            <?php echo nl2br(htmlspecialchars($meeting['agenda'])); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Meeting Notes (if completed) -->
            <?php if ($meeting['meeting_notes'] && $meeting['status'] == 'completed'): ?>
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-gray-900 mb-3">
                        <i class="fas fa-sticky-note mr-2 text-yellow-600"></i>Meeting Notes
                    </h4>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="prose max-w-none">
                            <?php echo nl2br(htmlspecialchars($meeting['meeting_notes'])); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Items (if completed) -->
            <?php if ($meeting['action_items'] && $meeting['status'] == 'completed'): ?>
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-gray-900 mb-3">
                        <i class="fas fa-tasks mr-2 text-purple-600"></i>Action Items
                    </h4>
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <div class="prose max-w-none">
                            <?php echo nl2br(htmlspecialchars($meeting['action_items'])); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Timestamps -->
            <div class="border-t border-gray-200 pt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                    <div>
                        <strong>Created:</strong> <?php echo date('M d, Y g:i A', strtotime($meeting['created_at'])); ?>
                    </div>
                    <?php if ($meeting['updated_at'] != $meeting['created_at']): ?>
                        <div>
                            <strong>Last Updated:</strong> <?php echo date('M d, Y g:i A', strtotime($meeting['updated_at'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions -->
            <div class="border-t border-gray-200 pt-6 mt-6 flex justify-end space-x-3">
                <button onclick="window.print()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                    <i class="fas fa-print mr-2"></i>Print
                </button>
                <?php if ($meeting['meeting_link']): ?>
                    <a href="<?php echo htmlspecialchars($meeting['meeting_link']); ?>" 
                       target="_blank"
                       class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        <i class="fas fa-external-link-alt mr-2"></i>Join Meeting
                    </a>
                <?php endif; ?>
                <button onclick="window.close()" class="px-4 py-2 bg-supervisor-primary text-white rounded hover:bg-supervisor-secondary">
                    Close
                </button>
            </div>
        </div>
    </div>

    <style>
        @media print {
            button, a[target="_blank"] {
                display: none !important;
            }
            body {
                background: white !important;
                padding: 0 !important;
            }
        }
    </style>
</body>
</html>
