<?php
/**
 * DEVIZO - API Parteneri
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
            // Creare partener
            $input = json_decode(file_get_contents('php://input'), true);
            
            $denumire = clean($input['denumire'] ?? '');
            $cui = clean($input['cui'] ?? '');
            $nrRegCom = clean($input['nr_reg_com'] ?? '');
            $adresa = clean($input['adresa'] ?? '');
            $telefon = clean($input['telefon'] ?? '');
            $email = clean($input['email'] ?? '');
            $iban = clean($input['iban'] ?? '');
            $banca = clean($input['banca'] ?? '');
            
            if (empty($denumire)) {
                jsonResponse(false, null, 'Denumirea este obligatorie.');
            }
            
            $stmt = $db->prepare("
                INSERT INTO parteneri (
                    firma_id, denumire, cui, nr_reg_com, adresa, telefon, email, iban, banca
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $firmaId, $denumire, $cui, $nrRegCom, $adresa, $telefon, $email, $iban, $banca
            ]);
            
            logActivity($_SESSION['user_id'], 'create_partener', "Partener nou: $denumire");
            
            jsonResponse(true, ['id' => $db->lastInsertId()], 'Partener creat cu succes!');
            break;
            
        case 'PUT':
            // Actualizare partener
            $input = json_decode(file_get_contents('php://input'), true);
            
            $id = (int)($input['id'] ?? 0);
            $denumire = clean($input['denumire'] ?? '');
            $cui = clean($input['cui'] ?? '');
            $nrRegCom = clean($input['nr_reg_com'] ?? '');
            $adresa = clean($input['adresa'] ?? '');
            $telefon = clean($input['telefon'] ?? '');
            $email = clean($input['email'] ?? '');
            $iban = clean($input['iban'] ?? '');
            $banca = clean($input['banca'] ?? '');
            
            if ($id <= 0 || empty($denumire)) {
                jsonResponse(false, null, 'Date invalide.');
            }
            
            // Verificare proprietate
            $stmt = $db->prepare("SELECT id FROM parteneri WHERE id = ? AND firma_id = ?");
            $stmt->execute([$id, $firmaId]);
            if (!$stmt->fetch()) {
                jsonResponse(false, null, 'Partener negasit.');
            }
            
            $stmt = $db->prepare("
                UPDATE parteneri 
                SET denumire = ?, cui = ?, nr_reg_com = ?, adresa = ?, 
                    telefon = ?, email = ?, iban = ?, banca = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $denumire, $cui, $nrRegCom, $adresa, $telefon, $email, $iban, $banca, $id
            ]);
            
            logActivity($_SESSION['user_id'], 'update_partener', "Partener actualizat: $denumire");
            
            jsonResponse(true, null, 'Partener actualizat cu succes!');
            break;
            
        case 'DELETE':
            // Stergere partener
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['id'] ?? 0);
            
            if ($id <= 0) {
                jsonResponse(false, null, 'ID invalid.');
            }
            
            // Verificare proprietate
            $stmt = $db->prepare("SELECT denumire FROM parteneri WHERE id = ? AND firma_id = ?");
            $stmt->execute([$id, $firmaId]);
            $partener = $stmt->fetch();
            
            if (!$partener) {
                jsonResponse(false, null, 'Partener negasit.');
            }
            
            $stmt = $db->prepare("DELETE FROM parteneri WHERE id = ?");
            $stmt->execute([$id]);
            
            logActivity($_SESSION['user_id'], 'delete_partener', "Partener sters: " . $partener['denumire']);
            
            jsonResponse(true, null, 'Partener sters cu succes!');
            break;
            
        default:
            jsonResponse(false, null, 'Metoda invalida.');
    }
    
} catch (PDOException $e) {
    logError("Eroare API parteneri: " . $e->getMessage());
    jsonResponse(false, null, 'Eroare la procesarea cererii.');
}
