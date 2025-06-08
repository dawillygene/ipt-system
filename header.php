<style>
    .scroll-left{
        width: 65%;
        float: left;
    }

    .scroll-left marquee{
        padding: 20px 20px;
        color: #fff;
        text-transform: uppercase;
    }
</style>
<!-- New Header Start Here -->
<header>
    <div class="intro">
        <div class="intro-content">
            <img class="zanzi-flag" src="./images/znz_flag.gif" alt="" />
            <img class="kist-logo" src="./images/kist.webp" alt="" />

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
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="user_profile.php">Profile</a></li>
                <li><a href="change_password.php">Change Password</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    <?php }else{?>
    <nav class="nav-menu">
        <ul class="nav-menu-list">
            <li><a href="./index.php">Home</a></li>
            <li><a href="./login.php">Login</a></li>
            <li><a href="./register.php">Register</a></li>
        </ul>
    </nav>
    
    <?php } ?>

    <div class="section-title">
        <h3></h3>
    </div>
</header>