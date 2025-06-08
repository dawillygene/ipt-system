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
$students_sql = "SELECT students.* FROM students";
$students_result = mysqli_query($con, $students_sql);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Students</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="./css/index.css" rel="stylesheet">
</head>
<body>
<div class="page-container">
    <?php include('./sidebar.php'); ?>

    <div class="topbar">
        <a href="admin_logout.php" class="admin-logout">Logout</a>
    </div>

    <div class="info">
        <h2 class="mt-4 section-title">Students</h2>
        <a href="./add-student.php" class="add-btn">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add Student
        </a>
        <div class="data-container">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th scope="row">Id</th>
                    <th scope="row">College Name</th>
                    <th scope="row">Course Name</th>
                    <th scope="row">Year Of Study</th>
                    <th scope="row">Phone Number</th>
                    <th scope="row">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($request = mysqli_fetch_assoc($students_result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($request['id']); ?></td>
                        <td><?php echo htmlspecialchars($request['college_name']); ?></td>
                        <td><?php echo htmlspecialchars($request['course_name']); ?></td>
                        <td><?php echo htmlspecialchars($request['year_of_study']); ?></td>
                        <td><?php echo htmlspecialchars($request['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($request['address']); ?></td>
                        <!--<td>
                            <?php if ($request['status'] == 'pending') { ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                    <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                </form>
                                <form method="GET" action="view_user.php" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?php echo $request['user_id']; ?>">
                                    <button type="submit" class="btn btn-info btn-sm">View User Data</button>
                                </form>
                            <?php } else { ?>
                                <?php echo ucfirst($request['status']); ?>
                            <?php } ?>
                        </td>!-->
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
