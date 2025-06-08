<?php
include('db.php');
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $message = mysqli_real_escape_string($con, $_POST['message']);

    $sql = "INSERT INTO contact_details (name, phone, email, message)
            VALUES ('$name', '$phone', '$email', '$message')";

    if (mysqli_query($con, $sql)) {
        $message = "Contact details saved successfully.";
    } else {
        $message = "Error: " . mysqli_error($con);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>EXAMINATIONS OBSERVER SELECTION SYSTEM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="Resort Inn Responsive, Smartphone Compatible web template, Samsung, LG, Sony Ericsson, Motorola web design" />
    <script type="application/x-javascript">
        addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false);
        function hideURLbar(){ window.scrollTo(0,1); }
    </script>
    <link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
    <link href="css/font-awesome.css" rel="stylesheet">
    <link rel="stylesheet" href="css/chocolat.css" type="text/css" media="screen">
    <link href="css/easy-responsive-tabs.css" rel='stylesheet' type='text/css'/>
    <link rel="stylesheet" href="css/flexslider.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="css/jquery-ui.css" />
    <link href="css/style.css" rel="stylesheet" type="text/css" media="all" />
    <script type="text/javascript" src="js/modernizr-2.6.2.min.js"></script>
    <link href="//fonts.googleapis.com/css?family=Oswald:300,400,700" rel="stylesheet">
    <link href="//fonts.googleapis.com/css?family=Federo" rel="stylesheet">
    <link href="//fonts.googleapis.com/css?family=Lato:300,400,700,900" rel="stylesheet">
</head>
<body>
<div class="banner-top">
    <div class="social-bnr-agileits">
        <ul class="social-icons3">
            <li><a href="https://www.facebook.com/" class="fa fa-facebook icon-border facebook"></a></li>
            <li><a href="https://twitter.com/" class="fa fa-twitter icon-border twitter"></a></li>
            <li><a href="https://plus.google.com/u/0/" class="fa fa-google-plus icon-border googleplus"></a></li>
        </ul>
    </div>
    <div class="contact-bnr-w3-agile">
        <ul>
            <li><i class="fa fa-envelope" aria-hidden="true"></i><a href="mailto:info@example.com">INFO@IPT.go.tz</a></li>
            <li><i class="fa fa-phone" aria-hidden="true"></i>+255777370707</li>
            <li class="s-bar">
                <div class="search">
                    <input class="search_box" type="checkbox" id="search_box">
                    <label class="icon-search" for="search_box"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></label>
                    <div class="search_form">
                        <form action="#" method="post">
                            <input type="search" name="Search" placeholder=" " required=" " />
                            <input type="submit" value="Search">
                        </form>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    <div class="clearfix"></div>
</div>
<div class="w3_navigation">
    <div class="container">
        <nav class="navbar navbar-default">
            <div class="navbar-header navbar-left">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <h1><a class="navbar-brand" href="index.php">IPT <span>ZANZIBAR</span><p class="logo_w3l_agile_caption">Good education is the basis of development</p></a></h1>
            </div>
            <div class="collapse navbar-collapse navbar-right" id="bs-example-navbar-collapse-1">
                <nav class="menu__list">
                    <ul class="nav navbar-nav menu__list">
                        <li class="menu__item menu__item--current"><a href="index.php" class="menu__link">Home</a></li>
                        <li class="menu__item"><a href="#newapplication" class="menu__link scroll">New Application</a></li>
                        <li class="menu__item"><a href="#myapplication" class="menu__link scroll">My Application</a></li>
                        <li class="menu__item"><a href="#changepassword" class="menu__link scroll">Change Password</a></li>
                        <li class="menu__item"><a href="#contact" class="menu__link scroll">Contact</a></li>
                        <li class="menu__item"><a href="index.php" class="menu__link scroll">Logout</a></li>
                    </ul>
                </nav>
            </div
