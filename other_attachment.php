<?php
include('db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uploadOk = 1;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];

    // Ensure the uploads directory exists
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Array to hold the paths of uploaded files and their names
    $file_data = [];

    // Allowed file types
    $allowed_types = array("pdf", "doc", "docx", "jpg", "jpeg", "png");

    // Document names array
    $document_names = [
        'CV' => 'cv_file',
        'Application Letter' => 'application_letter_file',
        'Passport Size Photo' => 'passport_photo_file',
        'Certification 1' => 'certification_1_file',
        'Certification 2' => 'certification_2_file',
        'Certification 3' => 'certification_3_file',
        'Other Document' => 'other_document_file'
    ];

    // Loop through each document
    foreach ($document_names as $doc_name => $input_name) {
        if (isset($_FILES[$input_name]) && $_FILES[$input_name]["error"] == 0) {
            $target_file = $target_dir . basename($_FILES[$input_name]["name"]);
            $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if file type is allowed
            if (!in_array($fileType, $allowed_types)) {
                $errors[] = "Sorry, only PDF, DOC, DOCX, JPG, JPEG, and PNG files are allowed.";
                $uploadOk = 0;
            }

            // Check file size (5MB max)
            if ($_FILES[$input_name]["size"] > 5000000) {
                $errors[] = "Sorry, your file is too large.";
                $uploadOk = 0;
            }

            // Attempt to move the uploaded file
            if ($uploadOk && move_uploaded_file($_FILES[$input_name]["tmp_name"], $target_file)) {
                $file_data[] = [
                    'name' => $doc_name,
                    'path' => $target_file
                ];
            } else {
                $errors[] = "Sorry, there was an error uploading your file.";
                $uploadOk = 0;
            }
        }
    }

    // If all files uploaded successfully, save to database
    if ($uploadOk && !empty($file_data)) {
        foreach ($file_data as $file) {
            $sql = "INSERT INTO other_attachments (user_id, attachment_name, file_path) VALUES ('$user_id', '{$file['name']}', '{$file['path']}')";

            if (!mysqli_query($con, $sql)) {
                $errors[] = "Error: " . $sql . "<br>" . mysqli_error($con);
            }
        }

        if (empty($errors)) {
            // Redirect to Academic_qualification.php after successful upload
            header("Location: Academic_qualification.php");
            exit;
        } else {
            foreach ($errors as $error) {
                echo $error . "<br>";
            }
        }
    } else {
        foreach ($errors as $error) {
            echo $error . "<br>";
        }
    }
}

mysqli_close($con);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Other Attachments</title>

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
                    <h1 class="detail-header">Other Attachments</h1>
                    <div class="form-inside">
                        <form action="other_attachment.php" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="cv_file">CV:</label>
                                <input type="file" id="cv_file" name="cv_file" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="application_letter_file">Application Letter:</label>
                                <input type="file" id="application_letter_file" name="application_letter_file" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="passport_photo_file">Passport Size Photo:</label>
                                <input type="file" id="passport_photo_file" name="passport_photo_file" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="certification_1_file">Certification 1:</label>
                                <input type="file" id="certification_1_file" name="certification_1_file" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="certification_2_file">Certification 2:</label>
                                <input type="file" id="certification_2_file" name="certification_2_file" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="certification_3_file">Certification 3:</label>
                                <input type="file" id="certification_3_file" name="certification_3_file" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="other_document_file">Other Document:</label>
                                <input type="file" id="other_document_file" name="other_document_file" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Add Attachment</button>
                        </form>
                        <hr />
                        <a href="dashboard.php" class="btn btn-link mt-3">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include('./footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.min.js"></script>

    
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
