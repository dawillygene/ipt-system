<?php
// Sidebar component for student pages - Pure CSS Solution
// Requires: $student_data, $student_name arrays/variables to be defined before including

// Get current page for highlighting active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Pure CSS Mobile Sidebar Toggle (Hidden Checkbox) -->
<input type="checkbox" id="mobile-sidebar-toggle" class="hidden">

<!-- Enhanced Static Sidebar - Project Colors -->
<div id="sidebar" class="hidden md:flex md:flex-shrink-0">
    <div class="flex flex-col w-64 h-screen bg-gradient-to-br from-slate-800 via-slate-700 to-slate-900 shadow-2xl border-r border-slate-600 static top-0 left-0 z-30">
        <!-- Enhanced Profile Section -->
        <div class="flex-shrink-0 p-6 bg-gradient-to-br from-primary/30 to-secondary/30 border-b border-slate-600">
            <div class="text-center">
                <div class="relative inline-block mb-4">
                    <?php 
                    $profile_photo = $student_data['profile_photo'] ?? null;
                    if (!empty($profile_photo) && file_exists($profile_photo)): 
                    ?>
                        <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile Photo" 
                             class="w-20 h-20 rounded-full border-4 border-primary/20 object-cover shadow-lg hover:shadow-xl transition-all duration-300 ring-2 ring-primary/10">
                    <?php else: ?>
                        <div class="w-20 h-20 rounded-full border-4 border-primary/20 bg-gradient-to-br from-primary/10 via-secondary/10 to-accent/10 flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-300 ring-2 ring-primary/10">
                            <i class="fas fa-user-graduate text-primary text-2xl"></i>
                        </div>
                    <?php endif; ?>
                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 border-3 border-white rounded-full shadow-lg">
                        <i class="fas fa-check text-white text-xs absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"></i>
                    </div>
                </div>
                
                <div class="text-white">
                    <h3 class="text-lg font-bold text-slate-100 mb-1"><?php echo htmlspecialchars($student_name); ?></h3>
                    <?php if (!empty($student_data['reg_number'])): ?>
                        <p class="text-sm text-slate-300 mb-3"><?php echo htmlspecialchars($student_data['reg_number']); ?></p>
                    <?php endif; ?>
                    <div class="inline-flex items-center bg-gradient-to-r from-primary to-secondary text-white px-4 py-2 rounded-full text-sm font-medium shadow-lg hover:shadow-xl transition-all duration-300">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        <span>Active Student</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navigation Menu -->
        <div class="flex-1 p-4 overflow-y-auto">
            <nav class="space-y-2">
                <!-- Dashboard -->
                <a href="student_dashboard.php" class="flex items-center px-4 py-3 <?php echo ($current_page == 'student_dashboard.php') ? 'text-white bg-gradient-to-r from-primary to-secondary shadow-lg' : 'text-slate-300 hover:text-white hover:bg-slate-700/50'; ?> rounded-lg transition-all duration-200 group text-sm font-medium">
                    <i class="fas fa-tachometer-alt <?php echo ($current_page == 'student_dashboard.php') ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?> mr-3 w-5 text-center"></i>
                    <span>Dashboard</span>
                    <?php if ($current_page == 'student_dashboard.php'): ?>
                        <i class="fas fa-chevron-right ml-auto text-white text-xs"></i>
                    <?php endif; ?>
                </a>
                
                <!-- Profile -->
                <a href="student_profile.php" class="flex items-center px-4 py-3 <?php echo ($current_page == 'student_profile.php') ? 'text-white bg-gradient-to-r from-primary to-secondary shadow-lg' : 'text-slate-300 hover:text-white hover:bg-slate-700/50'; ?> rounded-lg transition-all duration-200 group text-sm font-medium">
                    <i class="fas fa-user <?php echo ($current_page == 'student_profile.php') ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?> mr-3 w-5 text-center"></i>
                    <span>Profile</span>
                    <?php if ($current_page == 'student_profile.php'): ?>
                        <i class="fas fa-chevron-right ml-auto text-white text-xs"></i>
                    <?php endif; ?>
                </a>
                
                <!-- Applications -->
                <a href="student_applications.php" class="flex items-center px-4 py-3 <?php echo ($current_page == 'student_applications.php') ? 'text-white bg-gradient-to-r from-primary to-secondary shadow-lg' : 'text-slate-300 hover:text-white hover:bg-slate-700/50'; ?> rounded-lg transition-all duration-200 group text-sm font-medium">
                    <i class="fas fa-file-contract <?php echo ($current_page == 'student_applications.php') ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?> mr-3 w-5 text-center"></i>
                    <span>Applications</span>
                    <?php if ($current_page == 'student_applications.php'): ?>
                        <i class="fas fa-chevron-right ml-auto text-white text-xs"></i>
                    <?php endif; ?>
                </a>
                
                <!-- Reports -->
                <a href="student_reports.php" class="flex items-center px-4 py-3 <?php echo ($current_page == 'student_reports.php') ? 'text-white bg-gradient-to-r from-primary to-secondary shadow-lg' : 'text-slate-300 hover:text-white hover:bg-slate-700/50'; ?> rounded-lg transition-all duration-200 group text-sm font-medium">
                    <i class="fas fa-chart-line <?php echo ($current_page == 'student_reports.php') ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?> mr-3 w-5 text-center"></i>
                    <span>Reports</span>
                    <?php if ($current_page == 'student_reports.php'): ?>
                        <i class="fas fa-chevron-right ml-auto text-white text-xs"></i>
                    <?php endif; ?>
                </a>
                
                <!-- Feedback -->
                <a href="student_feedback.php" class="flex items-center px-4 py-3 <?php echo ($current_page == 'student_feedback.php') ? 'text-white bg-gradient-to-r from-primary to-secondary shadow-lg' : 'text-slate-300 hover:text-white hover:bg-slate-700/50'; ?> rounded-lg transition-all duration-200 group text-sm font-medium">
                    <i class="fas fa-comments <?php echo ($current_page == 'student_feedback.php') ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?> mr-3 w-5 text-center"></i>
                    <span>Feedback</span>
                    <?php if ($current_page == 'student_feedback.php'): ?>
                        <i class="fas fa-chevron-right ml-auto text-white text-xs"></i>
                    <?php endif; ?>
                </a>
                
                <!-- Documents -->
                <a href="student_documents.php" class="flex items-center px-4 py-3 <?php echo ($current_page == 'student_documents.php') ? 'text-white bg-gradient-to-r from-primary to-secondary shadow-lg' : 'text-slate-300 hover:text-white hover:bg-slate-700/50'; ?> rounded-lg transition-all duration-200 group text-sm font-medium">
                    <i class="fas fa-folder-open <?php echo ($current_page == 'student_documents.php') ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?> mr-3 w-5 text-center"></i>
                    <span>Documents</span>
                    <?php if ($current_page == 'student_documents.php'): ?>
                        <i class="fas fa-chevron-right ml-auto text-white text-xs"></i>
                    <?php endif; ?>
                </a>
            </nav>
            
            <!-- Bottom Section -->
            <div class="mt-8 pt-6 border-t border-slate-600">
                <div class="bg-gradient-to-r from-primary/20 to-secondary/20 rounded-lg p-4 border border-primary/30">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-primary/30 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-graduation-cap text-white text-lg"></i>
                        </div>
                        <h4 class="text-sm font-semibold text-slate-100 mb-1">IPT System</h4>
                        <p class="text-xs text-slate-300 mb-3">Industrial Practical Training Management</p>
                        <a href="change_password.php" class="inline-flex items-center text-xs text-primary hover:text-secondary font-medium">
                            <i class="fas fa-cog mr-1"></i>
                            Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Sidebar Overlay with Pure CSS Control -->
