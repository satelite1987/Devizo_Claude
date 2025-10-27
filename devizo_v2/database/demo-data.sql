-- ============================================
-- DEVIZO v2.0 - Demo Data
-- ============================================
-- IMPORTANT: Run setup.php after importing to generate password hashes
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- CORE DATA
-- ============================================

-- Roluri utilizatori
INSERT INTO `roluri` (`id`, `nume`, `nivel`, `descriere`) VALUES
(1, 'Super Admin', 1, 'Administrator sistem - control total asupra aplicației și tuturor firmelor'),
(2, 'Master Firmă', 2, 'Administrator firmă - gestionare utilizatori și date propria firmă'),
(3, 'Utilizator Firmă', 3, 'Utilizator normal - acces limitat conform permisiunilor alocate');

-- Firma DEMO
INSERT INTO `firme` (`id`, `cui`, `denumire`, `nr_reg_com`, `adresa`, `telefon`, `email`,
                     `abonament_activ`, `data_start_abonament`, `data_expirare_abonament`,
                     `max_utilizatori`, `procent_manopera_default`) VALUES
(1, 'RO_DEMO', 'Firma DEMONSTRATIVĂ',
 'J40/DEMO/2025', 'Strada Demonstrației nr. 1, București',
 '0700000000', 'contact@demo.devizo.ro',
 1, '2025-01-01', '2026-01-01', 10, 30.00);

-- Utilizatori
-- NOTA: Parolele vor fi setate de setup.php cu hash-uri BCrypt corecte
INSERT INTO `utilizatori` (`id`, `firma_id`, `rol_id`, `email`, `parola`, `nume`, `activ`) VALUES
(1, NULL, 1, 'imperator@devizo.ro', 'PLACEHOLDER_HASH_IMPERATOR', 'Imperator', 1),
(2, 1, 2, 'dominus@demo.devizo.ro', 'PLACEHOLDER_HASH_DOMINUS', 'Dominus', 1),
(3, 1, 3, 'executor@demo.devizo.ro', 'PLACEHOLDER_HASH_EXECUTOR', 'Executor', 1);

-- ============================================
-- MODULE SYSTEM
-- ============================================

-- Module disponibile
INSERT INTO `module_sistem` (`id`, `slug`, `nume`, `versiune`, `descriere`, `autor`, `activ`, `premium`, `cale_fisiere`) VALUES
(1, 'devize', 'Devize', '1.0.0', 'Generare și gestionare devize de lucrări cu procent manoperă individual', 'Devizo Team', 1, 1, 'modules/devize'),
(2, 'articole', 'Articole & Nomenclator', '1.0.0', 'Gestionare articole cu posibilitate adăugare din deviz', 'Devizo Team', 1, 0, 'modules/articole'),
(3, 'parteneri', 'Parteneri', '1.0.0', 'Gestionare parteneri (clienți și furnizori) cu integrare ANAF', 'Devizo Team', 1, 0, 'modules/parteneri'),
(4, 'rapoarte', 'Rapoarte PDF', '1.0.0', 'Generare rapoarte PDF avansate', 'Devizo Team', 1, 1, 'modules/rapoarte'),
(5, 'curs-bnr', 'Curs BNR', '1.0.0', 'Preluare automată curs valutar de la BNR', 'Devizo Team', 1, 1, 'modules/curs-bnr'),
(6, 'export-contabilitate', 'Export Contabilitate', '1.0.0', 'Export către Saga, SmartBill, FGO, Oblio', 'Devizo Team', 1, 1, 'modules/export-contabilitate'),
(7, 'cereri-oferta', 'Cereri Ofertă', '1.0.0', 'Gestionare cereri de ofertă către furnizori', 'Devizo Team', 1, 0, 'modules/cereri-oferta');

-- Alocăm toate modulele firmei DEMO
INSERT INTO `firme_module` (`firma_id`, `modul_id`, `activ`) VALUES
(1, 1, 1), -- Devize
(1, 2, 1), -- Articole
(1, 3, 1), -- Parteneri
(1, 4, 1), -- Rapoarte
(1, 5, 1), -- Curs BNR
(1, 6, 1), -- Export
(1, 7, 1); -- Cereri Oferta

