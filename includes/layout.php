<?php
/**
 * Base Layout Template for IPT S        <!-- Inter Font -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        <!-- CSRF Token for AJAX requests -->
        <meta name="csrf-token" content="<?php echo $csrf_token; ?>">, Provides consistent header, navigation, and layout structure
 */

// Include session management
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../db.php';

// Initialize session
SessionManager::init();

// Get current user info
$current_user = [
    'id' => SessionManager::getUserId(),
    'name' => SessionManager::getUserName(),
    'email' => SessionManager::getUserEmail(),
    'role' => SessionManager::getUserRole()
];

// Generate CSRF token
$csrf_token = SessionManager::generateCSRFToken();

// Get flash messages
$flash_messages = SessionManager::getFlashMessages();

/**
 * Render the page header with navigation
 */
function renderHeader($page_title = 'IPT System', $current_page = '') {
    global $current_user, $csrf_token;
    ?>
    <!DOCTYPE html>
    <html lang="en" class="h-full bg-gray-50">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($page_title); ?> - Industrial Training Practical System</title>
        
        <!-- Tailwind CSS -->
        <link href="<?php echo getAssetPath('css/tailwind.css'); ?>" rel="stylesheet">
        
        <!-- Inter Font -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        <!-- CSRF Token for AJAX requests -->
        <meta name="csrf-token" content="<?php echo $csrf_token; ?>">
        
        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="<?php echo getAssetPath('images/favicon.svg'); ?>">
    </head>
    <body class="h-full">
        <div class="min-h-full">
            <!-- Navigation -->
            <nav class="bg-white shadow border-b border-gray-200">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex flex-shrink-0 items-center">
                                <a href="<?php echo getHomeUrl(); ?>" class="flex items-center space-x-2">
                                    <div class="h-8 w-8 bg-primary-600 rounded-lg flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">IPT</span>
                                    </div>
                                    <span class="text-xl font-semibold text-gray-900">Training System</span>
                                </a>
                            </div>
                            
                            <!-- Main Navigation -->
                            <div class="hidden sm:-my-px sm:ml-6 sm:flex sm:space-x-8">
                                <?php echo renderMainNavigation($current_page); ?>
                            </div>
                        </div>
                        
                        <!-- User menu -->
                        <div class="hidden sm:ml-6 sm:flex sm:items-center">
                            <?php if (SessionManager::isLoggedIn()): ?>
                                <!-- Notifications -->
                                <button type="button" class="relative rounded-full bg-white p-1 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                    <span class="sr-only">View notifications</span>
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                    </svg>
                                </button>
                                
                                <!-- Profile dropdown -->
                                <div class="relative ml-3">
                                    <div class="flex items-center space-x-3">
                                        <span class="text-sm text-gray-700"><?php echo htmlspecialchars($current_user['name']); ?></span>
                                        <div class="relative">
                                            <button type="button" class="flex max-w-xs items-center rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2" id="user-menu-button">
                                                <span class="sr-only">Open user menu</span>
                                                <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center">
                                                    <span class="text-primary-700 font-medium text-sm">
                                                        <?php echo strtoupper(substr($current_user['name'], 0, 1)); ?>
                                                    </span>
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="flex items-center space-x-4">
                                    <a href="login.php" class="btn-outline">Login</a>
                                    <a href="register.php" class="btn-primary">Register</a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Mobile menu button -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button type="button" class="inline-flex items-center justify-center rounded-md bg-white p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2" id="mobile-menu-button">
                                <span class="sr-only">Open main menu</span>
                                <svg class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Mobile menu (hidden by default) -->
                <div class="sm:hidden hidden" id="mobile-menu">
                    <div class="space-y-1 pb-3 pt-2">
                        <?php echo renderMobileNavigation($current_page); ?>
                    </div>
                    <?php if (SessionManager::isLoggedIn()): ?>
                        <div class="border-t border-gray-200 pb-3 pt-4">
                            <div class="flex items-center px-4">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                                        <span class="text-primary-700 font-medium">
                                            <?php echo strtoupper(substr($current_user['name'], 0, 1)); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <div class="text-base font-medium text-gray-800"><?php echo htmlspecialchars($current_user['name']); ?></div>
                                    <div class="text-sm font-medium text-gray-500"><?php echo htmlspecialchars($current_user['email']); ?></div>
                                </div>
                            </div>
                            <div class="mt-3 space-y-1 px-2">
                                <a href="profile.php" class="block rounded-md px-3 py-2 text-base font-medium text-gray-500 hover:bg-gray-50 hover:text-gray-900">Profile</a>
                                <a href="settings.php" class="block rounded-md px-3 py-2 text-base font-medium text-gray-500 hover:bg-gray-50 hover:text-gray-900">Settings</a>
                                <a href="logout.php" class="block rounded-md px-3 py-2 text-base font-medium text-gray-500 hover:bg-gray-50 hover:text-gray-900">Sign out</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </nav>
            
            <!-- Flash messages -->
            <?php renderFlashMessages(); ?>
            
            <!-- Page content -->
            <main class="flex-1">
    <?php
}

