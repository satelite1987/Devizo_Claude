# DEVIZO v2.0 - PLAN FINAL CONFIRMAT
## Dezvoltare Intensivă - Start ACUM

---

## ✅ CLARIFICĂRI FINALE

### **1. Model Deployment:**
- ❌ **NU White-Label** - Toți clienții folosesc **devizo.ro**
- ✅ **SaaS Multi-Tenant** - O singură instalare, multiple firme
- ✅ **Subscripții** - Tu gestionezi prețurile separat (nu în cod)

### **2. Infrastructură Confirmată:**

**Hosting Actual:**
- Provider: Hostico.ro (Shared Hosting)
- Server: glc29
- PHP: 8.2 (perfect! ✅)
- MariaDB: 10.6.19 (perfect! ✅)
- Apache: 2.4.65
- SSL: ✅ Activ
- SMTP: ✅ Configurat
- Spațiu: ✅ Suficient

**Recomandare:**
```
ACUM (0-50 clienți):
✅ Shared hosting e PERFECT
✅ Voi optimiza codul pentru shared environment
✅ Cache agresiv pentru performanță

VIITOR (50+ clienți activi simultan):
→ Migrare la VPS recomandat
→ Specificații VPS recomandate:
  - 4 CPU cores
  - 8 GB RAM
  - 100 GB SSD
  - MariaDB 10.6+
  - PHP 8.2+
  - Redis/Memcached pentru cache
```

---

## 🎯 PLAN DEZVOLTARE RAPID

### **SĂPTĂMÂNA 1 (Zile 1-7): FUNDAȚIE + MODULE CORE**

#### **Zi 1-2: Core Framework**
```php
core/
├── Database.php          # PDO + Query Builder optimizat
├── Auth.php              # Multi-tenant authentication
├── Router.php            # Dynamic routing
├── Session.php           # Secure session management
├── Cache.php             # File-based cache (shared hosting)
└── Helpers.php           # Utility functions
```

**Optimizări Shared Hosting:**
- Query caching agresiv
- File-based cache (NU Redis/Memcached)
- Lazy loading pentru module
- Minificare CSS/JS automată
- Image optimization
- Database connection pooling

#### **Zi 3-4: Module System**
```php
core/
├── ModuleManager.php     # Load/Install/Uninstall
├── HookSystem.php        # Events & Filters
└── PermissionManager.php # RBAC granular
```

**Funcționalități:**
- Upload modul .zip
- Auto-instalare tabele SQL
- Activare/Dezactivare instant
- Dependency checking
- Version management

#### **Zi 5-7: Module DEVIZE (PRIORITATE 1)**
```
modules/devize/
├── manifest.json
├── install.sql
├── DevizeController.php
├── DevizeModel.php
├── views/
│   ├── list.php
│   ├── create.php
│   ├── edit.php
│   └── view.php
└── api/
    └── devize-api.php
```

**Funcționalități Speciale:**
✅ **Procent manoperă INDIVIDUAL per linie**
```sql
CREATE TABLE deviz_randuri (
    ...
    procent_manopera_tip ENUM('general', 'custom') DEFAULT 'general',
    procent_manopera_custom DECIMAL(5,2) NULL,
    ...
);
```

✅ **Adăugare articol DIN deviz**
```javascript
// În formular deviz, când tastezi produs inexistent:
onProductNotFound(denumire) {
    showModal('Adaugă "' + denumire + '" în nomenclator?');
    // Modal pre-completat cu denumirea
    // La salvare → adaugă în articole + completează deviz
}
```

---

### **SĂPTĂMÂNA 2 (Zile 8-14): MODULE ESENȚIALE**

#### **Zi 8-9: Module ARTICOLE + PARTENERI**

**Module ARTICOLE:**
- CRUD complet articole
- Categorii articole
- Import CSV
- **Nomenclator Unități Măsură EDITABIL**
- Căutare rapidă

**Module PARTENERI:**
- CRUD parteneri
- Import ANAF
- Istoricul colaborărilor
- Filtre avansate

