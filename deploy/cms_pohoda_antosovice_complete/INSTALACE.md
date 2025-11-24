# CMS Pohoda Antošovice - Instalační příručka

## Datum vydání: 24. listopadu 2025
## Verze: 2.1

---

## 🎯 Co obsahuje tento balíček?

✅ **Kompletní CMS systém** pro web antosovice.endora.site  
✅ **Administrace** - plně funkční admin panel  
✅ **Všechny moduly** - stránky, příspěvky, události, galerie  
✅ **Databáze** - kompletní SQL dump s daty  
✅ **Assety** - CSS, JS, obrázky včetně loga  
✅ **Nahrávací adresář** - uploads/  

---

## 📋 Obsah balíčku

```
cms_pohoda_antosovice_complete/
├── admin/                      # Administrační panel
│   ├── login.php              # Přihlášení
│   ├── index.php              # Dashboard
│   ├── pages.php              # Správa stránek
│   ├── posts.php              # Správa příspěvků
│   ├── events.php             # Správa událostí
│   ├── gallery.php            # Správa galerie
│   ├── profile.php            # Profil uživatele
│   ├── settings.php           # Nastavení webu
│   ├── upload.php             # Nahrávání souborů
│   └── assets/                # Admin CSS
├── assets/                     # CSS, JS
│   ├── css/
│   └── js/
├── images/                     # Obrázky a logo
│   └── sno-logo.png
├── includes/                   # PHP funkce
│   ├── functions.php          # Hlavní funkce
│   └── menu.php               # Menu generátor
├── uploads/                    # Nahrané soubory
├── index.php                   # Hlavní stránka
├── page_new.php                # Dynamické stránky
├── post_new.php                # Detail příspěvku
├── posts.php                   # Seznam příspěvků
├── events.php                  # Seznam událostí
├── event.php                   # Detail události
├── gallery.php                 # Fotogalerie
├── 404.php                     # Chybová stránka
├── config.php                  # 🔴 KONFIGURACE
├── database_complete.sql       # 🔴 DATABÁZE
├── INSTALACE.md                # Tento soubor
├── LICENSE.txt
└── README.md
```

---

## 🚀 INSTALACE KROK ZA KROKEM

### KROK 1: Požadavky serveru

**Minimální:**
- PHP 7.4 nebo vyšší
- MySQL 5.7 nebo vyšší
- Apache s mod_rewrite NEBO Nginx
- 50 MB volného místa

**Doporučené:**
- PHP 8.0+
- MySQL 8.0+
- SSL certifikát (HTTPS)
- 100+ MB volného místa

---

### KROK 2: Příprava databáze

1. **Vytvořte novou databázi** v cPanel / phpMyAdmin:
   ```sql
   CREATE DATABASE moje_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Vytvořte uživatele** (nebo použijte existujícího):
   ```sql
   CREATE USER 'cms_user'@'localhost' IDENTIFIED BY 'silne_heslo';
   GRANT ALL PRIVILEGES ON moje_cms.* TO 'cms_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. **Importujte databázi:**
   
   **Příkazová řádka:**
   ```bash
   mysql -u cms_user -p moje_cms < database_complete.sql
   ```
   
   **phpMyAdmin:**
   - Vyberte databázi `moje_cms`
   - Klikněte na záložku "Import"
   - Vyberte soubor `database_complete.sql`
   - Klikněte "Provést"

---

### KROK 3: Nahrání souborů

1. **Připojte se na server:**
   - FTP (FileZilla, Cyberduck)
   - SFTP
   - cPanel File Manager

2. **Nahrajte VŠECHNY soubory a složky** do kořenového adresáře:
   ```
   /public_html/           (většina hostingů)
   /www/                   (některé hostingy)
   /httpdocs/              (Plesk)
   /html/                  (některé VPS)
   ```

3. **Nastavte oprávnění:**
   ```bash
   # Složky
   chmod 755 admin/
   chmod 755 assets/
   chmod 755 images/
   chmod 755 includes/
   chmod 777 uploads/          # Zapisovatelná pro nahrávání!
   
   # Soubory
   chmod 644 *.php
   chmod 600 config.php        # Ochrana konfigurace
   ```

---

### KROK 4: Konfigurace připojení k databázi

Upravte soubor **config.php**:

