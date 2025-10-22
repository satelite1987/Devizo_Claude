# DEVIZO - Aplicatie Web pentru Gestiunea Devizelor

## Versiune: 1.0.0
## Data: 22 Octombrie 2025

---

## CUPRINS

1. [Descriere Aplicatie](#descriere-aplicatie)
2. [Cerinte de Sistem](#cerinte-de-sistem)
3. [Instalare Pas cu Pas](#instalare-pas-cu-pas)
4. [Structura Fisierelor](#structura-fisierelor)
5. [Configurare](#configurare)
6. [Utilizare](#utilizare)
7. [Conturi Demo](#conturi-demo)
8. [Troubleshooting](#troubleshooting)
9. [Dezvoltare Ulterioara](#dezvoltare-ulterioara)
10. [Securitate](#securitate)

---

## DESCRIERE APLICATIE

DEVIZO este o aplicatie web profesionala pentru gestionarea devizelor, dezvoltata special pentru monetizare prin sistem de abonamente.

### Caracteristici Principale:

#### **Sistem Multi-Tenant**
- Fiecare firma client (identificata prin CUI) are propriile date
- Izolare completa a datelor intre firme
- Gestionare abonamente cu date de expirare

#### **Ierarhie Utilizatori**
1. **Super Admin** - Administrator principal al aplicatiei
   - Gestioneaza toate firmele
   - Controleaza abonamentele
   - Acces complet la toate datele

2. **Master Firma** - Administrator firma client
   - Gestioneaza utilizatorii propriei firme
   - Configureaza setarile firmei (logo, culori, etc.)
   - Acces complet la datele firmei

3. **Utilizator Firma** - Utilizator normal
   - Creaza si gestioneaza devize
   - Gestioneaza articole si parteneri
   - Acces limitat conform permisiunilor

#### **Functionalitati Devize**
- Creare devize cu statusuri (In asteptare, Acceptat, In lucru, Finalizat)
- Export PDF personalizat cu logo si culori firma
- Link unic de partajare (ramane acelasi la modificari)
- Filtrare dupa status, client, perioada
- Numerotare automata per client
- Calcul automat totaluri (materiale + manopera)

#### **Nomenclatoare**
- **Articole**: Baza de date cu materiale si preturi
- **Parteneri**: Clientii firmei (cu date ANAF optionale)
- **Unitati Masura**: Configurabile per firma

#### **Cereri Oferta Furnizori**
- Trimitere cereri catre multiple furnizori
- Import CSV pentru cantitati
- Furnizori completeaza preturile
- Generare deviz direct din oferte

#### **Monetizare**
- Abonamente cu date de expirare
- Limita numar utilizatori per firma
- Control activare/dezactivare import CSV
- Rapoarte si statistici

#### **Integrare ANAF** (Optional)
- Extragere automata date firma pe baza CUI
- Validare CUI-uri
- Completare automata date partene

ri

---

## CERINTE DE SISTEM

### Server Web:
- **Web Server**: Apache 2.4+ sau Nginx 1.18+
- **PHP**: 7.4 sau superior (recomandat 8.0+)
- **MySQL**: 5.7+ sau MariaDB 10.3+
- **Spatiu Disc**: Minim 100 MB (pentru aplicatie si uploads)
- **SSL**: Recomandat (pentru securitate)

### Extensii PHP Necesare:
```
- php-mysqli (sau php-pdo)
- php-curl (pentru integrare ANAF)
- php-gd (pentru procesare imagini/logo-uri)
- php-mbstring
- php-json
- php-session
```

### Browser-e Suportate:
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Orice browser modern cu suport JavaScript

### Alte Cerinte:
- Acces cPanel, Plesk sau FTP pentru upload fisiere
- Acces phpMyAdmin sau linie comanda MySQL pentru baza de date
- Conexiune internet stabila

---

## INSTALARE PAS CU PAS

### PASUL 1: PREGATIRE FISIERE

1. **Descarcati arhiva ZIP** cu aplicatia
2. **Dezarhivati** arhiva pe computerul local
3. **Verificati** ca aveti toate fisierele din structura de mai jos

### PASUL 2: UPLOAD FISIERE PE SERVER

#### Varianta A: Prin cPanel File Manager
1. Logati-va in cPanel
2. Accesati "File Manager"
3. Navigati la folder-ul `public_html` (sau `www`, `htdocs`)
4. Creati un folder nou numit `devizo` (optional, puteti pune direct in root)
5. Uploadati toate fisierele din folder-ul `devizo` dezarhivat
6. Setati permisiuni:
   - Foldere: 755
   - Fisiere: 644
   - Folder `assets/uploads`: 775
   - Folder `logs`: 775 (il veti crea manual daca nu exista)

#### Varianta B: Prin FTP (FileZilla)
1. Deschideti FileZilla
2. Conectati-va la server cu datele FTP (host, user, parola)
3. In panoul stanga (local) navigati la folder-ul dezarhivat
4. In panoul dreapta (server) navigati la `public_html`
5. Trageti toate fisierele din stanga in dreapta
6. Asteptati finalizarea upload-ului

### PASUL 3: CREARE BAZA DE DATE

#### Varianta A: Prin cPanel (recomandat pentru incepatori)
1. Logati-va in cPanel
2. Cautati si accesati "MySQL Databases"
3. Creati o baza de date noua:
   - Nume: `devizo_db` (sau alt nume dorit)
   - Click "Create Database"
4. Creati un utilizator MySQL:
   - Username: `devizo_user` (sau alt nume dorit)
   - Parola: Generati o parola sigura (SALVATI-O!)
   - Click "Create User"
5. Adaugati utilizatorul la baza de date:
   - Selectati baza de date creata
   - Selectati utilizatorul creat
   - Click "Add"
   - Bifati "ALL PRIVILEGES"
   - Click "Make Changes"

#### Varianta B: Prin phpMyAdmin
1. Accesati phpMyAdmin
2. Click pe tab-ul "SQL"
3. Copiati comanda:
   ```sql
   CREATE DATABASE devizo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
4. Click "Go"

### PASUL 4: IMPORTARE STRUCTURA BAZA DE DATE

1. In cPanel, accesati "phpMyAdmin"
2. Selectati baza de date `devizo_db` din lista din stanga
3. Click pe tab-ul "Import"
4. Click "Choose File" si selectati fisierul `database.sql` din arhiva
5. Asigurati-va ca "Format" este setat pe "SQL"
6. Click "Go" (sau "Execute")
7. Asteptati mesajul de confirmare (poate dura 30-60 secunde)
8. Verificati ca toate tabelele au fost create:
   - Click pe tab-ul "Structure"
   - Ar trebui sa vedeti: utilizatori, firme, devize, articole, parteneri, etc.

### PASUL 5: CONFIGURARE APLICATIE

1. Accesati folder-ul aplicatiei pe server (prin File Manager sau FTP)
2. Navigati la `config/database.php`
3. Click dreapta > "Edit" (sau descarcati, editati local si re-uploadati)
4. **MODIFICATI** urmatoarele linii cu datele voastre:

```php
// EDITATI ACESTE VALORI:
define('DB_HOST', 'localhost');        // Pastrati 'localhost' in majoritatea cazurilor
define('DB_NAME', 'devizo_db');        // Numele bazei de date create la Pasul 3
define('DB_USER', 'devizo_user');      // Utilizatorul MySQL creat la Pasul 3
define('DB_PASS', 'PAROLA_VOASTRA');   // Parola utilizatorului MySQL

// Schimbati URL-ul cu URL-ul real al aplicatiei:
define('SITE_URL', 'https://domeniul-vostru.ro/devizo');  // SAU doar '/devizo' daca e in subfolder
// Exemple:
// https://devizo.ro (daca aplicatia e in root)
// https://domeniul.ro/devizo (daca e in subfolder)
// http://localhost/devizo (pentru testare locala)
```

5. **SALVATI** fisierul
6. **IMPORTANT**: Dupa testare, schimbati `DEBUG_MODE` de la `1` la `0` pentru productie

### PASUL 6: VERIFICARE INSTALARE

1. Deschideti browser-ul
2. Navigati la URL-ul aplicatiei: `https://domeniul-vostru.ro/devizo`
3. Ar trebui sa vedeti pagina de login
4. Daca vedeti erori:
   - Verificati din nou datele de conectare la baza de date
   - Verificati ca fisierul `database.sql` a fost importat corect
   - Consultati sectiunea Troubleshooting

### PASUL 7: PRIMA AUTENTIFICARE

1. Pe pagina de login, folositi unul din conturile demo (vezi sectiunea Conturi Demo)
2. **Cont Super Admin** (pentru gestionare firme):
   - Email: `admin@devizo.ro`
   - Parola: `admin123`
3. Logati-va cu succes
4. **IMPORTANT**: Schimbati IMEDIAT parola din "Contul Meu" > "Schimba Parola"

### PASUL 8: CONFIGURARE INITIALA

#### Pentru Super Admin:
1. Accesati "Administrare" > "Firme"
2. Editati firma demo sau adaugati firma voastra
3. Setati datele de expirare abonament
4. Creati primul utilizator Master pentru firma

#### Pentru Master Firma:
1. Accesati "Setari Firma"
2. Uploadati logo-ul firmei
3. Setati culoarea predominanta pe devize
4. Configurati procentul implicit pentru manopera
5. Activati/dezactivati importul CSV daca e necesar

---

## STRUCTURA FISIERELOR

```
devizo/
│
├── config/
│   └── database.php          # Configurare conexiune baza de date [EDITATI ACEST FISIER]
│
├── includes/
│   ├── auth.php              # Functii autentificare (login, logout, register)
│   ├── functions.php         # Functii generale (validari, formatari, ANAF)
│   ├── header.php            # Template header (meniu, navigare)
│   └── footer.php            # Template footer
│
├── api/
│   ├── articole.php          # API pentru gestionare articole
│   ├── parteneri.php         # API pentru gestionare parteneri
│   ├── devize.php            # API pentru gestionare devize
│   └── furnizori.php         # API pentru cereri oferta
│
├── admin/
│   ├── index.php             # Dashboard Super Admin
│   ├── firme.php             # Gestionare firme
│   ├── utilizatori.php       # Gestionare utilizatori
│   └── rapoarte.php          # Rapoarte si statistici
│
├── assets/
│   ├── css/
│   │   └── style.css         # Stiluri CSS custom
│   ├── js/
│   │   └── main.js           # JavaScript principal
│   └── uploads/
│       └── logos/            # Logo-uri firme (PERMISIUNI 775)
│
├── logs/
│   └── app.log               # Log-uri aplicatie (PERMISIUNI 775)
│
├── database.sql              # Structura baza de date [IMPORTATI IN MYSQL]
├── index.php                 # Dashboard principal
├── login.php                 # Pagina autentificare
├── logout.php                # Deconectare
├── devize.php                # Pagina gestionare devize
├── articole.php              # Pagina gestionare articole
├── parteneri.php             # Pagina gestionare parteneri
├── cereri-oferta.php         # Pagina cereri oferta furnizori
├── utilizatori.php           # Pagina gestionare utilizatori (Master)
├── profil.php                # Profilul utilizatorului
├── setari-firma.php          # Setari firma (logo, culori, etc.)
├── schimba-parola.php        # Schimbare parola
└── README.md                 # Acest fisier

```

---

## CONFIGURARE

### Fisier: `config/database.php`

Acesta este SINGURUL fisier care trebuie OBLIGATORIU editat pentru a face aplicatia sa functioneze.

```php
// OBLIGATORIU - Datele de conectare la MySQL
define('DB_HOST', 'localhost');           // Adresa server MySQL
define('DB_NAME', 'devizo_db');           // Numele bazei de date
define('DB_USER', 'root');                // Utilizator MySQL
define('DB_PASS', '');                    // Parola MySQL

// OBLIGATORIU - URL-ul aplicatiei
define('SITE_URL', 'http://localhost/devizo');  // URL complet

// OPTIONAL - Setari aplicatie
define('DEBUG_MODE', 1);                  // 1 = dezvoltare, 0 = productie
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);  // 5 MB
define('SESSION_LIFETIME', 3600 * 24);    // 24 ore
define('PASSWORD_MIN_LENGTH', 6);         // Lungime minima parola
```

### Setari Importante:

1. **DEBUG_MODE**:
   - Setati la `1` in timpul dezvoltarii/testarii
   - Setati la `0` cand aplicatia merge in PRODUCTIE (OBLIGATORIU pentru securitate)

2. **SITE_URL**:
   - Trebuie sa fie URL-ul EXACT al aplicatiei
   - Include protocol (http:// sau https://)
   - NU pune slash la final

3. **Permisiuni Foldere**:
   ```
   assets/uploads/logos - 775 (pentru a putea upload logo-uri)
   logs/ - 775 (pentru a putea scrie log-uri)
   ```

---

## UTILIZARE

### PENTRU SUPER ADMIN (Proprietarul Aplicatiei)

1. **Management Firme**:
   - Administrare > Firme
   - Adaugati firme noi cu CUI
   - Setati numar maxim utilizatori
   - Configurati data expirare abonament
   - Activati/dezactivati import CSV

2. **Management Utilizatori**:
   - Creati utilizatorul Master pentru fiecare firma
   - Reseteaza parole la nevoie

3. **Monitorizare Abonamente**:
   - Dashboard-ul arata firmele cu abonament aproape de expirare
   - Prelungiti abonamentele la plata

### PENTRU MASTER FIRMA

1. **Prima Configurare**:
   - Setari Firma > Upload Logo
   - Setati culoarea predominanta
   - Configurati procent manopera implicit

2. **Management Utilizatori**:
   - Utilizatori > Adauga Utilizator
   - Puteti adauga maxim numarul permis de Super Admin

3. **Gestionare Nomenclatoare**:
   - Adaugati articole (manual sau import CSV daca e activat)
   - Adaugati parteneri (cu CUI pentru extragere date ANAF)
   - Configurati unitati de masura custom

### PENTRU UTILIZATOR FIRMA

1. **Creare Deviz**:
   - Devize > Deviz Nou
   - Selectati partener
   - Adaugati articole (manual sau din nomenclator)
   - Salvati devizul

2. **Export si Partajare**:
   - Genereaza PDF cu logo si culori firma
   - Obtine link partajare (ramane acelasi la modificari)

3. **Filtrare si Cautare**:
   - Filtrati dupa status, client, perioada
   - Vizualizati istoric complet

---

## CONTURI DEMO

Aplicatia vine cu 3 conturi demo preconfigurate pentru testare:

### 1. SUPER ADMIN
```
Email: admin@devizo.ro
Parola: admin123
Rol: Administrator principal
Poate: Gestioneaza toate firmele si utilizatorii
```

### 2. MASTER FIRMA
```
Email: doru@zaninstal.ro
Parola: demo123
Rol: Administrator firma "ENERGY ZAN INSTAL SRL"
Poate: Gestioneaza utilizatori, setari firma, devize
```

### 3. UTILIZATOR FIRMA
```
Email: user@zaninstal.ro
Parola: user123
Rol: Utilizator normal
Poate: Creaza devize, gestioneaza articole si parteneri
```

**IMPORTANT**: Schimbati TOATE parolele demo imediat dupa instalare!

---

## TROUBLESHOOTING

### Eroare: "Eroare conexiune baza de date"

**Cauze posibile:**
1. Datele de conectare din `config/database.php` sunt incorecte
2. Baza de date nu exista
3. Utilizatorul MySQL nu are permisiuni

**Solutii:**
1. Verificati:
   ```php
   define('DB_HOST', 'localhost');  // Corect?
   define('DB_NAME', 'devizo_db');  // Exista baza de date?
   define('DB_USER', 'devizo_user');// Utilizator corect?
   define('DB_PASS', 'parola');     // Parola corecta?
   ```
2. Testati conexiunea MySQL prin phpMyAdmin cu aceleasi date
3. Verificati ca utilizatorul are permisiuni pe baza de date

### Eroare: "Table 'devizo_db.utilizatori' doesn't exist"

**Cauza:**
Fisierul `database.sql` nu a fost importat corect.

**Solutie:**
1. Accesati phpMyAdmin
2. Selectati baza de date
3. Tab "Import"
4. Selectati fisierul `database.sql`
5. Click "Go"

### Eroare: "Cannot upload logo" / "Permission denied"

**Cauza:**
Folder-ul `assets/uploads` nu are permisiuni de scriere.

**Solutie:**
1. Prin cPanel File Manager sau FTP
2. Click dreapta pe folder `assets/uploads`
3. "Change Permissions" sau "Properties"
4. Setati la `775` (rwxrwxr-x)
5. Bifati "Recurse into subdirectories"

### Pagina alba / Eroare 500

**Cauze posibile:**
1. Eroare de sintaxa PHP
2. Versiune PHP prea veche
3. Extensii PHP lipsa

**Solutii:**
1. Verificati versiunea PHP (minim 7.4):
   ```php
   <?php phpinfo(); ?>
   ```
2. Activati afisarea erorilor temporar:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```
3. Verificati log-urile server-ului (error_log)

### Link-urile nu functioneaza / CSS nu se incarca

**Cauza:**
`SITE_URL` din config este incorect.

**Solutie:**
Editati `config/database.php`:
```php
// Trebuie sa fie URL-ul EXACT
define('SITE_URL', 'https://domeniul.ro/devizo');
// NU puneti slash la final!
```

### Sesiunea expira prea repede

**Solutie:**
Editati `config/database.php`:
```php
define('SESSION_LIFETIME', 3600 * 24);  // 24 ore in secunde
```

### API ANAF nu functioneaza

**Cauze posibile:**
1. Extensia `php-curl` nu este instalata
2. Firewall blocheaza conexiunile externe
3. Server-ul nu are acces internet

**Solutii:**
1. Verificati daca CURL este instalat:
   ```php
   <?php var_dump(function_exists('curl_init')); ?>
   ```
2. Contactati hosting-ul pentru activare CURL
3. Folositi introducerea manuala a datelor daca ANAF nu este disponibil

---

## DEZVOLTARE ULTERIOARA

Aplicatia a fost construita modular pentru a permite dezvoltari viitoare usoare.

### Unde sa Adaugati Functionalitati Noi:

1. **Tabele noi in baza de date**:
   - Creati fisier SQL separat in folder `database/migrations/`
   - Rulati manual in phpMyAdmin

2. **API endpoints noi**:
   - Creati fisiere noi in folder `api/`
   - Urmati structura existenta (`api/articole.php` ca model)

3. **Pagini noi**:
   - Creati fisier PHP in root
   - Includeti `includes/header.php` la inceput
   - Includeti `includes/footer.php` la final

4. **Functii noi**:
   - Adaugati in `includes/functions.php`
   - Grupati logic pe categorii

5. **Stiluri CSS custom**:
   - Adaugati in `assets/css/style.css`

6. **JavaScript custom**:
   - Adaugati in `assets/js/main.js` sau creati fisiere separate

### Exemple de Functionalitati ce Pot Fi Adaugate:

- [ ] Rapoarte financiare detaliate
- [ ] Export Excel pentru devize
- [ ] Notificari email automate
- [ ] Integrare sisteme plati online (Stripe, PayPal)
- [ ] API REST pentru integrari externe
- [ ] Aplicatie mobila (folosind API-ul)
- [ ] Chat suport integrat
- [ ] Facturare automata
- [ ] Semnatura electronica devize
- [ ] Versiuni multiple deviz (istoricul modificarilor)

---

## SECURITATE

### Masuri de Securitate Implementate:

1. **Autentificare Sigura**:
   - Parole hash-uite cu bcrypt
   - Validare input la toate formularele
   - Protectie impotriva SQL Injection (PDO prepared statements)
   - Protectie XSS (htmlspecialchars)

2. **Gestionare Sesiuni**:
   - Sesiuni cu timeout configurbil
   - Regenerare ID sesiune la login
   - Validare IP pentru sesiuni (optional)

3. **Upload Fisiere**:
   - Validare tip MIME
   - Limitare dimensiune
   - Nume fisiere unice (previne suprascrierea)

4. **Baza de Date**:
   - Utilizator MySQL cu permisiuni limitate
   - Nu foloseste cont root
   - Prepared statements pentru toate query-urile

### Recomandari Suplimentare:

1. **SSL/HTTPS**:
   ```
   OBLIGATORIU in productie!
   Obtineti certificat SSL gratuit de la Let's Encrypt
   ```

2. **Backup-uri**:
   ```
   Configurati backup-uri automate zilnice:
   - Baza de date (mysqldump)
   - Fisiere (upload-uri, log-uri)
   ```

3. **Actualizari**:
   ```
   Verificati lunar pentru:
   - Actualizari PHP
   - Actualizari MySQL
   - Patch-uri securitate server
   ```

4. **Monitorizare**:
   ```
   Verificati regulat:
   - Log-urile din folder logs/
   - Incercari esuate de login
   - Activitati suspecte
   ```

5. **Parole**:
   ```
   - Schimbati TOATE parolele demo
   - Folositi parole complexe (minim 12 caractere)
   - Activati autentificare 2FA (dezvoltare viitoare)
   ```

---

## DESCRIEREA TEHNICA (pentru Programatori)

### Tehnologii Utilizate:

- **Backend**: PHP 7.4+ (PHP vanilla, fara framework)
- **Baza de Date**: MySQL 5.7+ / MariaDB 10.3+
- **Frontend**: HTML5, CSS3, JavaScript (vanilla)
- **Librarii externe**:
  - Font Awesome 6.0 (iconite)
  - jsPDF + autoTable (generare PDF)

### Arhitectura:

```
┌─────────────────────────────────────────────┐
│         CLIENT (Browser)                     │
│  HTML + CSS + JavaScript                     │
└──────────────┬──────────────────────────────┘
               │
               │ HTTP/HTTPS
               │
┌──────────────▼──────────────────────────────┐
│         SERVER (Apache/Nginx + PHP)          │
│                                              │
│  ┌────────────────────────────────────────┐ │
│  │  Presentation Layer                    │ │
│  │  (index.php, devize.php, etc.)        │ │
│  └────────────┬───────────────────────────┘ │
│               │                              │
│  ┌────────────▼───────────────────────────┐ │
│  │  Business Logic                        │ │
│  │  (includes/functions.php, auth.php)   │ │
│  └────────────┬───────────────────────────┘ │
│               │                              │
│  ┌────────────▼───────────────────────────┐ │
│  │  API Layer                             │ │
│  │  (api/articole.php, devize.php, etc.) │ │
│  └────────────┬───────────────────────────┘ │
│               │                              │
│  ┌────────────▼───────────────────────────┐ │
│  │  Data Access Layer                     │ │
│  │  (config/database.php, PDO)           │ │
│  └────────────┬───────────────────────────┘ │
└───────────────┼──────────────────────────────┘
                │
┌───────────────▼──────────────────────────────┐
│         DATABASE (MySQL)                      │
│  Tables: firme, utilizatori, devize,         │
│          articole, parteneri, etc.           │
└───────────────────────────────────────────────┘
```

### Pattern-uri Utilizate:

1. **Singleton** - Pentru conexiunea la baza de date
2. **MVC Simplificat** - Separare logica, prezentare, date
3. **RESTful API** - Pentru operatiuni CRUD
4. **Template Include** - Header/Footer comune

### Fluxul de Date:

```
User Action → Form Submit → PHP Processing → Database Query → Response → UI Update
```

---

## SUPORT SI CONTACT

Pentru intrebari, probleme sau sugestii:

1. Consultati mai intai sectiunea **Troubleshooting**
2. Verificati log-urile aplicatiei (`logs/app.log`)
3. Contactati dezvoltatorul cu detalii despre:
   - Versiune PHP si MySQL
   - Mesaj eroare exact
   - Pasi pentru reproducerea problemei
   - Screenshot-uri (daca e cazul)

---

## LICENTA

Aplicatie dezvoltata custom pentru monetizare.
Toate drepturile rezervate.

---

## CHANGELOG

### Versiunea 1.0.0 (22 Octombrie 2025)
- Prima versiune stabila
- Sistem multi-tenant complet functional
- Management devize, articole, parteneri
- Cereri oferta furnizori
- Export PDF personalizat
- Link-uri partajare devize
- Integrare ANAF (optional)
- Management abonamente
- 3 niveluri utilizatori (Super Admin, Master, Utilizator)

---

## MULTUMIRI

Multumim pentru utilizarea DEVIZO!

Pentru feedback si sugestii, va rugam sa ne contactati.

---

**DEVIZO - Aplicatie Profesionala de Gestiune Devize**
*Versiunea 1.0.0 - Octombrie 2025*
