<?php
/**
 * DEVIZO - Admin API
 * API pentru gestionare firme (Super Admin only)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Verificare autentificare
if (!isLoggedIn()) {
    jsonResponse(false, null, 'Nu esti autentificat');
    exit;
}

// DOAR Super Admin
if (!isSuperAdmin()) {
    jsonResponse(false, null, 'Acces interzis');
    exit;
}

$db = getDB();
$action = isset($_GET['action']) ? clean($_GET['action']) : (isset($_POST['action']) ? clean($_POST['action']) : '');

try {
    switch ($action) {
        case 'get':
            // Get firma by ID
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

            if (!$id) {
                jsonResponse(false, null, 'ID firma invalid');
                exit;
            }

            $stmt = $db->prepare("SELECT * FROM firme WHERE id = ?");
            $stmt->execute([$id]);
            $firma = $stmt->fetch();

            if (!$firma) {
                jsonResponse(false, null, 'Firma nu a fost gasita');
                exit;
            }

            jsonResponse(true, $firma, 'Success');
            break;

        case 'create':
            // Create firma
            $denumire = isset($_POST['denumire']) ? clean($_POST['denumire']) : '';
            $cui = isset($_POST['cui']) ? clean($_POST['cui']) : '';
            $email = isset($_POST['email']) ? clean($_POST['email']) : '';
            $max_utilizatori = isset($_POST['max_utilizatori']) ? (int)$_POST['max_utilizatori'] : 10;
            $abonament_activ = isset($_POST['abonament_activ']) ? (int)$_POST['abonament_activ'] : 1;
            $data_expirare = isset($_POST['data_expirare_abonament']) ? clean($_POST['data_expirare_abonament']) : '';

            // Validare
            if (!$denumire || !$cui || !$email || !$data_expirare) {
                jsonResponse(false, null, 'Toate campurile sunt obligatorii');
                exit;
            }

            if (!validateEmail($email)) {
                jsonResponse(false, null, 'Email invalid');
                exit;
            }

            // Verificare CUI duplicat
            $stmt = $db->prepare("SELECT id FROM firme WHERE cui = ?");
            $stmt->execute([$cui]);
            if ($stmt->fetch()) {
                jsonResponse(false, null, 'Exista deja o firma cu acest CUI');
                exit;
            }

            // Insert
            $stmt = $db->prepare("
                INSERT INTO firme (
                    denumire, cui, email, max_utilizatori,
                    abonament_activ, data_expirare_abonament, creat_la
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $denumire, $cui, $email, $max_utilizatori,
                $abonament_activ, $data_expirare
            ]);

            logActivity('Adaugare firma: ' . $denumire);
            jsonResponse(true, ['id' => $db->lastInsertId()], 'Firma a fost adaugata cu succes');
            break;

        case 'update':
            // Update firma
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $denumire = isset($_POST['denumire']) ? clean($_POST['denumire']) : '';
            $cui = isset($_POST['cui']) ? clean($_POST['cui']) : '';
            $email = isset($_POST['email']) ? clean($_POST['email']) : '';
            $max_utilizatori = isset($_POST['max_utilizatori']) ? (int)$_POST['max_utilizatori'] : 10;
            $abonament_activ = isset($_POST['abonament_activ']) ? (int)$_POST['abonament_activ'] : 1;
            $data_expirare = isset($_POST['data_expirare_abonament']) ? clean($_POST['data_expirare_abonament']) : '';

            // Validare
            if (!$id || !$denumire || !$cui || !$email || !$data_expirare) {
                jsonResponse(false, null, 'Toate campurile sunt obligatorii');
                exit;
            }

            if (!validateEmail($email)) {
                jsonResponse(false, null, 'Email invalid');
                exit;
            }

            // Verificare firma exista
            $stmt = $db->prepare("SELECT denumire FROM firme WHERE id = ?");
            $stmt->execute([$id]);
            $firma = $stmt->fetch();

            if (!$firma) {
                jsonResponse(false, null, 'Firma nu a fost gasita');
                exit;
            }

            // Verificare CUI duplicat (except current)
            $stmt = $db->prepare("SELECT id FROM firme WHERE cui = ? AND id != ?");
            $stmt->execute([$cui, $id]);
            if ($stmt->fetch()) {
                jsonResponse(false, null, 'Exista deja o firma cu acest CUI');
                exit;
            }

            // Update
            $stmt = $db->prepare("
                UPDATE firme SET
                    denumire = ?,
                    cui = ?,
                    email = ?,
                    max_utilizatori = ?,
                    abonament_activ = ?,
                    data_expirare_abonament = ?,
                    actualizat_la = NOW()
                WHERE id = ?
            ");

            $stmt->execute([
                $denumire, $cui, $email, $max_utilizatori,
                $abonament_activ, $data_expirare, $id
            ]);

            logActivity('Actualizare firma: ' . $denumire);
            jsonResponse(true, null, 'Firma a fost actualizata cu succes');
            break;

        default:
            jsonResponse(false, null, 'Actiune invalida');
            break;
    }

} catch (PDOException $e) {
    error_log('Admin API Error: ' . $e->getMessage());
    jsonResponse(false, null, 'Eroare baza de date: ' . $e->getMessage());
} catch (Exception $e) {
    error_log('Admin API Error: ' . $e->getMessage());
    jsonResponse(false, null, 'Eroare: ' . $e->getMessage());
}
?>
