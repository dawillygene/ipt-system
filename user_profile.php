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

    <div class="container" style="margin-bottom: 100px">
        <div class="row">
            <div class="col-md-3">
                <?php include('./sidebar.php'); ?>
            </div>

            <div class="col-md-9">
                <div class="section-info">
                    <h3>Welcome,<?php echo htmlspecialchars($student['full_name']); ?>!</h3>
                    <p>Your University Profile Details</p>
                    <hr />
                    <a href="update_profile.php">Update your profile</a>
                </div>

                <!--<div class="user-info-detail">

                    <div class="detail-header">
                        <p><strong>User Name:</strong> <?php echo htmlspecialchars($user_data['name']); ?></p>
                        <p><strong>User Email:</strong> <?php echo htmlspecialchars($user_data['email']); ?></p>
                    </div>
                </div>!-->


                <div class="user-info-detail">

                    <div class="detail-header">
                        <p>Profile Details</p>
                    </div>


                    <?php if ($student): ?>
                        <table class="table table-striped mt-4">
                            <tr><th>Full Name</th><td><?= htmlspecialchars($student['full_name']) ?></td></tr>
                            <tr><th>Registration Number</th><td><?= htmlspecialchars($student['reg_number']) ?></td></tr>
                            <tr><th>Gender</th><td><?= htmlspecialchars($student['gender']) ?></td></tr>
                            <tr><th>College</th><td><?= htmlspecialchars($student['college_name']) ?></td></tr>
                            <tr><th>Department</th><td><?= htmlspecialchars($student['department']) ?></td></tr>
                            <tr><th>Course</th><td><?= htmlspecialchars($student['course_name']) ?></td></tr>
                            <tr><th>Program</th><td><?= htmlspecialchars($student['program']) ?></td></tr>
                            <tr><th>Level</th><td><?= htmlspecialchars($student['level']) ?></td></tr>
                            <tr><th>Year of Study</th><td><?= htmlspecialchars($student['year_of_study']) ?></td></tr>
                            <tr><th>Phone</th><td><?= htmlspecialchars($student['phone_number']) ?></td></tr>
                            <tr><th>Email</th><td><?= htmlspecialchars($student['email']) ?></td></tr>
                            <tr><th>Address</th><td><?= htmlspecialchars($student['address']) ?></td></tr>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-warning">No student profile found for your account.</div>
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
