# DEVIZO v2.0 - ARHITECTURĂ MODULARĂ COMPLETĂ
## Plan Dezvoltare SaaS Multi-Tenant cu Sistem Modular

---

## 🎯 VIZIUNE PROIECT

**Tip:** SaaS Multi-Tenant cu Module Premium
**Model Business:** Subscripție cu acces selectiv la module
**Arhitectură:** Modular (WordPress-style) + Multi-Tenant
**Timeline:** 2-3 săptămâni (35-45 ore dezvoltare)

---

## 📊 ARHITECTURĂ TEHNICĂ

### **NIVEL 1: CORE FRAMEWORK (Nucleu - NU SE ATINGE)**

```
core/
├── bootstrap.php              # Inițializare aplicație
├── Database.php               # Conexiuni DB + Query Builder
├── Auth.php                   # Autentificare multi-nivel
├── Session.php                # Management sesiuni
├── Router.php                 # URL routing dinamic
├── ModuleManager.php          # Manager module (inimă sistem)
├── HookSystem.php             # Hooks & Filters (ca WordPress)
├── PermissionManager.php      # RBAC + Module permissions
├── CacheManager.php           # Caching pentru performanță
└── Helpers.php                # Funcții ajutătoare
```

**Responsabilități Core:**
- Gestionare conexiuni bază de date
- Autentificare și autorizare
- Încărcare și gestionare module
- Sistem hook-uri pentru extensibilitate
- Permisiuni granulare (Super Admin → Firmă → User)

---

### **NIVEL 2: MODULE SYSTEM**

```
modules/
├── devize/                    # 📦 MODUL: Devize
│   ├── devize.module.php     # Controller principal
│   ├── manifest.json         # Configurare modul
│   ├── install.sql           # Tabele module
│   ├── uninstall.sql         # Cleanup la dezinstalare
│   ├── permissions.php       # Permisiuni specifice
│   ├── hooks.php             # Hook-uri expuse
│   ├── api/                  # API endpoints
│   ├── views/                # Template-uri UI
│   └── assets/               # CSS/JS specific
│
├── articole/                  # 📦 MODUL: Articole & Nomenclator
├── parteneri/                 # 📦 MODUL: Parteneri
├── cereri-oferta/             # 📦 MODUL: Cereri Oferta
├── rapoarte/                  # 📦 MODUL: Rapoarte PDF Avansate
├── curs-bnr/                  # 📦 MODUL: Preluare Curs BNR
├── export-contabilitate/      # 📦 MODUL: Export (Saga, SmartBill, etc)
├── stoc-management/           # 📦 MODUL: Gestiune Stocuri (viitor)
└── crm/                       # 📦 MODUL: CRM (viitor)
```

**Structură manifest.json:**
```json
{
  "name": "Devize",
  "slug": "devize",
  "version": "1.0.0",
  "author": "Devizo Team",
  "description": "Generare și gestionare devize de lucrări",
  "requires_modules": ["articole", "parteneri"],
  "permissions": [
    "devize.view",
    "devize.create",
    "devize.edit",
    "devize.delete",
    "devize.export_pdf"
  ],
  "menu": {
    "title": "Devize",
    "icon": "file-invoice",
    "position": 10,
    "parent": null
  },
  "hooks": {
    "provides": ["deviz_created", "deviz_updated", "deviz_deleted"],
    "listens_to": ["articol_updated", "partener_deleted"]
  },
  "database_tables": ["devize", "deviz_randuri", "deviz_contoare"],
  "premium": true,
  "price_monthly": 50.00,
  "currency": "EUR"
}
```

---

### **NIVEL 3: PERMISIUNI GRANULARE**

#### **Ierarhie Permisiuni:**

