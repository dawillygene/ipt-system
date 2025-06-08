<?php
// Admin UI Constants
class AdminUIConstants {
    // Color Palette
    const COLORS = [
        'primary' => '#07442d',       
        'secondary' => '#206f56',      
        'accent' => '#0f7b5a',         
        'success' => '#10b981',        
        'warning' => '#f59e0b',        
        'danger' => '#ef4444',         
        'info' => '#3b82f6',           
        'light' => '#f8f9fc',          
        'white' => '#ffffff',          
        'gray' => [
            '50' => '#f9fafb',
            '100' => '#f3f4f6',
            '200' => '#e5e7eb',
            '300' => '#d1d5db',
            '400' => '#9ca3af',
            '500' => '#6b7280',
            '600' => '#4b5563',
            '700' => '#374151',
            '800' => '#1f2937',
            '900' => '#111827'
        ]
    ];
    
    const LAYOUT = [
        'sidebar_width' => 'w-64',         
        'sidebar_width_collapsed' => 'w-16', 
        'topbar_height' => 'h-16',         
        'content_padding' => 'p-6',
        'border_radius' => 'rounded-lg',
        'shadow' => 'shadow-lg'
    ];
    
    const NAV_ITEMS = [
        [
            'name' => 'Dashboard',
            'url' => './admin_dashboard.php',
            'icon' => 'fas fa-tachometer-alt',
            'active_keywords' => ['dashboard']
        ],
        [
            'name' => 'Applications',
            'url' => './admin_applications.php',
            'icon' => 'fas fa-file-alt',
            'active_keywords' => ['applications', 'application']
        ],
        [
            'name' => 'Students',
            'url' => './admin_students.php',
            'icon' => 'fas fa-user-graduate',
            'active_keywords' => ['students', 'student']
        ],
        [
            'name' => 'Supervisors',
            'url' => './admin_supervisors.php',
            'icon' => 'fas fa-user-tie',
            'active_keywords' => ['supervisors', 'supervisor']
        ],
        [
            'name' => 'Users',
            'url' => './admin_users.php',
            'icon' => 'fas fa-users',
            'active_keywords' => ['users', 'user']
        ],
        [
            'name' => 'Feedback',
            'url' => './admin_feedback.php',
            'icon' => 'fas fa-comments',
            'active_keywords' => ['feedback']
        ],
        [
            'name' => 'Evaluations',
            'url' => './admin_evaluations.php',
            'icon' => 'fas fa-clipboard-check',
            'active_keywords' => ['evaluations', 'evaluation']
        ],
        [
            'name' => 'Training Assignments',
            'url' => './admin_assignments.php',
            'icon' => 'fas fa-tasks',
            'active_keywords' => ['assignments', 'assignment', 'training']
        ],
        [
            'name' => 'Reports',
            'url' => './admin_reports.php',
            'icon' => 'fas fa-chart-bar',
            'active_keywords' => ['reports', 'report']
        ]
    ];
    

    public static function isActiveRoute($item) {
        $current_page = basename($_SERVER['PHP_SELF']);
        $item_page = basename($item['url']);
        
        // Direct match
        if ($current_page === $item_page) {
            return true;
        }
        
        // Keyword match
        foreach ($item['active_keywords'] as $keyword) {
            if (strpos(strtolower($current_page), strtolower($keyword)) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    public static function getActiveClass($item) {
        return self::isActiveRoute($item) ? 'bg-green-700 border-r-4 border-green-300' : '';
    }
    
    public static function getHoverClass() {
        return 'hover:bg-green-700 hover:bg-opacity-50 hover:border-r-4 hover:border-green-300';
    }
}
?>