#### **Zi 10-11: Module RAPOARTE PDF**
```
modules/rapoarte/
├── templates/
│   ├── deviz-standard.php
│   ├── deviz-detaliat.php
│   ├── raport-lunar.php
│   └── raport-furnizor.php
└── RapoarteGenerator.php
```

**Rapoarte Disponibile:**
- Deviz PDF (model standard firmă)
- Raport lunar devize
- Raport pe partener
- Raport articole utilizate
- Export Excel (.xlsx)

#### **Zi 12: Module CURS BNR**
```php
// Preluare automată zilnică
class CursBNR {
    public function fetchDaily() {
        $xml = file_get_contents('https://www.bnr.ro/nbrfxrates.xml');
        $curs = parseXML($xml);
        saveToDB($curs);
    }

    public function getCursLaData($data) {
        return DB::getCurs($data);
    }
}

// Cron job zilnic (setup automat)
// 08:00 AM: Preia curs BNR
```

**Database:**
```sql
CREATE TABLE curs_valutar_bnr (
    data DATE PRIMARY KEY,
    eur_ron DECIMAL(10,4),
    usd_ron DECIMAL(10,4),
    preluare_la TIMESTAMP
);

ALTER TABLE devize ADD COLUMN moneda ENUM('EUR', 'RON') DEFAULT 'EUR';
ALTER TABLE devize ADD COLUMN curs_valutar DECIMAL(10,4);
```

#### **Zi 13-14: Module EXPORT CONTABILITATE**
```
modules/export-contabilitate/
├── adapters/
│   ├── SagaAdapter.php
│   ├── SmartBillAdapter.php
│   ├── FGOAdapter.php
│   └── OblioAdapter.php
└── ExportManager.php
```

**Funcționalități:**
- Export deviz → Saga (format XML/CSV)
- Export deviz → SmartBill (API integration)
- Export deviz → FGO (format specific)
- Export deviz → Oblio (API integration)
- Queue system pentru export multiple
- Log export (succes/eroare)

---

### **SĂPTĂMÂNA 3 (Zile 15-21): TESTING + POLISH + DEPLOY**

#### **Zi 15-17: Testing Complet**
```
tests/
├── CoreTest.php           # Unit tests core
├── ModulesTest.php        # Integration tests module
├── PermissionsTest.php    # RBAC testing
├── SecurityTest.php       # SQL injection, XSS, CSRF
└── PerformanceTest.php    # Load testing
```

**Scenarii Testate:**
1. Super Admin (Imperator):
   - Login hidden (nu apare pe pagină)
   - Gestionare firme
   - Alocare module per firmă
   - Vezi date toate firmele

2. Master Firmă (Dominus):
   - Login vizibil pe pagină
   - Gestionare utilizatori
   - Alocare permisiuni per user
   - Acces doar la module alocate de Imperator

3. Utilizator (Executor):
   - Login vizibil pe pagină
   - Acces doar la permisiuni alocate de Dominus
   - Nu vede alte firme

**Testing Scenarii Complexe:**
- Master creează deviz → User vede/nu vede (depinde de permisiuni)
- Imperator dezactivează modul → Firmă nu mai are acces instant
- Modificare permisiuni → Efect la următorul login (NU relogin forțat)
- Export masiv 100 devize simultan
- 50 utilizatori activi simultan

#### **Zi 18-19: Admin Panel + UI Polish**

**Admin Panel Super Admin:**
```
admin/
├── dashboard.php          # Statistici globale
├── firme.php             # Gestionare firme
│   └── [modal] Alocare module per firmă
├── module.php            # Gestionare module sistem
│   ├── Upload modul nou
│   ├── Activare/Dezactivare
│   └── Verificare dependențe
└── settings.php          # Setări globale
```

**UI Improvements:**
- Mobile-first responsive design
- Dark mode (optional)
- Accessibility (WCAG 2.1)
- Loading states
- Error handling UI-friendly
- Success notifications

#### **Zi 20-21: Documentation + Deployment Package**

