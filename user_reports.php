<?php
    session_start();

    // Redirect if user is not logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Connect to the database
    $conn = new mysqli("localhost", "root", "", "ipt-sys-test");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch student data
    $sql = "SELECT * FROM students WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $student = $result->fetch_assoc();
?>

<?php

    $user_id = $_SESSION['user_id'];

    // Connect to the database
    $conn = new mysqli("localhost", "root", "", "ipt-sys-test");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch student data
    $sql = "SELECT * FROM reports WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $reports = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>IPT - Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="Responsive web template" />
    <link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
    <link href="css/font-awesome.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet" type="text/css" media="all" />
    <link href="css/index.css" rel="stylesheet" type="text/css" media="all" />
    <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Federo" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900" rel="stylesheet">
</head>
<body>
<!-- New Header Start Here -->
<?php include('./header.php'); ?>

<hr />

<main>

    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <?php include('./sidebar.php'); ?>
            </div>

            <div class="col-md-9">
                <div class="section-info">
                    <h3>Your Reports,<?php echo htmlspecialchars($student['full_name']); ?>!</h3>
                    <p>Your Report List</p>

                    <hr />
                    <a href="add-report.php">Add Report</a>
                </div>

                <div class="user-info-detail">

                    <div class="detail-header">
                        <p>Reports</p>
                    </div>

                    <!--<table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Request</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>request_text</td>
                                <td>status</td>
                                <td>created_at</td>
                            </tr>
                        </tbody>
                    </table>!-->

                    <?php if ($result): ?>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>Week Number</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Description</th>
                                <th>Skills Gained</th>
                                <th>Challenges Faced</th>
                                <th>Report File</th>
                                <th>Submitted On</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?= htmlspecialchars($reports['week_number']) ?></td>
                                    <td><?= htmlspecialchars($reports['start_date']) ?></td>
                                    <td><?= htmlspecialchars($reports['end_date']) ?></td>
                                    <td><?= nl2br(htmlspecialchars($reports['description'])) ?></td>
                                    <td><?= nl2br(htmlspecialchars($reports['skills_gained'])) ?></td>
                                    <td><?= nl2br(htmlspecialchars($reports['challenges_faced'])) ?></td>
                                    <td>
                                        <?php if (!empty($reports['report_file'])): ?>
                                            <a href="uploads/logbook/<?= urlencode($reports['report_file']) ?>" target="_blank">View File</a>
                                        <?php else: ?>
                                            No File
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($reports['created_at']) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No logbook entries found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include('./footer.php'); ?>

<script type="text/javascript" src="js/jquery-2.1.4.min.js"></script>
<script src="js/jqBootstrapValidation.js"></script>
<script src="js/jquery-ui.js"></script>
<script>
    $(function() {
        $("#datepicker,#datepicker1,#datepicker2,#datepicker3").datepicker();
    });
</script>
<script src="js/jquery.swipebox.min.js"></script>
<script type="text/javascript">
    jQuery(function($) {
        $(".swipebox").swipebox();
    });
</script>
<script type="text/javascript" src="js/move-top.js"></script>
<script type="text/javascript" src="js/easing.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        $(".scroll").click(function(event){
            event.preventDefault();
            $('html,body').animate({scrollTop:$(this.hash).offset().top},1000);
        });
    });
</script>
<script defer src="js/jquery.flexslider.js"></script>
<script type="text/javascript">
    $(window).load(function(){
        $('.flexslider').flexslider({
            animation: "slide",
            start: function(slider){
                $('body').removeClass('loading');
            }
        });
    });
</script>
<script src="js/responsiveslides.min.js"></script>
<script>
    $(function () {
        $("#slider4").responsiveSlides({
            auto: true,
            pager: true,
            nav: false,
            speed: 500,
            namespace: "callbacks",
            before: function () {
                $('.events').append("<li>before event fired.</li>");
            },
            after: function () {
                $('.events').append("<li>after event fired.</li>");
            }
        });
    });
</script>
<script src="js/main.js"></script>
<script src="js/easy-responsive-tabs.js"></script>
<script>
    $(document).ready(function () {
        $('#horizontalTab').easyResponsiveTabs({
            type: 'default',
            width: 'auto',
            fit: true,
            closed: 'accordion',
            activate: function(event) {
                var $tab = $(this);
                var $info = $('#tabInfo');
                var $name = $('span', $info);
                $name.text($tab.text());
                $info.show();
            }
        });
        $('#verticalTab').easyResponsiveTabs({
            type: 'vertical',
            width: 'auto',
            fit: true
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $().UItoTop({ easingType: 'easeOutQuart' });
    });
</script>
<script type="text/javascript" src="js/bootstrap-3.1.1.min.js"></script>

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