```
SUPER ADMIN (Imperator)
  │
  ├─ Gestionare Module GLOBAL
  │   ├─ Activare/Dezactivare module în sistem
  │   ├─ Upload module noi
  │   └─ Setare preț module
  │
  ├─ Gestionare Firme
  │   ├─ Creare/Editare/Ștergere firme
  │   ├─ **ALOCARE MODULE PER FIRMĂ** ⭐
  │   │   └─ Ex: Firma A are: [devize, articole, rapoarte]
  │   │       Firma B are: [devize, articole, rapoarte, export-contabilitate]
  │   └─ Setare max utilizatori per firmă
  │
  └─ Acces complet la date toate firmele

MASTER FIRMĂ (Dominus)
  │
  ├─ Gestionare Utilizatori propria firmă
  │   ├─ Creare/Editare/Ștergere utilizatori
  │   └─ **ALOCARE MODULE PER USER** ⭐
  │       └─ Ex: User "Executor" are acces doar la: [devize.view, articole.view]
  │           User "Manager" are acces la: [devize.*, articole.*, rapoarte.*]
  │
  ├─ Acces la modulele alocate firmei de Super Admin
  └─ Acces doar la datele propriei firme

UTILIZATOR FIRMĂ (Executor)
  │
  ├─ Acces doar la modulele alocate de Master Firmă
  ├─ Permisiuni granulare per modul (view/create/edit/delete)
  └─ Acces doar la datele propriei firme
```

#### **Tabele Database pentru Permisiuni:**

```sql
-- Module disponibile în sistem
CREATE TABLE module_sistem (
    id INT PRIMARY KEY AUTO_INCREMENT,
    slug VARCHAR(50) UNIQUE NOT NULL,
    nume VARCHAR(100) NOT NULL,
    versiune VARCHAR(20),
    activ BOOLEAN DEFAULT 1,
    premium BOOLEAN DEFAULT 0,
    pret_lunar DECIMAL(10,2),
    creat_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Module alocate per FIRMĂ (Super Admin decide)
CREATE TABLE firme_module (
    id INT PRIMARY KEY AUTO_INCREMENT,
    firma_id INT NOT NULL,
    modul_id INT NOT NULL,
    activ BOOLEAN DEFAULT 1,
    data_activare TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_expirare TIMESTAMP NULL,

    FOREIGN KEY (firma_id) REFERENCES firme(id) ON DELETE CASCADE,
    FOREIGN KEY (modul_id) REFERENCES module_sistem(id) ON DELETE CASCADE,
    UNIQUE KEY unique_firma_modul (firma_id, modul_id)
);

-- Permisiuni per UTILIZATOR (Master Firmă decide)
CREATE TABLE utilizatori_permisiuni (
    id INT PRIMARY KEY AUTO_INCREMENT,
    utilizator_id INT NOT NULL,
    modul_id INT NOT NULL,
    permisiune VARCHAR(100) NOT NULL, -- Ex: "devize.create"
    valoare BOOLEAN DEFAULT 1,

    FOREIGN KEY (utilizator_id) REFERENCES utilizatori(id) ON DELETE CASCADE,
    FOREIGN KEY (modul_id) REFERENCES module_sistem(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_modul_permisiune (utilizator_id, modul_id, permisiune)
);
```

---

### **NIVEL 4: HOOK SYSTEM (Extensibilitate)**

Inspirat din WordPress, permite modulelor să "vorbească" între ele:

```php
// Modul DEVIZE emite event
HookSystem::trigger('deviz_created', [
    'deviz_id' => 123,
    'firma_id' => 5,
    'total' => 15000.00
]);

// Modul RAPOARTE ascultă și răspunde
HookSystem::listen('deviz_created', function($data) {
    // Generează automat raport lunar
    Rapoarte::updateMonthlyStats($data['firma_id']);
});

// Modul EXPORT-CONTABILITATE ascultă
HookSystem::listen('deviz_created', function($data) {
    // Marchează pentru export către Saga
    ExportQueue::add('saga', $data['deviz_id']);
});
```

**Hook-uri Standard:**
```php
// Core hooks
'user_login'
'user_logout'
'firma_created'
'firma_updated'

// Module hooks
'deviz_created'
'deviz_updated'
'deviz_deleted'
'articol_created'
'partener_deleted'
'curs_bnr_updated'
```

---

## 🔧 FUNCȚIONALITĂȚI SPECIFICE CERUTE

### **1. Procent Manoperă Individual pe Linie**

**Tabel deviz_randuri actualizat:**
```sql
CREATE TABLE deviz_randuri (
    -- ... coloane existente

    -- VECHI (procent general)
    pret_manopera DECIMAL(10,2) COMMENT 'Calculat din procent general',

    -- NOU (procent individual)
    procent_manopera_custom DECIMAL(5,2) NULL COMMENT 'Procent custom per linie',
    tip_manopera ENUM('general', 'custom') DEFAULT 'general',

    -- Calculul se face astfel:
    -- Dacă tip_manopera = 'custom' → folosește procent_manopera_custom
    -- Dacă tip_manopera = 'general' → folosește devize.procent_manopera
);
```

