# 🚀 Deploy Package - 23. listopadu 2025

## 📦 Obsah balíčku

Tento balíček obsahuje kompletní produkční verzi CMS Pohoda Antošovice připravenou k nasazení na Endora hosting.

### Soubory v deploy složce:

1. **cms-pohoda-antosovice-deploy-20251123.zip** (294 KB)
   - Kompletní aplikace bez .git a debug souborů
   - Připravená k nahrání na hosting

2. **database_production_full_20251123.sql** (30 KB)
   - Export aktuální databáze včetně VŠECH dat z lokálu
   - Obsahuje stránky, příspěvky, události, nastavení, quick_links, gallery_photos
   - Admin přístupy (admin/admin)

3. **database_schema_empty.sql**
   - Prázdné schéma bez dat (pro čistý start)

4. **ENDORA_CONFIG.md**
   - Specifický návod pro Endora hosting
   - Řešení HTTP 500 a dalších chyb
   - Config s přepínačem dev/produkce

5. **PRODUCTION_CONFIG.md**
   - Šablona produkčního config.php
   - .htaccess bezpečnost

6. **README.md**
   - Základní informace o balíčku

## 🎯 Rychlý postup nasazení

### 1. Příprava na Endora
```
- Vytvořit databázi v administraci
- Poznamenat: host (mysql.endora.cz), port (3310), DB název, user, heslo
```

### 2. Nahrání souborů
```
- Rozbalit cms-pohoda-antosovice-deploy-20251123.zip lokálně
- Nahrát přes FTP/cPanel do public_html/ (nebo web/)
```

### 3. Databáze
```
- V phpMyAdmin importovat: database_production_full_20251123.sql
- Zkontrolovat, že všechny tabulky byly vytvořeny
```

### 4. Konfigurace
Upravit `config.php`:
```php
// DATABÁZE
define('DB_HOST', 'mysql.endora.cz');
$dbport = '3310';  // free plán
$username = 'TVŮJ_DB_USER';
$password = 'TVÉ_DB_HESLO';
$dbname = 'TVÁ_DATABÁZE';

// SITE URL
define('SITE_URL', 'https://antosovice.endora.site');

// DSN s portem
$dsn = "mysql:host=" . DB_HOST . ";port=$dbport;dbname=$dbname;charset=utf8";
```

### 5. Oprávnění
```bash
uploads/ → 777 (nebo 775)
ostatní složky → 755
soubory → 644
```

### 6. Test
```
✓ Otevřít: https://antosovice.endora.site/
✓ Admin: https://antosovice.endora.site/admin/
   Login: admin / admin (změň po prvním přihlášení!)
✓ Nahrát fotku v galerii
✓ Vytvořit testovací akci
```

## 📋 Co je zahrnuto v databázi

### Tabulky:
- `admin`, `admins` - admin účty
- `pages` - stránky (O kempu, Ubytování, Ceník...)
- `posts` - příspěvky/články
- `events`, `event_categories`, `event_registrations` - kalendář akcí
- `gallery_photos` - galerie fotek okolí
- `quick_links` - rychlé odkazy v patičce
- `settings` - nastavení webu (email, telefon, Facebook, Instagram)
- `reservations` - rezervace (pokud existují)

### Admin přístup:
- **Username:** admin
- **Password:** admin
- **Důležité:** Změň heslo hned po prvním přihlášení!

## 🔧 Řešení problémů

### HTTP 500
1. Zapnout display_errors v config.php
2. Zkontrolovat DB údaje (host, port, user, heslo, název DB)
3. Ověřit, že DSN obsahuje port pro free plán

### Chybí tabulky
- Reimportovat database_production_full_20251123.sql

### Fotky se neukládají
- Nastavit uploads/ na 777

### Warnings "Undefined array key"
- Aktuální verze již obsahuje všechny opravy

## 📊 Statistiky balíčku
- **Velikost aplikace:** 294 KB (komprimováno)
- **Velikost databáze:** 30 KB
- **Počet souborů:** ~80
- **PHP verze:** 7.4+
- **MySQL verze:** 5.7+

## 🎨 Funkce systému
✅ Responzivní design (Bootstrap 5)
✅ Admin panel s přehledným rozhraním
✅ Správa stránek s hierarchií (menu/submenu)
✅ Blog/příspěvky
✅ Kalendář akcí s kategoriemi
✅ Galerie fotek s modal preview
✅ Dynamické menu generované z DB
✅ Quick links v patičce
✅ Facebook/Instagram integrace
✅ České měsíce všude
✅ Unified design napříč celým systémem
✅ FontAwesome ikony (CDN)

## 📞 Podpora
Pro detailní návody viz:
- `ENDORA_CONFIG.md` - specifika Endora hostingu
- `DEPLOY_GUIDE.md` - obecný deploy guide
- `PRODUCTION_CONFIG.md` - produkční konfigurace

---
**Verze:** Production 2025-11-23  
**Připraveno pro:** Endora hosting (antosovice.endora.site)  
**Status:** Ready to deploy ✅
