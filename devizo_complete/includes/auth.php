<?php
/**
 * DEVIZO - Sistem de Autentificare
 *
 * Acest fisier gestioneaza autentificarea utilizatorilor
 *
 * FUNCTII PRINCIPALE:
 * - login() - Autentificare utilizator
 * - logout() - Deconectare utilizator
 * - register() - Inregistrare utilizator nou
 * - updateLastLogin() - Actualizeaza ultima autentificare
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

// Porneste sesiunea daca nu este deja pornita
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

/**
 * Autentifica un utilizator
 *
 * @param string $email Email utilizator
 * @param string $password Parola
 * @return array ['success' => bool, 'message' => string, 'user' => array]
 */
function login($email, $password) {
    $result = ['success' => false, 'message' => '', 'user' => null];

    // Validare input
    if (empty($email) || empty($password)) {
        $result['message'] = 'Email si parola sunt obligatorii.';
        return $result;
    }

    if (!validateEmail($email)) {
        $result['message'] = 'Email invalid.';
        return $result;
    }

    try {
        $db = getDB();

        // Cautam utilizatorul
        $stmt = $db->prepare("
            SELECT u.*, f.denumire as firma_denumire, f.abonament_activ,
                   f.data_expirare_abonament, f.logo_path, f.culoare_primara,
                   r.nume as rol_nume
            FROM utilizatori u
            LEFT JOIN firme f ON u.firma_id = f.id
            LEFT JOIN roluri r ON u.rol_id = r.id
            WHERE u.email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $result['message'] = 'Email sau parola incorecte.';
            logError("Tentativa de login pentru email inexistent: $email");
            return $result;
        }

        // Verificam daca utilizatorul este activ
        if ($user['activ'] != 1) {
            $result['message'] = 'Contul este dezactivat. Contactati administratorul.';
            logError("Tentativa de login cu cont dezactivat: $email");
            return $result;
        }

        // Verificam parola
        if (!password_verify($password, $user['parola'])) {
            $result['message'] = 'Email sau parola incorecte.';
            logError("Tentativa de login cu parola incorecta pentru: $email");
            return $result;
        }

        // Verificam abonamentul (doar pentru utilizatori non-admin)
        if ($user['rol_id'] != 1) { // Nu verificam pentru Super Admin
            if (!$user['abonament_activ']) {
                $result['message'] = 'Abonamentul firmei a expirat. Contactati administratorul.';
                logError("Tentativa de login cu abonament expirat pentru firma: " . $user['firma_id']);
                return $result;
            }

            // Verificam data expirare
            if (strtotime($user['data_expirare_abonament']) < time()) {
                $result['message'] = 'Abonamentul firmei a expirat la data de ' . formatDateRO($user['data_expirare_abonament']) . '.';
                logError("Tentativa de login cu abonament expirat (data) pentru firma: " . $user['firma_id']);

                // Dezactivam abonamentul automat
                $stmtUpdate = $db->prepare("UPDATE firme SET abonament_activ = 0 WHERE id = ?");
                $stmtUpdate->execute([$user['firma_id']]);

                return $result;
            }
        }

        // Autentificare reusita - salvam datele in sesiune
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['nume'] = $user['nume'];
        $_SESSION['rol_id'] = $user['rol_id'];
        $_SESSION['rol_nume'] = $user['rol_nume'];
        $_SESSION['firma_id'] = $user['firma_id'];
        $_SESSION['firma_denumire'] = $user['firma_denumire'];
        $_SESSION['abonament_activ'] = $user['abonament_activ'];
        $_SESSION['data_expirare'] = $user['data_expirare_abonament'];
        $_SESSION['logo_path'] = $user['logo_path'];
        $_SESSION['culoare_primara'] = $user['culoare_primara'];

        // Actualizam ultima autentificare
        updateLastLogin($user['id']);

        // Logam activitatea
        logActivity($user['id'], 'login', 'Autentificare reusita');

        $result['success'] = true;
        $result['message'] = 'Autentificare reusita!';
        $result['user'] = $user;

        return $result;

    } catch (PDOException $e) {
        logError("Eroare login: " . $e->getMessage());
        $result['message'] = 'Eroare la autentificare. Va rugam incercati din nou.';
        return $result;
    }
}

