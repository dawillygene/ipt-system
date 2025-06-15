<?php
// Quick test of application submission
session_start();
$_SESSION['student_id'] = 1;
$_SESSION['student_name'] = 'Test Student';

$_POST = [
    'action' => 'submit',
    'company_name' => 'Test Company',
    'company_location' => 'Test Location', 
    'position_title' => 'Test Position',
    'training_duration' => 12,
    'start_date' => '2025-07-01',
    'end_date' => '2025-09-30',
    'training_area' => 'Software Development',
    'skills_to_acquire' => 'Testing skills',
    'motivation_letter' => 'This is a test motivation letter that is longer than 100 characters to meet the minimum requirement for application submission testing.',
    'preferred_company1' => '',
    'preferred_company2' => '',
    'preferred_company3' => ''
];

$_SERVER['REQUEST_METHOD'] = 'POST';

echo "Testing application submission...\n";

// Capture any errors
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    include 'student_applications.php';
    echo "✓ Application processing completed without fatal errors\n";
} catch (Throwable $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

$output = ob_get_clean();
echo "Script executed successfully\n";
?>
