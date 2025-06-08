<?php
include('db.php'); // Assuming this file contains your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $id = $_POST['id'];
    $name = $_POST['name'];
    $position = $_POST['position'];
    $gender = $_POST['gender'];
    $maritalStatus = $_POST['marital-status'];
    $approval = "Pending"; // Assuming you're setting a default value for approval

    // SQL query to insert data into the database
    $sql = "INSERT INTO employee_information (id, name, position, gender, marital_status, approval) VALUES ('$id', '$name', '$position', '$gender', '$maritalStatus', '$approval')";

    // Execute query
    if (mysqli_query($con, $sql)) {
        echo "Employee information submitted successfully.";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($con);
    }

    // Close database connection
    mysqli_close($con);
}
?>
