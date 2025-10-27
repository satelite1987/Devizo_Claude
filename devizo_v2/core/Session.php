<?php
/**
 * DEVIZO v2.0 - Session Management
 *
 * Secure session handling with configurable lifetime,
 * garbage collection, and security measures
 */

if (!defined('DEVIZO_APP')) {
    die('Direct access not permitted');
}

class Session {

    /**
     * Initialize session with security settings
     */
    public static function init() {
        // Session configuration
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_cookies', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);

        // Set session cookie parameters
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_name('DEVIZO_SESSION');
            session_start();
        }

        // Validate session
        self::validate();

        // Update session activity
        self::updateActivity();
    }

    /**
     * Validate session security
     */
    private static function validate() {
        // Initialize session security markers if not set
        if (!isset($_SESSION['_init'])) {
            $_SESSION['_init'] = true;
            $_SESSION['_ip'] = get_client_ip();
            $_SESSION['_ua'] = get_user_agent();
            $_SESSION['_created'] = time();
            $_SESSION['_last_activity'] = time();
            return;
        }

        // Check session timeout
        if (isset($_SESSION['_last_activity'])) {
            $inactive = time() - $_SESSION['_last_activity'];

            if ($inactive > SESSION_LIFETIME) {
                self::destroy();
                redirect(url('login.php', ['timeout' => '1']));
            }
        }

        // Validate IP address (optional - can be disabled for mobile users)
        if (defined('SESSION_VALIDATE_IP') && SESSION_VALIDATE_IP) {
            if (isset($_SESSION['_ip']) && $_SESSION['_ip'] !== get_client_ip()) {
                self::destroy();
                redirect(url('login.php', ['security' => '1']));
            }
        }

        // Validate user agent
        if (isset($_SESSION['_ua']) && $_SESSION['_ua'] !== get_user_agent()) {
            self::destroy();
            redirect(url('login.php', ['security' => '1']));
        }

        // Regenerate session ID periodically (every 30 minutes)
        if (isset($_SESSION['_created'])) {
            if (time() - $_SESSION['_created'] > 1800) {
                self::regenerate();
            }
        }
    }

    /**
     * Update session activity timestamp
     */
    private static function updateActivity() {
        $_SESSION['_last_activity'] = time();

        // Update database session activity if user is logged in
        if (Auth::check()) {
            Auth::updateSessionActivity();
        }
    }

    /**
     * Regenerate session ID
     */
    public static function regenerate() {
        session_regenerate_id(true);
        $_SESSION['_created'] = time();
    }

    /**
     * Destroy session completely
     */
    public static function destroy() {
        $_SESSION = [];

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        session_destroy();
    }

    /**
     * Set session value
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Get session value
     */
    public static function get($key, $default = null) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    /**
     * Check if session key exists
     */
    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session key
     */
    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Flash message - set
     */
    public static function flash($type, $message) {
        $_SESSION['_flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Flash message - get and clear
     */
    public static function getFlash() {
        if (isset($_SESSION['_flash'])) {
            $flash = $_SESSION['_flash'];
            unset($_SESSION['_flash']);
            return $flash;
        }
        return null;
    }

    /**
     * Check if flash message exists
     */
    public static function hasFlash() {
        return isset($_SESSION['_flash']);
    }

    /**
     * Get all session data (for debugging)
     */
    public static function all() {
        return $_SESSION;
    }

    /**
     * Clear all session data except security markers
     */
    public static function clear() {
        $keep = ['_init', '_ip', '_ua', '_created', '_last_activity'];
        $preserved = [];

        foreach ($keep as $key) {
            if (isset($_SESSION[$key])) {
                $preserved[$key] = $_SESSION[$key];
            }
        }

        $_SESSION = $preserved;
    }

    /**
     * Clean expired sessions from database
     */
    public static function cleanExpiredSessions() {
        try {
            $expiredTime = date('Y-m-d H:i:s', time() - SESSION_LIFETIME);

            db()->update('sesiuni', [
                'sfarsit' => date('Y-m-d H:i:s')
            ], 'ultima_activitate < ? AND sfarsit IS NULL', [$expiredTime]);

        } catch (Exception $e) {
            error_log("Failed to clean expired sessions: " . $e->getMessage());
        }
    }
}