```php
<?php
// Databázové připojení
define('DB_HOST', 'localhost');           // Host databáze
define('DB_USER', 'cms_user');            // 🔴 ZMĚŇTE na svého uživatele
define('DB_PASS', 'silne_heslo');         // 🔴 ZMĚŇTE na své heslo
define('DB_NAME', 'moje_cms');            // 🔴 ZMĚŇTE na název databáze

// URL nastavení
define('BASE_URL', 'https://antosovice.endora.site');  // 🔴 ZMĚŇTE na vaši URL

// Timezone
date_default_timezone_set('Europe/Prague');

// Error reporting (vypněte v produkci!)
error_reporting(E_ALL);
ini_set('display_errors', 0);  // Nastavte na 0 v produkci!
?>
```

---

### KROK 5: Přihlášení do administrace

1. **Otevřete admin panel:**
   ```
   https://antosovice.endora.site/admin/
   ```

2. **Přihlašovací údaje:**
   - Zkontrolujte v databázi tabulku `users`
   - Výchozí email a heslo najdete v importovaných datech

3. **⚠️ DŮLEŽITÉ - Změňte heslo ihned!**
   - Po prvním přihlášení jděte na: Admin → Profil
   - Změňte heslo na silné
   - Případně změňte i email

---

### KROK 6: Kontrola funkčnosti

Ověřte, že vše funguje:

✅ **Hlavní stránka:** https://antosovice.endora.site/  
✅ **Menu:** Zobrazuje se správně, barva #6f9183  
✅ **Galerie:** Menu položka "Galerie" pod "Pláž pohoda"  
✅ **Admin panel:** https://antosovice.endora.site/admin/  
✅ **Stránky:** Dynamické načítání funguje  
✅ **Události:** Seznam a detail  
✅ **Příspěvky:** Seznam a detail  
✅ **Nahrávání:** Test uploadu v admin/upload.php  

---

## 🎨 Hlavní funkce systému

### 1. Správa stránek (admin/pages.php)
- Vytváření a editace stránek
- Hierarchické menu (stránky a podstránky)
- SEO metadata (title, description)
- Ikony Font Awesome
- Pořadí v menu (menu_order)
- Publikovat/Skrýt stránky

### 2. Příspěvky - Blog (admin/posts.php)
- Vytváření článků
- Kategorie
- Obrázek hlavičky
- Zobrazení na hlavní stránce
- Detail příspěvku

### 3. Události (admin/events.php)
- Kalendář akcí
- Datum a čas konání
- Místo konání
- Cena vstupu
- Barevné kategorie
- Detail události

### 4. Galerie (admin/gallery.php)
- **NOVĚ:** Dynamická správa v admin panelu
- Upload fotek
- Automatické thumbnaily
- Správa alba
- Možnost přidat do menu nebo podmenu

### 5. Nahrávání souborů (admin/upload.php)
- Upload obrázků
- Správa médií
- Automatická URL pro vložení

---

## 🎨 Design a barvy

**Hlavní barva:** `#6f9183` (barva loga SNO)  
**Hover barva:** `#5a7a6b`  
**Logo:** 90px výška, levý horní roh  
**Font:** Segoe UI, Tahoma, Geneva  

### Použité technologie:
- **Frontend:**
  - Bootstrap 5.3.3
  - Font Awesome 6
  - jQuery 3.7.1
  - Vanilla JavaScript
  
- **Backend:**
  - PHP 8.x
  - MySQL 8.x
  - PDO pro databázi

---

## 🔧 Pokročilá konfigurace

### Apache (.htaccess)

Vytvořte soubor `.htaccess` v kořenovém adresáři:

```apache
# Zapnutí mod_rewrite
RewriteEngine On
RewriteBase /

# Redirect na HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Odstranění trailing slashes
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)/$ /$1 [L,R=301]

# Ochrana config.php
<Files config.php>
    Order allow,deny
    Deny from all
</Files>

# Ochrana proti PHP exekuci v uploads
<Directory "uploads">
    php_flag engine off
</Directory>

# Komprese
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>

# Cache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### Nginx

Konfigurace pro Nginx (`/etc/nginx/sites-available/antosovice`):

```nginx
server {
    listen 80;
    server_name antosovice.endora.site;
    root /var/www/antosovice;
    index index.php index.html;

    # Redirect na HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name antosovice.endora.site;
    root /var/www/antosovice;
    index index.php;

    # SSL certifikáty
    ssl_certificate /etc/ssl/certs/antosovice.crt;
    ssl_certificate_key /etc/ssl/private/antosovice.key;

    # PHP
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Ochrana config.php
    location ~ config.php {
        deny all;
    }

    # Ochrana uploads
    location ~* ^/uploads/.*\.php$ {
        deny all;
    }

    # Pretty URLs
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Cache statických souborů
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 365d;
        add_header Cache-Control "public, immutable";
    }
}
```

---

## 🆘 Řešení problémů

### Web nezobrazuje nic / bílá stránka

**Příčina:** Chyba v PHP nebo špatné připojení k DB

**Řešení:**
1. Zkontrolujte `config.php` - správné DB údaje?
2. Zapněte zobrazení chyb dočasně v `config.php`:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```
3. Zkontrolujte error log: `/var/log/apache2/error.log`