/**
 * Deconecteaza utilizatorul curent
 *
 * @return bool True daca deconectarea a reusit
 */
function logout() {
    if (isLoggedIn()) {
        $userId = $_SESSION['user_id'];

        // Logam activitatea
        logActivity($userId, 'logout', 'Deconectare');

        // Stergem toate variabilele de sesiune
        $_SESSION = [];

        // Stergem cookie-ul de sesiune
        if (isset($_COOKIE[SESSION_NAME])) {
            setcookie(SESSION_NAME, '', time() - 3600, '/');
        }

        // Distrugem sesiunea
        session_destroy();

        return true;
    }

    return false;
}

/**
 * Inregistreaza un utilizator nou
 *
 * @param array $data Date utilizator
 * @return array ['success' => bool, 'message' => string, 'user_id' => int]
 */
function registerUser($data) {
    $result = ['success' => false, 'message' => '', 'user_id' => null];

    // Validare input
    if (empty($data['email']) || empty($data['password']) || empty($data['nume'])) {
        $result['message'] = 'Email, parola si nume sunt obligatorii.';
        return $result;
    }

    if (!validateEmail($data['email'])) {
        $result['message'] = 'Email invalid.';
        return $result;
    }

    $passwordValidation = validatePassword($data['password']);
    if (!$passwordValidation['valid']) {
        $result['message'] = $passwordValidation['error'];
        return $result;
    }

    // Verificam daca email-ul exista deja
    if (emailExists($data['email'])) {
        $result['message'] = 'Email-ul este deja utilizat.';
        return $result;
    }

    // Verificam limita de utilizatori pentru firma (daca nu e Super Admin)
    if (isset($data['firma_id']) && !empty($data['firma_id'])) {
        if (!checkUserLimit($data['firma_id'])) {
            $result['message'] = 'Limita de utilizatori pentru aceasta firma a fost atinsa.';
            return $result;
        }
    }

    try {
        $db = getDB();

        // Hash-uim parola
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Inserare utilizator
        $stmt = $db->prepare("
            INSERT INTO utilizatori (firma_id, rol_id, email, parola, nume, telefon, activ)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['firma_id'] ?? null,
            $data['rol_id'] ?? 3, // Default: Utilizator Firma
            $data['email'],
            $hashedPassword,
            $data['nume'],
            $data['telefon'] ?? null,
            $data['activ'] ?? 1
        ]);

        $userId = $db->lastInsertId();

        // Logam activitatea
        logActivity($userId, 'register', 'Inregistrare cont nou');

        $result['success'] = true;
        $result['message'] = 'Cont creat cu succes!';
        $result['user_id'] = $userId;

        return $result;

    } catch (PDOException $e) {
        logError("Eroare registerUser: " . $e->getMessage());
        $result['message'] = 'Eroare la crearea contului. Va rugam incercati din nou.';
        return $result;
    }
}

/**
 * Actualizeaza timpul ultimei autentificari
 *
 * @param int $userId ID utilizator
 * @return bool True daca actualizarea a reusit
 */
