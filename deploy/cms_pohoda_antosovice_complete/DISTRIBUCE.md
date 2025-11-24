# DISTRIBUCE CMS PRO KEMP POHODA ANTOŠOVICE

## Aktuální verze (22. listopadu 2025)

### 📦 Hlavní distribuční archiv
**kemp_pohoda_cms_v2.0_20251122.zip**
- Kompletní CMS systém s nejnovějšími funkcemi
- Čistá verze připravená k produkci
- Všechny soubory optimalizované

## 🚀 Nové funkce v této verzi:

### ✨ Pokročilé funkce:
- **Quick Links systém** - správa rychlých odkazů v admin
- **Moderní 404 stránka** - sjednocený design s hlavními stránkami
- **Vylepšený admin** - lepší UI/UX, bezpečnost
- **Responzivní design** - Bootstrap 5 s moderními efekty
- **Striped link effects** - animované efekty pro odkazy

### 🔧 Technické vylepšení:
- **Bezpečnostní úpravy** - skrytá admin entrance
- **Database optimalizace** - nová quick_links tabulka
- **CSS modernizace** - glassmorphism efekty
- **Python skripty** - pro migraci obsahu (volitelné)

## 📁 Struktura distribuce:

### Frontend soubory:
- `index.php` - hlavní stránka s nejnovějším designem
- `page_new.php` - zobrazení stránek  
- `post_new.php` - zobrazení článků
- `404.php` - moderní error stránka se sjednoceným designem
- `config.php.template` - šablona konfigurace

### Admin rozhraní:
- `admin/` - kompletní administrace
- `admin/quick_links.php` - správa rychlých odkazů (NOVÉ!)
- `admin/login.php` - přihlášení
- `admin/pages.php` - správa stránek
- `admin/posts.php` - správa článků  
- `admin/settings.php` - nastavení webu
- `admin/upload_image.php` - upload obrázků

### Styly a assety:
- `assets/css/` - moderní CSS s animacemi
- `assets/js/` - JavaScript funkce
- `admin/assets/` - admin styly
- `uploads/` - složka pro nahrané soubory

### Databáze:
- `database_complete_v2.sql` - aktuální SQL dump
- `INSTALACE.md` - podrobný instalační návod

## 🔧 Co obsahuje databáze:

### Tabulky:
- `pages` - stránky webu
- `posts` - články/novinky
- `settings` - konfigurace webu  
- `users` - admin uživatelé
- `quick_links` - rychlé odkazy (NOVÉ!)

### Výchozí data:
- Admin účet: `admin` / `password123`
- Základní nastavení webu
- Ukázkové rychlé odkazy

## 🛠️ Instalační instrukce:

### 1. Příprava serveru
```bash
# Vytvořte složku pro web
mkdir /var/www/naturist-camp
cd /var/www/naturist-camp

# Rozbalte archiv
unzip kemp_pohoda_cms_v2.0_20251122.zip
```

### 2. Konfigurace databáze
```sql
# Vytvořte MySQL databázi
CREATE DATABASE naturist_camp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Importujte strukturu a data
mysql -u root -p naturist_camp < database_complete_v2.sql
```

### 3. Nastavení konfigurace
```php
# Zkopírujte a upravte config soubor
cp config.php.template config.php
nano config.php

# Nastavte databázové údaje:
$host = 'localhost';
$dbname = 'naturist_camp';
$username = 'your_db_user';
$password = 'your_db_password';
```

### 4. Oprávnění souborů
```bash
# Nastavte správná oprávnění
chmod 755 uploads/
chown -R www-data:www-data uploads/
chmod 644 *.php
```

### 5. První přihlášení
- Otevřete: `https://yoursite.com/admin/`
- Přihlašte se: `admin` / `password123`
- **ZMĚŇTE HESLO** v sekci Profil
- Upravte nastavení webu v sekci Nastavení

## 🔒 Bezpečnostní doporučení:

### Po instalaci:
1. ✅ Změnit admin heslo
2. ✅ Smazat nepotřebné soubory (Python skripty)
3. ✅ Nastavit HTTPS
4. ✅ Zakázat zobrazování chyb PHP v produkci
5. ✅ Nastavit zálohy databáze

### Volitelné:
- Přejmenovat admin složku pro větší bezpečnost
- Nastavit fail2ban pro ochranu před brute-force útoky
- Implementovat 2FA (dvoufaktorové ověření)

## 📱 Testování:

### Před nasazením otestujte:
- [ ] Hlavní stránka se načte správně
- [ ] Menu a navigace fungují
- [ ] Rychlé odkazy v patičce
- [ ] Admin přihlášení
- [ ] Vytvoření nové stránky/článku
- [ ] Upload obrázků
- [ ] Responzivní design na mobilu
- [ ] 404 stránka

## 🆘 Podpora:

### V případě problémů:
1. Zkontrolujte PHP error log
2. Ověřte databázové připojení
3. Zkontrolujte oprávnění souborů
4. Povolte PHP debug režim pro diagnostiku

---

## 📈 Technické specifikace:

### Minimální požadavky:
- **PHP:** 7.4+
- **MySQL:** 5.7+ (doporučeno 8.0+)
- **Web server:** Apache/Nginx
- **Disk space:** 100MB
- **RAM:** 256MB

### Doporučené:
- **PHP:** 8.1+
- **MySQL:** 8.0+
- **SSL certifikát**
- **Daily backups**

---
**Připraveno:** 22. listopadu 2025  
**Verze:** 2.0 Complete  
**Status:** Ready for Production 🚀
