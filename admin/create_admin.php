<?php
include('db.php');

$email = 'admin@dmin.com';
$password = password_hash('admin', PASSWORD_BCRYPT);

$sql = "INSERT INTO admins (email, password) VALUES ('$email', '$password')";

if (mysqli_query($con, $sql)) {
    echo "Admin user created successfully!";
} else {
    echo "Error: " . $sql . "<br>" . mysqli_error($con);
}

mysqli_close($con);
?>