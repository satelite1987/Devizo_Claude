# DEVIZO - Ghid Complet de Instalare

## VERIFICARE PRE-INSTALARE

Inainte de instalare, verificati ca serverul indeplineste toate cerintele:

### 1. Verificare Versiune PHP

```bash
php -v
```

Trebuie sa fie **PHP 8.0+**

### 2. Verificare Extensii PHP

```bash
php -m | grep -E 'PDO|pdo_mysql|mbstring|curl|openssl|json|fileinfo'
```

Toate extensiile trebuie sa apara in lista.

### 3. Verificare MySQL/MariaDB

```bash
mysql --version
```

---

## PASUL 1: PREGATIRE BAZA DE DATE

### Varianta A: Prin phpMyAdmin

1. Accesati phpMyAdmin
2. Click pe "New" pentru baza de date noua
3. Nume: `devizo_db`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"
6. Selectati baza de date `devizo_db`
7. Click pe "Import"
8. Alegeti fisierul `database.sql`
9. Click "Go"

### Varianta B: Prin Command Line

```bash
# Logare MySQL
mysql -u root -p

# Executare comenzi SQL
CREATE DATABASE devizo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'devizo_user'@'localhost' IDENTIFIED BY 'Parola_Sigura_123!';
GRANT ALL PRIVILEGES ON devizo_db.* TO 'devizo_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import structura
mysql -u devizo_user -p devizo_db < database.sql
```

---

## PASUL 2: UPLOAD FISIERE

### Prin FTP/SFTP

1. Conectati-va la server cu FileZilla/WinSCP
2. Navigati la directorul web (ex: `/public_html`, `/var/www/html`, `/htdocs`)
3. Urcati **TOATE** fisierele si directoarele din `devizo_complete/`
4. Asteptati finalizarea upload-ului

### Prin SSH

```bash
# Navigare la director web
cd /var/www/html

# Upload fisiere (daca sunt pe masina locala)
scp -r devizo_complete/* user@server:/var/www/html/

# SAU daca descarcati de pe un server
wget https://url-catre-arhiva/devizo.zip
unzip devizo.zip
```

---

## PASUL 3: CONFIGURARE APLICATIE

### 1. Editare config/database.php

```bash
nano config/database.php
```

SAU folositi editorul din cPanel/Plesk.

Modificati liniile:

```php
define('DB_HOST', 'localhost');        // Adresa server MySQL
define('DB_NAME', 'devizo_db');        // Numele bazei de date
define('DB_USER', 'devizo_user');      // Utilizator MySQL
define('DB_PASS', 'Parola_Dvs_Aici');  // Parola MySQL

define('SITE_URL', 'https://www.domeniul-dvs.ro');  // URL complet

// Pentru productie:
define('DEBUG_MODE', 0);  // NU afisa erori in productie!
```

**IMPORTANT:** Salvati fisierul ca UTF-8 **FARA BOM**!

### 2. Verificare .htaccess

Deschideti `.htaccess` si verificati:

```apache
RewriteBase /
```

**NU** modificati aceasta linie! Trebuie sa fie exact `/`

### 3. **IMPORTANT:** Resetare Parole (OBLIGATORIU!)

**ATENTIE:** Baza de date contine parole temporare care **NU functioneaza**!
Trebuie sa rulati scriptul de resetare parole:

**Acceseaza in browser:**

```
https://www.domeniul-dvs.ro/fix_parole.php
```

Vei vedea o pagina cu fundal violet care va:
1. Conecta la baza de date
2. Genera hash-uri BCrypt pentru parolele demo
3. Actualiza parolele in baza de date
4. Verifica ca totul functioneaza

**Dupa ce scriptul afiseaza "SUCCES COMPLET":**

✅ Click pe butonul "INTRA IN APLICATIE"
❌ **STERGE IMEDIAT** fisierul `fix_parole.php` din server! (Contine credentiale sensibile!)

**Parolele setate vor fi:**
- `admin@devizo.ro` → parola `admin123`
- `doru@zaninstal.ro` → parola `demo123`
- `user@zaninstal.ro` → parola `user123`

---

## PASUL 4: SETARE PERMISIUNI

### Prin FTP

Click dreapta pe fiecare director si setati:
- `devizo_complete/` → **755**
- `logs/` → **777**
- `assets/uploads/` → **777**

### Prin SSH

```bash
cd /var/www/html  # SAU calea catre aplicatie

chmod 755 .
chmod 777 logs
chmod 777 assets/uploads
chmod 755 api
chmod 755 config
chmod 755 includes
```

---

## PASUL 5: TESTARE INSTALARE

### 1. Accesare Aplicatie

Deschideti browser-ul si accesati:

```
https://www.domeniul-dvs.ro/login.php
```

### 2. Utilizatori Demo

Autentificati-va cu unul dintre utilizatorii demo:

**Super Admin:**
- Email: `admin@devizo.ro`
- Parola: `admin123`

**Master Firma (Zaninstal):**
- Email: `doru@zaninstal.ro`
- Parola: `demo123`

