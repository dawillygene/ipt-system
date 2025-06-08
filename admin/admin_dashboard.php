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
$requests_sql = "SELECT user_requests.*, users.name, users.email FROM user_requests INNER JOIN users ON user_requests.user_id = users.id";
$requests_result = mysqli_query($con, $requests_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="./css/index.css" rel="stylesheet">
</head>
<body>
    <div class="page-container-12">
        <?php include('./sidebar.php'); ?>
        
        <div class="topbar">
            <a href="admin_logout.php" class="admin-logout">Logout</a>
        </div>

        <div class="container" style="margin-top: 100px;width: 100%">
            <div class="row">
                <div class="col-md-12">
                    <div class="">
                        <h2>Welcome Back!</h2>
                        <small></small>
                    </div>
                </div>
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
