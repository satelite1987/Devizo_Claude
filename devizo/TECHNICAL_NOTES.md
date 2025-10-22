# DEVIZO - Note Tehnice pentru Dezvoltatori

## Scopul acestui document

Acest document explica deciziile tehnice luate in timpul dezvoltarii aplicatiei DEVIZO si ofera context pentru dezvoltatorii care vor prelua/extinde proiectul.

---

## Arhitectura Aleasa

### De ce PHP Vanilla (fara framework)?

**Decizie:** Am ales PHP vanilla in loc de Laravel/Symfony/CodeIgniter.

**Motivatie:**
1. **Simplitate pentru deployment**: Nu necesita Composer, npm, sau alte dependinte
2. **Performanta**: Mai rapid decat framework-uri grele pentru o aplicatie de aceasta dimensiune
3. **Compatibilitate**: Functioneaza pe orice hosting shared care are PHP + MySQL
4. **Curba de invatare**: Orice programator PHP poate intelege codul
5. **Control complet**: Nu suntem limitati de conventiile unui framework

**Trade-off-uri acceptate:**
- Mai mult cod "boilerplate" (dar organizat modular)
- Nu avem routing sofisticat (dar nu avem nevoie pentru aceasta aplicatie)
- Trebuie sa gestionam manual securitatea (dar am implementat functii helper)

---

## Structura Bazei de Date

### Pattern Multi-Tenant

**Implementare:**
- Toate tabelele principale au `firma_id` ca foreign key
- Izolare date prin WHERE conditii in toate query-urile
- Trigger-e si constrangeri pentru integritate referentiala

**De ce aceasta abordare?**
1. **Simplitate**: O singura baza de date pentru toate firmele
2. **Cost**: Nu necesita infrastructura complexa (vs. baza de date separata per firma)
3. **Maintenance**: Actualizari facile (un singur schema)
4. **Rapoarte**: Usor de generat rapoarte cross-firm pentru Super Admin

**Daca aplicatia creste mult (>1000 firme):**
- Considerati sharding pe baza de `firma_id`
- Sau treceti la baze de date separate per firma (cu migration script)

### Denormalizare Intentionata

**Tabela `devize`:**
- Stocam totalurile calculate (`total_materiale`, `total_manopera`, `total_general`)
- **De ce?** Performanta - evitam recalculare la fiecare afisare
- **Trade-off:** Complexitate in mentinere consistenta (rezolvat prin trigger-e)

**Tabela `deviz_randuri`:**
- Stocam denumirea, UM ca stringuri (nu foreign keys)
- **De ce?** Istoric - daca un articol se modifica, devizul ramane cu datele originale
- **Trade-off:** Duplicate data (acceptabil pentru acest caz)

### Trigger-e vs. Logica Aplicatie

**Am folosit trigger-e pentru:**
- Actualizare automata totaluri deviz
- Generare hash-uri unice pentru link-uri partajare

**De ce trigger-e si nu PHP?**
1. **Garantie:** Totalurile sunt corecte chiar daca se modifica direct in DB
2. **Performanta:** Mai rapid decat sa refacem calculele in PHP
3. **Consistenta:** Nu depinde de logica aplicatiei

**Cand sa NU folositi trigger-e:**
- Logica business complexa (ramane in PHP)
- Validari (mai bine in PHP pentru mesaje custom)

---

## Securitate

### Autentificare

**Pattern ales:** Session-based authentication

**De ce nu JWT/Token-based?**
- Nu e necesar pentru o aplicatie web traditionala
- Sesiunile PHP sunt simple si sigure daca configurate corect
- JWT e mai potrivit pentru API-uri stateless

**Implementare:**
```php
// Parole hash-uite cu bcrypt (PHP password_hash)
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Verificare
if (password_verify($inputPassword, $hashedPassword)) {
    // Success
}
```

**De ce bcrypt?**
- Built-in PHP
- Adaptive (cost factor poate fi marit)
- Industry standard

### SQL Injection