<div id="mobile-sidebar" class="css-mobile-sidebar">
    <!-- Background overlay -->
    <label for="mobile-sidebar-toggle" class="fixed inset-0 bg-black bg-opacity-75 cursor-pointer" id="sidebar-overlay"></label>
    
    <!-- Sidebar panel with enhanced styling -->
    <div class="sidebar-panel fixed left-0 top-0 flex flex-col h-full w-80 max-w-xs bg-gradient-to-br from-slate-800 via-slate-700 to-slate-900 shadow-2xl z-50">
        <!-- Close button for mobile -->
        <div class="absolute top-4 right-4 z-10">
            <label for="mobile-sidebar-toggle" class="flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-white bg-white/20 hover:bg-white/30 transition-colors cursor-pointer">
                <i class="fas fa-times text-white text-lg"></i>
            </label>
        </div>

        <!-- Mobile Profile Section -->
        <div class="flex-shrink-0 p-6 bg-gradient-to-br from-primary/30 to-secondary/30 border-b border-slate-600">
            <div class="text-center">
                <div class="relative inline-block mb-4">
                    <?php if (!empty($profile_photo) && file_exists($profile_photo)): ?>
                        <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile Photo" 
                             class="w-20 h-20 rounded-full border-4 border-primary/40 object-cover shadow-lg">
                    <?php else: ?>
                        <div class="w-20 h-20 rounded-full border-4 border-primary/40 bg-gradient-to-br from-primary/30 via-secondary/30 to-accent/30 flex items-center justify-center shadow-lg">
                            <i class="fas fa-user-graduate text-white text-2xl"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="text-white">
                    <h3 class="text-lg font-bold text-slate-100 mb-1"><?php echo htmlspecialchars($student_name); ?></h3>
                    <?php if (!empty($student_data['reg_number'])): ?>
                        <p class="text-sm text-slate-300"><?php echo htmlspecialchars($student_data['reg_number']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="flex-1 h-0 pt-5 pb-4 overflow-y-auto">
            <nav class="mt-5 px-4 space-y-2">
                <a href="student_dashboard.php" class="<?php echo ($current_page == 'student_dashboard.php') ? 'bg-gradient-to-r from-primary to-secondary text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700 hover:text-white'; ?> group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200">
                    <i class="fas fa-tachometer-alt mr-3 text-center w-5"></i>
                    Dashboard
                </a>
                <a href="student_profile.php" class="<?php echo ($current_page == 'student_profile.php') ? 'bg-gradient-to-r from-primary to-secondary text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700 hover:text-white'; ?> group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200">
                    <i class="fas fa-user mr-3 text-center w-5"></i>
                    Profile
                </a>
                <a href="student_applications.php" class="<?php echo ($current_page == 'student_applications.php') ? 'bg-gradient-to-r from-primary to-secondary text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700 hover:text-white'; ?> group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200">
                    <i class="fas fa-file-contract mr-3 text-center w-5"></i>
                    Applications
                </a>
                <a href="student_reports.php" class="<?php echo ($current_page == 'student_reports.php') ? 'bg-gradient-to-r from-primary to-secondary text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700 hover:text-white'; ?> group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200">
                    <i class="fas fa-chart-line mr-3 text-center w-5"></i>
                    Reports
                </a>
                <a href="student_feedback.php" class="<?php echo ($current_page == 'student_feedback.php') ? 'bg-gradient-to-r from-primary to-secondary text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700 hover:text-white'; ?> group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200">
                    <i class="fas fa-comments mr-3 text-center w-5"></i>
                    Feedback
                </a>
                <a href="student_documents.php" class="<?php echo ($current_page == 'student_documents.php') ? 'bg-gradient-to-r from-primary to-secondary text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700 hover:text-white'; ?> group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200">
                    <i class="fas fa-folder-open mr-3 text-center w-5"></i>
                    Documents
                </a>
                <a href="change_password.php" class="<?php echo ($current_page == 'change_password.php') ? 'bg-gradient-to-r from-primary to-secondary text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700 hover:text-white'; ?> group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200">
                    <i class="fas fa-cog mr-3 text-center w-5"></i>
                    Settings
                </a>
            </nav>
        </div>

        <!-- Mobile Navigation Auto-close Links -->
        <div class="border-t border-slate-600 p-4">
            <label for="mobile-sidebar-toggle" class="block w-full text-center px-4 py-2 bg-red-500/20 hover:bg-red-500/30 text-red-300 rounded-lg transition-colors cursor-pointer">
                <i class="fas fa-times mr-2"></i>
                Close Menu
            </label>
        </div>
    </div>
</div>

<style>
/* ==================== PURE CSS MOBILE SIDEBAR SOLUTION ==================== */

/* Hide the checkbox that controls the sidebar */
#mobile-sidebar-toggle {
    display: none !important;
}

