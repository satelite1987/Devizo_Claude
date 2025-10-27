<?php
// DIAGNOSTIC SIMPLU - fără dependențe
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Diagnostic DEVIZO</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .ok { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        h2 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        pre { background: white; padding: 15px; border-left: 4px solid #667eea; }
        .box { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>

<h1>🔍 DEVIZO v2.0 - Diagnostic Simplu</h1>

<?php
// 1. PHP Info
echo "<div class='box'>";
echo "<h2>1. Versiune PHP</h2>";
echo "<p><strong>PHP " . phpversion() . "</strong></p>";
echo "</div>";

// 2. Directoare
echo "<div class='box'>";
echo "<h2>2. Verificare Directoare</h2>";
$dirs = ['core', 'database', 'dashboards', 'views', 'uploads', 'cache', 'logs'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        echo "<p class='ok'>✓ /{$dir}/ există</p>";
    } else {
        echo "<p class='error'>✗ /{$dir}/ LIPSEȘTE - creez acum...</p>";
        if (@mkdir($dir, 0755, true)) {
            echo "<p class='ok'>✓ /{$dir}/ creat cu succes!</p>";
        } else {
            echo "<p class='error'>✗ Nu am putut crea /{$dir}/ - verifică permisiunile</p>";
        }
    }
}
echo "</div>";

// 3. Fișiere importante
echo "<div class='box'>";
echo "<h2>3. Verificare Fișiere</h2>";
$files = [
    'config.php',
    'login.php',
    'index.php',
    'setup.php',
    'core/Database.php',
    'core/Helpers.php',
    'core/Session.php',
    'core/Auth.php'
];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p class='ok'>✓ {$file}</p>";
    } else {
        echo "<p class='error'>✗ {$file} LIPSEȘTE!</p>";
    }
}
echo "</div>";

// 4. Test config.php
echo "<div class='box'>";
echo "<h2>4. Test config.php</h2>";
if (file_exists('config.php')) {
    echo "<p class='ok'>✓ config.php există</p>";
    echo "<p>Încerc să-l încărca...</p>";

    // Citesc primele linii
    $content = file_get_contents('config.php', false, null, 0, 500);
    if (strpos($content, 'DEVIZO_APP') !== false) {
        echo "<p class='ok'>✓ config.php pare valid</p>";
    } else {
        echo "<p class='error'>✗ config.php pare corupt</p>";
    }

    // Încerc să definesc constant și să încărca
    if (!defined('DEVIZO_APP')) {
        define('DEVIZO_APP', true);
    }

    try {
        require_once 'config.php';
        echo "<p class='ok'>✓ config.php încărcat cu succes!</p>";
        echo "<pre>";
        echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NU E DEFINIT') . "\n";
        echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NU E DEFINIT') . "\n";
        echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'NU E DEFINIT') . "\n";
        echo "</pre>";
    } catch (Exception $e) {
        echo "<p class='error'>✗ EROARE la încărcare config.php:</p>";
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    } catch (Error $e) {
        echo "<p class='error'>✗ EROARE FATALĂ la încărcare config.php:</p>";
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    }
} else {
    echo "<p class='error'>✗ config.php NU EXISTĂ!</p>";
}
echo "</div>";

// 5. Test Database
echo "<div class='box'>";
echo "<h2>5. Test Database</h2>";
if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS')) {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo "<p class='ok'>✓ Conexiune la database REUȘITĂ!</p>";

        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p class='ok'>✓ Găsite <strong>" . count($tables) . " tabele</strong></p>";

        if (count($tables) >= 33) {
            echo "<p class='ok'>✓ Toate tabelele sunt importate!</p>";
        } else {
            echo "<p class='error'>⚠ Așteptate 33 tabele, găsite doar " . count($tables) . "</p>";
            echo "<p>Trebuie să importezi schema.sql și demo-data.sql în phpMyAdmin!</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ EROARE conexiune database:</p>";
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        echo "<p><strong>Verifică:</strong></p>";
        echo "<ul>";
        echo "<li>Baza de date <strong>" . DB_NAME . "</strong> există?</li>";
        echo "<li>Userul <strong>" . DB_USER . "</strong> are permisiuni?</li>";
        echo "<li>Parola este corectă?</li>";
        echo "</ul>";
    }
} else {
    echo "<p class='error'>✗ Constantele DB nu sunt definite - config.php nu s-a încărcat corect!</p>";
}
echo "</div>";

// 6. Test login.php
echo "<div class='box'>";
echo "<h2>6. Test login.php</h2>";
if (file_exists('login.php')) {
    $size = filesize('login.php');
    echo "<p class='ok'>✓ login.php există (mărime: " . number_format($size) . " bytes)</p>";

    if ($size > 1000) {
        echo "<p class='ok'>✓ login.php pare să conțină cod</p>";
    } else {
        echo "<p class='error'>⚠ login.php pare prea mic - ar putea fi corupt</p>";
    }

    // Verifică primele linii
    $firstLines = file_get_contents('login.php', false, null, 0, 200);
    if (strpos($firstLines, '<?php') === 0) {
        echo "<p class='ok'>✓ login.php începe corect cu &lt;?php</p>";
    } else {
        echo "<p class='error'>✗ login.php NU începe cu &lt;?php - CORUPT!</p>";
    }
} else {
    echo "<p class='error'>✗ login.php NU EXISTĂ!</p>";
}
echo "</div>";

// 7. Permisiuni
echo "<div class='box'>";
echo "<h2>7. Verificare Permisiuni</h2>";
foreach (['uploads', 'cache', 'logs'] as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<p class='ok'>✓ /{$dir}/ este writable</p>";
        } else {
            echo "<p class='error'>✗ /{$dir}/ NU este writable</p>";
            echo "<p>Rulează: <code>chmod 755 {$dir}</code></p>";
        }
    }
}
echo "</div>";

// 8. phpinfo (parțial)
echo "<div class='box'>";
echo "<h2>8. Informații PHP</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Current Dir: " . __DIR__ . "\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "error_reporting: " . error_reporting() . "\n";
echo "</pre>";
echo "</div>";

?>

<div class='box' style='background: #e8f5e9;'>
    <h2>✅ Următorii Pași</h2>
    <ol>
        <li><strong>Dacă toate verificările de mai sus sunt OK:</strong>
            <ul>
                <li>Accesează <a href='login.php' target='_blank'>login.php</a></li>
                <li>Ar trebui să vezi pagina de login cu carduri</li>
            </ul>
        </li>
        <li><strong>Dacă login.php încă e gol:</strong>
            <ul>
                <li>Trimite-mi screenshot cu acest diagnostic</li>
                <li>Verifică error_log în cPanel</li>
            </ul>
        </li>
        <li><strong>Dacă vezi erori roșii mai sus:</strong>
            <ul>
                <li>Trimite-mi screenshot complet</li>
                <li>Rezolvăm problema specifică</li>
            </ul>
        </li>
    </ol>
</div>

<div class='box' style='background: #fff3e0;'>
    <h2>⚠️ Dacă acest diagnostic.php e tot gol:</h2>
    <p><strong>Înseamnă că PHP nu funcționează deloc pe server!</strong></p>
    <p>Verifică în cPanel:</p>
    <ul>
        <li>PHP este activat?</li>
        <li>Extensia .php este procesată de PHP?</li>
        <li>Nu ai erori în .htaccess care blochează PHP?</li>
    </ul>
</div>

</body>
</html>
