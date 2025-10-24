<?php
/**
 * DEVIZO - Script Resetare Parole
 *
 * IMPORTANT: Acest script TREBUIE rulat IMEDIAT dupa importul bazei de date!
 *
 * Acceseaza: https://www.devizo.ro/fix_parole.php
 *
 * STERGE acest fisier IMEDIAT dupa ce ai terminat!
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Configurare baza de date
$db_host = 'localhost';
$db_name = 'devizo_db';
$db_user = 'devizo_user';
$db_pass = 'Satelite1987!';

?><!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resetare Parole - DEVIZO</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            color: #333;
        }
        .container { max-width: 900px; margin: 0 auto; }
        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .step {
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            border-left: 4px solid #ccc;
        }
        .success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }
        .warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #667eea;
            color: white;
            font-weight: 600;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            margin-top: 15px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #218838;
        }
        .icon { font-size: 28px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1><span class="icon">🔐</span> Resetare Parole DEVIZO</h1>
            <p style="color: #666; margin-bottom: 20px;">
                Acest script reseteaza parolele utilizatorilor demo la valorile implicite.
            </p>
        </div>

        <?php
        try {
            // Conectare la baza de date
            echo '<div class="card">';
            echo '<h2 style="color: #667eea; margin-bottom: 15px;">📡 Pasul 1: Conectare la Baza de Date</h2>';

            $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ];

            $pdo = new PDO($dsn, $db_user, $db_pass, $options);

            echo '<div class="step success">';
            echo '<strong>✅ SUCCES!</strong> Conectat la baza de date <code>' . $db_name . '</code>';
            echo '</div>';
            echo '</div>';

            // Generare hash-uri parole
            echo '<div class="card">';
            echo '<h2 style="color: #667eea; margin-bottom: 15px;">🔑 Pasul 2: Generare Hash-uri Parole</h2>';

            $hash_admin = password_hash('admin123', PASSWORD_DEFAULT);
            $hash_demo = password_hash('demo123', PASSWORD_DEFAULT);
            $hash_user = password_hash('user123', PASSWORD_DEFAULT);

            echo '<div class="step success">';
            echo '<strong>✅ SUCCES!</strong> Hash-uri generate cu succes pentru toate parolele.';
            echo '</div>';
            echo '</div>';

            // Update parole
            echo '<div class="card">';
            echo '<h2 style="color: #667eea; margin-bottom: 15px;">💾 Pasul 3: Resetare Parole Utilizatori</h2>';

            $stmt = $pdo->prepare("UPDATE utilizatori SET parola = ? WHERE email = ?");

            // Super Admin
            $stmt->execute([$hash_admin, 'admin@devizo.ro']);
            echo '<div class="step success">✅ Parola resetata pentru <code>admin@devizo.ro</code></div>';

            // Master Firma
            $stmt->execute([$hash_demo, 'doru@zaninstal.ro']);
            echo '<div class="step success">✅ Parola resetata pentru <code>doru@zaninstal.ro</code></div>';

            // Utilizator Normal
            $stmt->execute([$hash_user, 'user@zaninstal.ro']);
            echo '<div class="step success">✅ Parola resetata pentru <code>user@zaninstal.ro</code></div>';

            echo '</div>';

            // Verificare parole
            echo '<div class="card">';
            echo '<h2 style="color: #667eea; margin-bottom: 15px;">🧪 Pasul 4: Verificare Parole</h2>';

            $stmt = $pdo->query("SELECT email, parola FROM utilizatori ORDER BY id");
            $users = $stmt->fetchAll();

            $passwords = [
                'admin@devizo.ro' => 'admin123',
                'doru@zaninstal.ro' => 'demo123',
                'user@zaninstal.ro' => 'user123'
            ];

            $allOk = true;
            foreach ($users as $user) {
                if (isset($passwords[$user['email']])) {
                    $testPass = $passwords[$user['email']];
                    if (password_verify($testPass, $user['parola'])) {
                        echo '<div class="step success">✅ Parola pentru <code>' . htmlspecialchars($user['email']) . '</code> este CORECTA</div>';
                    } else {
                        echo '<div class="step error">❌ Parola pentru <code>' . htmlspecialchars($user['email']) . '</code> este INCORECTA!</div>';
                        $allOk = false;
                    }
                }
            }

            echo '</div>';

            // Rezultat final
            echo '<div class="card">';
            if ($allOk) {
                echo '<div class="step success" style="font-size: 18px;">';
                echo '<strong>🎉 SUCCES COMPLET!</strong><br><br>';
                echo 'Toate parolele au fost resetate si verificate cu succes!';
                echo '</div>';

                echo '<h3 style="color: #667eea; margin: 25px 0 15px 0;">📋 Credentiale de Autentificare</h3>';
                echo '<table>';
                echo '<thead><tr><th>Rol</th><th>Email</th><th>Parola</th></tr></thead>';
                echo '<tbody>';
                echo '<tr><td><strong>Super Admin</strong></td><td><code>admin@devizo.ro</code></td><td><code>admin123</code></td></tr>';
                echo '<tr><td><strong>Master Firma</strong></td><td><code>doru@zaninstal.ro</code></td><td><code>demo123</code></td></tr>';
                echo '<tr><td><strong>Utilizator</strong></td><td><code>user@zaninstal.ro</code></td><td><code>user123</code></td></tr>';
                echo '</tbody>';
                echo '</table>';

                echo '<div style="text-align: center; margin-top: 30px;">';
                echo '<a href="login.php" class="btn">🚀 INTRA IN APLICATIE</a>';
                echo '</div>';

                echo '<div class="step warning" style="margin-top: 25px;">';
                echo '<strong>⚠️ IMPORTANT - SECURITATE!</strong><br><br>';
                echo 'STERGE IMEDIAT acest fisier (<code>fix_parole.php</code>) din server!<br>';
                echo 'Acest fisier contine credentiale sensibile si NU trebuie sa ramana pe server!';
                echo '</div>';

            } else {
                echo '<div class="step error" style="font-size: 18px;">';
                echo '<strong>❌ EROARE!</strong><br><br>';
                echo 'Au fost detectate probleme la resetarea parolelor. Verifica mesajele de mai sus.';
                echo '</div>';
            }
            echo '</div>';

        } catch (PDOException $e) {
            echo '<div class="card">';
            echo '<div class="step error">';
            echo '<strong>❌ EROARE la conectarea la baza de date!</strong><br><br>';
            echo '<strong>Mesaj eroare:</strong><br>';
            echo '<code>' . htmlspecialchars($e->getMessage()) . '</code><br><br>';
            echo '<strong>Verifica:</strong><br>';
            echo '1. Credentialele bazei de date sunt corecte<br>';
            echo '2. Baza de date <code>' . $db_name . '</code> exista<br>';
            echo '3. Utilizatorul <code>' . $db_user . '</code> are acces la baza<br>';
            echo '</div>';
            echo '</div>';
        } catch (Exception $e) {
            echo '<div class="card">';
            echo '<div class="step error">';
            echo '<strong>❌ EROARE GENERALA!</strong><br><br>';
            echo '<code>' . htmlspecialchars($e->getMessage()) . '</code>';
            echo '</div>';
            echo '</div>';
        }
        ?>

        <div class="card" style="text-align: center; color: #999; font-size: 13px;">
            <p>DEVIZO - Script Resetare Parole v1.0</p>
            <p>Generated: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