/* Enhanced static sidebar styling */
#sidebar {
    z-index: 30;
}

/* Smooth transitions for sidebar links */
#sidebar nav a {
    position: relative;
    overflow: hidden;
}

#sidebar nav a::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(7, 68, 45, 0.1), transparent);
    transition: left 0.5s;
}

#sidebar nav a:hover::before {
    left: 100%;
}

/* PURE CSS MOBILE SIDEBAR IMPLEMENTATION */
/* Default state: Mobile sidebar is hidden */
.css-mobile-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100vw;
    height: 100vh;
    z-index: 9999;
    
    /* Initially hidden - using visibility and opacity for smooth transitions */
    visibility: hidden;
    opacity: 0;
    
    /* Smooth transition */
    transition: visibility 0.3s ease, opacity 0.3s ease;
    
    /* Hide on desktop */
    display: none;
}

/* Show mobile sidebar only on mobile screens */
@media (max-width: 767px) {
    .css-mobile-sidebar {
        display: block;
    }
}

/* When checkbox is checked: Show the mobile sidebar */
#mobile-sidebar-toggle:checked ~ .css-mobile-sidebar {
    visibility: visible !important;
    opacity: 1 !important;
}

/* Sidebar panel default state (off-screen) */
.css-mobile-sidebar .sidebar-panel {
    transform: translateX(-100%);
    transition: transform 0.3s ease-in-out;
    width: 280px;
    max-width: 90vw;
    height: 100vh;
    overflow-y: auto;
    background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #0f172a 100%);
    z-index: 9999;
}

