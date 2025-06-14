<?php
session_start();
require_once 'includes/supervisor_db.php';

checkSupervisorSession();

$supervisor_id = $_SESSION['supervisor_id'];
$message_id = (int)$_GET['id'];

// Get message details
$stmt = $con->prepare("
    SELECT sm.*, 
           CASE 
               WHEN sm.sender_type = 'student' THEN s.first_name
               WHEN sm.sender_type = 'supervisor' THEN sup.supervisor_name
               ELSE 'Admin'
           END as sender_name,
           CASE 
               WHEN sm.recipient_type = 'student' THEN rs.first_name  
               WHEN sm.recipient_type = 'supervisor' THEN rsup.supervisor_name
               ELSE 'Admin'
           END as recipient_name
    FROM supervisor_messages sm
    LEFT JOIN students s ON sm.sender_id = s.student_id AND sm.sender_type = 'student'
    LEFT JOIN supervisors sup ON sm.sender_id = sup.supervisor_id AND sm.sender_type = 'supervisor'
    LEFT JOIN students rs ON sm.recipient_id = rs.student_id AND sm.recipient_type = 'student'
    LEFT JOIN supervisors rsup ON sm.recipient_id = rsup.supervisor_id AND sm.recipient_type = 'supervisor'
    WHERE sm.message_id = ? AND 
          ((sm.sender_id = ? AND sm.sender_type = 'supervisor') OR 
           (sm.recipient_id = ? AND sm.recipient_type = 'supervisor'))
");
$stmt->bind_param("iii", $message_id, $supervisor_id, $supervisor_id);
$stmt->execute();
$message = $stmt->get_result()->fetch_assoc();

if (!$message) {
    echo '<div class="p-4 text-center"><p class="text-red-600">Message not found or access denied.</p></div>';
    exit;
}

// Mark as read if it's incoming
if ($message['recipient_id'] == $supervisor_id && $message['recipient_type'] == 'supervisor' && !$message['is_read']) {
    $stmt = $con->prepare("UPDATE supervisor_messages SET is_read = 1, read_at = CURRENT_TIMESTAMP WHERE message_id = ?");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
}
?>

<div class="p-6">
    <div class="flex justify-between items-start mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Message Details</h3>
        <button onclick="parent.closeMessageModal()" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="border-b border-gray-200 pb-4 mb-4">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center space-x-2">
                <?php if ($message['is_urgent']): ?>
                    <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">Urgent</span>
                <?php endif; ?>
                <span class="text-sm text-gray-600">
                    <?php echo date('M d, Y g:i A', strtotime($message['sent_at'])); ?>
                </span>
            </div>
        </div>
        
        <h4 class="text-xl font-semibold text-gray-900 mb-2">
            <?php echo htmlspecialchars($message['subject']); ?>
        </h4>
        
        <div class="text-sm text-gray-600">
            <p><strong>From:</strong> <?php echo htmlspecialchars($message['sender_name']); ?></p>
            <p><strong>To:</strong> <?php echo htmlspecialchars($message['recipient_name']); ?></p>
        </div>
    </div>
    
    <div class="mb-6">
        <div class="prose max-w-none">
            <?php echo nl2br(htmlspecialchars($message['message_content'])); ?>
        </div>
    </div>
    
    <?php if ($message['attachment_path']): ?>
        <div class="border-t border-gray-200 pt-4">
            <h5 class="text-sm font-medium text-gray-700 mb-2">Attachment:</h5>
            <a href="../<?php echo htmlspecialchars($message['attachment_path']); ?>" 
               target="_blank" 
               class="text-supervisor-primary hover:text-supervisor-secondary">
                <i class="fas fa-paperclip mr-1"></i>
                <?php echo basename($message['attachment_path']); ?>
            </a>
        </div>
    <?php endif; ?>
    
    <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
        <?php if ($message['sender_id'] != $supervisor_id || $message['sender_type'] != 'supervisor'): ?>
            <button onclick="replyToMessage(<?php echo $message['message_id']; ?>)" 
                    class="px-4 py-2 bg-supervisor-primary text-white rounded hover:bg-supervisor-secondary">
                <i class="fas fa-reply mr-2"></i>Reply
            </button>
        <?php endif; ?>
        <button onclick="parent.closeMessageModal()" 
                class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
            Close
        </button>
    </div>
</div>

<script>
function replyToMessage(messageId) {
    parent.closeMessageModal();
    parent.toggleMessageForm();
    // You could populate the form with reply data here
}
</script>
