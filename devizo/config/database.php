<?php
/**
 * DEVIZO - Configurare Conexiune Baza de Date
 *
 * Acest fisier contine configurarea conexiunii la baza de date MySQL.
 *
 * INSTRUCTIUNI:
 * 1. Dupa ce urcati fisierele pe server, editati acest fisier
 * 2. Completati datele de conexiune corecte pentru serverul dvs.
 * 3. Asigurati-va ca utilizatorul MySQL are permisiuni pe baza de date
 *
 * SECURITATE:
 * - NU partajati acest fisier cu nimeni
 * - NU il urcati pe repository-uri publice (ex: GitHub)
 * - Utilizati parole puternice pentru MySQL
 */

// Oprire afisare erori in productie (setati la 0 dupa testare)
// In timpul dezvoltarii/testarii: 1
// In productie: 0
define('DEBUG_MODE', 1);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configurare conexiune baza de date
// CONFIGURARE PENTRU WWW.DEVIZO.RO
define('DB_HOST', 'localhost');        // Adresa server MySQL
define('DB_NAME', 'devizo_db');        // Numele bazei de date
define('DB_USER', 'devizo_user');      // Utilizator MySQL
define('DB_PASS', 'Satelite1987!');    // Parola MySQL
define('DB_CHARSET', 'utf8mb4');       // Charset (NU modificati)

// Configurari aplicatie
define('SITE_URL', 'https://www.devizo.ro');  // URL-ul complet al aplicatiei
define('SITE_NAME', 'DEVIZO');                  // Numele aplicatiei
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');  // Calea catre director upload-uri
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);     // Marime maxima fisier upload (5 MB)

// Setari sesiune
define('SESSION_LIFETIME', 3600 * 24);  // Durata sesiune: 24 ore (in secunde)
define('SESSION_NAME', 'DEVIZO_SESSION');

// Setari securitate
define('PASSWORD_MIN_LENGTH', 6);       // Lungime minima parola
define('MAX_LOGIN_ATTEMPTS', 5);        // Numar maxim incercari login

// Configurare timezone
date_default_timezone_set('Europe/Bucharest');

/**
 * Clasa pentru gestionarea conexiunii la baza de date
 *
 * Foloseste PDO (PHP Data Objects) pentru securitate si flexibilitate
 * Implementeaza pattern Singleton pentru o singura instanta de conexiune
 */
class Database {
    private static $instance = null;
    private $conn;

    /**
     * Constructor privat - previne instantierea directa
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];

            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);

        } catch(PDOException $e) {
            if (DEBUG_MODE) {
                die("Eroare conexiune baza de date: " . $e->getMessage());
            } else {
                die("Eroare conexiune baza de date. Va rugam contactati administratorul.");
            }
        }
    }

    /**
     * Obtine instanta unica a conexiunii (Singleton)
     *
     * @return Database Instanta unica
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtine conexiunea PDO
     *
     * @return PDO Obiectul de conexiune
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * Previne clonarea obiectului
     */
    private function __clone() {}

    /**
     * Previne deserializarea obiectului
     */
    public function __wakeup() {}
}

/**
 * Functie helper pentru obtinerea conexiunii la baza de date
 *
 * @return PDO Obiectul de conexiune
 */
function getDB() {
    return Database::getInstance()->getConnection();
}

/**
 * Functie pentru logging erori
 *
 * @param string $message Mesajul de eroare
 * @param string $type Tipul erorii (error, warning, info)
 */
function logError($message, $type = 'error') {
    $logFile = __DIR__ . '/../logs/app.log';
    $logDir = dirname($logFile);

    // Cream directorul de log-uri daca nu exista
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$type] $message" . PHP_EOL;

    file_put_contents($logFile, $logMessage, FILE_APPEND);
}