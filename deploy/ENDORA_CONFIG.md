# 🛠 Endora Hosting – Nastavení CMS

Tento dokument popisuje, jak správně nasadit CMS Pohoda Antošovice na hosting Endora.

## 1. Databáze (MySQL)
Na Endora si v administraci založte novou databázi. V administraci najdete přesné:
- Host (server) – nejčastěji `mysql.endora.cz`
- Port: 
  - Free varianta: často 3310
  - Placené programy: standardní 3306
- Název databáze
- Uživatelské jméno
- Heslo

Ověřte aktuální hodnoty vždy v sekci Databáze / Přístupy.

## 2. Konfigurace `config.php`
Upravte produkční soubor:
```php
<?php
$servername = 'mysql.endora.cz'; // nebo konkrétní server uvedený v Endora administraci
$dbport     = '3310';            // Pokud jste na free variantě, jinak vynechat
$username   = 'VASE_DB_UZIVATEL';
$password   = 'VASE_DB_HESLO';
$dbname     = 'VASE_DB_NAZEV';

$debug = false; // V produkci vypnout!
$site_url = 'https://vasedomena.endora.cz';

try {
    // Pokud je potřeba port, použijte host=servername;port=dbport
    $dsn = "mysql:host=$servername;port=$dbport;dbname=$dbname;charset=utf8";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Chyba připojení k databázi: ' . $e->getMessage());
}
?>
```
Pokud používáte placený program a port je 3306 (standard), můžete řádek `$dbport` a `port=$dbport;` odstranit:
```php
$dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8";
```

## 3. Import databáze
V phpMyAdmin na Endora:
1. Otevřete databázi
2. Klikněte na Import
3. Nahrajte soubor `database_full_current_20251123.sql` (pro kompletní obsah)
4. Nebo `database_schema_empty.sql` (pokud chcete začít od čisté struktury)
5. Spusťte import

Poté se přihlaste do administrace: `/admin/` (uživatel admin podle importu).

## 4. Oprávnění souborů
- Složka `uploads/` musí být zapisovatelná (na Endora je obvykle vše OK, případně nastavit 775/777)
- Ostatní složky 755, soubory 644

## 5. .htaccess doporučení
Endora podporuje .htaccess – použijte úpravy pro bezpečnost:
```apache
<Files "config.php">
  Order allow,deny
  Deny from all
</Files>
<Files "*.sql">
  Order allow,deny
  Deny from all
</Files>
```

## 6. HTTPS
Na Endora lze aktivovat HTTPS (LetsEncrypt) – doporučeno: po aktivaci upravte `$site_url` na `https://...`

## 7. Nejčastější chyby
| Chyba | Řešení |
|-------|--------|
| "Access denied for user" | Zkontrolovat uživatelské jméno/heslo v config.php |
| "Unknown database" | Název DB není správně nebo nebyl vytvořen |
| "Connection timed out" | Špatný host nebo port (ověřte v Endora administraci) |
| Obrázky se neukládají | Práva k `uploads/` nejsou správná |

### Řešení HTTP 500 (bílá stránka / interní chyba)
HTTP 500 bez detailu znamená, že PHP spadlo (nejčastěji špatné DB údaje nebo chyba v kódu) a zobrazování chyb je vypnuté. Postup:

1. Dočasně povolte chyby (hned po zjištění vraťte zpět):
```php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```
Umístěte úplně na začátek `config.php`.

2. Ověřte DSN – Endora FREE často vyžaduje port:
```php
$dsn = "mysql:host=$servername;port=$dbport;dbname=$dbname;charset=utf8";
```
Pokud jste na placeném tarifu (3306), port nemusíte uvádět.

3. Nejčastější příčiny:
- Nesprávné heslo nebo uživatel → chyba typu Access denied
- Nesprávný název DB → Unknown database
- Špatný host / port → Connection timed out / could not find driver
- Chybějící rozšíření PDO/MySQL (u Endora bývá aktivní automaticky)

4. Testovací soubor pro ověření běhu PHP:
Vytvořte `test.php` v kořeni:
```php
<?php phpinfo();
```
Pokud nejde načíst, problém je mimo aplikaci (hosting / umístění souborů).

5. Příklad produkčního přepínače v `config.php`:
```php
$isProduction = (strpos($_SERVER['HTTP_HOST'], 'endora.site') !== false);
if ($isProduction) {
  $servername = 'mysql.endora.cz';
  $dbport     = '3310'; // upravte dle administrace
  $username   = 'PROD_USER';
  $password   = 'PROD_PASS';
  $dbname     = 'PROD_DB';
  $site_url   = 'https://antosovice.endora.site';
  $debug = false;
} else {
  // Lokál (MAMP)
  $servername = 'localhost';
  $dbport     = '8889';
  $username   = 'root';
  $password   = 'root';
  $dbname     = 'moje_cms';
  $site_url   = 'http://localhost:8888/moje_cms';
  $debug = true;
}
// Sestavení DSN
$dsn = isset($dbport) && $dbport !== ''
  ? "mysql:host=$servername;port=$dbport;dbname=$dbname;charset=utf8"
  : "mysql:host=$servername;dbname=$dbname;charset=utf8";
try {
  $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
  if ($debug) { die('DB ERROR: ' . $e->getMessage()); }
  die('Interní chyba.');
}
```

6. Po vyřešení chyby VYPNĚTE zobrazování chyb (nastavte `$debug = false`).

## 8. Test po nasazení
- Otevřít `index.php` → načte se homepage?
- `admin/login.php` → přihlášení funguje?
- Nahrát fotku v galerii → uloží se?
- Vložit akci → zobrazí se v `events.php`?

## 9. Doporučené další kroky
- Změnit admin heslo hned po deploy
- Vyplnit Facebook/Instagram v Nastavení
- Nahrát první galerii fotek
- Vytvořit základní stránky (Kontakt, O nás)

---
V případě problémů zkontrolujte logy chyb (Endora administrace → Logy) nebo dočasně nastavte `$debug = true` (POUZE krátkodobě).
