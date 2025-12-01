-- Migrace: Přidání custom_url pro existující stránky
-- Aktualizace stránky Administrace s custom_url

UPDATE pages 
SET custom_url = 'admin/' 
WHERE slug = 'administrace';

-- Přidat ikonku pro Administraci
UPDATE pages 
SET icon = 'fas fa-cog' 
WHERE slug = 'administrace';
