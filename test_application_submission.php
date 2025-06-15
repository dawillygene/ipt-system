<?php
// Test application submission
session_start();
require_once 'db.php';

// Set a test student session
$_SESSION['student_id'] = 1;
$_SESSION['student_name'] = 'Lulu Ibrahim';

// Simulate form submission
$_POST = [
    'action' => 'submit',
    'company_name' => 'Test Company Ltd',
    'company_location' => 'Dodoma, Tanzania',
    'position_title' => 'Software Developer Intern',
    'training_duration' => 12,
    'start_date' => '2025-07-01',
    'end_date' => '2025-09-30',
    'training_area' => 'Software Development',
    'skills_to_acquire' => 'PHP, JavaScript, Database Management',
    'motivation_letter' => 'This is a test motivation letter that is longer than 100 characters to meet the minimum requirement for application submission. I am very interested in this internship opportunity.'
];

// Set REQUEST_METHOD to POST
$_SERVER['REQUEST_METHOD'] = 'POST';

echo "Testing application submission...\n";

// Include the application file to process the submission
try {
    ob_start();
    include 'student_applications.php';
    $output = ob_get_clean();
    
    echo "Application processed successfully!\n";
    
    // Check if application was saved
    $stmt = $con->prepare("SELECT * FROM applications WHERE student_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $_SESSION['student_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $application = $result->fetch_assoc();
        echo "Application found in database:\n";
        echo "- Company: " . $application['company_name'] . "\n";
        echo "- Status: " . $application['status'] . "\n";
        echo "- Submitted: " . ($application['submitted_at'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "No application found in database.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
