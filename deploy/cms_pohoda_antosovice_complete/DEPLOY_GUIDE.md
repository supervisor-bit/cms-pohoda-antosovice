# 🚀 Kompletní Deploy Guide - CMS Pohoda Antošovice

## 📋 Přehled systému

Váš CMS obsahuje:
- ✅ **PHP 7.4+** aplikaci s MySQL databází
- ✅ **Foto galerii** s admin rozhraním  
- ✅ **Kalendář akcí** s kategoriemi
- ✅ **Responzivní design** (Bootstrap 5)
- ✅ **Admin panel** pro správu obsahu
- ✅ **Dynamické menu** a nastavení

---

## 🗃️ Požadavky hostingu

### Minimální požadavky:
- **PHP 7.4+** (ideálně 8.0+)
- **MySQL 5.7+** nebo **MariaDB 10.2+**
- **Apache/Nginx** web server
- **mod_rewrite** (pro .htaccess)
- **GD extension** (pro zpracování obrázků)
- **PDO MySQL extension**

### Doporučené PHP extensions:
```
php-gd
php-mysql
php-mbstring
php-curl
php-zip
php-fileinfo
```

---

## 📁 Příprava souborů

### 1. Export z lokálního prostředí
```bash
# Vytvoření archivu bez git a temp souborů
tar -czf pohoda-cms.tar.gz \
  --exclude='.git' \
  --exclude='node_modules' \
  --exclude='.DS_Store' \
  --exclude='*.log' \
  --exclude='debug_*' \
  --exclude='*_backup.*' \
  --exclude='*_original.*' \
  --exclude='getMessage' \
  .
```

### 2. Důležité soubory k nahrání:
```
📁 moje_cms/
├── 📄 index.php                    # Hlavní stránka
├── 📄 config.php                   # Databázové připojení
├── 📄 gallery.php                  # Galerie fotek
├── 📄 events.php                   # Kalendář akcí  
├── 📄 event.php                    # Detail akce
├── 📄 page_new.php                 # Zobrazení stránek
├── 📄 post_new.php                 # Zobrazení článků
├── 📄 404.php                      # Chybová stránka
├── 📄 .htaccess                    # Apache konfigurace
├── 📁 admin/                       # Admin rozhraní
├── 📁 assets/                      # CSS, JS, obrázky
├── 📁 includes/                    # PHP funkce
├── 📁 images/                      # Systémové obrázky
├── 📁 uploads/                     # Nahrané soubory
└── 📄 database_final_complete.sql  # Databáze struktura
```

---

## 🗄️ Nastavení databáze

### 1. Vytvoření databáze na hostingu
```sql
-- Vytvořte novou databázi (např. přes cPanel/phpMyAdmin)
CREATE DATABASE pohoda_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci;
```

### 2. Import databáze
```bash
# Přes phpMyAdmin: Import → vybrat database_final_complete.sql
# Nebo přes terminál:
mysql -u username -p pohoda_cms < database_final_complete.sql
```

### 3. Aktualizace config.php
```php
<?php
// config.php - PRODUKČNÍ NASTAVENÍ
$host = 'localhost';                    // nebo IP adresa DB serveru
$dbname = 'vase_db_jmeno';             // název vaší databáze
$username = 'vase_db_uzivatel';        // databázový uživatel
$password = 'vase_db_heslo';           // databázové heslo

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Chyba připojení k databázi: " . $e->getMessage());
}
?>
```

---

## 📤 Upload na hosting

### Metoda 1: FTP/SFTP
```bash
# Přes FTP klienta (FileZilla, WinSCP)
1. Připojte se k FTP serveru
2. Přejděte do public_html/ (nebo www/)
3. Nahrajte všechny soubory CMS
4. Nastavte oprávnění 755 pro složky, 644 pro soubory
```

### Metoda 2: cPanel File Manager
```
1. Přihlaste se do cPanel
2. File Manager → Public HTML
3. Upload → vyberte pohoda-cms.tar.gz
4. Extract → vyberte archiv
5. Přesuňte obsah z podsložky do root
```

### Metoda 3: Git deployment
```bash
# Pokud hosting podporuje Git
git clone https://github.com/supervisor-bit/cms-pohoda-antosovice.git
cd cms-pohoda-antosovice
# Nastavte config.php pro produkci
```

