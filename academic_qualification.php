<?php
include('db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = ""; // Initialize the message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form field values with proper error handling to avoid undefined array key warnings
    $qualification = isset($_POST['qualification']) ? mysqli_real_escape_string($con, $_POST['qualification']) : '';
    $institution = isset($_POST['institution']) ? mysqli_real_escape_string($con, $_POST['institution']) : '';
    $year_of_completion = isset($_POST['year_of_completion']) ? mysqli_real_escape_string($con, $_POST['year_of_completion']) : '';
    $position = isset($_POST['position']) ? mysqli_real_escape_string($con, $_POST['position']) : '';
    $experience_national_exam = isset($_POST['experience_national_exam']) ? mysqli_real_escape_string($con, $_POST['experience_national_exam']) : '';
    $experience_work = isset($_POST['experience_work']) ? mysqli_real_escape_string($con, $_POST['experience_work']) : '';
    $level_of_education = isset($_POST['level_of_education']) ? mysqli_real_escape_string($con, $_POST['level_of_education']) : '';
    $level_teach = isset($_POST['level_teach']) ? mysqli_real_escape_string($con, $_POST['level_teach']) : '';
    $subject_teach = isset($_POST['subject_teach']) ? mysqli_real_escape_string($con, $_POST['subject_teach']) : '';
    $school_teach = isset($_POST['school_teach']) ? mysqli_real_escape_string($con, $_POST['school_teach']) : '';
    $subject_study = isset($_POST['subject_study']) ? mysqli_real_escape_string($con, $_POST['subject_study']) : '';

    // Insert data into the database
    $sql = "INSERT INTO academic_qualification (user_id, qualification, institution, year_of_completion, position, experience_national_exam, experience_work, level_of_education, level_teach, subject_teach, school_teach, subject_study) 
            VALUES ('{$_SESSION['user_id']}', '$qualification', '$institution', '$year_of_completion', '$position', '$experience_national_exam', '$experience_work', '$level_of_education', '$level_teach', '$subject_teach', '$school_teach', '$subject_study')";

    if (mysqli_query($con, $sql)) {
        $message = "Academic qualification added successfully!";
    } else {
        $message = "Error: " . $sql . "<br>" . mysqli_error($con);
    }

    mysqli_close($con);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPT - Academic Qualification</title>

        
    
    <link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
    <link href="css/font-awesome.css" rel="stylesheet"> 
    <link href="css/style.css" rel="stylesheet" type="text/css" media="all" />
    <link href="css/index.css" rel="stylesheet" type="text/css" media="all" />
    <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Federo" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900" rel="stylesheet">
</head>
<body>
    <?php include('./header.php'); ?>
        
    <hr />
    
    <main>
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <?php include('./sidebar.php'); ?>
                </div>
                
                <div class="col-md-9">
                    <h2 class="detail-header">Add Academic Qualification</h2>
                    <div class="form-inside">
                        <?php if (!empty($message)) { ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo $message; ?>
                        </div>
                        <div class="back-to-dashboard">
                            <a href="dashboard.php">Back to Dashboard</a>
                        </div>
                    <?php } else { ?>
                        <form action="academic_qualification.php" method="post">
                            <div class="form-group">
                                <label for="qualification">Qualification</label>
                                <input type="text" class="form-control" id="qualification" name="qualification" required>
                            </div>
                            <div class="form-group">
                                <label for="institution">Institution</label>
                                <input type="text" class="form-control" id="institution" name="institution" required>
                            </div>
                            <div class="form-group">
                                <label for="year_of_completion">Year of Completion</label>
                                <input type="text" class="form-control" id="year_of_completion" name="year_of_completion" required>
                            </div>
                            <div class="form-group">
                                <label for="position">Position</label>
                                <input type="text" class="form-control" id="position" name="position" required>
                            </div>
                            <div class="form-group">
                                <label for="experience_national_exam">Experience of National Examinations (Years)</label>
                                <input type="number" class="form-control" id="experience_national_exam" name="experience_national_exam" required>
                            </div>
                            <div class="form-group">
                                <label for="experience_work">Experience of Your Work (Years)</label>
                                <input type="number" class="form-control" id="experience_work" name="experience_work" required>
                            </div>
                            <div class="form-group">
                                <label for="level_of_education">Level of Education</label>
                                <input type="text" class="form-control" id="level_of_education" name="level_of_education" required>
                            </div>
                            <div class="form-group">
                                <label for="level_teach">Level You Teach</label>
                                <input type="text" class="form-control" id="level_teach" name="level_teach" required>
                            </div>
                            <div class="form-group">
                                <label for="subject_teach">Subject You Teach</label>
                                <input type="text" class="form-control" id="subject_teach" name="subject_teach" required>
                            </div>
                            <div class="form-group">
                                <label for="school_teach">School You Teach</label>
                                <input type="text" class="form-control" id="school_teach" name="school_teach" required>
                            </div>
                            <div class="form-group">
                                <label for="subject_study">Subject You Studied</label>
                                <input type="text" class="form-control" id="subject_study" name="subject_study" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </form>
                    <?php } ?>

                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include('./footer.php'); ?>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.min.js"></script>

        
    <!-- Arrow No Function -->
    <script>
        window.history.forward();
        function noBack() {
            window.history.forward();
        }
        setTimeout("noBack()", 0);
        window.onunload = function() {null};

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

    </script>
    <!-- End Arrow -->
    
</body>
</html>