/**
 * Render the page footer
 */
function renderFooter() {
    ?>
            </main>
        </div>
        
        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-sm text-gray-500">
                        © <?php echo date('Y'); ?> Industrial Training Practical System. All rights reserved.
                    </div>
                    <div class="mt-4 md:mt-0 flex space-x-6">
                        <a href="help.php" class="text-sm text-gray-500 hover:text-gray-900">Help</a>
                        <a href="privacy.php" class="text-sm text-gray-500 hover:text-gray-900">Privacy</a>
                        <a href="terms.php" class="text-sm text-gray-500 hover:text-gray-900">Terms</a>
                    </div>
                </div>
            </div>
        </footer>
        
        <!-- JavaScript -->
        <script src="<?php echo getAssetPath('js/app.js'); ?>"></script>
    </body>
    </html>
    <?php
}

/**
 * Helper functions
 */
function getAssetPath($path) {
    // Get the current script's directory relative to document root
    $currentDir = dirname($_SERVER['SCRIPT_NAME']);
    $basePath = '';
    
    // If we're in a subdirectory (like admin/), adjust the base path
    if ($currentDir !== '/') {
        $depth = substr_count(trim($currentDir, '/'), '/');
        $basePath = str_repeat('../', $depth);
    }
    
    return $basePath . ltrim($path, '/');
}

function getHomeUrl() {
    if (SessionManager::isLoggedIn()) {
        $role = SessionManager::getUserRole();
        switch ($role) {
            case 'admin':
                return 'admin/admin_dashboard.php';
            case 'student':
                return 'dashboard.php';
            case 'academic_supervisor':
            case 'industrial_supervisor':
                return 'supervisor_dashboard.php';
            default:
                return 'dashboard.php';
        }
    }
    return 'index.php';
}

function renderMainNavigation($current_page) {
    $nav_items = getNavigationItems();
    $html = '';
    
    foreach ($nav_items as $item) {
        $active_class = ($current_page === $item['page']) ? 'nav-link-active' : 'nav-link-inactive';
        $html .= sprintf(
            '<a href="%s" class="%s">%s</a>',
            htmlspecialchars($item['url']),
            $active_class,
            htmlspecialchars($item['label'])
        );
    }
    
    return $html;
}

function renderMobileNavigation($current_page) {
    $nav_items = getNavigationItems();
    $html = '';
    
    foreach ($nav_items as $item) {
        $active_class = ($current_page === $item['page']) 
            ? 'bg-primary-50 border-primary-500 text-primary-700' 
            : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700';
        
        $html .= sprintf(
            '<a href="%s" class="block border-l-4 px-3 py-2 text-base font-medium %s">%s</a>',
            htmlspecialchars($item['url']),
            $active_class,
            htmlspecialchars($item['label'])
        );
    }
    
    return $html;
}

