<?php
/**
 * DEVIZO - Diagnostic Complet Fisiere si Functionalitate
 *
 * IMPORTANT: Ruleaza acest script pentru a verifica ce lipseste pe server!
 * Acceseaza: https://www.devizo.ro/diagnostic_complet.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

?><!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic Complet DEVIZO</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            color: #333;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 28px;
        }
        h2 {
            color: #667eea;
            margin: 20px 0 15px 0;
            font-size: 20px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 8px;
        }
        .check {
            padding: 12px;
            margin: 8px 0;
            border-radius: 6px;
            border-left: 4px solid #ccc;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .check.success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .check.error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .check.warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        .icon { font-size: 20px; font-weight: bold; }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 13px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-box {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
        }
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>🔍 Diagnostic Complet DEVIZO</h1>
            <p style="color: #666;">Verificare automată a tuturor componentelor aplicației</p>
        </div>

        <?php
        $allOk = true;
        $missingFiles = [];
        $missingDirs = [];
        $totalChecks = 0;
        $passedChecks = 0;

        // Lista completa de fisiere obligatorii
        $requiredFiles = [
            // Root
            'index.php',
            'login.php',
            'logout.php',
            'devize.php',
            'deviz.php',
            'deviz-nou.php',
            'articole.php',
            'parteneri.php',
            'unitati-masura.php',
            'utilizatori.php',
            'cereri-oferta.php',
            'profil.php',
            'schimba-parola.php',
            'setari-firma.php',
            '.htaccess',
            'database.sql',
            'INSTALLATION_GUIDE.md',

            // Config
            'config/database.php',

            // Includes
            'includes/auth.php',
            'includes/functions.php',
            'includes/header.php',
            'includes/footer.php',

            // API
            'api/devize.php',
            'api/articole.php',
            'api/parteneri.php',
            'api/unitati-masura.php',
            'api/utilizatori.php',
            'api/cereri-oferta.php',
            'api/anaf.php',
            'api/admin.php',

            // Admin
            'admin/index.php',
        ];

        $requiredDirs = [
            'config',
            'includes',
            'api',
            'admin',
            'assets',
            'assets/css',
            'assets/js',
            'assets/uploads',
            'logs',
            'pdf',
        ];

        echo '<div class="card">';
        echo '<h2>📁 Verificare Directoare</h2>';

        foreach ($requiredDirs as $dir) {
            $totalChecks++;
            $exists = is_dir(__DIR__ . '/' . $dir);
            $writable = $exists ? is_writable(__DIR__ . '/' . $dir) : false;

            if ($exists) {
                $passedChecks++;
                echo '<div class="check success">';
                echo '<span class="icon">✓</span>';
                echo '<div>';
                echo '<strong>Directorul <code>' . htmlspecialchars($dir) . '</code> există</strong>';
                if ($writable) {
                    echo ' <span style="color: #28a745;">(Scris: DA)</span>';
                } else if (in_array($dir, ['logs', 'assets/uploads', 'pdf'])) {
                    echo ' <span style="color: #dc3545;">(Scris: NU - PROBLEMA!)</span>';
                    $allOk = false;
                }
                echo '</div>';
                echo '</div>';
            } else {
                $allOk = false;
                $missingDirs[] = $dir;
                echo '<div class="check error">';
                echo '<span class="icon">✗</span>';
                echo '<strong>LIPSEȘTE: Directorul <code>' . htmlspecialchars($dir) . '</code></strong>';
                echo '</div>';
            }
        }

        echo '</div>';

        echo '<div class="card">';
        echo '<h2>📄 Verificare Fisiere</h2>';

        foreach ($requiredFiles as $file) {
            $totalChecks++;
            $exists = file_exists(__DIR__ . '/' . $file);
            $size = $exists ? filesize(__DIR__ . '/' . $file) : 0;

            if ($exists && $size > 0) {
                $passedChecks++;
                echo '<div class="check success">';
                echo '<span class="icon">✓</span>';
                echo '<div>';
                echo '<strong><code>' . htmlspecialchars($file) . '</code></strong>';
                echo '<span style="margin-left: 10px; color: #666;">(' . number_format($size / 1024, 1) . ' KB)</span>';
                echo '</div>';
                echo '</div>';
            } else if ($exists && $size == 0) {
                $allOk = false;
                echo '<div class="check warning">';
                echo '<span class="icon">⚠</span>';
                echo '<strong>FIȘIER GOL: <code>' . htmlspecialchars($file) . '</code></strong>';
                echo '</div>';
            } else {
                $allOk = false;
                $missingFiles[] = $file;
                echo '<div class="check error">';
                echo '<span class="icon">✗</span>';
                echo '<strong>LIPSEȘTE: <code>' . htmlspecialchars($file) . '</code></strong>';
                echo '</div>';
            }
        }

        echo '</div>';

        // Verificare conexiune database
        echo '<div class="card">';
        echo '<h2>🗄️ Verificare Baza de Date</h2>';

        $dbOk = false;
        try {
            require_once __DIR__ . '/config/database.php';

            $totalChecks++;
            echo '<div class="check success">';
            echo '<span class="icon">✓</span>';
            echo '<strong>Conectare la baza de date: OK</strong>';
            echo '</div>';
            $passedChecks++;
            $dbOk = true;

            $db = getDB();

            // Verificare tabele
            $tables = [
                'utilizatori', 'roluri', 'firme', 'devize', 'devize_randuri',
                'articole', 'parteneri', 'unitati_masura', 'cereri_oferta',
                'cereri_oferta_randuri', 'log_activitate'
            ];

            foreach ($tables as $table) {
                $totalChecks++;
                try {
                    $stmt = $db->query("SELECT COUNT(*) FROM $table");
                    $count = $stmt->fetchColumn();
                    $passedChecks++;

                    echo '<div class="check success">';
                    echo '<span class="icon">✓</span>';
                    echo '<strong>Tabel <code>' . htmlspecialchars($table) . '</code>: </strong>';
                    echo '<span style="color: #28a745;">' . $count . ' înregistrări</span>';
                    echo '</div>';
                } catch (PDOException $e) {
                    $allOk = false;
                    echo '<div class="check error">';
                    echo '<span class="icon">✗</span>';
                    echo '<strong>LIPSEȘTE tabel <code>' . htmlspecialchars($table) . '</code></strong>';
                    echo '</div>';
                }
            }

        } catch (Exception $e) {
            $allOk = false;
            echo '<div class="check error">';
            echo '<span class="icon">✗</span>';
            echo '<strong>EROARE conexiune baza de date:</strong><br>';
            echo '<code>' . htmlspecialchars($e->getMessage()) . '</code>';
            echo '</div>';
        }

        echo '</div>';

        // Statistici
        echo '<div class="card">';
        echo '<h2>📊 Statistici Verificare</h2>';
        echo '<div class="stats">';
        echo '<div class="stat-box">';
        echo '<div class="stat-label">Total Verificări</div>';
        echo '<div class="stat-number">' . $totalChecks . '</div>';
        echo '</div>';
        echo '<div class="stat-box">';
        echo '<div class="stat-label">Verificări Reușite</div>';
        echo '<div class="stat-number">' . $passedChecks . '</div>';
        echo '</div>';
        echo '<div class="stat-box">';
        echo '<div class="stat-label">Erori Găsite</div>';
        echo '<div class="stat-number" style="color: ' . (($totalChecks - $passedChecks) > 0 ? '#ff6b6b' : 'white') . '">' . ($totalChecks - $passedChecks) . '</div>';
        echo '</div>';
        echo '<div class="stat-box">';
        echo '<div class="stat-label">Procent Succes</div>';
        echo '<div class="stat-number">' . round(($passedChecks / $totalChecks) * 100) . '%</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // Rezultat final
        echo '<div class="card">';
        if ($allOk) {
            echo '<div class="check success" style="font-size: 18px; padding: 20px;">';
            echo '<span class="icon" style="font-size: 32px;">✓</span>';
            echo '<div>';
            echo '<strong>🎉 TOATE VERIFICĂRILE AU TRECUT!</strong><br><br>';
            echo 'Aplicația DEVIZO este instalată corect și toate fișierele sunt prezente.';
            echo '<br><br><a href="login.php" style="display: inline-block; padding: 12px 25px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; font-weight: 600; margin-top: 10px;">🚀 INTRA IN APLICATIE</a>';
            echo '</div>';
            echo '</div>';
        } else {
            echo '<div class="check error" style="font-size: 18px; padding: 20px;">';
            echo '<span class="icon" style="font-size: 32px;">✗</span>';
            echo '<div>';
            echo '<strong>⚠️ AU FOST GĂSITE PROBLEME!</strong><br><br>';

            if (!empty($missingDirs)) {
                echo '<strong>Directoare lipsă:</strong><br>';
                echo '<ul style="margin: 10px 0 10px 20px;">';
                foreach ($missingDirs as $dir) {
                    echo '<li><code>' . htmlspecialchars($dir) . '</code></li>';
                }
                echo '</ul>';
            }

            if (!empty($missingFiles)) {
                echo '<strong>Fișiere lipsă:</strong><br>';
                echo '<ul style="margin: 10px 0 10px 20px;">';
                foreach ($missingFiles as $file) {
                    echo '<li><code>' . htmlspecialchars($file) . '</code></li>';
                }
                echo '</ul>';
            }

            echo '<br><strong>SOLUȚIE:</strong><br>';
            echo '1. Descarcă arhiva COMPLETĂ de pe GitHub<br>';
            echo '2. Extrage TOATE fișierele și directoarele<br>';
            echo '3. Upload-ează COMPLET folderul <code>devizo_complete/</code> pe server<br>';
            echo '4. Rulează din nou acest diagnostic<br>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';

        // Info versiune
        echo '<div class="card" style="text-align: center; color: #999; font-size: 13px;">';
        echo '<p>DEVIZO Diagnostic Script v1.0</p>';
        echo '<p>Server: ' . htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . '</p>';
        echo '<p>PHP: ' . PHP_VERSION . '</p>';
        echo '<p>Generated: ' . date('Y-m-d H:i:s') . '</p>';
        echo '</div>';
        ?>
    </div>
</body>
</html>
