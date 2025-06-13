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

// Create documents table if it doesn't exist
$con->query("CREATE TABLE IF NOT EXISTS student_documents (
    document_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    document_type ENUM('application_letter', 'resume', 'cover_letter', 'report', 'certificate', 'other') NOT NULL,
    document_title VARCHAR(255) NOT NULL,
    document_description TEXT,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size INT(11) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    is_generated BOOLEAN DEFAULT FALSE,
    status ENUM('draft', 'final', 'submitted', 'approved') DEFAULT 'draft',
    generated_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
)");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'upload_document') {
        $document_type = $_POST['document_type'] ?? 'other';
        $document_title = trim($_POST['document_title'] ?? '');
        $document_description = trim($_POST['document_description'] ?? '');
        
        // Validation
        if (empty($document_title)) $errors[] = 'Document title is required';
        if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Please select a file to upload';
        }
        
        // Handle file upload
        if (empty($errors) && isset($_FILES['document_file'])) {
            $upload_dir = 'uploads/documents/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['document_file']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $file_size = $_FILES['document_file']['size'];
                if ($file_size <= 10 * 1024 * 1024) { // 10MB limit
                    $filename = 'doc_' . $student_id . '_' . time() . '.' . $file_extension;
                    $file_path = $upload_dir . $filename;
                    
                    if (move_uploaded_file($_FILES['document_file']['tmp_name'], $file_path)) {
                        // Insert document record
                        $stmt = $con->prepare("INSERT INTO student_documents 
                            (student_id, document_type, document_title, document_description, 
                             file_path, file_name, file_size, file_type) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("isssssii", $student_id, $document_type, $document_title, 
                            $document_description, $file_path, $_FILES['document_file']['name'], 
                            $file_size, $file_extension);
                        
                        if ($stmt->execute()) {
                            $success = 'Document uploaded successfully!';
                        } else {
                            $errors[] = 'Failed to save document record.';
                        }
                        $stmt->close();
                    } else {
                        $errors[] = 'Failed to upload file.';
                    }
                } else {
                    $errors[] = 'File size must be less than 10MB.';
                }
            } else {
                $errors[] = 'Invalid file type. Allowed: PDF, DOC, DOCX, JPG, JPEG, PNG, TXT';
            }
        }
    }
    
    if ($action === 'delete_document') {
        $document_id = (int)($_POST['document_id'] ?? 0);
        if ($document_id > 0) {
            // Get document info first
            $stmt = $con->prepare("SELECT file_path FROM student_documents WHERE document_id = ? AND student_id = ?");
            $stmt->bind_param("ii", $document_id, $student_id);
            $stmt->execute();
            $document = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($document) {
                // Delete database record
                $stmt = $con->prepare("DELETE FROM student_documents WHERE document_id = ? AND student_id = ?");
                $stmt->bind_param("ii", $document_id, $student_id);
                if ($stmt->execute()) {
                    // Delete physical file
                    if (file_exists($document['file_path'])) {
                        unlink($document['file_path']);
                    }
                    $success = 'Document deleted successfully!';
                } else {
                    $errors[] = 'Failed to delete document.';
                }
                $stmt->close();
            }
        }
    }
    
    if ($action === 'generate_application_letter') {
        // Get student information
        $stmt = $con->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        // Get latest application
        $app_stmt = $con->prepare("SELECT * FROM applications WHERE student_id = ? ORDER BY created_at DESC LIMIT 1");
        $app_stmt->bind_param("i", $student_id);
        $app_stmt->execute();
        $application = $app_stmt->get_result()->fetch_assoc();
        $app_stmt->close();
        
        if ($student && $application) {
            // Generate application letter content
            $letter_content = generateApplicationLetter($student, $application);
            
            // Save as document
            $upload_dir = 'uploads/documents/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $filename = 'application_letter_' . $student_id . '_' . time() . '.txt';
            $file_path = $upload_dir . $filename;
            
            if (file_put_contents($file_path, $letter_content)) {
                $stmt = $con->prepare("INSERT INTO student_documents 
                    (student_id, document_type, document_title, document_description, 
                     file_path, file_name, file_size, file_type, is_generated, generated_at) 
                    VALUES (?, 'application_letter', ?, ?, ?, ?, ?, 'txt', 1, CURRENT_TIMESTAMP)");
                $title = 'Application Letter - ' . $application['company_name'];
                $description = 'Auto-generated application letter for ' . $application['company_name'];
                $file_size = filesize($file_path);
                $stmt->bind_param("isssii", $student_id, $title, $description, 
                    $file_path, $filename, $file_size);
                
                if ($stmt->execute()) {
                    $success = 'Application letter generated successfully!';
                } else {
                    $errors[] = 'Failed to save generated document.';
                }
                $stmt->close();
            } else {
                $errors[] = 'Failed to generate application letter file.';
            }
        } else {
            $errors[] = 'Please complete your profile and submit an application first.';
        }
    }
}

