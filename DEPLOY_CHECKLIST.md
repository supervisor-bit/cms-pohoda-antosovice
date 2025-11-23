# 📋 Rychlý Deploy Checklist

## Před deployem - lokální příprava
- [ ] Exportovat databázi z phpMyAdmin  
- [ ] Zabalit soubory (bez .git, debug souborů)
- [ ] Připravit produkční config.php

## Hosting setup  
- [ ] Vytvořit MySQL databázi na hostingu
- [ ] Importovat database_final_complete.sql
- [ ] Nahrát všechny soubory do public_html/
- [ ] Nastavit oprávnění: složky 755, soubory 644
- [ ] uploads/ složka: 777

## Konfigurace
- [ ] Upravit config.php s produkčními údaji
- [ ] Testovat databázové připojení
- [ ] Otestovat admin login (/admin/)

## První konfigurace
- [ ] Admin → Nastavení → vyplnit základní informace
- [ ] Nahraát první fotku do galerie  
- [ ] Vytvořit testovací akci
- [ ] Vytvořit základní stránky (Kontakt, O nás)

## Finální test
- [ ] Hlavní stránka se načítá
- [ ] Menu funguje
- [ ] Galerie zobrazuje fotky
- [ ] Kalendář akcí funguje
- [ ] Admin panel přístupný
- [ ] Upload fotek funguje

✅ **Web je připraven pro produkci!**