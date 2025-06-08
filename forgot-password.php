<?php
session_start();
include('db.php');


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $username = $con->real_escape_string(trim($_POST['Uname']));
    $password = $con->real_escape_string(trim($_POST['Pass']));

    // Fetch user from database
    $query = "SELECT * FROM users WHERE email = '$username'";
    $result = $con->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            // Redirect to dashboard or protected page
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with this email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPT | Login</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- //for-mobile-apps -->
    <link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
    <link href="css/font-awesome.css" rel="stylesheet">
    <link rel="stylesheet" href="css/chocolat.css" type="text/css" media="screen">
    <link href="css/easy-responsive-tabs.css" rel='stylesheet' type='text/css'/>
    <link rel="stylesheet" href="css/flexslider.css" type="text/css" media="screen" property="" />
    <link rel="stylesheet" href="css/jquery-ui.css" />
    <link href="css/style.css" rel="stylesheet" type="text/css" media="all" />
    <link href="css/index.css" rel="stylesheet" type="text/css" media="all" />
    <script type="text/javascript" src="js/modernizr-2.6.2.min.js"></script>
    <!--fonts-->
    <link href="//fonts.googleapis.com/css?family=Oswald:300,400,700" rel="stylesheet">
    <link href="//fonts.googleapis.com/css?family=Federo" rel="stylesheet">
    <link href="//fonts.googleapis.com/css?family=Lato:300,400,700,900" rel="stylesheet">
    <!--//fonts-->
</head>
<body>
<!-- New Header Start Here -->
<?php include('./header.php'); ?>

<main>
    <div class="section-title">
        <small>Forgot Password</small>
    </div>

    <section id="center-form">
        <div class="form-box login-form">
            <h2>Enter New Password ..</h2>
            <form action="login.php" method="POST">
                <div class="input-box">
                    <label>Your Email</label>
                    <input type="email" name="Uname" placeholder="Email" required>
                </div>
                <div class="forgot-pass">
                    <a href="./login.php">Have an account? Login</a>
                </div>
                <button type="submit" class="btn">Reset</button>
                <div class="signup-link">
                    <a href="register.php">Don't have an account? Register</a>
                </div>
            </form>
        </div>
    </section>
</main>


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
