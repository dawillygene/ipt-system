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
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-br from-admin-primary via-admin-secondary to-admin-accent shadow-2xl transition-transform duration-300 ease-in-out transform -translate-x-full lg:translate-x-0 backdrop-blur-sm">
    <!-- Logo Section with Enhanced Design -->
    <div class="relative flex items-center justify-center h-20 px-4 border-b border-green-600/30 bg-gradient-to-r from-green-800/20 to-green-700/10 backdrop-blur-sm">
        <!-- Animated Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-2 left-4 w-8 h-8 bg-white/20 rounded-full animate-pulse"></div>
            <div class="absolute bottom-3 right-6 w-4 h-4 bg-green-300/30 rounded-full animate-bounce" style="animation-delay: 0.5s;"></div>
            <div class="absolute top-1/2 right-8 w-2 h-2 bg-green-200/40 rounded-full animate-ping" style="animation-delay: 1s;"></div>
        </div>
        
        <div class="relative flex items-center space-x-3 group">
            <!-- Enhanced Logo Container -->
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-r from-green-400 to-green-300 rounded-full blur-md opacity-70 group-hover:opacity-100 transition-opacity duration-300"></div>
                <img class="relative h-12 w-12 rounded-full shadow-xl ring-2 ring-white/30 transition-all duration-300 group-hover:ring-4 group-hover:ring-green-300/50 group-hover:scale-105" 
                     src="../images/kist.webp" 
                     alt="KIST Logo" 
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                <!-- Fallback Logo -->
                <div class="hidden h-12 w-12 rounded-full bg-gradient-to-br from-green-400 to-green-600 items-center justify-center shadow-xl ring-2 ring-white/30">
                    <i class="fas fa-graduation-cap text-white text-xl"></i>
                </div>
            </div>
            
            <!-- Enhanced Text Content -->
            <div class="text-white">
                <h2 class="text-lg font-bold tracking-wide bg-gradient-to-r from-white to-green-100 bg-clip-text text-transparent group-hover:from-green-100 group-hover:to-white transition-all duration-300">
                    IPT Admin
                </h2>
                <p class="text-xs text-green-200/90 opacity-80 font-medium tracking-wider uppercase">
                    Management Portal
                </p>
                <!-- Status Indicator -->
                <div class="flex items-center mt-1">
                    <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse mr-2"></div>
                    <span class="text-xs text-green-300/70 font-light">System Online</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Menu with Enhanced Design -->
    <nav class="mt-6 px-3 pb-20 overflow-y-auto scrollbar-thin scrollbar-thumb-green-700/50 scrollbar-track-transparent">
        <!-- Quick Actions Section -->
        <div class="mb-6 px-3">
            <h3 class="text-xs font-semibold text-green-300/70 uppercase tracking-wider mb-3">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-2">
                <button class="p-2 bg-green-700/30 hover:bg-green-600/40 rounded-lg transition-all duration-200 group">
                    <i class="fas fa-plus text-green-300 group-hover:text-white text-sm"></i>
                </button>
                <button class="p-2 bg-green-700/30 hover:bg-green-600/40 rounded-lg transition-all duration-200 group">
                    <i class="fas fa-search text-green-300 group-hover:text-white text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Main Navigation -->
        <div class="mb-4 px-3">
            <h3 class="text-xs font-semibold text-green-300/70 uppercase tracking-wider mb-3">Navigation</h3>
        </div>
        
        <ul class="space-y-2">
            <?php foreach (AdminUIConstants::NAV_ITEMS as $index => $item): ?>
                <?php 
                $isActive = AdminUIConstants::isActiveRoute($item);
                $activeClasses = $isActive ? 'bg-gradient-to-r from-green-600/80 to-green-700/60 border-r-4 border-green-300 text-white shadow-xl backdrop-blur-sm' : 'text-green-100/90';
                $hoverClasses = 'hover:bg-gradient-to-r hover:from-green-700/50 hover:to-green-600/40 hover:text-white hover:shadow-lg hover:backdrop-blur-sm';
                ?>
                <li class="relative group">
                    <!-- Animated background for active item -->
                    <?php if ($isActive): ?>
                        <div class="absolute inset-0 bg-gradient-to-r from-green-400/20 to-green-300/10 rounded-xl blur-sm"></div>
                    <?php endif; ?>
                    
                    <a href="<?php echo htmlspecialchars($item['url']); ?>" 
                       class="relative flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 ease-in-out transform hover:scale-[1.02] hover:translate-x-1 <?php echo $activeClasses; ?> <?php echo $hoverClasses; ?>"
                       onclick="closeMobileMenu()">
                        
                        <!-- Icon with enhanced styling -->
                        <div class="relative flex items-center justify-center w-10 h-8 mr-3">
                            <?php if ($isActive): ?>
                                <div class="absolute inset-0 bg-green-400/30 rounded-lg blur-sm"></div>
                            <?php endif; ?>
                            <i class="<?php echo htmlspecialchars($item['icon']); ?> relative text-lg transition-all duration-300 <?php echo $isActive ? 'text-green-200 drop-shadow-lg' : 'text-green-300/80 group-hover:text-white group-hover:scale-110'; ?>"></i>
                        </div>
                        
                        <!-- Text with better typography -->
                        <span class="relative flex-1 truncate font-medium transition-all duration-300 <?php echo $isActive ? 'text-white font-semibold' : 'group-hover:text-white group-hover:font-medium'; ?>">
                            <?php echo htmlspecialchars($item['name']); ?>
                        </span>
                        
                        <!-- Active indicator with animation -->
                        <?php if ($isActive): ?>
                            <div class="flex items-center space-x-2 ml-2">
                                <div class="w-2 h-2 bg-green-300 rounded-full animate-pulse shadow-lg"></div>
                                <div class="w-1 h-4 bg-green-300 rounded-full opacity-60"></div>
                            </div>
                        <?php else: ?>
                            <!-- Hover arrow indicator -->
                            <i class="fas fa-chevron-right text-xs text-green-400/0 group-hover:text-green-300/70 transition-all duration-300 group-hover:translate-x-1"></i>
                        <?php endif; ?>
                        
                        <!-- Subtle glow effect for active items -->
                        <?php if ($isActive): ?>
                            <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-green-500/10 to-green-400/5 pointer-events-none"></div>
                        <?php endif; ?>
                    </a>
                    
                    <!-- Badge for notifications (example for some items) -->
                    <?php if (in_array($item['name'], ['Applications', 'Reports'])): ?>
                        <div class="absolute top-2 right-2 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold shadow-lg animate-pulse">
                            <?php echo $item['name'] === 'Applications' ? '5' : '2'; ?>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Footer Section -->
        <div class="mt-8 px-3">
            <div class="border-t border-green-600/30 pt-6">
                <!-- System Status -->
                <div class="bg-green-800/20 rounded-xl p-4 mb-4 backdrop-blur-sm border border-green-600/20">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-medium text-green-300/90">System Status</span>
                        <div class="flex items-center space-x-1">
                            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                            <span class="text-xs text-green-400 font-medium">Online</span>
                        </div>
                    </div>
                    <div class="w-full bg-green-900/50 rounded-full h-1.5">
                        <div class="bg-gradient-to-r from-green-400 to-green-300 h-1.5 rounded-full animate-pulse" style="width: 95%"></div>
                    </div>
                </div>

                <!-- Help & Support -->
                <button class="w-full flex items-center justify-center space-x-2 p-3 bg-green-700/30 hover:bg-green-600/40 rounded-xl transition-all duration-200 text-green-200 hover:text-white group">
                    <i class="fas fa-question-circle group-hover:animate-bounce"></i>
                    <span class="text-sm font-medium">Help & Support</span>
                </button>
            </div>
        </div>
    </nav>
