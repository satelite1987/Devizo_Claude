-- ============================================
-- DEVIZO - Script Adaugare Tabele Lipsa
-- ============================================
--
-- Ruleaza acest script DOAR daca vezi in phpMyAdmin ca lipsesc tabele!
--
-- INSTRUCTIUNI:
-- 1. Deschide phpMyAdmin
-- 2. Selecteaza baza de date: devizo_db
-- 3. Click pe "SQL" (tab-ul de sus)
-- 4. Copiaza si lipeste ACEST fisier INTREG
-- 5. Click "Go" / "Executa"
-- ============================================

-- Tabel pentru randurile din devize
CREATE TABLE IF NOT EXISTS deviz_randuri (
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

-- Tabel pentru contoare devize (generare numar automat)
CREATE TABLE IF NOT EXISTS deviz_contoare (
    id INT PRIMARY KEY AUTO_INCREMENT,
    firma_id INT NOT NULL,
    an INT NOT NULL COMMENT 'Anul pentru care e contorul',
    luna INT NOT NULL COMMENT 'Luna pentru care e contorul',
    contor INT NOT NULL DEFAULT 0 COMMENT 'Ultimul numar generat',

    FOREIGN KEY (firma_id) REFERENCES firme(id) ON DELETE CASCADE,
    UNIQUE KEY unique_firma_an_luna (firma_id, an, luna)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel pentru oferte primite de la furnizori
CREATE TABLE IF NOT EXISTS oferte_furnizori (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cerere_id INT NOT NULL,
    nume_furnizor VARCHAR(255) NOT NULL,
    email_furnizor VARCHAR(255),
    telefon_furnizor VARCHAR(50),
    observatii TEXT,
    total_oferta DECIMAL(12,2),
    status ENUM('Primita', 'In evaluare', 'Acceptata', 'Respinsa') DEFAULT 'Primita',
    creat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (cerere_id) REFERENCES cereri_oferta(id) ON DELETE CASCADE,
    INDEX idx_cerere (cerere_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel pentru randurile din ofertele furnizorilor
CREATE TABLE IF NOT EXISTS oferte_furnizori_randuri (
    id INT PRIMARY KEY AUTO_INCREMENT,
    oferta_id INT NOT NULL,
    cerere_rand_id INT NOT NULL COMMENT 'Referinta la randul din cererea originala',
    pret_unitar DECIMAL(10,2) NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    observatii TEXT,

    FOREIGN KEY (oferta_id) REFERENCES oferte_furnizori(id) ON DELETE CASCADE,
    FOREIGN KEY (cerere_rand_id) REFERENCES cereri_oferta_randuri(id) ON DELETE CASCADE,
    INDEX idx_oferta (oferta_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- VERIFICARE FINALA
-- ============================================
-- Dupa rulare, verifica ca toate tabelele au fost create:
SELECT 'VERIFICARE COMPLETA!' as Mesaj,
       (SELECT COUNT(*) FROM information_schema.tables
        WHERE table_schema = DATABASE()
        AND table_name IN ('deviz_randuri', 'deviz_contoare', 'oferte_furnizori', 'oferte_furnizori_randuri')) as 'Tabele Create',
       4 as 'Tabele Asteptate';
