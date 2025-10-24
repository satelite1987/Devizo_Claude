<?php
/**
 * DEVIZO - API Unitati de Masura
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    jsonResponse(false, null, 'Nu sunteti autentificat.');
}

if (isSuperAdmin()) {
    jsonResponse(false, null, 'Acces interzis.');
}

$action = $_GET['action'] ?? '';
$db = getDB();
$firmaId = $_SESSION['firma_id'];

try {
    switch ($action) {
        case 'create':
            // Creare unitate de masura
            $input = json_decode(file_get_contents('php://input'), true);

            $simbol = clean($input['simbol'] ?? '');
            $denumire = clean($input['denumire'] ?? '');

            if (empty($simbol) || empty($denumire)) {
                jsonResponse(false, null, 'Simbol si denumire sunt obligatorii.');
            }

            // Verificam daca simbolul exista deja pentru aceasta firma
            $stmt = $db->prepare("SELECT id FROM unitati_masura WHERE simbol = ? AND (firma_id = ? OR firma_id IS NULL)");
            $stmt->execute([$simbol, $firmaId]);
            if ($stmt->fetch()) {
                jsonResponse(false, null, 'Simbolul este deja utilizat.');
            }

            $stmt = $db->prepare("
                INSERT INTO unitati_masura (firma_id, simbol, denumire)
                VALUES (?, ?, ?)
            ");

            $stmt->execute([$firmaId, $simbol, $denumire]);

            logActivity($_SESSION['user_id'], 'create_um', "Unitate masura noua: $simbol - $denumire");

            jsonResponse(true, ['id' => $db->lastInsertId()], 'Unitate de masura creata cu succes!');
            break;

        case 'update':
            // Actualizare unitate de masura
            $input = json_decode(file_get_contents('php://input'), true);

            $id = (int)($input['id'] ?? 0);
            $simbol = clean($input['simbol'] ?? '');
            $denumire = clean($input['denumire'] ?? '');

            if ($id <= 0 || empty($simbol) || empty($denumire)) {
                jsonResponse(false, null, 'Date invalide.');
            }

            // Verificare proprietate - nu putem edita UM globale (firma_id NULL)
            $stmt = $db->prepare("SELECT id FROM unitati_masura WHERE id = ? AND firma_id = ?");
            $stmt->execute([$id, $firmaId]);
            if (!$stmt->fetch()) {
                jsonResponse(false, null, 'Unitatea de masura nu poate fi editata sau nu exista.');
            }

            // Verificam daca simbolul nou este folosit
            $stmt = $db->prepare("
                SELECT id FROM unitati_masura
                WHERE simbol = ? AND id != ? AND (firma_id = ? OR firma_id IS NULL)
            ");
            $stmt->execute([$simbol, $id, $firmaId]);
            if ($stmt->fetch()) {
                jsonResponse(false, null, 'Simbolul este deja utilizat.');
            }

            $stmt = $db->prepare("
                UPDATE unitati_masura
                SET simbol = ?, denumire = ?
                WHERE id = ?
            ");

            $stmt->execute([$simbol, $denumire, $id]);

            logActivity($_SESSION['user_id'], 'update_um', "Unitate masura actualizata: $simbol (#$id)");

            jsonResponse(true, null, 'Unitate de masura actualizata cu succes!');
            break;

        case 'delete':
            // Stergere unitate de masura
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['id'] ?? 0);

            if ($id <= 0) {
                jsonResponse(false, null, 'ID invalid.');
            }

            // Verificare proprietate - nu putem sterge UM globale
            $stmt = $db->prepare("SELECT simbol FROM unitati_masura WHERE id = ? AND firma_id = ?");
            $stmt->execute([$id, $firmaId]);
            $um = $stmt->fetch();

            if (!$um) {
                jsonResponse(false, null, 'Unitatea de masura nu poate fi stearsa sau nu exista.');
            }

            // Verificam daca unitatea este folosita in articole
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM articole WHERE um_id = ? AND firma_id = ?");
            $stmt->execute([$id, $firmaId]);
            $count = $stmt->fetch()['total'];

            if ($count > 0) {
                jsonResponse(false, null, 'Unitatea de masura este folosita in ' . $count . ' articole si nu poate fi stearsa.');
            }

            $stmt = $db->prepare("DELETE FROM unitati_masura WHERE id = ?");
            $stmt->execute([$id]);

            logActivity($_SESSION['user_id'], 'delete_um', "Unitate masura stearsa: " . $um['simbol'] . " (#$id)");

            jsonResponse(true, null, 'Unitate de masura stearsa cu succes!');
            break;

        default:
            jsonResponse(false, null, 'Actiune invalida.');
    }

} catch (PDOException $e) {
    logError("Eroare API unitati masura: " . $e->getMessage());
    jsonResponse(false, null, 'Eroare la procesarea cererii.');
}
