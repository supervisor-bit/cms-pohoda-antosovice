# CMS Pohoda Antošovice - Kompletní instalační balíček

## Datum vydání: 24. listopadu 2025

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
│   ├── login.php
│   ├── index.php
│   ├── pages.php
│   ├── posts.php
│   ├── events.php
│   ├── profile.php
│   ├── settings.php
│   ├── upload.php
│   └── assets/
├── assets/                     # CSS, JS
│   ├── css/
│   └── js/
├── images/                     # Obrázky a logo
│   └── sno-logo.png
├── includes/                   # Funkce a menu
│   ├── functions.php
│   └── menu.php
├── uploads/                    # Nahrané soubory
├── index.php                   # Hlavní stránka
├── page_new.php                # Dynamické stránky
├── post_new.php                # Detail příspěvku
├── posts.php                   # Seznam příspěvků
├── events.php                  # Seznam událostí
├── event.php                   # Detail události
├── gallery.php                 # Fotogalerie
├── 404.php                     # Chybová stránka
├── config.php                  # Konfigurace
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

**Doporučené:**
- PHP 8.0+
- MySQL 8.0+
- SSL certifikát

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
   ```bash
   mysql -u cms_user -p moje_cms < database_complete.sql
   ```
   
   **Nebo v phpMyAdmin:**
   - Vyberte databázi `moje_cms`
   - Import → Vybrat soubor → `database_complete.sql`
   - Spustit

---

### KROK 3: Nahrání souborů

1. **Připojte se na server** (FTP/SFTP/cPanel File Manager)

2. **Nahrajte VŠECHNY soubory a složky** do kořenového adresáře webu:
   ```
   /public_html/
   nebo
   /www/
   nebo
   /httpdocs/
   ```

3. **Nastavte oprávnění:**
   ```bash
   chmod 755 admin/
   chmod 755 assets/
   chmod 755 images/
   chmod 755 includes/
   chmod 777 uploads/          # Zapisovatelný pro nahrávání
   chmod 644 *.php
   chmod 644 config.php
   ```

---

### KROK 4: Konfigurace připojení k databázi

Upravte soubor **config.php**:

```php
<?php
// Databázové připojení
define('DB_HOST', 'localhost');           // Změňte pokud je jiný host
define('DB_USER', 'cms_user');            // 🔴 ZMĚŇTE na svého uživatele
define('DB_PASS', 'silne_heslo');         // 🔴 ZMĚŇTE na své heslo
define('DB_NAME', 'moje_cms');            // 🔴 ZMĚŇTE na název databáze

// URL nastavení
define('BASE_URL', 'https://antosovice.endora.site');  // 🔴 ZMĚŇTE na vaši URL

// Timezone
date_default_timezone_set('Europe/Prague');
?>
```

---

### KROK 5: Přihlášení do administrace

1. **Otevřete admin panel:**
   ```
   https://antosovice.endora.site/admin/
   ```

2. **Přihlašovací údaje** (z databáze):
   - **Email:** `admin@example.com` (nebo dle vaší databáze)
   - **Heslo:** Dle vaší databáze

3. **⚠️ DŮLEŽITÉ - Změňte heslo ihned po přihlášení!**
   - Admin → Profil → Změnit heslo

---

### KROK 6: Kontrola funkčnosti

Ověřte, že funguje:

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

### 1. Správa stránek
- Vytváření a editace stránek
- Hierarchické menu (stránky a podstránky)
- SEO metadata
- Ikony Font Awesome
- Pořadí v menu

### 2. Příspěvky (Blog)
- Vytváření článků
- Kategorie
- Zobrazení na hlavní stránce
- Detail příspěvku

### 3. Události
- Kalendář akcí
- Datum konání
- Místo a cena
- Barevné kategorie
- Detail události

### 4. Galerie
- **NOVĚ:** Dynamická správa v admin panelu
- Upload fotek
- Automatické thumbnail
- Správa alba

### 5. Nahrávání souborů
- Upload obrázků
- Správa médií
- Automatická URL

---

## 🎨 Design a barvy

**Hlavní barva:** `#6f9183` (barva loga SNO)  
**Hover barva:** `#5a7a6b`  
**Logo:** 90px výška, levý horní roh  
**Font:** Bootstrap 5 výchozí  

### Použité technologie:
- Bootstrap 5.3.3
- Font Awesome 6
- jQuery 3.7.1
- PHP 8.x
- MySQL 8.x

---

## 🔧 Pokročilá konfigurace

### Apache (.htaccess)

Pokud potřebujete, vytvořte `.htaccess`:

```apache
# Pretty URLs
RewriteEngine On
RewriteBase /

# Redirect to HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Remove trailing slashes
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)/$ /$1 [L,R=301]

# Protect config
<Files config.php>
    Order allow,deny
    Deny from all
</Files>
```

### Nginx

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
}

location ~ /\.ht {
    deny all;
}
```

---

## 🆘 Řešení problémů

### Web nezobrazuje nic / bílá stránka
- Zkontrolujte `config.php` - správné DB údaje?
- Zkontrolujte error log: `/var/log/apache2/error.log`
- Zapněte zobrazení chyb dočasně:
  ```php
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  ```

### Admin panel - Cannot login
- Zkontrolujte databázi - importovala se správně?
- Zkontrolujte tabulku `users`
- Reset hesla v DB:
  ```sql
  UPDATE users SET password = MD5('noveheslo') WHERE email = 'admin@example.com';
  ```

### Galerie se nezobrazuje
- Zkontrolujte, že existuje stránka se slug 'fotky-okoli':
  ```sql
  SELECT * FROM pages WHERE slug = 'fotky-okoli';
  ```
- Měla by mít `is_published = 1`

### Menu nezobrazuje galerii
- Admin → Stránky → Najděte "Galerie"
- Zkontrolujte "Nadřazená stránka" = "Pláž pohoda"
- Zkontrolujte "Pořadí v menu" = 10

### Obrázky se nenahrávají
- Zkontrolujte oprávnění složky `uploads/`: `chmod 777 uploads/`
- Zkontrolujte PHP nastavení:
  ```php
  upload_max_filesize = 10M
  post_max_size = 10M
  ```

### 404 stránka nefunguje
- Zkontrolujte Apache mod_rewrite
- Zkontrolujte `.htaccess`

---

## 📞 Technická podpora

**Webové stránky:** https://antosovice.endora.site  
**GitHub:** https://github.com/supervisor-bit/cms-pohoda-antosovice  

---

## 📝 Poznámky k verzi

**Verze:** 2.0 (24. listopadu 2025)

**Hlavní změny:**
- ✅ Jednotná barva #6f9183 napříč celým systémem
- ✅ Galerie dynamicky spravovatelná z admin panelu
- ✅ Odstranění hardcoded odkazů
- ✅ Čisté navbar menu bez efektů
- ✅ Optimalizace barev - footery, tlačítka, odkazy

**Předchozí verze:**
- 1.0 - Základní CMS systém

---

## 📜 Licence

Viz soubor `LICENSE.txt`

---

**🎉 Úspěšnou instalaci!**
