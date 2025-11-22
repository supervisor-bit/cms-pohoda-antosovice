# CMS systém pro Naturistický kemp Pohoda Antošovice

## Popis
Kompletní Content Management System (CMS) vytvořený speciálně pro naturistický kemp. Systém obsahuje moderní administraci s WYSIWYG editorem, správu stránek a článků, vkládání obrázků a bezpečnostní funkce.

## Funkce
- ✅ **Správa stránek** - hierarchické menu, meta popisy, ikony
- ✅ **Správa článků** - blog systém s kategoriemi a náhledy
- ✅ **WYSIWYG editor** - CKEditor 5 s českým jazykem
- ✅ **Vkládání obrázků** - z URL i upload z disku
- ✅ **Responzivní design** - mobilní optimalizace
- ✅ **Bezpečnostní funkce** - hash hesel, zabezpečené přihlášení
- ✅ **Reset hesla** - systém pro obnovení zapomenutých hesel
- ✅ **Earth green téma** - naturistický design
- ✅ **Social media integrace** - Facebook a Instagram odkazy

## Instalace

### 1. Nahrajte soubory na server
Nahrajte všechny soubory do kořenového adresáře vašeho webu.

### 2. Nastavte databázi
1. Vytvořte MySQL databázi
2. Importujte soubor `database_final_complete.sql`
3. Upravte připojení v `config.php`:
```php
$host = 'localhost';      // Váš databázový server
$dbname = 'nazev_db';     // Název vaší databáze
$username = 'uzivatel';   // Databázový uživatel
$password = 'heslo';      // Databázové heslo
```

### 3. Nastavte oprávnění
Ujistěte se, že složka `uploads/` má oprávnění 755:
```bash
chmod 755 uploads/
```

### 4. První přihlášení
- URL administrace: `vase-domena.cz/admin/`
- Výchozí přihlášení: `admin` / `password`
- **DŮLEŽITÉ:** Změňte heslo po prvním přihlášení v sekci "Profil"

## Administrace

### Přístup
- URL: `/admin/`
- Výchozí účet: `admin` / `password`

### Hlavní funkce
1. **Dashboard** - přehled systému
2. **Stránky** - správa obsahových stránek s hierarchickou strukturou
3. **Články** - blog systém s WYSIWYG editorem
4. **Nastavení** - konfigurace webu (název, popisy, social media)
5. **Profil** - změna hesla a informace o účtu

### Vkládání obrázků
Každý článek a stránka má panel pro vkládání obrázků:
- **Z URL** - vložení obrázku z internetu (Google Maps, Wikipedie)
- **Z disku** - nahrání obrázku ze svého počítače

## Zabezpečení

### Změna hesla
1. Přihlaste se do administrace
2. Jděte do sekce "Profil"
3. Vyplňte formulář pro změnu hesla
4. Systém kontroluje sílu hesla

### Zapomenuté heslo
1. Na přihlašovací stránce klikněte "Zapomenuté heslo?"
2. Zadejte uživatelské jméno
3. Zkopírujte vygenerovaný token
4. Použijte token pro nastavení nového hesla

## Technické požadavky

- **PHP** 7.4 nebo vyšší
- **MySQL** 5.7 nebo vyšší
- **Apache/Nginx** s mod_rewrite
- **Minimum 50MB** volného místa

---

**Vytvořeno pro Naturistický kemp Pohoda Antošovice**
Datum vytvoření: 19. srpna 2025
