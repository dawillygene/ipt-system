<?php
// Sidebar component for student pages - Static design
// Requires: $student_data, $student_name arrays/variables to be defined before including

// Get current page for highlighting active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>

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
                <a href="student_feedback.php" class="flex items-center px-4 py-3 <?php echo ($current_page == 'student_feedback.php') ? 'text-primary bg-primary/10 border-r-4 border-primary shadow-sm' : 'text-gray-700 hover:text-primary hover:bg-gray-50'; ?> rounded-lg transition-all duration-200 group text-sm font-medium">
                    <i class="fas fa-comments <?php echo ($current_page == 'student_feedback.php') ? 'text-primary' : 'text-gray-500 group-hover:text-primary'; ?> mr-3 w-5 text-center"></i>
                    <span>Feedback</span>
                    <?php if ($current_page == 'student_feedback.php'): ?>
                        <i class="fas fa-chevron-right ml-auto text-primary text-xs"></i>
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

<!-- Mobile Sidebar Overlay -->
<div id="mobile-sidebar" class="fixed inset-0 z-50 md:hidden hidden">
    <!-- Background overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-75" id="sidebar-overlay"></div>
    
    <!-- Sidebar panel -->
    <div class="absolute left-0 top-0 flex flex-col h-full w-80 max-w-xs bg-gradient-to-br from-slate-800 via-slate-700 to-slate-900 shadow-2xl">
        <!-- Close button for mobile -->
        <div class="absolute top-4 right-4 z-10">
            <button id="close-sidebar" class="flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-white bg-white/20 hover:bg-white/30 transition-colors">
                <i class="fas fa-times text-white text-lg"></i>
            </button>
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
    </div>
</div>

<style>
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

/* Mobile sidebar specific styling */
#mobile-sidebar {
    z-index: 9999 !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    background-color: rgba(0, 0, 0, 0.5) !important;
}

#mobile-sidebar.hidden {
    display: none !important;
}

#mobile-sidebar:not(.hidden) {
    display: flex !important;
}

/* Mobile sidebar panel animation */
#mobile-sidebar > div:last-child {
    transform: translateX(-100%);
    transition: transform 0.3s ease-in-out;
    background-color: white !important;
    width: 280px !important;
    height: 100vh !important;
    overflow-y: auto !important;
}

#mobile-sidebar:not(.hidden) > div:last-child {
    transform: translateX(0);
}

/* Additional mobile sidebar fixes */
#mobile-sidebar {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
}

/* Ensure overlay covers everything */
#sidebar-overlay {
    width: 100vw !important;
    height: 100vh !important;
}

/* Profile hover effects */
.ring-2 {
    transition: all 0.3s ease;
}

.ring-2:hover {
    transform: scale(1.05);
}

/* Scrollbar styling for sidebar */
#sidebar::-webkit-scrollbar {
    width: 6px;
}

#sidebar::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.05);
    border-radius: 3px;
}

#sidebar::-webkit-scrollbar-thumb {
    background: rgba(7, 68, 45, 0.2);
    border-radius: 3px;
}

#sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(7, 68, 45, 0.4);
}

/* Ensure content flows properly with static sidebar */
@media (min-width: 768px) {
    .main-content-area {
        transition: none;
    }
}

/* Mobile sidebar specific styles */
#mobile-sidebar {
    z-index: 9999 !important;
}

#mobile-sidebar.hidden {
    display: none !important;
}

#mobile-sidebar:not(.hidden) {
    display: flex !important;
}
</style>
