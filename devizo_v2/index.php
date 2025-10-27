<?php
/**
 * DEVIZO v2.0 - Application Entry Point
 *
 * Main index file that initializes the application and handles routing
 */

// Define application constant
define('DEVIZO_APP', true);

// Load configuration
require_once __DIR__ . '/config.php';

// Load core classes
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Helpers.php';
require_once CORE_PATH . '/Session.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/PermissionManager.php';
require_once CORE_PATH . '/Router.php';

// Initialize session
Session::init();

// Error handling based on environment
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Set error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }

    $error = "Error [{$errno}]: {$errstr} in {$errfile} on line {$errline}";
    error_log($error);

    if (DEBUG_MODE) {
        echo "<pre>{$error}</pre>";
    } else {
        echo "A apărut o eroare. Vă rugăm să încercați din nou.";
    }

    return true;
});

// Set exception handler
set_exception_handler(function($exception) {
    $error = "Uncaught Exception: " . $exception->getMessage() . " in " .
             $exception->getFile() . " on line " . $exception->getLine();
    error_log($error);

    if (DEBUG_MODE) {
        echo "<pre>{$error}\n\n{$exception->getTraceAsString()}</pre>";
    } else {
        echo "A apărut o eroare. Vă rugăm să încercați din nou.";
    }
});

// Check if user is logged in
if (!Auth::check()) {
    // Not logged in - redirect to login
    redirect(url('login.php'));
}

// User is logged in - show appropriate dashboard
$user = Auth::user();

// Include header
require_once __DIR__ . '/views/header.php';

// Show dashboard based on role
if (Auth::isSuperAdmin()) {
    require_once __DIR__ . '/dashboards/super-admin.php';
} elseif (Auth::isMasterFirma()) {
    require_once __DIR__ . '/dashboards/master-firma.php';
} else {
    require_once __DIR__ . '/dashboards/utilizator.php';
}

// Include footer
require_once __DIR__ . '/views/footer.php';
