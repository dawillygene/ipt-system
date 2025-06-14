<?php
// Database configuration for supervisor module
require_once '../db.php'; // Use the main database connection

// Use the existing users table for supervisor authentication
// but create additional supervisor-specific tables for extended functionality

// Check if the supervisors table exists, if not, use the users table approach
$check_supervisors_table = $con->query("SHOW TABLES LIKE 'supervisors'");
$supervisors_table_exists = $check_supervisors_table->num_rows > 0;

if (!$supervisors_table_exists) {
    // Create a view or use users table directly for supervisors
    // For now, we'll work with the users table where role = 'Supervisor'
}

// Create supervisor-specific tables if they don't exist

// Supervisors table
$con->query("CREATE TABLE IF NOT EXISTS supervisors (
    supervisor_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    supervisor_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20),
    department VARCHAR(255) NOT NULL,
    institution VARCHAR(255) NOT NULL,
    specialization TEXT,
    years_experience INT(11) DEFAULT 0,
    profile_photo VARCHAR(500),
    bio TEXT,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Student-Supervisor assignments
$con->query("CREATE TABLE IF NOT EXISTS supervisor_assignments (
    assignment_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    supervisor_id INT(11) NOT NULL,
    student_id INT(11) NOT NULL,
    assignment_type ENUM('academic', 'industrial') NOT NULL DEFAULT 'academic',
    assigned_date DATE NOT NULL,
    status ENUM('active', 'completed', 'terminated') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (supervisor_id, student_id, assignment_type)
)");

// Report reviews and feedback
$con->query("CREATE TABLE IF NOT EXISTS report_reviews (
    review_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    report_id INT(11) NOT NULL,
    supervisor_id INT(11) NOT NULL,
    review_status ENUM('pending', 'reviewed', 'approved', 'needs_revision') DEFAULT 'pending',
    feedback_content TEXT,
    grade VARCHAR(10),
    review_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES student_reports(report_id) ON DELETE CASCADE
)");

// Student evaluations
$con->query("CREATE TABLE IF NOT EXISTS student_evaluations (
    evaluation_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    supervisor_id INT(11) NOT NULL,
    evaluation_type ENUM('mid_term', 'final', 'monthly', 'custom') NOT NULL,
    evaluation_period VARCHAR(100) NOT NULL,
    technical_skills_score INT(11) CHECK (technical_skills_score >= 0 AND technical_skills_score <= 100),
    communication_score INT(11) CHECK (communication_score >= 0 AND communication_score <= 100),
    teamwork_score INT(11) CHECK (teamwork_score >= 0 AND teamwork_score <= 100),
    punctuality_score INT(11) CHECK (punctuality_score >= 0 AND punctuality_score <= 100),
    initiative_score INT(11) CHECK (initiative_score >= 0 AND initiative_score <= 100),
    overall_grade VARCHAR(10),
    strengths TEXT,
    areas_for_improvement TEXT,
    recommendations TEXT,
    evaluation_date DATE NOT NULL,
    status ENUM('draft', 'submitted', 'approved') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
)");

// Supervisor messages/communications
$con->query("CREATE TABLE IF NOT EXISTS supervisor_messages (
    message_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    sender_id INT(11) NOT NULL,
    sender_type ENUM('supervisor', 'student', 'admin') NOT NULL,
    recipient_id INT(11) NOT NULL,
    recipient_type ENUM('supervisor', 'student', 'admin') NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message_content TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    is_urgent BOOLEAN DEFAULT FALSE,
    parent_message_id INT(11) NULL,
    attachment_path VARCHAR(500),
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (parent_message_id) REFERENCES supervisor_messages(message_id) ON DELETE SET NULL
)");

// Meeting schedules
$con->query("CREATE TABLE IF NOT EXISTS supervisor_meetings (
    meeting_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    supervisor_id INT(11) NOT NULL,
    student_id INT(11) NOT NULL,
    meeting_title VARCHAR(255) NOT NULL,
    meeting_description TEXT,
    meeting_date DATE NOT NULL,
    meeting_time TIME NOT NULL,
    duration_minutes INT(11) DEFAULT 60,
    location VARCHAR(255),
    meeting_type ENUM('physical', 'virtual', 'phone') DEFAULT 'physical',
    meeting_link VARCHAR(500),
    status ENUM('scheduled', 'completed', 'cancelled', 'rescheduled') DEFAULT 'scheduled',
    agenda TEXT,
    meeting_notes TEXT,
    action_items TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
)");

// Supervisor session management
function startSupervisorSession($supervisor_id, $supervisor_name, $email) {
    $_SESSION['supervisor_id'] = $supervisor_id;
    $_SESSION['supervisor_name'] = $supervisor_name;
    $_SESSION['supervisor_email'] = $email;
    $_SESSION['user_type'] = 'supervisor';
}

function checkSupervisorSession() {
    if (!isset($_SESSION['supervisor_id']) || $_SESSION['user_type'] !== 'supervisor') {
        header('Location: login.php');
        exit;
    }
}

function getSupervisorInfo($con, $supervisor_id) {
    // Get supervisor info from users table
    $stmt = $con->prepare("SELECT * FROM users WHERE id = ? AND role = 'Supervisor'");
    $stmt->bind_param("i", $supervisor_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Map fields to match expected structure
    if ($result) {
        $result['supervisor_name'] = $result['name'];
        $result['supervisor_id'] = $result['id'];
    }
    
    return $result;
}

function getAssignedStudents($con, $supervisor_id) {
    $stmt = $con->prepare("
        SELECT s.*, sa.assignment_type, sa.assigned_date, sa.status as assignment_status 
        FROM students s 
        JOIN supervisor_assignments sa ON s.student_id = sa.student_id 
        WHERE sa.supervisor_id = ? AND sa.status = 'active'
        ORDER BY sa.assigned_date DESC
    ");
    $stmt->bind_param("i", $supervisor_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $result;
}

function getStudentReports($con, $student_id, $supervisor_id = null) {
    $sql = "SELECT sr.*, rr.review_status, rr.feedback_content, rr.grade, rr.review_date 
            FROM student_reports sr 
            LEFT JOIN report_reviews rr ON sr.report_id = rr.report_id";
    
    if ($supervisor_id) {
        $sql .= " AND rr.supervisor_id = ?";
        $stmt = $con->prepare($sql . " WHERE sr.student_id = ? ORDER BY sr.report_date DESC");
        $stmt->bind_param("ii", $supervisor_id, $student_id);
    } else {
        $stmt = $con->prepare($sql . " WHERE sr.student_id = ? ORDER BY sr.report_date DESC");
        $stmt->bind_param("i", $student_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $result;
}
?>
