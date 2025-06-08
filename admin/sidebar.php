<style>
    .sidebar{
        position: fixed;
        background: #07442d;
        width: 14%;
        left: 0;
        bottom: 0;
        top: 0;
    }

    .topbar{
        position: fixed;
        background: #206f56;
        width: 86%;
        height: 60px;
        left: 14%;
        right: 0;
        top: 0;
        box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15) !important;
    }

    .info{
        margin-left: 17%;
        width: 82%;
        margin-top: 100px;
    }

    .admin-logout{
        float: right;
        padding: 16px 30px;
        text-decoration: none;
        color: #fff;
        text-transform: uppercase;
        font-size: 20px;
    }

    .info{
        background: #f8f9fc;
        box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15) !important;
        border-radius: 4px;
        height: 100%;
        padding-top: 10px;
    }

    .info .data-container{
        background: #fff;
        padding: 20px 20px;
        height: 100%;
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;
        padding-bottom: 100px;
    }

    .info h2{
        padding: 0 20px;
    }

    table thead th{
        background: #0056b3 !important;
        color: #fff;
    }

    .sidebar h3{
        color: #fff;
        font-size: 30px;
        padding: 13px 0;
        margin: 0;
        text-align: center;
        border-bottom: 2px solid #fff1;
    }

    .sidebar ul{
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .sidebar ul li{
        padding: 12px 20px;
        width: 80%;
    }

    .sidebar ul li a{
        color: #fff;
        text-decoration: none;
        border-bottom: 1px solid #cccccc14;
        display: inline-block;
        width: 100%;
        border-radius: 3px;
        color: #fff;
    }

    .kist-logo{
        position: absolute;
        top: 18px;
        height: 120px;
        width: 170px;
        margin-top: 15px;
    }

    .sidebar-logo{
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
    }
</style>

<div class="sidebar">
    <div class="sidebar-logo">
        <img class="kist-logo" src="../images/kist.webp" alt="" />
    </div>
    <ul>
        <li><a href="./admin_dashboard.php">Dashboard</a></li>
        <li><a href="./admin_applications.php">Applications</a></li>
        <li><a href="./admin_students.php">Students</a></li>
        <li><a href="./admin_supervisors.php">Supervisors</a></li>
        <li><a href="./admin_users.php">Users</a></li>
        <li><a href="./admin_feedback.php">Feedback</a></li>
        <li><a href="./admin_evaluations.php">Evaluations</a></li>
        <li><a href="./admin_assignments.php">Training Assignments</a></li>
        <li><a href="./admin_reports.php">Reports</a></li>
    </ul>
</div>