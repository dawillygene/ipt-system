<?php
include('db.php');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Function to fetch user data by user ID
function getUserData($userId, $con) {
    $userDataSql = "SELECT * FROM users WHERE id = '$userId'";
    $userDataResult = mysqli_query($con, $userDataSql);
    return mysqli_fetch_assoc($userDataResult);
}

// If an action (approve or reject) is submitted for a request, update the request status
if (isset($_POST['action']) && isset($_POST['request_id'])) {
    $action = $_POST['action'];
    $request_id = $_POST['request_id'];
    $status = $action === 'approve' ? 'approved' : 'rejected';

    $update_sql = "UPDATE user_requests SET status = '$status' WHERE id = '$request_id'";
    mysqli_query($con, $update_sql);
}

// Retrieve user requests from the database
//$applications_sql = "SELECT applications.*, users.name, users.email FROM user_requests INNER JOIN users ON user_requests.user_id = users.id";
//$applications_sql = "SELECT applications.* FROM applications INNER JOIN students ON students.user_id = students.id";
$applications_sql = "SELECT applications.* FROM applications";
$applications_result = mysqli_query($con, $applications_sql);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Add Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="./css/index.css" rel="stylesheet">
</head>
<body>
<div class="page-container">
    <?php include('./sidebar.php'); ?>

    <div class="topbar">
        <a href="admin_logout.php" class="admin-logout">Logout</a>
    </div>

    <div class="info">
        <div class="row">
            <div class="col-md-12">
                <h2 class="mt-4 section-title">Add Evaluation</h2>
                <a href="./admin_students.php" class="add-btn">
                    <i class="fas fa-plus fa-sm text-white-50"></i>Evaluations
                </a>
            </div>
        </div>
        <div class="data-container">
            <br />

            <form action="submit_evaluation.php" method="POST">
                <div class="mb-3">
                    <label for="student_id" class="form-label">Student</label>
                    <select class="form-select" name="student_id" required>
                        <option value="">-- Select Student --</option>

                    </select>
                </div>

                <div class="mb-3">
                    <label for="supervisor_id" class="form-label">Supervisor</label>
                    <select class="form-select" name="supervisor_id" required>
                        <option value="">-- Select Supervisor --</option>

                    </select>
                </div>

                <div class="mb-3">
                    <label for="evaluation_date" class="form-label">Evaluation Date</label>
                    <input type="date" name="evaluation_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="evaluation_score" class="form-label">Score (%)</label>
                    <input type="number" name="evaluation_score" class="form-control" step="0.01" min="0" max="100" required>
                </div>

                <div class="mb-3">
                    <label for="comments" class="form-label">Comments</label>
                    <textarea name="comments" class="form-control" rows="4" placeholder="Optional remarks..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Submit Evaluation</button>
            </form>
        </div>
    </div>
</div>


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
