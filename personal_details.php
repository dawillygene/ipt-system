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
        // Redirect to contact_details.php
        header("Location: contact_details.php");
        exit;
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
    
    <main class="container">
        <div class="row">
            <div class="col-md-3">
                <?php include('./sidebar.php'); ?>
            </div>
            
            <div class="col-md-9">
                <h2 class="detail-header">Personal Information</h2>
                <div class="form-inside">
                    <?php if (!empty($message)) { ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $message; ?>
                    </div>
                    <div class="back-to-dashboard">
                        <a href="dashboard.php">Back to Dashboard</a>
                    </div>
                <?php } else { ?>
                    <form action="personal_details.php" method="post" class="needs-validation" novalidate>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <div class="invalid-feedback">
                                Please enter your address.
                            </div>
                            <input type="text" class="form-control" id="address" name="address" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <div class="invalid-feedback">
                                Please enter your phone number.
                            </div>
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter phone number starting with +255 (e.g., +255753225961)" required>

                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="invalid-feedback">
                                Please enter a valid email address.
                            </div>
                            <input type="email" class="form-control" id="email" name="email" required>

                        </div>
                        <div class="form-group">
                            <label for="place_of_birth">Place of Birth</label>
                            <div class="invalid-feedback">
                                Please enter your place of birth.
                            </div>
                            <input type="text" class="form-control" id="place_of_birth" name="place_of_birth" required>

                        </div>
                        <div class="form-group">
                            <label for="resident_region">Resident Region</label>
                            <div class="invalid-feedback">
                                Please enter your resident region.
                            </div>
                            <input type="text" class="form-control" id="resident_region" name="resident_region" required>

                        </div>
                        <div class="form-group">
                            <label for="district">District</label>
                            <div class="invalid-feedback">
                                Please enter your district.
                            </div>
                            <input type="text" class="form-control" id="district" name="district" required>

                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save</button>
                    </form>
                <?php } ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include('./footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.min.js"></script>
    <script>
        // Bootstrap validation
        (function () {
            'use strict';
            window.addEventListener('load', function () {
                var forms = document.getElementsByClassName('needs-validation');
                Array.prototype.filter.call(forms, function (form) {
                    form.addEventListener('submit', function (event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>

        
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
