<?php
include('db.php');
session_start();

// Assuming you have some sort of authentication for admin
// For now, we will just use a simple session variable check
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch all users
$users_sql = "SELECT * FROM users";
$users_result = mysqli_query($con, $users_sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
    <link href="css/style.css" rel="stylesheet" type="text/css" media="all" />
</head>
<body>
    <div class="container">
        <h1>Admin Panel</h1>
        <h2>Registered Users</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = mysqli_fetch_assoc($users_result)) { ?>
                <tr>
                    <td><?php echo $user['name']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo $user['role']; ?></td>
                    <td><a href="view_user.php?id=<?php echo $user['id']; ?>">View Details</a></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
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
