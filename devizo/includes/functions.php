<?php
/**
 * DEVIZO - Functii Generale
 *
 * Acest fisier contine functii utilitare folosite in toata aplicatia
 *
 * CONTINUT:
 * - Functii de validare
 * - Functii de securitate
 * - Functii de formatare
 * - Functii pentru lucrul cu date
 */

require_once __DIR__ . '/../config/database.php';

/**
 * ============================================
 * FUNCTII DE SECURITATE
 * ============================================
 */

/**
 * Curata input-ul de caractere periculoase
 *
 * @param string $data Datele de curatat
 * @return string Date curatate
 */
function clean($data) {
    if (is_array($data)) {
        return array_map('clean', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Genereaza un token CSRF pentru securitate
 *
 * @return string Token generat
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica validitatea unui token CSRF
 *
 * @param string $token Token de verificat
 * @return bool True daca valid, false altfel
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Verifica daca utilizatorul este autentificat
 *
 * @return bool True daca autentificat, false altfel
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Verifica daca utilizatorul are un anumit rol
 *
 * @param int $rolId ID-ul rolului necesar
 * @return bool True daca are rolul, false altfel
 */
function hasRole($rolId) {
    return isLoggedIn() && isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == $rolId;
}

/**
 * Verifica daca utilizatorul este Super Admin
 *
 * @return bool True daca este Super Admin, false altfel
 */
function isSuperAdmin() {
    return hasRole(1);
}

/**
 * Verifica daca utilizatorul este Master Firma
 *
 * @return bool True daca este Master, false altfel
 */
function isMasterFirma() {
    return hasRole(2);
}

/**
 * Redirectioneaza utilizatorul catre o pagina
 *
 * @param string $url URL-ul destinatie
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Redirectioneaza daca utilizatorul NU este autentificat
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect(SITE_URL . '/login.php');
    }
}

/**
 * Redirectioneaza daca utilizatorul NU are rolul necesar
 *
 * @param int $rolId ID-ul rolului necesar
 */
function requireRole($rolId) {
    requireLogin();
    if (!hasRole($rolId)) {
        redirect(SITE_URL . '/index.php?error=access_denied');
    }
}

/**
 * Verifica daca abonamentul firmei este activ
 *
 * @return bool True daca activ, false altfel
 */
function isAbonamentActiv() {
    if (isSuperAdmin()) {
        return true; // Super admin-ul nu are restrictii
    }

    if (!isset($_SESSION['abonament_activ']) || !isset($_SESSION['data_expirare'])) {
        return false;
    }

    // Verificam daca abonamentul este marcat ca activ SI daca nu a expirat
    $dataExpirare = strtotime($_SESSION['data_expirare']);
    $acum = time();

    return $_SESSION['abonament_activ'] == 1 && $dataExpirare > $acum;
}

/**
 * ============================================
 * FUNCTII DE VALIDARE
 * ============================================
 */

/**
 * Valideaza o adresa de email
 *
 * @param string $email Email de validat
 * @return bool True daca valid, false altfel
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valideaza o parola
 *
 * @param string $password Parola de validat
 * @return array ['valid' => bool, 'error' => string]
 */
function validatePassword($password) {
    $result = ['valid' => true, 'error' => ''];

    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $result['valid'] = false;
        $result['error'] = 'Parola trebuie sa aiba minim ' . PASSWORD_MIN_LENGTH . ' caractere.';
    }

    return $result;
}

/**
 * Valideaza un CUI romanesc
 *
 * @param string $cui CUI de validat
 * @return bool True daca valid, false altfel
 */
function validateCUI($cui) {
    // Eliminam spatiile si caracterele speciale
    $cui = preg_replace('/[^0-9]/', '', $cui);

    // CUI trebuie sa aiba intre 2 si 10 cifre
    if (strlen($cui) < 2 || strlen($cui) > 10) {
        return false;
    }

    return true;
}

/**
 * ============================================
 * FUNCTII DE FORMATARE
 * ============================================
 */

/**
 * Formateaza o data in format romanesc
 *
 * @param string $date Data in format SQL (YYYY-MM-DD)
 * @return string Data formatata (DD.MM.YYYY)
 */
function formatDateRO($date) {
    if (empty($date) || $date == '0000-00-00') {
        return '-';
    }
    return date('d.m.Y', strtotime($date));
}

/**
 * Formateaza o data-timp in format romanesc
 *
 * @param string $datetime Data-timp in format SQL
 * @return string Data-timp formatata (DD.MM.YYYY HH:MM)
 */
function formatDateTimeRO($datetime) {
    if (empty($datetime)) {
        return '-';
    }
    return date('d.m.Y H:i', strtotime($datetime));
}

/**
 * Formateaza un numar ca pret in EUR
 *
 * @param float $amount Suma
 * @param bool $currency Afiseaza simbolul monedei
 * @return string Pret formatat
 */
function formatPrice($amount, $currency = true) {
    $formatted = number_format($amount, 2, '.', ',');
    return $currency ? $formatted . ' EUR' : $formatted;
}

/**
 * Formateaza un numar telefon
 *
 * @param string $phone Numar telefon
 * @return string Telefon formatat
 */
function formatPhone($phone) {
    // Eliminam spatiile si caracterele speciale
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    return $phone;
}

/**
 * Elimina diacriticele dintr-un text
 *
 * @param string $text Textul original
 * @return string Text fara diacritice
 */
function removeDiacritics($text) {
    $diacritics = [
        'ă' => 'a', 'â' => 'a', 'î' => 'i', 'ș' => 's', 'ț' => 't',
        'Ă' => 'A', 'Â' => 'A', 'Î' => 'I', 'Ș' => 'S', 'Ț' => 'T'
    ];
    return str_replace(array_keys($diacritics), array_values($diacritics), $text);
}

/**
 * ============================================
 * FUNCTII PENTRU LUCRUL CU DATE
 * ============================================
 */

/**
 * Obtine datele utilizatorului curent
 *
 * @return array|null Date utilizator sau null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT u.*, f.denumire as firma_denumire, f.cui as firma_cui,
                   f.logo_path, f.culoare_primara, f.procent_manopera,
                   r.nume as rol_nume
            FROM utilizatori u
            LEFT JOIN firme f ON u.firma_id = f.id
            LEFT JOIN roluri r ON u.rol_id = r.id
            WHERE u.id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        logError("Eroare getCurrentUser: " . $e->getMessage());
        return null;
    }
}

/**
 * Obtine datele firmei utilizatorului curent
 *
 * @return array|null Date firma sau null
 */
function getCurrentFirma() {
    if (!isLoggedIn() || isSuperAdmin()) {
        return null;
    }

    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM firme WHERE id = ?");
        $stmt->execute([$_SESSION['firma_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        logError("Eroare getCurrentFirma: " . $e->getMessage());
        return null;
    }
}

/**
 * Verifica daca un email exista deja in baza de date
 *
 * @param string $email Email de verificat
 * @param int|null $excludeUserId ID utilizator de exclus din cautare (pentru editare)
 * @return bool True daca exista, false altfel
 */
function emailExists($email, $excludeUserId = null) {
    try {
        $db = getDB();
        if ($excludeUserId) {
            $stmt = $db->prepare("SELECT id FROM utilizatori WHERE email = ? AND id != ?");
            $stmt->execute([$email, $excludeUserId]);
        } else {
            $stmt = $db->prepare("SELECT id FROM utilizatori WHERE email = ?");
            $stmt->execute([$email]);
        }
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        logError("Eroare emailExists: " . $e->getMessage());
        return false;
    }
}

/**
 * Verifica daca un CUI exista deja in baza de date
 *
 * @param string $cui CUI de verificat
 * @param int|null $excludeFirmaId ID firma de exclus din cautare (pentru editare)
 * @return bool True daca exista, false altfel
 */
function cuiExists($cui, $excludeFirmaId = null) {
    try {
        $db = getDB();
        if ($excludeFirmaId) {
            $stmt = $db->prepare("SELECT id FROM firme WHERE cui = ? AND id != ?");
            $stmt->execute([$cui, $excludeFirmaId]);
        } else {
            $stmt = $db->prepare("SELECT id FROM firme WHERE cui = ?");
            $stmt->execute([$cui]);
        }
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        logError("Eroare cuiExists: " . $e->getMessage());
        return false;
    }
}

/**
 * Genereaza un hash unic pentru link-uri de partajare
 *
 * @param string $prefix Prefix pentru identificare
 * @return string Hash generat
 */
function generateUniqueHash($prefix = '') {
    return hash('sha256', $prefix . uniqid() . random_bytes(32) . time());
}

/**
 * Uploadeaza un fisier
 *
 * @param array $file Fisierul din $_FILES
 * @param string $destination Director destinatie (relativ la UPLOAD_PATH)
 * @param array $allowedTypes Tipuri MIME permise
 * @return array ['success' => bool, 'path' => string, 'error' => string]
 */
function uploadFile($file, $destination, $allowedTypes = []) {
    $result = ['success' => false, 'path' => '', 'error' => ''];

    // Verificam daca fisierul a fost uploadat
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        $result['error'] = 'Niciun fisier selectat.';
        return $result;
    }

    // Verificam erorile de upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['error'] = 'Eroare la upload: ' . $file['error'];
        return $result;
    }

    // Verificam dimensiunea fisierului
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        $result['error'] = 'Fisierul este prea mare. Marime maxima: ' . (UPLOAD_MAX_SIZE / 1024 / 1024) . ' MB';
        return $result;
    }

    // Verificam tipul fisierului
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!empty($allowedTypes) && !in_array($mimeType, $allowedTypes)) {
        $result['error'] = 'Tip fisier invalid.';
        return $result;
    }

    // Generam nume unic pentru fisier
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;

    // Cream directorul daca nu exista
    $uploadDir = UPLOAD_PATH . $destination;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Mutam fisierul
    $targetPath = $uploadDir . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $result['success'] = true;
        $result['path'] = $destination . '/' . $filename;
    } else {
        $result['error'] = 'Eroare la salvarea fisierului.';
    }

    return $result;
}