function updateLastLogin($userId) {
    try {
        $db = getDB();
        $stmt = $db->prepare("UPDATE utilizatori SET ultima_autentificare = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
        return true;
    } catch (PDOException $e) {
        logError("Eroare updateLastLogin: " . $e->getMessage());
        return false;
    }
}

/**
 * Verifica daca firma mai poate adauga utilizatori
 *
 * @param int $firmaId ID firma
 * @return bool True daca se mai pot adauga utilizatori
 */
function checkUserLimit($firmaId) {
    try {
        $db = getDB();

        // Obtinem limita de utilizatori
        $stmt = $db->prepare("SELECT max_utilizatori FROM firme WHERE id = ?");
        $stmt->execute([$firmaId]);
        $firma = $stmt->fetch();

        if (!$firma) {
            return false;
        }

        // Numaram utilizatorii activi
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM utilizatori WHERE firma_id = ? AND activ = 1");
        $stmt->execute([$firmaId]);
        $count = $stmt->fetch();

        return $count['total'] < $firma['max_utilizatori'];

    } catch (PDOException $e) {
        logError("Eroare checkUserLimit: " . $e->getMessage());
        return false;
    }
}

/**
 * Schimba parola unui utilizator
 *
 * @param int $userId ID utilizator
 * @param string $oldPassword Parola veche
 * @param string $newPassword Parola noua
 * @return array ['success' => bool, 'message' => string]
 */
function changePassword($userId, $oldPassword, $newPassword) {
    $result = ['success' => false, 'message' => ''];

    // Validare parola noua
    $passwordValidation = validatePassword($newPassword);
    if (!$passwordValidation['valid']) {
        $result['message'] = $passwordValidation['error'];
        return $result;
    }

    try {
        $db = getDB();

        // Verificam parola veche
        $stmt = $db->prepare("SELECT parola FROM utilizatori WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            $result['message'] = 'Utilizator inexistent.';
            return $result;
        }

        if (!password_verify($oldPassword, $user['parola'])) {
            $result['message'] = 'Parola veche este incorecta.';
            return $result;
        }

        // Actualizam parola
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE utilizatori SET parola = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $userId]);

        // Logam activitatea
        logActivity($userId, 'change_password', 'Schimbare parola');

        $result['success'] = true;
        $result['message'] = 'Parola a fost schimbata cu succes!';

        return $result;

    } catch (PDOException $e) {
        logError("Eroare changePassword: " . $e->getMessage());
        $result['message'] = 'Eroare la schimbarea parolei. Va rugam incercati din nou.';
        return $result;
    }
}

/**
 * Reseteaza parola unui utilizator (pentru admin)
 *
 * @param int $userId ID utilizator
 * @param string $newPassword Parola noua
 * @return array ['success' => bool, 'message' => string]
 */
function resetPassword($userId, $newPassword) {
    $result = ['success' => false, 'message' => ''];

    // Validare parola noua
    $passwordValidation = validatePassword($newPassword);
    if (!$passwordValidation['valid']) {
        $result['message'] = $passwordValidation['error'];
        return $result;
    }

    try {
        $db = getDB();

        // Actualizam parola
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE utilizatori SET parola = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $userId]);

        // Logam activitatea
        logActivity($_SESSION['user_id'], 'reset_password', "Resetare parola pentru utilizatorul #$userId");

        $result['success'] = true;
        $result['message'] = 'Parola a fost resetata cu succes!';

        return $result;

    } catch (PDOException $e) {
        logError("Eroare resetPassword: " . $e->getMessage());
        $result['message'] = 'Eroare la resetarea parolei. Va rugam incercati din nou.';
        return $result;
    }
}

/**
 * Logheaza o activitate in baza de date
 *
 * @param int $userId ID utilizator
 * @param string $action Actiunea
 * @param string $details Detalii (JSON sau text)
 * @return bool True daca logarea a reusit
 */
function logActivity($userId, $action, $details = '') {
    try {
        $db = getDB();

        $stmt = $db->prepare("
            INSERT INTO log_activitate (utilizator_id, actiune, detalii, ip_adresa)
            VALUES (?, ?, ?, ?)
        ");

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        $stmt->execute([$userId, $action, $details, $ipAddress]);

        return true;

    } catch (PDOException $e) {
        logError("Eroare logActivity: " . $e->getMessage());
        return false;
    }
}