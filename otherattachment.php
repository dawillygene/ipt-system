<?php
include('db.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>EXAMINATIONS OBSERVER SELECTION SYSTEM</title>
<!-- for-mobile-apps -->
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="keywords" content="Resort Inn Responsive , Smartphone Compatible web template , Samsung, LG, Sony Ericsson, Motorola web design" />
<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false);
    function hideURLbar(){ window.scrollTo(0,1); } </script>
<!-- //for-mobile-apps -->
<link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
<link href="css/font-awesome.css" rel="stylesheet"> 
<link rel="stylesheet" href="css/chocolat.css" type="text/css" media="screen">
<link href="css/easy-responsive-tabs.css" rel='stylesheet' type='text/css'/>
<link rel="stylesheet" href="css/flexslider.css" type="text/css" media="screen" property="" />
<link rel="stylesheet" href="css/jquery-ui.css" />
<link href="css/style.css" rel="stylesheet" type="text/css" media="all" />
<script type="text/javascript" src="js/modernizr-2.6.2.min.js"></script>
<!--fonts-->
<link href="//fonts.googleapis.com/css?family=Oswald:300,400,700" rel="stylesheet">
<link href="//fonts.googleapis.com/css?family=Federo" rel="stylesheet">
<link href="//fonts.googleapis.com/css?family=Lato:300,400,700,900" rel="stylesheet">
<!--//fonts-->
</head>
<body>
<!-- header -->
<div class="banner-top">
      <div class="social-bnr-agileits">
        <ul class="social-icons3">
                <li><a href="https://www.facebook.com/" class="fa fa-facebook icon-border facebook"> </a></li>
                <li><a href="https://twitter.com/" class="fa fa-twitter icon-border twitter"> </a></li>
                <li><a href="https://plus.google.com/u/0/" class="fa fa-google-plus icon-border googleplus"> </a></li> 
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
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse navbar-right" id="bs-example-navbar-collapse-1">
          <nav class="menu__list">
            <ul class="nav navbar-nav menu__list">
              <li class="menu__item menu__item--current"><a href="" class="menu__link">Home</a></li>
              <li class="menu__item"><a href="#newapplication" class="menu__link scroll">New Application</a></li>
              <li class="menu__item"><a href="#myapplication" class="menu__link scroll">My Application</a></li>
              <li class="menu__item"><a href="#changepassword" class="menu__link scroll">Change Password</a></li>
              <li class="menu__item"><a href="#contact" class="menu__link scroll">Contact</a></li>
              <li class="menu__item"><a href="index.php" class="menu__link scroll">Logout</a></li>
            </ul>
          </nav>
        </div>
      </nav>

    </div>
  </div>

<!--\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\-->

<div class="container">
    <div class="partition" id="leftPartition">
<!--start left button-->
  <div class="container">
    <!-- Profile Picture -->
    <div class="profile">
      <img src="images/picture.png" alt="Profile Picture">
    
    <!-- Navigation Links -->
    <nav class="menu">
      <ul>
   <li><a href="index.php">Home</a></li>
        <li><a href="PersonalDetails.php">Personal Details</a></li>
        <li><a href="ContactDetails.php">Contact Details</a></li>
        <li><a href="AcademicQualification.php">Academic Qualification</a></li>
        <li><a href="LanguageProficiency.php">Language Proficiency</a></li>
        <li><a href="Referees.php">Referees</a></li>
        <li><a href="OtherAttachment.php">Other Attachments</a></li>
        <li><a href="CVpreview.php">CV Preview</a></li>
        <li><a href="Declaration.php">Declaration</a></li>
           </ul>
    </nav>
    </div>
  </div>
  <!--end left button-->

    </div>
    <div class="divider" id="divider"></div>
    <div class="partition" id="rightPartition">
       <form id="attachmentForm" enctype="multipart/form-data">
  <h2>Attach Document</h2><br>
    <label for="document">Attach letter:</label>
    <input type="file" id="document" name="document" accept=".pdf,.doc,.docx"><br>

    <labelfor="document">Attach cv:</label>
<input type="file" id="document" name="document" accept=".pdf,.doc,.docx">
    <input type="submit" value="Attach Document"> </form>

    </div>