/**
 * Sterge un fisier uploadat
 *
 * @param string $path Calea relativa a fisierului
 * @return bool True daca stergerea a reusit
 */
function deleteUploadedFile($path) {
    if (empty($path)) {
        return false;
    }

    $fullPath = UPLOAD_PATH . $path;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }

    return false;
}

/**
 * ============================================
 * FUNCTII PENTRU INTEGRARE ANAF
 * ============================================
 */

/**
 * Obtine informatii despre o firma de la ANAF pe baza CUI
 *
 * @param string $cui CUI firma
 * @return array|null Date firma sau null
 */
function getInfoFromANAF($cui) {
    // Curatam CUI-ul
    $cui = preg_replace('/[^0-9]/', '', $cui);

    if (empty($cui)) {
        return null;
    }

    try {
        // API-ul ANAF pentru informatii firma
        $url = "https://webservicesp.anaf.ro/PlatitorTvaRest/api/v8/ws/tva";

        $data = [
            [
                "cui" => $cui,
                "data" => date('Y-m-d')
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200 && $response) {
            $result = json_decode($response, true);

            if (isset($result['found']) && count($result['found']) > 0) {
                $firma = $result['found'][0];

                return [
                    'denumire' => isset($firma['denumire']) ? $firma['denumire'] : '',
                    'adresa' => isset($firma['adresa']) ? $firma['adresa'] : '',
                    'telefon' => isset($firma['telefon']) ? $firma['telefon'] : '',
                    'cui' => $cui,
                    'nr_reg_com' => '' // ANAF nu furnizeaza nr. reg. com.
                ];
            }
        }

        return null;

    } catch (Exception $e) {
        logError("Eroare ANAF API: " . $e->getMessage());
        return null;
    }
}

/**
 * ============================================
 * FUNCTII PENTRU MESAJE SI NOTIFICARI
 * ============================================
 */

/**
 * Seteaza un mesaj flash in sesiune
 *
 * @param string $message Mesajul
 * @param string $type Tipul: success, error, warning, info
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'text' => $message,
        'type' => $type
    ];
}

/**
 * Obtine si sterge mesajul flash din sesiune
 *
 * @return array|null Mesajul sau null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * ============================================
 * FUNCTII PENTRU JSON RESPONSE (API)
 * ============================================
 */

/**
 * Trimite un raspuns JSON
 *
 * @param bool $success Status success
 * @param mixed $data Date
 * @param string $message Mesaj
 */
function jsonResponse($success, $data = null, $message = '') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit();
}
