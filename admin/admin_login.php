<?php
include('db.php');
session_start();

if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    $sql = "SELECT * FROM admins WHERE email = '$email'";
    $result = mysqli_query($con, $sql);
    $admin = mysqli_fetch_assoc($result);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            padding: 40px;
            width: 100%;
            height: 500px;
        }

        h1 {
            font-size: 2em;
            color: #000;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            padding: 20px 0px
        }

        label {
            font-size: 1rem;
            color: #495057;
            margin-bottom: 5px;
        }

        input[type="email"], input[type="password"] {
            font-size: 1rem;
            padding: 14px 20px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        button {
            background-color: #0056b3;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 1.2em;
            color: #fff;
            font-weight: 600;
            padding: 15px 20px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        p {
            background-color: red;
            text-align: center;
            margin-top: 10px;
            color: #fff;
            padding: 15px 20px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container" id="login-form">
        <h1>Admin Login</h1>
        <form action="admin_login.php" method="POST">
            <label for="email">Your Email:</label>
            <input type="email" id="email" name="email" placeholder="Email" required>
            
            <label for="password">Your Password:</label>
            <input type="password" id="password" name="password" placeholder="******" required>
            
            <button type="submit">Login</button>
        </form>
        <?php if (isset($error)) { ?>
            <p><i class="fa fa-error"></i><?php echo $error; ?></p>
        <?php } ?>
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
