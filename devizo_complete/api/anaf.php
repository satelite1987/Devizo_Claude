<?php
/**
 * DEVIZO - API ANAF Integration
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    jsonResponse(false, null, 'Nu sunteti autentificat.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, null, 'Metoda invalida.');
}

$cui = isset($_GET['cui']) ? clean($_GET['cui']) : '';

if (empty($cui)) {
    jsonResponse(false, null, 'CUI lipseste.');
}

// Curatare CUI
$cui = preg_replace('/[^0-9]/', '', $cui);

if (empty($cui)) {
    jsonResponse(false, null, 'CUI invalid.');
}

try {
    $data = getInfoFromANAF($cui);
    
    if ($data) {
        jsonResponse(true, $data, 'Informatii obtinute cu succes!');
    } else {
        jsonResponse(false, null, 'Nu s-au gasit informatii pentru acest CUI.');
    }
    
} catch (Exception $e) {
    logError("Eroare API ANAF: " . $e->getMessage());
    jsonResponse(false, null, 'Eroare la comunicarea cu ANAF.');
}
