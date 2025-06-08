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

    $updated = "";

    // Fetch user requests data
    $requests_query = "SELECT * FROM user_requests WHERE user_id = $user_id";
    $requests_result = mysqli_query($con, $requests_query);

    if (isset($_POST['update'])) {
        // Sanitize and validate input
        $password = $con->real_escape_string(trim($_POST['password']));

        // Password hashing
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user into database
        $query = "UPDATE users SET password = '$hashed_password' WHERE id = '$user_id'";

        if ($con->query($query) === TRUE) {
            header("Location: login.php");
            exit();
        } else {
            $error = "Error: " . $query . "<br>" . $con->error;
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>IPT - Change Password</title>
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
    
    <div class="section-title-01">
        <div class="container">
            <h3>Change your account password</h3>
            <p>Create new password that you can use to login with old email address</p>

            <p><strong>Old Email:</strong> <?php echo htmlspecialchars($user_data['email']); ?></p>

            <form class="update-password-form" method="POST" enctype="multipart/form-data">
                <div class="update-password-input-box">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Pass**" required>
                </div>
                <button name="update" type="submit" class="btn">Update Password</button>
            </form>
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