**Prevenire:** PDO Prepared Statements PESTE TOT

```php
// CORECT
$stmt = $db->prepare("SELECT * FROM utilizatori WHERE email = ?");
$stmt->execute([$email]);

// GRESIT (NU FOLOSITI ASA)
$stmt = $db->query("SELECT * FROM utilizatori WHERE email = '$email'");
```

**De ce PDO si nu mysqli?**
- Sintaxa mai curata
- Suport pentru alte DB-uri (PostgreSQL, SQLite) daca e nevoie
- Better error handling

### XSS (Cross-Site Scripting)

**Prevenire:** Functia `clean()` folosita PESTE TOT la output

```php
echo clean($userInput);  // Converteste <script> in &lt;script&gt;
```

**Implementare:**
```php
function clean($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
```

### CSRF (Cross-Site Request Forgery)

**Implementare:** Token-uri CSRF in formulare

```php
// Generare token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Verificare
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Invalid CSRF token');
}
```

**Unde sa adaugati:**
- Toate formularele care modifica date (POST, PUT, DELETE)
- NU e necesar pentru GET requests (read-only)

---

## Gestionarea Fisierelor

### Upload-uri

**Locatie:** `assets/uploads/logos/`

**Implementare:**
```php
// Validare tip MIME (nu extensie!)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $tempPath);

// Nume unic pentru a preveni suprascrieri
$filename = uniqid() . '_' . time() . '.' . $extension;
```

**De ce nume unice?**
- Prevenim suprascrieri accidentale
- Prevenim atacuri (upload fisier cu acelasi nume)
- Permite versioning daca e necesar

**Securitate:**
- Folder `uploads/` NU trebuie sa fie executable
- Validam MIME type, nu doar extensia
- Limitam dimensiunea fisierelor

---

## Performanta

### Caching

**Implementat:**
- Browser caching prin .htaccess
- Rezultate devize (daca se acceseaza des)

**Nu am implementat:**
- Redis/Memcached (overkill pentru dimensiunea aplicatiei)
- Query caching (MySQL face asta automat)

**Daca aplicatia devine lenta:**
1. Adaugati index-uri pe coloanele folosite in WHERE
2. Analizati slow queries cu `EXPLAIN`
3. Considerati caching layer (Redis)

### Indexuri Baza de Date

**Implementate:**
- Pe foreign keys (automat)
- Pe coloane de cautare (`email`, `cui`, `numar_deviz`)
- Pe coloane de filtrare (`status`, `data_deviz`)

**Cum sa adaugati index-uri:**
```sql
CREATE INDEX idx_coloana ON tabela(coloana);
```

**Cand sa adaugati:**
- Daca un query e lent (verificati cu EXPLAIN)
- Pe coloanele folosite in WHERE, JOIN, ORDER BY

**Cand sa NU adaugati:**
- Pe fiecare coloana (incetineste INSERT/UPDATE)
- Pe tabele foarte mici (<1000 randuri)

---

## API Design

### RESTful Endpoints

**Pattern:**
```
api/
├── articole.php    # GET, POST, PUT, DELETE pentru articole
├── parteneri.php   # GET, POST, PUT, DELETE pentru parteneri
├── devize.php      # GET, POST, PUT, DELETE pentru devize
└── furnizori.php   # Operatiuni cereri oferta
```

**Structura raspuns:**
```json
{
    "success": true,
    "data": { ... },
    "message": "Operatiune reusita"
}
```

**De ce aceasta structura?**
- Consistenta in toata aplicatia
- Usor de parsat in JavaScript
- Permite erori si succese cu aceeasi structura

### HTTP Methods

**Folosim:**
- GET pentru citire
- POST pentru creare
- PUT pentru update (ar trebui)
- DELETE pentru stergere (ar trebui)

**In realitate (din cauza limitarilor PHP simple):**
- POST cu parametru `action=update` pentru UPDATE
- POST cu parametru `action=delete` pentru DELETE

