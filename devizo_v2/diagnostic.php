<?php
/**
 * DIAGNOSTIC SCRIPT - Identificare erori
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Diagnostic DEVIZO</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}";
echo ".ok{color:green;font-weight:bold;}.error{color:red;font-weight:bold;}";
echo "h2{color:#333;border-bottom:2px solid #667eea;padding-bottom:10px;}";
echo "pre{background:white;padding:15px;border-left:4px solid #667eea;overflow:auto;}</style></head><body>";

echo "<h1>🔍 DEVIZO v2.0 - Diagnostic</h1>";

// 1. Check PHP version
echo "<h2>1. Versiune PHP</h2>";
echo "<pre>PHP " . phpversion() . "</pre>";
if (version_compare(phpversion(), '8.0.0', '>=')) {
    echo "<p class='ok'>✓ PHP 8.0+ instalat</p>";
} else {
    echo "<p class='error'>✗ PHP versiune prea veche (necesită 8.0+)</p>";
}

// 2. Check required directories
echo "<h2>2. Verificare Directoare</h2>";
$requiredDirs = [
    'core',
    'database',
    'dashboards',
    'views',
    'uploads',
    'cache',
    'logs'
];

foreach ($requiredDirs as $dir) {
    if (is_dir(__DIR__ . '/' . $dir)) {
        echo "<p class='ok'>✓ /{$dir}/ există</p>";
    } else {
        echo "<p class='error'>✗ /{$dir}/ LIPSEȘTE</p>";
    }
}

// 3. Check required files
echo "<h2>3. Verificare Fișiere Core</h2>";
$requiredFiles = [
    'config.php',
    'core/Database.php',
    'core/Helpers.php',
    'core/Session.php',
    'core/Auth.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<p class='ok'>✓ {$file} există</p>";
    } else {
        echo "<p class='error'>✗ {$file} LIPSEȘTE</p>";
    }
}

// 4. Test config.php
echo "<h2>4. Test Încărcare config.php</h2>";
try {
    define('DEVIZO_APP', true);
    require_once __DIR__ . '/config.php';
    echo "<p class='ok'>✓ config.php încărcat cu succes</p>";
    echo "<pre>";
    echo "DB_HOST: " . DB_HOST . "\n";
    echo "DB_NAME: " . DB_NAME . "\n";
    echo "DB_USER: " . DB_USER . "\n";
    echo "DEBUG_MODE: " . (DEBUG_MODE ? 'true' : 'false') . "\n";
    echo "</pre>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Eroare la încărcare config.php:</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}

// 5. Test Database connection
echo "<h2>5. Test Conexiune Database</h2>";
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "<p class='ok'>✓ Conexiune la baza de date reușită</p>";

    // Check tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p class='ok'>✓ Găsite " . count($tables) . " tabele</p>";

    if (count($tables) < 33) {
        echo "<p class='error'>⚠ Așteptate 33 tabele, găsite doar " . count($tables) . "</p>";
        echo "<p>Ai importat schema.sql și demo-data.sql?</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Eroare conexiune database:</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}

// 6. Test loading core files
echo "<h2>6. Test Încărcare Core Files</h2>";

$coreFiles = [
    'Database.php',
    'Helpers.php',
    'Session.php',
    'Auth.php'
];

foreach ($coreFiles as $file) {
    try {
        require_once CORE_PATH . '/' . $file;
        echo "<p class='ok'>✓ {$file} încărcat cu succes</p>";
    } catch (Exception $e) {
        echo "<p class='error'>✗ Eroare la încărcare {$file}:</p>";
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    }
}

// 7. Test creating directories
echo "<h2>7. Creare Directoare Lipsă</h2>";
$dirsToCreate = ['uploads', 'cache', 'logs'];
foreach ($dirsToCreate as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (!is_dir($path)) {
        if (@mkdir($path, 0755, true)) {
            echo "<p class='ok'>✓ Directorul /{$dir}/ a fost creat</p>";
        } else {
            echo "<p class='error'>✗ Nu s-a putut crea /{$dir}/ - verifică permisiunile</p>";
        }
    } else {
        echo "<p class='ok'>✓ /{$dir}/ există deja</p>";
    }
}

// 8. Check permissions
echo "<h2>8. Verificare Permisiuni</h2>";
foreach ($dirsToCreate as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (is_dir($path)) {
        if (is_writable($path)) {
            echo "<p class='ok'>✓ /{$dir}/ este writable</p>";
        } else {
            echo "<p class='error'>✗ /{$dir}/ NU este writable - rulează: chmod 755 {$dir}</p>";
        }
    }
}

echo "<hr><h2>✅ Diagnostic Complet</h2>";
echo "<p><strong>Următorii pași:</strong></p>";
echo "<ol>";
echo "<li>Dacă toate directoarele sunt OK, încearcă să accesezi <a href='login.php'>login.php</a></li>";
echo "<li>Dacă login.php încă e gol, verifică error_log-ul serverului</li>";
echo "<li>Sau contactează-mă cu rezultatele de aici</li>";
echo "</ol>";

echo "</body></html>";
