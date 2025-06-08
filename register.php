<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $name = $con->real_escape_string(trim($_POST['name']));
    $email = $con->real_escape_string(trim($_POST['email']));
    $password = $con->real_escape_string(trim($_POST['password']));
    $role = $con->real_escape_string(trim($_POST['role']));
    $profile_photo = $_FILES['profile_photo'];

    // Password hashing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Handle file upload
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($profile_photo["name"]);
    move_uploaded_file($profile_photo["tmp_name"], $target_file);

    // Insert user into database
    $query = "INSERT INTO users (name, email, password, role, profile_photo) VALUES ('$name', '$email', '$hashed_password', '$role', '$target_file')";
    
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPT - Register</title>
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
    <?php include('./header.php'); ?>
    
    <main>
        <div class="section-title">
            <small>Register</small>
        </div>
    
        <section id="center-form">
            <div class="form-box register-form">
                <h2>Register your account</h2>
                <form action="register.php" method="POST" enctype="multipart/form-data">
                    <div class="input-box">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" placeholder="Name" required>
                    </div>
                    <div class="input-box">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="input-box">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Pass**" required>
                    </div>
                    <div class="input-box">
                        <label for="role">Role</label>
                        <select id="role" name="role" required>
                            <option value="" disabled selected>Select Role</option>
                            <option value="Supervisor">Supervisor</option>
                            <option value="Student">Student</option>
                        </select>
                    </div>
                    <!-- <div class="input-box">
                        <label for="profile_photo">Profile Photo</label>
                        <input type="file" id="profile_photo" name="profile_photo" accept="image/*" required>
                    </div> -->
                    <button type="submit" class="btn">Register</button>
                    <div class="signup-link">
                        <a href="login.php">Login</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
    <footer>
        <small>IPT. All Rights Reserved | <?php echo date('Y'); ?></small>
    </footer>

    
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
