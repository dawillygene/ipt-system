<?php
session_start();
$_SESSION['student_id'] = 1;
$_SESSION['student_name'] = 'Test Student';
header('Location: student_reports.php');
?>
