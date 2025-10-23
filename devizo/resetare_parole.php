<?php
/**
 * DEVIZO - Script Resetare Parole Demo
 *
 * Acest script reseteaza parolele utilizatorilor demo cu hash-uri corecte
 *
 * INSTRUCTIUNI:
 * 1. Uploadati in /public_html/devizo/
 * 2. Accesati: https://www.devizo.ro/devizo/resetare_parole.php
 * 3. STERGETI fisierul dupa utilizare!
 */

require_once __DIR__ . '/config/database.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Resetare Parole Demo - DEVIZO</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .box { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    h1 { color: #3498db; }
    .success { color: #2ecc71; font-weight: bold; }
    .error { color: #e74c3c; font-weight: bold; }
    .warning { color: #f39c12; font-weight: bold; }
    code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; font-family: monospace; }
    .info { background: #d1ecf1; padding: 15px; border-left: 4px solid #0c5460; margin: 10px 0; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
    th { background: #3498db; color: white; }
    tr:nth-child(even) { background: #f9f9f9; }
    .hash { font-size: 11px; word-break: break-all; }
</style>";
echo "</head><body>";

echo "<h1>🔐 Resetare Parole Utilizatori Demo</h1>";

try {
    $db = getDB();

    echo "<div class='box'>";
    echo "<h2>1. Generare Hash-uri Noi</h2>";
    echo "<p>Generez hash-uri corecte pentru parolele demo...</p>";

    // Generam hash-uri noi pentru fiecare parola
    $parole = [
        'admin123' => password_hash('admin123', PASSWORD_DEFAULT),
        'demo123' => password_hash('demo123', PASSWORD_DEFAULT),
        'user123' => password_hash('user123', PASSWORD_DEFAULT)
    ];

    echo "<table>";
    echo "<tr><th>Parola</th><th>Hash Generat (BCrypt)</th></tr>";
    foreach ($parole as $text => $hash) {
        echo "<tr>";
        echo "<td><code><strong>$text</strong></code></td>";
        echo "<td class='hash'>$hash</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<p class='success'>✅ Hash-uri generate cu succes!</p>";
    echo "</div>";

    // Resetam parolele pentru utilizatorii demo
    echo "<div class='box'>";
    echo "<h2>2. Resetare Parole Utilizatori</h2>";

    $utilizatori = [
        [
            'email' => 'admin@devizo.ro',
            'parola_text' => 'admin123',
            'parola_hash' => $parole['admin123'],
            'nume' => 'Super Administrator'
        ],
        [
            'email' => 'doru@zaninstal.ro',
            'parola_text' => 'demo123',
            'parola_hash' => $parole['demo123'],
            'nume' => 'Batagui Doru'
        ],
        [
            'email' => 'user@zaninstal.ro',
            'parola_text' => 'user123',
            'parola_hash' => $parole['user123'],
            'nume' => 'Utilizator Demo'
        ]
    ];

    echo "<table>";
    echo "<tr><th>Email</th><th>Nume</th><th>Parola Noua</th><th>Status</th><th>Verificare</th></tr>";

    foreach ($utilizatori as $user) {
        // Verificam daca utilizatorul exista
        $stmt = $db->prepare("SELECT id, parola FROM utilizatori WHERE email = ?");
        $stmt->execute([$user['email']]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Actualizam parola
            $stmt = $db->prepare("UPDATE utilizatori SET parola = ? WHERE email = ?");
            $stmt->execute([$user['parola_hash'], $user['email']]);

            // Verificam daca hash-ul functioneaza
            $stmt = $db->prepare("SELECT parola FROM utilizatori WHERE email = ?");
            $stmt->execute([$user['email']]);
            $updated = $stmt->fetch();

            $verificare = password_verify($user['parola_text'], $updated['parola']);

            echo "<tr>";
            echo "<td><code>" . $user['email'] . "</code></td>";
            echo "<td>" . $user['nume'] . "</td>";
            echo "<td><strong><code>" . $user['parola_text'] . "</code></strong></td>";
            echo "<td class='success'>✅ ACTUALIZAT</td>";

            if ($verificare) {
                echo "<td class='success'>✅ Hash CORECT</td>";
            } else {
                echo "<td class='error'>❌ Hash GRESIT</td>";
            }
            echo "</tr>";
        } else {
            echo "<tr>";
            echo "<td><code>" . $user['email'] . "</code></td>";
            echo "<td>" . $user['nume'] . "</td>";
            echo "<td><code>" . $user['parola_text'] . "</code></td>";
            echo "<td class='error'>❌ NU EXISTA</td>";
            echo "<td>-</td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    echo "</div>";

    // Test final
    echo "<div class='box'>";
    echo "<h2>3. Test Autentificare</h2>";

    $allOk = true;
    foreach ($utilizatori as $user) {
        $stmt = $db->prepare("SELECT parola FROM utilizatori WHERE email = ?");
        $stmt->execute([$user['email']]);
        $existing = $stmt->fetch();

        if ($existing && password_verify($user['parola_text'], $existing['parola'])) {
            echo "<p class='success'>✅ <strong>" . $user['email'] . "</strong> cu parola <code>" . $user['parola_text'] . "</code> = FUNCTIONEAZA!</p>";
        } else {
            echo "<p class='error'>❌ <strong>" . $user['email'] . "</strong> cu parola <code>" . $user['parola_text'] . "</code> = NU FUNCTIONEAZA!</p>";
            $allOk = false;
        }
    }

    echo "</div>";

    // Succes final
    if ($allOk) {
        echo "<div class='box' style='background: #d4edda; border-left: 4px solid #2ecc71;'>";
        echo "<h2>✅ SUCCES COMPLET!</h2>";
        echo "<p style='font-size: 18px;'><strong>Toate parolele au fost resetate cu succes!</strong></p>";
        echo "<p>Puteti acum sa va autentificati cu:</p>";
        echo "<table>";
        echo "<tr><th>Rol</th><th>Email</th><th>Parola</th></tr>";
        echo "<tr><td><strong>Super Admin</strong></td><td><code>admin@devizo.ro</code></td><td><strong><code>admin123</code></strong></td></tr>";
        echo "<tr><td><strong>Master Firma</strong></td><td><code>doru@zaninstal.ro</code></td><td><strong><code>demo123</code></strong></td></tr>";
        echo "<tr><td><strong>Utilizator</strong></td><td><code>user@zaninstal.ro</code></td><td><strong><code>user123</code></strong></td></tr>";
        echo "</table>";
        echo "<p style='margin-top: 20px;'><a href='login.php' style='background: #3498db; color: white; padding: 15px 30px; text-decoration: none; border-radius: 4px; display: inline-block; font-size: 16px; font-weight: bold;'>🚀 MERGI LA LOGIN</a></p>";
        echo "</div>";
    } else {
        echo "<div class='box' style='background: #f8d7da; border-left: 4px solid #e74c3c;'>";
        echo "<h2>❌ PROBLEME GASITE</h2>";
        echo "<p>Unele parole nu functioneaza corect. Verificati erorile de mai sus.</p>";
        echo "</div>";
    }

    // Informatii hash-uri
    echo "<div class='box'>";
    echo "<h2>4. Informatii Tehnice</h2>";
    echo "<p><strong>Problema initiala:</strong></p>";
    echo "<p>Hash-ul initial folosit (<code>\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye...</code>) era un hash de test din Laravel care corespunde parolei <strong>'password'</strong>, NU parolelor demo.</p>";
    echo "<p><strong>Solutia aplicata:</strong></p>";
    echo "<p>Am generat hash-uri BCrypt noi pentru fiecare parola demo:</p>";
    echo "<ul>";
    echo "<li><code>admin123</code> → Hash BCrypt nou (functional)</li>";
    echo "<li><code>demo123</code> → Hash BCrypt nou (functional)</li>";
    echo "<li><code>user123</code> → Hash BCrypt nou (functional)</li>";
    echo "</ul>";
    echo "<p><strong>Algoritm:</strong> BCrypt (PHP password_hash cu PASSWORD_DEFAULT)</p>";
    echo "<p><strong>Cost Factor:</strong> 10 (default PHP)</p>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='box' style='background: #f8d7da; border-left: 4px solid #e74c3c;'>";
    echo "<h2 class='error'>❌ EROARE</h2>";
    echo "<p><strong>Mesaj:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Trace:</strong></p>";
    echo "<pre style='background: #f4f4f4; padding: 10px; overflow: auto; font-size: 11px;'>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "<div class='box' style='background: #fff3cd; border-left: 4px solid #f39c12;'>";
echo "<h2>⚠️ SECURITATE - ACTIUNE URGENTA!</h2>";
echo "<p style='font-size: 16px;'><strong>STERGETI IMEDIAT acest fisier dupa utilizare!</strong></p>";
echo "<p>Fisiere de sters din <code>/public_html/devizo/</code>:</p>";
echo "<ul>";
echo "<li><code>resetare_parole.php</code> (ACEST FISIER)</li>";
echo "<li><code>verificare_db.php</code></li>";
echo "<li><code>creare_utilizatori_demo.php</code></li>";
echo "</ul>";
echo "<p class='error'>Aceste fisiere contin informatii sensibile si NU trebuie lasate pe server!</p>";
echo "</div>";

echo "<p style='text-align: center; color: #999; margin-top: 40px;'>DEVIZO v1.0.0 - Resetare Parole</p>";
echo "</body></html>";
