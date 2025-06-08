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

    $user_id         = $_SESSION['user_id'];
    $week_number     = $_POST['week_number'];
    $start_date      = $_POST['start_date'];
    $end_date        = $_POST['end_date'];
    $description     = $_POST['description'];
    $skills_gained   = $_POST['skills_gained'];
    $challenges_faced = $_POST['challenges_faced'];


    // File upload
    $report_file_name = '';
    if (!empty($_FILES['report_file']['name'])) {
        $target_dir = "uploads/logbook/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_tmp  = $_FILES["report_file"]["tmp_name"];
        $file_name = time() . "_" . basename($_FILES["report_file"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($file_tmp, $target_file)) {
            $report_file_name = $file_name;
        } else {
            echo "<div class='alert alert-danger'>Failed to upload file.</div>";
            exit;
        }
    }


    $stmt = $conn->prepare("INSERT INTO reports (user_id, week_number, start_date, end_date, description, skills_gained, challenges_faced, report_file)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssss", $user_id, $week_number, $start_date, $end_date, $description, $skills_gained, $challenges_faced, $report_file_name);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Report submitted successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
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
                <h2 class="detail-header">Fill Report</h2>
                <div class="form-inside">
                    <?= $message ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="week_number" class="form-label">Week Number</label>
                            <input type="number" class="form-control" name="week_number" id="week_number" min="1" required>
                        </div>

                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" id="start_date" required>
                        </div>

                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" id="end_date" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="description" rows="4" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="skills_gained" class="form-label">Skills Gained</label>
                            <textarea class="form-control" name="skills_gained" id="skills_gained" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="challenges_faced" class="form-label">Challenges Faced</label>
                            <textarea class="form-control" name="challenges_faced" id="challenges_faced" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="report_file" class="form-label">Upload Report File (PDF/Image)</label>
                            <input type="file" class="form-control" name="report_file" id="report_file" accept=".pdf,.jpg,.jpeg,.png">
                        </div>

                        <button style="width: 40%" type="submit" class="submit-btn">Submit Report</button>
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