**Livrabile:**
```
DEVIZO_v2.0_FINAL/
│
├── devizo/                    # Cod aplicație
│   ├── core/
│   ├── modules/
│   ├── admin/
│   ├── themes/
│   └── ...
│
├── database/
│   ├── schema.sql            # Schema completă
│   ├── demo-data.sql         # Date demo
│   └── README.md             # Instrucțiuni import
│
├── docs/
│   ├── 01-Installation.pdf   # Instalare pas cu pas
│   ├── 02-User-Manual.pdf    # Manual utilizator
│   ├── 03-Admin-Manual.pdf   # Manual administrator
│   ├── 04-Developer-Guide.pdf # Creare module noi
│   └── 05-API-Reference.pdf  # API documentation
│
├── scripts/
│   ├── install.php           # Installer automat
│   ├── update.php            # Update system
│   └── backup.php            # Backup script
│
├── config.example.php        # Template config
├── .htaccess
└── README.txt                # Start aici!
```

---

## 🗄️ DATABASE SCHEMA COMPLETĂ

### **Tabele Core:**

```sql
-- Roluri utilizatori
CREATE TABLE roluri (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nume VARCHAR(50) UNIQUE NOT NULL,
    nivel INT NOT NULL COMMENT '1=SuperAdmin, 2=Master, 3=User',
    descriere TEXT,
    creat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Firme (clienți)
CREATE TABLE firme (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cui VARCHAR(20) UNIQUE NOT NULL,
    denumire VARCHAR(255) NOT NULL,
    nr_reg_com VARCHAR(50),
    adresa TEXT,
    telefon VARCHAR(50),
    email VARCHAR(100),

    -- Abonament
    abonament_activ BOOLEAN DEFAULT 1,
    data_start_abonament DATE,
    data_expirare_abonament DATE,

    -- Limite
    max_utilizatori INT DEFAULT 3,

    -- Personalizare
    logo_path VARCHAR(255),
    culoare_primara VARCHAR(7) DEFAULT '#3498db',

    -- Timestamps
    creat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_cui (cui),
    INDEX idx_abonament (abonament_activ, data_expirare_abonament)
);

-- Utilizatori
CREATE TABLE utilizatori (
    id INT PRIMARY KEY AUTO_INCREMENT,
    firma_id INT NULL COMMENT 'NULL = Super Admin',
    rol_id INT NOT NULL,

    email VARCHAR(100) UNIQUE NOT NULL,
    parola VARCHAR(255) NOT NULL,
    nume VARCHAR(100) NOT NULL,

    activ BOOLEAN DEFAULT 1,
    ultima_autentificare TIMESTAMP NULL,

    creat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (firma_id) REFERENCES firme(id) ON DELETE CASCADE,
    FOREIGN KEY (rol_id) REFERENCES roluri(id),
    INDEX idx_email (email),
    INDEX idx_firma (firma_id)
);

-- Module sistem
CREATE TABLE module_sistem (
    id INT PRIMARY KEY AUTO_INCREMENT,
    slug VARCHAR(50) UNIQUE NOT NULL,
    nume VARCHAR(100) NOT NULL,
    versiune VARCHAR(20) NOT NULL,
    descriere TEXT,
    autor VARCHAR(100),

    activ BOOLEAN DEFAULT 1,
    premium BOOLEAN DEFAULT 0,

    cale_fisiere VARCHAR(255),

    creat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_slug (slug),
    INDEX idx_activ (activ)
);

-- Module alocate firmelor (Super Admin decide)
CREATE TABLE firme_module (
    id INT PRIMARY KEY AUTO_INCREMENT,
    firma_id INT NOT NULL,
    modul_id INT NOT NULL,

    activ BOOLEAN DEFAULT 1,
    data_activare TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_expirare TIMESTAMP NULL,

    FOREIGN KEY (firma_id) REFERENCES firme(id) ON DELETE CASCADE,
    FOREIGN KEY (modul_id) REFERENCES module_sistem(id) ON DELETE CASCADE,
    UNIQUE KEY unique_firma_modul (firma_id, modul_id),
    INDEX idx_firma (firma_id),
    INDEX idx_modul (modul_id)
);

-- Permisiuni utilizatori (Master Firmă decide)
CREATE TABLE utilizatori_permisiuni (
    id INT PRIMARY KEY AUTO_INCREMENT,
    utilizator_id INT NOT NULL,
    modul_id INT NOT NULL,
    permisiune VARCHAR(100) NOT NULL COMMENT 'Ex: devize.create, devize.edit',
    valoare BOOLEAN DEFAULT 1,

    FOREIGN KEY (utilizator_id) REFERENCES utilizatori(id) ON DELETE CASCADE,
    FOREIGN KEY (modul_id) REFERENCES module_sistem(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_modul_perm (utilizator_id, modul_id, permisiune),
    INDEX idx_utilizator (utilizator_id)
);

-- Log activitate
CREATE TABLE log_activitate (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    utilizator_id INT,
    firma_id INT,
    actiune VARCHAR(100) NOT NULL,
    detalii TEXT,
    ip_adresa VARCHAR(45),
    user_agent TEXT,
    creat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (utilizator_id) REFERENCES utilizatori(id) ON DELETE SET NULL,
    FOREIGN KEY (firma_id) REFERENCES firme(id) ON DELETE SET NULL,
    INDEX idx_utilizator (utilizator_id),
    INDEX idx_firma (firma_id),
    INDEX idx_creat (creat_la)
);

-- Sesiuni (pentru tracking multiple devices)
CREATE TABLE sesiuni (
    id VARCHAR(128) PRIMARY KEY,
    utilizator_id INT NOT NULL,
    ip_adresa VARCHAR(45),
    user_agent TEXT,
    ultima_activitate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (utilizator_id) REFERENCES utilizatori(id) ON DELETE CASCADE,
    INDEX idx_utilizator (utilizator_id),
    INDEX idx_ultima_activitate (ultima_activitate)
);
```