// Function to generate application letter
function generateApplicationLetter($student, $application) {
    $date = date('F d, Y');
    
    $letter = <<<EOL
{$student['full_name']}
{$student['address']}
{$student['phone_number']}
{$student['email']}

$date

Dear Hiring Manager,
{$application['company_name']}
{$application['company_location']}

Subject: Application for Industrial Training Placement

Dear Sir/Madam,

I am writing to express my interest in undertaking my industrial training at {$application['company_name']}. I am currently a {$student['year_of_study']} year student pursuing {$student['course_name']} in the Department of {$student['department']} at {$student['college_name']}.

As part of my academic requirements, I am required to undertake industrial training for {$application['training_duration']} weeks, from {$application['start_date']} to {$application['end_date']}. I am particularly interested in the {$application['position_title']} position as it aligns with my career aspirations and academic background.

Through this training opportunity, I hope to:
• Gain practical experience in {$application['training_area']}
• Develop professional skills in a real work environment
• Apply theoretical knowledge gained in my studies
• Contribute meaningfully to your organization's objectives

{$application['motivation_letter']}

I am a dedicated and enthusiastic student with a strong academic record. I am eager to learn and contribute to your team while gaining valuable industry experience. I am available for an interview at your convenience and can provide any additional documentation you may require.

Thank you for considering my application. I look forward to hearing from you soon.

Yours sincerely,

{$student['full_name']}
Registration Number: {$student['reg_number']}
EOL;
    
    return $letter;
}

// Get all documents for this student
$documents_stmt = $con->prepare("SELECT * FROM student_documents WHERE student_id = ? ORDER BY created_at DESC");
$documents_stmt->bind_param("i", $student_id);
$documents_stmt->execute();
$documents = $documents_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$documents_stmt->close();