</aside>

<!-- Top Header with Enhanced Design -->
<header class="fixed top-0 left-0 lg:left-64 right-0 z-40 h-16 bg-gradient-to-r from-admin-secondary via-admin-accent to-admin-primary shadow-2xl backdrop-blur-md border-b border-green-600/20">
    <!-- Animated background elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-0 left-1/4 w-32 h-1 bg-gradient-to-r from-transparent via-green-300/30 to-transparent"></div>
        <div class="absolute top-0 right-1/3 w-24 h-1 bg-gradient-to-r from-transparent via-green-400/20 to-transparent"></div>
    </div>
    
    <div class="relative flex items-center justify-between h-full px-4 lg:px-6">
        <!-- Left Section: Mobile Menu Space + Page Title -->
        <div class="flex items-center space-x-4">
            <!-- Space for mobile menu button -->
            <div class="w-12 lg:w-0"></div>
            
            <!-- Page Title with Breadcrumb Style -->
            <div class="flex items-center space-x-3">
                <div class="hidden lg:flex items-center space-x-2 text-green-200/60">
                    <i class="fas fa-home text-sm"></i>
                    <i class="fas fa-chevron-right text-xs"></i>
                </div>
                
                <h1 class="text-xl font-bold text-white flex items-center space-x-2">
                    <?php
                    $currentPageTitle = '';
                    $currentPageIcon = '';
                    foreach (AdminUIConstants::NAV_ITEMS as $item) {
                        if (AdminUIConstants::isActiveRoute($item)) {
                            $currentPageTitle = $item['name'];
                            $currentPageIcon = $item['icon'];
                            break;
                        }
                    }
                    ?>
                    <i class="<?php echo htmlspecialchars($currentPageIcon); ?> text-green-200"></i>
                    <span class="bg-gradient-to-r from-white to-green-100 bg-clip-text text-transparent">
                        <?php echo htmlspecialchars($currentPageTitle); ?>
                    </span>
                </h1>
            </div>
        </div>

        <!-- Right Section: User Actions -->
        <div class="flex items-center space-x-3 lg:space-x-4">
            <!-- Search Button -->
            <button class="relative p-2.5 text-green-100 hover:text-white hover:bg-green-700/30 rounded-xl transition-all duration-200 group">
                <i class="fas fa-search text-lg group-hover:scale-110 transition-transform duration-200"></i>
            </button>
            
            <!-- Notifications with Enhanced Design -->
            <div class="relative">
                <button class="relative p-2.5 text-green-100 hover:text-white hover:bg-green-700/30 rounded-xl transition-all duration-200 group">
                    <i class="fas fa-bell text-lg group-hover:animate-swing transition-all duration-200"></i>
                    <!-- Enhanced notification badge -->
                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-gradient-to-r from-red-500 to-red-600 text-white text-xs rounded-full flex items-center justify-center font-bold shadow-lg animate-pulse ring-2 ring-white/30">
                        3
                    </span>
                </button>
                
                <!-- Notification dropdown (hidden by default) -->
                <div class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
                    <div class="p-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900">Recent Notifications</h3>
                    </div>
                    <div class="max-h-64 overflow-y-auto">
                        <!-- Sample notifications -->
                        <div class="p-3 hover:bg-gray-50 border-b border-gray-50">
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900">New application submitted</p>
                                    <p class="text-xs text-gray-500">2 minutes ago</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Settings -->
            <button class="relative p-2.5 text-green-100 hover:text-white hover:bg-green-700/30 rounded-xl transition-all duration-200 group">
                <i class="fas fa-cog text-lg group-hover:rotate-90 transition-transform duration-300"></i>
            </button>
            
            <!-- Profile Dropdown with Enhanced Design -->
            <div class="relative group">
                <button class="flex items-center space-x-3 px-4 py-2 text-green-100 hover:text-white hover:bg-green-700/30 rounded-xl transition-all duration-200">
                    <!-- Enhanced avatar -->
                    <div class="relative">
                        <div class="w-9 h-9 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center shadow-lg ring-2 ring-white/20 group-hover:ring-white/40 transition-all duration-200">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                        <!-- Online status indicator -->
                        <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-400 rounded-full border-2 border-white shadow-md"></div>
                    </div>
                    
                    <!-- User info -->
                    <div class="hidden sm:block text-left">
                        <div class="text-sm font-semibold">Admin User</div>
                        <div class="text-xs text-green-200/70">Administrator</div>
                    </div>
                    
                    <i class="fas fa-chevron-down text-xs group-hover:rotate-180 transition-transform duration-200"></i>
                </button>
                
                <!-- Profile dropdown menu -->
                <div class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
                    <div class="p-4 border-b border-gray-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">Admin User</div>
                                <div class="text-sm text-gray-500">admin@ipt.system</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="py-2">
                        <a href="#" class="flex items-center space-x-3 px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                            <i class="fas fa-user-cog w-4"></i>
                            <span>Profile Settings</span>
                        </a>
                        <a href="#" class="flex items-center space-x-3 px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                            <i class="fas fa-bell w-4"></i>
                            <span>Notifications</span>
                        </a>
                        <a href="#" class="flex items-center space-x-3 px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                            <i class="fas fa-shield-alt w-4"></i>
                            <span>Security</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Logout Button -->
            <a href="admin_logout.php" 
               class="flex items-center space-x-2 px-4 py-2 text-green-100 hover:text-white bg-red-600/20 hover:bg-red-600/80 rounded-xl transition-all duration-200 group border border-red-500/30 hover:border-red-400">
                <i class="fas fa-sign-out-alt group-hover:animate-pulse"></i>
                <span class="text-sm font-medium hidden sm:block">Logout</span>
            </a>
        </div>
    </div>
    
    <!-- Bottom border with gradient -->
    <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-green-400/30 to-transparent"></div>
