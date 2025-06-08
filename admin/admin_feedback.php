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
if (isset($_POST['delete']) && isset($_POST['request_id'])) {
    $action = $_POST['action'];
    $request_id = $_POST['request_id'];
    $status = $action === 'approve' ? 'approved' : 'rejected';

    //$update_sql = "UPDATE user_requests SET status = '$status' WHERE id = '$request_id'";
    $update_sql = "DELETE FROM users WHERE id = '$request_id'";
    mysqli_query($con, $update_sql);
}

// Retrieve user requests from the database
$feedback_sql = "SELECT * FROM feedback";
$feedback_result = mysqli_query($con, $feedback_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="./css/index.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 50px;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #343a40;
        }
        .table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #dee2e6;
        }
        .table th {
            background-color: #343a40;
            color: #ffffff;
        }
        .table tbody tr:nth-child(odd) {
            background-color: #e9ecef;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }
        .btn-info:hover {
            background-color: #138496;
            border-color: #117a8b;
        }
        .text-right {
            margin-top: 20px;
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .sidebar{
            position: fixed;
            background: #0056b3;
            width: 16%;
            left: 0;
            bottom: 0;
            top: 0;
        }

        .topbar{
            position: fixed;
            background: #fff;
            width: 84%;
            height: 60px;
            left: 16%;
            right: 0;
            top: 0;
            box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15) !important;
        }

        .info{
            margin-left: 17%;
            width: 82%;
            margin-top: 100px;
        }

        .admin-logout{
            float: right;
            padding: 16px 30px;
            text-decoration: none;
            color: #0056b3;
            font-size: 20px;
        }

        .info{
            background: #f8f9fc;
            box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15) !important;
            border-radius: 4px;
            height: 100%;
            padding-top: 10px;
        }

        .info .data-container{
            background: #fff;
            padding: 20px 20px;
            height: 100%;
            border-bottom-left-radius: 4px;
            border-bottom-right-radius: 4px;
            padding-bottom: 100px;
        }

        .info h2{
            padding: 0 20px;
        }

        table thead th{
            background: #0056b3 !important;
            color: #fff;
        }

        .sidebar h3{
            color: #fff;
            font-size: 30px;
            padding: 13px 0;
            margin: 0;
            text-align: center;
            border-bottom: 2px solid #fff1;
        }

        .sidebar ul{
            list-style: none;
        }

        .sidebar ul li a{
            color: #fff;
            text-decoration: none;
            margin: 10px 0;
            display: inline-block;
        }
    </style>
</head>
<body>
<div class="page-container">
    <?php include('./sidebar.php'); ?>

    <div class="topbar">
        <a href="admin_logout.php" class="admin-logout">Logout</a>
    </div>

    <div class="info">
        <h2 class="mt-4 section-title">Feedback</h2>
        <a href="./add-feedback.php" class="add-btn">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add Feedback
        </a>
        <div class="data-container">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th scope="row">Id</th>
                    <th scope="row">Report</th>
                    <th scope="row">Supervisor</th>
                    <th scope="row">Feedback</th>
                    <th scope="row">Rating</th>
                    <th scope="row">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($request = mysqli_fetch_assoc($feedback_result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($request['id']); ?></td>
                        <td><?php echo htmlspecialchars($request['report_id']); ?></td>
                        <td><?php echo htmlspecialchars($request['supervisor_id']); ?></td>
                        <td><?php echo htmlspecialchars($request['feedback']); ?></td>
                        <td><?php echo htmlspecialchars($request['rating']); ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <button type="submit" name="delete"class="btn btn-danger btn-sm">Delete</button>
                            </form>
                            <form method="GET" action="view_user.php" class="d-inline">
                                <input type="hidden" name="user_id" value="<?php echo $request['id']; ?>">
                                <button type="submit" class="btn btn-info btn-sm">View</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
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