### Admin panel - Cannot login

**Příčina:** Problém s databází nebo uživatelskými údaji

**Řešení:**
1. Zkontrolujte, že databáze byla importována
2. Zkontrolujte tabulku `users`:
   ```sql
   SELECT * FROM users;
   ```
3. Reset hesla v databázi:
   ```sql
   UPDATE users SET password = MD5('noveheslo123') WHERE email = 'admin@example.com';
   ```

### Galerie se nezobrazuje v menu

**Příčina:** Stránka není publikovaná nebo chybí v DB

**Řešení:**
1. Zkontrolujte stránku v databázi:
   ```sql
   SELECT * FROM pages WHERE slug = 'fotky-okoli';
   ```
2. Měla by mít `is_published = 1`
3. V admin panelu: Stránky → Galerie → Zkontrolujte nastavení

### Obrázky se nenahrávají

**Příčina:** Špatná oprávnění složky uploads

**Řešení:**
1. Nastavte oprávnění:
   ```bash
   chmod 777 uploads/
   # nebo
   chown www-data:www-data uploads/
   chmod 755 uploads/
   ```
2. Zkontrolujte PHP nastavení v `php.ini`:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   max_execution_time = 300
   ```

### 404 stránka se nezobrazuje správně

**Příčina:** Chybí mod_rewrite nebo .htaccess

**Řešení:**
1. Ověřte, že je mod_rewrite zapnutý:
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```
2. Zkontrolujte `.htaccess` v kořenovém adresáři
3. Ověřte AllowOverride v Apache config:
   ```apache
   <Directory /var/www/html>
       AllowOverride All
   </Directory>
   ```

### Pomalé načítání stránky

**Příčina:** Chybí optimalizace nebo cache

**Řešení:**
1. Zapněte OPcache v `php.ini`:
   ```ini
   opcache.enable=1
   opcache.memory_consumption=128
   ```
2. Použijte CDN pro Bootstrap a Font Awesome
3. Optimalizujte obrázky (TinyPNG, ImageOptim)
4. Zapněte GZIP kompresi v .htaccess

---

## 🔒 Bezpečnostní doporučení

### Po instalaci VŽDY:

1. **Změňte výchozí heslo admina**
2. **Nastavte config.php jako read-only:**
   ```bash
   chmod 600 config.php
   ```
3. **Vypněte zobrazení chyb v produkci:**
   ```php
   ini_set('display_errors', 0);
   ```
4. **Použijte HTTPS** (SSL certifikát)
5. **Chraňte admin panel** - můžete přidat .htaccess do /admin/:
   ```apache
   AuthType Basic
   AuthName "Admin Area"
   AuthUserFile /path/to/.htpasswd
   Require valid-user
   ```
6. **Pravidelně zálohujte** databázi a soubory
7. **Aktualizujte PHP** na nejnovější verzi

---

## 📞 Technická podpora

**Web:** https://antosovice.endora.site  
**GitHub:** https://github.com/supervisor-bit/cms-pohoda-antosovice  
**Email:** (doplňte kontakt)

---

## 📝 Poznámky k verzi

**Verze 2.1** - 24. listopadu 2025

**Hlavní změny:**
- ✅ Jednotná barva #6f9183 napříč celým systémem
- ✅ Galerie dynamicky spravovatelná z admin panelu
- ✅ Odstranění hardcoded odkazů
- ✅ Čisté navbar menu bez efektů
- ✅ Optimalizace barev - footery, tlačítka, odkazy
- ✅ **NOVĚ:** Oprava zobrazení kurzívy v textu

**Verze 2.0** - 23. listopadu 2025
- Základní unifikace barev
- Implementace dynamického menu

**Verze 1.0** - Původní verze
- Základní CMS systém

---

## 📜 Licence

Viz soubor `LICENSE.txt`

---

**🎉 Přejeme úspěšnou instalaci!**

Pro jakékoliv dotazy se neváhejte obrátit na podporu.
