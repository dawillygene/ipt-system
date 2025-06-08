<?php
include('db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch personal details
$personal_details_sql = "SELECT * FROM personal_details WHERE user_id = '$user_id'";
$personal_details_result = mysqli_query($con, $personal_details_sql);
$personal_details = mysqli_fetch_assoc($personal_details_result);

// Fetch academic qualifications
$academic_qualification_sql = "SELECT * FROM academic_qualification WHERE user_id = '$user_id'";
$academic_qualification_result = mysqli_query($con, $academic_qualification_sql);

// Fetch other attachments
$other_attachments_sql = "SELECT * FROM other_attachments WHERE user_id = '$user_id'";
$other_attachments_result = mysqli_query($con, $other_attachments_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPT - CV Preview</title>
    
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
                    <h1 class="detail-header">CV Preview</h1>
                        <div class="form-inside">
                            <div class="cv-section">
                                <h2>Personal Details</h2>
                                <?php if ($personal_details) { ?>
                                    <p><strong>Address:</strong> <?php echo $personal_details['address']; ?></p>
                                    <p><strong>Phone:</strong> <?php echo $personal_details['phone']; ?></p>
                                    <p><strong>Email:</strong> <?php echo $personal_details['email']; ?></p>
                                    <p><strong>Place of Birth:</strong> <?php echo $personal_details['place_of_birth']; ?></p>
                                    <p><strong>Resident Region:</strong> <?php echo $personal_details['resident_region']; ?></p>
                                    <p><strong>District:</strong> <?php echo $personal_details['district']; ?></p>
                                <?php } else { ?>
                                    <p>No personal details available.</p>
                                <?php } ?>
                            </div>

                            <div class="cv-section">
                                <h2>Academic Qualifications</h2>
                                <?php if (mysqli_num_rows($academic_qualification_result) > 0) { ?>
                                    <ul>
                                        <?php while ($qualification = mysqli_fetch_assoc($academic_qualification_result)) { ?>
                                            <li>
                                                <strong>Qualification:</strong> <?php echo $qualification['qualification']; ?><br>
                                                <strong>Institution:</strong> <?php echo $qualification['institution']; ?><br>
                                                <strong>Year of Completion:</strong> <?php echo $qualification['year_of_completion']; ?>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                <?php } else { ?>
                                    <p>No academic qualifications available.</p>
                                <?php } ?>
                            </div>
                            
                            <div class="cv-section">
                                <h2>Contact Details</h2>
                                <?php if ($personal_details) { ?>
                                    <p><strong>Address:</strong> <?php echo $personal_details['address']; ?></p>
                                    <p><strong>Phone:</strong> <?php echo $personal_details['phone']; ?></p>
                                    <p><strong>Email:</strong> <?php echo $personal_details['email']; ?></p>
                                    <p><strong>Place of Birth:</strong> <?php echo $personal_details['place_of_birth']; ?></p>
                                    <p><strong>Resident Region:</strong> <?php echo $personal_details['resident_region']; ?></p>
                                    <p><strong>District:</strong> <?php echo $personal_details['district']; ?></p>
                                <?php } else { ?>
                                    <p>No personal details available.</p>
                                <?php } ?>
                            </div>

                            <div class="cv-section">
                                <h2>Other Attachments</h2>
                                <?php if (mysqli_num_rows($other_attachments_result) > 0) { ?>
                                    <ul>
                                        <?php while ($attachment = mysqli_fetch_assoc($other_attachments_result)) { ?>
                                            <li>
                                                <strong>Attachment Name:</strong> <?php echo $attachment['attachment_name']; ?><br>
                                                <button type="button" class="btn btn-primary view-attachment" data-toggle="modal" data-target="#attachmentModal" data-content-url="<?php echo $attachment['file_path']; ?>" data-content-type="<?php echo pathinfo($attachment['file_path'], PATHINFO_EXTENSION); ?>">
                                                    View Attachment
                                                </button>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                <?php } else { ?>
                                    <p>No other attachments available.</p>
                                <?php } ?>
                                <hr />
                                <div class="cv-section">
                                    <div style="margin-top:30px">
                                        <a href="index.php" class="btn btn-primary back-btn">Back to Home</a>
                                        <button type="button" class="btn btn-primary pdf-btn" onclick="window.print();">Download as PDF</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

    <!-- Modal -->
    <div class="modal fade" id="attachmentModal" tabindex="-1" aria-labelledby="attachmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attachmentModalLabel">Attachment Preview</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="attachmentContent"></div>
                </div>
            </div>
        </div>
    </div>

    <?php include('./footer.php'); ?>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#attachmentModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var contentUrl = button.data('content-url');
                var contentType = button.data('content-type');
                var modal = $(this);
                var attachmentContent = $('#attachmentContent');

                attachmentContent.html(''); // Clear previous content
                
                if (contentType === 'pdf') {
                    attachmentContent.html('<iframe src="' + contentUrl + '" frameborder="0"></iframe>');
                } else if (contentType === 'jpg' || contentType === 'jpeg' || contentType === 'png' || contentType === 'gif') {
                    attachmentContent.html('<img src="' + contentUrl + '" alt="Attachment Image">');
                } else {
                    attachmentContent.html('<p>Unable to preview this file type. <a href="' + contentUrl + '" target="_blank">Download</a> to view.</p>');
                }
            });

            $('#attachmentModal').on('hidden.bs.modal', function () {
                $('#attachmentContent').html(''); // Clear the content when the modal is closed
            });
        });
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
