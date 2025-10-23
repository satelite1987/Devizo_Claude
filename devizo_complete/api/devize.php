<?php
/**
 * DEVIZO - API Devize
 * 
 * Endpoints: GET, POST, PUT, DELETE
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Verificare autentificare
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
        case 'GET':
            // Obtinere deviz/devize
            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                $stmt = $db->prepare("
                    SELECT d.*, p.denumire as partener_denumire
                    FROM devize d
                    LEFT JOIN parteneri p ON d.partener_id = p.id
                    WHERE d.id = ? AND d.firma_id = ?
                ");
                $stmt->execute([$id, $firmaId]);
                $deviz = $stmt->fetch();
                
                if (!$deviz) {
                    jsonResponse(false, null, 'Deviz negasit.');
                }
                
                // Obtinere articole
                $stmt = $db->prepare("SELECT * FROM deviz_articole WHERE deviz_id = ?");
                $stmt->execute([$id]);
                $deviz['articole'] = $stmt->fetchAll();
                
                jsonResponse(true, $deviz);
            } else {
                // Lista devize
                $stmt = $db->prepare("
                    SELECT d.*, p.denumire as partener_denumire
                    FROM devize d
                    LEFT JOIN parteneri p ON d.partener_id = p.id
                    WHERE d.firma_id = ?
                    ORDER BY d.data_deviz DESC
                ");
                $stmt->execute([$firmaId]);
                $devize = $stmt->fetchAll();
                
                jsonResponse(true, $devize);
            }
            break;
            
        case 'POST':
            // Creare deviz nou
            $input = json_decode(file_get_contents('php://input'), true);
            
            $numarDeviz = clean($input['numar_deviz'] ?? '');
            $dataDeviz = clean($input['data_deviz'] ?? '');
            $partenerId = (int)($input['partener_id'] ?? 0);
            $observatii = clean($input['observatii'] ?? '');
            $procentManopera = (float)($input['procent_manopera'] ?? 0);
            $articole = $input['articole'] ?? [];
            
            // Validari
            if (empty($numarDeviz) || empty($dataDeviz) || $partenerId <= 0 || empty($articole)) {
                jsonResponse(false, null, 'Date invalide.');
            }
            
            $db->beginTransaction();
            
            // Calcul totaluri
            $totalMateriale = 0;
            foreach ($articole as $art) {
                $totalMateriale += (float)$art['cantitate'] * (float)$art['pret_unitar'];
            }
            
            $totalManopera = $totalMateriale * ($procentManopera / 100);
            $totalGeneral = $totalMateriale + $totalManopera;
            
            $linkHash = generateUniqueHash('deviz_');
            
            // Inserare deviz
            $stmt = $db->prepare("
                INSERT INTO devize (
                    firma_id, utilizator_id, partener_id, numar_deviz, data_deviz,
                    total_materiale, total_manopera, procent_manopera, total_general,
                    observatii, status, link_hash
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'In asteptare', ?)
            ");
            
            $stmt->execute([
                $firmaId,
                $_SESSION['user_id'],
                $partenerId,
                $numarDeviz,
                $dataDeviz,
                $totalMateriale,
                $totalManopera,
                $procentManopera,
                $totalGeneral,
                $observatii,
                $linkHash
            ]);
            
            $devizId = $db->lastInsertId();
            
            // Inserare articole
            $stmt = $db->prepare("
                INSERT INTO deviz_articole (
                    deviz_id, articol_id, denumire, um, cantitate, pret_unitar, total
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($articole as $art) {
                $cantitate = (float)$art['cantitate'];
                $pretUnitar = (float)$art['pret_unitar'];
                $total = $cantitate * $pretUnitar;
                
                $stmt->execute([
                    $devizId,
                    (int)$art['articol_id'],
                    $art['denumire'],
                    $art['um'],
                    $cantitate,
                    $pretUnitar,
                    $total
                ]);
            }
            
            $db->commit();
            
            logActivity($_SESSION['user_id'], 'create_deviz', "Deviz nou: $numarDeviz");
            
            jsonResponse(true, ['id' => $devizId], 'Deviz creat cu succes!');
            break;
            
        case 'PUT':
            // Actualizare deviz (doar status)
            $input = json_decode(file_get_contents('php://input'), true);
            
            $id = (int)($input['id'] ?? 0);
            $status = clean($input['status'] ?? '');
            
            if ($id <= 0 || empty($status)) {
                jsonResponse(false, null, 'Date invalide.');
            }
            
            // Verificare proprietate
            $stmt = $db->prepare("SELECT id FROM devize WHERE id = ? AND firma_id = ?");
            $stmt->execute([$id, $firmaId]);
            if (!$stmt->fetch()) {
                jsonResponse(false, null, 'Deviz negasit.');
            }
            
            $stmt = $db->prepare("UPDATE devize SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            
            logActivity($_SESSION['user_id'], 'update_deviz', "Status deviz #$id: $status");
            
            jsonResponse(true, null, 'Status actualizat cu succes!');
            break;
            
        case 'DELETE':
            // Stergere deviz
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['id'] ?? 0);
            
            if ($id <= 0) {
                jsonResponse(false, null, 'ID invalid.');
            }
            
            // Verificare proprietate
            $stmt = $db->prepare("SELECT id, numar_deviz FROM devize WHERE id = ? AND firma_id = ?");
            $stmt->execute([$id, $firmaId]);
            $deviz = $stmt->fetch();
            
            if (!$deviz) {
                jsonResponse(false, null, 'Deviz negasit.');
            }
            
            $db->beginTransaction();
            
            // Stergere articole deviz
            $stmt = $db->prepare("DELETE FROM deviz_articole WHERE deviz_id = ?");
            $stmt->execute([$id]);
            
            // Stergere deviz
            $stmt = $db->prepare("DELETE FROM devize WHERE id = ?");
            $stmt->execute([$id]);
            
            $db->commit();
            
            logActivity($_SESSION['user_id'], 'delete_deviz', "Deviz sters: " . $deviz['numar_deviz']);
            
            jsonResponse(true, null, 'Deviz sters cu succes!');
            break;
            
        default:
            jsonResponse(false, null, 'Metoda invalida.');
    }
    
} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    logError("Eroare API devize: " . $e->getMessage());
    jsonResponse(false, null, 'Eroare la procesarea cererii.');
}
