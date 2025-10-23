<?php
/**
 * DEVIZO - API Articole
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

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();
$firmaId = $_SESSION['firma_id'];

try {
    switch ($method) {
        case 'POST':
            // Creare articol
            $input = json_decode(file_get_contents('php://input'), true);
            
            $cod = clean($input['cod'] ?? '');
            $denumire = clean($input['denumire'] ?? '');
            $um = (int)($input['um'] ?? 0);
            $pretUnitar = (float)($input['pret_unitar'] ?? 0);
            $descriere = clean($input['descriere'] ?? '');
            
            if (empty($cod) || empty($denumire) || $um <= 0) {
                jsonResponse(false, null, 'Date invalide.');
            }
            
            $stmt = $db->prepare("
                INSERT INTO articole (firma_id, cod, denumire, um, pret_unitar, descriere)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([$firmaId, $cod, $denumire, $um, $pretUnitar, $descriere]);
            
            logActivity($_SESSION['user_id'], 'create_articol', "Articol nou: $denumire");
            
            jsonResponse(true, ['id' => $db->lastInsertId()], 'Articol creat cu succes!');
            break;
            
        case 'PUT':
            // Actualizare articol
            $input = json_decode(file_get_contents('php://input'), true);
            
            $id = (int)($input['id'] ?? 0);
            $cod = clean($input['cod'] ?? '');
            $denumire = clean($input['denumire'] ?? '');
            $um = (int)($input['um'] ?? 0);
            $pretUnitar = (float)($input['pret_unitar'] ?? 0);
            $descriere = clean($input['descriere'] ?? '');
            
            if ($id <= 0 || empty($cod) || empty($denumire) || $um <= 0) {
                jsonResponse(false, null, 'Date invalide.');
            }
            
            // Verificare proprietate
            $stmt = $db->prepare("SELECT id FROM articole WHERE id = ? AND firma_id = ?");
            $stmt->execute([$id, $firmaId]);
            if (!$stmt->fetch()) {
                jsonResponse(false, null, 'Articol negasit.');
            }
            
            $stmt = $db->prepare("
                UPDATE articole 
                SET cod = ?, denumire = ?, um = ?, pret_unitar = ?, descriere = ?
                WHERE id = ?
            ");
            
            $stmt->execute([$cod, $denumire, $um, $pretUnitar, $descriere, $id]);
            
            logActivity($_SESSION['user_id'], 'update_articol', "Articol actualizat: $denumire");
            
            jsonResponse(true, null, 'Articol actualizat cu succes!');
            break;
            
        case 'DELETE':
            // Stergere articol
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['id'] ?? 0);
            
            if ($id <= 0) {
                jsonResponse(false, null, 'ID invalid.');
            }
            
            // Verificare proprietate
            $stmt = $db->prepare("SELECT denumire FROM articole WHERE id = ? AND firma_id = ?");
            $stmt->execute([$id, $firmaId]);
            $articol = $stmt->fetch();
            
            if (!$articol) {
                jsonResponse(false, null, 'Articol negasit.');
            }
            
            $stmt = $db->prepare("DELETE FROM articole WHERE id = ?");
            $stmt->execute([$id]);
            
            logActivity($_SESSION['user_id'], 'delete_articol', "Articol sters: " . $articol['denumire']);
            
            jsonResponse(true, null, 'Articol sters cu succes!');
            break;
            
        default:
            jsonResponse(false, null, 'Metoda invalida.');
    }
    
} catch (PDOException $e) {
    logError("Eroare API articole: " . $e->getMessage());
    jsonResponse(false, null, 'Eroare la procesarea cererii.');
}
