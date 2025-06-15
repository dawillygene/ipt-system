<?php
// Migration script to update applications table structure
require_once 'db.php';

echo "Starting applications table migration...\n";

try {
    // First, let's check current structure
    $result = $con->query("DESCRIBE applications");
    $current_columns = [];
    while ($row = $result->fetch_assoc()) {
        $current_columns[] = $row['Field'];
    }
    
    echo "Current columns: " . implode(', ', $current_columns) . "\n";
    
    // Add application_id as alias for id (for compatibility)
    if (!in_array('application_id', $current_columns)) {
        echo "Adding application_id column...\n";
        $con->query("ALTER TABLE applications ADD COLUMN application_id INT(11) AUTO_INCREMENT AFTER id, ADD UNIQUE KEY(application_id)");
        
        // Copy id values to application_id
        $con->query("UPDATE applications SET application_id = id");
    }
    
    // Add missing columns for the new application system
    $new_columns = [
        'company_name' => "VARCHAR(255)",
        'company_location' => "VARCHAR(255)", 
        'position_title' => "VARCHAR(255)",
        'training_duration' => "INT(11)",
        'start_date' => "DATE",
        'end_date' => "DATE", 
        'training_area' => "VARCHAR(255)",
        'skills_to_acquire' => "TEXT",
        'motivation_letter' => "TEXT",
        'preferred_company1' => "VARCHAR(255)",
        'preferred_company2' => "VARCHAR(255)", 
        'preferred_company3' => "VARCHAR(255)",
        'submitted_at' => "TIMESTAMP NULL",
        'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
    ];
    
    foreach ($new_columns as $column => $definition) {
        if (!in_array($column, $current_columns)) {
            echo "Adding column: $column\n";
            $con->query("ALTER TABLE applications ADD COLUMN $column $definition");
        }
    }
    
    // Update status enum to include new values
    echo "Updating status enum...\n";
    $con->query("ALTER TABLE applications MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected', 'in_review', 'pending') DEFAULT 'draft'");
    
    // Migrate existing data to new format
    echo "Migrating existing data...\n";
    $con->query("UPDATE applications SET 
        company_name = COALESCE(industrial, 'Unknown Company'),
        company_location = 'Unknown Location',
        position_title = 'Internship Position', 
        training_duration = 12,
        start_date = application_date,
        end_date = DATE_ADD(application_date, INTERVAL 12 WEEK),
        training_area = 'General',
        motivation_letter = 'Migrated from old system',
        status = CASE WHEN status = 'pending' THEN 'submitted' ELSE status END,
        submitted_at = created_at
        WHERE company_name IS NULL OR company_name = ''");
    
    echo "Migration completed successfully!\n";
    
    // Show final structure
    echo "\nFinal table structure:\n";
    $result = $con->query("DESCRIBE applications");
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
