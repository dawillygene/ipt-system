<?php
require_once 'db.php';

echo "Testing database connection and table structure...\n";

try {
    // Test connection
    if ($con->ping()) {
        echo "✓ Database connection successful\n";
    } else {
        echo "✗ Database connection failed\n";
        exit;
    }
    
    // Check applications table structure
    $result = $con->query("DESCRIBE applications");
    echo "✓ Applications table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    // Test insert operation
    $student_id = 1;
    $company_name = 'Test Company';
    $company_location = 'Test Location';
    $position_title = 'Test Position';
    $training_duration = 12;
    $start_date = '2025-07-01';
    $end_date = '2025-09-30';
    $training_area = 'Software Development';
    $skills_to_acquire = 'Test Skills';
    $motivation_letter = 'This is a test motivation letter that is longer than 100 characters to meet the requirements.';
    $preferred_company1 = '';
    $preferred_company2 = '';
    $preferred_company3 = '';
    $status = 'submitted';
    $submitted_at = date('Y-m-d H:i:s');
    
    // Check if application exists
    $check_stmt = $con->prepare("SELECT id FROM applications WHERE student_id = ?");
    $check_stmt->bind_param("i", $student_id);
    $check_stmt->execute();
    $existing = $check_stmt->get_result()->fetch_assoc();
    $check_stmt->close();
    
    if ($existing) {
        echo "✓ Found existing application for student $student_id\n";
        // Update existing
        $update_stmt = $con->prepare("UPDATE applications SET company_name = ?, company_location = ?, position_title = ?, training_duration = ?, start_date = ?, end_date = ?, training_area = ?, skills_to_acquire = ?, motivation_letter = ?, preferred_company1 = ?, preferred_company2 = ?, preferred_company3 = ?, status = ?, submitted_at = ? WHERE student_id = ?");
        $update_stmt->bind_param("sssississsssssi", $company_name, $company_location, $position_title, $training_duration, $start_date, $end_date, $training_area, $skills_to_acquire, $motivation_letter, $preferred_company1, $preferred_company2, $preferred_company3, $status, $submitted_at, $student_id);
        
        if ($update_stmt->execute()) {
            echo "✓ Application updated successfully\n";
        } else {
            echo "✗ Failed to update application: " . $update_stmt->error . "\n";
        }
        $update_stmt->close();
    } else {
        echo "✓ No existing application, creating new one\n";
        // Create new
        $insert_stmt = $con->prepare("INSERT INTO applications (student_id, company_name, company_location, position_title, training_duration, start_date, end_date, training_area, skills_to_acquire, motivation_letter, preferred_company1, preferred_company2, preferred_company3, status, submitted_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("isssisssssssss", $student_id, $company_name, $company_location, $position_title, $training_duration, $start_date, $end_date, $training_area, $skills_to_acquire, $motivation_letter, $preferred_company1, $preferred_company2, $preferred_company3, $status, $submitted_at);
        
        if ($insert_stmt->execute()) {
            echo "✓ New application created successfully\n";
        } else {
            echo "✗ Failed to create application: " . $insert_stmt->error . "\n";
        }
        $insert_stmt->close();
    }
    
    // Verify the data
    $verify_stmt = $con->prepare("SELECT * FROM applications WHERE student_id = ? ORDER BY id DESC LIMIT 1");
    $verify_stmt->bind_param("i", $student_id);
    $verify_stmt->execute();
    $result = $verify_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $app = $result->fetch_assoc();
        echo "✓ Application verified in database:\n";
        echo "  - ID: " . $app['id'] . "\n";
        echo "  - Company: " . $app['company_name'] . "\n";
        echo "  - Status: " . $app['status'] . "\n";
        echo "  - Submitted: " . ($app['submitted_at'] ? date('Y-m-d H:i:s', strtotime($app['submitted_at'])) : 'No') . "\n";
    } else {
        echo "✗ Application not found after insert/update\n";
    }
    
    echo "\n✓ Database operations test completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
