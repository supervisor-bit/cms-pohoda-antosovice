# 🚀 INSTALAČNÍ NÁVOD CMS POHODA ANTOŠOVICE v2.0

## 📋 Co potřebujete před instalací

### Minimální požadavky serveru:
- ✅ **PHP 7.4+** (doporučeno 8.1+)
- ✅ **MySQL 5.7+** (doporučeno 8.0+)
- ✅ **Web server** (Apache/Nginx)
- ✅ **100MB místa** na disku
- ✅ **SSL certifikát** (doporučeno)

### Přístupové údaje:
- 📧 Přístup k hosting panelu
- 🗄️ Databázové údaje (host, název DB, user, heslo)
- 📁 FTP/SFTP přístup

---

## 📦 KROK 1: Stažení a rozbalení

```bash
# Stáhněte archiv: kemp_pohoda_cms_v2.0_20251122.zip

# Rozbalte na váš server do složky webu
unzip kemp_pohoda_cms_v2.0_20251122.zip -d /path/to/your/website/
```

---

## 🗄️ KROK 2: Nastavení databáze

### A) Vytvoření databáze
```sql
CREATE DATABASE naturist_camp 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

### B) Import dat
```bash
# Přes phpMyAdmin nebo příkazovou řádku:
mysql -u username -p naturist_camp < database_complete_v2.sql

# Nebo přes hosting panel:
# - Jděte do phpMyAdmin
# - Vyberte vaši databázi
# - Klikněte "Import"
# - Vyberte soubor database_complete_v2.sql
```

---

## ⚙️ KROK 3: Konfigurace webu

### A) Nastavení config.php
```bash
# Zkopírujte template
cp config.php.template config.php

# Upravte databázové údaje
nano config.php  # nebo jiný editor
```

### B) Upravte tyto řádky v config.php:
```php
// DATABÁZOVÁ KONFIGURACE
define('DB_HOST', 'localhost');           // Váš DB server
define('DB_NAME', 'naturist_camp');       // Název vaší databáze
define('DB_USER', 'your_username');       // Vaše DB uživatelské jméno
define('DB_PASS', 'your_password');       // Vaše DB heslo

// KONFIGURACE WEBU
define('SITE_URL', 'https://yourdomain.com');  // Vaše doména

// BEZPEČNOST
define('PASSWORD_SALT', 'your_unique_salt_here');  // Vygenerujte nový!
```

### C) Vygenerování nového saltu:
```php
// Spusťte tento PHP kód pro generování saltu:
echo bin2hex(random_bytes(32));
```

---

## 🔒 KROK 4: Oprávnění souborů

```bash
# Nastavte správná oprávnění
chmod 755 uploads/
chmod 644 *.php
chmod 644 admin/*.php

# Pro Apache/Nginx server:
chown -R www-data:www-data ./
# nebo pro jiné systémy:
chown -R apache:apache ./
```

---

## 🌐 KROK 5: Konfigurace web serveru

### Apache (.htaccess již je připravený)
```apache
# Již máte .htaccess soubor v archivu
# Obsahuje základní nastavení pro SEO a bezpečnost
```

### Nginx (přidejte do server bloku)
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}

# Zabezpečení admin složky
location /admin {
    # Můžete přidat IP omezení
    # allow 192.168.1.0/24;
    # deny all;
}
```

---

## 🔐 KROK 6: První přihlášení a nastavení

### A) Přihlášení do administrace
1. 🌐 Otevřete: `https://yourdomain.com/admin/`
2. 👤 Přihlaste se:
   - **Uživatel:** `admin`
   - **Heslo:** `password123`

### B) OKAMŽITĚ změňte heslo!
1. 👤 Jděte do sekce **Profil**
2. 🔒 Změňte heslo na silné
3. ✉️ Případně změňte email

### C) Základní nastavení
1. ⚙️ Jděte do **Nastavení**
2. 📝 Upravte:
   - Název webu
   - Popis
   - Kontaktní údaje
   - Sociální sítě

---

## ✅ KROK 7: Testování instalace

### Otestujte tyto funkce:
- [ ] **Hlavní stránka** se načte správně
- [ ] **Menu a navigace** fungují
- [ ] **Rychlé odkazy** v patičce se zobrazují
- [ ] **Admin přihlášení** funguje
- [ ] **Vytvoření nové stránky** funguje
- [ ] **Vytvoření nového článku** funguje
- [ ] **Upload obrázků** funguje
- [ ] **404 stránka** se zobrazuje správně
- [ ] **Responzivní design** na mobilu

---

## 🔐 KROK 8: Zabezpečení (DŮLEŽITÉ!)

### A) Základní bezpečnost
```php
// V config.php zakažte zobrazování chyb:
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
```

### B) Doporučené kroky
1. 🔒 **Přejmenujte admin složku** na něco nepředvídatelného
2. 🛡️ **Nastavte SSL/HTTPS** pro celý web
3. 💾 **Nastavte pravidelné zálohy** databáze
4. 🚫 **Smažte nepotřebné soubory** (Python skripty, .zip archiv)
5. 🔄 **Pravidelně aktualizujte** PHP a MySQL

### C) Pokročilá bezpečnost
```bash
# Fail2ban pro ochranu před brute-force
apt install fail2ban

# Firewall
ufw allow ssh
ufw allow http
ufw allow https
ufw enable
```

---

## 🆘 Řešení problémů

### ❌ Databáze se nepřipojí
- Zkontrolujte údaje v `config.php`
- Ověřte, že databáze existuje
- Zkontrolujte oprávnění DB uživatele

### ❌ Obrázky se nenahrávají
- Zkontrolujte oprávnění složky `uploads/` (755)
- Ověřte PHP limity (`upload_max_filesize`, `post_max_size`)

### ❌ Stránky se nezobrazují
- Zkontrolujte `.htaccess` soubor
- Ověřte mod_rewrite v Apache

### ❌ Chybí CSS styly
- Zkontrolujte cestu v `SITE_URL`
- Ověřte, že složka `assets/` je přístupná

---

## 📞 Podpora

### V případě vážných problémů:
1. 📋 Zkontrolujte **PHP error log**
2. 🔍 Povolte **debug režim** v config.php
3. 📧 Kontaktujte **vývojáře** s detaily chyby

---

## 🎉 Gratulujeme!

Váš CMS je nyní připravený k používání. Můžete začít:

- ✍️ **Vytvářet stránky** a články
- 🖼️ **Nahrávat obrázky** 
- ⚙️ **Spravovat rychlé odkazy**
- 🎨 **Přizpůsobovat design**

**Užijte si váš nový web! 🌲✨**

---
*CMS Pohoda Antošovice v2.0 | Listopad 2025*