**UI Deviz - Adăugare Linie:**
```
Denumire: [ Montaj gresie                    ]
UM:       [ mp ▼ ]  Cant: [50.00]  Preț: [25.00]

Manoperă:
  ○ Procent general (30%)  → Total: 375.00 EUR
  ● Procent custom: [50%]  → Total: 625.00 EUR  ⭐

Total linie: 2,500.00 EUR (materiale) + 625.00 EUR (manoperă) = 3,125.00 EUR
```

---

### **2. Adăugare Articol Direct din Deviz**

**Flow:**
```
1. User tastează în deviz: "Gresie Ceramos 60x60"
2. Sistem caută în nomenclator → NU EXISTĂ
3. Apare buton: [+ Adaugă "Gresie Ceramos 60x60" în nomenclator]
4. Click → Modal cu formular pre-completat:

   ╔═══════════════════════════════════════╗
   ║  Articol Nou                       [X]║
   ╠═══════════════════════════════════════╣
   ║ Denumire: Gresie Ceramos 60x60  ⭐    ║  (PRE-COMPLETAT)
   ║ UM:       [mp ▼]                      ║
   ║ Preț:     [____]                      ║
   ║ Categorie:[____]                      ║
   ║                                       ║
   ║     [Anulează]  [Salvează și Adaugă] ║
   ╚═══════════════════════════════════════╝

5. La salvare → Articol adăugat în nomenclator
6. Automat se completează în linia devizului
```

---

### **3. Utilizatori Demo**

**Database - Utilizatori Inițiali:**

```sql
-- Super Admin (NU apare pe login)
INSERT INTO utilizatori (firma_id, rol_id, email, parola, nume, activ) VALUES
(NULL, 1, 'imperator@devizo.ro',
 '$2y$10$[HASH_PENTRU_Satelite1987!]',
 'Imperator', 1);

-- Firmă DEMO
INSERT INTO firme (cui, denumire, ...) VALUES
('RO_DEMO', 'Firma DEMONSTRATIVĂ', ...);

-- Master Firmă DEMO (apare pe login)
INSERT INTO utilizatori (firma_id, rol_id, email, parola, nume, activ) VALUES
(1, 2, 'dominus@demo.devizo.ro',
 '$2y$10$[HASH_PENTRU_Demo123]',
 'Dominus', 1);

-- Utilizator Normal DEMO (apare pe login)
INSERT INTO utilizatori (firma_id, rol_id, email, parola, nume, activ) VALUES
(1, 3, 'executor@demo.devizo.ro',
 '$2y$10$[HASH_PENTRU_Demo123]',
 'Executor', 1);
```

**Pagina Login - UI:**
```
╔════════════════════════════════════╗
║         DEVIZO                      ║
║   Sistem Devize Profesional         ║
╠════════════════════════════════════╣
║                                     ║
║  Email:    [___________________]   ║
║  Parolă:   [___________________]   ║
║                                     ║
║           [  AUTENTIFICARE  ]      ║
║                                     ║
║  ───────── Cont Demo ─────────     ║
║                                     ║
║  👤 Dominus (Master)                ║
║     dominus@demo.devizo.ro          ║
║     [Folosește acest cont]          ║
║                                     ║
║  👤 Executor (Utilizator)           ║
║     executor@demo.devizo.ro         ║
║     [Folosește acest cont]          ║
║                                     ║
╚════════════════════════════════════╝
```

---

### **4. Multi-Currency & Curs BNR**