-- Permisiuni pentru Dominus (Master) - toate permisiunile pentru toate modulele
INSERT INTO `utilizatori_permisiuni` (`utilizator_id`, `modul_id`, `permisiune`, `valoare`) VALUES
-- Devize
(2, 1, 'devize.view', 1),
(2, 1, 'devize.create', 1),
(2, 1, 'devize.edit', 1),
(2, 1, 'devize.delete', 1),
(2, 1, 'devize.export', 1),
-- Articole
(2, 2, 'articole.view', 1),
(2, 2, 'articole.create', 1),
(2, 2, 'articole.edit', 1),
(2, 2, 'articole.delete', 1),
-- Parteneri
(2, 3, 'parteneri.view', 1),
(2, 3, 'parteneri.create', 1),
(2, 3, 'parteneri.edit', 1),
(2, 3, 'parteneri.delete', 1),
-- Rapoarte
(2, 4, 'rapoarte.view', 1),
(2, 4, 'rapoarte.generate', 1),
-- Curs BNR
(2, 5, 'curs-bnr.view', 1),
-- Export
(2, 6, 'export.use', 1),
-- Cereri Oferta
(2, 7, 'cereri-oferta.view', 1),
(2, 7, 'cereri-oferta.create', 1),
(2, 7, 'cereri-oferta.edit', 1),
(2, 7, 'cereri-oferta.delete', 1);

-- Permisiuni pentru Executor (User) - doar view și create pentru câteva module
INSERT INTO `utilizatori_permisiuni` (`utilizator_id`, `modul_id`, `permisiune`, `valoare`) VALUES
-- Devize - poate vedea și crea, dar nu edita/șterge
(3, 1, 'devize.view', 1),
(3, 1, 'devize.create', 1),
(3, 1, 'devize.edit', 0),
(3, 1, 'devize.delete', 0),
(3, 1, 'devize.export', 1),
-- Articole - poate vedea
(3, 2, 'articole.view', 1),
(3, 2, 'articole.create', 0),
(3, 2, 'articole.edit', 0),
(3, 2, 'articole.delete', 0),
-- Parteneri - poate vedea
(3, 3, 'parteneri.view', 1),
(3, 3, 'parteneri.create', 0),
(3, 3, 'parteneri.edit', 0),
(3, 3, 'parteneri.delete', 0);

-- ============================================
-- NOMENCLATOR DATA
-- ============================================

-- Unități de măsură default pentru firma DEMO
INSERT INTO `unitati_masura` (`firma_id`, `simbol`, `denumire`, `descriere`) VALUES
(1, 'buc', 'Bucată', 'Unitate individuală'),
(1, 'mp', 'Metru pătrat', 'Suprafață'),
(1, 'ml', 'Metru liniar', 'Lungime'),
(1, 'mc', 'Metru cub', 'Volum'),
(1, 'kg', 'Kilogram', 'Greutate'),
(1, 't', 'Tonă', 'Greutate mare'),
(1, 'set', 'Set', 'Ansamblu de piese'),
(1, 'pach', 'Pachet', 'Ambalaj'),
(1, 'ore', 'Ore', 'Timp lucru'),
(1, 'zi', 'Zi', 'Perioadă lucrare');

-- Articole demo
INSERT INTO `articole` (`firma_id`, `cod`, `denumire`, `um_id`, `categorie`, `pret_unitar`, `moneda`, `descriere`) VALUES
(1, 'MAT001', 'Gresie porțelanată 60x60 cm', 1, 'Materiale', 45.00, 'EUR', 'Gresie de calitate premium'),
(1, 'MAT002', 'Adeziv pentru gresie', 8, 'Materiale', 12.50, 'EUR', 'Adeziv flexibil C2TE'),
(1, 'MAT003', 'Chit de rosturi', 1, 'Materiale', 8.00, 'EUR', 'Chit epoxidic rezistent'),
(1, 'MAT004', 'Vopsea lavabilă albă', 1, 'Materiale', 25.00, 'EUR', 'Vopsea lavabilă premium, 15L'),
(1, 'MAT005', 'Tencuială decorativă', 8, 'Materiale', 35.00, 'EUR', 'Tencuială decorativă granulată'),
(1, 'SERV001', 'Montaj gresie', 2, 'Servicii', 15.00, 'EUR', 'Montaj gresie cu adeziv'),
(1, 'SERV002', 'Vopsit pereți', 2, 'Servicii', 8.00, 'EUR', 'Vopsit în 2 straturi'),
(1, 'SERV003', 'Aplicare tencuială decorativă', 2, 'Servicii', 12.00, 'EUR', 'Aplicare tencuială decorativă');

-- Parteneri demo
INSERT INTO `parteneri` (`firma_id`, `cui`, `denumire`, `nr_reg_com`, `adresa`, `telefon`, `email`, `observatii`) VALUES
(1, 'RO12345678', 'CONSTRUCT PLUS SRL', 'J40/1234/2020',
 'Str. Constructorilor nr. 10, București', '0721111111', 'office@constructplus.ro',
 'Client important - proiecte rezidențiale'),
(1, 'RO87654321', 'RENOVARI EXPERT SRL', 'J40/5678/2019',
 'Bd. Unirii nr. 25, București', '0722222222', 'contact@renovari-expert.ro',
 'Specializat în renovări apartamente'),
