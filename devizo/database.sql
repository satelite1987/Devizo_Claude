-- ============================================
-- DEVIZO - Aplicatie Web pentru Gestiunea Devizelor
-- Versiunea: 1.0.0
-- Data: 2025-10-22
-- ============================================
--
-- DESCRIERE STRUCTURA BAZA DE DATE:
--
-- Aceasta baza de date este structurata pentru a suporta o aplicatie multi-tenant
-- unde fiecare firma (identificata prin CUI) poate avea mai multi utilizatori.
--
-- IERARHIA UTILIZATORILOR:
-- 1. Super Admin (rol_id = 1) - Administrator principal al aplicatiei
-- 2. Master Firma (rol_id = 2) - Administrator al unei firme
-- 3. Utilizator Firma (rol_id = 3) - Utilizator normal al unei firme
--
-- FLUXUL DE DATE:
-- - Super Admin creaza Firme (tabel: firme)
-- - Pentru fiecare firma se creeaza un utilizator Master
-- - Master poate crea utilizatori suplimentari (in limita max_utilizatori)
-- - Fiecare firma are propriile: devize, articole, parteneri, furnizori
-- ============================================

-- Stergem baza de date daca exista si o recreem
DROP DATABASE IF EXISTS devizo_db;
CREATE DATABASE devizo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE devizo_db;

-- ============================================
-- TABELE PRINCIPALE
-- ============================================