**Modul: curs-bnr/**

```php
// Preluare automată curs BNR
class CursBNR {
    public function fetchDaily() {
        $xml = file_get_contents('https://www.bnr.ro/nbrfxrates.xml');
        // Parse XML
        // Salvează în DB
    }

    public function getCursLaData($data) {
        // Returnează EUR/RON pentru data specificată
    }
}

// Hook: La creare deviz
HookSystem::listen('deviz_creating', function($data) {
    if ($data['moneda'] == 'EUR') {
        $curs = CursBNR::getCursLaData($data['data_deviz']);
        $data['curs_valutar'] = $curs;
        $data['total_ron'] = $data['total_eur'] * $curs;
    }
});
```

**Database:**
```sql
CREATE TABLE curs_valutar_bnr (
    id INT PRIMARY KEY AUTO_INCREMENT,
    data DATE UNIQUE NOT NULL,
    eur_ron DECIMAL(10,4) NOT NULL,
    usd_ron DECIMAL(10,4),
    preluare_la TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_data (data)
);

-- Tabela devize se extinde:
ALTER TABLE devize ADD COLUMN moneda ENUM('EUR', 'RON') DEFAULT 'EUR';
ALTER TABLE devize ADD COLUMN curs_valutar DECIMAL(10,4) NULL;
ALTER TABLE devize ADD COLUMN total_ron DECIMAL(12,2) NULL;
```

---

## 🗂️ STRUCTURĂ FIȘIERE FINALĂ

```
devizo/
│
├── index.php                  # Entry point
├── config.php                 # Configurare globală
├── .htaccess                  # URL rewriting
│
├── core/                      # Framework core (NU SE MODIFICĂ)
│   ├── bootstrap.php
│   ├── Database.php
│   ├── Auth.php
│   ├── Router.php
│   ├── ModuleManager.php
│   ├── HookSystem.php
│   ├── PermissionManager.php
│   └── Helpers.php
│
├── modules/                   # Module instalabile
│   ├── devize/
│   ├── articole/
│   ├── parteneri/
│   ├── rapoarte/
│   ├── curs-bnr/
│   └── export-contabilitate/
│
├── admin/                     # Panou administrare
│   ├── index.php             # Dashboard Super Admin
│   ├── module.php            # Gestionare module
│   ├── firme.php             # Gestionare firme + alocare module
│   └── utilizatori.php       # Gestionare utilizatori global
│
├── themes/                    # Teme vizuale
│   └── default/
│       ├── header.php
│       ├── footer.php
│       ├── sidebar.php
│       └── assets/
│
├── uploads/                   # Fișiere utilizatori
│   ├── logos/
│   ├── module/               # Module uploadate
│   └── documente/
│
├── cache/                     # Cache pentru performanță
├── logs/                      # Log-uri sistem
│
└── database/                  # SQL-uri
    ├── schema.sql            # Schema completă
    ├── demo-data.sql         # Date demo
    └── updates/              # Update-uri versiuni
```

---

## ⏱️ TIMELINE DEZVOLTARE

### **FAZA 1: Architecture & Core (Săptămâna 1)**
**Zile 1-2: Architecture Design**
- ✅ Design bază de date completă
- ✅ Structură fișiere și directoare
- ✅ Documentație tehnică

**Zile 3-4: Core Framework**
- ✅ Database.php (Query Builder)
- ✅ Auth.php (Multi-level auth)
- ✅ Router.php (Dynamic routing)
- ✅ Session.php (Session management)

**Zile 5-7: Module System**
- ✅ ModuleManager.php (Instalare/Dezinstalare module)
- ✅ HookSystem.php (Hooks & Filters)
- ✅ PermissionManager.php (RBAC granular)
- ✅ Testing core framework

---

### **FAZA 2: Core Modules (Săptămâna 2)**
**Zile 8-10: Module Esențiale**
- ✅ Modul: Devize (cu procent manoperă individual)
- ✅ Modul: Articole (cu adăugare din deviz)
- ✅ Modul: Parteneri
- ✅ Modul: Unități de Măsură

**Zile 11-12: Module Avansate**
- ✅ Modul: Rapoarte PDF
- ✅ Modul: Curs BNR
- ✅ Modul: Export Contabilitate (Saga, SmartBill, FGO, Oblio)

**Zi 13: Admin Panel**
- ✅ Dashboard Super Admin
- ✅ Gestionare module sistem
- ✅ Alocare module per firmă
- ✅ Gestionare utilizatori cu permisiuni

---

### **FAZA 3: Testing & Deployment (Săptămâna 3)**
**Zile 14-16: Testing Complet**
- ✅ Unit testing core
- ✅ Integration testing module
- ✅ User acceptance testing (toate scenariile)
- ✅ Bug fixing

**Zile 17-18: Polish & Documentation**
- ✅ UI/UX polish
- ✅ Documentație utilizator
- ✅ Documentație dezvoltator (pentru module noi)
- ✅ Video tutorial instalare

**Zi 19-20: Deployment**
- ✅ Script instalare automată
- ✅ Migration path (dacă vii de la v1)
- ✅ Backup & Recovery plan
- ✅ Performance optimization

**Zi 21: Final Delivery**
- ✅ Livrare arhivă completă
- ✅ Database cu toate datele demo
- ✅ Documentație completă
- ✅ Training session (dacă e necesar)

---

## 🎯 LIVRABILE

### **La Final Vei Primi:**

1. **Arhivă Completă (devizo_v2.0.zip)**
   - Cod 100% funcțional, testat
   - Database schema completă
   - Date demo (Imperator, Dominus, Executor)
   - Toate modulele core

2. **Instalare Simplă (3 pași):**
   ```
   1. Upload arhivă pe server
   2. Importă database.sql în phpMyAdmin
   3. Editează config.php (DB credentials)
   → GATA! Login cu Imperator / Satelite1987!
   ```

3. **Documentație Completă:**
   - Manual Utilizator (PDF)
   - Manual Administrator (PDF)
   - Manual Dezvoltator - Creare Module Noi (PDF)
   - API Documentation

4. **Video Tutorials:**
   - Instalare aplicație
   - Administrare module
   - Creare modul nou (pentru dezvoltatori)

5. **Bonus:**
   - Script migration de la v1 la v2 (dacă ai date existente)
   - Template modul gol pentru dezvoltări viitoare
   - Best practices & coding standards

---

## 💰 MONETIZARE - MODEL BUSINESS

### **Structură Prețuri Sugerată:**

**Plan FREE:**
- Devize (maxim 10/lună)
- Articole (maxim 50)
- Parteneri (maxim 10)
- 1 utilizator

**Plan STARTER (29 EUR/lună):**
- Devize nelimitate
- Articole nelimitate
- Parteneri nelimitați
- 3 utilizatori
- Module: Devize, Articole, Parteneri, Rapoarte Basic

**Plan PROFESSIONAL (59 EUR/lună):**
- Tot din STARTER +
- 10 utilizatori
- Module: + Curs BNR, Rapoarte Avansate, Export Contabilitate

**Plan ENTERPRISE (149 EUR/lună):**
- Tot din PROFESSIONAL +
- Utilizatori nelimitați
- Toate modulele (inclusiv CRM, Stock Management)
- Suport prioritar
- API access

**Module À La Carte:**
- Export Contabilitate: +15 EUR/lună
- Rapoarte Avansate: +10 EUR/lună
- Curs BNR: +5 EUR/lună
- Stock Management: +25 EUR/lună
- CRM: +30 EUR/lună

---

## 🔒 SECURITATE & CALITATE

### **Măsuri Implementate:**

1. **SQL Injection:** PDO prepared statements 100%
2. **XSS:** Sanitizare toate input-uri
3. **CSRF:** Token-uri pe toate formularele
4. **Password:** BCrypt hashing, hash-uri pre-generate în DB
5. **Session:** Secure session handling, regenerare ID
6. **Permissions:** Verificare permisiuni la fiecare request
7. **Logging:** Toate acțiunile critice logat
8. **Backup:** System de backup automat
9. **Updates:** System de update module automat

### **Testing:**

- Unit Tests pentru Core
- Integration Tests pentru Module
- Security Audit
- Performance Testing (1000+ utilizatori simultan)
- Cross-browser Testing (Chrome, Firefox, Safari, Edge)
- Mobile Responsiveness Testing

---

## 📞 NEXT STEPS

### **Confirmă-mi că:**

1. ✅ Ești de acord cu planul prezentat
2. ✅ Timeline 2-3 săptămâni e OK
3. ✅ Ai întrebări despre arhitectură?
4. ✅ Vrei să modific ceva?

### **După confirmare, pornesc imediat:**

**Azi (Zi 1):**
- Creez schema completă bază de date
- Setupez structura de directoare
- Încep development Core Framework

**Comunicare:**
- Update daily cu progres
- Demo la sfârșitul fiecărei săptămâni
- Răspund la orice întrebare în maxim 2 ore

---

## ❓ ÎNTREBĂRI PENTRU TINE

Înainte să pornesc, confirmă-mi:

1. **Hosting:** Ai hosting dedicat sau shared? (pentru optimizări)
2. **SSL:** Ai certificat SSL pe devizo.ro?
3. **Email:** Ai server SMTP pentru notificări email?
4. **Backup:** Ai acces la backup automat pe hosting?
5. **Domain:** devizo.ro e deja configurat și funcțional?

---

**Sunt gata să pornesc! Confirmă-mi și încep imediat cu FAZA 1!** 🚀