/* When checkbox is checked: Slide the sidebar panel in */
#mobile-sidebar-toggle:checked ~ .css-mobile-sidebar .sidebar-panel {
    transform: translateX(0) !important;
}

/* Background overlay styling */
.css-mobile-sidebar #sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: 9998;
}

/* Body scroll lock when sidebar is open */
#mobile-sidebar-toggle:checked ~ * body,
#mobile-sidebar-toggle:checked ~ body {
    overflow: hidden !important;
}

/* Alternative body scroll lock method */
html:has(#mobile-sidebar-toggle:checked) body {
    overflow: hidden !important;
}

/* Close button styling with better visibility */
.css-mobile-sidebar label[for="mobile-sidebar-toggle"] {
    background: rgba(255, 255, 255, 0.2) !important;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.css-mobile-sidebar label[for="mobile-sidebar-toggle"]:hover {
    background: rgba(255, 255, 255, 0.3) !important;
    transform: scale(1.05);
}

/* Navigation link auto-close functionality */
/* When a navigation link is clicked, it will naturally navigate to the new page, closing the sidebar */
.css-mobile-sidebar nav a {
    /* Add visual feedback for link interaction */
    transition: all 0.2s ease;
}

.css-mobile-sidebar nav a:active {
    transform: scale(0.98);
    background-color: rgba(7, 68, 45, 0.3) !important;
}

/* Profile hover effects */
.ring-2 {
    transition: all 0.3s ease;
}

.ring-2:hover {
    transform: scale(1.05);
}

/* Scrollbar styling for sidebar */
#sidebar::-webkit-scrollbar,
.css-mobile-sidebar .sidebar-panel::-webkit-scrollbar {
    width: 6px;
}

#sidebar::-webkit-scrollbar-track,
.css-mobile-sidebar .sidebar-panel::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.05);
    border-radius: 3px;
}

#sidebar::-webkit-scrollbar-thumb,
.css-mobile-sidebar .sidebar-panel::-webkit-scrollbar-thumb {
    background: rgba(7, 68, 45, 0.2);
    border-radius: 3px;
}

#sidebar::-webkit-scrollbar-thumb:hover,
.css-mobile-sidebar .sidebar-panel::-webkit-scrollbar-thumb:hover {
    background: rgba(7, 68, 45, 0.4);
}

/* Ensure content flows properly with static sidebar */
@media (min-width: 768px) {
    .main-content-area {
        transition: none;
    }
    
    /* Hide mobile sidebar completely on desktop */
    .css-mobile-sidebar {
        display: none !important;
    }
}

/* Enhanced focus styles for accessibility */
label[for="mobile-sidebar-toggle"]:focus-within {
    outline: 2px solid #07442d;
    outline-offset: 2px;
}

/* Smooth animation improvements */
.css-mobile-sidebar * {
    box-sizing: border-box;
}

/* Fallback for browsers that don't support :has() */
@supports not selector(:has(*)) {
    .css-mobile-sidebar-open {
        overflow: hidden !important;
    }
}

/* Print styles - hide sidebar in print */
@media print {
    #sidebar,
    .css-mobile-sidebar {
        display: none !important;
    }
}

/* High contrast mode adjustments */
@media (prefers-contrast: high) {
    .css-mobile-sidebar .sidebar-panel {
        border: 2px solid #ffffff;
    }
    
    .css-mobile-sidebar #sidebar-overlay {
        background-color: rgba(0, 0, 0, 0.8);
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .css-mobile-sidebar,
    .css-mobile-sidebar .sidebar-panel,
    .css-mobile-sidebar * {
        transition: none !important;
        animation: none !important;
    }
}
</style>
