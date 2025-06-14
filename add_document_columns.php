<?php
// Add document columns to students table
require_once 'db.php';

try {
    echo "Adding document columns to students table...\n";
    
    $columns_to_add = [
        'profile_photo' => 'VARCHAR(500) NULL COMMENT "Path to profile photo"',
        'academic_transcript' => 'VARCHAR(500) NULL COMMENT "Path to academic transcript document"',
        'id_document' => 'VARCHAR(500) NULL COMMENT "Path to national ID or passport document"',
        'cv_document' => 'VARCHAR(500) NULL COMMENT "Path to CV document"'
    ];
    
    foreach ($columns_to_add as $column => $definition) {
        // Check if column exists
        $check_query = "SHOW COLUMNS FROM students LIKE '$column'";
        $result = $con->query($check_query);
        
        if ($result && $result->num_rows == 0) {
            // Column doesn't exist, add it
            $add_query = "ALTER TABLE students ADD COLUMN $column $definition";
            if ($con->query($add_query)) {
                echo "✓ Added column: $column\n";
            } else {
                echo "✗ Failed to add column: $column - " . $con->error . "\n";
            }
        } else {
            echo "✓ Column already exists: $column\n";
        }
    }
    
    echo "\nDocument columns setup complete!\n";
    echo "\nColumns added:\n";
    echo "- profile_photo: For storing profile pictures\n";
    echo "- academic_transcript: For academic transcripts (PDF, DOC, DOCX)\n";
    echo "- id_document: For National ID/Passport (PDF, JPG, PNG)\n";
    echo "- cv_document: For CV documents (PDF, DOC, DOCX)\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
