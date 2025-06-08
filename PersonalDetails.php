<?php
include('db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $place_of_birth = mysqli_real_escape_string($con, $_POST['place_of_birth']);
    $resident_region = mysqli_real_escape_string($con, $_POST['resident_region']);
    $district = mysqli_real_escape_string($con, $_POST['district']);

    $sql = "INSERT INTO personal_details (user_id, address, phone, email, place_of_birth, resident_region, district) VALUES ('$user_id', '$address', '$phone', '$email', '$place_of_birth', '$resident_region', '$district')";

    if (mysqli_query($con, $sql)) {
        echo "Personal details saved successfully.";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($con);
    }

    mysqli_close($con);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPT - Personal Details</title>
    <link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
    <link href="css/style.css" rel="stylesheet" type="text/css" media="all" />
    
    <link href="css/font-awesome.css" rel="stylesheet"> 
    <link href="css/index.css" rel="stylesheet" type="text/css" media="all" />
    <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Federo" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900" rel="stylesheet">
</head>
<body>
    <div class="section-title">
        <small>Dashboard</small>
    </div>
    <div class="container">
        <h2>Personal Information</h2>
        <form action="personal_details.php" method="post">
            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required>
            
            <label for="phone">Phone Number:</label>
            <input type="text" id="phone" name="phone" required>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            
            <label for="place-of-birth">Place of Birth:</label>
            <input type="text" id="place-of-birth" name="place_of_birth" required>
            
            <label for="resident-region">Resident Region:</label>
            <input type="text" id="resident-region" name="resident_region" required>
            
            <label for="district">District:</label>
            <input type="text" id="district" name="district" required>
            
            <button type="submit">Save</button>
        </form>
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
