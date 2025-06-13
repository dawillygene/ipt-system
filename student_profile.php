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

// Get current student data
$stmt = $con->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // Validation
    if (empty($full_name)) $errors[] = 'Full name is required';
    if (empty($reg_number)) $errors[] = 'Registration number is required';
    if (empty($gender)) $errors[] = 'Gender is required';
    if (empty($college_name)) $errors[] = 'College name is required';
    if (empty($department)) $errors[] = 'Department is required';
    if (empty($course_name)) $errors[] = 'Course name is required';
    if (empty($program)) $errors[] = 'Program is required';
    if (empty($level)) $errors[] = 'Level is required';
    if ($year_of_study < 1 || $year_of_study > 8) $errors[] = 'Year of study must be between 1-8';
    if (empty($phone_number)) $errors[] = 'Phone number is required';
    if (empty($address)) $errors[] = 'Address is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';

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
        $update_stmt = $con->prepare("UPDATE students SET full_name = ?, reg_number = ?, gender = ?, college_name = ?, department = ?, course_name = ?, program = ?, level = ?, year_of_study = ?, phone_number = ?, address = ?, email = ? WHERE student_id = ?");
        $update_stmt->bind_param("ssssssssisssi", $full_name, $reg_number, $gender, $college_name, $department, $course_name, $program, $level, $year_of_study, $phone_number, $address, $email, $student_id);
        
        if ($update_stmt->execute()) {
            $success = 'Profile updated successfully!';
            // Refresh student data
            $stmt = $con->prepare("SELECT * FROM students WHERE student_id = ?");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $student = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        } else {
            $errors[] = 'Failed to update profile. Please try again.';
        }
        $update_stmt->close();
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

        <!-- Profile Form -->
        <form method="POST" action="" class="bg-white shadow-lg rounded-lg p-6">
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
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="reg_number" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-id-card mr-1 text-primary"></i>Registration Number
                        </label>
                        <input type="text" id="reg_number" name="reg_number" required
                               value="<?php echo htmlspecialchars($student['reg_number'] ?? ''); ?>"
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
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-building mr-1 text-primary"></i>Department
                        </label>
                        <input type="text" id="department" name="department" required
                               value="<?php echo htmlspecialchars($student['department'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="course_name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-book mr-1 text-primary"></i>Course Name
                        </label>
                        <input type="text" id="course_name" name="course_name" required
                               value="<?php echo htmlspecialchars($student['course_name'] ?? ''); ?>"
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
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-phone mr-1 text-primary"></i>Phone Number
                        </label>
                        <input type="tel" id="phone_number" name="phone_number" required
                               value="<?php echo htmlspecialchars($student['phone_number'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt mr-1 text-primary"></i>Address
                        </label>
                        <textarea id="address" name="address" rows="3" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                                  placeholder="Enter your full address"><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
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
                    <div class="space-y-4">
                        <div>
                            <label for="academic_transcript" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-file-alt mr-1 text-primary"></i>Academic Transcript
                            </label>
                            <input type="file" id="academic_transcript" name="academic_transcript" accept=".pdf,.doc,.docx"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-secondary file:text-white hover:file:bg-accent">
                        </div>

                        <div>
                            <label for="id_document" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-id-card mr-1 text-primary"></i>National ID / Passport
                            </label>
                            <input type="file" id="id_document" name="id_document" accept=".pdf,.jpg,.jpeg,.png"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-secondary file:text-white hover:file:bg-accent">
                        </div>

                        <div>
                            <label for="cv_document" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-file-user mr-1 text-primary"></i>Curriculum Vitae (CV)
                            </label>
                            <input type="file" id="cv_document" name="cv_document" accept=".pdf,.doc,.docx"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-secondary file:text-white hover:file:bg-accent">
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
        });
    </script>
</body>
</html>