**Utilizator Normal:**
- Email: `user@zaninstal.ro`
- Parola: `user123`

### 3. Verificari Post-Instalare

✅ Login functioneaza  
✅ Dashboard se incarca  
✅ Meniul de navigare apare  
✅ Nu sunt erori PHP afisate  
✅ Logo si stiluri se incarca corect  

---

## PASUL 6: SECURIZARE

### 1. Schimbare Parole Demo

**OBLIGATORIU!** Schimbati toate parolele demo:

1. Login ca `admin@devizo.ro`
2. Click pe "Contul Meu" → "Schimba Parola"
3. Introduceti parola noua (minim 6 caractere)
4. Salvati

Repetati pentru TOTI utilizatorii demo!

### 2. Creare Utilizatori Noi

**Pentru Super Admin:**
1. Navigati la "Admin" → "Gestionare Firme"
2. Click "Adauga Firma Noua"
3. Completati date firma
4. Setati abonament si numar utilizatori
5. Salvati

**Pentru Master Firma:**
1. Navigati la "Utilizatori"
2. Click "Utilizator Nou"
3. Completati date utilizator
4. Selectati rol (Master sau Utilizator)
5. Salvati

### 3. Configurare SSL (HTTPS)

**OBLIGATORIU pentru productie!**

Cerere certificat SSL:
- Let's Encrypt (GRATUIT) - recomandat
- cPanel AutoSSL
- Certificat comercial

Dupa activare SSL, modificati in `config/database.php`:

```php
define('SITE_URL', 'https://www.domeniul-dvs.ro');  // CU https://
```

### 4. Protectie Directoare

Adaugati `.htaccess` in `config/` si `logs/`:

```apache
# config/.htaccess
Order Deny,Allow
Deny from all

# logs/.htaccess  
Order Deny,Allow
Deny from all
```

---

## DEPANARE PROBLEME COMUNE

### Eroare: "Conexiune baza de date"

**Cauze:**
- Credentiale gresite in `config/database.php`
- MySQL nu ruleaza
- Utilizator MySQL fara permisiuni

**Solutie:**
```bash
# Verificare MySQL
systemctl status mysql

# Testare credentiale
mysql -u devizo_user -p devizo_db
```

### Eroare: "Headers already sent"

**Cauze:**
- Fisier PHP cu BOM (Byte Order Mark)
- Spatii/newline inainte de `<?php`

**Solutie:**
- Salvati fisierele ca UTF-8 **FARA BOM**
- Verificati ca nu exista spatii inainte de `<?php`

### Eroare 404 pe toate paginile

**Cauze:**
- `.htaccess` nu functioneaza
- mod_rewrite dezactivat

**Solutie:**
```bash
# Activare mod_rewrite
a2enmod rewrite
systemctl restart apache2

# Verificare AllowOverride
nano /etc/apache2/sites-available/000-default.conf

# Adaugati:
<Directory /var/www/html>
    AllowOverride All
</Directory>
```

### Paginile sunt albe (white screen)

**Cauze:**
- Eroare PHP critica
- Display errors = OFF

**Solutie:**
```php
// Temporar, in config/database.php
define('DEBUG_MODE', 1);  // Activati erorile
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificati logs/app.log pentru erori
```

### Upload-uri nu functioneaza

**Cauze:**
- Permisiuni gresite pe `assets/uploads/`
- PHP upload_max_filesize prea mic

**Solutie:**
```bash
# Permisiuni
chmod 777 assets/uploads/

# Verificare PHP settings
php -i | grep upload_max_filesize

# Modificare in php.ini
upload_max_filesize = 10M
post_max_size = 10M
```

---

## OPTIMIZARI PRODUCTIE

### 1. Cache

Adaugati in `.htaccess`:

```apache
# Cache static resources
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType text/javascript "access plus 1 month"
</IfModule>
```

### 2. Compresie GZIP

```apache
# Compresie
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

### 3. Backup Automat

Creati cron job pentru backup:

```bash
# Editare crontab
crontab -e

# Adaugare backup zilnic la 2 AM
0 2 * * * /usr/bin/mysqldump -u devizo_user -pParola devizo_db > /backup/devizo_$(date +\%Y\%m\%d).sql
```

---

## CHECKLIST FINAL

Inainte de a declara instalarea completa:

- [ ] Baza de date creata si importata
- [ ] Fisiere urcate pe server
- [ ] `config/database.php` configurat corect
- [ ] Permisiuni setate (755 si 777)
- [ ] Login functioneaza
- [ ] Dashboard se incarca
- [ ] Toate parolele demo schimbate
- [ ] SSL (HTTPS) activat
- [ ] `DEBUG_MODE = 0` in productie
- [ ] Directoare `config/` si `logs/` protejate
- [ ] Backup configurat

---

## SUPORT TEHNIC

Daca intampinati probleme:

1. Verificati `logs/app.log` pentru erori
2. Activati `DEBUG_MODE = 1` temporar
3. Verificati toate cerintele sistem
4. Contactati suport: support@devizo.ro

---

**Instalare realizata cu succes? Incepeti sa creati devize!** 🎉