**De ce?**
- Mai simplu fara routing complex
- Functioneaza cu formulare HTML standard
- Majoritatea browserelor suporta doar GET/POST in formulare

---

## Frontend

### De ce nu framework JavaScript?

**Nu am folosit React/Vue/Angular**

**Motivatie:**
1. **Simplicitate:** Vanilla JS e suficient pentru aceasta complexitate
2. **Performanta:** Pagini se incarca instant
3. **Compatibilitate:** Functioneaza pe browsere mai vechi
4. **Learning curve:** Oricine stie JS poate modifica

**Cand sa considerati un framework:**
- Daca interfata devine foarte dinamica (SPA)
- Daca aveti nevoie de state management complex
- Daca echipa e familiarizata cu React/Vue

### Librarii Externe

**Font Awesome:** Pentru icoane
- De ce? Gratuit, popular, multe icoane
- Alternativa: SVG icons custom

**jsPDF + autoTable:** Pentru generare PDF
- De ce? Functioneaza client-side (nu necesita server processing)
- Alternativa: TCPDF/mPDF server-side (mai flexibile dar mai grele)

---

## Integrare ANAF

### API ANAF

**Endpoint folosit:**
```
https://webservicesp.anaf.ro/PlatitorTvaRest/api/v8/ws/tva
```

**Implementare:**
```php
function getInfoFromANAF($cui) {
    // Request POST cu CURL
    // Parse JSON response
    // Return date firma
}
```

**Limitari cunoscute:**
1. API-ul ANAF poate fi instabil (down uneori)
2. Rate limiting (nu specificat clar de ANAF)
3. Nu returneaza toate datele (lipsa telefon, email)

**Best practices:**
- Timeout la 10 secunde (nu blocam aplicatia)
- Fallback la introducere manuala daca API-ul pica
- Cache rezultate (optional) pentru a reduce requesturi

---

## Monetizare

### Sistem Abonamente

**Implementare:**
- `abonament_activ` (boolean)
- `data_expirare_abonament` (date)
- Verificare la fiecare login

**Fluxul:**
1. Super Admin seteaza data expirare
2. La login, verificam daca `data_expirare > NOW()`
3. Daca nu, blocam accesul

**Nu am implementat:**
- Plati automate (necesita integrare Stripe/PayPal)
- Renewal automat
- Notificari email

**Cum sa adaugati plati:**
1. Creati tabel `tranzactii`
2. Integrati Stripe SDK
3. La plata reusita, update `data_expirare_abonament`

### Limita Utilizatori

**Implementare:**
```php
function checkUserLimit($firmaId) {
    // Numara utilizatori activi
    // Compara cu max_utilizatori
    // Return true/false
}
```

**Verificare:**
- La creare utilizator nou
- NU la login (altfel utilizatori existenti ar fi blocati)

---

## Dezvoltare Viitoare

### Cum sa Adaugati Functionalitati

#### 1. Adaugare Tabel Nou

```sql
CREATE TABLE nume_tabel (
    id INT PRIMARY KEY AUTO_INCREMENT,
    firma_id INT NOT NULL,
    -- alte coloane
    FOREIGN KEY (firma_id) REFERENCES firme(id) ON DELETE CASCADE
);
```

#### 2. Adaugare Pagina Noua

```php
<?php
$pageTitle = 'Titlu Pagina';
require_once __DIR__ . '/includes/header.php';
requireLogin(); // Forteaza autentificare

// Logica pagina

require_once __DIR__ . '/includes/footer.php';
?>
```

#### 3. Adaugare API Endpoint

```php
// api/nume_endpoint.php
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Logica GET
        break;
    case 'POST':
        // Logica POST
        break;
}

jsonResponse($success, $data, $message);
?>
```

#### 4. Adaugare Functie Helper

```php
// includes/functions.php

/**
 * Descriere functie
 *
 * @param type $param Descriere parametru
 * @return type Descriere return
 */
function numeFunctie($param) {
    // Implementare
}
```

---

## Testing

### Nu am implementat Unit Tests

