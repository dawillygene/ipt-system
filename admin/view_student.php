<?php
session_start();
require_once 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo "Access denied";
    exit();
}

if (!isset($_GET['id'])) {
    echo "Student ID not provided";
    exit();
}

$student_id = (int)$_GET['id'];

// Get student details with user information
$stmt = $pdo->prepare("
    SELECT s.*, u.username, u.email as user_email, u.created_at as user_created_at
    FROM students s 
    LEFT JOIN users u ON s.user_id = u.id 
    WHERE s.student_id = ?
");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    echo "Student not found";
    exit();
}
?>

<div class="row">
    <div class="col-md-4 text-center">
        <?php if ($student['profile_photo']): ?>
            <img src="../<?php echo htmlspecialchars($student['profile_photo']); ?>" 
                 alt="Profile Photo" class="img-fluid rounded-circle mb-3" 
                 style="width: 150px; height: 150px; object-fit: cover;">
        <?php else: ?>
            <div class="bg-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" 
                 style="width: 150px; height: 150px;">
                <i class="fas fa-user fa-4x text-white"></i>
            </div>
        <?php endif; ?>
        <h5><?php echo htmlspecialchars($student['full_name']); ?></h5>
        <p class="text-muted"><?php echo htmlspecialchars($student['reg_number']); ?></p>
    </div>
    
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary">Personal Information</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Full Name:</strong></td>
                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Gender:</strong></td>
                        <td>
                            <span class="badge bg-<?php echo $student['gender'] == 'Male' ? 'primary' : ($student['gender'] == 'Female' ? 'info' : 'secondary'); ?>">
                                <?php echo htmlspecialchars($student['gender']); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Phone:</strong></td>
                        <td><?php echo htmlspecialchars($student['phone_number']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><?php echo htmlspecialchars($student['email'] ?: 'Not provided'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Address:</strong></td>
                        <td><?php echo nl2br(htmlspecialchars($student['address'])); ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="col-md-6">
                <h6 class="text-success">Academic Information</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Registration No:</strong></td>
                        <td><?php echo htmlspecialchars($student['reg_number']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>College:</strong></td>
                        <td><?php echo htmlspecialchars($student['college_name']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Department:</strong></td>
                        <td><?php echo htmlspecialchars($student['department']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Course:</strong></td>
                        <td><?php echo htmlspecialchars($student['course_name']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Program:</strong></td>
                        <td><?php echo htmlspecialchars($student['program']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Level:</strong></td>
                        <td><?php echo htmlspecialchars($student['level']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Year of Study:</strong></td>
                        <td>
                            <span class="badge bg-warning text-dark">
                                <?php echo htmlspecialchars($student['year_of_study']); ?><?php echo in_array($student['year_of_study'], ['1', '21', '31']) ? 'st' : (in_array($student['year_of_study'], ['2', '22', '32']) ? 'nd' : (in_array($student['year_of_study'], ['3', '23', '33']) ? 'rd' : 'th')); ?> Year
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <?php if ($student['username']): ?>
            <div class="mt-3">
                <h6 class="text-info">Account Information</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Username:</strong></td>
                        <td><?php echo htmlspecialchars($student['username']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Account Email:</strong></td>
                        <td><?php echo htmlspecialchars($student['user_email']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Account Created:</strong></td>
                        <td><?php echo date('M d, Y g:i A', strtotime($student['user_created_at'])); ?></td>
                    </tr>
                </table>
            </div>
        <?php endif; ?>
        
        <div class="mt-3">
            <h6 class="text-secondary">Record Information</h6>
            <table class="table table-sm">
                <tr>
                    <td><strong>Student ID:</strong></td>
                    <td><?php echo $student['student_id']; ?></td>
                </tr>
                <tr>
                    <td><strong>Record Created:</strong></td>
                    <td><?php echo date('M d, Y g:i A', strtotime($student['created_at'])); ?></td>
                </tr>
                <tr>
                    <td><strong>Last Updated:</strong></td>
                    <td><?php echo date('M d, Y g:i A', strtotime($student['updated_at'])); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>