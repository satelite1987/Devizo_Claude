<?php
/**
 * DEVIZO v2.0 - Configuration File
 *
 * PRE-CONFIGURED for:
 * Database: devizo_nodex
 * User: devizo_Zeus
 * Password: Satelite1987!@#
 *
 * ⚠️ IMPORTANT: Change DEBUG_MODE to false in production!
 */

// Prevent direct access
if (!defined('DEVIZO_APP')) {
    define('DEVIZO_APP', true);
}

// ============================================
// DATABASE CONFIGURATION
// ============================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'devizo_nodex');
define('DB_USER', 'devizo_Zeus');
define('DB_PASS', 'Satelite1987!@#');
define('DB_CHARSET', 'utf8mb4');

// ============================================
// APPLICATION CONFIGURATION
// ============================================

// Site URL (change to your domain)
define('SITE_URL', 'https://www.devizo.ro');

// Application paths
define('APP_ROOT', __DIR__);
define('CORE_PATH', APP_ROOT . '/core');
define('MODULES_PATH', APP_ROOT . '/modules');
define('THEMES_PATH', APP_ROOT . '/themes');
define('UPLOADS_PATH', APP_ROOT . '/uploads');
define('CACHE_PATH', APP_ROOT . '/cache');
define('LOGS_PATH', APP_ROOT . '/logs');

// ============================================
// ENVIRONMENT
// ============================================

// Debug mode (set to false in production!)
define('DEBUG_MODE', true);

// Environment (development, staging, production)
define('ENVIRONMENT', 'development');

// Error reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// ============================================
// SECURITY
// ============================================

// Session configuration
define('SESSION_NAME', 'DEVIZO_SESSION');
define('SESSION_LIFETIME', 7200); // 2 hours in seconds
define('SESSION_COOKIE_SECURE', true); // true if using HTTPS
define('SESSION_COOKIE_HTTPONLY', true);
define('SESSION_COOKIE_SAMESITE', 'Strict');

// Password hashing
define('PASSWORD_COST', 12); // BCrypt cost (10-12 recommended)

// CSRF token
define('CSRF_TOKEN_NAME', 'devizo_csrf_token');
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour

// Login attempts
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes in seconds

// ============================================
// MODULES
// ============================================

// Enable/disable module system
define('MODULES_ENABLED', true);

// Auto-load modules on startup
define('MODULES_AUTOLOAD', true);

// Available modules (will be loaded from database)
$MODULES = [
    'devize' => 'Devize',
    'articole' => 'Articole',
    'parteneri' => 'Parteneri',
    'rapoarte' => 'Rapoarte',
    'curs-bnr' => 'Curs BNR',
    'export-contabilitate' => 'Export Contabilitate',
    'cereri-oferta' => 'Cereri Ofertă'
];

// ============================================
// LOCALIZATION
// ============================================

// Default language
define('DEFAULT_LANGUAGE', 'ro');

// Timezone
define('DEFAULT_TIMEZONE', 'Europe/Bucharest');
date_default_timezone_set(DEFAULT_TIMEZONE);

// Date/time formats
define('DATE_FORMAT', 'd.m.Y');
define('TIME_FORMAT', 'H:i');
define('DATETIME_FORMAT', 'd.m.Y H:i');

// Currency
define('DEFAULT_CURRENCY', 'EUR');
define('CURRENCY_SYMBOL', '€');
define('CURRENCY_DECIMALS', 2);

// ============================================
// FILE UPLOADS
// ============================================

// Maximum file upload size (in bytes)
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10 MB

// Allowed file extensions
define('ALLOWED_EXTENSIONS', [
    'images' => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'],
    'documents' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv'],
    'archives' => ['zip', 'rar', '7z']
]);

// ============================================
// CACHE
// ============================================

// Enable caching
define('CACHE_ENABLED', true);

// Cache lifetime (in seconds)
define('CACHE_LIFETIME', 3600); // 1 hour

// Cache method (file, redis, memcached)
define('CACHE_METHOD', 'file'); // 'file' for shared hosting

// ============================================
// EMAIL CONFIGURATION
// ============================================

// Email method (smtp, mail, sendmail)
define('EMAIL_METHOD', 'smtp');

// SMTP settings
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls'); // tls or ssl
define('SMTP_AUTH', true);
define('SMTP_USERNAME', 'your-email@example.com');
define('SMTP_PASSWORD', 'your-smtp-password');

// From email
define('EMAIL_FROM_ADDRESS', 'noreply@devizo.ro');
define('EMAIL_FROM_NAME', 'DEVIZO');

// ============================================
// API KEYS (External Integrations)
// ============================================

