# CMS Pohoda Antošovice

Kompletní webový systém pro správu webu pláže Pohoda v Antošovicích.

## 🚀 Rychlý start

1. Rozbalte balíček na server
2. Vytvořte databázi a importujte `database_complete.sql`
3. Upravte `config.php` s přístupovými údaji
4. Otevřete admin panel: `/admin/`

**Detailní návod:** viz soubor `INSTALACE.md`

---

## ✨ Hlavní funkce

- ✅ Správa stránek s hierarchickým menu
- ✅ Blog (příspěvky)
- ✅ Kalendář událostí
- ✅ Fotogalerie
- ✅ Upload souborů
- ✅ Responzivní design
- ✅ SEO optimalizace
- ✅ Admin panel

---

## 🎨 Design

**Barva:** #6f9183 (barva SNO loga)  
**Framework:** Bootstrap 5.3.3  
**Ikony:** Font Awesome 6  

---

## 📦 Požadavky

- PHP 7.4+ (doporučeno 8.0+)
- MySQL 5.7+ (doporučeno 8.0+)
- Apache / Nginx
- mod_rewrite (pro pretty URLs)

---

## 📂 Struktura

```
/admin/         - Administrační panel
/assets/        - CSS, JS
/images/        - Obrázky a logo
/includes/      - PHP funkce
/uploads/       - Nahrané soubory
/*.php          - Veřejné stránky
config.php      - Konfigurace
```

---

## 🔐 Bezpečnost

**Po instalaci:**
1. Změňte výchozí heslo admina
2. Nastavte oprávnění souborů: `chmod 644 config.php`
3. Chraňte složku uploads před PHP exekucí
4. Použijte HTTPS

---

## 🛠️ Podpora

**GitHub:** https://github.com/supervisor-bit/cms-pohoda-antosovice  
**Web:** https://antosovice.endora.site

---

## 📜 Licence

Viz soubor LICENSE.txt

---

**Verze 2.1** - 24. listopadu 2025