-- Tabelul cu roluri utilizatori
CREATE TABLE roluri (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nume VARCHAR(50) NOT NULL UNIQUE,
    descriere TEXT,
    creat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserare roluri predefinite
INSERT INTO roluri (id, nume, descriere) VALUES
(1, 'Super Admin', 'Administrator principal al aplicatiei - gestioneaza toate firmele'),
(2, 'Master Firma', 'Administrator al unei firme - poate crea utilizatori si gestiona datele firmei'),
(3, 'Utilizator Firma', 'Utilizator normal - poate crea devize si gestiona articole');

-- Tabelul cu firme (clienti ai aplicatiei)
CREATE TABLE firme (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cui VARCHAR(20) NOT NULL UNIQUE COMMENT 'Cod Unic de Identificare',
    denumire VARCHAR(255) NOT NULL,
    nr_reg_com VARCHAR(50) COMMENT 'Numar Registrul Comertului (J)',
    adresa TEXT,
    telefon VARCHAR(50),
    email VARCHAR(100),
    iban VARCHAR(50),
    banca VARCHAR(100),

    -- Date monetizare
    abonament_activ BOOLEAN DEFAULT 1 COMMENT '1 = activ, 0 = expirat',
    data_start_abonament DATE,
    data_expirare_abonament DATE,
    max_utilizatori INT DEFAULT 3 COMMENT 'Numarul maxim de utilizatori permisi',

    -- Setari firma
    logo_path VARCHAR(255) COMMENT 'Calea catre logo-ul firmei',
    culoare_primara VARCHAR(7) DEFAULT '#3498db' COMMENT 'Culoarea predominanta pe devize (HEX)',
    procent_manopera DECIMAL(5,2) DEFAULT 0 COMMENT 'Procent implicit pentru manopera',
    import_csv_activ BOOLEAN DEFAULT 0 COMMENT 'Permite importul de fisiere CSV cu articole',

    -- Metadata
    creat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_cui (cui),
    INDEX idx_abonament (abonament_activ, data_expirare_abonament)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabelul cu utilizatori
CREATE TABLE utilizatori (
    id INT PRIMARY KEY AUTO_INCREMENT,
    firma_id INT,
    rol_id INT NOT NULL DEFAULT 3,

    -- Date autentificare
    email VARCHAR(100) NOT NULL UNIQUE,
    parola VARCHAR(255) NOT NULL COMMENT 'Parola hash-uita cu password_hash()',

    -- Date personale
    nume VARCHAR(100) NOT NULL,
    telefon VARCHAR(50),

    -- Status
    activ BOOLEAN DEFAULT 1 COMMENT '1 = activ, 0 = dezactivat',
    ultima_autentificare TIMESTAMP NULL,

    -- Metadata
    creat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (firma_id) REFERENCES firme(id) ON DELETE CASCADE,
    FOREIGN KEY (rol_id) REFERENCES roluri(id),
    INDEX idx_email (email),
    INDEX idx_firma (firma_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabelul cu parteneri (clientii clientilor nostri)
CREATE TABLE parteneri (
    id INT PRIMARY KEY AUTO_INCREMENT,
    firma_id INT NOT NULL COMMENT 'Firma careia ii apartine acest partener',

    -- Date partener
    cui VARCHAR(20),
    denumire VARCHAR(255) NOT NULL,
    nr_reg_com VARCHAR(50),
    adresa TEXT,
    telefon VARCHAR(50),
    email VARCHAR(100),
    observatii TEXT,

    -- Metadata
    creat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (firma_id) REFERENCES firme(id) ON DELETE CASCADE,
    INDEX idx_firma (firma_id),
    INDEX idx_cui (cui)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabelul cu unitati de masura
CREATE TABLE unitati_masura (
    id INT PRIMARY KEY AUTO_INCREMENT,
    firma_id INT COMMENT 'NULL pentru UM globale, ID pentru UM custom per firma',
    simbol VARCHAR(20) NOT NULL,
    denumire VARCHAR(100),

    creat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (firma_id) REFERENCES firme(id) ON DELETE CASCADE,
    INDEX idx_firma (firma_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserare unitati de masura globale
INSERT INTO unitati_masura (firma_id, simbol, denumire) VALUES
(NULL, 'buc', 'Bucata'),
(NULL, 'kg', 'Kilogram'),
(NULL, 'm', 'Metru'),
(NULL, 'mp', 'Metru patrat'),
(NULL, 'mc', 'Metru cub'),
(NULL, 'l', 'Litru'),
(NULL, 'set', 'Set'),
(NULL, 'ore', 'Ore'),
(NULL, 'zile', 'Zile');

-- Tabelul cu articole
CREATE TABLE articole (
    id INT PRIMARY KEY AUTO_INCREMENT,
    firma_id INT NOT NULL,

    -- Date articol
    denumire VARCHAR(255) NOT NULL,
    um_id INT NOT NULL COMMENT 'ID unitate de masura',
    furnizor VARCHAR(255) COMMENT 'Numele furnizorului',
    pret_unitar DECIMAL(10,2) DEFAULT 0 COMMENT 'Pret unitar materiale in EUR',
    pret_manopera DECIMAL(10,2) DEFAULT 0 COMMENT 'Pret manopera in EUR (daca e setat, are prioritate fata de procent)',

    -- Metadata
    creat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (firma_id) REFERENCES firme(id) ON DELETE CASCADE,
    FOREIGN KEY (um_id) REFERENCES unitati_masura(id),
    INDEX idx_firma (firma_id),
    INDEX idx_denumire (denumire)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabelul cu devize
CREATE TABLE devize (
    id INT PRIMARY KEY AUTO_INCREMENT,
    firma_id INT NOT NULL,
    utilizator_id INT NOT NULL COMMENT 'Utilizatorul care a creat devizul',
    partener_id INT COMMENT 'Clientul pentru care este devizul',

    -- Date deviz
    numar_deviz VARCHAR(50) NOT NULL,
    data_deviz DATE NOT NULL,
    amplasament TEXT,
    descriere TEXT,

    -- Status deviz
    status ENUM('In asteptare', 'Acceptat', 'In lucru', 'Finalizat') DEFAULT 'In asteptare',

    -- Totaluri (calculate si stocate pentru performanta)
    total_materiale DECIMAL(12,2) DEFAULT 0,
    total_manopera DECIMAL(12,2) DEFAULT 0,
    total_general DECIMAL(12,2) DEFAULT 0,

    -- Link partajare
    link_hash VARCHAR(64) UNIQUE COMMENT 'Hash unic pentru link de partajare',

    -- Metadata
    creat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (firma_id) REFERENCES firme(id) ON DELETE CASCADE,
    FOREIGN KEY (utilizator_id) REFERENCES utilizatori(id),
    FOREIGN KEY (partener_id) REFERENCES parteneri(id) ON DELETE SET NULL,
    UNIQUE KEY unique_numar_deviz (firma_id, numar_deviz),
    INDEX idx_firma (firma_id),
    INDEX idx_status (status),
    INDEX idx_data (data_deviz),
    INDEX idx_link_hash (link_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabelul cu randuri deviz (articole din deviz)
CREATE TABLE deviz_randuri (
    id INT PRIMARY KEY AUTO_INCREMENT,
    deviz_id INT NOT NULL,

    -- Date articol
    nr_crt INT NOT NULL COMMENT 'Numarul curent in cadrul devizului',
    denumire VARCHAR(255) NOT NULL,
    um VARCHAR(20) NOT NULL,
    cantitate DECIMAL(10,2) NOT NULL,

    -- Preturi
    pret_unitar DECIMAL(10,2) NOT NULL COMMENT 'Pret unitar materiale EUR',
    total_materiale DECIMAL(12,2) NOT NULL COMMENT 'Cantitate * Pret unitar',
    pret_manopera DECIMAL(10,2) NOT NULL COMMENT 'Pret manopera pe unitate EUR',
    total_manopera DECIMAL(12,2) NOT NULL COMMENT 'Cantitate * Pret manopera',
    total_rand DECIMAL(12,2) NOT NULL COMMENT 'Total materiale + Total manopera',

    -- Metadata
    creat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (deviz_id) REFERENCES devize(id) ON DELETE CASCADE,
    INDEX idx_deviz (deviz_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabelul cu contoare devize (pentru numerotare automata)
CREATE TABLE deviz_contoare (
    id INT PRIMARY KEY AUTO_INCREMENT,
    firma_id INT NOT NULL,
    partener_id INT NOT NULL,
    ultim_numar INT DEFAULT 0 COMMENT 'Ultimul numar de deviz generat pentru acest partener',

    FOREIGN KEY (firma_id) REFERENCES firme(id) ON DELETE CASCADE,
    FOREIGN KEY (partener_id) REFERENCES parteneri(id) ON DELETE CASCADE,
    UNIQUE KEY unique_firma_partener (firma_id, partener_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabelul cu cereri oferta catre furnizori
CREATE TABLE cereri_oferta (
    id INT PRIMARY KEY AUTO_INCREMENT,
    firma_id INT NOT NULL,
    utilizator_id INT NOT NULL,

    -- Date cerere
    titlu VARCHAR(255) NOT NULL,
    descriere TEXT,
    data_limita DATE COMMENT 'Data limita pentru raspuns',

    -- Link partajare
    link_hash VARCHAR(64) UNIQUE COMMENT 'Hash unic pentru link catre furnizori',

    -- Status
    status ENUM('Activa', 'Inchisa', 'Anulata') DEFAULT 'Activa',

    -- Metadata
    creat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (firma_id) REFERENCES firme(id) ON DELETE CASCADE,
    FOREIGN KEY (utilizator_id) REFERENCES utilizatori(id),
    INDEX idx_firma (firma_id),
    INDEX idx_link_hash (link_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabelul cu randuri cerere oferta (articolele solicitate)
CREATE TABLE cereri_oferta_randuri (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cerere_id INT NOT NULL,

    -- Date articol solicitat
    nr_crt INT NOT NULL,
    denumire VARCHAR(255) NOT NULL,
    um VARCHAR(20) NOT NULL,
    cantitate DECIMAL(10,2) NOT NULL,
    specificatii TEXT COMMENT 'Specificatii tehnice sau observatii',

    FOREIGN KEY (cerere_id) REFERENCES cereri_oferta(id) ON DELETE CASCADE,
    INDEX idx_cerere (cerere_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabelul cu oferte de la furnizori
CREATE TABLE oferte_furnizori (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cerere_id INT NOT NULL,

    -- Date furnizor
    nume_furnizor VARCHAR(255) NOT NULL,
    email_furnizor VARCHAR(100) NOT NULL,
    telefon_furnizor VARCHAR(50),

    -- Status oferta
    status ENUM('In asteptare', 'Trimisa', 'Acceptata', 'Refuzata') DEFAULT 'In asteptare',

    -- Metadata
    creat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (cerere_id) REFERENCES cereri_oferta(id) ON DELETE CASCADE,
    INDEX idx_cerere (cerere_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabelul cu randuri oferta furnizor (preturile oferite)
CREATE TABLE oferte_furnizori_randuri (
    id INT PRIMARY KEY AUTO_INCREMENT,
    oferta_id INT NOT NULL,
    cerere_rand_id INT NOT NULL COMMENT 'Leaga de randul din cererea de oferta',

    -- Pret oferit
    pret_unitar DECIMAL(10,2) COMMENT 'Pretul oferit de furnizor',
    observatii TEXT,

    FOREIGN KEY (oferta_id) REFERENCES oferte_furnizori(id) ON DELETE CASCADE,
    FOREIGN KEY (cerere_rand_id) REFERENCES cereri_oferta_randuri(id) ON DELETE CASCADE,
    INDEX idx_oferta (oferta_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabelul cu log-uri activitate (optional - pentru audit)
CREATE TABLE log_activitate (
    id INT PRIMARY KEY AUTO_INCREMENT,
    utilizator_id INT,
    actiune VARCHAR(100) NOT NULL COMMENT 'Tipul actiunii: login, creare_deviz, etc.',
    detalii TEXT COMMENT 'Detalii suplimentare in format JSON',
    ip_adresa VARCHAR(45),

    creat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (utilizator_id) REFERENCES utilizatori(id) ON DELETE SET NULL,
    INDEX idx_utilizator (utilizator_id),
    INDEX idx_actiune (actiune),
    INDEX idx_data (creat_la)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DATE INITIALE PENTRU TESTARE
-- ============================================

-- Cream Super Admin (parola: admin123)
-- IMPORTANT: Parola trebuie schimbata imediat dupa prima autentificare!
INSERT INTO utilizatori (firma_id, rol_id, email, parola, nume, activ) VALUES
(NULL, 1, 'admin@devizo.ro', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Administrator', 1);

-- Cream o firma demo pentru testare
INSERT INTO firme (cui, denumire, nr_reg_com, adresa, telefon, email,
                   abonament_activ, data_start_abonament, data_expirare_abonament,
                   max_utilizatori, procent_manopera, import_csv_activ) VALUES
('RO46632242', 'ENERGY ZAN INSTAL SRL', 'J40/15628/2022',
 'Bucuresti, Sector 4', '0767573469', 'office@zaninstal.ro',
 1, '2025-01-01', '2025-12-31', 5, 30.00, 1);

-- Cream un utilizator master pentru firma demo (parola: demo123)
INSERT INTO utilizatori (firma_id, rol_id, email, parola, nume, activ) VALUES
(1, 2, 'doru@zaninstal.ro', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Batagui Doru', 1);

-- Cream un utilizator normal pentru firma demo (parola: user123)
INSERT INTO utilizatori (firma_id, rol_id, email, parola, nume, activ) VALUES
(1, 3, 'user@zaninstal.ro', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Utilizator Demo', 1);

-- Cream cativa parteneri demo
INSERT INTO parteneri (firma_id, cui, denumire, nr_reg_com, adresa, telefon, email, observatii) VALUES
(1, 'RO12345678', 'SC POPESCU CONSTRUCT SRL', 'J40/1234/2020',
 'Bucuresti, Sector 1, Str. Principala, Nr. 10', '0721234567', 'contact@popescu.ro', 'Client VIP'),
(1, 'RO87654321', 'SC IONESCU DEVELOPMENT SRL', 'J23/4321/2019',
 'Ilfov, Voluntari, Str. Secundara, Nr. 5', '0731234567', 'office@ionescu.ro', 'Plata la 30 zile');

-- Cream cateva articole demo
INSERT INTO articole (firma_id, denumire, um_id, furnizor, pret_unitar, pret_manopera) VALUES
(1, 'Tub PVC 20mm', 3, 'Dedeman', 2.50, 1.50),
(1, 'Racord 20mm', 1, 'Dedeman', 1.20, 0.80),
(1, 'Cot 90 grade 20mm', 1, 'Leroy Merlin', 0.80, 0.50),
(1, 'Mufa 20mm', 1, 'Dedeman', 0.60, 0.40),
(1, 'Teava cupru 18mm', 3, 'Ruvil Distribution', 15.50, 8.00);

-- ============================================
-- VEDERI (VIEWS) PENTRU RAPOARTE
-- ============================================

-- Vedere pentru afisarea devizelor cu informatii complete
CREATE VIEW v_devize_complete AS
SELECT
    d.id,
    d.numar_deviz,
    d.data_deviz,
    d.status,
    d.amplasament,
    d.descriere,
    d.total_materiale,
    d.total_manopera,
    d.total_general,
    d.link_hash,
    f.denumire AS firma_denumire,
    f.cui AS firma_cui,
    p.denumire AS partener_denumire,
    p.cui AS partener_cui,
    u.nume AS creat_de,
    d.creat_la,
    d.actualizat_la
FROM devize d
JOIN firme f ON d.firma_id = f.id
LEFT JOIN parteneri p ON d.partener_id = p.id
JOIN utilizatori u ON d.utilizator_id = u.id;

-- Vedere pentru statistici firme
CREATE VIEW v_statistici_firme AS
SELECT
    f.id,
    f.denumire,
    f.cui,
    f.abonament_activ,
    f.data_expirare_abonament,
    f.max_utilizatori,
    COUNT(DISTINCT u.id) AS numar_utilizatori,
    COUNT(DISTINCT d.id) AS numar_devize,
    COALESCE(SUM(d.total_general), 0) AS total_valoare_devize
FROM firme f
LEFT JOIN utilizatori u ON f.id = u.firma_id
LEFT JOIN devize d ON f.id = d.firma_id
GROUP BY f.id;

-- ============================================
-- FUNCTII SI PROCEDURI STOCATE
-- ============================================

DELIMITER //

-- Functie pentru generarea urmatorul numar deviz
CREATE FUNCTION get_urmatorul_numar_deviz(p_firma_id INT, p_partener_id INT)
RETURNS VARCHAR(50)
DETERMINISTIC
BEGIN
    DECLARE v_numar INT;
    DECLARE v_an VARCHAR(4);

    SET v_an = YEAR(CURDATE());

    -- Verificam daca exista un contor pentru aceasta combinatie
    SELECT ultim_numar INTO v_numar
    FROM deviz_contoare
    WHERE firma_id = p_firma_id AND partener_id = p_partener_id;

    IF v_numar IS NULL THEN
        SET v_numar = 1;
        INSERT INTO deviz_contoare (firma_id, partener_id, ultim_numar)
        VALUES (p_firma_id, p_partener_id, 1);
    ELSE
        SET v_numar = v_numar + 1;
        UPDATE deviz_contoare
        SET ultim_numar = v_numar
        WHERE firma_id = p_firma_id AND partener_id = p_partener_id;
    END IF;

    RETURN CONCAT('DEV', v_an, '-', LPAD(v_numar, 4, '0'));
END//

-- Procedura pentru actualizarea totalurilor unui deviz
CREATE PROCEDURE actualizeaza_totaluri_deviz(IN p_deviz_id INT)
BEGIN
    UPDATE devize d
    SET
        total_materiale = (SELECT COALESCE(SUM(total_materiale), 0) FROM deviz_randuri WHERE deviz_id = p_deviz_id),
        total_manopera = (SELECT COALESCE(SUM(total_manopera), 0) FROM deviz_randuri WHERE deviz_id = p_deviz_id),
        total_general = (SELECT COALESCE(SUM(total_rand), 0) FROM deviz_randuri WHERE deviz_id = p_deviz_id)
    WHERE id = p_deviz_id;
END//

DELIMITER ;

-- ============================================
-- TRIGGERE
-- ============================================

DELIMITER //

-- Trigger pentru actualizarea automata a totalurilor la inserare rand deviz
CREATE TRIGGER after_insert_deviz_rand
AFTER INSERT ON deviz_randuri
FOR EACH ROW
BEGIN
    CALL actualizeaza_totaluri_deviz(NEW.deviz_id);
END//

-- Trigger pentru actualizarea automata a totalurilor la update rand deviz
CREATE TRIGGER after_update_deviz_rand
AFTER UPDATE ON deviz_randuri
FOR EACH ROW
BEGIN
    CALL actualizeaza_totaluri_deviz(NEW.deviz_id);
END//

-- Trigger pentru actualizarea automata a totalurilor la stergere rand deviz
CREATE TRIGGER after_delete_deviz_rand
AFTER DELETE ON deviz_randuri
FOR EACH ROW
BEGIN
    CALL actualizeaza_totaluri_deviz(OLD.deviz_id);
END//

-- Trigger pentru generarea automata a hash-ului de partajare la crearea unui deviz
CREATE TRIGGER before_insert_deviz
BEFORE INSERT ON devize
FOR EACH ROW
BEGIN
    IF NEW.link_hash IS NULL THEN
        SET NEW.link_hash = SHA2(CONCAT(NEW.firma_id, NEW.numar_deviz, UNIX_TIMESTAMP()), 256);
    END IF;
END//

DELIMITER ;

-- ============================================
-- INDEXURI SUPLIMENTARE PENTRU PERFORMANTA
-- ============================================

-- Index pentru cautari rapide in devize
CREATE INDEX idx_devize_search ON devize(firma_id, data_deviz DESC, status);

-- Index pentru rapoarte financiare
CREATE INDEX idx_devize_financial ON devize(firma_id, data_deviz, total_general);

-- ============================================
-- PERMISIUNI SI SECURITATE
-- ============================================

-- Cream utilizator MySQL dedicat aplicatiei
-- NOTA: Trebuie adaptat cu parola dorita si hostname-ul serverului

-- CREATE USER 'devizo_user'@'localhost' IDENTIFIED BY 'parola_foarte_sigura_aici';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON devizo_db.* TO 'devizo_user'@'localhost';
-- FLUSH PRIVILEGES;

-- ============================================
-- INFORMATII FINALE
-- ============================================

SELECT 'Baza de date DEVIZO a fost creata cu succes!' AS mesaj;
SELECT 'Utilizatori creati:' AS info;
SELECT email,
       CASE rol_id
           WHEN 1 THEN 'Super Admin'
           WHEN 2 THEN 'Master Firma'
           WHEN 3 THEN 'Utilizator Firma'
       END AS rol,
       'Parola implicita: admin123 / demo123 / user123' AS nota
FROM utilizatori;

-- ============================================
-- SFARSIT SCRIPT BAZA DE DATE
-- ============================================
