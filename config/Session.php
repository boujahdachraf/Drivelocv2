<?php
class Session {
    private static $instance = null;
    private const SESSION_LIFETIME = 3600; // 1 hour
    
    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session parameters
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_samesite', 'Lax');
            
            session_set_cookie_params([
                'lifetime' => self::SESSION_LIFETIME,
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            session_start();
            
            // Regenerate session ID periodically for security
            if (!isset($_SESSION['last_regeneration'])) {
                $this->regenerateSession();
            } else if (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
                $this->regenerateSession();
            }
        }
    }
    
    public static function getInstance(): Session {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function regenerateSession(): void {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    public function set(string $key, $value): void {
        $_SESSION[$key] = $value;
    }
    
    public function get(string $key) {
        return $_SESSION[$key] ?? null;
    }
    
    public function remove(string $key): void {
        unset($_SESSION[$key]);
    }
    
    public function destroy(): void {
        session_destroy();
        $_SESSION = [];
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
    }
    
    public function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }
    
    public function isAdmin(): bool {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}
?>
