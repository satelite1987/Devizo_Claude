<?php
/**
 * DEVIZO - Cereri Oferta API
 * CRUD operations for supplier quote requests
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, null, 'Nu esti autentificat');
    exit;
}

if (isSuperAdmin()) {
    jsonResponse(false, null, 'Super Admin nu are acces la cereri oferta');
    exit;
}

$db = getDB();
$firmaId = $_SESSION['firma_id'];
$action = isset($_GET['action']) ? clean($_GET['action']) : (isset($_POST['action']) ? clean($_POST['action']) : '');

try {
    switch ($action) {
        case 'create':
            // Create cerere oferta
            $input = json_decode(file_get_contents('php://input'), true);

            $titlu = isset($input['titlu']) ? clean($input['titlu']) : '';
            $descriere = isset($input['descriere']) ? clean($input['descriere']) : '';
            $data_limita = isset($input['data_limita']) ? clean($input['data_limita']) : null;
            $articole = isset($input['articole']) ? $input['articole'] : [];

            // Validare
            if (!$titlu) {
                jsonResponse(false, null, 'Titlul este obligatoriu');
                exit;
            }

            if (empty($articole)) {
                jsonResponse(false, null, 'Trebuie sa adaugati cel putin un articol');
                exit;
            }

            // Generare link hash unic
            $linkHash = bin2hex(random_bytes(16));

            // Insert cerere
            $stmt = $db->prepare("
                INSERT INTO cereri_oferta (
                    firma_id, utilizator_id, titlu, descriere,
                    data_limita, link_hash, status, creat_la
                ) VALUES (?, ?, ?, ?, ?, ?, 'Activa', NOW())
            ");

            $stmt->execute([
                $firmaId,
                $_SESSION['user_id'],
                $titlu,
                $descriere,
                $data_limita ?: null,
                $linkHash
            ]);

            $cerereId = $db->lastInsertId();

            // Insert articole
            $stmtArticol = $db->prepare("
                INSERT INTO cereri_oferta_randuri (
                    cerere_id, denumire, um, cantitate
                ) VALUES (?, ?, ?, ?)
            ");

            foreach ($articole as $articol) {
                $stmtArticol->execute([
                    $cerereId,
                    clean($articol['denumire']),
                    clean($articol['um']),
                    (float)$articol['cantitate']
                ]);
            }

            logActivity('Adaugare cerere oferta: ' . $titlu);
            jsonResponse(true, ['id' => $cerereId, 'link_hash' => $linkHash], 'Cererea a fost creata cu succes');
            break;

        case 'close':
            // Close cerere oferta
            $input = json_decode(file_get_contents('php://input'), true);
            $id = isset($input['id']) ? (int)$input['id'] : 0;

            if (!$id) {
                jsonResponse(false, null, 'ID invalid');
                exit;
            }

            // Verificare ownership
            $stmt = $db->prepare("SELECT titlu FROM cereri_oferta WHERE id = ? AND firma_id = ?");
            $stmt->execute([$id, $firmaId]);
            $cerere = $stmt->fetch();

            if (!$cerere) {
                jsonResponse(false, null, 'Cerere nu a fost gasita');
                exit;
            }

            // Update status
            $stmt = $db->prepare("UPDATE cereri_oferta SET status = 'Inchisa' WHERE id = ?");
            $stmt->execute([$id]);

            logActivity('Inchidere cerere oferta: ' . $cerere['titlu']);
            jsonResponse(true, null, 'Cererea a fost inchisa cu succes');
            break;

        case 'delete':
            // Delete cerere oferta
            $input = json_decode(file_get_contents('php://input'), true);
            $id = isset($input['id']) ? (int)$input['id'] : 0;

            if (!$id) {
                jsonResponse(false, null, 'ID invalid');
                exit;
            }

            // Verificare ownership
            $stmt = $db->prepare("SELECT titlu FROM cereri_oferta WHERE id = ? AND firma_id = ?");
            $stmt->execute([$id, $firmaId]);
            $cerere = $stmt->fetch();

            if (!$cerere) {
                jsonResponse(false, null, 'Cerere nu a fost gasita');
                exit;
            }

            // Stergere articole
            $stmt = $db->prepare("DELETE FROM cereri_oferta_randuri WHERE cerere_id = ?");
            $stmt->execute([$id]);

            // Stergere oferte furnizori (daca exista)
            $stmt = $db->prepare("DELETE FROM oferte_furnizori WHERE cerere_id = ?");
            $stmt->execute([$id]);

            // Stergere cerere
            $stmt = $db->prepare("DELETE FROM cereri_oferta WHERE id = ?");
            $stmt->execute([$id]);

            logActivity('Stergere cerere oferta: ' . $cerere['titlu']);
            jsonResponse(true, null, 'Cererea a fost stearsa cu succes');
            break;

        default:
            jsonResponse(false, null, 'Actiune invalida');
            break;
    }

} catch (PDOException $e) {
    error_log('Cereri Oferta API Error: ' . $e->getMessage());
    jsonResponse(false, null, 'Eroare baza de date');
} catch (Exception $e) {
    error_log('Cereri Oferta API Error: ' . $e->getMessage());
    jsonResponse(false, null, 'Eroare: ' . $e->getMessage());
}
?>