</header>

<!-- Enhanced Sidebar Custom Styles -->
<style>
/* Custom scrollbar for sidebar */
.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(34, 197, 94, 0.3);
    border-radius: 2px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: rgba(34, 197, 94, 0.5);
}

/* Custom animations */
@keyframes swing {
    0%, 100% { transform: rotate(0deg); }
    15% { transform: rotate(15deg); }
    30% { transform: rotate(-10deg); }
    45% { transform: rotate(5deg); }
    60% { transform: rotate(-5deg); }
    75% { transform: rotate(2deg); }
}

.animate-swing {
    animation: swing 0.6s ease-in-out;
}

@keyframes fadeInSlide {
    0% {
        opacity: 0;
        transform: translateY(-10px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeInSlide {
    animation: fadeInSlide 0.3s ease-out;
}

/* Glassmorphism effect */
.glass-effect {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Hover glow effect */
.hover-glow:hover {
    box-shadow: 0 0 20px rgba(34, 197, 94, 0.3);
}

/* Smooth transitions */
* {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Enhanced focus states */
button:focus,
a:focus {
    outline: 2px solid rgba(34, 197, 94, 0.5);
    outline-offset: 2px;
}

/* Loading animation for status indicators */
@keyframes pulse-glow {
    0%, 100% {
        opacity: 1;
        box-shadow: 0 0 5px rgba(34, 197, 94, 0.5);
    }
    50% {
        opacity: 0.7;
        box-shadow: 0 0 10px rgba(34, 197, 94, 0.8);
    }
}

.pulse-glow {
    animation: pulse-glow 2s infinite;
}

/* Notification dropdown animation */
.notification-dropdown {
    animation: fadeInSlide 0.3s ease-out;
    transform-origin: top right;
}

/* Profile dropdown animation */
.profile-dropdown {
    animation: fadeInSlide 0.3s ease-out;
    transform-origin: top right;
}

/* Mobile menu improvements */
@media (max-width: 1023px) {
    #sidebar {
        backdrop-filter: blur(20px);
    }
    
    #mobile-overlay {
        backdrop-filter: blur(2px);
    }
}

/* Dark mode support (optional) */
@media (prefers-color-scheme: dark) {
    .notification-dropdown,
    .profile-dropdown {
        background: rgba(17, 24, 39, 0.95);
        border-color: rgba(75, 85, 99, 0.3);
    }
    
    .notification-dropdown h3,
    .profile-dropdown .font-semibold {
        color: #f9fafb;
    }
    
    .notification-dropdown p,
    .profile-dropdown .text-gray-700 {
        color: #d1d5db;
    }
}
</style>

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