// Get document statistics
$stats_stmt = $con->prepare("SELECT 
    COUNT(*) as total_documents,
    SUM(file_size) as total_size,
    COUNT(CASE WHEN document_type = 'application_letter' THEN 1 END) as application_letters,
    COUNT(CASE WHEN document_type = 'resume' THEN 1 END) as resumes,
    COUNT(CASE WHEN document_type = 'report' THEN 1 END) as reports,
    COUNT(CASE WHEN is_generated = 1 THEN 1 END) as generated_docs
    FROM student_documents WHERE student_id = ?");
$stats_stmt->bind_param("i", $student_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
$stats_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Management - IPT System</title>
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
                    <h1 class="text-xl font-bold">
                        <i class="fas fa-graduation-cap mr-2"></i>IPT System
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm">Welcome, <?php echo htmlspecialchars($student_name); ?></span>
                    <a href="student_dashboard.php" class="px-3 py-2 rounded-md text-sm font-medium bg-secondary hover:bg-accent transition-colors">
                        <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                    </a>
                    <a href="student_logout.php" class="px-3 py-2 rounded-md text-sm font-medium bg-red-600 hover:bg-red-700 transition-colors">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Document Management</h1>
            <p class="text-gray-600 mt-2">Upload, generate, and manage your training documents</p>
        </div>

        <!-- Document Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file text-primary text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Documents</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo $stats['total_documents']; ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-hdd text-primary text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Storage Used</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo round(($stats['total_size'] ?? 0) / 1024 / 1024, 2); ?> MB</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-alt text-primary text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Reports</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo $stats['reports']; ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-magic text-primary text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Generated</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo $stats['generated_docs']; ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="mb-6">
            <nav class="flex space-x-8">
                <a href="#upload" id="tab-upload" class="tab-link border-primary text-primary border-b-2 py-2 px-1 text-sm font-medium">
                    <i class="fas fa-upload mr-2"></i>Upload Documents
                </a>
                <a href="#generate" id="tab-generate" class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-2 px-1 text-sm font-medium">
                    <i class="fas fa-magic mr-2"></i>Generate Documents
                </a>
                <a href="#manage" id="tab-manage" class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-2 px-1 text-sm font-medium">
                    <i class="fas fa-folder-open mr-2"></i>Manage Documents
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

        <!-- Upload Documents Tab -->
        <div id="content-upload" class="tab-content">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Upload New Document</h3>
                
                <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="upload_document">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="document_type" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-tag mr-1 text-primary"></i>Document Type
                            </label>
                            <select id="document_type" name="document_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="resume">Resume/CV</option>
                                <option value="cover_letter">Cover Letter</option>
                                <option value="application_letter">Application Letter</option>
                                <option value="report">Training Report</option>
                                <option value="certificate">Certificate</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="document_title" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-heading mr-1 text-primary"></i>Document Title
                            </label>
                            <input type="text" id="document_title" name="document_title" required
                                   placeholder="e.g., My Resume 2024"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <div>
                        <label for="document_description" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-align-left mr-1 text-primary"></i>Description (Optional)
                        </label>
                        <textarea id="document_description" name="document_description" rows="3"
                                  placeholder="Brief description of the document..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                    </div>

                    <div>
                        <label for="document_file" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-file-upload mr-1 text-primary"></i>Select File
                        </label>
                        <input type="file" id="document_file" name="document_file" required
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        <p class="text-sm text-gray-500 mt-1">Supported formats: PDF, DOC, DOCX, JPG, JPEG, PNG, TXT (Max: 10MB)</p>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="px-6 py-3 bg-primary text-white rounded-md hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-primary transition-colors">
                            <i class="fas fa-upload mr-2"></i>Upload Document
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Generate Documents Tab -->
        <div id="content-generate" class="tab-content hidden">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Generate Documents</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="text-center">
                            <i class="fas fa-file-alt text-primary text-4xl mb-4"></i>
                            <h4 class="text-lg font-medium text-gray-900 mb-2">Application Letter</h4>
                            <p class="text-sm text-gray-600 mb-4">Generate a professional application letter based on your profile and latest application.</p>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="generate_application_letter">
                                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-primary transition-colors">
                                    <i class="fas fa-magic mr-2"></i>Generate Letter
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg p-4 opacity-50">
                        <div class="text-center">
                            <i class="fas fa-certificate text-gray-400 text-4xl mb-4"></i>
                            <h4 class="text-lg font-medium text-gray-500 mb-2">Training Certificate</h4>
                            <p class="text-sm text-gray-500 mb-4">Generate training completion certificate (Available after training completion).</p>
                            <button disabled class="px-4 py-2 bg-gray-300 text-gray-500 rounded-md cursor-not-allowed">
                                <i class="fas fa-lock mr-2"></i>Coming Soon
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manage Documents Tab -->
        <div id="content-manage" class="tab-content hidden">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">My Documents</h3>
                
                <?php if (empty($documents)): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-folder-open text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-500">No documents uploaded yet. Upload your first document above.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($documents as $document): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-file-<?php echo $document['file_type'] === 'pdf' ? 'pdf' : ($document['file_type'] === 'doc' || $document['file_type'] === 'docx' ? 'word' : 'alt'); ?> text-primary text-xl"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($document['document_title']); ?></div>
                                                    <?php if ($document['document_description']): ?>
                                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars(substr($document['document_description'], 0, 50)) . '...'; ?></div>
                                                    <?php endif; ?>
                                                    <?php if ($document['is_generated']): ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                            <i class="fas fa-magic mr-1"></i>Generated
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo ucfirst(str_replace('_', ' ', $document['document_type'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo round($document['file_size'] / 1024, 2); ?> KB
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($document['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <a href="<?php echo htmlspecialchars($document['file_path']); ?>" target="_blank" 
                                               class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-eye mr-1"></i>View
                                            </a>
                                            <a href="<?php echo htmlspecialchars($document['file_path']); ?>" download="<?php echo htmlspecialchars($document['file_name']); ?>" 
                                               class="text-green-600 hover:text-green-900">
                                                <i class="fas fa-download mr-1"></i>Download
                                            </a>
                                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this document?');">
                                                <input type="hidden" name="action" value="delete_document">
                                                <input type="hidden" name="document_id" value="<?php echo $document['document_id']; ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash mr-1"></i>Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active classes
                    tabLinks.forEach(l => {
                        l.classList.remove('border-primary', 'text-primary');
                        l.classList.add('border-transparent', 'text-gray-500');
                    });
                    
                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                    });
                    
                    // Add active classes
                    this.classList.remove('border-transparent', 'text-gray-500');
                    this.classList.add('border-primary', 'text-primary');
                    
                    // Show corresponding content
                    const targetId = this.getAttribute('href').substring(1);
                    const targetContent = document.getElementById('content-' + targetId);
                    if (targetContent) {
                        targetContent.classList.remove('hidden');
                    }
                });
            });
        });
    </script>
</body>
</html>
