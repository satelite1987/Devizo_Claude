<?php
/**
 * DEVIZO - Script Verificare Baza de Date
 *
 * Acest script verifica daca baza de date a fost importata corect
 * si afiseaza informatii despre utilizatorii existenti.
 *
 * INSTRUCTIUNI:
 * 1. Uploadati acest fisier in /public_html/devizo/
 * 2. Accesati: https://www.devizo.ro/devizo/verificare_db.php
 * 3. Cititi rezultatele
 * 4. STERGETI fisierul dupa verificare (securitate!)
 */

require_once __DIR__ . '/config/database.php';

// Setam display_errors pentru a vedea toate erorile
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Verificare Baza Date DEVIZO</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .box { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    h1 { color: #3498db; }
    h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
    .success { color: #2ecc71; font-weight: bold; }
    .error { color: #e74c3c; font-weight: bold; }
    .warning { color: #f39c12; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
    th { background: #3498db; color: white; }
    tr:nth-child(even) { background: #f9f9f9; }
    .info { background: #d1ecf1; padding: 10px; border-left: 4px solid #0c5460; margin: 10px 0; }
    code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
    .hash { font-family: monospace; font-size: 11px; word-break: break-all; }
</style>";
echo "</head><body>";

echo "<h1>🔍 DEVIZO - Verificare Baza de Date</h1>";

// Verificare conexiune
echo "<div class='box'>";
echo "<h2>1. Verificare Conexiune MySQL</h2>";
try {
    $db = getDB();
    echo "<p class='success'>✅ Conexiune la baza de date: REUSITA</p>";
    echo "<p>Database: <strong>" . DB_NAME . "</strong></p>";
    echo "<p>Host: <strong>" . DB_HOST . "</strong></p>";
    echo "<p>User: <strong>" . DB_USER . "</strong></p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Eroare conexiune: " . $e->getMessage() . "</p>";
    echo "<div class='info'><strong>Solutie:</strong> Verificati datele din config/database.php</div>";
    echo "</body></html>";
    exit;
}
echo "</div>";

// Verificare tabele
echo "<div class='box'>";
echo "<h2>2. Verificare Tabele</h2>";
try {
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $expectedTables = [
        'roluri', 'firme', 'utilizatori', 'parteneri', 'unitati_masura',
        'articole', 'devize', 'deviz_randuri', 'deviz_contoare',
        'cereri_oferta', 'cereri_oferta_randuri', 'oferte_furnizori',
        'oferte_furnizori_randuri', 'log_activitate'
    ];

    echo "<p>Tabele gasite: <strong>" . count($tables) . "</strong></p>";

    if (count($tables) >= 14) {
        echo "<p class='success'>✅ Toate tabelele principale exista!</p>";
    } else {
        echo "<p class='error'>❌ Lipsesc tabele! Gasit: " . count($tables) . ", Asteptat: minim 14</p>";
    }

    echo "<table>";
    echo "<tr><th>Nr.</th><th>Tabel</th><th>Status</th></tr>";
    foreach ($expectedTables as $i => $table) {
        $exists = in_array($table, $tables);
        $status = $exists ? "<span class='success'>✅ Exista</span>" : "<span class='error'>❌ LIPSA</span>";
        echo "<tr><td>" . ($i + 1) . "</td><td><code>$table</code></td><td>$status</td></tr>";
    }
    echo "</table>";

    if (count($tables) < 14) {
        echo "<div class='info'>";
        echo "<strong>Solutie:</strong> Re-importati database.sql in phpMyAdmin<br>";
        echo "1. phpMyAdmin → devizo_db → Operations → Drop database<br>";
        echo "2. Recreati baza in cPanel → MySQL Databases<br>";
        echo "3. Importati database.sql din nou";
        echo "</div>";
    }

} catch (Exception $e) {
    echo "<p class='error'>❌ Eroare verificare tabele: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Verificare utilizatori
echo "<div class='box'>";
echo "<h2>3. Verificare Utilizatori</h2>";
try {
    // Verificam daca tabelul utilizatori exista
    if (!in_array('utilizatori', $tables)) {
        echo "<p class='error'>❌ Tabelul 'utilizatori' NU EXISTA!</p>";
    } else {
        $stmt = $db->query("SELECT COUNT(*) as total FROM utilizatori");
        $count = $stmt->fetch();

        if ($count['total'] == 0) {
            echo "<p class='error'>❌ Tabelul utilizatori este GOL! (0 utilizatori)</p>";
            echo "<div class='info'>";
            echo "<strong>Problema:</strong> database.sql nu a fost importat complet sau utilizatorii demo nu au fost inserati.<br><br>";
            echo "<strong>Solutie 1:</strong> Re-importati database.sql complet<br>";
            echo "<strong>Solutie 2:</strong> Rulati scriptul de creare utilizatori demo (vezi mai jos)";
            echo "</div>";
        } else {
            echo "<p class='success'>✅ Utilizatori gasiti: <strong>" . $count['total'] . "</strong></p>";

            // Afisam detalii utilizatori
            $stmt = $db->query("
                SELECT u.id, u.email, u.nume, r.nume as rol, u.activ, u.parola
                FROM utilizatori u
                LEFT JOIN roluri r ON u.rol_id = r.id
                ORDER BY u.id
            ");
            $users = $stmt->fetchAll();

            echo "<table>";
            echo "<tr><th>ID</th><th>Email</th><th>Nume</th><th>Rol</th><th>Activ</th><th>Hash Parola</th></tr>";
            foreach ($users as $user) {
                $activ = $user['activ'] ? "<span class='success'>DA</span>" : "<span class='error'>NU</span>";
                $hashPreview = substr($user['parola'], 0, 30) . '...';
                echo "<tr>";
                echo "<td>" . $user['id'] . "</td>";
                echo "<td><strong>" . $user['email'] . "</strong></td>";
                echo "<td>" . $user['nume'] . "</td>";
                echo "<td>" . ($user['rol'] ?? 'Necunoscut') . "</td>";
                echo "<td>" . $activ . "</td>";
                echo "<td class='hash'>" . $hashPreview . "</td>";
                echo "</tr>";
            }
            echo "</table>";

            // Verificam hash-ul parolei pentru admin
            $adminUser = null;
            foreach ($users as $user) {
                if ($user['email'] == 'admin@devizo.ro') {
                    $adminUser = $user;
                    break;
                }
            }

            if ($adminUser) {
                echo "<h3>Verificare Parola Admin</h3>";
                $testPassword = 'admin123';
                $isValid = password_verify($testPassword, $adminUser['parola']);

                if ($isValid) {
                    echo "<p class='success'>✅ Hash-ul parolei pentru admin@devizo.ro este CORECT!</p>";
                    echo "<p>Parola <code>admin123</code> ar trebui sa functioneze.</p>";
                } else {
                    echo "<p class='error'>❌ Hash-ul parolei pentru admin@devizo.ro este GRESIT!</p>";
                    echo "<p>Hash gasit: <code class='hash'>" . $adminUser['parola'] . "</code></p>";
                    echo "<p>Hash asteptat: <code class='hash'>\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi</code></p>";
                }
            } else {
                echo "<p class='error'>❌ Utilizatorul admin@devizo.ro NU EXISTA!</p>";
            }
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Eroare verificare utilizatori: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Verificare roluri
echo "<div class='box'>";
echo "<h2>4. Verificare Roluri</h2>";
try {
    if (in_array('roluri', $tables)) {
        $stmt = $db->query("SELECT * FROM roluri ORDER BY id");
        $roluri = $stmt->fetchAll();

        if (count($roluri) == 0) {
            echo "<p class='error'>❌ Tabelul roluri este GOL!</p>";
        } else {
            echo "<p class='success'>✅ Roluri gasite: <strong>" . count($roluri) . "</strong></p>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Nume</th><th>Descriere</th></tr>";
            foreach ($roluri as $rol) {
                echo "<tr>";
                echo "<td>" . $rol['id'] . "</td>";
                echo "<td><strong>" . $rol['nume'] . "</strong></td>";
                echo "<td>" . $rol['descriere'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p class='error'>❌ Tabelul 'roluri' NU EXISTA!</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Eroare: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Verificare firme
echo "<div class='box'>";
echo "<h2>5. Verificare Firme Demo</h2>";
try {
    if (in_array('firme', $tables)) {
        $stmt = $db->query("SELECT COUNT(*) as total FROM firme");
        $count = $stmt->fetch();

        if ($count['total'] == 0) {
            echo "<p class='warning'>⚠️ Nu exista firme in baza de date (normal la prima instalare)</p>";
        } else {
            echo "<p class='success'>✅ Firme gasite: <strong>" . $count['total'] . "</strong></p>";

            $stmt = $db->query("SELECT id, denumire, cui, abonament_activ, data_expirare_abonament FROM firme");
            $firme = $stmt->fetchAll();

            echo "<table>";
            echo "<tr><th>ID</th><th>Denumire</th><th>CUI</th><th>Abonament</th><th>Expira</th></tr>";
            foreach ($firme as $firma) {
                $abonament = $firma['abonament_activ'] ? "<span class='success'>Activ</span>" : "<span class='error'>Inactiv</span>";
                echo "<tr>";
                echo "<td>" . $firma['id'] . "</td>";
                echo "<td><strong>" . $firma['denumire'] . "</strong></td>";
                echo "<td>" . $firma['cui'] . "</td>";
                echo "<td>" . $abonament . "</td>";
                echo "<td>" . $firma['data_expirare_abonament'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Eroare: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Concluzie si actiuni
echo "<div class='box'>";
echo "<h2>6. Concluzie si Actiuni Recomandate</h2>";

$allGood = true;
$actions = [];

if (count($tables) < 14) {
    $allGood = false;
    $actions[] = "Re-importati database.sql complet in phpMyAdmin";
}

if (isset($count) && $count['total'] == 0) {
    $allGood = false;
    $actions[] = "Utilizatorii demo lipsesc - rulati scriptul de creare (vezi mai jos)";
}

if (isset($adminUser) && !password_verify('admin123', $adminUser['parola'])) {
    $allGood = false;
    $actions[] = "Hash parola admin este gresit - resetati parola";
}

if ($allGood) {
    echo "<p class='success' style='font-size: 18px;'>✅ TOTUL ESTE OK!</p>";
    echo "<p>Baza de date este configurata corect. Ar trebui sa va puteti autentifica.</p>";
    echo "<p><strong>Testati login cu:</strong></p>";
    echo "<ul>";
    echo "<li>Email: <code>admin@devizo.ro</code></li>";
    echo "<li>Parola: <code>admin123</code></li>";
    echo "</ul>";
} else {
    echo "<p class='error' style='font-size: 18px;'>❌ PROBLEME GASITE!</p>";
    echo "<p><strong>Actiuni necesare:</strong></p>";
    echo "<ol>";
    foreach ($actions as $action) {
        echo "<li>" . $action . "</li>";
    }
    echo "</ol>";
}

echo "</div>";

// Script de creare utilizatori demo
echo "<div class='box'>";
echo "<h2>7. Script Creare Utilizatori Demo (daca lipsesc)</h2>";
echo "<p>Daca utilizatorii demo lipsesc, accesati:</p>";
echo "<p><a href='creare_utilizatori_demo.php' style='background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;'>Creaza Utilizatori Demo</a></p>";
echo "</div>";

echo "<div class='box' style='background: #fff3cd; border-left: 4px solid #f39c12;'>";
echo "<h2>⚠️ SECURITATE</h2>";
echo "<p><strong>IMPORTANT:</strong> Dupa verificare, STERGETI acest fisier de pe server!</p>";
echo "<p>Fisier de sters: <code>/public_html/devizo/verificare_db.php</code></p>";
echo "</div>";

echo "<p style='text-align: center; color: #999; margin-top: 40px;'>DEVIZO v1.0.0 - Verificare Baza de Date</p>";
echo "</body></html>";
