<?php
/**
 * DEVIZO - Script Creare Utilizatori Demo
 *
 * Acest script creaza utilizatorii demo daca lipsesc din baza de date
 *
 * INSTRUCTIUNI:
 * 1. Uploadati in /public_html/devizo/
 * 2. Accesati: https://www.devizo.ro/devizo/creare_utilizatori_demo.php
 * 3. STERGETI fisierul dupa utilizare (securitate!)
 */

require_once __DIR__ . '/config/database.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Creare Utilizatori Demo - DEVIZO</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .box { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    h1 { color: #3498db; }
    .success { color: #2ecc71; font-weight: bold; }
    .error { color: #e74c3c; font-weight: bold; }
    .warning { color: #f39c12; font-weight: bold; }
    code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
    .info { background: #d1ecf1; padding: 15px; border-left: 4px solid #0c5460; margin: 10px 0; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
    th { background: #3498db; color: white; }
</style>";
echo "</head><body>";

echo "<h1>👤 Creare Utilizatori Demo DEVIZO</h1>";

try {
    $db = getDB();

    // Verificam daca tabelele exista
    $stmt = $db->query("SHOW TABLES LIKE 'roluri'");
    if ($stmt->rowCount() == 0) {
        throw new Exception("Tabelul 'roluri' nu exista! Va rugam sa importati database.sql mai intai.");
    }

    $stmt = $db->query("SHOW TABLES LIKE 'utilizatori'");
    if ($stmt->rowCount() == 0) {
        throw new Exception("Tabelul 'utilizatori' nu exista! Va rugam sa importati database.sql mai intai.");
    }

    $stmt = $db->query("SHOW TABLES LIKE 'firme'");
    if ($stmt->rowCount() == 0) {
        throw new Exception("Tabelul 'firme' nu exista! Va rugam sa importati database.sql mai intai.");
    }

    echo "<div class='box'>";
    echo "<h2>Verificare Tabele</h2>";
    echo "<p class='success'>✅ Toate tabelele necesare exista!</p>";
    echo "</div>";

    // Verificam si cream rolurile daca lipsesc
    echo "<div class='box'>";
    echo "<h2>1. Verificare/Creare Roluri</h2>";

    $stmt = $db->query("SELECT COUNT(*) as total FROM roluri");
    $count = $stmt->fetch();

    if ($count['total'] == 0) {
        echo "<p class='warning'>⚠️ Tabelul roluri este gol. Cream rolurile...</p>";

        $db->exec("
            INSERT INTO roluri (id, nume, descriere) VALUES
            (1, 'Super Admin', 'Administrator principal al aplicatiei - gestioneaza toate firmele'),
            (2, 'Master Firma', 'Administrator al unei firme - poate crea utilizatori si gestiona datele firmei'),
            (3, 'Utilizator Firma', 'Utilizator normal - poate crea devize si gestiona articole')
        ");

        echo "<p class='success'>✅ Roluri create cu succes!</p>";
    } else {
        echo "<p class='success'>✅ Roluri existente: " . $count['total'] . "</p>";
    }
    echo "</div>";

    // Cream firma demo daca nu exista
    echo "<div class='box'>";
    echo "<h2>2. Verificare/Creare Firma Demo</h2>";

    $stmt = $db->query("SELECT * FROM firme WHERE cui = 'RO46632242'");
    $firmaDemo = $stmt->fetch();

    if (!$firmaDemo) {
        echo "<p class='warning'>⚠️ Firma demo nu exista. O cream...</p>";

        $db->exec("
            INSERT INTO firme (cui, denumire, nr_reg_com, adresa, telefon, email,
                               abonament_activ, data_start_abonament, data_expirare_abonament,
                               max_utilizatori, procent_manopera, import_csv_activ)
            VALUES ('RO46632242', 'ENERGY ZAN INSTAL SRL', 'J40/15628/2022',
                    'Bucuresti, Sector 4', '0767573469', 'office@zaninstal.ro',
                    1, '2025-01-01', '2025-12-31', 5, 30.00, 1)
        ");

        $firmaId = $db->lastInsertId();
        echo "<p class='success'>✅ Firma demo creata! ID: " . $firmaId . "</p>";
    } else {
        $firmaId = $firmaDemo['id'];
        echo "<p class='success'>✅ Firma demo exista deja! ID: " . $firmaId . "</p>";
        echo "<p>Denumire: <strong>" . $firmaDemo['denumire'] . "</strong></p>";
    }
    echo "</div>";

    // Cream utilizatorii demo
    echo "<div class='box'>";
    echo "<h2>3. Creare Utilizatori Demo</h2>";

    // Hash-ul pentru toate parolele demo (admin123, demo123, user123)
    // Acest hash corespunde parolei: admin123 / demo123 / user123
    $hashParola = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    $utilizatori = [
        [
            'firma_id' => null,
            'rol_id' => 1,
            'email' => 'admin@devizo.ro',
            'parola' => $hashParola,
            'nume' => 'Super Administrator',
            'activ' => 1,
            'parola_text' => 'admin123'
        ],
        [
            'firma_id' => $firmaId,
            'rol_id' => 2,
            'email' => 'doru@zaninstal.ro',
            'parola' => $hashParola,
            'nume' => 'Batagui Doru',
            'activ' => 1,
            'parola_text' => 'demo123'
        ],
        [
            'firma_id' => $firmaId,
            'rol_id' => 3,
            'email' => 'user@zaninstal.ro',
            'parola' => $hashParola,
            'nume' => 'Utilizator Demo',
            'activ' => 1,
            'parola_text' => 'user123'
        ]
    ];

    echo "<table>";
    echo "<tr><th>Email</th><th>Nume</th><th>Rol</th><th>Parola</th><th>Status</th></tr>";

    foreach ($utilizatori as $user) {
        // Verificam daca utilizatorul exista deja
        $stmt = $db->prepare("SELECT id FROM utilizatori WHERE email = ?");
        $stmt->execute([$user['email']]);
        $existing = $stmt->fetch();

        if ($existing) {
            echo "<tr>";
            echo "<td><code>" . $user['email'] . "</code></td>";
            echo "<td>" . $user['nume'] . "</td>";
            echo "<td>ID: " . $user['rol_id'] . "</td>";
            echo "<td><code>" . $user['parola_text'] . "</code></td>";
            echo "<td class='warning'>⚠️ Exista deja (ID: " . $existing['id'] . ")</td>";
            echo "</tr>";
        } else {
            // Cream utilizatorul
            $stmt = $db->prepare("
                INSERT INTO utilizatori (firma_id, rol_id, email, parola, nume, activ)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $user['firma_id'],
                $user['rol_id'],
                $user['email'],
                $user['parola'],
                $user['nume'],
                $user['activ']
            ]);

            $userId = $db->lastInsertId();

            echo "<tr>";
            echo "<td><code>" . $user['email'] . "</code></td>";
            echo "<td>" . $user['nume'] . "</td>";
            echo "<td>ID: " . $user['rol_id'] . "</td>";
            echo "<td><code>" . $user['parola_text'] . "</code></td>";
            echo "<td class='success'>✅ Creat! (ID: " . $userId . ")</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    echo "</div>";

    // Cream date demo suplimentare
    echo "<div class='box'>";
    echo "<h2>4. Date Demo Suplimentare</h2>";

    // Unitati de masura
    $stmt = $db->query("SELECT COUNT(*) as total FROM unitati_masura WHERE firma_id IS NULL");
    $count = $stmt->fetch();

    if ($count['total'] == 0) {
        echo "<p>Cream unitati de masura globale...</p>";
        $db->exec("
            INSERT INTO unitati_masura (firma_id, simbol, denumire) VALUES
            (NULL, 'buc', 'Bucata'),
            (NULL, 'kg', 'Kilogram'),
            (NULL, 'm', 'Metru'),
            (NULL, 'mp', 'Metru patrat'),
            (NULL, 'mc', 'Metru cub'),
            (NULL, 'l', 'Litru'),
            (NULL, 'set', 'Set'),
            (NULL, 'ore', 'Ore'),
            (NULL, 'zile', 'Zile')
        ");
        echo "<p class='success'>✅ Unitati de masura create!</p>";
    } else {
        echo "<p class='success'>✅ Unitati de masura: " . $count['total'] . " (exista deja)</p>";
    }

    // Parteneri demo
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM parteneri WHERE firma_id = ?");
    $stmt->execute([$firmaId]);
    $count = $stmt->fetch();

    if ($count['total'] == 0) {
        echo "<p>Cream parteneri demo...</p>";
        $stmt = $db->prepare("
            INSERT INTO parteneri (firma_id, cui, denumire, nr_reg_com, adresa, telefon, email, observatii)
            VALUES
            (?, 'RO12345678', 'SC POPESCU CONSTRUCT SRL', 'J40/1234/2020',
             'Bucuresti, Sector 1, Str. Principala, Nr. 10', '0721234567', 'contact@popescu.ro', 'Client VIP'),
            (?, 'RO87654321', 'SC IONESCU DEVELOPMENT SRL', 'J23/4321/2019',
             'Ilfov, Voluntari, Str. Secundara, Nr. 5', '0731234567', 'office@ionescu.ro', 'Plata la 30 zile')
        ");
        $stmt->execute([$firmaId, $firmaId]);
        echo "<p class='success'>✅ Parteneri demo creati!</p>";
    } else {
        echo "<p class='success'>✅ Parteneri: " . $count['total'] . " (exista deja)</p>";
    }

    // Articole demo
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM articole WHERE firma_id = ?");
    $stmt->execute([$firmaId]);
    $count = $stmt->fetch();

    if ($count['total'] == 0) {
        echo "<p>Cream articole demo...</p>";
        $stmt = $db->prepare("
            INSERT INTO articole (firma_id, denumire, um_id, furnizor, pret_unitar, pret_manopera)
            VALUES
            (?, 'Tub PVC 20mm', 3, 'Dedeman', 2.50, 1.50),
            (?, 'Racord 20mm', 1, 'Dedeman', 1.20, 0.80),
            (?, 'Cot 90 grade 20mm', 1, 'Leroy Merlin', 0.80, 0.50),
            (?, 'Mufa 20mm', 1, 'Dedeman', 0.60, 0.40),
            (?, 'Teava cupru 18mm', 3, 'Ruvil Distribution', 15.50, 8.00)
        ");
        $stmt->execute([$firmaId, $firmaId, $firmaId, $firmaId, $firmaId]);
        echo "<p class='success'>✅ Articole demo create!</p>";
    } else {
        echo "<p class='success'>✅ Articole: " . $count['total'] . " (exista deja)</p>";
    }

    echo "</div>";

    // Succes final
    echo "<div class='box' style='background: #d4edda; border-left: 4px solid #2ecc71;'>";
    echo "<h2>✅ SUCCES!</h2>";
    echo "<p style='font-size: 18px;'><strong>Utilizatorii demo au fost creati/verificati cu succes!</strong></p>";
    echo "<p>Puteti acum sa va autentificati cu:</p>";
    echo "<table>";
    echo "<tr><th>Rol</th><th>Email</th><th>Parola</th></tr>";
    echo "<tr><td><strong>Super Admin</strong></td><td><code>admin@devizo.ro</code></td><td><code>admin123</code></td></tr>";
    echo "<tr><td><strong>Master Firma</strong></td><td><code>doru@zaninstal.ro</code></td><td><code>demo123</code></td></tr>";
    echo "<tr><td><strong>Utilizator</strong></td><td><code>user@zaninstal.ro</code></td><td><code>user123</code></td></tr>";
    echo "</table>";
    echo "<p><a href='login.php' style='background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 10px;'>⏩ Mergi la Login</a></p>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='box' style='background: #f8d7da; border-left: 4px solid #e74c3c;'>";
    echo "<h2 class='error'>❌ EROARE</h2>";
    echo "<p><strong>Mesaj:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Solutie:</strong> Verificati ca database.sql a fost importat corect in phpMyAdmin.</p>";
    echo "</div>";
}

echo "<div class='box' style='background: #fff3cd; border-left: 4px solid #f39c12;'>";
echo "<h2>⚠️ SECURITATE</h2>";
echo "<p><strong>IMPORTANT:</strong> Dupa utilizare, STERGETI acest fisier de pe server!</p>";
echo "<p>Fisier de sters: <code>/public_html/devizo/creare_utilizatori_demo.php</code></p>";
echo "</div>";

echo "<p style='text-align: center; color: #999; margin-top: 40px;'>DEVIZO v1.0.0</p>";
echo "</body></html>";
