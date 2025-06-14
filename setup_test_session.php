<?php
// Set up test student session
session_start();

// Create a test student session
$_SESSION['student_id'] = 1;
$_SESSION['student_name'] = 'Test Student';

echo "Test student session created successfully!<br>";
echo "Student ID: " . $_SESSION['student_id'] . "<br>";
echo "Student Name: " . $_SESSION['student_name'] . "<br>";
echo "<br>";
echo "<a href='student_reports.php'>Go to Student Reports</a><br>";
echo "<a href='manual_test.php'>Go to Manual Test Page</a><br>";
echo "<a href='student_dashboard.php'>Go to Student Dashboard</a><br>";
?>
