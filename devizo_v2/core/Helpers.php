<?php
/**
 * DEVIZO v2.0 - Helper Functions
 *
 * Utility functions used throughout the application
 */

if (!defined('DEVIZO_APP')) {
    die('Direct access not permitted');
}

// ============================================
// STRING FUNCTIONS
// ============================================

/**
 * Sanitize input string
 */
function clean($str) {
    if (is_array($str)) {
        return array_map('clean', $str);
    }
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

/**
 * Escape output
 */
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize HTML
 */
function sanitize_html($html) {
    return strip_tags($html, '<p><br><strong><em><u><a><ul><ol><li>');
}

/**
 * Generate random string
 */
function random_string($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Generate slug from string
 */
function slug($str) {
    $str = mb_strtolower($str, 'UTF-8');

    $replacements = [
        'ă' => 'a', 'â' => 'a', 'î' => 'i', 'ș' => 's', 'ț' => 't',
        'Ă' => 'a', 'Â' => 'a', 'Î' => 'i', 'Ș' => 's', 'Ț' => 't'
    ];

    $str = strtr($str, $replacements);
    $str = preg_replace('/[^a-z0-9]+/', '-', $str);
    $str = trim($str, '-');

    return $str;
}

/**
 * Truncate string
 */
function truncate($str, $length = 100, $suffix = '...') {
    if (mb_strlen($str) <= $length) {
        return $str;
    }
    return mb_substr($str, 0, $length) . $suffix;
}

// ============================================
// VALIDATION FUNCTIONS
// ============================================

/**
 * Validate email
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate CUI (Romanian Tax ID)
 */
function is_valid_cui($cui) {
    $cui = preg_replace('/[^0-9]/', '', $cui);
    return strlen($cui) >= 6 && strlen($cui) <= 10;
}

/**
 * Validate phone number
 */
function is_valid_phone($phone) {
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    return strlen($phone) >= 10;
}

/**
 * Validate IBAN
 */
function is_valid_iban($iban) {
    $iban = strtoupper(preg_replace('/[^A-Z0-9]/', '', $iban));
    return preg_match('/^RO[0-9]{2}[A-Z]{4}[A-Z0-9]{16}$/', $iban);
}

/**
 * Validate date
 */
function is_valid_date($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// ============================================
// DATE/TIME FUNCTIONS
// ============================================

/**
 * Format date to Romanian format
 */
function format_date($date, $format = DATE_FORMAT) {
    if (empty($date)) {
        return '';
    }

    if (is_string($date)) {
        $date = new DateTime($date);
    }

    return $date->format($format);
}

/**
 * Format date to Romanian format (dd.mm.yyyy)
 */
function format_date_ro($date) {
    return format_date($date, 'd.m.Y');
}

/**
 * Format datetime to Romanian format
 */
function format_datetime_ro($datetime) {
    return format_date($datetime, 'd.m.Y H:i');
}

/**
 * Get current date
 */
function now($format = 'Y-m-d H:i:s') {
    return date($format);
}

/**
 * Get today's date
 */
function today($format = 'Y-m-d') {
    return date($format);
}

/**
 * Convert date from Romanian format to MySQL format
 */
function date_to_mysql($date) {
    if (empty($date)) {
        return null;
    }

    $d = DateTime::createFromFormat('d.m.Y', $date);
    return $d ? $d->format('Y-m-d') : null;
}

/**
 * Get relative time (e.g., "2 hours ago")
 */
function time_ago($datetime) {
    if (is_string($datetime)) {
        $datetime = new DateTime($datetime);
    }

    $now = new DateTime();
    $diff = $now->diff($datetime);

    if ($diff->y > 0) {
        return $diff->y . ' ' . ($diff->y == 1 ? 'an' : 'ani');
    } else if ($diff->m > 0) {
        return $diff->m . ' ' . ($diff->m == 1 ? 'lună' : 'luni');
    } else if ($diff->d > 0) {
        return $diff->d . ' ' . ($diff->d == 1 ? 'zi' : 'zile');
    } else if ($diff->h > 0) {
        return $diff->h . ' ' . ($diff->h == 1 ? 'oră' : 'ore');
    } else if ($diff->i > 0) {
        return $diff->i . ' ' . ($diff->i == 1 ? 'minut' : 'minute');
    } else {
        return 'acum';
    }
}

// ============================================
// NUMBER/CURRENCY FUNCTIONS
// ============================================

/**
 * Format price
 */
function format_price($amount, $decimals = 2, $currency = DEFAULT_CURRENCY) {
    $formatted = number_format($amount, $decimals, ',', '.');

    $symbols = [
        'EUR' => '€',
        'RON' => 'lei',
        'USD' => '$'
    ];

    $symbol = $symbols[$currency] ?? $currency;

    return $formatted . ' ' . $symbol;
}

/**
 * Format number
 */
function format_number($number, $decimals = 2) {
    return number_format($number, $decimals, ',', '.');
}

/**
 * Parse Romanian number format to float
 */
function parse_number($str) {
    $str = str_replace(['.', ','], ['', '.'], $str);
    return (float) $str;
}

// ============================================
// ARRAY FUNCTIONS
// ============================================

/**
 * Get array value by key with default
 */
function array_get($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

/**
 * Pluck column from array of arrays
 */
function array_pluck($array, $column) {
    return array_map(function($item) use ($column) {
        return is_array($item) ? $item[$column] : $item->$column;
    }, $array);
}

/**
 * Group array by key
 */
function array_group_by($array, $key) {
    $result = [];
    foreach ($array as $item) {
        $groupKey = is_array($item) ? $item[$key] : $item->$key;
        $result[$groupKey][] = $item;
    }
    return $result;
}

// ============================================
// REQUEST FUNCTIONS
// ============================================

/**
 * Get POST data
 */
function post($key = null, $default = null) {
    if ($key === null) {
        return $_POST;
    }
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}

/**
 * Get GET data
 */
function get($key = null, $default = null) {
    if ($key === null) {
        return $_GET;
    }
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}

/**
 * Get REQUEST data
 */
function request($key = null, $default = null) {
    if ($key === null) {
        return $_REQUEST;
    }
    return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
}

/**
 * Check if request is POST
 */
function is_post() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Check if request is GET
 */
function is_get() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Check if request is AJAX
 */
function is_ajax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get client IP address
 */
function get_client_ip() {
    $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            return $_SERVER[$key];
        }
    }
    return 'UNKNOWN';
}

/**
 * Get user agent
 */
function get_user_agent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
}

// ============================================
// REDIRECT & URL FUNCTIONS
// ============================================

/**
 * Redirect to URL
 */
function redirect($url, $statusCode = 302) {
    if (!headers_sent()) {
        header("Location: {$url}", true, $statusCode);
        exit;
    }
}

/**
 * Redirect back
 */
function redirect_back() {
    $referer = $_SERVER['HTTP_REFERER'] ?? SITE_URL;
    redirect($referer);
}

/**
 * Get current URL
 */
function current_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Build URL with query parameters
 */
function url($path = '', $params = []) {
    $url = rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');

    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    return $url;
}

// ============================================
// FLASH MESSAGE FUNCTIONS
// ============================================

/**
 * Set flash message
 */
function set_flash($type, $message) {
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get flash message and clear it
 */
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Check if flash message exists
 */
function has_flash() {
    return isset($_SESSION['flash']);
}

// ============================================
// FILE FUNCTIONS
// ============================================

/**
 * Get file extension
 */
function file_extension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file extension is allowed
 */
function is_allowed_extension($filename, $type = 'all') {
    $ext = file_extension($filename);
    $allowed = ALLOWED_EXTENSIONS[$type] ?? [];

    if ($type === 'all') {
        $allowed = array_merge(...array_values(ALLOWED_EXTENSIONS));
    }

    return in_array($ext, $allowed);
}

/**
 * Format file size
 */
function format_file_size($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Generate unique filename
 */
function unique_filename($originalName) {
    $ext = file_extension($originalName);
    return random_string(32) . '.' . $ext;
}

// ============================================
// JSON RESPONSE
// ============================================

/**
 * Send JSON response
 */
function json_response($success, $data = null, $message = '', $statusCode = 200) {
    header('Content-Type: application/json', true, $statusCode);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Send JSON error response
 */
function json_error($message, $statusCode = 400) {
    json_response(false, null, $message, $statusCode);
}

/**
 * Send JSON success response
 */
function json_success($data = null, $message = 'Success') {
    json_response(true, $data, $message);
}

// ============================================
// CSRF TOKEN
// ============================================

/**
 * Generate CSRF token
 */
function csrf_token() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = random_string(64);
        $_SESSION[CSRF_TOKEN_NAME . '_time'] = time();
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Get CSRF token field (HTML)
 */
function csrf_field() {
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }

    // Check token
    if (!hash_equals($_SESSION[CSRF_TOKEN_NAME], $token)) {
        return false;
    }

    // Check expiration
    $tokenTime = $_SESSION[CSRF_TOKEN_NAME . '_time'] ?? 0;
    if ((time() - $tokenTime) > CSRF_TOKEN_LIFETIME) {
        unset($_SESSION[CSRF_TOKEN_NAME]);
        unset($_SESSION[CSRF_TOKEN_NAME . '_time']);
        return false;
    }

    return true;
}

// ============================================
// DEBUG FUNCTIONS
// ============================================

/**
 * Dump and die
 */
function dd(...$vars) {
    echo '<pre style="background:#f8f9fa;padding:20px;border:1px solid #dee2e6;border-radius:5px;font-size:14px;">';
    foreach ($vars as $var) {
        var_dump($var);
    }
    echo '</pre>';
    die();
}

/**
 * Dump variable
 */
function dump(...$vars) {
    echo '<pre style="background:#f8f9fa;padding:20px;border:1px solid #dee2e6;border-radius:5px;font-size:14px;">';
    foreach ($vars as $var) {
        var_dump($var);
    }
    echo '</pre>';
}

/**
 * Print array/object readable
 */
function pr($var) {
    echo '<pre style="background:#f8f9fa;padding:20px;border:1px solid #dee2e6;border-radius:5px;font-size:14px;">';
    print_r($var);
    echo '</pre>';
}

// ============================================
// MISC FUNCTIONS
// ============================================

/**
 * Check if string starts with
 */
function starts_with($haystack, $needle) {
    return substr($haystack, 0, strlen($needle)) === $needle;
}

/**
 * Check if string ends with
 */
function ends_with($haystack, $needle) {
    return substr($haystack, -strlen($needle)) === $needle;
}

/**
 * Check if string contains
 */
function contains($haystack, $needle) {
    return strpos($haystack, $needle) !== false;
}

/**
 * Generate hash
 */
function generate_hash($data) {
    return hash('sha256', $data . config('APP_KEY', 'devizo-secret-key'));
}

/**
 * Get gravatar URL
 */
function gravatar($email, $size = 80) {
    $hash = md5(strtolower(trim($email)));
    return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=mp";
}

/**
 * Asset URL helper
 */
function asset($path) {
    return SITE_URL . '/assets/' . ltrim($path, '/');
}

/**
 * Module URL helper
 */
function module_url($module, $path = '') {
    return SITE_URL . '/modules/' . $module . '/' . ltrim($path, '/');
}
