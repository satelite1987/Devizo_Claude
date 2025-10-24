<?php
/**
 * DEVIZO - API Utilizatori
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    jsonResponse(false, null, 'Nu sunteti autentificat.');
}

// Doar Master Firma si Super Admin pot gestiona utilizatori
if (!isMasterFirma() && !isSuperAdmin()) {
    jsonResponse(false, null, 'Nu aveti permisiuni pentru aceasta actiune.');
}

$action = $_GET['action'] ?? '';
$db = getDB();
$firmaId = $_SESSION['firma_id'];

try {
    switch ($action) {
        case 'create':
            // Creare utilizator
            $input = json_decode(file_get_contents('php://input'), true);

            $nume = clean($input['nume'] ?? '');
            $email = clean($input['email'] ?? '');
            $telefon = clean($input['telefon'] ?? '');
            $parola = $input['parola'] ?? '';
            $rolId = (int)($input['rol_id'] ?? 3);
            $activ = (int)($input['activ'] ?? 1);

            // Validare
            if (empty($nume) || empty($email) || empty($parola)) {
                jsonResponse(false, null, 'Nume, email si parola sunt obligatorii.');
            }

            if (!validateEmail($email)) {
                jsonResponse(false, null, 'Email invalid.');
            }

            $passwordValidation = validatePassword($parola);
            if (!$passwordValidation['valid']) {
                jsonResponse(false, null, $passwordValidation['error']);
            }

            // Verificare daca email-ul exista deja
            if (emailExists($email)) {
                jsonResponse(false, null, 'Email-ul este deja utilizat.');
            }

            // Verificare limita utilizatori
            if (!checkUserLimit($firmaId)) {
                jsonResponse(false, null, 'Ati atins limita maxima de utilizatori pentru firma dvs.');
            }

            // Master Firma poate crea doar utilizatori normali (rol_id = 3)
            if (isMasterFirma() && $rolId != 3) {
                $rolId = 3;
            }

            // Hash parola
            $hashedPassword = password_hash($parola, PASSWORD_DEFAULT);

            $stmt = $db->prepare("
                INSERT INTO utilizatori (firma_id, rol_id, email, parola, nume, telefon, activ)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([$firmaId, $rolId, $email, $hashedPassword, $nume, $telefon, $activ]);

            logActivity($_SESSION['user_id'], 'create_utilizator', "Utilizator nou: $nume ($email)");

            jsonResponse(true, ['id' => $db->lastInsertId()], 'Utilizator creat cu succes!');
            break;

        case 'update':
            // Actualizare utilizator
            $input = json_decode(file_get_contents('php://input'), true);

            $id = (int)($input['id'] ?? 0);
            $nume = clean($input['nume'] ?? '');
            $email = clean($input['email'] ?? '');
            $telefon = clean($input['telefon'] ?? '');
            $parola = $input['parola'] ?? '';
            $rolId = (int)($input['rol_id'] ?? 3);
            $activ = (int)($input['activ'] ?? 1);

            if ($id <= 0 || empty($nume) || empty($email)) {
                jsonResponse(false, null, 'Date invalide.');
            }

            if (!validateEmail($email)) {
                jsonResponse(false, null, 'Email invalid.');
            }

            // Verificare proprietate - utilizatorul trebuie sa apartina aceleasi firme
            $stmt = $db->prepare("SELECT id, email FROM utilizatori WHERE id = ? AND firma_id = ?");
            $stmt->execute([$id, $firmaId]);
            $utilizator = $stmt->fetch();

            if (!$utilizator) {
                jsonResponse(false, null, 'Utilizator negasit.');
            }

            // Nu poate edita pe el insusi (pentru a preveni blocarea)
            if ($id == $_SESSION['user_id']) {
                jsonResponse(false, null, 'Nu puteti edita propriul cont din aceasta pagina.');
            }

            // Verificare daca email-ul exista (exclus utilizatorul curent)
            if (emailExists($email, $id)) {
                jsonResponse(false, null, 'Email-ul este deja utilizat de alt utilizator.');
            }

            // Master Firma poate edita doar utilizatori normali
            if (isMasterFirma() && $rolId != 3) {
                $rolId = 3;
            }

            // Daca s-a furnizat o noua parola, o validam si actualizam
            if (!empty($parola)) {
                $passwordValidation = validatePassword($parola);
                if (!$passwordValidation['valid']) {
                    jsonResponse(false, null, $passwordValidation['error']);
                }

                $hashedPassword = password_hash($parola, PASSWORD_DEFAULT);

                $stmt = $db->prepare("
                    UPDATE utilizatori
                    SET nume = ?, email = ?, telefon = ?, parola = ?, rol_id = ?, activ = ?
                    WHERE id = ?
                ");
                $stmt->execute([$nume, $email, $telefon, $hashedPassword, $rolId, $activ, $id]);
            } else {
                // Fara schimbare parola
                $stmt = $db->prepare("
                    UPDATE utilizatori
                    SET nume = ?, email = ?, telefon = ?, rol_id = ?, activ = ?
                    WHERE id = ?
                ");
                $stmt->execute([$nume, $email, $telefon, $rolId, $activ, $id]);
            }

            logActivity($_SESSION['user_id'], 'update_utilizator', "Utilizator actualizat: $nume (#$id)");

            jsonResponse(true, null, 'Utilizator actualizat cu succes!');
            break;

        case 'delete':
            // Stergere utilizator
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['id'] ?? 0);

            if ($id <= 0) {
                jsonResponse(false, null, 'ID invalid.');
            }

            // Nu poate sterge pe el insusi
            if ($id == $_SESSION['user_id']) {
                jsonResponse(false, null, 'Nu puteti sterge propriul cont.');
            }

            // Verificare proprietate
            $stmt = $db->prepare("SELECT nume FROM utilizatori WHERE id = ? AND firma_id = ?");
            $stmt->execute([$id, $firmaId]);
            $utilizator = $stmt->fetch();

            if (!$utilizator) {
                jsonResponse(false, null, 'Utilizator negasit.');
            }

            $stmt = $db->prepare("DELETE FROM utilizatori WHERE id = ?");
            $stmt->execute([$id]);

            logActivity($_SESSION['user_id'], 'delete_utilizator', "Utilizator sters: " . $utilizator['nume'] . " (#$id)");

            jsonResponse(true, null, 'Utilizator sters cu succes!');
            break;

        default:
            jsonResponse(false, null, 'Actiune invalida.');
    }

} catch (PDOException $e) {
    logError("Eroare API utilizatori: " . $e->getMessage());
    jsonResponse(false, null, 'Eroare la procesarea cererii.');
}