---

## 🔧 Konfigurace serveru

### .htaccess pro Apache
```apache
RewriteEngine On
RewriteBase /

# Bezpečnost - skrytí citlivých souborů
<Files "config.php">
    Order allow,deny
    Deny from all
</Files>

<Files "*.sql">
    Order allow,deny  
    Deny from all
</Files>

# PHP nastavení
php_flag display_errors Off
php_value upload_max_filesize 50M
php_value post_max_size 50M
php_value max_execution_time 300

# Komprese
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css text/javascript application/javascript application/json
</IfModule>

# Cache headers
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
</IfModule>
```

---

## 🔒 Zabezpečení

### 1. Oprávnění souborů
```bash
# Nastavte správná oprávnění
find . -type d -exec chmod 755 {} \;    # Složky
find . -type f -exec chmod 644 {} \;    # Soubory
chmod 600 config.php                    # Konfigurační soubor
chmod 777 uploads/                      # Upload složka
```

### 2. Bezpečnostní hlavičky
```php
// Přidejte do config.php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
```

### 3. Změna admin přístupů
```sql
-- Změňte výchozí admin hesla
UPDATE admin_users SET password = PASSWORD('nove_silne_heslo') WHERE username = 'admin';
```

---

## ✅ Post-deploy checklist

### 1. Testování základní funkčnosti
- [ ] **Hlavní stránka** se načítá správně
- [ ] **Admin login** funguje (/admin/)
- [ ] **Databázové připojení** je funkční
- [ ] **Upload obrázků** funguje v admin
- [ ] **Galerie** zobrazuje nahraných fotek
- [ ] **Kalendář akcí** je přístupný

### 2. Konfigurace v admin panelu
```
Admin → Nastavení:
- [ ] Název webu
- [ ] Popis webu  
- [ ] Kontaktní email
- [ ] Telefon
- [ ] Facebook URL
- [ ] Instagram URL (volitelně)
```

### 3. Vytvoření prvního obsahu
```
- [ ] Vytvořte základní stránky (O nás, Kontakt, etc.)
- [ ] Nahrajte první fotky do galerie
- [ ] Vytvořte kategorie akcí
- [ ] Přidejte testovací akci
```

---

## 🌍 Doporučení pro různé hosting providery

### Wedos.cz
```php
$host = 'mysql.wedos.net';
// Aktivujte PHP 8.0+ v administraci
// Nastavte databázi přes WebAdmin
```

### Forpsi.cz  
```php
$host = 'mysql.forpsi.com';
// Použijte MySQL 5.7+
// Upload limit: standardně 32MB
```

### Active24.cz
```php
$host = 'mysql.active24.cz';
// PHP extensions jsou předinstalované
// SSL certifikát zdarma přes Let's Encrypt
```

### SiteGround
```php
$host = 'localhost';
// Optimalizace pro WordPress ale podporuje i custom PHP
// Automatické backupy
```

---

## 📞 Řešení problémů

### Časté chyby po deployu:

**1. "Database connection failed"**
```
✓ Zkontrolujte config.php
✓ Ověřte databázové přihlašovací údaje
✓ Importujte database_final_complete.sql
```

**2. "500 Internal Server Error"**  
```
✓ Zkontrolujte .htaccess
✓ Nastavte správná oprávnění souborů
✓ Aktivujte error reporting dočasně
```

**3. "Upload fotek nefunguje"**
```
✓ Nastavte chmod 777 na uploads/
✓ Zkontrolujte upload_max_filesize v PHP
✓ Ověřte dostupné místo na disku
```

**4. "Admin panel nedostupný"**
```
✓ Ověřte admin/ složku na serveru
✓ Zkontrolujte databázové tabulky admin_users
✓ Resetujte admin heslo přes SQL
```

---

## 🎯 Finální kroky

1. **Testování na všech zařízeních** (mobil, tablet, desktop)
2. **SEO optimalizace** (meta tagy, sitemap.xml)
3. **Nastavení SSL certifikátu** (HTTPS)
4. **Konfigurace zálohování** databáze
5. **Monitoring** dostupnosti webu

---

**🎉 Váš CMS je připraven pro produkční nasazení!**

Pro podporu nebo otázky kontaktujte: [vaš kontakt]