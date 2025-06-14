<?php
/*
include('db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $bank_no = mysqli_real_escape_string($con, $_POST['bank_no']);
    $bank_name = mysqli_real_escape_string($con, $_POST['bank_name']);
    $zan_id = mysqli_real_escape_string($con, $_POST['zan_id']);
    $zssf_no = mysqli_real_escape_string($con, $_POST['zssf_no']);
    $license_no = mysqli_real_escape_string($con, $_POST['license_no']);
    $vol_no = mysqli_real_escape_string($con, $_POST['vol_no']);

    // Handle file upload
    $upload_dir = 'uploads/';
    $upload_zan_id_photo = $_FILES['upload_zan_id_photo']['name'];
    $upload_file = $upload_dir . basename($upload_zan_id_photo);
    if (move_uploaded_file($_FILES['upload_zan_id_photo']['tmp_name'], $upload_file)) {
        // File is uploaded successfully
    } else {
        echo "Error uploading file.";
        exit;
    }

    $sql = "INSERT INTO contact_details (user_id, bank_no, bank_name, zan_id, zssf_no, upload_zan_id_photo, license_no, vol_no) VALUES ('$user_id', '$bank_no', '$bank_name', '$zan_id', '$zssf_no', '$upload_zan_id_photo', '$license_no', '$vol_no')";

    if (mysqli_query($con, $sql)) {
        echo "Contact details saved successfully.";
        // Redirect to another page, e.g., a confirmation page
        header("Location: Other_attachment.php");
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($con);
    }

    mysqli_close($con);
}*/
?>

<?php

session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form inputs
    $full_name = $_POST['full-name'];
    $phone = $_POST['phone'];
    $reg_number = $_POST['reg-number'];
    $department = $_POST['department'];
    $industrial = $_POST['industrial'];
    $application_date = $_POST['application_date'];
    $user_id = $_SESSION['user_id'];

    // Connect to the database
    $conn = new mysqli("localhost", "root", "", "ipt-sys-test");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if an application already exists for this user
    $check_sql = "SELECT id FROM applications WHERE user_id = $user_id";
    $result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($result) > 0) {
        $message = "<div class='alert alert-warning'>You have already submitted an application.</div>";
    } else {
        // Insert new application
        $insert_sql = "INSERT INTO applications (user_id,  phone, full_name, reg_number, department, industrial, application_date)
                       VALUES ($user_id, '$full_name', '$phone', '$reg_number', '$department', '$industrial', '$application_date')";

        if (mysqli_query($conn, $insert_sql)) {
            $message = "<div class='alert alert-success'>Application submitted successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
        }
    }

    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPT - Contact Details</title>


    <link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
    <link href="css/font-awesome.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet" type="text/css" media="all" />
    <link href="css/index.css" rel="stylesheet" type="text/css" media="all" />
    <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Federo" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900" rel="stylesheet">
</head>
<body>
<?php include('./header.php'); ?>

<hr />
<main>
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <?php include('./sidebar.php'); ?>
            </div>

            <div class="col-md-9">
                <h2 class="detail-header">Apply Now</h2>
                <div class="form-inside"><h4>
                    <?php if (isset($message)) echo $message; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="full-name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full-name" id="full-name" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="phone" id="phone" placeholder="Enter phone number starting with +255 (e.g., +255753225961)" required>
                        </div>

                        <div class="mb-3">
                            <label for="reg-number" class="form-label">Registration Number</label>
                            <input type="text" class="form-control" name="reg-number" id="reg-number" required>
                        </div>

                        <div class="mb-3">
                            <label for="department" class="form-label">Department</label>
                            <input type="text" class="form-control" name="department" id="department" required>
                        </div>

                        <div class="mb-3">
                            <label for="industrial" class="form-label">Industrial (More than one)</label>
                            <input type="text" class="form-control" name="industrial" id="industrial" required>
                        </div>

                        <div class="mb-3">
                            <label for="application_date" class="form-label">Application Date</label>
                            <input type="date" class="form-control" name="application_date" id="application_date" required>
                        </div>

                        <button type="submit" class="submit-btn">Submit</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</main>

<?php include('./footer.php'); ?>


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
