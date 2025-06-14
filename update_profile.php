<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "root", "", "ipt-sys-test");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $user_id       = $_SESSION['user_id'];
    $full_name     = $_POST['full_name'];
    $reg_number    = $_POST['reg_number'];
    $gender        = $_POST['gender'];
    $college_name  = $_POST['college_name'];
    $department    = $_POST['department'];
    $course_name   = $_POST['course_name'];
    $program       = $_POST['program'];
    $level         = $_POST['level'];
    $year_of_study = $_POST['year_of_study'];
    $phone_number  = $_POST['phone_number'];
    $address       = $_POST['address'];
    $email         = $_POST['email'];

    // Handle profile photo upload
    $profile_photo_path = null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
        $target_dir = "uploads/profiles/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = time() . "_" . basename($_FILES["profile_photo"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed = ["jpg", "jpeg", "png", "gif"];

        if (in_array($imageFileType, $allowed)) {
            if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
                $profile_photo_path = $target_file;
            } else {
                $message = "<div class='alert alert-danger'>Failed to upload profile photo.</div>";
            }
        } else {
            $message = "<div class='alert alert-warning'>Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.</div>";
        }
    }

    if ($message == "") {
        // Check if student info already exists for this user
        $check = "SELECT student_id FROM students WHERE user_id = '$user_id'";
        $result = $conn->query($check);

        if ($result->num_rows > 0) {
            // Update existing student record
            $update_sql = "UPDATE students SET 
                full_name = '$full_name',
                reg_number = '$reg_number',
                gender = '$gender',
                college_name = '$college_name',
                department = '$department',
                course_name = '$course_name',
                program = '$program',
                level = '$level',
                year_of_study = '$year_of_study',
                phone_number = '$phone_number',
                address = '$address',
                email = '$email'";

            if ($profile_photo_path) {
                $update_sql .= ", profile_photo = '$profile_photo_path'";
            }

            $update_sql .= " WHERE user_id = '$user_id'";

            if ($conn->query($update_sql) === TRUE) {
                $message = "<div class='alert alert-info'>Profile updated successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Update failed: " . $conn->error . "</div>";
            }
        } else {
            // Insert new student record
            $sql = "INSERT INTO students (
                user_id, full_name, reg_number, gender, college_name, department,
                course_name, program, level, year_of_study, phone_number, address, email, profile_photo
            ) VALUES (
                '$user_id', '$full_name', '$reg_number', '$gender', '$college_name', '$department',
                '$course_name', '$program', '$level', '$year_of_study', '$phone_number', '$address', '$email', '$profile_photo_path'
            )";

            if ($conn->query($sql) === TRUE) {
                $message = "<div class='alert alert-success'>Student profile registered successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Insert failed: " . $conn->error . "</div>";
            }
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
                <h2 class="detail-header">Update Your Profile</h2>
                <div class="form-inside">
                    <?= $message ?>

                    <form method="POST" class="bg-white p-4 rounded shadow-sm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" id="full_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="reg_number" class="form-label">Registration Number</label>
                            <input type="text" class="form-control" name="reg_number" id="reg_number" required>
                        </div>

                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select form-control" name="gender" id="gender" required>
                                <option value="">-- Select Gender --</option>
                                <option>Male</option>
                                <option>Female</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="college_name" class="form-label">College Name</label>
                            <input type="text" class="form-control" name="college_name" id="college_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="department" class="form-label">Department</label>
                            <input type="text" class="form-control" name="department" id="department" required>
                        </div>

                        <div class="mb-3">
                            <label for="course_name" class="form-label">Course Name</label>
                            <input type="text" class="form-control" name="course_name" id="course_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="program" class="form-label">Program</label>
                            <input type="text" class="form-control" name="program" id="program" placeholder="e.g., Undergraduate" required>
                        </div>

                        <div class="mb-3">
                            <label for="level" class="form-label">Level</label>
                            <input type="text" class="form-control" name="level" id="level" placeholder="e.g., Bachelor, Diploma" required>
                        </div>

                        <div class="mb-3">
                            <label for="year_of_study" class="form-label">Year of Study</label>
                            <input type="number" class="form-control" name="year_of_study" id="year_of_study" min="1" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="phone_number" id="phone_number" placeholder="Enter phone number starting with +255 (e.g., +255753225961)" required>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" name="address" id="address" rows="2" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email (optional)</label>
                            <input type="email" class="form-control" name="email" id="email">
                        </div>

                        <div class="mb-3">
                            <label for="profile_photo" class="form-label">Profile Photo</label>
                            <input type="file" name="profile_photo" id="profile_photo" class="form-control" required>
                        </div>

                        <button style="width: 40%" type="submit" class="submit-btn">Update Profile</button>
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
