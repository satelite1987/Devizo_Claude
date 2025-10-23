# DEVIZO - Sistema de Gestiune Devize

## Aplicatie Web Profesionala pentru Gestiunea Devizelor si Ofertelor

**Versiune:** 1.0.0  
**Data:** 2025-10-23  
**Licenta:** Proprietara

---

## DESCRIERE

DEVIZO este o aplicatie web moderna si profesionala pentru gestionarea devizelor, ofertelor de pret si nomenclatoarelor. Aplicatia este construita pentru a fi **multi-tenant**, permitand gestionarea mai multor firme cu utilizatori si date separate.

### CARACTERISTICI PRINCIPALE

- **Gestionare Devize**: Creare, editare, vizualizare si export devize
- **Nomenclatoare**: Gestiune articole, parteneri, unitati de masura
- **Multi-tenant**: Fiecare firma isi gestioneaza propriile date
- **Sistem Autentificare**: 3 nivele de acces (Super Admin, Master Firma, Utilizator)
- **Integrare ANAF**: Completare automata date firme din Registrul ANAF
- **Export PDF**: Generare devize in format PDF
- **Partajare Devize**: Link-uri unice pentru partajare cu clienti
- **Design Responsive**: Functional pe desktop, tableta si mobil
- **Securitate**: Prepared statements, CSRF protection, password hashing

---

## CERINTE SISTEM

### Server
- **PHP:** 8.0 sau mai nou
- **MySQL/MariaDB:** 5.7+ / 10.3+
- **Apache/Nginx** cu mod_rewrite activat
- **HTTPS** recomandat pentru productie

### Extensii PHP (OBLIGATORII)
- PDO + PDO_MySQL
- mbstring
- curl
- openssl
- json
- session
- fileinfo

### Resurse Server (Recomandat)
- RAM: minim 512MB, recomandat 1GB+
- Storage: minim 500MB pentru aplicatie + date

---

## INSTALARE

### 1. PREGATIRE BAZA DE DATE

```sql
-- Creare baza de date
CREATE DATABASE devizo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Creare utilizator
CREATE USER 'devizo_user'@'localhost' IDENTIFIED BY 'parola_sigura_aici';
GRANT ALL PRIVILEGES ON devizo_db.* TO 'devizo_user'@'localhost';
FLUSH PRIVILEGES;

-- Import structura
mysql -u devizo_user -p devizo_db < database.sql
```

### 2. UPLOAD FISIERE

Urcati toate fisierele pe server in directorul dorit (ex: /public_html sau /var/www/html)

### 3. CONFIGURARE

Editati `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'devizo_db');
define('DB_USER', 'devizo_user');
define('DB_PASS', 'parola_dvs_aici');

define('SITE_URL', 'https://www.domeniul-dvs.ro');

// Pentru productie, setati:
define('DEBUG_MODE', 0);
```

### 4. PERMISIUNI DIRECTOARE

```bash
chmod 755 /calea/catre/devizo_complete
chmod 777 /calea/catre/devizo_complete/logs
chmod 777 /calea/catre/devizo_complete/assets/uploads
```

### 5. VERIFICARE .htaccess

Asigurati-va ca `.htaccess` contine:

```apache
RewriteBase /
```

**NU** folositi `/devizo/` sau alt subdirector!

### 6. CREARE UTILIZATORI

Accesati aplicatia si folositi utilizatorii demo:

- **Super Admin:** admin@devizo.ro / admin123
- **Master Firma:** doru@zaninstal.ro / demo123
- **Utilizator:** user@zaninstal.ro / user123

**IMPORTANT:** Schimbati parolele imediat!

---

## STRUCTURA APLICATIE

```
devizo_complete/
├── config/
│   └── database.php          # Configurare conexiune DB
├── includes/
│   ├── auth.php              # Sistem autentificare
│   ├── functions.php         # Functii utilitare
│   ├── header.php            # Header comun
│   └── footer.php            # Footer comun
├── api/
│   ├── devize.php            # API CRUD devize
│   ├── articole.php          # API CRUD articole
│   ├── parteneri.php         # API CRUD parteneri
│   └── anaf.php              # Integrare ANAF
├── assets/
│   ├── js/
│   │   └── main.js           # JavaScript principal
│   ├── css/                  # (styluri in header.php)
│   └── uploads/              # Upload-uri (logo, etc)
├── admin/                    # Panou Super Admin
├── logs/                     # Log-uri aplicatie
├── index.php                 # Dashboard
├── login.php                 # Autentificare
├── logout.php                # Deconectare
├── devize.php                # Lista devize
├── deviz-nou.php             # Creare deviz nou
├── deviz.php                 # Vizualizare/editare deviz
├── articole.php              # Gestiune articole
├── parteneri.php             # Gestiune parteneri
├── profil.php                # Profil utilizator
├── schimba-parola.php        # Schimbare parola
├── setari-firma.php          # Setari firma
├── .htaccess                 # Configurare Apache
└── database.sql              # Structura baza de date
```

