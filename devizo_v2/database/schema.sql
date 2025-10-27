-- ============================================
-- DEVIZO v2.0 - Complete Database Schema
-- ============================================
-- Database: devizo_nodex
-- User: Zeus
-- Password: Satelite1987!@#
-- Generated: 2025-10-26
-- ============================================

-- Set charset and collation
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- CORE TABLES - User Management & Authentication
-- ============================================

-- Roluri utilizatori
CREATE TABLE IF NOT EXISTS `roluri` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `nume` VARCHAR(50) NOT NULL UNIQUE,
    `nivel` INT NOT NULL COMMENT '1=SuperAdmin, 2=Master, 3=User',
    `descriere` TEXT,
    `creat_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_nivel` (`nivel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User roles hierarchy';

-- Firme (Multi-tenant companies)
CREATE TABLE IF NOT EXISTS `firme` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `cui` VARCHAR(20) NOT NULL UNIQUE COMMENT 'Tax ID',
    `denumire` VARCHAR(255) NOT NULL,
    `nr_reg_com` VARCHAR(50) COMMENT 'Trade Registry Number',
    `adresa` TEXT,
    `telefon` VARCHAR(50),
    `email` VARCHAR(100),

    -- Subscription management
    `abonament_activ` BOOLEAN DEFAULT 1,
    `data_start_abonament` DATE,
    `data_expirare_abonament` DATE,

    -- Limits
    `max_utilizatori` INT DEFAULT 3 COMMENT 'Max users allowed',

    -- Customization
    `logo_path` VARCHAR(255),
    `culoare_primara` VARCHAR(7) DEFAULT '#3498db' COMMENT 'Primary brand color HEX',
    `procent_manopera_default` DECIMAL(5,2) DEFAULT 30.00 COMMENT 'Default labor percentage',

    -- Timestamps
    `creat_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `actualizat_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_cui` (`cui`),
    INDEX `idx_abonament` (`abonament_activ`, `data_expirare_abonament`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Multi-tenant companies';

-- Utilizatori
CREATE TABLE IF NOT EXISTS `utilizatori` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `firma_id` INT NULL COMMENT 'NULL = Super Admin',
    `rol_id` INT NOT NULL,

    -- Authentication
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `parola` VARCHAR(255) NOT NULL COMMENT 'BCrypt hashed',
    `nume` VARCHAR(100) NOT NULL,

    -- Status
    `activ` BOOLEAN DEFAULT 1,
    `ultima_autentificare` TIMESTAMP NULL,

    -- Timestamps
    `creat_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `actualizat_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`firma_id`) REFERENCES `firme`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`rol_id`) REFERENCES `roluri`(`id`),
    INDEX `idx_email` (`email`),
    INDEX `idx_firma` (`firma_id`),
    INDEX `idx_activ` (`activ`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='System users';

-- ============================================
-- MODULE SYSTEM TABLES
-- ============================================

-- Module disponibile în sistem
CREATE TABLE IF NOT EXISTS `module_sistem` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `slug` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Module identifier',
    `nume` VARCHAR(100) NOT NULL,
    `versiune` VARCHAR(20) NOT NULL,
    `descriere` TEXT,
    `autor` VARCHAR(100),

    -- Status
    `activ` BOOLEAN DEFAULT 1,
    `premium` BOOLEAN DEFAULT 0,

    -- File path
    `cale_fisiere` VARCHAR(255) COMMENT 'Path to module files',

    -- Dependencies
    `dependinte` TEXT COMMENT 'JSON array of required modules',

    -- Timestamps
    `creat_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `actualizat_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_slug` (`slug`),
    INDEX `idx_activ` (`activ`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Available system modules';

-- Module alocate firmelor (Super Admin decides)
CREATE TABLE IF NOT EXISTS `firme_module` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `firma_id` INT NOT NULL,
    `modul_id` INT NOT NULL,

    -- Status
    `activ` BOOLEAN DEFAULT 1,
    `data_activare` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `data_expirare` TIMESTAMP NULL,

    FOREIGN KEY (`firma_id`) REFERENCES `firme`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`modul_id`) REFERENCES `module_sistem`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_firma_modul` (`firma_id`, `modul_id`),
    INDEX `idx_firma` (`firma_id`),
    INDEX `idx_modul` (`modul_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Modules allocated to companies';

-- Permisiuni utilizatori per modul (Master decides)
CREATE TABLE IF NOT EXISTS `utilizatori_permisiuni` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `utilizator_id` INT NOT NULL,
    `modul_id` INT NOT NULL,
    `permisiune` VARCHAR(100) NOT NULL COMMENT 'e.g., devize.create, devize.edit',
    `valoare` BOOLEAN DEFAULT 1,

    FOREIGN KEY (`utilizator_id`) REFERENCES `utilizatori`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`modul_id`) REFERENCES `module_sistem`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_user_modul_perm` (`utilizator_id`, `modul_id`, `permisiune`),
    INDEX `idx_utilizator` (`utilizator_id`),
    INDEX `idx_modul` (`modul_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User permissions per module';

-- ============================================
-- CORE DATA TABLES
-- ============================================

-- Unități de măsură
CREATE TABLE IF NOT EXISTS `unitati_masura` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `firma_id` INT NOT NULL COMMENT 'Company-specific units',
    `simbol` VARCHAR(20) NOT NULL,
    `denumire` VARCHAR(100) NOT NULL,
    `descriere` TEXT,
    `creat_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`firma_id`) REFERENCES `firme`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_firma_simbol` (`firma_id`, `simbol`),
    INDEX `idx_firma` (`firma_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Units of measure';

-- Articole (nomenclator)
CREATE TABLE IF NOT EXISTS `articole` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `firma_id` INT NOT NULL,

    -- Article data
    `cod` VARCHAR(50),
    `denumire` VARCHAR(255) NOT NULL,
    `um_id` INT NOT NULL,
    `categorie` VARCHAR(100),

    -- Pricing
    `pret_unitar` DECIMAL(10,2) DEFAULT 0,
    `moneda` ENUM('EUR', 'RON') DEFAULT 'EUR',

    -- Additional info
    `descriere` TEXT,
    `observatii` TEXT,

    -- Timestamps
    `creat_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `actualizat_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`firma_id`) REFERENCES `firme`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`um_id`) REFERENCES `unitati_masura`(`id`),
    INDEX `idx_firma` (`firma_id`),
    INDEX `idx_denumire` (`denumire`),
    INDEX `idx_cod` (`cod`),
    INDEX `idx_categorie` (`categorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Products/Services nomenclator';

-- Parteneri (clienți/furnizori)
CREATE TABLE IF NOT EXISTS `parteneri` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `firma_id` INT NOT NULL,

    -- Company data
    `cui` VARCHAR(20),
    `denumire` VARCHAR(255) NOT NULL,
    `nr_reg_com` VARCHAR(50),

    -- Contact
    `adresa` TEXT,
    `telefon` VARCHAR(50),
    `email` VARCHAR(100),

    -- Banking
    `iban` VARCHAR(34),
    `banca` VARCHAR(255),

    -- Additional
    `observatii` TEXT,

    -- Timestamps
    `creat_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `actualizat_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`firma_id`) REFERENCES `firme`(`id`) ON DELETE CASCADE,
    INDEX `idx_firma` (`firma_id`),
    INDEX `idx_denumire` (`denumire`),
    INDEX `idx_cui` (`cui`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Partners (clients/suppliers)';

-- ============================================
-- DEVIZE (QUOTES) MODULE
-- ============================================

-- Devize
CREATE TABLE IF NOT EXISTS `devize` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `firma_id` INT NOT NULL,
    `utilizator_id` INT NOT NULL COMMENT 'Creator',
    `partener_id` INT COMMENT 'Client',

    -- Quote data
    `numar_deviz` VARCHAR(50) NOT NULL,
    `data_deviz` DATE NOT NULL,
    `amplasament` TEXT COMMENT 'Project location',
    `descriere` TEXT,

    -- Multi-currency
    `moneda` ENUM('EUR', 'RON') DEFAULT 'EUR',
    `curs_valutar` DECIMAL(10,4) NULL COMMENT 'Exchange rate at quote date',

    -- Status
    `status` ENUM('Draft', 'Trimis', 'Acceptat', 'Respins', 'In Lucru', 'Finalizat') DEFAULT 'Draft',

    -- Totals (calculated and stored for performance)
    `total_materiale` DECIMAL(12,2) DEFAULT 0,
    `total_manopera` DECIMAL(12,2) DEFAULT 0,
    `total_general` DECIMAL(12,2) DEFAULT 0,
    `total_ron` DECIMAL(12,2) NULL COMMENT 'Total in RON if EUR',

    -- Labor default percentage (can be overridden per line)
    `procent_manopera_default` DECIMAL(5,2) DEFAULT 30.00,

    -- Sharing
    `link_hash` VARCHAR(64) UNIQUE COMMENT 'Public share link hash',

    -- Timestamps
    `creat_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `actualizat_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`firma_id`) REFERENCES `firme`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`utilizator_id`) REFERENCES `utilizatori`(`id`),
    FOREIGN KEY (`partener_id`) REFERENCES `parteneri`(`id`) ON DELETE SET NULL,
    UNIQUE KEY `unique_numar_deviz` (`firma_id`, `numar_deviz`),
    INDEX `idx_firma` (`firma_id`),
    INDEX `idx_utilizator` (`utilizator_id`),
    INDEX `idx_partener` (`partener_id`),
    INDEX `idx_data` (`data_deviz`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Quotes/Estimates';

-- Deviz randuri (quote line items)
CREATE TABLE IF NOT EXISTS `deviz_randuri` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `deviz_id` INT NOT NULL,

    -- Line data
    `nr_crt` INT NOT NULL COMMENT 'Line number',
    `denumire` VARCHAR(255) NOT NULL,
    `um` VARCHAR(20) NOT NULL,
    `cantitate` DECIMAL(10,2) NOT NULL,

    -- Pricing
    `pret_unitar` DECIMAL(10,2) NOT NULL COMMENT 'Unit price for materials',
    `total_materiale` DECIMAL(12,2) NOT NULL COMMENT 'Quantity * Unit price',

    -- Labor - INDIVIDUAL PERCENTAGE PER LINE ⭐
    `tip_manopera` ENUM('general', 'custom') DEFAULT 'general',
    `procent_manopera_custom` DECIMAL(5,2) NULL COMMENT 'Custom labor % for this line',
    `pret_manopera` DECIMAL(10,2) NOT NULL COMMENT 'Labor price per unit',
    `total_manopera` DECIMAL(12,2) NOT NULL COMMENT 'Quantity * Labor price',

    -- Total
    `total_rand` DECIMAL(12,2) NOT NULL COMMENT 'Materials + Labor',

    -- Timestamps
    `creat_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`deviz_id`) REFERENCES `devize`(`id`) ON DELETE CASCADE,
    INDEX `idx_deviz` (`deviz_id`),
    INDEX `idx_nr_crt` (`nr_crt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Quote line items with individual labor %';

-- Deviz contoare (auto-numbering)
CREATE TABLE IF NOT EXISTS `deviz_contoare` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `firma_id` INT NOT NULL,
    `an` INT NOT NULL,
    `luna` INT NOT NULL,
    `contor` INT NOT NULL DEFAULT 0 COMMENT 'Last generated number',

    FOREIGN KEY (`firma_id`) REFERENCES `firme`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_firma_an_luna` (`firma_id`, `an`, `luna`),
    INDEX `idx_firma` (`firma_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Auto-numbering counters';

-- ============================================
-- CERERI OFERTA (RFQ) MODULE
-- ============================================

-- Cereri de oferta catre furnizori
CREATE TABLE IF NOT EXISTS `cereri_oferta` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `firma_id` INT NOT NULL,
    `utilizator_id` INT NOT NULL,

    -- Request data
    `titlu` VARCHAR(255) NOT NULL,
    `descriere` TEXT,
    `data_limita` DATE COMMENT 'Deadline for quotes',

    -- Status
    `status` ENUM('Activa', 'Inchisa', 'Anulata') DEFAULT 'Activa',

    -- Sharing
    `link_hash` VARCHAR(64) UNIQUE,

    -- Timestamps
    `creat_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `actualizat_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`firma_id`) REFERENCES `firme`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`utilizator_id`) REFERENCES `utilizatori`(`id`),
    INDEX `idx_firma` (`firma_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='RFQ to suppliers';

-- Cereri oferta randuri
CREATE TABLE IF NOT EXISTS `cereri_oferta_randuri` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `cerere_id` INT NOT NULL,
    `denumire` VARCHAR(255) NOT NULL,
    `um` VARCHAR(20) NOT NULL,
    `cantitate` DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (`cerere_id`) REFERENCES `cereri_oferta`(`id`) ON DELETE CASCADE,
    INDEX `idx_cerere` (`cerere_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='RFQ line items';

-- Oferte de la furnizori
CREATE TABLE IF NOT EXISTS `oferte_furnizori` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `cerere_id` INT NOT NULL,

    -- Supplier data
    `nume_furnizor` VARCHAR(255) NOT NULL,
    `email_furnizor` VARCHAR(255),
    `telefon_furnizor` VARCHAR(50),

    -- Quote
    `observatii` TEXT,
    `total_oferta` DECIMAL(12,2),
    `status` ENUM('Primita', 'In Evaluare', 'Acceptata', 'Respinsa') DEFAULT 'Primita',

    `creat_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`cerere_id`) REFERENCES `cereri_oferta`(`id`) ON DELETE CASCADE,
    INDEX `idx_cerere` (`cerere_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Supplier quotes';

-- Oferte furnizori randuri
CREATE TABLE IF NOT EXISTS `oferte_furnizori_randuri` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `oferta_id` INT NOT NULL,
    `cerere_rand_id` INT NOT NULL,
    `pret_unitar` DECIMAL(10,2) NOT NULL,
    `total` DECIMAL(12,2) NOT NULL,
    `observatii` TEXT,

    FOREIGN KEY (`oferta_id`) REFERENCES `oferte_furnizori`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`cerere_rand_id`) REFERENCES `cereri_oferta_randuri`(`id`) ON DELETE CASCADE,
    INDEX `idx_oferta` (`oferta_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Supplier quote line items';

-- ============================================
-- CURS VALUTAR BNR MODULE
-- ============================================

-- Cursuri valutare BNR
CREATE TABLE IF NOT EXISTS `curs_valutar_bnr` (
    `data` DATE PRIMARY KEY,
    `eur_ron` DECIMAL(10,4) NOT NULL,
    `usd_ron` DECIMAL(10,4),
    `gbp_ron` DECIMAL(10,4),
    `preluare_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_data` (`data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='BNR exchange rates';

-- ============================================
-- EXPORT CONTABILITATE MODULE
-- ============================================

-- Export queue
CREATE TABLE IF NOT EXISTS `export_queue` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `firma_id` INT NOT NULL,
    `deviz_id` INT NOT NULL,
    `tip_export` ENUM('saga', 'smartbill', 'fgo', 'oblio') NOT NULL,
    `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    `eroare` TEXT,
    `creat_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `procesat_la` TIMESTAMP NULL,

    FOREIGN KEY (`firma_id`) REFERENCES `firme`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`deviz_id`) REFERENCES `devize`(`id`) ON DELETE CASCADE,
    INDEX `idx_firma` (`firma_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Export queue for accounting software';

-- ============================================
-- LOGGING & AUDIT TABLES
-- ============================================

-- Activity log
CREATE TABLE IF NOT EXISTS `log_activitate` (
    `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
    `utilizator_id` INT,
    `firma_id` INT,
    `actiune` VARCHAR(100) NOT NULL,
    `detalii` TEXT,
    `ip_adresa` VARCHAR(45),
    `user_agent` TEXT,
    `creat_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`utilizator_id`) REFERENCES `utilizatori`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`firma_id`) REFERENCES `firme`(`id`) ON DELETE SET NULL,
    INDEX `idx_utilizator` (`utilizator_id`),
    INDEX `idx_firma` (`firma_id`),
    INDEX `idx_creat` (`creat_la`),
    INDEX `idx_actiune` (`actiune`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Activity audit log';

-- Sessions
CREATE TABLE IF NOT EXISTS `sesiuni` (
    `id` VARCHAR(128) PRIMARY KEY,
    `utilizator_id` INT NOT NULL,
    `ip_adresa` VARCHAR(45),
    `user_agent` TEXT,
    `data_sesiune` TEXT,
    `ultima_activitate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`utilizator_id`) REFERENCES `utilizatori`(`id`) ON DELETE CASCADE,
    INDEX `idx_utilizator` (`utilizator_id`),
    INDEX `idx_ultima_activitate` (`ultima_activitate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User sessions';

-- System settings
CREATE TABLE IF NOT EXISTS `setari_sistem` (
    `cheie` VARCHAR(100) PRIMARY KEY,
    `valoare` TEXT,
    `descriere` TEXT,
    `actualizat_la` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='System settings';

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- END OF SCHEMA
-- ============================================
