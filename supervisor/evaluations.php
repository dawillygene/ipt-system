<?php
session_start();
require_once 'includes/supervisor_db.php';

checkSupervisorSession();

$supervisor_id = $_SESSION['supervisor_id'];
// Use supervisor_name from session, consistent with other pages
$supervisor_name = $_SESSION['supervisor_name']; 
$supervisor = getSupervisorInfo($con, $supervisor_id); // Can be kept if other specific info is needed

$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_evaluation'])) {
        $student_id = (int)$_POST['student_id'];
        $evaluation_type = $_POST['evaluation_type'];
        $evaluation_period = trim($_POST['evaluation_period']);
        $technical_skills = (int)$_POST['technical_skills'];
        $communication = (int)$_POST['communication'];
        $teamwork = (int)$_POST['teamwork'];
        $punctuality = (int)$_POST['punctuality'];
        $initiative = (int)$_POST['initiative'];
        $overall_grade = $_POST['overall_grade'];
        $strengths = trim($_POST['strengths']);
        $improvements = trim($_POST['improvements']);
        $recommendations = trim($_POST['recommendations']);
        $evaluation_date = $_POST['evaluation_date'];
        
        // Validation
        if (empty($evaluation_period) || empty($evaluation_date)) {
            $error = 'Evaluation period and date are required.';
        } else {
            // Create evaluation
            $stmt = $con->prepare("
                INSERT INTO student_evaluations 
                (student_id, supervisor_id, evaluation_type, evaluation_period, technical_skills_score, 
                 communication_score, teamwork_score, punctuality_score, initiative_score, overall_grade,
                 strengths, areas_for_improvement, recommendations, evaluation_date, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted')
            ");
            $stmt->bind_param("iissiiiissss", $student_id, $supervisor_id, $evaluation_type, $evaluation_period, 
                            $technical_skills, $communication, $teamwork, $punctuality, $initiative, 
                            $overall_grade, $strengths, $improvements, $recommendations, $evaluation_date);
            
            if ($stmt->execute()) {
                $success = 'Evaluation created successfully!';
            } else {
                $error = 'Failed to create evaluation. Please try again.';
            }
            $stmt->close(); // Close statement
        }
    }
}

// Get assigned students
$assigned_students = getAssignedStudents($con, $supervisor_id);

