<?php
// Quick script to add profile_photo column to students table
require_once 'db.php';

try {
    // Check if column exists
    $result = $con->query("SHOW COLUMNS FROM students LIKE 'profile_photo'");
    
    if ($result->num_rows == 0) {
        // Add the column
        $con->query("ALTER TABLE students ADD COLUMN profile_photo VARCHAR(500) NULL");
        echo "Profile photo column added successfully!";
    } else {
        echo "Profile photo column already exists.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
