<?php
session_start();
require_once 'includes/supervisor_db.php';

checkSupervisorSession();

$supervisor_id = $_SESSION['supervisor_id'];
$supervisor = getSupervisorInfo($con, $supervisor_id);

$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['send_message'])) {
        $recipient_id = (int)$_POST['recipient_id'];
        $recipient_type = $_POST['recipient_type'];
        $subject = trim($_POST['subject']);
        $message_content = trim($_POST['message_content']);
        $is_urgent = isset($_POST['is_urgent']) ? 1 : 0;
        
        // Handle file upload
        $attachment_path = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
            $upload_dir = '../uploads/messages/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = uniqid() . '_' . basename($_FILES['attachment']['name']);
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_path)) {
                $attachment_path = 'uploads/messages/' . $file_name;
            }
        }
        
        // Validation
        if (empty($subject) || empty($message_content)) {
            $error = 'Subject and message content are required.';
        } else {
            // Send message
            $stmt = $con->prepare("
                INSERT INTO supervisor_messages 
                (sender_id, sender_type, recipient_id, recipient_type, subject, message_content, is_urgent, attachment_path)
                VALUES (?, 'supervisor', ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iisssiss", $supervisor_id, $recipient_id, $recipient_type, $subject, $message_content, $is_urgent, $attachment_path);
            
            if ($stmt->execute()) {
                $success = 'Message sent successfully!';
            } else {
                $error = 'Failed to send message. Please try again.';
            }
        }
    } elseif (isset($_POST['mark_read'])) {
        $message_id = (int)$_POST['message_id'];
        $stmt = $con->prepare("UPDATE supervisor_messages SET is_read = 1, read_at = CURRENT_TIMESTAMP WHERE message_id = ? AND recipient_id = ?");
        $stmt->bind_param("ii", $message_id, $supervisor_id);
        $stmt->execute();
    }
}

// Get assigned students for message recipients
$assigned_students = getAssignedStudents($con, $supervisor_id);

// Get messages with filters
$filter_type = $_GET['type'] ?? 'all';
$filter_status = $_GET['status'] ?? 'all';

$messages_query = "
    SELECT sm.*, 
           CASE 
               WHEN sm.sender_type = 'student' THEN s_sender.full_name 
               WHEN sm.sender_type = 'supervisor' THEN sup_sender.name
               ELSE 'Admin'
           END as sender_name,
           CASE 
               WHEN sm.recipient_type = 'student' THEN s_recipient.full_name 
               WHEN sm.recipient_type = 'supervisor' THEN sup_recipient.name
               ELSE 'Admin'
           END as recipient_name
    FROM supervisor_messages sm
    LEFT JOIN users sup_sender ON sm.sender_id = sup_sender.id AND sm.sender_type = 'supervisor'
    LEFT JOIN students s_sender ON sm.sender_id = s_sender.user_id AND sm.sender_type = 'student' -- Assuming student_id in messages maps to user_id in students for sender
    LEFT JOIN users sup_recipient ON sm.recipient_id = sup_recipient.id AND sm.recipient_type = 'supervisor'
    LEFT JOIN students s_recipient ON sm.recipient_id = s_recipient.user_id AND sm.recipient_type = 'student' -- Assuming student_id in messages maps to user_id in students for recipient
    WHERE (sm.sender_id = ? AND sm.sender_type = 'supervisor') 
       OR (sm.recipient_id = ? AND sm.recipient_type = 'supervisor')
";

$params = [$supervisor_id, $supervisor_id];
$param_types = "ii";

if ($filter_type === 'sent') {
    $messages_query .= " AND sm.sender_id = ? AND sm.sender_type = 'supervisor'";
    $params[] = $supervisor_id;
    $param_types .= "i";
} elseif ($filter_type === 'received') {
    $messages_query .= " AND sm.recipient_id = ? AND sm.recipient_type = 'supervisor'";
    $params[] = $supervisor_id;
    $param_types .= "i";
}

if ($filter_status === 'unread') {
    $messages_query .= " AND sm.is_read = 0 AND sm.recipient_id = ?";
    $params[] = $supervisor_id;
    $param_types .= "i";
} elseif ($filter_status === 'urgent') {
    $messages_query .= " AND sm.is_urgent = 1";
}

$messages_query .= " ORDER BY sm.sent_at DESC";

