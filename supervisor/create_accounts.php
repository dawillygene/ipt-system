<?php
// Simple Supervisor Account Creator
require_once '../db.php';

echo "<h2>Creating Supervisor Test Accounts</h2>";

// Let's check what tables exist
$tables_result = $con->query("SHOW TABLES");
echo "<h3>Available tables:</h3>";
while ($row = $tables_result->fetch_array()) {
    echo "<p>- " . $row[0] . "</p>";
}

// Check if we have a users table for general authentication
$users_check = $con->query("DESCRIBE users");
if ($users_check) {
    echo "<h3>Users table structure:</h3>";
    while ($row = $users_check->fetch_assoc()) {
        echo "<p>" . $row['Field'] . " (" . $row['Type'] . ")</p>";
    }
}

// Create supervisor accounts in the users table with supervisor role
$supervisors = [
    [
        'name' => 'Dr. John Smith',
        'email' => 'supervisor@test.com', 
        'password' => 'password123'
    ],
    [
        'name' => 'Prof. Sarah Johnson',
        'email' => 'sarah.supervisor@test.com',
        'password' => 'supervisor456'  
    ]
];

echo "<h3>Creating Supervisor User Accounts:</h3>";

foreach ($supervisors as $supervisor) {
    // Check if user exists
    $check = $con->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $supervisor['email']);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        echo "<p>✓ User already exists: " . $supervisor['email'] . "</p>";
        continue;
    }
    
    // Create user account
    $hashed_password = password_hash($supervisor['password'], PASSWORD_DEFAULT);
    $stmt = $con->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'supervisor')");
    $stmt->bind_param("sss", $supervisor['name'], $supervisor['email'], $hashed_password);
    
    if ($stmt->execute()) {
        echo "<p>✓ Created: " . $supervisor['name'] . " (" . $supervisor['email'] . ")</p>";
    } else {
        echo "<p>✗ Failed to create: " . $supervisor['email'] . " - " . $con->error . "</p>";
    }
}

echo "<hr>";
echo "<h2>🔑 Supervisor Login Credentials</h2>";
echo "<div style='background: #f0f8ff; padding: 20px; margin: 20px 0; border: 1px solid #ddd;'>";
echo "<h3>Test Account 1:</h3>";
echo "<strong>Email:</strong> supervisor@test.com<br>";
echo "<strong>Password:</strong> password123<br>";
echo "<strong>Name:</strong> Dr. John Smith<br><br>";

echo "<h3>Test Account 2:</h3>";
echo "<strong>Email:</strong> sarah.supervisor@test.com<br>";
echo "<strong>Password:</strong> supervisor456<br>";
echo "<strong>Name:</strong> Prof. Sarah Johnson<br><br>";

echo "<h3>Login URL:</h3>";
echo "<a href='login.php' target='_blank'>Go to Supervisor Login</a><br>";
echo "</div>";

// Show current count  
$result = $con->query("SELECT COUNT(*) as count FROM users WHERE role = 'supervisor'");
$count = $result ? $result->fetch_assoc()['count'] : 0;
echo "<p>Total supervisor users in database: <strong>" . $count . "</strong></p>";

echo "<hr>";
echo "<h3>Quick Actions:</h3>";
echo "<p><a href='login.php'>→ Go to Supervisor Login</a></p>";
echo "<p><a href='register.php'>→ Register New Supervisor</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #07442d; }
h3 { color: #206f56; }
p { margin: 10px 0; }
</style>