**De ce?**
- Aplicatie relativ simpla
- Focus pe delivery rapid
- Testing manual e suficient initial

**Daca vreti sa adaugati teste:**
1. PHPUnit pentru backend
2. Jest pentru JavaScript
3. Selenium pentru teste end-to-end

**Ce sa testati prioritar:**
1. Autentificare (login/logout)
2. Calcule devize (totaluri)
3. Securitate (SQL injection, XSS)

---

## Debugging

### Log-uri

**Implementat:**
```php
logError("Mesaj eroare", "tip");
// Scrie in logs/app.log
```

**Logam:**
- Erori de conexiune DB
- Tentative de login esuate
- Exceptii PHP

**NU logam:**
- Actiuni normale (ar umfla fisierul)
- Date sensibile (parole, carduri)

### Debug Mode

**config/database.php:**
```php
define('DEBUG_MODE', 1); // 1 = afiseaza erori, 0 = ascunde
```

**IMPORTANT:**
- Setati la 1 in timpul dezvoltarii
- Setati la 0 in productie (securitate!)

---

## Best Practices pentru Colaboratori

### Coding Style

1. **Indentare:** 4 spatii (nu tabs)
2. **Brackets:** Same line
3. **Nume variabile:** camelCase pentru JS, snake_case pentru PHP/SQL
4. **Comentarii:** PHPDoc pentru functii

### Git Workflow (daca folositi Git)

```
main/master - Productie (STABLE)
develop - Development branch
feature/nume-feature - Feature branches
```

### Before Commit

1. Testati local
2. Verificati ca nu ati commit-at parole/keys
3. Rulati code linter (optional)

---

## Dependinte si Actualizari

### PHP

**Versiune minima:** 7.4
**Versiune recomandata:** 8.0+

**De ce 7.4 minimum?**
- Typed properties
- Arrow functions
- Null coalescing assignment

### MySQL

**Versiune minima:** 5.7
**Versiune recomandata:** 8.0+

**Features folosite:**
- JSON data type (optional)
- Triggers
- Stored procedures
- Views

### Browsere

**Suportam:**
- Ultimele 2 versiuni ale Chrome, Firefox, Safari, Edge

**Features moderne folosite:**
- Fetch API (nu XMLHttpRequest)
- Flexbox & Grid CSS
- ES6 JavaScript

**Polyfills:**
- Nu am inclus (99% browsere moderne au suport)
- Daca aveti nevoie de IE11: adaugati Babel

---

## Performanta si Scalabilitate

### Limite Curente

**Aplicatia poate gestiona:**
- ~100 firme simultane
- ~1000 utilizatori totali
- ~100.000 devize

**Bottleneck-uri posibile:**
1. DB queries neoptimizate (adaugati index-uri)
2. Upload fisiere mari (configurati PHP limits)
3. Generare PDF pentru devize foarte mari (optimizati)

### Cum sa Scalati

**Orizontal (mai multi utilizatori):**
1. Adaugati cache layer (Redis/Memcached)
2. Optimizati query-uri (index-uri, EXPLAIN)
3. CDN pentru assets statice

**Vertical (mai multe firme):**
1. Sharding baza de date
2. Multiple DB servers
3. Load balancer

---

## Securitate - Checklist Productie

- [ ] `DEBUG_MODE = 0`
- [ ] SSL/HTTPS activat
- [ ] Parole demo schimbate
- [ ] .htaccess configurat corect
- [ ] Backup-uri configurate
- [ ] Log-uri monitorizate
- [ ] Permisiuni fisiere corecte (644/755)
- [ ] PHP versiune actualizata
- [ ] MySQL utilizator cu permisiuni limitate (nu root)

---

## Contact Dezvoltator

Pentru intrebari tehnice despre cod sau arhitectura:
- Consultati comentariile din cod
- Verificati log-urile
- Rulati EXPLAIN pe query-uri lente

---

**Succes in dezvoltare!**

Acest document va fi actualizat pe masura ce aplicatia evolueaza.
