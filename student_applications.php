<?php
session_start();
require_once 'db.php';

// Check if user is logged in as student
if (!isset($_SESSION['student_id'])) {
    header('Location: student_login.php');
    exit;
}

$student_id = $_SESSION['student_id'];
$success = '';
$errors = [];

// Get student information
$stmt = $con->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Create applications table if it doesn't exist
$con->query("CREATE TABLE IF NOT EXISTS applications (
    application_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    company_location VARCHAR(255) NOT NULL,
    position_title VARCHAR(255) NOT NULL,
    training_duration INT(11) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    training_area VARCHAR(255) NOT NULL,
    skills_to_acquire TEXT,
    motivation_letter TEXT NOT NULL,
    preferred_company1 VARCHAR(255),
    preferred_company2 VARCHAR(255),
    preferred_company3 VARCHAR(255),
    status ENUM('draft', 'submitted', 'approved', 'rejected', 'in_review') DEFAULT 'draft',
    submitted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
)");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Get form data
    $company_name = trim($_POST['company_name'] ?? '');
    $company_location = trim($_POST['company_location'] ?? '');
    $position_title = trim($_POST['position_title'] ?? '');
    $training_duration = (int)($_POST['training_duration'] ?? 0);
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $training_area = trim($_POST['training_area'] ?? '');
    $skills_to_acquire = trim($_POST['skills_to_acquire'] ?? '');
    $motivation_letter = trim($_POST['motivation_letter'] ?? '');
    $preferred_company1 = trim($_POST['preferred_company1'] ?? '');
    $preferred_company2 = trim($_POST['preferred_company2'] ?? '');
    $preferred_company3 = trim($_POST['preferred_company3'] ?? '');

    // Validation for submitted applications
    if ($action === 'submit') {
        if (empty($company_name)) $errors[] = 'Company name is required';
        if (empty($company_location)) $errors[] = 'Company location is required';
        if (empty($position_title)) $errors[] = 'Position title is required';
        if ($training_duration < 1) $errors[] = 'Training duration must be at least 1 week';
        if (empty($start_date)) $errors[] = 'Start date is required';
        if (empty($end_date)) $errors[] = 'End date is required';
        if (empty($training_area)) $errors[] = 'Training area is required';
        if (empty($motivation_letter)) $errors[] = 'Motivation letter is required';
        if (strlen($motivation_letter) < 100) $errors[] = 'Motivation letter must be at least 100 characters';

        // Date validation
        if ($start_date && $end_date) {
            $start = new DateTime($start_date);
            $end = new DateTime($end_date);
            $today = new DateTime();
            
            if ($start <= $today) {
                $errors[] = 'Start date must be in the future';
            }
            if ($end <= $start) {
                $errors[] = 'End date must be after start date';
            }
        }
    }

    // Save application
    if (empty($errors) || $action === 'save_draft') {
        $status = ($action === 'submit') ? 'submitted' : 'draft';
        $submitted_at = ($action === 'submit') ? date('Y-m-d H:i:s') : null;

        // Check if application already exists for this student
        $check_stmt = $con->prepare("SELECT application_id FROM applications WHERE student_id = ?");
        $check_stmt->bind_param("i", $student_id);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();

        if ($existing) {
            // Update existing application
            $update_stmt = $con->prepare("UPDATE applications SET company_name = ?, company_location = ?, position_title = ?, training_duration = ?, start_date = ?, end_date = ?, training_area = ?, skills_to_acquire = ?, motivation_letter = ?, preferred_company1 = ?, preferred_company2 = ?, preferred_company3 = ?, status = ?, submitted_at = ? WHERE student_id = ?");
            $update_stmt->bind_param("sssississsssssi", $company_name, $company_location, $position_title, $training_duration, $start_date, $end_date, $training_area, $skills_to_acquire, $motivation_letter, $preferred_company1, $preferred_company2, $preferred_company3, $status, $submitted_at, $student_id);
            
            if ($update_stmt->execute()) {
                $success = ($action === 'submit') ? 'Application submitted successfully!' : 'Application saved as draft';
            } else {
                $errors[] = 'Failed to save application. Please try again.';
            }
            $update_stmt->close();
        } else {
            // Create new application
            $insert_stmt = $con->prepare("INSERT INTO applications (student_id, company_name, company_location, position_title, training_duration, start_date, end_date, training_area, skills_to_acquire, motivation_letter, preferred_company1, preferred_company2, preferred_company3, status, submitted_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("isssisssssssss", $student_id, $company_name, $company_location, $position_title, $training_duration, $start_date, $end_date, $training_area, $skills_to_acquire, $motivation_letter, $preferred_company1, $preferred_company2, $preferred_company3, $status, $submitted_at);
            
            if ($insert_stmt->execute()) {
                $success = ($action === 'submit') ? 'Application submitted successfully!' : 'Application saved as draft';
            } else {
                $errors[] = 'Failed to save application. Please try again.';
            }
            $insert_stmt->close();
        }
    }
}