// Get existing evaluations
$stmt = $con->prepare("
    SELECT se.*, s.full_name as student_name, s.student_id as student_number
    FROM student_evaluations se
    JOIN students s ON se.student_id = s.student_id
    WHERE se.supervisor_id = ?
    ORDER BY se.evaluation_date DESC
");
$stmt->bind_param("i", $supervisor_id);
$stmt->execute();
$evaluations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close(); // Close statement

// Get evaluation to edit if specified
$edit_evaluation = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $con->prepare("
        SELECT se.*, s.full_name as student_name 
        FROM student_evaluations se
        JOIN students s ON se.student_id = s.student_id
        WHERE se.evaluation_id = ? AND se.supervisor_id = ?
    "); // Corrected to use s.full_name
    $stmt->bind_param("ii", $edit_id, $supervisor_id);
    $stmt->execute();
    $edit_evaluation = $stmt->get_result()->fetch_assoc();
    $stmt->close(); // Close statement
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Evaluations - IPT Supervisor Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#07442d', // Standardized
                        'secondary': '#206f56', // Standardized
                        'accent': '#0f7b5a', // Standardized
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
                        <i class="fas fa-chalkboard-teacher mr-2"></i>IPT Supervisor Portal
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm">Welcome, <?php echo htmlspecialchars($supervisor_name); ?></span>
                    <div class="relative">
                        <button id="menu-button" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-primary focus:ring-white">
                            <i class="fas fa-user-circle text-2xl"></i>
                        </button>
                        <div id="dropdown-menu" class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>Profile
                                </a>
                                <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-2"></i>Settings
                                </a>
                                <hr class="my-1">
                                <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Layout -->
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg min-h-screen">
            <div class="p-4">
                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-tachometer-alt mr-3"></i>
                        Dashboard
                    </a>
                    <a href="students.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-users mr-3"></i>
                        My Students
                    </a>
                    <a href="reports.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-file-alt mr-3"></i>
                        Review Reports
                    </a>
                    <a href="evaluations.php" class="flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-lg"> {/* Active link style */}
                        <i class="fas fa-star mr-3"></i> {/* Changed icon for consistency */}
                        Evaluations
                    </a>
                    <a href="meetings.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-calendar mr-3"></i>
                        Meetings
                    </a>
                    <a href="messages.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-comments mr-3"></i>
                        Messages
                    </a>
                    <hr class="my-2">
                    <a href="profile.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-user-cog mr-3"></i>
                        My Profile
                    </a>
                    <a href="settings.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-cogs mr-3"></i>
                        Settings
                    </a>
                    <hr class="my-2">
                    <a href="logout.php" class="flex items-center px-4 py-2 text-red-600 hover:bg-red-100 rounded-lg">
                        <i class="fas fa-sign-out-alt mr-3"></i>
                        Logout
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6"> {/* Standardized padding */}
            <div class="container mx-auto"> {/* Standardized container */}
                <!-- Page Header -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-primary">Student Evaluations</h1>
                    <button onclick="toggleEvaluationForm()" class="bg-primary hover:bg-secondary text-white px-6 py-2 rounded-lg transition">
                        <i class="fas fa-plus mr-2"></i>New Evaluation
                    </button>
                </div>

                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                     <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong class="font-bold">Success!</strong>
                        <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Evaluation Form -->
                <div id="evaluation-form" class="bg-white shadow-lg rounded-lg mb-8" style="display: <?php echo ($edit_evaluation || !empty($error) && isset($_POST['create_evaluation'])) ? 'block' : 'none'; ?>;">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900"><?php echo $edit_evaluation ? 'Edit' : 'Create'; ?> Student Evaluation</h2>
                    </div>
                    
                    <form method="POST" class="p-6">
                        <input type="hidden" name="create_evaluation" value="1">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Select Student *</label>
                                <select name="student_id" id="student_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary">
                                    <option value="">Choose a student...</option>
                                    <?php foreach ($assigned_students as $student): ?>
                                        <option value="<?php echo $student['student_id']; ?>" <?php echo ($edit_evaluation && $edit_evaluation['student_id'] == $student['student_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label for="evaluation_type" class="block text-sm font-medium text-gray-700 mb-2">Evaluation Type *</label>
                                <select name="evaluation_type" id="evaluation_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary">
                                    <option value="mid_term" <?php echo ($edit_evaluation && $edit_evaluation['evaluation_type'] == 'mid_term') ? 'selected' : ''; ?>>Mid-term</option>
                                    <option value="final" <?php echo ($edit_evaluation && $edit_evaluation['evaluation_type'] == 'final') ? 'selected' : ''; ?>>Final</option>
                                    <option value="monthly" <?php echo ($edit_evaluation && $edit_evaluation['evaluation_type'] == 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                                    <option value="custom" <?php echo ($edit_evaluation && $edit_evaluation['evaluation_type'] == 'custom') ? 'selected' : ''; ?>>Custom</option>
                                </select>
                            </div>

                            <div>
                                <label for="evaluation_period" class="block text-sm font-medium text-gray-700 mb-2">Evaluation Period *</label>
                                <input type="text" name="evaluation_period" id="evaluation_period" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary"
                                       placeholder="e.g., Semester 1 2024" value="<?php echo $edit_evaluation ? htmlspecialchars($edit_evaluation['evaluation_period']) : ''; ?>">
                            </div>

                            <div>
                                <label for="evaluation_date" class="block text-sm font-medium text-gray-700 mb-2">Evaluation Date *</label>
                                <input type="date" name="evaluation_date" id="evaluation_date" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary"
                                       value="<?php echo $edit_evaluation ? date('Y-m-d', strtotime($edit_evaluation['evaluation_date'])) : date('Y-m-d'); ?>">
                            </div>
                        </div>

                        <!-- Performance Scores -->
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Scores (0-100)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div>
                                    <label for="technical_skills" class="block text-sm font-medium text-gray-700 mb-2">Technical Skills</label>
                                    <input type="number" name="technical_skills" id="technical_skills" min="0" max="100"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary"
                                           value="<?php echo $edit_evaluation ? htmlspecialchars($edit_evaluation['technical_skills_score']) : ''; ?>">
                                </div>

                                <div>
                                    <label for="communication" class="block text-sm font-medium text-gray-700 mb-2">Communication</label>
                                    <input type="number" name="communication" id="communication" min="0" max="100"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary"
                                           value="<?php echo $edit_evaluation ? htmlspecialchars($edit_evaluation['communication_score']) : ''; ?>">
                                </div>

                                <div>
                                    <label for="teamwork" class="block text-sm font-medium text-gray-700 mb-2">Teamwork</label>
                                    <input type="number" name="teamwork" id="teamwork" min="0" max="100"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary"
                                           value="<?php echo $edit_evaluation ? htmlspecialchars($edit_evaluation['teamwork_score']) : ''; ?>">
                                </div>

                                <div>
                                    <label for="punctuality" class="block text-sm font-medium text-gray-700 mb-2">Punctuality</label>
                                    <input type="number" name="punctuality" id="punctuality" min="0" max="100"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary"
                                           value="<?php echo $edit_evaluation ? htmlspecialchars($edit_evaluation['punctuality_score']) : ''; ?>">
                                </div>

                                <div>
                                    <label for="initiative" class="block text-sm font-medium text-gray-700 mb-2">Initiative</label>
                                    <input type="number" name="initiative" id="initiative" min="0" max="100"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary"
                                           value="<?php echo $edit_evaluation ? htmlspecialchars($edit_evaluation['initiative_score']) : ''; ?>">
                                </div>

                                <div>
                                    <label for="overall_grade" class="block text-sm font-medium text-gray-700 mb-2">Overall Grade</label>
                                    <select name="overall_grade" id="overall_grade"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary">
                                        <option value="A" <?php echo ($edit_evaluation && $edit_evaluation['overall_grade'] == 'A') ? 'selected' : ''; ?>>A - Excellent</option>
                                        <option value="B" <?php echo ($edit_evaluation && $edit_evaluation['overall_grade'] == 'B') ? 'selected' : ''; ?>>B - Good</option>
                                        <option value="C" <?php echo ($edit_evaluation && $edit_evaluation['overall_grade'] == 'C') ? 'selected' : ''; ?>>C - Satisfactory</option>
                                        <option value="D" <?php echo ($edit_evaluation && $edit_evaluation['overall_grade'] == 'D') ? 'selected' : ''; ?>>D - Needs Improvement</option>
                                        <option value="F" <?php echo ($edit_evaluation && $edit_evaluation['overall_grade'] == 'F') ? 'selected' : ''; ?>>F - Unsatisfactory</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Comments Section -->
                        <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div>
                                <label for="strengths" class="block text-sm font-medium text-gray-700 mb-2">Strengths</label>
                                <textarea name="strengths" id="strengths" rows="4"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary"
                                          placeholder="List the student's key strengths..."><?php echo $edit_evaluation ? htmlspecialchars($edit_evaluation['strengths']) : ''; ?></textarea>
                            </div>

                            <div>
                                <label for="improvements" class="block text-sm font-medium text-gray-700 mb-2">Areas for Improvement</label>
                                <textarea name="improvements" id="improvements" rows="4"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary"
                                          placeholder="Areas where the student can improve..."><?php echo $edit_evaluation ? htmlspecialchars($edit_evaluation['areas_for_improvement']) : ''; ?></textarea>
                            </div>

                            <div>
                                <label for="recommendations" class="block text-sm font-medium text-gray-700 mb-2">Recommendations</label>
                                <textarea name="recommendations" id="recommendations" rows="4"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-supervisor-primary"
                                          placeholder="Your recommendations for the student..."><?php echo $edit_evaluation ? htmlspecialchars($edit_evaluation['recommendations']) : ''; ?></textarea>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end space-x-4">
                            <button type="button" onclick="toggleEvaluationForm()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-2 bg-supervisor-primary text-white rounded-lg hover:bg-supervisor-secondary transition">
                                <i class="fas fa-save mr-2"></i><?php echo $edit_evaluation ? 'Update' : 'Create'; ?> Evaluation
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Evaluations List -->
                <div class="bg-white shadow-lg rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Evaluation History</h2>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overall Grade</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($evaluations)): ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                            <i class="fas fa-clipboard-check text-4xl mb-4"></i>
                                            <p>No evaluations created yet. Create your first evaluation above.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($evaluations as $evaluation): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($evaluation['student_name']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    ID: <?php echo htmlspecialchars($evaluation['student_number']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 capitalize">
                                                    <?php echo str_replace('_', ' ', htmlspecialchars($evaluation['evaluation_type'])); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo htmlspecialchars($evaluation['evaluation_period']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-sm rounded-full 
                                                    <?php 
                                                    switch($evaluation['overall_grade']) {
                                                        case 'A': echo 'bg-green-100 text-green-800'; break;
                                                        case 'B': echo 'bg-blue-100 text-blue-800'; break;
                                                        case 'C': echo 'bg-yellow-100 text-yellow-800'; break;
                                                        case 'D': echo 'bg-orange-100 text-orange-800'; break;
                                                        case 'F': echo 'bg-red-100 text-red-800'; break;
                                                    }
                                                    ?>">
                                                    <?php echo htmlspecialchars($evaluation['overall_grade']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y', strtotime($evaluation['evaluation_date'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs rounded-full capitalize
                                                    <?php 
                                                    switch($evaluation['status']) {
                                                        case 'submitted': echo 'bg-green-100 text-green-800'; break;
                                                        case 'draft': echo 'bg-gray-100 text-gray-800'; break;
                                                        case 'approved': echo 'bg-blue-100 text-blue-800'; break;
                                                    }
                                                    ?>">
                                                    <?php echo htmlspecialchars($evaluation['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button onclick="viewEvaluation(<?php echo $evaluation['evaluation_id']; ?>)" 
                                                        class="text-supervisor-primary hover:text-supervisor-secondary mr-3">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($evaluation['status'] == 'draft'): ?>
                                                    <a href="?edit=<?php echo $evaluation['evaluation_id']; ?>" 
                                                       class="text-blue-600 hover:text-blue-900 mr-3">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleEvaluationForm() {
            const form = document.getElementById('evaluation-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function viewEvaluation(evaluationId) {
            // Create modal or redirect to view page
            window.open(`view_evaluation.php?id=${evaluationId}`, '_blank', 'width=800,height=600');
        }

        // Auto-calculate average for overall grade suggestion
        function calculateAverage() {
            const scores = ['technical_skills', 'communication', 'teamwork', 'punctuality', 'initiative'];
            let total = 0;
            let count = 0;
            
            scores.forEach(function(scoreId) {
                const value = document.getElementById(scoreId).value;
                if (value && !isNaN(value)) {
                    total += parseInt(value);
                    count++;
                }
            });
            
            if (count > 0) {
                const average = total / count;
                const gradeSelect = document.getElementById('overall_grade');
                
                if (average >= 90) gradeSelect.value = 'A';
                else if (average >= 80) gradeSelect.value = 'B';
                else if (average >= 70) gradeSelect.value = 'C';
                else if (average >= 60) gradeSelect.value = 'D';
                else gradeSelect.value = 'F';
            }
        }

        // Add event listeners to score inputs
        ['technical_skills', 'communication', 'teamwork', 'punctuality', 'initiative'].forEach(function(id) {
            document.getElementById(id).addEventListener('input', calculateAverage);
        });
    </script>
</body>
</html>