---

## UTILIZARE

### ROLURI UTILIZATORI

1. **Super Admin (ID=1)**
   - Gestioneaza toate firmele
   - Creeaza/editeaza/sterge firme
   - Prelungeste abonamente
   - Acces la panou admin

2. **Master Firma (ID=2)**
   - Administrator al propriei firme
   - Creeaza utilizatori (in limita abonamentului)
   - Modifica setari firma
   - Acces complet la devize si nomenclatoare

3. **Utilizator Firma (ID=3)**
   - Creeaza si gestioneaza devize
   - Acces la articole si parteneri
   - NU poate modifica setari firma
   - NU poate crea alti utilizatori

### FLUX DE LUCRU TIPIC

1. **Creare Nomenclatoare**
   - Adaugare unitati de masura
   - Adaugare articole cu preturi
   - Adaugare parteneri/clienti

2. **Creare Deviz**
   - Selectare partener
   - Adaugare articole cu cantitati
   - Setare procent manopera
   - Generare deviz

3. **Gestionare Deviz**
   - Schimbare status (In lucru, Acceptat, Finalizat)
   - Export PDF
   - Partajare cu clientul
   - Duplicare pentru devize similare

---

## SECURITATE

### IMPLEMENTATE

✅ Prepared Statements (PDO) pentru toate query-urile  
✅ Password hashing cu `password_hash()`  
✅ CSRF protection  
✅ Session security  
✅ Input validation si sanitization  
✅ Verificare proprietate date (multi-tenant isolation)  
✅ Rate limiting pentru login  
✅ Logging activitati utilizatori  

### RECOMANDARI

- Folositi **HTTPS** in productie
- Schimbati **toate parolele demo**
- Setati `DEBUG_MODE = 0` in productie
- Backup regulat baza de date
- Monitorizati `logs/app.log`
- Restrictionati accesul la `config/` si `logs/`

---

## API ENDPOINTS

Toate API-urile returneaza JSON:

```json
{
  "success": true/false,
  "data": {...},
  "message": "Mesaj..."
}
```

### Devize
- `GET /api/devize.php?id=123` - Obtine un deviz
- `POST /api/devize.php` - Creare deviz
- `PUT /api/devize.php` - Actualizare status deviz
- `DELETE /api/devize.php` - Stergere deviz

### Articole
- `POST /api/articole.php` - Creare articol
- `PUT /api/articole.php` - Actualizare articol
- `DELETE /api/articole.php` - Stergere articol

### Parteneri
- `POST /api/parteneri.php` - Creare partener
- `PUT /api/parteneri.php` - Actualizare partener
- `DELETE /api/parteneri.php` - Stergere partener

### ANAF
- `GET /api/anaf.php?cui=XXXXXX` - Obtine date firma din ANAF

---

## DEPANARE

### Login nu functioneaza
- Verificati credentialele
- Verificati ca tabela `utilizatori` are date
- Verificati ca sesiunea PHP functioneaza
- Verificati permisiuni pe directorul de sesiuni

### Eroare "headers already sent"
- Verificati ca fisierele PHP sunt UTF-8 **fara BOM**
- Verificati ca nu exista spatii/newline-uri inainte de `<?php`
- Folositi `ob_start()` la inceputul fisierelor

### .htaccess nu functioneaza
- Verificati ca mod_rewrite este activat
- Verificati ca `AllowOverride All` in configurare Apache
- Asigurati-va ca `RewriteBase /` (nu `/subdirector/`)

### Eroare conexiune baza de date
- Verificati credentialele in `config/database.php`
- Verificati ca MySQL ruleaza
- Verificati ca utilizatorul are permisiuni

---

## SUPORT

Pentru intrebari si suport:
- Email: support@devizo.ro
- Website: https://www.devizo.ro

---

## CHANGELOG

### v1.0.0 (2025-10-23)
- Versiune initiala completa
- Sistem multi-tenant functional
- CRUD complet pentru devize, articole, parteneri
- Integrare ANAF
- Export PDF
- Design responsive
- Securitate completa

---

**Dezvoltat cu ❤️ pentru eficienta in business**