### **Date Demo Pre-Populate:**

```sql
-- Roluri
INSERT INTO roluri (id, nume, nivel, descriere) VALUES
(1, 'Super Admin', 1, 'Administrator sistem - control total'),
(2, 'Master Firmă', 2, 'Administrator firmă - gestionare utilizatori proprii'),
(3, 'Utilizator Firmă', 3, 'Utilizator normal - acces limitat');

-- Super Admin (HIDDEN from login page)
INSERT INTO utilizatori (firma_id, rol_id, email, parola, nume, activ) VALUES
(NULL, 1, 'imperator@devizo.ro',
 '$2y$10$[BCrypt_HASH_pentru_Satelite1987!]',
 'Imperator', 1);

-- Firmă DEMO
INSERT INTO firme (cui, denumire, nr_reg_com, email,
                   abonament_activ, data_start_abonament, data_expirare_abonament,
                   max_utilizatori) VALUES
('RO_DEMO', 'Firma DEMONSTRATIVĂ',
 'J40/DEMO/2025', 'contact@demo.devizo.ro',
 1, '2025-01-01', '2026-01-01', 5);

-- Master Firmă DEMO (VISIBLE on login)
INSERT INTO utilizatori (firma_id, rol_id, email, parola, nume, activ) VALUES
(1, 2, 'dominus@demo.devizo.ro',
 '$2y$10$[BCrypt_HASH_pentru_Demo123]',
 'Dominus', 1);

-- Utilizator DEMO (VISIBLE on login)
INSERT INTO utilizatori (firma_id, rol_id, email, parola, nume, activ) VALUES
(1, 3, 'executor@demo.devizo.ro',
 '$2y$10$[BCrypt_HASH_pentru_Demo123]',
 'Executor', 1);

-- Module sistem
INSERT INTO module_sistem (slug, nume, versiune, premium) VALUES
('devize', 'Devize', '1.0.0', 1),
('articole', 'Articole & Nomenclator', '1.0.0', 0),
('parteneri', 'Parteneri', '1.0.0', 0),
('rapoarte', 'Rapoarte PDF Avansate', '1.0.0', 1),
('curs-bnr', 'Preluare Curs BNR', '1.0.0', 1),
('export-contabilitate', 'Export Contabilitate', '1.0.0', 1);

-- Alocăm toate modulele firmei DEMO
INSERT INTO firme_module (firma_id, modul_id)
SELECT 1, id FROM module_sistem;
```

