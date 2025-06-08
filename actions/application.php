<?php
session_start();

// DB config
$host = 'localhost';
$dbname = 'ipt-sys-test';
$username = 'root';
$password = '';

// Connect to DB
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check login
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];
$alert = "";

// Get form data
$full_name = $_POST['full-name'];
$reg_number = $_POST['reg-number'];
$department = $_POST['department'];
$industrial = $_POST['industrial'];
$application_date = $_POST['application_date'];

// Prepare and insert
$stmt = $conn->prepare("INSERT INTO applications 
    (user_id, full_name, reg_number, department, industrial, application_date) 
    VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssss", $user_id, $full_name, $reg_number, $department, $industrial, $application_date);

if ($stmt->execute()) {
    $alert = "Application submitted successfully.";
    // header("Location: ../success.php"); // Uncomment to redirect
} else {
    $alert = "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <title>IPT - Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="Responsive web template" />
    <link href="../css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
    <link href="../css/font-awesome.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet" type="text/css" media="all" />
    <link href="../css/index.css" rel="stylesheet" type="text/css" media="all" />
    <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Federo" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900" rel="stylesheet">
</head>
<body>
<style>
    body {
        margin: 0;
        height: 100vh;
        overflow: hidden;
        background-color: #f4f4f4;
    }

    .floating-word {
        position: absolute;
        top: 40%;
        left: -200px; /* start off-screen */
        font-size: 32px;
        color: #198754;
        font-weight: bold;
        animation: floatLeftToRight 8s linear infinite;
    }

    @keyframes floatLeftToRight {
        0% {
            left: -200px;
        }
        100% {
            left: 100%;
        }
    }

    .scroll-left{
        position: absolute;
        width: 65%;

    }

    .scroll-left marquee{
        color: #fff;
        padding: 20px 20px;
        text-transform: uppercase;
    }
</style>
<!-- New Header Start Here -->
<header>
    <div class="intro">
        <div class="intro-content">
            <img class="zanzi-flag" src="../images/znz_flag.gif" alt="" />
            <img class="kist-logo" src="../images/kist.webp" alt="" />

            <h1>KARUME INSTITUTE OF SCIENCE AND TECHNOLOGY</h1>
            <small>Industrial Practical Training Management System.</small>
        </div>
    </div>

    <?php if (isset($_SESSION['user_id'])) { ?>
        <nav class="nav-menu">
            <div class="scroll-left">
                <marquee behavior="scroll" direction="left" scrollamount="5">
                    Welcome To The Industrial Practical Training System
                </marquee>
            </div>
            <ul class="nav-menu-list">
                <li><a href="../dashboard.php">Dashboard</a></li>
                <li><a href="../user_applications.php">My Applications</a></li>
                <li><a href="../change_password.php">Change Password</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    <?php }else{?>
        <nav class="nav-menu">
            <ul class="nav-menu-list">
                <li><a href=".../index.php">Home</a></li>
                <li><a href=".../login.php">Login</a></li>
                <li><a href=".../register.php">Register</a></li>
            </ul>
        </nav>

    <?php } ?>

    <div class="section-title">
        <h3></h3>
    </div>
</header>

<hr />

<main>

    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <?php include('../sidebar.php'); ?>
            </div>

            <div class="col-md-9">
                <div class="section-info">
                    <h3><?php echo $alert ?>!</h3>
                    <p>Thank you for submitting your application. We will review your request and get back to you shortly.</p>
                </div>

                <hr />

                <a href="user_application.php">Your Applications</a>
            </div>
        </div>
    </div>
</main>

<?php include('../footer.php'); ?>

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
</script>
<script type="text/javascript" src="js/bootstrap-3.1.1.min.js"></script>


</body>
</html>