// Get existing application data based on current table structure
$existing_application = null;
$stmt = $con->prepare("SELECT * FROM applications WHERE user_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $existing_application = $result->fetch_assoc();
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Application - IPT System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#07442d',
                        'secondary': '#206f56',
                        'accent': '#0f7b5a',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-primary text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-lg sm:text-xl font-bold">
                            <i class="fas fa-graduation-cap mr-2"></i>IPT System
                        </h1>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="student_dashboard.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-secondary transition-colors">
                        <i class="fas fa-arrow-left mr-1"></i>Back to Dashboard
                    </a>
                    <a href="student_logout.php" class="px-3 py-2 rounded-md text-sm font-medium bg-secondary hover:bg-accent transition-colors">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 sm:px-0">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Training Application</h1>
            <p class="text-gray-600 mb-6">Apply for Industrial Practical Training placement</p>
        </div>

        <!-- Application Status -->
        <?php if ($existing_application): ?>
            <div class="mb-6 bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Application Status</h3>
                        <p class="text-sm text-gray-600">Current status of your training application</p>
                    </div>
                    <div class="flex items-center">
                        <?php
                        $status = $existing_application['status'];
                        $status_colors = [
                            'draft' => 'bg-gray-100 text-gray-800',
                            'submitted' => 'bg-blue-100 text-blue-800',
                            'in_review' => 'bg-yellow-100 text-yellow-800',
                            'approved' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800'
                        ];
                        $status_icons = [
                            'draft' => 'fas fa-edit',
                            'submitted' => 'fas fa-paper-plane',
                            'in_review' => 'fas fa-clock',
                            'approved' => 'fas fa-check-circle',
                            'rejected' => 'fas fa-times-circle'
                        ];
                        ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $status_colors[$status]; ?>">
                            <i class="<?php echo $status_icons[$status]; ?> mr-2"></i>
                            <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                        </span>
                    </div>
                </div>
                <?php if ($existing_application['submitted_at']): ?>
                    <p class="mt-2 text-sm text-gray-500">
                        Submitted on <?php echo date('F j, Y \a\t g:i A', strtotime($existing_application['submitted_at'])); ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Error/Success Messages -->
        <?php if (!empty($errors)): ?>
            <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800"><?php echo htmlspecialchars($success); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Application Form -->
        <form method="POST" action="" id="applicationForm" class="bg-white shadow-lg rounded-lg p-6">
            <!-- Company Information -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Company Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-building mr-1 text-primary"></i>Company Name *
                        </label>
                        <input type="text" id="company_name" name="company_name" required
                               value="<?php echo htmlspecialchars($existing_application['company_name'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="company_location" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt mr-1 text-primary"></i>Company Location *
                        </label>
                        <input type="text" id="company_location" name="company_location" required
                               value="<?php echo htmlspecialchars($existing_application['company_location'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="position_title" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-briefcase mr-1 text-primary"></i>Position/Role *
                        </label>
                        <input type="text" id="position_title" name="position_title" required
                               value="<?php echo htmlspecialchars($existing_application['position_title'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="training_area" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tools mr-1 text-primary"></i>Training Area *
                        </label>
                        <select id="training_area" name="training_area" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Select Training Area</option>
                            <option value="Software Development" <?php echo ($existing_application['training_area'] ?? '') === 'Software Development' ? 'selected' : ''; ?>>Software Development</option>
                            <option value="Network Administration" <?php echo ($existing_application['training_area'] ?? '') === 'Network Administration' ? 'selected' : ''; ?>>Network Administration</option>
                            <option value="Database Management" <?php echo ($existing_application['training_area'] ?? '') === 'Database Management' ? 'selected' : ''; ?>>Database Management</option>
                            <option value="Cyber Security" <?php echo ($existing_application['training_area'] ?? '') === 'Cyber Security' ? 'selected' : ''; ?>>Cyber Security</option>
                            <option value="Web Development" <?php echo ($existing_application['training_area'] ?? '') === 'Web Development' ? 'selected' : ''; ?>>Web Development</option>
                            <option value="Mobile App Development" <?php echo ($existing_application['training_area'] ?? '') === 'Mobile App Development' ? 'selected' : ''; ?>>Mobile App Development</option>
                            <option value="IT Support" <?php echo ($existing_application['training_area'] ?? '') === 'IT Support' ? 'selected' : ''; ?>>IT Support</option>
                            <option value="Data Analysis" <?php echo ($existing_application['training_area'] ?? '') === 'Data Analysis' ? 'selected' : ''; ?>>Data Analysis</option>
                            <option value="Other" <?php echo ($existing_application['training_area'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Training Period -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Training Period</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="training_duration" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-1 text-primary"></i>Duration (weeks) *
                        </label>
                        <select id="training_duration" name="training_duration" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Select Duration</option>
                            <?php for ($i = 1; $i <= 52; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($existing_application['training_duration'] ?? '') == $i ? 'selected' : ''; ?>><?php echo $i; ?> week<?php echo $i > 1 ? 's' : ''; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-plus mr-1 text-primary"></i>Start Date *
                        </label>
                        <input type="date" id="start_date" name="start_date" required
                               value="<?php echo htmlspecialchars($existing_application['start_date'] ?? ''); ?>"
                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-minus mr-1 text-primary"></i>End Date *
                        </label>
                        <input type="date" id="end_date" name="end_date" required
                               value="<?php echo htmlspecialchars($existing_application['end_date'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                </div>
            </div>

            <!-- Skills and Motivation -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Skills & Motivation</h3>
                <div class="space-y-6">
                    <div>
                        <label for="skills_to_acquire" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lightbulb mr-1 text-primary"></i>Skills You Want to Acquire
                        </label>
                        <textarea id="skills_to_acquire" name="skills_to_acquire" rows="3"
                                  placeholder="List the specific skills and knowledge you hope to gain during this training..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"><?php echo htmlspecialchars($existing_application['skills_to_acquire'] ?? ''); ?></textarea>
                    </div>

                    <div>
                        <label for="motivation_letter" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-heart mr-1 text-primary"></i>Motivation Letter *
                        </label>
                        <textarea id="motivation_letter" name="motivation_letter" rows="6" required
                                  placeholder="Explain why you want to do your industrial training at this company, what you hope to achieve, and how it relates to your career goals... (minimum 100 characters)"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"><?php echo htmlspecialchars($existing_application['motivation_letter'] ?? ''); ?></textarea>
                        <p class="mt-1 text-sm text-gray-500">
                            Character count: <span id="char-count">0</span> / 100 minimum
                        </p>
                    </div>
                </div>
            </div>

            <!-- Alternative Companies -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Alternative Company Preferences</h3>
                <p class="text-sm text-gray-600 mb-4">List alternative companies in case your first choice is not available</p>
                <div class="space-y-4">
                    <div>
                        <label for="preferred_company1" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-star mr-1 text-primary"></i>Second Choice Company
                        </label>
                        <input type="text" id="preferred_company1" name="preferred_company1"
                               value="<?php echo htmlspecialchars($existing_application['preferred_company1'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="preferred_company2" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-star-half-alt mr-1 text-primary"></i>Third Choice Company
                        </label>
                        <input type="text" id="preferred_company2" name="preferred_company2"
                               value="<?php echo htmlspecialchars($existing_application['preferred_company2'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="preferred_company3" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-star mr-1 text-gray-400"></i>Fourth Choice Company
                        </label>
                        <input type="text" id="preferred_company3" name="preferred_company3"
                               value="<?php echo htmlspecialchars($existing_application['preferred_company3'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-6 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-4">
                    <a href="student_dashboard.php" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors text-center">
                        Cancel
                    </a>
                    <button type="submit" name="action" value="save_draft" 
                            class="px-6 py-2 border border-secondary text-secondary rounded-md hover:bg-secondary hover:text-white transition-colors">
                        <i class="fas fa-save mr-2"></i>Save as Draft
                    </button>
                    <button type="submit" name="action" value="submit" 
                            class="px-6 py-2 bg-primary text-white rounded-md hover:bg-secondary transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Application
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- JavaScript for form enhancements -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const motivationLetter = document.getElementById('motivation_letter');
            const charCount = document.getElementById('char-count');
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            const duration = document.getElementById('training_duration');

            // Character counter
            function updateCharCount() {
                const count = motivationLetter.value.length;
                charCount.textContent = count;
                charCount.className = count >= 100 ? 'text-green-600' : 'text-red-600';
            }

            motivationLetter.addEventListener('input', updateCharCount);
            updateCharCount();

            // Auto-calculate end date based on start date and duration
            function calculateEndDate() {
                if (startDate.value && duration.value) {
                    const start = new Date(startDate.value);
                    const weeks = parseInt(duration.value);
                    const end = new Date(start.getTime() + (weeks * 7 * 24 * 60 * 60 * 1000));
                    endDate.value = end.toISOString().split('T')[0];
                }
            }

            startDate.addEventListener('change', calculateEndDate);
            duration.addEventListener('change', calculateEndDate);

            // Form validation before submission
            document.getElementById('applicationForm').addEventListener('submit', function(e) {
                const action = e.submitter.value;
                if (action === 'submit') {
                    if (motivationLetter.value.length < 100) {
                        e.preventDefault();
                        alert('Motivation letter must be at least 100 characters long.');
                        motivationLetter.focus();
                        return false;
                    }
                }
            });
        });
    </script>
</body>
</html>
