<?php
include('../db.php');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['user_id'])) {
    echo "User ID is not provided.";
    exit;
}

$user_id = $_GET['user_id'];

// Fetch user details
$user_sql = "SELECT * FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($con, $user_sql);
if (!$user_result) {
    echo "Error fetching user data: " . mysqli_error($con);
    exit;
}
$user = mysqli_fetch_assoc($user_result);

// Fetch personal details
$personal_details_sql = "SELECT * FROM personal_details WHERE user_id = '$user_id'";
$personal_details_result = mysqli_query($con, $personal_details_sql);
if (!$personal_details_result) {
    echo "Error fetching personal details: " . mysqli_error($con);
    exit;
}
$personal_details = mysqli_fetch_assoc($personal_details_result);

// Fetch contact details
$contact_details_sql = "SELECT * FROM contact_details WHERE user_id = '$user_id'";
$contact_details_result = mysqli_query($con, $contact_details_sql);
if (!$contact_details_result) {
    echo "Error fetching personal details: " . mysqli_error($con);
    exit;
}
$contact_details = mysqli_fetch_assoc($contact_details_result);

// Fetch academic qualifications
$academic_qualification_sql = "SELECT * FROM academic_qualification WHERE user_id = '$user_id'";
$academic_qualification_result = mysqli_query($con, $academic_qualification_sql);
if (!$academic_qualification_result) {
    echo "Error fetching academic qualifications: " . mysqli_error($con);
    exit;
}

// Fetch other attachments
$other_attachments_sql = "SELECT * FROM other_attachments WHERE user_id = '$user_id'";
$other_attachments_result = mysqli_query($con, $other_attachments_sql);
if (!$other_attachments_result) {
    echo "Error fetching other attachments: " . mysqli_error($con);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 50px;
        }
        .card {
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #343a40;
            color: #fff;
        }
        .card-body p {
            margin: 0;
        }
        .attachment img {
            max-width: 100%;
            height: auto;
        }
        .attachment embed {
            width: 100%;
            height: 400px;
        }
        
        body {
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 50px;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #343a40;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <?php include('./sidebar.php'); ?>
        <div class="topbar">
            <a href="admin_logout.php" class="admin-logout">Logout</a>
        </div>
        
        <div class="info" style="background: white">
        <div class="data-container">
        <h1 class="mb-4">User Details</h1>

        <!-- User Info -->
        <div class="card">
            <div class="card-header">User Info</div>
            <div class="card-body">
                <?php if ($user) { ?>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <?php } else { ?>
                    <p>No user info available.</p>
                <?php } ?>
            </div>
        </div>

        <!-- Personal Details -->
        <div class="card">
            <div class="card-header">Personal Details</div>
                <div class="card-body">
                    <?php if ($personal_details) { ?>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($personal_details['address']); ?></p>
                        <p><strong>Phone :</strong> <?php echo htmlspecialchars($personal_details['phone']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($personal_details['email']); ?></p>
                        <p><strong>Date of birth:</strong> <?php echo htmlspecialchars($personal_details['place_of_birth']); ?></p>
                        <p><strong>Region:</strong> <?php echo htmlspecialchars($personal_details['resident_region']); ?></p>
                        <p><strong>District:</strong> <?php echo htmlspecialchars($personal_details['district']); ?></p>
                            
                    <?php } else { ?>
                        <p>No personal details available.</p>
                    <?php } ?>
                </div>
            </div>
            
            <!-- Contact Details -->
        <div class="card">
            <div class="card-header">Contact Details</div>
                <div class="card-body">
                    <?php if ($contact_details) { ?>
                        <p><strong>Bank No:</strong> <?php echo htmlspecialchars($contact_details['bank_no']); ?></p>
                        <p><strong>Bank Name:</strong> <?php echo htmlspecialchars($contact_details['bank_name']); ?></p>
                        <p><strong>Zan ID:</strong> <?php echo htmlspecialchars($contact_details['zan_id']); ?></p>
                        <p><strong>ZSSF No:</strong> <?php echo htmlspecialchars($contact_details['zssf_no']); ?></p>
                        <p><strong>License No:</strong> <?php echo htmlspecialchars($contact_details['license_no']); ?></p>
                        <p><strong>Vol No:</strong> <?php echo htmlspecialchars($contact_details['vol_no']); ?></p>
                        <p><strong>Upload Zan ID Photo:</strong>
                            <?php if (!empty($contact_details['upload_zan_id_photo'])) { ?>
                                <a href="../uploads/<?php echo htmlspecialchars($contact_details['upload_zan_id_photo']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($contact_details['upload_zan_id_photo']); ?>
                                </a>
                            <?php } else { ?>
                                No photo uploaded.
                            <?php } ?>
                        </p>
                    <?php } else { ?>
                        <p>No contact details available.</p>
                    <?php } ?>
                </div>
            </div>


            <!-- Academic Qualifications -->
            <div class="card">
                <div class="card-header">Academic Qualifications</div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($academic_qualification_result) > 0) { ?>
                        <ul>
                            <?php while ($qualification = mysqli_fetch_assoc($academic_qualification_result)) { ?>
                                <li>
                                    <strong>Qualification:</strong> <?php echo htmlspecialchars($qualification['qualification']); ?><br>
                                    <strong>Institution:</strong> <?php echo htmlspecialchars($qualification['institution']); ?><br>
                                    <strong>Year of Completion:</strong> <?php echo htmlspecialchars($qualification['year_of_completion']); ?>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } else { ?>
                        <p>No academic qualifications available.</p>
                    <?php } ?>
                </div>
            </div>

            <!-- Other Attachments -->
            <div class="card">
                <div class="card-header">Other Attachments</div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($other_attachments_result) > 0) { ?>
                        <ul class="list-unstyled">
                            <?php while ($attachment = mysqli_fetch_assoc($other_attachments_result)) { ?>
                                <li class="mb-3">
                                    <strong>Attachment Name:</strong> <?php echo htmlspecialchars($attachment['attachment_name']); ?><br>
                                    <?php
                                    $file_extension = pathinfo($attachment['attachment_url'], PATHINFO_EXTENSION);
                                    $file_url = '../'.htmlspecialchars($attachment['file_path']);
                                    if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                        echo '<div class="attachment"><img src="'. $file_url . '" alt="Attachment Image"></div>';
                                    } elseif ($file_extension === 'pdf') {
                                        echo '<div class="attachment"><embed src="'.$file_url.'" type="application/pdf"></div>';
                                    } else {
                                        echo '<a href="'.$file_url.'" target="_blank">View Attachment</a>';
                                    }
                                    ?>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } else { ?>
                        <p>No other attachments available.</p>
                    <?php } ?>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
