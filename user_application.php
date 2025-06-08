<?php
session_start();
include('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch user data from the database
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($con, $query);
$user_data = mysqli_fetch_assoc($result);

// Fetch user requests data
$requests_query = "SELECT * FROM user_requests WHERE user_id = $user_id";
$requests_result = mysqli_query($con, $requests_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>IPT - Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="Responsive web template" />
    <link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
    <link href="css/font-awesome.css" rel="stylesheet"> 
    <link href="css/style.css" rel="stylesheet" type="text/css" media="all" />
    <link href="css/index.css" rel="stylesheet" type="text/css" media="all" />
    <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Federo" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900" rel="stylesheet">
</head>
<body>
    <!-- New Header Start Here -->
    <?php include('./header.php'); ?>
        
    <hr />
    
    <div class="container">
        <div class="user-info-detail">
            
            <div class="detail-header">
            <h2>Your Requests</h2>
                <p><strong>User Name:</strong> <?php echo htmlspecialchars($user_data['name']); ?></p>
                <p><strong>User Email:</strong> <?php echo htmlspecialchars($user_data['email']); ?></p>
            </div>
            <!-- Add more user information fields as needed -->
            <?php if (mysqli_num_rows($requests_result) > 0) { ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Request</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($request = mysqli_fetch_assoc($requests_result)) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['request_text']); ?></td>
                                <td><?php echo htmlspecialchars($request['status']); ?></td>
                                <td><?php echo htmlspecialchars($request['created_at']); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p>No requests submitted yet.</p>
            <?php } ?>
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
