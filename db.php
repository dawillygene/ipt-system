<?php

// Load environment variables
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = array_map('trim', explode('=', $line, 2));
        // Remove quotes if present
        $value = trim($value, '"\'');
        putenv("$name=$value");
    }
}

// Database configuration
$servername = getenv('DB_HOST') ?: '127.0.0.1';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$dbname = getenv('DB_NAME') ?: 'ipt-sys-test';

// Create connection with error handling
try {
    // Try to connect with socket if localhost fails
    if ($servername === 'localhost') {
        $con = new mysqli('localhost', $username, $password, $dbname, 3306, '/var/run/mysqld/mysqld.sock');
    } else {
        $con = new mysqli($servername, $username, $password, $dbname);
    }
    
    // Check connection
    if ($con->connect_error) {
        throw new Exception("Connection failed: " . $con->connect_error);
    }
    
    // Set charset to UTF-8
    $con->set_charset("utf8mb4");
    
    // Set timezone
    $con->query("SET time_zone = '+00:00'");
    
} catch (Exception $e) {
    // Log error (in production, log to file instead of displaying)
    error_log("Database connection error: " . $e->getMessage());
    
    if (getenv('APP_DEBUG') === 'true') {
        die("Database connection failed: " . $e->getMessage());
    } else {
        die("Database connection failed. Please try again later.");
    }
}

// Function to escape and sanitize input
function sanitize_input($data) {
    global $con;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $con->real_escape_string($data);
}

// Function to validate CSRF token
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Function to generate CSRF token
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

?>
