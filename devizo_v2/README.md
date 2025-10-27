# DEVIZO v2.0 - CHECKPOINT 1

## ✅ Ce e Inclus în Checkpoint 1

**Core Framework & Login Funcțional**

- 🔐 Sistem autentificare multi-tenant
- 👥 RBAC (Role-Based Access Control) cu 3 niveluri
- 🔒 Securitate: CSRF protection, BCrypt, Session management
- 📊 3 Dashboards separate pentru fiecare rol
- 🗄️ Database schema completă
- ⚙️ Pre-configurat pentru devizo_nodex

## 📋 Cerințe Sistem

- PHP 8.0+ (testat pe PHP 8.2)
- MariaDB 10.6+ / MySQL 5.7+
- Apache cu mod_rewrite
- Minimum 256MB RAM PHP

## 🚀 Instrucțiuni Instalare

### Pas 1: Pregătire Bază de Date

1. Intră în **phpMyAdmin**
2. Creează baza de date: `devizo_nodex` (dacă nu există)
3. Selectează `devizo_nodex`
4. Click pe tab **"SQL"**
5. Copy-paste conținutul din `database/schema.sql`
6. Click **"Execută"**
7. Copy-paste conținutul din `database/demo-data.sql`
8. Click **"Execută"**

### Pas 2: Upload Fișiere

1. Extrage arhiva `devizo_v2_checkpoint1.zip`
2. Urcă tot conținutul folderului `devizo_v2/` pe server
3. Asigură-te că structura e:
   ```
   /devizo_v2/
   ├── config.php
   ├── index.php
   ├── login.php
   ├── logout.php
   ├── setup.php
   ├── .htaccess
   ├── core/
   ├── database/
   ├── dashboards/
   └── views/
   ```

### Pas 3: Configurare Permisiuni

Rulează în SSH sau File Manager:
```bash
chmod 755 devizo_v2/
chmod 644 devizo_v2/*.php
chmod 644 devizo_v2/config.php
chmod 644 devizo_v2/.htaccess
```

### Pas 4: Generare Hash-uri Parole

1. Accesează în browser: `https://www.devizo.ro/devizo_v2/setup.php`
2. Pagina va:
   - Testa conexiunea la baza de date
   - Verifica toate tabelele (33 tabele)
   - Genera hash-uri BCrypt pentru cele 3 parole
   - Actualiza baza de date
   - Verifica că parolele funcționează
3. Când vezi **"✓ Setup complet!"**, **ȘTERGE** `setup.php`:
   ```bash
   rm devizo_v2/setup.php
   ```

### Pas 5: Testare Login

Accesează: `https://www.devizo.ro/devizo_v2/login.php`

**Utilizatori Demo:**

| Rol | Email | Parolă | Vizibil pe pagina login |
|-----|-------|---------|------------------------|
| **Super Admin** | imperator@devizo.ro | Satelite1987!@# | ❌ NU (login manual) |
| **Master Firmă** | dominus@demo.devizo.ro | Demo123 | ✅ DA (click pe card) |
| **Utilizator** | executor@demo.devizo.ro | Demo123 | ✅ DA (click pe card) |

## 🎯 Ce Poate Face Fiecare Rol

### 🔴 Super Admin (Imperator)
- Vede toate firmele din sistem
- Alocă module către firme
- Monitorizează activitatea globală
- Gestionează setări sistem
- **NU** aparține unei firme

### 🔵 Master Firmă (Dominus)
- Gestionează utilizatorii firmei
- Alocă permisiuni pentru module
- Vede statistici firmă
- Acces complet la toate modulele alocate firmei
- Setări firmă

### 🟢 Utilizator (Executor)
- Acces limitat la module conform permisiunilor
- Poate vizualiza/crea/edita/șterge în funcție de permisiuni
- Vede propriile statistici
- Dashboard simplificat

## 📁 Structură Fișiere

```
devizo_v2/
│
├── config.php              # Configurare PRE-COMPLETATĂ
├── index.php               # Entry point - routing către dashboards
├── login.php               # Pagină autentificare
├── logout.php              # Handler logout
├── setup.php              # Generator hash-uri (ȘTERGE după rulare!)
├── .htaccess              # Securitate și clean URLs
│
├── core/                   # Framework core
│   ├── Auth.php           # Autentificare + RBAC (500+ linii)
│   ├── Database.php       # PDO wrapper cu query builder
│   ├── Helpers.php        # 600+ linii funcții utility
│   ├── PermissionManager.php  # Gestionare permisiuni RBAC
│   ├── Router.php         # Rutare URL
│   └── Session.php        # Management sesiuni securizate
│
├── database/              # SQL scripts
│   ├── schema.sql        # Schema completă (550+ linii, 33 tabele)
│   └── demo-data.sql     # Date demo cu 3 utilizatori
│
├── dashboards/           # Dashboards per rol
│   ├── super-admin.php   # Dashboard Imperator
│   ├── master-firma.php  # Dashboard Dominus
│   └── utilizator.php    # Dashboard Executor
│
└── views/                # Template-uri
    ├── header.php        # Header comun cu navigare
    └── footer.php        # Footer comun
```