$stmt = $con->prepare($messages_query);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get unread count
$stmt = $con->prepare("SELECT COUNT(*) as count FROM supervisor_messages WHERE recipient_id = ? AND recipient_type = 'supervisor' AND is_read = 0");
$stmt->bind_param("i", $supervisor_id);
$stmt->execute();
$unread_count = $stmt->get_result()->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Supervisor Dashboard</title>
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
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-supervisor-primary shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-user-tie text-white text-xl"></i>
                    <span class="text-white font-semibold text-xl">Supervisor Dashboard</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-white">Welcome, <?php echo htmlspecialchars($supervisor['supervisor_name']); ?></span>
                    <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg min-h-screen">
            <div class="p-4">
                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center space-x-3 text-gray-700 p-2 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="students.php" class="flex items-center space-x-3 text-gray-700 p-2 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-user-graduate"></i>
                        <span>My Students</span>
                    </a>
                    <a href="reports.php" class="flex items-center space-x-3 text-gray-700 p-2 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-file-alt"></i>
                        <span>Reports</span>
                    </a>
                    <a href="evaluations.php" class="flex items-center space-x-3 text-gray-700 p-2 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Evaluations</span>
                    </a>
                    <a href="meetings.php" class="flex items-center space-x-3 text-gray-700 p-2 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Meetings</span>
                    </a>
                    <a href="messages.php" class="flex items-center space-x-3 text-supervisor-primary p-2 rounded-lg bg-supervisor-primary bg-opacity-10">
                        <i class="fas fa-envelope"></i>
                        <span>Messages</span>
                        <?php if ($unread_count > 0): ?>
                            <span class="bg-red-500 text-white text-xs rounded-full px-2 py-1"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="profile.php" class="flex items-center space-x-3 text-gray-700 p-2 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-user"></i>
                        <span>My Profile</span>
                    </a>
                    <a href="settings.php" class="flex items-center space-x-3 text-gray-700 p-2 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="max-w-6xl mx-auto">
                <!-- Page Header -->
                <div class="mb-8 flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Messages</h1>
                        <p class="text-gray-600 mt-2">Communicate with your students and manage messages</p>
                    </div>
                    <button onclick="toggleMessageForm()" class="bg-supervisor-primary hover:bg-supervisor-secondary text-white px-6 py-2 rounded-lg transition">
                        <i class="fas fa-plus mr-2"></i>New Message
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
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Messages</h3>
                    <div class="flex flex-wrap gap-4">
                        <div class="flex space-x-2">
                            <a href="messages.php?type=all&status=<?php echo $filter_status; ?>" 
                               class="px-4 py-2 rounded-lg transition <?php echo ($filter_type === 'all') ? 'bg-supervisor-primary text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                                All Messages
                            </a>
                            <a href="messages.php?type=received&status=<?php echo $filter_status; ?>" 
                               class="px-4 py-2 rounded-lg transition <?php echo ($filter_type === 'received') ? 'bg-supervisor-primary text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                                Inbox
                            </a>
                            <a href="messages.php?type=sent&status=<?php echo $filter_status; ?>" 
                               class="px-4 py-2 rounded-lg transition <?php echo ($filter_type === 'sent') ? 'bg-supervisor-primary text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                                Sent
                            </a>
                        </div>
                        <div class="flex space-x-2">
                            <a href="messages.php?type=<?php echo $filter_type; ?>&status=all" 
                               class="px-4 py-2 rounded-lg transition <?php echo ($filter_status === 'all') ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                                All
                            </a>
                            <a href="messages.php?type=<?php echo $filter_type; ?>&status=unread" 
                               class="px-4 py-2 rounded-lg transition <?php echo ($filter_status === 'unread') ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                                Unread (<?php echo $unread_count; ?>)
                            </a>
                            <a href="messages.php?type=<?php echo $filter_type; ?>&status=urgent" 
                               class="px-4 py-2 rounded-lg transition <?php echo ($filter_status === 'urgent') ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                                Urgent
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Message Form -->
                <div id="message-form" class="bg-white shadow-lg rounded-lg mb-8" style="display: none;">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Compose New Message</h2>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data" class="p-6">
                        <input type="hidden" name="send_message" value="1">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="recipient_type" class="block text-sm font-medium text-gray-700 mb-2">Recipient Type</label>
                                <select name="recipient_type" id="recipient_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary">
                                    <option value="student">Student</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>

                            <div>
                                <label for="recipient_id" class="block text-sm font-medium text-gray-700 mb-2">Select Recipient *</label>
                                <select name="recipient_id" id="recipient_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary">
                                    <option value="">Choose recipient...</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                            <input type="text" name="subject" id="subject" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary"
                                   placeholder="Enter message subject">
                        </div>

                        <div class="mt-6">
                            <label for="message_content" class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                            <textarea name="message_content" id="message_content" rows="6" required
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary"
                                      placeholder="Type your message here..."></textarea>
                        </div>

                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="attachment" class="block text-sm font-medium text-gray-700 mb-2">Attachment (optional)</label>
                                <input type="file" name="attachment" id="attachment"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary">
                                <p class="text-sm text-gray-500 mt-1">Max file size: 10MB</p>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="is_urgent" id="is_urgent" class="mr-2">
                                <label for="is_urgent" class="text-sm font-medium text-gray-700">Mark as urgent</label>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end space-x-4">
                            <button type="button" onclick="toggleMessageForm()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-2 bg-supervisor-primary text-white rounded-lg hover:bg-supervisor-secondary transition">
                                <i class="fas fa-paper-plane mr-2"></i>Send Message
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Messages List -->
                <div class="bg-white shadow-lg rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">
                            <?php 
                            switch($filter_type) {
                                case 'sent': echo 'Sent Messages'; break;
                                case 'received': echo 'Inbox'; break;
                                default: echo 'All Messages'; break;
                            }
                            ?>
                        </h2>
                    </div>
                    
                    <div class="divide-y divide-gray-200">
                        <?php if (empty($messages)): ?>
                            <div class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-envelope text-4xl mb-4"></i>
                                <p>No messages found. Send your first message above.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $message): ?>
                                <div class="px-6 py-4 hover:bg-gray-50 cursor-pointer <?php echo (!$message['is_read'] && $message['recipient_id'] == $supervisor_id && $message['recipient_type'] == 'supervisor') ? 'bg-blue-50' : ''; ?>"
                                     onclick="viewMessage(<?php echo $message['message_id']; ?>)">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-shrink-0">
                                                <?php if ($message['is_urgent']): ?>
                                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                                <?php endif; ?>
                                                <?php if ($message['attachment_path']): ?>
                                                    <i class="fas fa-paperclip text-gray-400 ml-1"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center space-x-2">
                                                    <p class="text-sm font-medium text-gray-900">
                                                        <?php 
                                                        if ($message['sender_id'] == $supervisor_id && $message['sender_type'] == 'supervisor') {
                                                            echo 'To: ' . htmlspecialchars($message['recipient_name']);
                                                        } else {
                                                            echo 'From: ' . htmlspecialchars($message['sender_name']);
                                                        }
                                                        ?>
                                                    </p>
                                                    <?php if (!$message['is_read'] && $message['recipient_id'] == $supervisor_id && $message['recipient_type'] == 'supervisor'): ?>
                                                        <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded">New</span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="text-sm text-gray-900 font-medium">
                                                    <?php echo htmlspecialchars($message['subject']); ?>
                                                </p>
                                                <p class="text-sm text-gray-500 truncate">
                                                    <?php echo htmlspecialchars(substr($message['message_content'], 0, 100)) . (strlen($message['message_content']) > 100 ? '...' : ''); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo date('M d, g:i A', strtotime($message['sent_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message View Modal -->
    <div id="message-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-10 mx-auto p-5 border w-4/5 max-w-3xl shadow-lg rounded-md bg-white">
            <div id="message-content">
                <!-- Message content will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        function toggleMessageForm() {
            const form = document.getElementById('message-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        // Update recipient list based on type
        document.getElementById('recipient_type').addEventListener('change', function() {
            const type = this.value;
            const recipientSelect = document.getElementById('recipient_id');
            
            recipientSelect.innerHTML = '<option value="">Choose recipient...</option>';
            
            if (type === 'student') {
                <?php foreach ($assigned_students as $student): ?>
                    recipientSelect.innerHTML += '<option value="<?php echo $student['student_id']; ?>"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></option>';
                <?php endforeach; ?>
            } else if (type === 'admin') {
                recipientSelect.innerHTML += '<option value="1">System Administrator</option>';
            }
        });

        // Trigger change event on page load to populate students by default
        document.getElementById('recipient_type').dispatchEvent(new Event('change'));

        function viewMessage(messageId) {
            // Load message content via AJAX or redirect
            fetch(`view_message.php?id=${messageId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('message-content').innerHTML = html;
                    document.getElementById('message-modal').classList.remove('hidden');
                    
                    // Mark as read if it's an incoming message
                    markAsRead(messageId);
                });
        }

        function markAsRead(messageId) {
            const formData = new FormData();
            formData.append('mark_read', '1');
            formData.append('message_id', messageId);
            
            fetch('messages.php', {
                method: 'POST',
                body: formData
            });
        }

        function closeMessageModal() {
            document.getElementById('message-modal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('message-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeMessageModal();
            }
        });
    </script>
</body>
</html>
