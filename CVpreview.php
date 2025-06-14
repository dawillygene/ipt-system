<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPT - CVPreview</title>
    
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
    
    <main>
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    
                </div>
                
                <div class="col-md-9">
                
                </div>
            </div>
        </div>
    </main>


<!-- Content -->
<div class="container-fluid">
    <div class="row">
        <!-- Left Partition -->
        <div class="col-md-3">
            <!-- Profile Picture and Navigation Links -->
            <div class="profile">
                <img src="images/picture.png" alt="Profile Picture">
                <nav class="menu">
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="PersonalDetails.php">Personal Details</a></li>
                        <!-- Add other navigation links as needed -->
                    </ul>
                </nav>
            </div>
        </div>
        <!-- Divider -->
        <div class="col-md-1 divider"></div>
        <!-- Right Partition -->
        <div class="col-md-8">
            <!-- Your main content goes here -->
        </div>
    </div>
</div>

<!-- Contact Section -->
<section class="contact-section" id="contact">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h4>Contact Us</h4>
                <!-- Contact Form -->
                <form method="post" name="contactForm" id="contactForm">
                    <div class="form-group">
                        <label for="name">Full Name:</label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number:</label>
                        <input type="text" class="form-control" name="phone" id="phone" placeholder="Enter phone number starting with +255 (e.g., +255753225961)" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address:</label>
                        <input type="email" class="form-control" name="email" id="email" required>
                    </div>
                    <button type="submit" name="sub" class="btn btn-primary">Send Now</button>
                </form>
                <!-- End Contact Form -->
            </div>
            <div class="col-md-6">
                <!-- Contact Info -->
                <h4>Connect With Us</h4>
                <p><strong>Phone :</strong> +255777370707</p>
                <p><strong>Email :</strong> <a href="mailto:info@bmz.go.tz">INFO@IPT.go.tz</a></p>
                <p><strong>Address :</strong> VUGA ZANZIBAR</p>
                <!-- End Contact Info -->
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <p>IPT ZANZIBAR. All Rights Reserved | Design by <a href="#">SALMA NASSOR MELIMELI</a></p>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    
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
