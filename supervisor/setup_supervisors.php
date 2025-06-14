<?php
// Supervisor Setup Script - Create test supervisor accounts
require_once 'includes/supervisor_db.php';

echo "=== IPT Supervisor Setup Script ===\n\n";

// Test supervisor accounts to create
$test_supervisors = [
    [
        'name' => 'Dr. John Smith',
        'email' => 'john.smith@university.edu',
        'password' => 'supervisor123',
        'phone' => '+1-555-0101',
        'department' => 'Computer Science',
        'institution' => 'Tech University',
        'specialization' => 'Software Engineering, Database Systems',
        'years_experience' => 15
    ],
    [
        'name' => 'Prof. Sarah Johnson',
        'email' => 'sarah.johnson@university.edu',
        'password' => 'supervisor456',
        'phone' => '+1-555-0102',
        'department' => 'Information Technology',
        'institution' => 'Tech University',
        'specialization' => 'Network Security, Web Development',
        'years_experience' => 12
    ],
    [
        'name' => 'Dr. Michael Chen',
        'email' => 'michael.chen@university.edu',
        'password' => 'supervisor789',
        'phone' => '+1-555-0103',
        'department' => 'Software Engineering',
        'institution' => 'Tech University',
        'specialization' => 'Mobile Development, AI/ML',
        'years_experience' => 8
    ]
];

echo "Creating test supervisor accounts...\n\n";

foreach ($test_supervisors as $index => $supervisor) {
    // Check if supervisor already exists
    $stmt = $con->prepare("SELECT supervisor_id FROM supervisors WHERE email = ?");
    $stmt->bind_param("s", $supervisor['email']);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        echo "✓ Supervisor already exists: " . $supervisor['email'] . "\n";
        continue;
    }
    
    // Hash the password
    $hashed_password = password_hash($supervisor['password'], PASSWORD_DEFAULT);
    
    // Insert supervisor
    $stmt = $con->prepare("
        INSERT INTO supervisors 
        (supervisor_name, email, password, phone_number, department, institution, specialization, years_experience, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ");
    
    $stmt->bind_param("sssssssi", 
        $supervisor['name'], 
        $supervisor['email'], 
        $hashed_password, 
        $supervisor['phone'], 
        $supervisor['department'], 
        $supervisor['institution'], 
        $supervisor['specialization'], 
        $supervisor['years_experience']
    );
    
    if ($stmt->execute()) {
        echo "✓ Created supervisor: " . $supervisor['name'] . " (" . $supervisor['email'] . ")\n";
    } else {
        echo "✗ Failed to create supervisor: " . $supervisor['email'] . "\n";
        echo "  Error: " . $con->error . "\n";
    }
}

echo "\n=== Supervisor Login Credentials ===\n\n";

// Display all supervisor credentials
foreach ($test_supervisors as $supervisor) {
    echo "Name: " . $supervisor['name'] . "\n";
    echo "Email: " . $supervisor['email'] . "\n";
    echo "Password: " . $supervisor['password'] . "\n";
    echo "Department: " . $supervisor['department'] . "\n";
    echo "Access URL: http://your-domain/supervisor/login.php\n";
    echo "---\n";
}

echo "\nNote: These are test accounts. In production, supervisors should register with their own credentials.\n";
echo "You can now login at: /supervisor/login.php\n\n";

// Show current supervisor count
$result = $con->query("SELECT COUNT(*) as count FROM supervisors WHERE status = 'active'");
$count = $result->fetch_assoc()['count'];
echo "Total active supervisors in system: " . $count . "\n";

?>