---

## 🔒 SECURITATE & PERFORMANȚĂ

### **Securitate:**

1. **SQL Injection:** ✅ PDO Prepared Statements 100%
2. **XSS:** ✅ htmlspecialchars() pe tot output
3. **CSRF:** ✅ Token-uri pe toate formularele
4. **Password:** ✅ BCrypt + cost 12
5. **Session Hijacking:** ✅ Regenerare ID după login
6. **Brute Force:** ✅ Rate limiting (max 5 încercări/minut)
7. **File Upload:** ✅ Whitelist extensii + validare MIME
8. **Directory Traversal:** ✅ Path sanitization

### **Performanță (Shared Hosting):**

```php
// Cache strategy
class Cache {
    // File-based cache (NU Redis - shared hosting)
    public function get($key) {
        $file = CACHE_DIR . '/' . md5($key) . '.cache';
        if (file_exists($file) && (time() - filemtime($file)) < 3600) {
            return unserialize(file_get_contents($file));
        }
        return null;
    }

    public function set($key, $value, $ttl = 3600) {
        $file = CACHE_DIR . '/' . md5($key) . '.cache';
        file_put_contents($file, serialize($value));
    }
}

// Query optimization
class Database {
    // Connection pooling simulation
    private static $connection = null;

    public static function getConnection() {
        if (self::$connection === null) {
            self::$connection = new PDO(...);
            self::$connection->setAttribute(PDO::ATTR_PERSISTENT, true);
        }
        return self::$connection;
    }

    // Prepared statement caching
    private static $statements = [];

    public function prepare($sql) {
        $hash = md5($sql);
        if (!isset(self::$statements[$hash])) {
            self::$statements[$hash] = parent::prepare($sql);
        }
        return self::$statements[$hash];
    }
}

// Lazy loading module
ModuleManager::register('devize', function() {
    require_once 'modules/devize/DevizeController.php';
    return new DevizeController();
});
```

**Optimizări:**
- Gzip compression
- Browser caching (1 an pentru assets)
- Minificare CSS/JS
- Lazy load images
- Database indexes optimizați
- Query result caching
- Session storage optimizat

---

## 📦 INSTALARE SIMPLIFICATĂ

### **3 Pași TOTAL:**

```
1. Upload arhivă devizo_v2.0.zip → Extrage în public_html/

2. Importă în phpMyAdmin:
   - database/schema.sql
   - database/demo-data.sql

3. Editează config.php:
   DB_HOST: localhost
   DB_NAME: devizo_db
   DB_USER: devizo_user
   DB_PASS: [parola_ta]

   SITE_URL: https://www.devizo.ro

→ GATA! Acceseaza: https://www.devizo.ro

Login:
  Imperator: imperator@devizo.ro / Satelite1987!
  Dominus: dominus@demo.devizo.ro / Demo123
  Executor: executor@demo.devizo.ro / Demo123
```

**ZERO scripturi de fix necesare!**

---

## ⚡ START DEZVOLTARE - IMEDIAT!

### **Azi (Ziua 1) - ACUM:**

```
✅ 09:00-12:00: Core Database.php + Auth.php
✅ 12:00-14:00: Router.php + Session.php
✅ 14:00-17:00: Cache.php + Helpers.php
✅ 17:00-18:00: Testing core + commit
```

### **Daily Updates:**
Voi face commit zilnic cu progres și îți voi trimite:
- Ce am făcut azi
- Ce urmează mâine
- Orice blocker întâlnit

### **Weekly Demos:**
- Vineri Săptămâna 1: Demo Core + Module System
- Vineri Săptămâna 2: Demo Module Complete
- Vineri Săptămâna 3: **DELIVERY FINAL**

---

## 🎯 PORNESC ACUM!

**Status:** ✅ CONFIRMAT - DEVELOPMENT STARTED

**Timeline:** 21 zile (target: 15-18 zile dacă totul merge smooth)

**First Commit:** În 3-4 ore (Core Database + Auth)

---

**LET'S BUILD THIS! 🚀**