// ANAF API
define('ANAF_API_URL', 'https://webservicesp.anaf.ro/PlatitorTvaRest/api/v6/ws/tva');

// SmartBill API
define('SMARTBILL_API_URL', 'https://ws.smartbill.ro/SBORO/api');
define('SMARTBILL_API_KEY', ''); // Add your key

// Oblio API
define('OBLIO_API_URL', 'https://www.oblio.eu/api');
define('OBLIO_API_KEY', ''); // Add your key

// BNR Exchange Rate
define('BNR_API_URL', 'https://www.bnr.ro/nbrfxrates.xml');

// ============================================
// LOGGING
// ============================================

// Enable logging
define('LOGGING_ENABLED', true);

// Log level (debug, info, warning, error, critical)
define('LOG_LEVEL', DEBUG_MODE ? 'debug' : 'error');

// Log file
define('LOG_FILE', LOGS_PATH . '/app.log');

// Max log file size (in bytes)
define('MAX_LOG_SIZE', 10 * 1024 * 1024); // 10 MB

// ============================================
// PERFORMANCE
// ============================================

// Enable query caching
define('QUERY_CACHE_ENABLED', true);

// Enable output buffering
define('OUTPUT_BUFFERING', true);

// Compress output (gzip)
define('COMPRESS_OUTPUT', true);

// ============================================
// VERSIONING
// ============================================

define('APP_VERSION', '2.0.0');
define('APP_NAME', 'DEVIZO');
define('APP_DESCRIPTION', 'Sistem modular de gestionare devize și oferte');

// ============================================
// CONSTANTS
// ============================================

// User roles
define('ROLE_SUPER_ADMIN', 1);
define('ROLE_MASTER', 2);
define('ROLE_USER', 3);

// Module permissions
$PERMISSIONS = [
    'view' => 'Vizualizare',
    'create' => 'Creare',
    'edit' => 'Editare',
    'delete' => 'Ștergere',
    'export' => 'Export'
];

// ============================================
// AUTO-LOADER
// ============================================

// Register autoloader for core classes
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $file = CORE_PATH . '/' . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require_once $file;
        return true;
    }

    return false;
});

// ============================================
// ERROR HANDLER
// ============================================

// Custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }

    $error = "[" . date('Y-m-d H:i:s') . "] ";
    $error .= "Error [{$errno}]: {$errstr} in {$errfile} on line {$errline}\n";

    if (LOGGING_ENABLED) {
        error_log($error, 3, LOG_FILE);
    }

    if (DEBUG_MODE) {
        echo "<pre style='background:#f8d7da;padding:15px;border:1px solid #f5c6cb;border-radius:5px;'>";
        echo htmlspecialchars($error);
        echo "</pre>";
    }

    return true;
});

// Custom exception handler
set_exception_handler(function($exception) {
    $error = "[" . date('Y-m-d H:i:s') . "] ";
    $error .= "Exception: " . $exception->getMessage() . "\n";
    $error .= "File: " . $exception->getFile() . "\n";
    $error .= "Line: " . $exception->getLine() . "\n";
    $error .= "Trace: " . $exception->getTraceAsString() . "\n";

    if (LOGGING_ENABLED) {
        error_log($error, 3, LOG_FILE);
    }

    if (DEBUG_MODE) {
        echo "<pre style='background:#f8d7da;padding:15px;border:1px solid #f5c6cb;border-radius:5px;'>";
        echo htmlspecialchars($error);
        echo "</pre>";
    } else {
        echo "A apărut o eroare. Vă rugăm contactați administratorul.";
    }
});

// ============================================
// INITIALIZATION
// ============================================

// Start output buffering if enabled
if (OUTPUT_BUFFERING) {
    ob_start();
}

// Enable gzip compression if supported and enabled
if (COMPRESS_OUTPUT && extension_loaded('zlib')) {
    ini_set('zlib.output_compression', 'On');
    ini_set('zlib.output_compression_level', 6);
}

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Get configuration value
 */
function config($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

/**
 * Check if we're in debug mode
 */
function is_debug() {
    return DEBUG_MODE === true;
}

/**
 * Check if we're in production
 */
function is_production() {
    return ENVIRONMENT === 'production';
}

/**
 * Log message to file
 */
function log_message($message, $level = 'info') {
    if (!LOGGING_ENABLED) {
        return;
    }

    $log = "[" . date('Y-m-d H:i:s') . "] ";
    $log .= strtoupper($level) . ": ";
    $log .= $message . "\n";

    error_log($log, 3, LOG_FILE);
}

// ============================================
// END OF CONFIGURATION
// ============================================
