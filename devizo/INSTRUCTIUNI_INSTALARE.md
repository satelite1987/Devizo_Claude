# DEVIZO - Instructiuni Complete de Instalare

## 📋 CUPRINS
1. [Informatii Generale](#informatii-generale)
2. [Pasii de Instalare](#pasii-de-instalare)
3. [Rezolvarea Problemei cu Parola](#rezolvarea-problemei-cu-parola)
4. [Utilizatori Demo](#utilizatori-demo)
5. [Securizare Finala](#securizare-finala)
6. [Verificari](#verificari)
7. [Probleme Frecvente](#probleme-frecvente)

---

## 📌 INFORMATII GENERALE

**Domeniu:** www.devizo.ro
**Baza de Date:** devizo_db
**Utilizator DB:** devizo_user
**Parola DB:** Satelite1987!

**Aplicatie:** DEVIZO - Sistem de Management Devize si Oferte
**Versiune:** 1.0
**Data:** 23 Octombrie 2025

---

## 🚀 PASII DE INSTALARE

### PAS 1: PREGATIRE BAZA DE DATE

#### 1.1. Acceseaza cPanel
- Intra in contul tau de hosting la www.devizo.ro
- Acceseaza cPanel (de obicei la https://www.devizo.ro:2083)

#### 1.2. Sterge toate tabelele existente (daca exista)
- Du-te la **phpMyAdmin**
- Selecteaza baza de date **devizo_db** din lista din stanga
- Daca exista tabele, selecteaza-le pe toate si sterge-le (Drop)
- Confirma stergerea

#### 1.3. Importa baza de date
- In phpMyAdmin, cu baza **devizo_db** selectata
- Click pe tab-ul **Import**
- Click **Choose File** si selecteaza fisierul **database.sql** din arhiva
- Scroll down si click **Go** (sau **Import**)
- Asteapta confirmarea: "Import has been successfully finished"

**IMPORTANT:** Fisierul database.sql este deja configurat pentru shared hosting (fara DROP DATABASE / CREATE DATABASE).

---

### PAS 2: UPLOAD FISIERE PE SERVER

#### 2.1. Acceseaza File Manager sau FTP
Ai doua optiuni:

**Optiunea A - File Manager (recomandat pentru incepatori):**
- In cPanel, deschide **File Manager**
- Navigheaza la directorul **public_html**
- Sterge tot continutul existent (daca exista)

**Optiunea B - FTP (pentru utilizatori avansati):**
- Foloseste FileZilla sau alt client FTP
- Conecteaza-te la server cu credentialele FTP
- Navigheaza la directorul **public_html**

#### 2.2. Upload arhiva
- Upload arhiva **DEVIZO_COMPLETE_v1.0.zip** in **public_html**
- Click dreapta pe arhiva si selecteaza **Extract** (File Manager)
- SAU dezarhiveaza local si upload toate fisierele

#### 2.3. Verifica structura de fisiere

Dupa dezarhivare, in **public_html** trebuie sa ai:

```
public_html/
├── index.php
├── login.php
├── logout.php
├── database.sql
├── .htaccess
├── README.md
├── INSTALL_QUICK.txt
├── TECHNICAL_NOTES.md
├── DEPLOYMENT_DEVIZO.RO.txt
├── verificare_db.php
├── resetare_parole.php
├── creare_utilizatori_demo.php
├── config/
│   └── database.php
├── includes/
│   ├── auth.php
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── uploads/
│       └── logos/
├── api/
├── admin/
├── pdf/
└── logs/
```

#### 2.4. Seteaza permisiuni
Asigura-te ca urmatoarele directoare au permisiuni de scriere (755 sau 775):
- `assets/uploads/`
- `assets/uploads/logos/`
- `pdf/`
- `logs/`

**In File Manager:**
- Click dreapta pe folder
- Selecteaza **Permissions** sau **Change Permissions**
- Seteaza la **755** (rwxr-xr-x)

---

### PAS 3: REZOLVAREA PROBLEMEI CU PAROLA

Acest pas este **ESENTIAL** pentru ca login-ul sa functioneze!

#### 3.1. Ruleaza scriptul de resetare parole

**In browser, acceseaza:**
```
https://www.devizo.ro/resetare_parole.php
```

#### 3.2. Verifica rezultatul

Dupa ce scriptul se executa, vei vedea un raport detaliat:

✅ **Rezultat Asteptat:**
```
RESETARE PAROLE DEVIZO
======================

Actualizare Utilizatori:
✅ admin@devizo.ro → Parola actualizata cu succes!
✅ doru@zaninstal.ro → Parola actualizata cu succes!
✅ user@zaninstal.ro → Parola actualizata cu succes!

Verificare Hash-uri:
✅ admin@devizo.ro → Hash VALID! password_verify('admin123') = SUCCESS
✅ doru@zaninstal.ro → Hash VALID! password_verify('demo123') = SUCCESS
✅ user@zaninstal.ro → Hash VALID! password_verify('user123') = SUCCESS

SUCCES COMPLET!
Toate parolele au fost actualizate si verificate!
```

❌ **Daca vezi erori:**
- Verifica ca baza de date este corect configurata
- Asigura-te ca tabelul `utilizatori` exista si contine datele
- Contacteaza-ma pentru suport

---

### PAS 4: TESTARE LOGIN

#### 4.1. Acceseaza pagina de login
```
https://www.devizo.ro/login.php
```

#### 4.2. Testeaza fiecare cont demo

**Cont 1 - Super Administrator:**
- Email: `admin@devizo.ro`
- Parola: `admin123`
- Rol: Super Admin (gestioneaza toate firmele)

**Cont 2 - Master Firma:**
- Email: `doru@zaninstal.ro`
- Parola: `demo123`
- Rol: Master Firma ZAN INSTAL (poate crea utilizatori)

**Cont 3 - Utilizator Firma:**
- Email: `user@zaninstal.ro`
- Parola: `user123`
- Rol: Utilizator simplu ZAN INSTAL

#### 4.3. Verifica dashboard-ul
Dupa login, vei fi redirectionat la:
```
https://www.devizo.ro/index.php
```

Dashboard-ul trebuie sa afiseze:
- Statistici (Devize Totale, Articole in Inventar, etc.)
- Meniu de navigare functional
- Informatii despre utilizatorul logat

---

## 🔒 PAS 5: SECURIZARE FINALA

**OBLIGATORIU** dupa ce login-ul functioneaza!

### 5.1. Sterge fisierele de diagnostic

Aceste fisiere contin informatii sensibile si trebuie STERSE imediat:

**Fisiere de sters:**
- `verificare_db.php`
- `resetare_parole.php`
- `creare_utilizatori_demo.php`
- `database.sql` (fisierul SQL nu mai este necesar dupa import)

**Cum le stergi:**
- In File Manager: Selecteaza fiecare fisier → Delete
- SAU prin FTP: Selecteaza si sterge

### 5.2. Dezactiveaza modul DEBUG

**Editeaza fisierul:** `config/database.php`

**Gaseste linia:**
```php
define('DEBUG_MODE', 1);
```

**Schimb-o in:**
```php
define('DEBUG_MODE', 0);
```

**Salveaza fisierul.**

### 5.3. Verifica fisierul .htaccess

Asigura-te ca `.htaccess` este prezent in `public_html/` si contine:
- Protectie pentru fisiere sensibile
- Redirectionare HTTPS (daca ai SSL)
- Setari PHP recomandate

### 5.4. Schimba parolele utilizatorilor demo

**IMPORTANT pentru productie!**

Dupa testare, schimba toate parolele demo:
1. Logheaza-te cu fiecare cont
2. Mergi la Profil → Schimba Parola
3. Seteaza o parola STRONG

SAU editeaza direct in baza de date (phpMyAdmin):
```sql
UPDATE utilizatori
SET parola = '$2y$10$HASH_NOU'
WHERE email = 'admin@devizo.ro';
```

Genereaza hash-uri noi cu:
```php
<?php echo password_hash('parola_noua_strong', PASSWORD_DEFAULT); ?>
```

---

## ✅ VERIFICARI FINALE

### Checklist instalare completa:

- [ ] Baza de date importata cu succes (18 tabele create)
- [ ] Toate fisierele uploadate in `public_html/`
- [ ] Permisiuni setat corect (755 pentru foldere upload)
- [ ] Script `resetare_parole.php` executat cu succes
- [ ] Login functioneaza pentru toate conturile demo
- [ ] Dashboard-ul se afiseaza corect
- [ ] Fisierele de diagnostic STERSE
- [ ] DEBUG_MODE setat pe 0
- [ ] Parolele schimbate (pentru productie)

### Link-uri de verificat:

1. **Login:** https://www.devizo.ro/login.php
2. **Dashboard:** https://www.devizo.ro/index.php (dupa login)
3. **Assets CSS:** https://www.devizo.ro/assets/css/style.css
4. **Assets JS:** https://www.devizo.ro/assets/js/main.js

---

## ⚠️ PROBLEME FRECVENTE

### Problem 1: "Headers already sent"
**Cauza:** Fisierele PHP au BOM sau spatii la inceputul/sfarsitul fisierului
**Solutie:** Fisierele din arhiva sunt deja corectate (UTF-8 without BOM)

### Problem 2: Eroare la import database.sql
**Cauza:** Permisiuni insuficiente pentru DROP/CREATE DATABASE
**Solutie:** Fisierul database.sql este deja configurat fara aceste comenzi

### Problem 3: Login nu functioneaza
**Cauza:** Hash-urile de parola sunt incorecte
**Solutie:** Ruleaza `resetare_parole.php` conform instructiunilor

### Problem 4: CSS/JS nu se incarca
**Cauza:** Cai gresite sau .htaccess lipseste
**Solutie:** Verifica ca `.htaccess` exista si ca permisiunile sunt corecte

### Problem 5: "Database connection failed"
**Cauza:** Credentiale gresite in `config/database.php`
**Solutie:** Verifica ca fisierul contine:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'devizo_db');
define('DB_USER', 'devizo_user');
define('DB_PASS', 'Satelite1987!');
```

### Problem 6: Eroare 500 Internal Server Error
**Cauza:** Eroare PHP sau permisiuni gresite
**Solutie:** Activeaza DEBUG_MODE=1 si verifica `logs/error.log`

---

## 📞 SUPORT

Daca intampini probleme:

1. **Activeaza DEBUG_MODE:**
   - Editeaza `config/database.php`
   - Seteaza `define('DEBUG_MODE', 1);`
   - Verifica erorile afisate

2. **Verifica logs:**
   - `logs/error.log` - erori aplicatie
   - `logs/access.log` - cereri HTTP
   - cPanel Error Log - erori server

3. **Contacteaza-ma:**
   - Trimite-mi screenshot-uri cu erorile
   - Include continutul fisierului `logs/error.log`
   - Descrie exact pasii urmati

---

## 📚 RESURSE ADITIONALE

**Documentatie completa:**
- `README.md` - Documentatie generala
- `TECHNICAL_NOTES.md` - Documentatie tehnica pentru dezvoltatori
- `INSTALL_QUICK.txt` - Ghid rapid de instalare (engleza)
- `DEPLOYMENT_DEVIZO.RO.txt` - Instructiuni specifice pentru devizo.ro

**Structura Baza de Date:**
- 18 tabele principale
- 5 trigger-uri automate pentru calcule
- 2 stored procedures pentru numerotare devize
- 3 view-uri pentru rapoarte

**Functionalitati Principale:**
- Gestionare devize/oferte cu numerotare automata
- Baza de articole cu preturi materiale si manopera
- Parteneri (clienti)
- Cereri oferta catre furnizori
- Filtrare dupa status, client, perioada
- Export PDF cu logo firma si culori personalizate
- Link-uri partajabile pentru devize
- Integrare ANAF pentru preluare date firme dupa CUI
- Import CSV pentru articole
- Notificari email SMTP
- Sistem multi-tenant cu abonamente
- 3 niveluri utilizatori (Super Admin, Master Firma, Utilizator)

---

## ✨ VERSIUNE SI DATA

**Versiune:** 1.0.0
**Data Release:** 23 Octombrie 2025
**Autor:** Claude (Anthropic)
**Client:** Satelite1987
**Domeniu:** www.devizo.ro

---

**SUCCES CU INSTALAREA!** 🎉

Dupa ce toate verificarile sunt OK, aplicatia este GATA pentru utilizare in productie!
