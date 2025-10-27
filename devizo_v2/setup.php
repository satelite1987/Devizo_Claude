<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DEVIZO v2.0 - Setup</title>
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
            font-size: 32px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        h2 {
            color: #667eea;
            margin: 25px 0 15px 0;
            font-size: 20px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 8px;
        }
        .step {
            padding: 15px;
            margin: 12px 0;
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
        code {
            background: #f4f4f4;
            padding: 2px 8px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            margin-top: 20px;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
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
        .icon { font-size: 40px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1><span class="icon">🚀</span> DEVIZO v2.0 Setup</h1>
            <p style="color: #666; font-size: 16px;">
                Configurare inițială și generare hash-uri parolă
            </p>
        </div>

        <?php
        error_reporting(E_ALL);
        ini_set('display_errors', '1');

        // Database configuration
        $db_host = 'localhost';
        $db_name = 'devizo_nodex';
        $db_user = 'devizo_Zeus';
        $db_pass = 'Satelite1987!@#';

        $allOk = true;
        $errors = [];

        try {
            echo '<div class="card">';
            echo '<h2>📡 Pasul 1: Conectare la Baza de Date</h2>';

            $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ];

            $pdo = new PDO($dsn, $db_user, $db_pass, $options);

            echo '<div class="step success">';
            echo '<strong>✅ SUCCES!</strong> Conectat la baza de date <code>' . htmlspecialchars($db_name) . '</code>';
            echo '</div>';
            echo '</div>';

            // Check if tables exist
            echo '<div class="card">';
            echo '<h2>🗄️ Pasul 2: Verificare Tabele</h2>';

            $requiredTables = [
                'roluri', 'firme', 'utilizatori', 'module_sistem',
                'firme_module', 'utilizatori_permisiuni', 'devize',
                'deviz_randuri', 'articole', 'parteneri', 'unitati_masura'
            ];

            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $missingTables = array_diff($requiredTables, $tables);

            if (empty($missingTables)) {
                echo '<div class="step success">';
                echo '<strong>✅ SUCCES!</strong> Toate tabelele necesare există (' . count($requiredTables) . ' tabele)';
                echo '</div>';
            } else {
                echo '<div class="step error">';
                echo '<strong>❌ EROARE!</strong> Lipsesc tabele: ' . implode(', ', $missingTables);
                echo '<br><br><strong>Soluție:</strong> Importați mai întâi <code>schema.sql</code> și <code>demo-data.sql</code> în phpMyAdmin';
                echo '</div>';
                $allOk = false;
                $errors[] = 'Missing tables';
            }
            echo '</div>';

            if ($allOk) {
                // Generate password hashes
                echo '<div class="card">';
                echo '<h2>🔐 Pasul 3: Generare Hash-uri Parolă</h2>';

                $passwords = [
                    'imperator@devizo.ro' => 'Satelite1987!@#',
                    'dominus@demo.devizo.ro' => 'Demo123',
                    'executor@demo.devizo.ro' => 'Demo123'
                ];

                $hashes = [];
                foreach ($passwords as $email => $pass) {
                    $hash = password_hash($pass, PASSWORD_DEFAULT);
                    $hashes[$email] = $hash;

                    echo '<div class="step success">';
                    echo '<strong>✅</strong> Hash generat pentru <code>' . htmlspecialchars($email) . '</code>';
                    echo '<br><small style="color: #666;">Parolă: ' . htmlspecialchars($pass) . '</small>';
                    echo '</div>';
                }
                echo '</div>';

                // Update passwords
                echo '<div class="card">';
                echo '<h2>💾 Pasul 4: Actualizare Parole în Baza de Date</h2>';

                $stmt = $pdo->prepare("UPDATE utilizatori SET parola = ? WHERE email = ?");

                foreach ($hashes as $email => $hash) {
                    $stmt->execute([$hash, $email]);

                    echo '<div class="step success">';
                    echo '<strong>✅</strong> Parola actualizată pentru <code>' . htmlspecialchars($email) . '</code>';
                    echo '</div>';
                }
                echo '</div>';

                // Verify passwords
                echo '<div class="card">';
                echo '<h2>🧪 Pasul 5: Verificare Parolă</h2>';

                $stmt = $pdo->query("SELECT id, email, parola FROM utilizatori WHERE id IN (1,2,3) ORDER BY id");
                $users = $stmt->fetchAll();

                $allPasswordsOk = true;
                foreach ($users as $user) {
                    if (isset($passwords[$user['email']])) {
                        $testPass = $passwords[$user['email']];
                        if (password_verify($testPass, $user['parola'])) {
                            echo '<div class="step success">';
                            echo '<strong>✅ CORECT!</strong> Parola pentru <code>' . htmlspecialchars($user['email']) . '</code> funcționează';
                            echo '</div>';
                        } else {
                            echo '<div class="step error">';
                            echo '<strong>❌ EROARE!</strong> Parola pentru <code>' . htmlspecialchars($user['email']) . '</code> NU funcționează!';
                            echo '</div>';
                            $allPasswordsOk = false;
                        }
                    }
                }
                echo '</div>';

                // Final status
                echo '<div class="card">';
                if ($allPasswordsOk) {
                    echo '<div class="step success" style="font-size: 18px; padding: 25px;">';
                    echo '<strong style="font-size: 24px;">🎉 SETUP COMPLET!</strong><br><br>';
                    echo 'Aplicația DEVIZO v2.0 este configurată și gata de utilizare!';
                    echo '</div>';

                    echo '<h2 style="margin-top: 30px;">📋 Credențiale Autentificare</h2>';
                    echo '<table>';
                    echo '<thead><tr><th>Rol</th><th>Email</th><th>Parolă</th><th>Vizibil pe Login</th></tr></thead>';
                    echo '<tbody>';
                    echo '<tr>';
                    echo '<td><strong>Super Admin</strong></td>';
                    echo '<td><code>imperator@devizo.ro</code></td>';
                    echo '<td><code>Satelite1987!@#</code></td>';
                    echo '<td>❌ ASCUNS</td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td><strong>Master Firmă</strong></td>';
                    echo '<td><code>dominus@demo.devizo.ro</code></td>';
                    echo '<td><code>Demo123</code></td>';
                    echo '<td>✅ VIZIBIL</td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td><strong>Utilizator</strong></td>';
                    echo '<td><code>executor@demo.devizo.ro</code></td>';
                    echo '<td><code>Demo123</code></td>';
                    echo '<td>✅ VIZIBIL</td>';
                    echo '</tr>';
                    echo '</tbody>';
                    echo '</table>';

                    echo '<div class="step warning" style="margin-top: 25px;">';
                    echo '<strong>⚠️ IMPORTANT - SECURITATE!</strong><br><br>';
                    echo '1. <strong>ȘTERGE</strong> imediat acest fișier (<code>setup.php</code>) din server!<br>';
                    echo '2. Schimbă parola pentru <code>imperator@devizo.ro</code> după primul login<br>';
                    echo '3. Verifică permisiunile directoarelor:<br>';
                    echo '&nbsp;&nbsp;&nbsp;- <code>logs/</code> → 777 (sau 755)<br>';
                    echo '&nbsp;&nbsp;&nbsp;- <code>uploads/</code> → 777 (sau 755)<br>';
                    echo '&nbsp;&nbsp;&nbsp;- <code>cache/</code> → 777 (sau 755)';
                    echo '</div>';

                    echo '<div style="text-align: center; margin-top: 30px;">';
                    echo '<a href="index.php" class="btn">🚀 INTRĂ ÎN APLICAȚIE</a>';
                    echo '</div>';
                } else {
                    echo '<div class="step error" style="font-size: 18px;">';
                    echo '<strong>❌ EROARE LA VERIFICARE!</strong><br><br>';
                    echo 'Au fost detectate probleme la verificarea parolelor.';
                    echo '</div>';
                }
                echo '</div>';
            }

        } catch (PDOException $e) {
            echo '<div class="card">';
            echo '<div class="step error">';
            echo '<strong>❌ EROARE la conectarea la baza de date!</strong><br><br>';
            echo '<strong>Mesaj eroare:</strong><br>';
            echo '<code>' . htmlspecialchars($e->getMessage()) . '</code><br><br>';
            echo '<strong>Verifică:</strong><br>';
            echo '1. Baza de date <code>devizo_nodex</code> există<br>';
            echo '2. Utilizatorul <code>devizo_Zeus</code> are acces la baza<br>';
            echo '3. Parola <code>Satelite1987!@#</code> este corectă<br>';
            echo '4. Ai importat <code>schema.sql</code> în phpMyAdmin';
            echo '</div>';
            echo '</div>';
        } catch (Exception $e) {
            echo '<div class="card">';
            echo '<div class="step error">';
            echo '<strong>❌ EROARE GENERALĂ!</strong><br><br>';
            echo '<code>' . htmlspecialchars($e->getMessage()) . '</code>';
            echo '</div>';
            echo '</div>';
        }
        ?>

        <div class="card" style="text-align: center; color: #999; font-size: 13px; margin-top: 30px;">
            <p><strong>DEVIZO v2.0</strong> - Setup Script</p>
            <p>Modular Multi-Tenant SaaS Application</p>
            <p>Generated: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