function getNavigationItems() {
    if (!SessionManager::isLoggedIn()) {
        return [
            ['page' => 'home', 'url' => 'index.php', 'label' => 'Home'],
            ['page' => 'about', 'url' => 'about.php', 'label' => 'About'],
            ['page' => 'contact', 'url' => 'contact.php', 'label' => 'Contact']
        ];
    }
    
    $role = SessionManager::getUserRole();
    
    switch ($role) {
        case 'admin':
            return [
                ['page' => 'dashboard', 'url' => 'admin/admin_dashboard.php', 'label' => 'Dashboard'],
                ['page' => 'students', 'url' => 'admin/admin_students.php', 'label' => 'Students'],
                ['page' => 'supervisors', 'url' => 'admin/admin_supervisors.php', 'label' => 'Supervisors'],
                ['page' => 'applications', 'url' => 'admin/admin_applications.php', 'label' => 'Applications'],
                ['page' => 'reports', 'url' => 'admin/admin_reports.php', 'label' => 'Reports']
            ];
            
        case 'student':
            return [
                ['page' => 'dashboard', 'url' => 'dashboard.php', 'label' => 'Dashboard'],
                ['page' => 'profile', 'url' => 'user_profile.php', 'label' => 'Profile'],
                ['page' => 'applications', 'url' => 'user_applications.php', 'label' => 'Applications'],
                ['page' => 'reports', 'url' => 'user_reports.php', 'label' => 'Reports']
            ];
            
        case 'academic_supervisor':
        case 'industrial_supervisor':
            return [
                ['page' => 'dashboard', 'url' => 'supervisor_dashboard.php', 'label' => 'Dashboard'],
                ['page' => 'students', 'url' => 'supervisor_students.php', 'label' => 'My Students'],
                ['page' => 'feedback', 'url' => 'supervisor_feedback.php', 'label' => 'Feedback'],
                ['page' => 'evaluations', 'url' => 'supervisor_evaluations.php', 'label' => 'Evaluations']
            ];
            
        default:
            return [
                ['page' => 'dashboard', 'url' => 'dashboard.php', 'label' => 'Dashboard']
            ];
    }
}

function renderFlashMessages() {
    global $flash_messages;
    
    if (empty($flash_messages)) {
        return;
    }
    
    echo '<div class="fixed top-16 right-4 z-50 space-y-2" id="flash-messages">';
    
    foreach ($flash_messages as $message) {
        $type_class = match($message['type']) {
            'success' => 'alert-success',
            'error' => 'alert-error',
            'warning' => 'alert-warning',
            default => 'alert-info'
        };
        
        echo sprintf(
            '<div class="%s flex justify-between items-center animate-slide-in">
                <span>%s</span>
                <button type="button" class="ml-4 inline-flex text-sm" onclick="this.parentElement.remove()">
                    <span class="sr-only">Dismiss</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>',
            $type_class,
            htmlspecialchars($message['message'])
        );
    }
    
    echo '</div>';
    
    // Auto-hide flash messages after 5 seconds
    echo '<script>
        setTimeout(function() {
            const flashContainer = document.getElementById("flash-messages");
            if (flashContainer) {
                flashContainer.style.transition = "opacity 0.5s";
                flashContainer.style.opacity = "0";
                setTimeout(() => flashContainer.remove(), 500);
            }
        }, 5000);
    </script>';
}

function renderPageHeader($title, $subtitle = '', $actions = []) {
    echo '<div class="page-header">';
    echo '<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">';
    echo '<div class="md:flex md:items-center md:justify-between">';
    echo '<div class="min-w-0 flex-1">';
    echo '<h1 class="page-title">' . htmlspecialchars($title) . '</h1>';
    if ($subtitle) {
        echo '<p class="page-subtitle">' . htmlspecialchars($subtitle) . '</p>';
    }
    echo '</div>';
    
    if (!empty($actions)) {
        echo '<div class="mt-4 flex md:ml-4 md:mt-0">';
        foreach ($actions as $action) {
            echo sprintf(
                '<a href="%s" class="%s">%s</a>',
                htmlspecialchars($action['url']),
                $action['class'] ?? 'btn-primary ml-3',
                htmlspecialchars($action['label'])
            );
        }
        echo '</div>';
    }
    
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
?>