(1, 'RO11223344', 'FURNIZOR MAT SRL', 'J40/9999/2018',
 'Str. Depozitelor nr. 5, București', '0723333333', 'vanzari@furnizormat.ro',
 'Furnizor materiale construcții');

-- ============================================
-- DEMO DEVIZE
-- ============================================

-- Deviz demo 1
INSERT INTO `devize` (`id`, `firma_id`, `utilizator_id`, `partener_id`, `numar_deviz`, `data_deviz`,
                      `amplasament`, `descriere`, `moneda`, `status`,
                      `total_materiale`, `total_manopera`, `total_general`, `procent_manopera_default`) VALUES
(1, 1, 2, 1, 'DEV-2025-001', '2025-01-15',
 'Apartament 3 camere, Str. Exemplu nr. 1, București',
 'Renovare completă apartament - baie și bucătărie',
 'EUR', 'Draft', 2500.00, 750.00, 3250.00, 30.00);

-- Randuri deviz demo 1
INSERT INTO `deviz_randuri` (`deviz_id`, `nr_crt`, `denumire`, `um`, `cantitate`,
                             `pret_unitar`, `total_materiale`,
                             `tip_manopera`, `procent_manopera_custom`, `pret_manopera`, `total_manopera`,
                             `total_rand`) VALUES
(1, 1, 'Gresie porțelanată 60x60 cm', 'mp', 25.00, 45.00, 1125.00, 'general', NULL, 13.50, 337.50, 1462.50),
(1, 2, 'Montaj gresie (manoperă specială 50%)', 'mp', 25.00, 15.00, 375.00, 'custom', 50.00, 7.50, 187.50, 562.50),
(1, 3, 'Adeziv pentru gresie', 'pach', 8.00, 12.50, 100.00, 'general', NULL, 3.75, 30.00, 130.00),
(1, 4, 'Chit de rosturi', 'buc', 10.00, 8.00, 80.00, 'general', NULL, 2.40, 24.00, 104.00);

-- Deviz demo 2
INSERT INTO `devize` (`id`, `firma_id`, `utilizator_id`, `partener_id`, `numar_deviz`, `data_deviz`,
                      `amplasament`, `descriere`, `moneda`, `curs_valutar`, `status`,
                      `total_materiale`, `total_manopera`, `total_general`, `total_ron`, `procent_manopera_default`) VALUES
(2, 1, 2, 2, 'DEV-2025-002', '2025-01-20',
 'Casa particulară, Str. Exemplu nr. 25, Ilfov',
 'Vopsit interior casă',
 'EUR', 4.9750, 'Trimis', 1800.00, 540.00, 2340.00, 11641.50, 30.00);

-- Randuri deviz demo 2
INSERT INTO `deviz_randuri` (`deviz_id`, `nr_crt`, `denumire`, `um`, `cantitate`,
                             `pret_unitar`, `total_materiale`,
                             `tip_manopera`, `procent_manopera_custom`, `pret_manopera`, `total_manopera`,
                             `total_rand`) VALUES
(2, 1, 'Vopsea lavabilă albă', 'buc', 12.00, 25.00, 300.00, 'general', NULL, 7.50, 90.00, 390.00),
(2, 2, 'Vopsit pereți', 'mp', 150.00, 8.00, 1200.00, 'general', NULL, 2.40, 360.00, 1560.00),
(2, 3, 'Tencuială decorativă', 'pach', 6.00, 35.00, 210.00, 'general', NULL, 10.50, 63.00, 273.00);

-- ============================================
-- CURS BNR - Date demo pentru ultima lună
-- ============================================

INSERT INTO `curs_valutar_bnr` (`data`, `eur_ron`, `usd_ron`) VALUES
('2025-01-20', 4.9750, 4.5200),
('2025-01-19', 4.9730, 4.5180),
('2025-01-18', 4.9700, 4.5150),
('2025-01-17', 4.9680, 4.5130),
('2025-01-16', 4.9720, 4.5160);

-- ============================================
-- SYSTEM SETTINGS
-- ============================================

INSERT INTO `setari_sistem` (`cheie`, `valoare`, `descriere`) VALUES
('app_version', '2.0.0', 'Versiunea aplicației'),
('maintenance_mode', '0', 'Modul mentenanță (0=OFF, 1=ON)'),
('enable_registration', '0', 'Permite înregistrare utilizatori noi (0=OFF, 1=ON)'),
('default_language', 'ro', 'Limba implicită aplicație'),
('session_lifetime', '7200', 'Durata sesiune în secunde (2 ore)'),
('max_login_attempts', '5', 'Număr maxim încercări login eșuate'),
('login_lockout_time', '900', 'Timp blocare după încercări eșuate (15 min)');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- IMPORTANT NOTE:
-- After importing this file, run setup.php to:
-- 1. Generate correct BCrypt password hashes
-- 2. Verify all data was imported correctly
-- 3. Initialize system
-- ============================================
