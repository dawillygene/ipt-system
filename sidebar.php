<?php

$conn = new mysqli("localhost", "root", "", "ipt-sys-test");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM students WHERE user_id = '$user_id'";
$result = $conn->query($sql);

// Get user details for better display
$user_sql = "SELECT name, email FROM users WHERE id = '$user_id'";
$user_result = $conn->query($user_sql);
$user_data = $user_result->fetch_assoc();
?>

<div class="bg-gradient-to-br from-slate-800 via-slate-700 to-slate-900 shadow-2xl border-r border-slate-600">
    <?php if ($result && $result->num_rows > 0):
        $row = $result->fetch_assoc();
    ?>
    <!-- Enhanced Profile Section -->
    <div class="p-6 bg-gradient-to-r from-blue-600/20 to-purple-600/20 border-b border-slate-600">
        <div class="text-center mb-6">
            <div class="relative inline-block mb-4">
                <?php if (!empty($row['profile_photo']) && file_exists($row['profile_photo'])): ?>
                    <img src="<?= htmlspecialchars($row['profile_photo']) ?>" alt="Profile Photo" 
                         class="w-20 h-20 rounded-full border-4 border-blue-400/50 object-cover shadow-xl hover:scale-105 transition-transform duration-300">
                <?php else: ?>
                    <img src="./images/picture.png" alt="Default Photo" 
                         class="w-20 h-20 rounded-full border-4 border-blue-400/50 object-cover shadow-xl hover:scale-105 transition-transform duration-300">
                <?php endif; ?>
                <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-400 border-4 border-slate-800 rounded-full animate-pulse"></div>
            </div>
            
            <div class="text-white">
                <h3 class="text-lg font-bold text-slate-100 mb-1"><?= htmlspecialchars($user_data['name'] ?? 'Student') ?></h3>
                <p class="text-sm text-slate-300 mb-3"><?= htmlspecialchars($user_data['email'] ?? '') ?></p>
                <div class="inline-flex items-center bg-gradient-to-r from-blue-500 to-blue-600 text-white px-3 py-1 rounded-full text-xs font-medium shadow-lg">
                    <i class="fas fa-graduation-cap mr-2"></i>
                    <span>Active Student</span>
                </div>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div class="bg-slate-700/50 backdrop-blur-sm rounded-xl p-3 text-center hover:bg-slate-600/50 transition-all duration-300 hover:scale-105">
                <div class="text-blue-400 mb-1">
                    <i class="fas fa-file-alt text-lg"></i>
                </div>
                <div class="text-white text-lg font-bold">
                    <?php
                    $app_count = $conn->query("SELECT COUNT(*) as count FROM applications WHERE user_id = '$user_id'")->fetch_assoc()['count'];
                    echo $app_count;
                    ?>
                </div>
                <div class="text-slate-300 text-xs uppercase tracking-wide">Applications</div>
            </div>
            
            <div class="bg-slate-700/50 backdrop-blur-sm rounded-xl p-3 text-center hover:bg-slate-600/50 transition-all duration-300 hover:scale-105">
                <div class="text-green-400 mb-1">
                    <i class="fas fa-chart-line text-lg"></i>
                </div>
                <div class="text-white text-lg font-bold">
                    <?php
                    $report_count = $conn->query("SELECT COUNT(*) as count FROM reports WHERE user_id = '$user_id'")->fetch_assoc()['count'];
                    echo $report_count;
                    ?>
                </div>
                <div class="text-slate-300 text-xs uppercase tracking-wide">Reports</div>
            </div>
        </div>
        
        <!-- Profile Action -->
        <div class="text-center">
            <a href="./user_profile.php" class="inline-flex items-center bg-gradient-to-r from-red-500 to-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:from-red-600 hover:to-red-700 transition-all duration-300 hover:scale-105 shadow-lg hover:shadow-xl">
                <i class="fas fa-edit mr-2"></i>
                <span>Edit Profile</span>
            </a>
        </div>
    </div>

    <?php else: ?>
        <!-- Guest Profile Section -->
        <div class="p-6 bg-gradient-to-r from-gray-600/20 to-gray-700/20 border-b border-slate-600">
            <div class="text-center">
                <div class="relative inline-block mb-4">
                    <img src="./images/picture.png" class="w-20 h-20 rounded-full border-4 border-gray-400/50 object-cover shadow-xl" alt="Default Photo">
                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-gray-400 border-4 border-slate-800 rounded-full"></div>
                </div>
                
                <div class="text-white">
                    <h3 class="text-lg font-bold text-slate-100 mb-1"><?= htmlspecialchars($user_data['name'] ?? 'Guest User') ?></h3>
                    <p class="text-sm text-slate-300 mb-3"><?= htmlspecialchars($user_data['email'] ?? '') ?></p>
                    <div class="inline-flex items-center bg-gradient-to-r from-gray-500 to-gray-600 text-white px-3 py-1 rounded-full text-xs font-medium">
                        <i class="fas fa-user mr-2"></i>
                        <span>New User</span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Enhanced Navigation Menu -->
    <nav class="p-4 space-y-2">
        <!-- Dashboard -->
        <a href="dashboard.php" class="group flex items-center px-4 py-3 text-slate-300 rounded-xl hover:bg-gradient-to-r hover:from-blue-600/20 hover:to-purple-600/20 hover:text-white transition-all duration-300 hover:scale-105 hover:shadow-lg">
            <div class="flex items-center justify-center w-8 h-8 mr-3 bg-blue-500/20 rounded-lg group-hover:bg-blue-500/30 transition-colors duration-300">
                <i class="fas fa-tachometer-alt text-blue-400 group-hover:text-blue-300"></i>
            </div>
            <span class="font-medium">Dashboard</span>
            <div class="ml-auto w-2 h-2 bg-transparent rounded-full group-hover:bg-green-400 transition-colors duration-300"></div>
        </a>
        
        <!-- Apply Now -->
        <a href="./apply_now.php" class="group flex items-center px-4 py-3 text-slate-300 rounded-xl hover:bg-gradient-to-r hover:from-green-600/20 hover:to-emerald-600/20 hover:text-white transition-all duration-300 hover:scale-105 hover:shadow-lg">
            <div class="flex items-center justify-center w-8 h-8 mr-3 bg-green-500/20 rounded-lg group-hover:bg-green-500/30 transition-colors duration-300">
                <i class="fas fa-plus-circle text-green-400 group-hover:text-green-300"></i>
            </div>
            <span class="font-medium">Apply Now</span>
            <div class="ml-auto w-2 h-2 bg-transparent rounded-full group-hover:bg-green-400 transition-colors duration-300"></div>
        </a>
        
        <!-- Applications -->
        <a href="./user_applications.php" class="group flex items-center px-4 py-3 text-slate-300 rounded-xl hover:bg-gradient-to-r hover:from-purple-600/20 hover:to-pink-600/20 hover:text-white transition-all duration-300 hover:scale-105 hover:shadow-lg">
            <div class="flex items-center justify-center w-8 h-8 mr-3 bg-purple-500/20 rounded-lg group-hover:bg-purple-500/30 transition-colors duration-300">
                <i class="fas fa-file-alt text-purple-400 group-hover:text-purple-300"></i>
            </div>
            <span class="font-medium">Applications</span>
            <div class="ml-auto w-2 h-2 bg-transparent rounded-full group-hover:bg-green-400 transition-colors duration-300"></div>
        </a>
        
        <!-- Reports -->
        <a href="./user_reports.php" class="group flex items-center px-4 py-3 text-slate-300 rounded-xl hover:bg-gradient-to-r hover:from-orange-600/20 hover:to-red-600/20 hover:text-white transition-all duration-300 hover:scale-105 hover:shadow-lg">
            <div class="flex items-center justify-center w-8 h-8 mr-3 bg-orange-500/20 rounded-lg group-hover:bg-orange-500/30 transition-colors duration-300">
                <i class="fas fa-chart-bar text-orange-400 group-hover:text-orange-300"></i>
            </div>
            <span class="font-medium">Reports</span>
            <div class="ml-auto w-2 h-2 bg-transparent rounded-full group-hover:bg-green-400 transition-colors duration-300"></div>
        </a>
        
        <!-- Profile -->
        <a href="./user_profile.php" class="group flex items-center px-4 py-3 text-slate-300 rounded-xl hover:bg-gradient-to-r hover:from-indigo-600/20 hover:to-blue-600/20 hover:text-white transition-all duration-300 hover:scale-105 hover:shadow-lg">
            <div class="flex items-center justify-center w-8 h-8 mr-3 bg-indigo-500/20 rounded-lg group-hover:bg-indigo-500/30 transition-colors duration-300">
                <i class="fas fa-user-circle text-indigo-400 group-hover:text-indigo-300"></i>
            </div>
            <span class="font-medium">Profile</span>
            <div class="ml-auto w-2 h-2 bg-transparent rounded-full group-hover:bg-green-400 transition-colors duration-300"></div>
        </a>
    </nav>
    
    <!-- Logout Button -->
    <div class="p-4 mt-auto border-t border-slate-600">
        <a href="logout.php" class="flex items-center justify-center w-full bg-gradient-to-r from-red-500 to-red-600 text-white px-4 py-3 rounded-xl font-medium hover:from-red-600 hover:to-red-700 transition-all duration-300 hover:scale-105 shadow-lg hover:shadow-xl">
            <i class="fas fa-sign-out-alt mr-2"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<script>
// Add active class to current menu item
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();
    const menuLinks = document.querySelectorAll('nav a');
    
    menuLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && (href === currentPage || href.includes(currentPage))) {
            link.classList.remove('text-slate-300');
            link.classList.add('bg-gradient-to-r', 'from-blue-600/30', 'to-purple-600/30', 'text-white', 'shadow-lg');
            const indicator = link.querySelector('.ml-auto');
            if (indicator) {
                indicator.classList.remove('bg-transparent');
                indicator.classList.add('bg-green-400');
            }
        }
    });
});
</script>