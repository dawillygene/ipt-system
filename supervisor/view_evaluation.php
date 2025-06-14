<?php
session_start();
require_once 'includes/supervisor_db.php';

checkSupervisorSession();

$supervisor_id = $_SESSION['supervisor_id'];
$evaluation_id = (int)$_GET['id'];

// Get evaluation details
$stmt = $con->prepare("
    SELECT se.*, s.first_name, s.last_name, s.student_id as student_number
    FROM student_evaluations se
    JOIN students s ON se.student_id = s.student_id
    WHERE se.evaluation_id = ? AND se.supervisor_id = ?
");
$stmt->bind_param("ii", $evaluation_id, $supervisor_id);
$stmt->execute();
$evaluation = $stmt->get_result()->fetch_assoc();

if (!$evaluation) {
    echo '<div class="p-4 text-center"><p class="text-red-600">Evaluation not found or access denied.</p></div>';
    exit;
}

function getGradeColor($grade) {
    switch($grade) {
        case 'A': return 'bg-green-100 text-green-800';
        case 'B': return 'bg-blue-100 text-blue-800';
        case 'C': return 'bg-yellow-100 text-yellow-800';
        case 'D': return 'bg-orange-100 text-orange-800';
        case 'F': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function getScoreColor($score) {
    if ($score >= 90) return 'text-green-600';
    if ($score >= 80) return 'text-blue-600';
    if ($score >= 70) return 'text-yellow-600';
    if ($score >= 60) return 'text-orange-600';
    return 'text-red-600';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'supervisor-primary': '#07442d',
                        'supervisor-secondary': '#206f56',
                        'supervisor-accent': '#0f7b5a',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Student Evaluation</h1>
                <p class="text-gray-600">
                    <?php echo htmlspecialchars($evaluation['first_name'] . ' ' . $evaluation['last_name']); ?> 
                    (ID: <?php echo htmlspecialchars($evaluation['student_number']); ?>)
                </p>
            </div>
            <button onclick="window.close()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="p-6">
            <!-- Evaluation Info -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-700">Evaluation Type</h3>
                    <p class="text-lg font-semibold text-gray-900 capitalize">
                        <?php echo str_replace('_', ' ', htmlspecialchars($evaluation['evaluation_type'])); ?>
                    </p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-700">Period</h3>
                    <p class="text-lg font-semibold text-gray-900">
                        <?php echo htmlspecialchars($evaluation['evaluation_period']); ?>
                    </p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-700">Date</h3>
                    <p class="text-lg font-semibold text-gray-900">
                        <?php echo date('M d, Y', strtotime($evaluation['evaluation_date'])); ?>
                    </p>
                </div>
            </div>

            <!-- Overall Grade -->
            <div class="mb-8 text-center">
                <h3 class="text-lg font-medium text-gray-700 mb-2">Overall Grade</h3>
                <span class="inline-block px-6 py-3 text-3xl font-bold rounded-full <?php echo getGradeColor($evaluation['overall_grade']); ?>">
                    <?php echo htmlspecialchars($evaluation['overall_grade']); ?>
                </span>
            </div>

            <!-- Performance Scores -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Scores</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php 
                    $scores = [
                        'Technical Skills' => $evaluation['technical_skills_score'],
                        'Communication' => $evaluation['communication_score'],
                        'Teamwork' => $evaluation['teamwork_score'],
                        'Punctuality' => $evaluation['punctuality_score'],
                        'Initiative' => $evaluation['initiative_score']
                    ];
                    
                    foreach ($scores as $skill => $score): 
                        if ($score !== null):
                    ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-700"><?php echo $skill; ?></h4>
                            <div class="flex items-center mt-2">
                                <span class="text-2xl font-bold <?php echo getScoreColor($score); ?>">
                                    <?php echo $score; ?>
                                </span>
                                <span class="text-gray-500 ml-1">/100</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                <div class="h-2 rounded-full <?php echo $score >= 70 ? 'bg-green-500' : ($score >= 50 ? 'bg-yellow-500' : 'bg-red-500'); ?>" 
                                     style="width: <?php echo $score; ?>%"></div>
                            </div>
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <?php if ($evaluation['strengths']): ?>
                    <div>
                        <h4 class="text-lg font-semibold text-green-700 mb-3">
                            <i class="fas fa-thumbs-up mr-2"></i>Strengths
                        </h4>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($evaluation['strengths'])); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($evaluation['areas_for_improvement']): ?>
                    <div>
                        <h4 class="text-lg font-semibold text-orange-700 mb-3">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Areas for Improvement
                        </h4>
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($evaluation['areas_for_improvement'])); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($evaluation['recommendations']): ?>
                    <div>
                        <h4 class="text-lg font-semibold text-blue-700 mb-3">
                            <i class="fas fa-lightbulb mr-2"></i>Recommendations
                        </h4>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($evaluation['recommendations'])); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Status and Actions -->
            <div class="border-t border-gray-200 pt-6 flex justify-between items-center">
                <div>
                    <span class="text-sm text-gray-600">Status: </span>
                    <span class="px-3 py-1 text-sm rounded-full capitalize
                        <?php 
                        switch($evaluation['status']) {
                            case 'submitted': echo 'bg-green-100 text-green-800'; break;
                            case 'draft': echo 'bg-gray-100 text-gray-800'; break;
                            case 'approved': echo 'bg-blue-100 text-blue-800'; break;
                        }
                        ?>">
                        <?php echo htmlspecialchars($evaluation['status']); ?>
                    </span>
                </div>
                
                <div class="space-x-3">
                    <button onclick="window.print()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                        <i class="fas fa-print mr-2"></i>Print
                    </button>
                    <button onclick="window.close()" class="px-4 py-2 bg-supervisor-primary text-white rounded hover:bg-supervisor-secondary">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            button {
                display: none !important;
            }
            body {
                background: white !important;
                padding: 0 !important;
            }
        }
    </style>
</body>
</html>