<!--////////////////////////////////////////////////////-->
<!-- contact -->
<section class="contact-w3ls" id="contact">
  <div class="container">
    <div class="col-lg-6 col-md-6 col-sm-6 contact-w3-agile2" data-aos="flip-left">
      <div class="contact-agileits">
        <h4>Contact Us</h4>
        <p class="contact-agile2">Sign Up For Our News Letters</p>
        <form  method="post" name="sentMessage" id="contactForm" >
          <div class="control-group form-group">                         <label class="contact-p1">Full Name:</label>
                        <input type="text" class="form-control" name="name" id="name" required >
                        <p class="help-block"></p>
                   
                </div>  
                <div class="control-group form-group">
                    
                        <label class="contact-p1">Phone Number:</label>
                        <input type="text" class="form-control" name="phone" id="phone" placeholder="Enter phone number starting with +255 (e.g., +255753225961)" required >
          <p class="help-block"></p>
        
                </div>
                <div class="control-group form-group">
                    
                        <label class="contact-p1">Email Address:</label>
                        <input type="email" class="form-control" name="email" id="email" required >
          <p class="help-block"></p>
        
                </div>
                
                <input type="submit" name="sub" value="Send Now" class="btn btn-primary"> 
    </form>
    <?php
    if(isset($_POST['sub']))
    {
      $name =$_POST['name'];
      $phone = $_POST['phone'];
      $email = $_POST['email'];
      $approval = "Not Allowed";
      $sql = "INSERT INTO `contact`(`fullname`, `phoneno`, `email`,`cdate`,`approval`) VALUES ('$name','$phone','$email',now(),'$approval')" ;
      
      
      if(mysqli_query($con,$sql))
      echo"OK";
      
    }
    ?>
  </div>
</div>
<div class="col-lg-6 col-md-6 col-sm-6 contact-w3-agile1" data-aos="flip-right">
  <h4>Connect With Us</h4>
  <p class="contact-agile1"><strong>Phone :</strong>+255777370707</p>
  <p class="contact-agile1"><strong>Email :</strong> <a href="mailto:name@example.com">INFO@MBNZ.go.tz</a></p>
  <p class="contact-agile1"><strong>Address :</strong> VUGA ZANZIBAR</p>
                            
  <div class="social-bnr-agileits footer-icons-agileinfo">
    <ul class="social-icons3">
            <li><a href="#" class="fa fa-facebook icon-border facebook"> </a></li>
            <li><a href="#" class="fa fa-twitter icon-border twitter"> </a></li>
            <li><a href="#" class="fa fa-google-plus icon-border googleplus"> </a></li> 
            
          </ul>
  </div>
  <iframe src="https://th.bing.com/th/id/R.e4addc8b8d18c65027d97e5a4b61fa80?rik=6uSphACB2wuvHQ&riu=http%3a%2f%2fmaps.maphill.com%2fatlas%2f6s05-39e10%2f3d-maps%2fsatellite-map%2fsatellite-3d-map-of-6s05-39e10.jpg&ehk=0Xd%2bGmh56H%2fJlM6aRh6LTRKUhq2SrnGs9A3PRR%2bM094%3d&risl=&pid=ImgRaw&r=0" frameborder="0" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>
</div>
<div class="clearfix"></div>
</div>
</section>
<!-- /contact -->
      <div class="copy">
             <p>IPT ZANZIBAR. All Rights Reserved | Design by <a href="index.php">SALMA NASSOR MELIMELI</a> </p>
        </div>
<!--/footer -->
<!-- js -->
<script type="text/javascript" src="js/jquery-2.1.4.min.js"></script>
<!-- contact form -->
<script src="js/jqBootstrapValidation.js"></script>
<!-- /contact form -->  
<!-- Calendar -->

<script src="js/jquery-ui.js"></script>
<script>
    $(function() {
    $( "#datepicker,#datepicker1,#datepicker2,#datepicker3" ).datepicker();
    });
</script>
<!-- //Calendar -->
<!-- gallery popup -->
<link rel="stylesheet" href="css/swipebox.css">
        <script src="js/jquery.swipebox.min.js"></script> 
          <script type="text/javascript">
            jQuery(function($) {
              $(".swipebox").swipebox();
            });
          </script>
<!-- //gallery popup -->
<!-- start-smoth-scrolling -->
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
<!-- start-smoth-scrolling -->
<!-- flexSlider -->

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
  <!-- //flexSlider -->
  <script src="js/responsiveslides.min.js"></script>
  <script>
        // You can also use "$(window).load(function() {"
        $(function () {
          // Slideshow 4
          $("#slider4").responsiveSlides({
          auto: true,
          pager:true,
          nav:false,
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
    
<!--search-bar-->


<script src="js/main.js"></script>  

<!--//search-bar-->
<!--tabs-->
<script src="js/easy-responsive-tabs.js"></script>
<script>
$(document).ready(function () {
$('#horizontalTab').easyResponsiveTabs({
type: '

