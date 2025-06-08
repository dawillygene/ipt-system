<?php

$conn = new mysqli("localhost", "root", "", "ipt-sys-test");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM students WHERE user_id = '$user_id'";
$result = $conn->query($sql);
?>

<div class="sidebar">
    <?php if ($result && $result->num_rows > 0):
        $row = $result->fetch_assoc();
    ?>
    <div class="profile">
        <?php if (!empty($row['profile_photo']) && file_exists($row['profile_photo'])): ?>
            <img src="<?= htmlspecialchars($row['profile_photo']) ?>"  alt="Profile Photo">
        <?php else: ?>
            <img src="./images/picture.png" alt="Default Photo">
        <?php endif; ?>
        <!--<img src="images/picture.png" alt="Profile Picture">!-->

        <?php else: ?>
            <img src="./images/picture.png" class="img-thumbnail" width="200" alt="Default Photo">
        <?php endif; ?>
    </div>
    <nav class="menu">
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="./apply_now.php">Apply Now</a></li>
            <li><a href="./user_applications.php">Applications</a></li>
            <li><a href="./user_reports.php">Reports</a></li>
            <li><a href="./user_profile.php">Profile</a></li>
        </ul>
    </nav>
</div>