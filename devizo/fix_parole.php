<?php
/**
 * DEVIZO - Reparare Parole Utilizatori Demo
 *
 * Script INDEPENDENT pentru resetarea parolelor
 * NU DEPINDE de alte fisiere - contine tot ce are nevoie
 *
 * INSTRUCTIUNI:
 * 1. Upload in /public_html/ (RADACINA, NU in folder devizo!)
 * 2. Acceseaza: https://www.devizo.ro/fix_parole.php
 * 3. STERGE fisierul imediat dupa folosire!
 */

// Activeaza afisarea erorilor
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Incepe output-ul HTML IMEDIAT
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reparare Parole DEVIZO</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 32px;
        }
        h2 {
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            font-size: 20px;
        }
        .step {
            background: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .warning {
            color: #ffc107;
            font-weight: bold;
        }
        .info {
            background: #d1ecf1;
            border-left: 4px solid #0c5460;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        code {
            background: #f4f4f4;
            padding: 3px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background: #667eea;
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .btn {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
            margin: 20px 0;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #218838;
        }
        .alert-success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .alert-danger {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .alert-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 12px;
        }
        ul {
            margin: 10px 0;
            padding-left: 30px;
        }
        li {
            margin: 5px 0;
        }
        .icon {
            font-size: 24px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1><span class="icon">🔐</span>Reparare Parole DEVIZO</h1>
            <p style="color: #666; font-size: 14px;">Script automat pentru resetarea parolelor utilizatorilor demo</p>
        </div>

<?php

// CONFIGURARE BAZA DE DATE
// Aceste valori TREBUIE sa fie EXACT ca in config/database.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'devizo_db');
define('DB_USER', 'devizo_user');
define('DB_PASS', 'Satelite1987!');
define('DB_CHARSET', 'utf8mb4');

echo '<div class="card">';
echo '<h2>Pas 1: Conectare la Baza de Date</h2>';

try {
    // Cream conexiunea PDO
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    echo '<div class="step">';
    echo '<p class="success">✅ Conexiune la baza de date reusita!</p>';
    echo '<p><strong>Host:</strong> <code>' . DB_HOST . '</code></p>';
    echo '<p><strong>Baza de date:</strong> <code>' . DB_NAME . '</code></p>';
    echo '<p><strong>Utilizator:</strong> <code>' . DB_USER . '</code></p>';
    echo '</div>';

} catch (PDOException $e) {
    echo '<div class="alert-danger">';
    echo '<h3 class="error">❌ EROARE: Nu pot conecta la baza de date!</h3>';
    echo '<p><strong>Mesaj eroare:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>Verificari necesare:</strong></p>';
    echo '<ul>';
    echo '<li>Host-ul este corect? (<code>' . DB_HOST . '</code>)</li>';
    echo '<li>Numele bazei de date este corect? (<code>' . DB_NAME . '</code>)</li>';
    echo '<li>Utilizatorul are acces? (<code>' . DB_USER . '</code>)</li>';
    echo '<li>Parola este corecta?</li>';
    echo '</ul>';
    echo '</div>';
    echo '</div></div></body></html>';
    exit;
}

echo '</div>';

// PAS 2: VERIFICARE TABELA UTILIZATORI
echo '<div class="card">';
echo '<h2>Pas 2: Verificare Tabela Utilizatori</h2>';

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM utilizatori");
    $result = $stmt->fetch();

    echo '<div class="step">';
    echo '<p class="success">✅ Tabela "utilizatori" gasita!</p>';
    echo '<p><strong>Numar total utilizatori:</strong> <code>' . $result['total'] . '</code></p>';
    echo '</div>';

} catch (PDOException $e) {
    echo '<div class="alert-danger">';
    echo '<h3 class="error">❌ EROARE: Tabela "utilizatori" nu exista!</h3>';
    echo '<p><strong>Mesaj:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p>Probabil baza de date nu a fost importata corect. Reimporteaza fisierul <code>database.sql</code> din phpMyAdmin.</p>';
    echo '</div>';
    echo '</div></div></body></html>';
    exit;
}

echo '</div>';

// PAS 3: GENERARE HASH-URI NOI
echo '<div class="card">';
echo '<h2>Pas 3: Generare Hash-uri Parole Noi</h2>';

$parole = [
    'admin123' => password_hash('admin123', PASSWORD_DEFAULT),
    'demo123' => password_hash('demo123', PASSWORD_DEFAULT),
    'user123' => password_hash('user123', PASSWORD_DEFAULT)
];

echo '<div class="step">';
echo '<p class="success">✅ Hash-uri BCrypt generate cu succes!</p>';
echo '<table>';
echo '<tr><th>Parola (text)</th><th>Hash BCrypt</th></tr>';
foreach ($parole as $text => $hash) {
    echo '<tr>';
    echo '<td><code><strong>' . $text . '</strong></code></td>';
    echo '<td style="font-size: 11px; word-break: break-all;">' . $hash . '</td>';
    echo '</tr>';
}
echo '</table>';
echo '</div>';

echo '</div>';

// PAS 4: RESETARE PAROLE
echo '<div class="card">';
echo '<h2>Pas 4: Resetare Parole Utilizatori</h2>';

$utilizatori = [
    [
        'email' => 'admin@devizo.ro',
        'parola_text' => 'admin123',
        'parola_hash' => $parole['admin123'],
        'nume' => 'Super Administrator',
        'rol' => 'Super Admin'
    ],
    [
        'email' => 'doru@zaninstal.ro',
        'parola_text' => 'demo123',
        'parola_hash' => $parole['demo123'],
        'nume' => 'Batagui Doru',
        'rol' => 'Master Firma'
    ],
    [
        'email' => 'user@zaninstal.ro',
        'parola_text' => 'user123',
        'parola_hash' => $parole['user123'],
        'nume' => 'Utilizator Demo',
        'rol' => 'Utilizator'
    ]
];

echo '<table>';
echo '<tr><th>Email</th><th>Nume</th><th>Rol</th><th>Parola Noua</th><th>Actualizare</th><th>Verificare</th></tr>';

$allSuccess = true;

foreach ($utilizatori as $user) {
    echo '<tr>';
    echo '<td><code>' . $user['email'] . '</code></td>';
    echo '<td>' . $user['nume'] . '</td>';
    echo '<td><strong>' . $user['rol'] . '</strong></td>';
    echo '<td><code><strong>' . $user['parola_text'] . '</strong></code></td>';

    try {
        // Verificam daca utilizatorul exista
        $stmt = $pdo->prepare("SELECT id FROM utilizatori WHERE email = ?");
        $stmt->execute([$user['email']]);
        $exists = $stmt->fetch();

        if ($exists) {
            // Actualizam parola
            $stmt = $pdo->prepare("UPDATE utilizatori SET parola = ? WHERE email = ?");
            $stmt->execute([$user['parola_hash'], $user['email']]);

            echo '<td class="success">✅ ACTUALIZAT</td>';

            // Verificam daca hash-ul functioneaza
            $stmt = $pdo->prepare("SELECT parola FROM utilizatori WHERE email = ?");
            $stmt->execute([$user['email']]);
            $updated = $stmt->fetch();

            if (password_verify($user['parola_text'], $updated['parola'])) {
                echo '<td class="success">✅ CORECT</td>';
            } else {
                echo '<td class="error">❌ GRESIT</td>';
                $allSuccess = false;
            }
        } else {
            echo '<td class="error">❌ NU EXISTA</td>';
            echo '<td>-</td>';
            $allSuccess = false;
        }
    } catch (PDOException $e) {
        echo '<td class="error">❌ EROARE</td>';
        echo '<td class="error">' . htmlspecialchars($e->getMessage()) . '</td>';
        $allSuccess = false;
    }

    echo '</tr>';
}

echo '</table>';
echo '</div>';

// PAS 5: TEST AUTENTIFICARE
echo '<div class="card">';
echo '<h2>Pas 5: Test Autentificare</h2>';

echo '<p>Testez daca fiecare utilizator se poate autentifica cu parola noua...</p>';

foreach ($utilizatori as $user) {
    try {
        $stmt = $pdo->prepare("SELECT id, email, nume, parola FROM utilizatori WHERE email = ?");
        $stmt->execute([$user['email']]);
        $dbUser = $stmt->fetch();

        if ($dbUser && password_verify($user['parola_text'], $dbUser['parola'])) {
            echo '<div class="step">';
            echo '<p class="success">✅ <strong>' . $user['email'] . '</strong> cu parola <code>' . $user['parola_text'] . '</code></p>';
            echo '<p style="font-size: 13px; color: #666;">password_verify() = SUCCESS</p>';
            echo '</div>';
        } else {
            echo '<div class="alert-danger">';
            echo '<p class="error">❌ <strong>' . $user['email'] . '</strong> cu parola <code>' . $user['parola_text'] . '</code></p>';
            echo '<p>Autentificarea nu functioneaza!</p>';
            echo '</div>';
            $allSuccess = false;
        }
    } catch (PDOException $e) {
        echo '<div class="alert-danger">';
        echo '<p class="error">❌ Eroare la testarea utilizatorului ' . $user['email'] . '</p>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '</div>';
        $allSuccess = false;
    }
}

echo '</div>';

// REZULTAT FINAL
if ($allSuccess) {
    echo '<div class="alert-success">';
    echo '<h2 style="color: #28a745;"><span class="icon">🎉</span>SUCCES COMPLET!</h2>';
    echo '<p style="font-size: 18px; margin: 15px 0;"><strong>Toate parolele au fost resetate si verificate cu succes!</strong></p>';
    echo '<p>Puteti acum sa va autentificati la aplicatia DEVIZO:</p>';
    echo '<table style="margin: 20px 0;">';
    echo '<tr><th>Rol</th><th>Email</th><th>Parola</th></tr>';
    echo '<tr><td><strong>Super Admin</strong></td><td><code>admin@devizo.ro</code></td><td><code><strong>admin123</strong></code></td></tr>';
    echo '<tr><td><strong>Master Firma</strong></td><td><code>doru@zaninstal.ro</code></td><td><code><strong>demo123</strong></code></td></tr>';
    echo '<tr><td><strong>Utilizator</strong></td><td><code>user@zaninstal.ro</code></td><td><code><strong>user123</strong></code></td></tr>';
    echo '</table>';
    echo '<a href="login.php" class="btn"><span class="icon">🚀</span>INTRA IN APLICATIE</a>';
    echo '</div>';
} else {
    echo '<div class="alert-danger">';
    echo '<h2 class="error"><span class="icon">❌</span>AU APARUT PROBLEME</h2>';
    echo '<p>Unele parole nu au fost resetate corect. Verificati erorile de mai sus.</p>';
    echo '<p><strong>Ce poti face:</strong></p>';
    echo '<ul>';
    echo '<li>Verifica ca fisierul <code>database.sql</code> a fost importat complet</li>';
    echo '<li>Verifica ca tabelul <code>utilizatori</code> contine cei 3 utilizatori demo</li>';
    echo '<li>Reincarca aceasta pagina (F5) pentru a reincerca</li>';
    echo '</ul>';
    echo '</div>';
}

// INFORMATII TEHNICE
echo '<div class="card">';
echo '<h2>Informatii Tehnice</h2>';
echo '<div class="info">';
echo '<p><strong>De ce nu functiona autentificarea?</strong></p>';
echo '<p>Hash-ul initial folosit in baza de date era:</p>';
echo '<pre>$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi</pre>';
echo '<p>Acesta este un hash de test din Laravel Framework care corespunde parolei <strong>"password"</strong>, NU parolelor "admin123", "demo123" sau "user123".</p>';
echo '<p><strong>Solutia:</strong></p>';
echo '<p>Acest script a generat hash-uri BCrypt NOI pentru fiecare parola demo folosind functia PHP <code>password_hash()</code> cu algoritm <strong>PASSWORD_DEFAULT</strong> (BCrypt cu cost factor 10).</p>';
echo '</div>';
echo '</div>';

// AVERTISMENT SECURITATE
echo '<div class="alert-warning">';
echo '<h2 style="color: #856404;"><span class="icon">⚠️</span>SECURITATE - ACTIUNE URGENTA!</h2>';
echo '<p style="font-size: 18px;"><strong>STERGETI IMEDIAT acest fisier dupa utilizare!</strong></p>';
echo '<p>Fisiere care TREBUIE sterse din <code>/public_html/</code>:</p>';
echo '<ul style="font-size: 16px;">';
echo '<li><code><strong>fix_parole.php</strong></code> (ACEST FISIER!)</li>';
echo '<li><code>verificare_db.php</code> (daca exista)</li>';
echo '<li><code>resetare_parole.php</code> (daca exista)</li>';
echo '<li><code>creare_utilizatori_demo.php</code> (daca exista)</li>';
echo '<li><code>database.sql</code> (dupa importare)</li>';
echo '</ul>';
echo '<p class="error" style="font-size: 16px; margin-top: 15px;">Aceste fisiere contin credentiale si informatii sensibile si reprezinta un RISC DE SECURITATE major daca raman pe server!</p>';
echo '</div>';

?>

        <div class="card" style="text-align: center; color: #999;">
            <p>DEVIZO v1.0.0 - Reparare Parole Utilizatori</p>
            <p style="font-size: 12px;">Script generat automat - <?php echo date('d.m.Y H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
