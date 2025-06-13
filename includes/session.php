<?php
/**
 * Secure Session Management for IPT System
 * Handles session initialization, security, and management
 */

class SessionManager {
    
    public static function init() {
        // Session configuration
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', getenv('SESSION_SECURE') ?: 0);
        ini_set('session.cookie_samesite', getenv('SESSION_SAMESITE') ?: 'Strict');
        
        // Set session lifetime
        $lifetime = getenv('SESSION_LIFETIME') ?: 120;
        ini_set('session.gc_maxlifetime', $lifetime * 60);
        session_set_cookie_params($lifetime * 60);
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerate session ID periodically for security
        if (!isset($_SESSION['last_regeneration'])) {
            self::regenerateSession();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
            self::regenerateSession();
        }
        
        // Check session timeout
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity'] > ($lifetime * 60))) {
            self::destroy();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    public static function regenerateSession() {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    public static function login($user_id, $user_role, $user_name, $user_email) {
        // Regenerate session ID on login
        self::regenerateSession();
        
        // Set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_role'] = $user_role;
        $_SESSION['user_name'] = $user_name;
        $_SESSION['user_email'] = $user_email;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Generate CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        return true;
    }
    
    public static function logout() {
        self::destroy();
        header('Location: login.php');
        exit();
    }
    
    public static function destroy() {
        session_unset();
        session_destroy();
        
        // Delete session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    public static function requireLogin($redirect = 'login.php') {
        if (!self::isLoggedIn()) {
            header("Location: $redirect");
            exit();
        }
    }
    
    public static function requireRole($required_role, $redirect = 'unauthorized.php') {
        self::requireLogin();
        
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $required_role) {
            header("Location: $redirect");
            exit();
        }
    }
    
    public static function hasRole($role) {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }
    
    public static function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    public static function getUserRole() {
        return $_SESSION['user_role'] ?? null;
    }
    
    public static function getUserName() {
        return $_SESSION['user_name'] ?? null;
    }
    
    public static function getUserEmail() {
        return $_SESSION['user_email'] ?? null;
    }
    
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public static function setFlashMessage($type, $message) {
        $_SESSION['flash_messages'][] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    public static function getFlashMessages() {
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }
}
?>
