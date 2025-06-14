<?php
session_start();
require_once 'includes/supervisor_db.php';

// Check if user is logged in as supervisor
if (isset($_SESSION['supervisor_id'])) {
    // Clear all session data
    session_unset();
    session_destroy();
}

// Redirect to login page
header('Location: login.php');
exit;
?>
