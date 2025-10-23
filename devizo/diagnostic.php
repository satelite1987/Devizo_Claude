<?php
/**
 * DEVIZO - Diagnostic Script
 *
 * Acest script ULTRA-SIMPLU testeaza pas cu pas ce functioneaza si ce nu
 * Nu se va inchide tab-ul - vei vedea EXACT unde apare problema
 */

// FORTEAZA afisarea erorilor
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Buffer output pentru a asigura ca se afiseaza
ob_start();

?><!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic DEVIZO</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 20px;
            color: #333;
        }
        .container { max-width: 800px; margin: 0 auto; }
        .card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        h1 { color: #1e3c72; margin-bottom: 20px; }
        h2 { color: #2a5298; margin-bottom: 15px; font-size: 18px; border-bottom: 2px solid #eee; padding-bottom: 8px; }
        .test {
            padding: 12px;
            margin: 8px 0;
            border-radius: 5px;
            border-left: 4px solid #ccc;
        }
        .success { background: #d4edda; border-left-color: #28a745; color: #155724; }
        .error { background: #f8d7da; border-left-color: #dc3545; color: #721c24; }
        .warning { background: #fff3cd; border-left-color: #ffc107; color: #856404; }
        .info { background: #d1ecf1; border-left-color: #17a2b8; color: #0c5460; }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 13px;
        }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: 600; }
        .icon { font-size: 20px; margin-right: 8px; }
        pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 12px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1><span class="icon">🔍</span>Diagnostic DEVIZO - Detectie Probleme</h1>
            <p style="color: #666;">Acest script testeaza fiecare componenta pas cu pas...</p>
        </div>

        <div class="card">
            <h2>Test 1: PHP Functioneaza</h2>
            <div class="test success">
                <strong>✅ SUCCES!</strong> Daca vezi acest mesaj, PHP functioneaza corect!
                <br>Versiune PHP: <code><?php echo PHP_VERSION; ?></code>
                <br>Ora server: <code><?php echo date('Y-m-d H:i:s'); ?></code>
            </div>
        </div>

        <?php
        // Test 2: Extensii PHP
        echo '<div class="card">';
        echo '<h2>Test 2: Extensii PHP Necesare</h2>';

        $extensii_necesare = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'curl', 'json'];
        $toate_ok = true;

        foreach ($extensii_necesare as $ext) {
            if (extension_loaded($ext)) {
                echo '<div class="test success">✅ Extensia <code>' . $ext . '</code> este ACTIVA</div>';
            } else {
                echo '<div class="test error">❌ Extensia <code>' . $ext . '</code> este INACTIVA!</div>';
                $toate_ok = false;
            }
        }

        if ($toate_ok) {
            echo '<div class="test success"><strong>✅ Toate extensiile necesare sunt active!</strong></div>';
        }

        echo '</div>';

        // Test 3: Functii necesare
        echo '<div class="card">';
        echo '<h2>Test 3: Functii PHP Necesare</h2>';

        if (function_exists('password_hash')) {
            echo '<div class="test success">✅ Functia <code>password_hash()</code> exista</div>';
        } else {
            echo '<div class="test error">❌ Functia <code>password_hash()</code> NU exista!</div>';
        }

        if (function_exists('password_verify')) {
            echo '<div class="test success">✅ Functia <code>password_verify()</code> exista</div>';
        } else {
            echo '<div class="test error">❌ Functia <code>password_verify()</code> NU exista!</div>';
        }

        if (class_exists('PDO')) {
            echo '<div class="test success">✅ Clasa <code>PDO</code> exista</div>';
        } else {
            echo '<div class="test error">❌ Clasa <code>PDO</code> NU exista!</div>';
        }

        echo '</div>';

        // Test 4: Informatii despre locatie fisier
        echo '<div class="card">';
        echo '<h2>Test 4: Informatii Fisier</h2>';
        echo '<div class="test info">';
        echo '<strong>Locatie acest script:</strong><br>';
        echo '<code>' . __FILE__ . '</code><br><br>';
        echo '<strong>Director curent:</strong><br>';
        echo '<code>' . __DIR__ . '</code><br><br>';
        echo '<strong>URL accesat:</strong><br>';
        echo '<code>' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'N/A') . '</code>';
        echo '</div>';
        echo '</div>';

        // Test 5: Credentiale baza de date (fara conectare)
        echo '<div class="card">';
        echo '<h2>Test 5: Credentiale Baza de Date</h2>';
        echo '<div class="test info">';
        echo '<p>Credentialele configurate in script:</p>';
        echo '<table>';
        echo '<tr><th>Parametru</th><th>Valoare</th></tr>';
        echo '<tr><td>DB_HOST</td><td><code>localhost</code></td></tr>';
        echo '<tr><td>DB_NAME</td><td><code>devizo_db</code></td></tr>';
        echo '<tr><td>DB_USER</td><td><code>devizo_user</code></td></tr>';
        echo '<tr><td>DB_PASS</td><td><code>Satelite1987!</code></td></tr>';
        echo '<tr><td>DB_CHARSET</td><td><code>utf8mb4</code></td></tr>';
        echo '</table>';
        echo '<p style="margin-top: 10px;"><strong>⚠️ Verifica ca aceste credentiale sunt CORECTE!</strong></p>';
        echo '</div>';
        echo '</div>';

        // Test 6: Incercare conectare la baza de date
        echo '<div class="card">';
        echo '<h2>Test 6: Conectare la Baza de Date</h2>';

        $db_host = 'localhost';
        $db_name = 'devizo_db';
        $db_user = 'devizo_user';
        $db_pass = 'Satelite1987!';
        $db_charset = 'utf8mb4';

        try {
            $dsn = "mysql:host={$db_host};dbname={$db_name};charset={$db_charset}";

            echo '<div class="test info">';
            echo '<strong>Încerc să conectez la baza de date...</strong><br>';
            echo 'DSN: <code>' . htmlspecialchars($dsn) . '</code>';
            echo '</div>';

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];

            $pdo = new PDO($dsn, $db_user, $db_pass, $options);

            echo '<div class="test success">';
            echo '<strong>✅ CONEXIUNE REUSITA la baza de date!</strong><br>';
            echo 'Baza de date: <code>' . $db_name . '</code><br>';
            echo 'Server: <code>' . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . '</code>';
            echo '</div>';

            // Test 7: Verificare tabela utilizatori
            echo '</div><div class="card">';
            echo '<h2>Test 7: Verificare Tabela "utilizatori"</h2>';

            $stmt = $pdo->query("SHOW TABLES LIKE 'utilizatori'");
            $tabel_exists = $stmt->fetch();

            if ($tabel_exists) {
                echo '<div class="test success">✅ Tabela <code>utilizatori</code> EXISTA</div>';

                // Numara utilizatori
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM utilizatori");
                $result = $stmt->fetch();

                echo '<div class="test info">';
                echo '<strong>Numar total utilizatori:</strong> <code>' . $result['total'] . '</code>';
                echo '</div>';

                // Lista utilizatori
                $stmt = $pdo->query("SELECT id, email, nume, activ FROM utilizatori ORDER BY id");
                $users = $stmt->fetchAll();

                if (count($users) > 0) {
                    echo '<table>';
                    echo '<tr><th>ID</th><th>Email</th><th>Nume</th><th>Activ</th></tr>';
                    foreach ($users as $user) {
                        echo '<tr>';
                        echo '<td>' . $user['id'] . '</td>';
                        echo '<td><code>' . htmlspecialchars($user['email']) . '</code></td>';
                        echo '<td>' . htmlspecialchars($user['nume']) . '</td>';
                        echo '<td>' . ($user['activ'] ? '✅ Da' : '❌ Nu') . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<div class="test warning">⚠️ Tabela exista dar nu contine utilizatori!</div>';
                }

            } else {
                echo '<div class="test error">❌ Tabela <code>utilizatori</code> NU EXISTA!</div>';
                echo '<div class="test warning">Reimporta fisierul <code>database.sql</code> in phpMyAdmin!</div>';
            }

        } catch (PDOException $e) {
            echo '<div class="test error">';
            echo '<strong>❌ EROARE la conectarea la baza de date!</strong><br><br>';
            echo '<strong>Mesaj eroare:</strong><br>';
            echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
            echo '<br><strong>Posibile cauze:</strong>';
            echo '<ul style="margin: 10px 0; padding-left: 25px;">';
            echo '<li>Credentialele sunt gresite</li>';
            echo '<li>Baza de date <code>devizo_db</code> nu exista</li>';
            echo '<li>Utilizatorul <code>devizo_user</code> nu are acces la baza</li>';
            echo '<li>MySQL server nu este pornit</li>';
            echo '</ul>';
            echo '</div>';
        }

        echo '</div>';

        // Test 8: Test password_hash
        echo '<div class="card">';
        echo '<h2>Test 8: Test Functii Password</h2>';

        $test_password = 'admin123';
        $test_hash = password_hash($test_password, PASSWORD_DEFAULT);

        echo '<div class="test info">';
        echo '<strong>Parola test:</strong> <code>' . $test_password . '</code><br>';
        echo '<strong>Hash generat:</strong><br>';
        echo '<code style="word-break: break-all;">' . $test_hash . '</code>';
        echo '</div>';

        if (password_verify($test_password, $test_hash)) {
            echo '<div class="test success">✅ password_verify() functioneaza CORECT!</div>';
        } else {
            echo '<div class="test error">❌ password_verify() NU functioneaza!</div>';
        }

        echo '</div>';

        // Concluzie
        echo '<div class="card">';
        echo '<h2>Concluzie</h2>';
        echo '<div class="test success">';
        echo '<strong>✅ Daca vezi ACEST mesaj, scriptul functioneaza complet!</strong><br><br>';
        echo 'Toate componentele au fost testate. Verifica mesajele de mai sus pentru a identifica eventualele probleme.';
        echo '</div>';
        echo '</div>';

        // Instructiuni
        echo '<div class="card">';
        echo '<h2>Ce urmeaza?</h2>';
        echo '<div class="test info">';
        echo '<p><strong>Daca TOATE testele de mai sus sunt ✅ VERZI:</strong></p>';
        echo '<p>Atunci poti rula scriptul de resetare parole. Spune-mi si iti trimit scriptul actualizat!</p>';
        echo '<br>';
        echo '<p><strong>Daca vezi ❌ ERORI ROSII:</strong></p>';
        echo '<p>Fa screenshot la TOATA aceasta pagina si trimite-mi-l. Voi vedea exact ce nu functioneaza.</p>';
        echo '</div>';
        echo '</div>';

        ?>

        <div class="card" style="text-align: center; color: #999; font-size: 13px;">
            <p>DEVIZO Diagnostic Script v1.0</p>
            <p>Generated: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
<?php
ob_end_flush();
?>
