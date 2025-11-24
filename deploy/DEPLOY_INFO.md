# Deploy balíček CMS Pohoda Antošovice
**Datum vytvoření:** 23. listopadu 2025, 21:09

## Změny v této verzi

### 🎨 Design a branding
- ✅ **Logo SNO**: Implementováno logo `images/sno-logo.png` (90px výška)
- ✅ **Navbar barva**: Změněna na #6f9183 (odpovídá barvě loga)
- ✅ **Zelená tlačítka**: Všechna tlačítka mají zemitou zelenou (#2d5016) místo modré
- ✅ **Odkazy**: Zelené odkazy v obsahu stránek s hover efekty

### 📱 Responzivní design
- ✅ **Mobilní menu**: Boční menu se na mobilu zobrazuje nad obsahem
- ✅ **Flexibilní layout**: Menu položky se na mobilu zobrazují horizontálně
- ✅ **Sticky menu vypnuto**: Na mobilu menu není přilepené k obrazovce

### 🗂️ Navigace a menu
- ✅ **Menu pořadí**: Změněno z abecedního na `menu_order`
- ✅ **"Naše akce"**: Přejmenováno z "Akce"
- ✅ **"Domů" na konci**: Menu položka "Domů" přesunuta na konec (vpravo)
- ✅ **Stránka "Poslání"**: Vytvořena s menu_order=1

### 📰 Články
- ✅ **1 článek na homepage**: Zobrazuje se pouze nejnovější článek
- ✅ **Články první**: Zobrazují se před statickými kartami
- ✅ **400 znaků preview**: Náhled článku zobrazuje 400 znaků obsahu
- ✅ **posts.php**: Nová stránka pro archiv všech článků
  - Vlevo: Detail vybraného článku
  - Vpravo: Seznam všech článků
  - URL parametr `?selected=slug` pro výběr článku

### 🔗 Odkazy
- ✅ **Facebook v novém okně**: FB odkazy se automaticky otevírají v novém okně
- ✅ **Externí odkazy**: JavaScript detekce Facebook odkazů
- ✅ **Zelené styly**: Všechny odkazy a tlačítka v obsahu mají zelenou barvu

### 🗑️ Odstraněno
- ✅ **Rychlé odkazy**: Kompletně odstraněna funkce z footerů, adminu a databáze
- ✅ **"Více informací" tlačítko**: Odstraněno z hlavní stránky header

## Soubory v balíčku

### Nové soubory
- `posts.php` - Archiv všech článků s dvousloupcovým layoutem
- `images/sno-logo.png` - Logo spolku (90px výška)

### Upravené soubory
- `index.php` - Logo, navbar barva, 1 článek, zelená tlačítka
- `page_new.php` - Zelené odkazy, FB odkazy v novém okně, responzivní sidebar
- `post_new.php` - Logo, navbar barva, zelená tlačítka
- `event.php` - Logo, navbar barva
- `events.php` - Logo, navbar barva
- `gallery.php` - Logo, navbar barva
- `includes/functions.php` - Menu ordering, "Naše akce", "Domů" na konci
- `admin/includes/admin_header.php` - Odstraněny rychlé odkazy
- `admin/index.php` - Odstraněny rychlé odkazy

### Databázové změny
```sql
-- Stránka "Poslání"
INSERT INTO pages (title, slug, content, menu_order, ...) VALUES ('Poslání', 'poslani', ..., 1, ...);

-- Menu order aktualizace
UPDATE pages SET menu_order = 1 WHERE slug = 'poslani';
UPDATE pages SET menu_order = 2 WHERE slug = 'o-organizaci';
UPDATE pages SET menu_order = 3 WHERE slug = 'kontakt';
UPDATE pages SET menu_order = 6 WHERE slug = 'provozni-rad';
UPDATE pages SET menu_order = 7 WHERE slug = 'vice-informaci';

-- Smazání rychlých odkazů
DROP TABLE IF EXISTS quick_links;
```

## Instalace na produkční server

### 1. Záloha
```bash
# Zálohujte současnou databázi
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# Zálohujte současné soubory
tar -czf backup_files_$(date +%Y%m%d).tar.gz /path/to/cms
```

### 2. Extrakce balíčku
```bash
cd /path/to/deployment
tar -xzf cms_deploy_YYYYMMDD_HHMMSS.tar.gz -C /path/to/cms/
```

### 3. Databáze
```bash
# Import databáze
mysql -u username -p database_name < database_export_YYYYMMDD_HHMMSS.sql
```

### 4. Konfigurace
```bash
# Upravte config.php s produkčními údaji
nano /path/to/cms/config.php
```

Aktualizujte:
- DB_HOST (např. localhost nebo IP adresa)
- DB_PORT (Endora používá port 3310)
- DB_NAME
- DB_USER
- DB_PASS

### 5. Oprávnění
```bash
# Nastavte správná oprávnění
chmod 755 /path/to/cms
chmod 644 /path/to/cms/*.php
chmod 755 /path/to/cms/uploads
chmod 755 /path/to/cms/images
```

### 6. Ověření
- [ ] Logo se zobrazuje správně
- [ ] Navbar má barvu #6f9183
- [ ] Tlačítka jsou zelená
- [ ] Menu je ve správném pořadí
- [ ] Články se zobrazují správně
- [ ] posts.php funguje
- [ ] FB odkazy se otevírají v novém okně
- [ ] Mobilní zobrazení funguje správně

## Technické detaily

### CSS změny
- **Navbar barva**: `background: #6f9183 !important;`
- **Logo**: `height: 90px !important;`
- **Primární barva**: `--primary-color: #2d5016`
- **Tlačítka**: `background: var(--primary-color)`
- **Responzivní**: `@media (max-width: 991px)` pro mobilní layout

### JavaScript funkce
- Automatické `target="_blank"` pro Facebook odkazy
- Inline styly pro přepsání Bootstrap tlačítek
- Hover efekty na zelené tlačítka

### Databázové změny
- Nová stránka "Poslání" (menu_order=1)
- Aktualizované menu_order hodnoty
- Smazána tabulka quick_links

## Kontakt
Pro technickou podporu kontaktujte vývojáře.

---
*Verze: 2025-11-23*
*Git commit: 2a1fe38*
