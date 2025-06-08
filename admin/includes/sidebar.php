<?php
require_once __DIR__ . '/../constants.php';
?>

<!-- Mobile Menu Button (Fixed Position) -->
<button id="mobile-menu-btn" 
        onclick="toggleMobileMenu()" 
        class="fixed top-4 left-4 z-50 p-3 bg-admin-primary text-white rounded-lg shadow-lg lg:hidden hover:bg-admin-secondary transition-colors duration-200">
    <i id="mobile-menu-icon" class="fas fa-bars text-lg"></i>
</button>

<!-- Mobile Overlay -->
<div id="mobile-overlay" 
     onclick="closeMobileMenu()" 
     class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>

<!-- Sidebar Component -->
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-admin-primary to-admin-secondary shadow-2xl transition-transform duration-300 ease-in-out transform -translate-x-full lg:translate-x-0">
    <!-- Logo Section -->
    <div class="flex items-center justify-center h-20 px-4 border-b border-green-600/30">
        <div class="flex items-center space-x-3">
            <img class="h-12 w-12 rounded-full shadow-lg ring-2 ring-white/20" 
                 src="../images/kist.webp" 
                 alt="KIST Logo" 
                 onerror="this.style.display='none'" />
            <div class="text-white">
                <h2 class="text-lg font-bold tracking-wide">IPT Admin</h2>
                <p class="text-xs text-green-200 opacity-80">Management Portal</p>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="mt-4 px-4 pb-20 overflow-y-auto">
        <ul class="space-y-1">
            <?php foreach (AdminUIConstants::NAV_ITEMS as $item): ?>
                <?php 
                $isActive = AdminUIConstants::isActiveRoute($item);
                $activeClasses = $isActive ? 'bg-green-700 border-r-4 border-green-300 text-white shadow-lg' : 'text-green-100';
                $hoverClasses = AdminUIConstants::getHoverClass();
                ?>
                <li>
                    <a href="<?php echo htmlspecialchars($item['url']); ?>" 
                       class="group flex items-center px-4 py-3 text-sm font-medium rounded-l-lg transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-md <?php echo $activeClasses; ?> <?php echo $hoverClasses; ?>"
                       onclick="closeMobileMenu()">
                        <i class="<?php echo htmlspecialchars($item['icon']); ?> w-5 h-5 mr-3 flex-shrink-0 transition-colors duration-200 <?php echo $isActive ? 'text-green-200' : 'text-green-300 group-hover:text-white'; ?>"></i>
                        <span class="truncate transition-colors duration-200 <?php echo $isActive ? 'text-white' : 'group-hover:text-white'; ?>">
                            <?php echo htmlspecialchars($item['name']); ?>
                        </span>
                        <?php if ($isActive): ?>
                            <span class="ml-auto w-2 h-2 bg-green-300 rounded-full animate-pulse"></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</aside>

<!-- Top Header -->
<header class="fixed top-0 left-0 lg:left-64 right-0 z-40 h-16 bg-gradient-to-r from-admin-secondary to-admin-accent shadow-lg">
    <div class="flex items-center justify-between h-full px-4 lg:px-6">
        <!-- Mobile Menu Space + Page Title -->
        <div class="flex items-center space-x-3">
            <!-- Space for mobile menu button -->
            <div class="w-12 lg:w-0"></div>
            <h1 class="text-xl font-semibold text-white">
                <?php
                foreach (AdminUIConstants::NAV_ITEMS as $item) {
                    if (AdminUIConstants::isActiveRoute($item)) {
                        echo htmlspecialchars($item['name']);
                        break;
                    }
                }
                ?>
            </h1>
        </div>

        <!-- User Actions -->
        <div class="flex items-center space-x-2 lg:space-x-4">
            <!-- Notifications -->
            <button class="relative p-2 text-green-100 hover:text-white hover:bg-green-700/50 rounded-lg transition-colors duration-200">
                <i class="fas fa-bell text-lg"></i>
                <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
            </button>
            
            <!-- Profile Dropdown -->
            <div class="relative">
                <button class="flex items-center space-x-2 px-3 py-2 text-green-100 hover:text-white hover:bg-green-700/50 rounded-lg transition-colors duration-200">
                    <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                    <span class="text-sm font-medium hidden sm:block">Admin</span>
                </button>
            </div>
            
            <!-- Logout -->
            <a href="admin_logout.php" 
               class="flex items-center space-x-2 px-3 py-2 text-green-100 hover:text-white hover:bg-red-600/80 rounded-lg transition-all duration-200">
                <i class="fas fa-sign-out-alt"></i>
                <span class="text-sm font-medium hidden sm:block">Logout</span>
            </a>
        </div>
    </div>
</header>

<script>
// Mobile menu toggle functionality
function toggleMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobile-overlay');
    const icon = document.getElementById('mobile-menu-icon');
    
    if (sidebar.classList.contains('-translate-x-full')) {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        icon.className = 'fas fa-times text-lg';
    } else {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        icon.className = 'fas fa-bars text-lg';
    }
}

// Close mobile menu when navigating to a page
function closeMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobile-overlay');
    const icon = document.getElementById('mobile-menu-icon');
    
    sidebar.classList.add('-translate-x-full');
    overlay.classList.add('hidden');
    icon.className = 'fas fa-bars text-lg';
}

// Handle window resize
window.addEventListener('resize', function() {
    if (window.innerWidth >= 1024) {
        closeMobileMenu();
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    if (window.innerWidth < 1024) {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.add('-translate-x-full');
    }
});
</script>