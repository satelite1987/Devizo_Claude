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

// Safe define - only define if not already defined
function safe_define($name, $value) {
    if (!defined($name)) {
        define($name, $value);
    }
}

// Prevent direct access
if (!defined('DEVIZO_APP')) {
    define('DEVIZO_APP', true);
}

// ============================================
// DATABASE CONFIGURATION
// ============================================

safe_define('DB_HOST', 'localhost');
safe_define('DB_NAME', 'devizo_nodex');
safe_define('DB_USER', 'devizo_Zeus');
safe_define('DB_PASS', 'Satelite1987!@#');
safe_define('DB_CHARSET', 'utf8mb4');

// ============================================
// APPLICATION CONFIGURATION
// ============================================

// Site URL (change to your domain)
safe_define('SITE_URL', 'https://www.devizo.ro');

// Application paths
safe_define('APP_ROOT', __DIR__);
safe_define('CORE_PATH', APP_ROOT . '/core');
safe_define('MODULES_PATH', APP_ROOT . '/modules');
safe_define('THEMES_PATH', APP_ROOT . '/themes');
safe_define('UPLOADS_PATH', APP_ROOT . '/uploads');
safe_define('CACHE_PATH', APP_ROOT . '/cache');
safe_define('LOGS_PATH', APP_ROOT . '/logs');

// ============================================
// ENVIRONMENT
// ============================================

// Debug mode (set to false in production!)
safe_define('DEBUG_MODE', true);

// Environment (development, staging, production)
safe_define('ENVIRONMENT', 'development');

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
safe_define('SESSION_NAME', 'DEVIZO_SESSION');
safe_define('SESSION_LIFETIME', 7200); // 2 hours in seconds
safe_define('SESSION_COOKIE_SECURE', true); // true if using HTTPS
safe_define('SESSION_COOKIE_HTTPONLY', true);
safe_define('SESSION_COOKIE_SAMESITE', 'Strict');

// Password hashing
safe_define('PASSWORD_COST', 12); // BCrypt cost (10-12 recommended)

// CSRF token
safe_define('CSRF_TOKEN_NAME', 'devizo_csrf_token');
safe_define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour

// Login attempts
safe_define('MAX_LOGIN_ATTEMPTS', 5);
safe_define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes in seconds

// ============================================
// MODULES
// ============================================

// Enable/disable module system
safe_define('MODULES_ENABLED', true);

// Auto-load modules on startup
safe_define('MODULES_AUTOLOAD', true);

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
safe_define('DEFAULT_LANGUAGE', 'ro');

// Timezone
safe_define('DEFAULT_TIMEZONE', 'Europe/Bucharest');
date_default_timezone_set(DEFAULT_TIMEZONE);

// Date/time formats
safe_define('DATE_FORMAT', 'd.m.Y');
safe_define('TIME_FORMAT', 'H:i');
safe_define('DATETIME_FORMAT', 'd.m.Y H:i');

// Currency
safe_define('DEFAULT_CURRENCY', 'EUR');
safe_define('CURRENCY_SYMBOL', '€');
safe_define('CURRENCY_DECIMALS', 2);

// ============================================
// FILE UPLOADS
// ============================================

// Maximum file upload size (in bytes)
safe_define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10 MB

// Allowed file extensions
safe_define('ALLOWED_EXTENSIONS', [
    'images' => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'],
    'documents' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv'],
    'archives' => ['zip', 'rar', '7z']
]);

// ============================================
// CACHE
// ============================================

// Enable caching
safe_define('CACHE_ENABLED', true);

// Cache lifetime (in seconds)
safe_define('CACHE_LIFETIME', 3600); // 1 hour

// Cache method (file, redis, memcached)
safe_define('CACHE_METHOD', 'file'); // 'file' for shared hosting

// ============================================
// EMAIL CONFIGURATION
// ============================================

// Email method (smtp, mail, sendmail)
safe_define('EMAIL_METHOD', 'smtp');

// SMTP settings
safe_define('SMTP_HOST', 'smtp.example.com');
safe_define('SMTP_PORT', 587);
safe_define('SMTP_SECURE', 'tls'); // tls or ssl
safe_define('SMTP_AUTH', true);
safe_define('SMTP_USERNAME', 'your-email@example.com');
safe_define('SMTP_PASSWORD', 'your-smtp-password');

// From email
safe_define('EMAIL_FROM_ADDRESS', 'noreply@devizo.ro');
safe_define('EMAIL_FROM_NAME', 'DEVIZO');

// ============================================
// API KEYS (External Integrations)
// ============================================

// ANAF API
safe_define('ANAF_API_URL', 'https://webservicesp.anaf.ro/PlatitorTvaRest/api/v6/ws/tva');

// SmartBill API
safe_define('SMARTBILL_API_URL', 'https://ws.smartbill.ro/SBORO/api');
safe_define('SMARTBILL_API_KEY', ''); // Add your key

// Oblio API
safe_define('OBLIO_API_URL', 'https://www.oblio.eu/api');
safe_define('OBLIO_API_KEY', ''); // Add your key

// BNR Exchange Rate
safe_define('BNR_API_URL', 'https://www.bnr.ro/nbrfxrates.xml');

// ============================================
// LOGGING
// ============================================

// Enable logging
safe_define('LOGGING_ENABLED', true);

// Log level (debug, info, warning, error, critical)
safe_define('LOG_LEVEL', DEBUG_MODE ? 'debug' : 'error');

// Log file
safe_define('LOG_FILE', LOGS_PATH . '/app.log');

// Max log file size (in bytes)
safe_define('MAX_LOG_SIZE', 10 * 1024 * 1024); // 10 MB

// ============================================
// PERFORMANCE
// ============================================

// Enable query caching
safe_define('QUERY_CACHE_ENABLED', true);

// Enable output buffering
safe_define('OUTPUT_BUFFERING', true);

// Compress output (gzip)
safe_define('COMPRESS_OUTPUT', true);

// ============================================
// VERSIONING
// ============================================

safe_define('APP_VERSION', '2.0.0');
safe_define('APP_NAME', 'DEVIZO');
safe_define('APP_DESCRIPTION', 'Sistem modular de gestionare devize și oferte');

// ============================================
// CONSTANTS
// ============================================

// User roles
safe_define('ROLE_SUPER_ADMIN', 1);
safe_define('ROLE_MASTER', 2);
safe_define('ROLE_USER', 3);

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
// AUTO-CREATE REQUIRED DIRECTORIES
// ============================================

$requiredDirs = [
    UPLOADS_PATH,
    CACHE_PATH,
    LOGS_PATH
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// ============================================
// END OF CONFIGURATION
// ============================================
