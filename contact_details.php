<?php
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
                    <h2 class="detail-header">Contact Information</h2>
                    <div class="form-inside">
                        <form action="contact_details.php" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                            <label for="bank_no">Phone:</label>
                            <input class="form-control" type="text" id="bank_no" name="bank_no" required>
                            </div>
                            
                            <div class="form-group">
                            <label for="bank_name">Address:</label>
                            <input class="form-control" type="text" id="bank_name" name="bank_name" required>
                            </div>
                            
                            <div class="form-group">
                            <label for="zan_id">Email:</label>
                            <input class="form-control" type="text" id="zan_id" name="zan_id" required>
                            </div>
                            
                            <button class="btn btn-primary" type="submit">Save</button>
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
