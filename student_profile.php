<?php
session_start();
require_once 'db.php';

// Check if user is logged in as student
if (!isset($_SESSION['student_id'])) {
    header('Location: student_login.php');
    exit;
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'] ?? 'Student';
$success = '';
$errors = [];

// Check for session-based success message
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Get current student data
$stmt = $con->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update_profile';
    
    if ($action === 'update_profile') {
        // Sanitize input
        $full_name = trim($_POST['full_name'] ?? '');
        $reg_number = trim($_POST['reg_number'] ?? '');
        $gender = $_POST['gender'] ?? '';
        $college_name = trim($_POST['college_name'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $course_name = trim($_POST['course_name'] ?? '');
        $program = trim($_POST['program'] ?? '');
        $level = trim($_POST['level'] ?? '');
        $year_of_study = (int)($_POST['year_of_study'] ?? 0);
        $phone_number = trim($_POST['phone_number'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $email = trim($_POST['email'] ?? '');

        // Comprehensive validation with detailed messages
        if (empty($full_name)) $errors[] = 'Full name is required - Please enter your complete name';
        if (empty($reg_number)) $errors[] = 'Registration number is required - This is your student registration number';
        if (empty($gender)) $errors[] = 'Gender is required - Please select your gender';
        if (empty($college_name)) $errors[] = 'College/Institution name is required - Enter your educational institution';
        if (empty($department)) $errors[] = 'Department is required - Enter your academic department';
        if (empty($course_name)) $errors[] = 'Course name is required - Enter your course/program name';
        if (empty($program)) $errors[] = 'Program type is required - Select Certificate, Diploma, Bachelor, etc.';
        if (empty($level)) $errors[] = 'Academic level is required - Enter your current level (e.g., Level 6, Year 3)';
        if ($year_of_study < 1 || $year_of_study > 8) $errors[] = 'Year of study must be between 1-8 - Select your current year';
        if (empty($phone_number)) $errors[] = 'Phone number is required - Enter a valid contact number';
        if (empty($address)) $errors[] = 'Address is required - Enter your complete residential address';
        if (empty($email)) $errors[] = 'Email address is required - Enter a valid email for communication';
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format - Please enter a valid email address (e.g., student@example.com)';
        
        // Additional phone number validation
        if (!empty($phone_number) && !preg_match('/^[0-9+\-\s\(\)]{10,15}$/', $phone_number)) {
            $errors[] = 'Phone number format is invalid - Please enter a valid phone number';
        }

        // Handle file uploads
        $profile_photo_path = $student['profile_photo'] ?? null;
        $academic_transcript_path = $student['academic_transcript'] ?? null;
        $id_document_path = $student['id_document'] ?? null;
        $cv_document_path = $student['cv_document'] ?? null;
        $upload_dir = __DIR__ . '/uploads/students/';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Handle profile photo upload
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($_FILES['profile_photo']['type'], $allowed_types)) {
                if ($_FILES['profile_photo']['size'] <= 2 * 1024 * 1024) { // 2MB limit
                    $file_extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
                    $filename = 'profile_' . $student_id . '_' . time() . '.' . $file_extension;
                    $profile_photo_path = 'uploads/students/' . $filename;
                    
                    if (!move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_dir . $filename)) {
                        $errors[] = 'Failed to upload profile photo';
                    }
                } else {
                    $errors[] = 'Profile photo size must be less than 2MB';
                }
            } else {
                $errors[] = 'Profile photo must be JPEG, PNG, or GIF format';
            }
        }

        // Handle academic transcript upload
        if (isset($_FILES['academic_transcript']) && $_FILES['academic_transcript']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (in_array($_FILES['academic_transcript']['type'], $allowed_types)) {
                if ($_FILES['academic_transcript']['size'] <= 5 * 1024 * 1024) { // 5MB limit
                    $file_extension = pathinfo($_FILES['academic_transcript']['name'], PATHINFO_EXTENSION);
                    $filename = 'transcript_' . $student_id . '_' . time() . '.' . $file_extension;
                    $academic_transcript_path = 'uploads/students/' . $filename;
                    
                    if (!move_uploaded_file($_FILES['academic_transcript']['tmp_name'], $upload_dir . $filename)) {
                        $errors[] = 'Failed to upload academic transcript';
                    }
                } else {
                    $errors[] = 'Academic transcript size must be less than 5MB';
                }
            } else {
                $errors[] = 'Academic transcript must be PDF, DOC, or DOCX format';
            }
        }

        // Handle ID document upload
        if (isset($_FILES['id_document']) && $_FILES['id_document']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
            if (in_array($_FILES['id_document']['type'], $allowed_types)) {
                if ($_FILES['id_document']['size'] <= 5 * 1024 * 1024) { // 5MB limit
                    $file_extension = pathinfo($_FILES['id_document']['name'], PATHINFO_EXTENSION);
                    $filename = 'id_' . $student_id . '_' . time() . '.' . $file_extension;
                    $id_document_path = 'uploads/students/' . $filename;
                    
                    if (!move_uploaded_file($_FILES['id_document']['tmp_name'], $upload_dir . $filename)) {
                        $errors[] = 'Failed to upload ID document';
                    }
                } else {
                    $errors[] = 'ID document size must be less than 5MB';
                }
            } else {
                $errors[] = 'ID document must be PDF, JPG, or PNG format';
            }
        }

        // Handle CV document upload
        if (isset($_FILES['cv_document']) && $_FILES['cv_document']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (in_array($_FILES['cv_document']['type'], $allowed_types)) {
                if ($_FILES['cv_document']['size'] <= 5 * 1024 * 1024) { // 5MB limit
                    $file_extension = pathinfo($_FILES['cv_document']['name'], PATHINFO_EXTENSION);
                    $filename = 'cv_' . $student_id . '_' . time() . '.' . $file_extension;
                    $cv_document_path = 'uploads/students/' . $filename;
                    
                    if (!move_uploaded_file($_FILES['cv_document']['tmp_name'], $upload_dir . $filename)) {
                        $errors[] = 'Failed to upload CV document';
                    }
                } else {
                    $errors[] = 'CV document size must be less than 5MB';
                }
            } else {
                $errors[] = 'CV document must be PDF, DOC, or DOCX format';
            }
        }

    // Check if registration number is taken by another student
    if (empty($errors) && $reg_number !== $student['reg_number']) {
        $check_stmt = $con->prepare("SELECT student_id FROM students WHERE reg_number = ? AND student_id != ?");
        $check_stmt->bind_param("si", $reg_number, $student_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $errors[] = 'Registration number already exists';
        }
        $check_stmt->close();
    }

    // Update if no errors
    if (empty($errors)) {
        // First, check if document columns exist, if not add them
        $columns_to_add = [
            'profile_photo' => 'VARCHAR(500) NULL',
            'academic_transcript' => 'VARCHAR(500) NULL',
            'id_document' => 'VARCHAR(500) NULL',
            'cv_document' => 'VARCHAR(500) NULL'
        ];
        
        foreach ($columns_to_add as $column => $definition) {
            $col_check = $con->query("SHOW COLUMNS FROM students LIKE '$column'");
            if ($col_check->num_rows == 0) {
                $con->query("ALTER TABLE students ADD COLUMN $column $definition");
            }
        }
        
        $update_stmt = $con->prepare("UPDATE students SET full_name = ?, reg_number = ?, gender = ?, college_name = ?, department = ?, course_name = ?, program = ?, level = ?, year_of_study = ?, phone_number = ?, address = ?, email = ?, profile_photo = ?, academic_transcript = ?, id_document = ?, cv_document = ? WHERE student_id = ?");
        $update_stmt->bind_param("ssssssssisssssssi", $full_name, $reg_number, $gender, $college_name, $department, $course_name, $program, $level, $year_of_study, $phone_number, $address, $email, $profile_photo_path, $academic_transcript_path, $id_document_path, $cv_document_path, $student_id);
        
        if ($update_stmt->execute()) {
            // Update session with new name
            $_SESSION['student_name'] = $full_name;
            $_SESSION['success_message'] = 'Profile updated successfully!';
            header('Location: student_profile.php');
            exit;
        } else {
            $errors[] = 'Failed to update profile. Please try again.';
        }
        $update_stmt->close();
    }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - IPT System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
<body class="bg-gray-100 min-h-screen">
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
                    <span class="text-sm">Welcome, <?php echo htmlspecialchars($student_name); ?></span>
                    <a href="student_dashboard.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-secondary transition-colors">
                        <i class="fas fa-arrow-left mr-1"></i>Back to Dashboard
                    </a>
                    <a href="student_logout.php" class="px-3 py-2 rounded-md text-sm font-medium bg-red-600 hover:bg-red-700 transition-colors">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 sm:px-0">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Profile Management</h1>
            <p class="text-gray-600 mb-6">Update your personal and academic information</p>
        </div>

        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <a href="#personal" id="tab-personal" class="tab-link border-primary text-primary border-b-2 py-2 px-1 text-sm font-medium">
                    <i class="fas fa-user mr-2"></i>Personal Details
                </a>
                <a href="#academic" id="tab-academic" class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-2 px-1 text-sm font-medium">
                    <i class="fas fa-graduation-cap mr-2"></i>Academic Details
                </a>
                <a href="#contact" id="tab-contact" class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-2 px-1 text-sm font-medium">
                    <i class="fas fa-address-book mr-2"></i>Contact Details
                </a>
                <a href="#documents" id="tab-documents" class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-2 px-1 text-sm font-medium">
                    <i class="fas fa-file-upload mr-2"></i>Documents
                </a>
            </nav>
        </div>

        <!-- Error/Success Messages -->
        <?php if (!empty($errors)): ?>
            <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4" style="display: none;">
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
            <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4" style="display: none;">
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

        <!-- Profile Form -->
        <form method="POST" action="" enctype="multipart/form-data" class="bg-white shadow-lg rounded-lg p-6">
            <input type="hidden" name="action" value="update_profile">
            
            <!-- Completion Status will be inserted here by JavaScript -->
            
            <!-- Required Fields Notice -->
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Important:</strong> All fields marked with <span class="text-red-500 font-bold">*</span> are required. 
                            Please fill in all required information to save your profile successfully.
                        </p>
                    </div>
                </div>
            </div>
            <!-- Personal Details Tab -->
            <div id="content-personal" class="tab-content">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user mr-1 text-primary"></i>Full Name
                        </label>
                        <input type="text" id="full_name" name="full_name" required
                               value="<?php echo htmlspecialchars($student['full_name'] ?? ''); ?>"
                               placeholder="Enter your complete name (e.g., John Doe Smith)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="reg_number" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-id-card mr-1 text-primary"></i>Registration Number
                        </label>
                        <input type="text" id="reg_number" name="reg_number" required
                               value="<?php echo htmlspecialchars($student['reg_number'] ?? ''); ?>"
                               placeholder="Enter your student registration number (e.g., REG/2024/001)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-venus-mars mr-1 text-primary"></i>Gender
                        </label>
                        <select id="gender" name="gender" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo ($student['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($student['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($student['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Academic Details Tab -->
            <div id="content-academic" class="tab-content hidden">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Academic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="college_name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-university mr-1 text-primary"></i>College/Institution
                        </label>
                        <input type="text" id="college_name" name="college_name" required
                               value="<?php echo htmlspecialchars($student['college_name'] ?? ''); ?>"
                               placeholder="Enter your college or institution name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-building mr-1 text-primary"></i>Department
                        </label>
                        <input type="text" id="department" name="department" required
                               value="<?php echo htmlspecialchars($student['department'] ?? ''); ?>"
                               placeholder="Enter your department (e.g., Computer Science, Engineering)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="course_name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-book mr-1 text-primary"></i>Course Name
                        </label>
                        <input type="text" id="course_name" name="course_name" required
                               value="<?php echo htmlspecialchars($student['course_name'] ?? ''); ?>"
                               placeholder="Enter your course name (e.g., Software Engineering, Business Administration)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="program" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-certificate mr-1 text-primary"></i>Program
                        </label>
                        <select id="program" name="program" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Select Program</option>
                            <option value="Certificate" <?php echo ($student['program'] ?? '') === 'Certificate' ? 'selected' : ''; ?>>Certificate</option>
                            <option value="Diploma" <?php echo ($student['program'] ?? '') === 'Diploma' ? 'selected' : ''; ?>>Diploma</option>
                            <option value="Bachelor" <?php echo ($student['program'] ?? '') === 'Bachelor' ? 'selected' : ''; ?>>Bachelor's Degree</option>
                            <option value="Master" <?php echo ($student['program'] ?? '') === 'Master' ? 'selected' : ''; ?>>Master's Degree</option>
                            <option value="PhD" <?php echo ($student['program'] ?? '') === 'PhD' ? 'selected' : ''; ?>>PhD</option>
                        </select>
                    </div>

                    <div>
                        <label for="level" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-layer-group mr-1 text-primary"></i>Level
                        </label>
                        <input type="text" id="level" name="level" required
                               value="<?php echo htmlspecialchars($student['level'] ?? ''); ?>"
                               placeholder="e.g., Level 6, Year 3, etc."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="year_of_study" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-1 text-primary"></i>Year of Study
                        </label>
                        <select id="year_of_study" name="year_of_study" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Select Year</option>
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($student['year_of_study'] ?? '') == $i ? 'selected' : ''; ?>>Year <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Contact Details Tab -->
            <div id="content-contact" class="tab-content hidden">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-1 text-primary"></i>Email Address
                        </label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>"
                               placeholder="Enter your email address (e.g., student@example.com)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-phone mr-1 text-primary"></i>Phone Number
                        </label>
                        <input type="text" id="phone_number" name="phone_number" required
                               value="<?php echo htmlspecialchars($student['phone_number'] ?? ''); ?>"
                               placeholder="Enter your phone number starting with +255 (e.g., +255753225961)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt mr-1 text-primary"></i>Address
                        </label>
                        <textarea id="address" name="address" rows="3" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                                  placeholder="Enter your complete residential address (Street, City, State/Region, Country)"><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Documents Tab -->
            <div id="content-documents" class="tab-content hidden">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Documents & Profile Photo</h3>
                
                <!-- Profile Photo Section -->
                <div class="mb-8">
                    <h4 class="text-md font-medium text-gray-800 mb-4">Profile Photo</h4>
                    <div class="flex items-start space-x-6">
                        <div class="flex-shrink-0">
                            <div class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center">
                                <?php if (!empty($student['profile_photo']) && file_exists($student['profile_photo'])): ?>
                                    <img src="<?php echo htmlspecialchars($student['profile_photo']); ?>" alt="Profile" class="w-24 h-24 rounded-full object-cover">
                                <?php else: ?>
                                    <i class="fas fa-user text-gray-400 text-2xl"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex-1">
                            <label for="profile_photo" class="block text-sm font-medium text-gray-700 mb-2">
                                Upload Profile Photo
                            </label>
                            <input type="file" id="profile_photo" name="profile_photo" accept="image/*"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary file:text-white hover:file:bg-secondary">
                            <p class="mt-1 text-xs text-gray-500">PNG, JPG up to 2MB</p>
                        </div>
                    </div>
                </div>

                <!-- Document Upload Section -->
                <div>
                    <h4 class="text-md font-medium text-gray-800 mb-4">Required Documents</h4>
                    <div class="space-y-6">
                        <!-- Academic Transcript -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <label for="academic_transcript" class="flex items-center text-sm font-medium text-gray-700">
                                    <i class="fas fa-file-alt mr-2 text-primary"></i>Academic Transcript
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <?php if (!empty($student['academic_transcript'])): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>Uploaded
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Required
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($student['academic_transcript'])): ?>
                                <div class="mb-3 p-3 bg-gray-50 rounded-md">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Current file:</span>
                                        <a href="<?php echo htmlspecialchars($student['academic_transcript']); ?>" target="_blank" 
                                           class="text-primary hover:text-secondary text-sm font-medium">
                                            <i class="fas fa-download mr-1"></i>View/Download
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <input type="file" id="academic_transcript" name="academic_transcript" accept=".pdf,.doc,.docx"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-secondary file:text-white hover:file:bg-accent">
                            <p class="mt-1 text-xs text-gray-500">PDF, DOC, DOCX up to 5MB</p>
                        </div>

                        <!-- National ID / Passport -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <label for="id_document" class="flex items-center text-sm font-medium text-gray-700">
                                    <i class="fas fa-id-card mr-2 text-primary"></i>National ID / Passport
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <?php if (!empty($student['id_document'])): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>Uploaded
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Required
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($student['id_document'])): ?>
                                <div class="mb-3 p-3 bg-gray-50 rounded-md">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Current file:</span>
                                        <a href="<?php echo htmlspecialchars($student['id_document']); ?>" target="_blank" 
                                           class="text-primary hover:text-secondary text-sm font-medium">
                                            <i class="fas fa-download mr-1"></i>View/Download
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <input type="file" id="id_document" name="id_document" accept=".pdf,.jpg,.jpeg,.png"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-secondary file:text-white hover:file:bg-accent">
                            <p class="mt-1 text-xs text-gray-500">PDF, JPG, PNG up to 5MB</p>
                        </div>

                        <!-- Curriculum Vitae (CV) -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <label for="cv_document" class="flex items-center text-sm font-medium text-gray-700">
                                    <i class="fas fa-file-user mr-2 text-primary"></i>Curriculum Vitae (CV)
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <?php if (!empty($student['cv_document'])): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>Uploaded
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Required
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($student['cv_document'])): ?>
                                <div class="mb-3 p-3 bg-gray-50 rounded-md">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Current file:</span>
                                        <a href="<?php echo htmlspecialchars($student['cv_document']); ?>" target="_blank" 
                                           class="text-primary hover:text-secondary text-sm font-medium">
                                            <i class="fas fa-download mr-1"></i>View/Download
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <input type="file" id="cv_document" name="cv_document" accept=".pdf,.doc,.docx"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-secondary file:text-white hover:file:bg-accent">
                            <p class="mt-1 text-xs text-gray-500">PDF, DOC, DOCX up to 5MB</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <div class="flex justify-end space-x-4">
                    <a href="student_dashboard.php" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-primary text-white rounded-md hover:bg-secondary transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        <i class="fas fa-save mr-2"></i>Save Profile
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tab Switching JavaScript -->
    <script>
        // SweetAlert notifications
        <?php if (!empty($errors)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Please fix the following errors:',
                html: '<?php echo "• " . implode("<br>• ", array_map(function($error) { return htmlspecialchars($error, ENT_QUOTES, "UTF-8"); }, $errors)); ?>',
                confirmButtonColor: '#dc2626'
            });
        <?php endif; ?>

        <?php if ($success): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?php echo addslashes(htmlspecialchars($success, ENT_QUOTES, "UTF-8")); ?>',
                confirmButtonColor: '#07442d'
            });
        <?php endif; ?>

        document.addEventListener('DOMContentLoaded', function() {
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');

            function showTab(targetTab) {
                // Hide all tab contents
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });

                // Remove active styles from all tabs
                tabLinks.forEach(link => {
                    link.classList.remove('border-primary', 'text-primary');
                    link.classList.add('border-transparent', 'text-gray-500');
                });

                // Show target tab content
                const targetContent = document.getElementById('content-' + targetTab);
                if (targetContent) {
                    targetContent.classList.remove('hidden');
                }

                // Add active styles to current tab
                const activeTab = document.getElementById('tab-' + targetTab);
                if (activeTab) {
                    activeTab.classList.remove('border-transparent', 'text-gray-500');
                    activeTab.classList.add('border-primary', 'text-primary');
                }
            }

            // Tab click handlers
            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetTab = this.getAttribute('href').substring(1);
                    showTab(targetTab);
                });
            });

            // Initialize first tab
            showTab('personal');

            // Form validation and submission
            const profileForm = document.querySelector('form[method="POST"]');
            if (profileForm) {
                // Add visual indicators for required fields
                const requiredFields = profileForm.querySelectorAll('input[required], select[required], textarea[required]');
                requiredFields.forEach(field => {
                    const label = document.querySelector(`label[for="${field.id}"]`);
                    if (label && !label.innerHTML.includes('*')) {
                        label.innerHTML += ' <span class="text-red-500">*</span>';
                    }
                });

                // Real-time validation for individual fields
                requiredFields.forEach(field => {
                    field.addEventListener('blur', function() {
                        validateField(this);
                    });
                    
                    field.addEventListener('input', function() {
                        clearFieldError(this);
                    });
                });

                function validateField(field) {
                    const value = field.value.trim();
                    const fieldName = field.name;
                    let isValid = true;
                    let errorMessage = '';

                    // Clear previous error
                    clearFieldError(field);

                    if (field.hasAttribute('required') && !value) {
                        isValid = false;
                        errorMessage = getFieldErrorMessage(fieldName, 'required');
                    } else if (value) {
                        // Specific field validations
                        switch (fieldName) {
                            case 'email':
                                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                                if (!emailRegex.test(value)) {
                                    isValid = false;
                                    errorMessage = 'Please enter a valid email address (e.g., student@example.com)';
                                }
                                break;
                            case 'phone_number':
                                // More flexible phone regex that accepts various formats including leading zeros
                                const phoneRegex = /^[0-9+\-\s\(\)]{10,15}$/;
                                const digitsOnly = value.replace(/[^0-9]/g, '');
                                if (!phoneRegex.test(value) || digitsOnly.length < 10 || digitsOnly.length > 15) {
                                    isValid = false;
                                    errorMessage = 'Please enter a valid phone number starting with +255 (e.g., +255753225961)';
                                }
                                break;
                            case 'year_of_study':
                                const year = parseInt(value);
                                if (year < 1 || year > 8) {
                                    isValid = false;
                                    errorMessage = 'Year of study must be between 1 and 8';
                                }
                                break;
                            case 'reg_number':
                                if (value.length < 3) {
                                    isValid = false;
                                    errorMessage = 'Registration number should be at least 3 characters';
                                }
                                break;
                        }
                    }

                    if (!isValid) {
                        showFieldError(field, errorMessage);
                    }

                    return isValid;
                }

                function getFieldErrorMessage(fieldName, type) {
                    const messages = {
                        'full_name': 'Please enter your complete name',
                        'reg_number': 'Registration number is required',
                        'gender': 'Please select your gender',
                        'college_name': 'Please enter your college/institution name',
                        'department': 'Please enter your department',
                        'course_name': 'Please enter your course name',
                        'program': 'Please select your program type',
                        'level': 'Please enter your academic level',
                        'year_of_study': 'Please select your year of study',
                        'phone_number': 'Please enter your phone number',
                        'address': 'Please enter your complete address',
                        'email': 'Please enter your email address'
                    };
                    return messages[fieldName] || 'This field is required';
                }

                function showFieldError(field, message) {
                    field.classList.add('border-red-500', 'bg-red-50');
                    field.classList.remove('border-gray-300');
                    
                    // Remove existing error message
                    const existingError = field.parentNode.querySelector('.field-error');
                    if (existingError) {
                        existingError.remove();
                    }
                    
                    // Add error message
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'field-error text-red-600 text-sm mt-1';
                    errorDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i>' + message;
                    field.parentNode.appendChild(errorDiv);
                }

                function clearFieldError(field) {
                    field.classList.remove('border-red-500', 'bg-red-50');
                    field.classList.add('border-gray-300');
                    
                    const existingError = field.parentNode.querySelector('.field-error');
                    if (existingError) {
                        existingError.remove();
                    }
                }

                // Check completion percentage
                function updateCompletionStatus() {
                    const totalFields = requiredFields.length;
                    let completedFields = 0;
                    
                    requiredFields.forEach(field => {
                        if (field.value.trim()) {
                            completedFields++;
                        }
                    });
                    
                    const percentage = Math.round((completedFields / totalFields) * 100);
                    
                    // Update or create completion indicator
                    let indicator = document.getElementById('completion-indicator');
                    if (!indicator) {
                        indicator = document.createElement('div');
                        indicator.id = 'completion-indicator';
                        indicator.className = 'mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md';
                        const form = document.querySelector('form[method="POST"]');
                        form.insertBefore(indicator, form.firstChild);
                    }
                    
                    if (percentage === 100) {
                        indicator.className = 'mb-4 p-3 bg-green-50 border border-green-200 rounded-md';
                        indicator.innerHTML = '<i class="fas fa-check-circle text-green-600 mr-2"></i><strong>Profile Complete!</strong> All required fields are filled. You can now save your profile.';
                    } else {
                        indicator.className = 'mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md';
                        indicator.innerHTML = `<i class="fas fa-info-circle text-yellow-600 mr-2"></i><strong>Profile ${percentage}% Complete</strong> - Please fill all required fields marked with <span class="text-red-500">*</span> to save your profile.`;
                    }
                }

                // File upload validation and preview
                const fileInputs = document.querySelectorAll('input[type="file"]');
                fileInputs.forEach(input => {
                    input.addEventListener('change', function(e) {
                        const file = e.target.files[0];
                        if (!file) return;
                        
                        const fileType = input.name;
                        let maxSize, allowedTypes;
                        
                        // Set validation rules based on file type
                        if (fileType === 'profile_photo') {
                            maxSize = 2 * 1024 * 1024; // 2MB
                            allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                        } else {
                            maxSize = 5 * 1024 * 1024; // 5MB
                            if (fileType === 'id_document') {
                                allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
                            } else {
                                allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                            }
                        }
                        
                        // Validate file size
                        if (file.size > maxSize) {
                            const maxSizeMB = maxSize / (1024 * 1024);
                            Swal.fire({
                                icon: 'error',
                                title: 'File Too Large',
                                text: `File size must be less than ${maxSizeMB}MB`,
                                confirmButtonColor: '#dc2626'
                            });
                            input.value = '';
                            return;
                        }
                        
                        // Validate file type
                        if (!allowedTypes.includes(file.type)) {
                            let allowedText = '';
                            if (fileType === 'profile_photo') {
                                allowedText = 'JPG, PNG, or GIF';
                            } else if (fileType === 'id_document') {
                                allowedText = 'PDF, JPG, or PNG';
                            } else {
                                allowedText = 'PDF, DOC, or DOCX';
                            }
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Invalid File Type',
                                text: `Please select a ${allowedText} file`,
                                confirmButtonColor: '#dc2626'
                            });
                            input.value = '';
                            return;
                        }
                        
                        // Show success message for valid file
                        const fileName = file.name;
                        const fileSize = (file.size / (1024 * 1024)).toFixed(2);
                        
                        // Update the visual indicator if it exists
                        const container = input.closest('.border');
                        if (container) {
                            let indicator = container.querySelector('.file-selected-indicator');
                            if (!indicator) {
                                indicator = document.createElement('div');
                                indicator.className = 'file-selected-indicator mt-2 p-2 bg-green-50 border border-green-200 rounded text-sm text-green-700';
                                input.parentNode.appendChild(indicator);
                            }
                            indicator.innerHTML = `<i class="fas fa-check-circle mr-1"></i>Selected: ${fileName} (${fileSize} MB)`;
                        }
                    });
                });

                // Update completion status on field changes
                requiredFields.forEach(field => {
                    field.addEventListener('input', updateCompletionStatus);
                    field.addEventListener('change', updateCompletionStatus);
                });

                // Initial completion check
                updateCompletionStatus();

                profileForm.addEventListener('submit', function(e) {
                    // Comprehensive validation before submission
                    const requiredFieldData = [
                        { field: 'full_name', name: 'Full Name', message: 'Please enter your complete name' },
                        { field: 'reg_number', name: 'Registration Number', message: 'Please enter your student registration number' },
                        { field: 'gender', name: 'Gender', message: 'Please select your gender' },
                        { field: 'college_name', name: 'College/Institution', message: 'Please enter your educational institution name' },
                        { field: 'department', name: 'Department', message: 'Please enter your academic department' },
                        { field: 'course_name', name: 'Course Name', message: 'Please enter your course/program name' },
                        { field: 'program', name: 'Program Type', message: 'Please select your program type (Certificate, Diploma, etc.)' },
                        { field: 'level', name: 'Academic Level', message: 'Please enter your current academic level' },
                        { field: 'year_of_study', name: 'Year of Study', message: 'Please select your current year of study' },
                        { field: 'phone_number', name: 'Phone Number', message: 'Please enter your contact phone number' },
                        { field: 'address', name: 'Address', message: 'Please enter your complete residential address' },
                        { field: 'email', name: 'Email Address', message: 'Please enter a valid email address for communication' }
                    ];
                    
                    const errors = [];
                    let firstErrorField = null;
                    
                    requiredFieldData.forEach(({ field, name, message }) => {
                        const element = document.querySelector(`[name="${field}"]`);
                        if (!element || !element.value.trim()) {
                            errors.push(message);
                            if (!firstErrorField) {
                                firstErrorField = element;
                            }
                            if (element) {
                                showFieldError(element, message);
                            }
                        }
                    });

                    // Email validation
                    const emailField = document.querySelector('[name="email"]');
                    if (emailField && emailField.value.trim()) {
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(emailField.value.trim())) {
                            errors.push('Please enter a valid email address (e.g., student@example.com)');
                            showFieldError(emailField, 'Please enter a valid email address');
                            if (!firstErrorField) firstErrorField = emailField;
                        }
                    }

                    // Phone validation
                    const phoneField = document.querySelector('[name="phone_number"]');
                    if (phoneField && phoneField.value.trim()) {
                        const phoneRegex = /^[0-9+\-\s\(\)]{10,15}$/;
                        const digitsOnly = phoneField.value.trim().replace(/[^0-9]/g, '');
                        if (!phoneRegex.test(phoneField.value.trim()) || digitsOnly.length < 10 || digitsOnly.length > 15) {
                            errors.push('Please enter a valid phone number starting with +255 (e.g., +255753225961)');
                            showFieldError(phoneField, 'Please enter a valid phone number');
                            if (!firstErrorField) firstErrorField = phoneField;
                        }
                    }

                    // Year validation
                    const yearField = document.querySelector('[name="year_of_study"]');
                    if (yearField && yearField.value) {
                        const year = parseInt(yearField.value);
                        if (year < 1 || year > 8) {
                            errors.push('Year of study must be between 1 and 8');
                            showFieldError(yearField, 'Year must be between 1-8');
                            if (!firstErrorField) firstErrorField = yearField;
                        }
                    }
                    
                    if (errors.length > 0) {
                        e.preventDefault();
                        
                        // Scroll to first error field
                        if (firstErrorField) {
                            firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            firstErrorField.focus();
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Please Complete All Required Fields',
                            html: '<div class="text-left"><p class="mb-3"><strong>The following fields need to be completed:</strong></p><ul class="text-sm">' + 
                                  errors.map(error => '<li class="mb-1"><i class="fas fa-exclamation-circle text-red-500 mr-2"></i>' + error + '</li>').join('') + 
                                  '</ul><p class="mt-3 text-sm text-gray-600"><strong>Tip:</strong> Look for fields marked with <span class="text-red-500">*</span> and error messages in red.</p></div>',
                            confirmButtonColor: '#dc2626',
                            confirmButtonText: 'OK, I\'ll complete them',
                            width: '600px'
                        });
                        return false;
                    }
                    
                    // Show loading for form submission
                    Swal.fire({
                        title: 'Updating Profile...',
                        text: 'Please wait while we save your information',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                });
            }

            // File upload preview
            const profilePhotoInput = document.getElementById('profile_photo');
            if (profilePhotoInput) {
                profilePhotoInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        // Validate file size (2MB)
                        if (file.size > 2 * 1024 * 1024) {
                            Swal.fire({
                                icon: 'error',
                                title: 'File Too Large',
                                text: 'Profile photo must be less than 2MB',
                                confirmButtonColor: '#dc2626'
                            });
                            e.target.value = '';
                            return;
                        }

                        // Validate file type
                        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                        if (!allowedTypes.includes(file.type)) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Invalid File Type',
                                text: 'Please select a JPEG, PNG, or GIF image',
                                confirmButtonColor: '#dc2626'
                            });
                            e.target.value = '';
                            return;
                        }

                        // Show preview
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const imgContainer = document.querySelector('.w-24.h-24.rounded-full');
                            if (imgContainer) {
                                imgContainer.innerHTML = '<img src="' + e.target.result + '" alt="Profile Preview" class="w-24 h-24 rounded-full object-cover">';
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
</body>
</html>
