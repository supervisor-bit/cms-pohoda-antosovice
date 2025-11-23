# CMS pro Naturistický kemp

Kompletní systém správy obsahu (CMS) pro naturistický kemp postavený na PHP a MySQL.

## Funkce

### Frontend (Veřejná část)
- **Responzivní design** - založený na HTML5 UP šabloně "Striped"
- **Hlavní stránka** - zobrazení nejnovějších článků
- **Stránky** - statické stránky (O kempu, Ubytování, Ceník, Kontakt)
- **Galerie** - fotogalerie s lightbox efektem
- **Rezervační formulář** - online rezervace pobytu
- **Vyhledávání** - fulltext vyhledávání v obsahu

### Backend (Administrace)
- **Dashboard** - přehled statistik a rychlé akce
- **Správa stránek** - vytváření a editace statických stránek
- **Správa článků** - blog/novinky systém
- **Správa galerie** - nahrávání a správa fotografií
- **Správa rezervací** - přehled a správa rezervací
- **Nastavení** - konfigurace webu a kontaktních informací

## Instalace

### 1. Příprava prostředí
- MAMP/XAMPP/WAMP s PHP 7.4+ a MySQL
- Zkopírujte všechny soubory do složky `htdocs/moje_cms`

### 2. Databáze
1. Otevřete phpMyAdmin (http://localhost:8888/phpMyAdmin v MAMP)
2. Importujte nebo spusťte SQL příkazy ze souboru `database.sql`
3. Upravte připojení k databázi v souboru `config.php` podle vašeho nastavení

### 3. Konfigurace
Upravte následující nastavení v souboru `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'naturisticky_kemp');  
define('DB_USER', 'root');
define('DB_PASS', 'root'); // Heslo dle vašeho MAMP nastavení
```

### 4. Přístup
- **Frontend:** http://localhost:8888/moje_cms/
- **Administrace:** http://localhost:8888/moje_cms/admin/
- **Výchozí přihlášení:** admin / password

## Struktura souborů

```
moje_cms/
├── admin/              # Administrační rozhraní
│   ├── assets/         # CSS styly pro admin
│   ├── includes/       # Společné části (header)
│   ├── index.php       # Dashboard
│   ├── login.php       # Přihlášení
│   ├── pages.php       # Správa stránek
│   ├── posts.php       # Správa článků  
│   ├── gallery.php     # Správa galerie
│   ├── reservations.php # Správa rezervací
│   └── settings.php    # Nastavení webu
├── assets/             # Styly a skripty (HTML5 UP šablona)
├── images/             # Obrázky šablony
├── uploads/            # Nahrané soubory
├── config.php          # Konfigurace a databázové připojení
├── database.sql        # SQL struktura a výchozí data
├── index.php           # Hlavní stránka
├── page.php            # Zobrazení statických stránek
├── galerie.php         # Fotogalerie
├── rezervace.php       # Rezervační formulář
└── 404.php             # Chybová stránka
```

## Přizpůsobení

### Změna vzhledu
- Upravte CSS v souboru `assets/css/main.css`
- Změňte obrázky ve složce `images/`
- Přidejte vlastní logo/favicon

### Přidání funkcí
- Systém je připraven pro rozšíření
- Databázová struktura podporuje další funkce
- Kód je dobře dokumentovaný pro snadnou úpravu

## Bezpečnost

- Hesla jsou hashována pomocí PHP password_hash()
- Všechny uživatelské vstupy jsou sanitizovány
- Ochrana proti SQL injection pomocí prepared statements
- CSRF ochrana pro administrační formuláře

## Podpora

Vytvořeno na základě:
- HTML5 UP šablona "Striped" (html5up.net)
- PHP 7.4+
- MySQL 5.7+
- jQuery pro interaktivní prvky

## Licence

Šablona Striped je licencována pod Creative Commons Attribution 3.0 License.
CMS kód je volně použitelný pro komerční i nekomerční účely.