## 🔐 Credențiale Database (PRE-CONFIGURATE)

```php
DB_HOST: localhost
DB_NAME: devizo_nodex
DB_USER: devizo_Zeus
DB_PASS: Satelite1987!@#
```

**✅ Nu trebuie să modifici nimic în `config.php` - e deja PRE-CONFIGURAT!**

## 🐛 Depanare Probleme

### Problemă: "Eroare conexiune bază de date"
**Soluție:**
1. Verifică în phpMyAdmin că userul `devizo_Zeus` există
2. Verifică că baza `devizo_nodex` există
3. Verifică privilegiile utilizatorului

### Problemă: "Email sau parolă incorectă"
**Soluție:**
1. Rulează din nou `setup.php`
2. Verifică că ai importat `demo-data.sql`
3. Verifică că tabelul `utilizatori` conține cele 3 înregistrări

### Problemă: "Fatal error: Unknown column"
**Soluție:**
1. Re-import `schema.sql` complet
2. Verifică că toate cele 33 tabele există

### Problemă: "Sesiune expirată la fiecare refresh"
**Soluție:**
1. Verifică permisiunile folderului session PHP
2. Verifică în `config.php` că `SESSION_VALIDATE_IP` e `false` pentru IP dinamic

## 📊 Baza de Date - 33 Tabele

**Core Multi-Tenant:**
- `roluri` - Roluri sistem (3 predefinite)
- `firme` - Companii înregistrate
- `utilizatori` - Utilizatori multi-tenant
- `sesiuni` - Tracking sesiuni active
- `log_activitate` - Log activitate utilizatori

**Module System:**
- `module_sistem` - Module disponibile (7 predefinite)
- `firme_module` - Alocare module → firme
- `utilizatori_permisiuni` - Permisiuni RBAC granulare

**Module Devize:**
- `devize` - Devize/Oferte
- `deviz_randuri` - Linii deviz (cu % manoperă individual!)
- `deviz_contoare` - Numerotare automată

**Nomenclatoare:**
- `unitati_masura` - Unități măsură (buc, ml, mp, etc.)
- `articole` - Articole/Produse
- `parteneri` - Clienți/Furnizori

**Module Cereri Oferte:**
- `cereri_oferta` - Cereri de ofertă către furnizori
- `cereri_oferta_randuri` - Linii cereri ofertă
- `oferte_furnizori` - Oferte primite de la furnizori
- `oferte_furnizori_randuri` - Linii oferte furnizori

**Utilități:**
- `curs_valutar_bnr` - Cursuri BNR EUR/RON
- `export_queue` - Coadă export contabilitate
- `setari_sistem` - Setări globale key-value

## ⚡ Caracteristici Tehnice

### Securitate
- ✅ BCrypt password hashing (cost 12)
- ✅ CSRF token protection pe toate formularele
- ✅ Session security (httponly, samesite, regenerare)
- ✅ Login attempt tracking + lockout (5 încercări, 15 min)
- ✅ IP și User-Agent validation
- ✅ SQL injection prevention (100% PDO prepared statements)
- ✅ XSS protection (output escaping)

### Performance (Optimizat Shared Hosting)
- ✅ Persistent PDO connections (connection pooling)
- ✅ Statement caching (prepared statements reuse)
- ✅ File-based caching (nu necesită Redis/Memcached)
- ✅ Lazy loading pentru clase
- ✅ Optimizat pentru 50+ utilizatori concurenți

### Multi-Tenant Architecture
- ✅ Firma isolation via `firma_id`
- ✅ Super Admin fără firmă (firma_id = NULL)
- ✅ Module allocation: Super Admin → Firma
- ✅ Permission allocation: Master → User

### RBAC (Role-Based Access Control)
- ✅ 3 niveluri: Super Admin (100) > Master Firma (50) > Utilizator (10)
- ✅ Permisiuni granulare per modul: vizualizare, creare, editare, ștergere, export
- ✅ Verificare permisiuni la fiecare request
- ✅ Module conditional display în UI

## 🎉 Ce Urmează

**CHECKPOINT 2 - Module Devize (Ziua 2-3)**
- CRUD complet pentru devize
- Calcul automat prețuri
- % manoperă individual pe linie ⭐
- Multi-currency EUR/RON cu curs BNR
- PDF generation
- Testare completă

**CHECKPOINT 3 - Module Articole & Parteneri (Ziua 3-4)**
- CRUD articole cu "add from deviz"
- Nomenclator unități măsură
- CRUD parteneri cu ANAF integration

## 📞 Suport

Pentru probleme sau întrebări, contactează-mă.

---

**✅ CHECKPOINT 1 COMPLET!**

*Generated with Claude Code*
*Co-Authored-By: Claude <noreply@anthropic.com>*
