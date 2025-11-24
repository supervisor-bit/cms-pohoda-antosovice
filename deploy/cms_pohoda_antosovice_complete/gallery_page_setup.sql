-- SQL pro přidání stránky "Galerie" do menu
-- Tato stránka bude automaticky přesměrována na gallery.php (díky úpravě v page_new.php)

-- VARIANTA 1: Galerie jako PODMENU pod "Pláž pohoda"
INSERT INTO pages (title, slug, content, meta_description, status, parent_slug, icon, menu_order, has_sidebar_menu, is_published) 
VALUES (
    'Fotky okolí',
    'fotky-okoli',
    '<p>Fotogalerie okolí pláže Pohoda Antošovice.</p>',
    'Fotogalerie okolí pláže Pohoda Antošovice - fotky kempu',
    'published',
    'plaz-pohoda',
    'fa-images',
    10,
    0,
    1
) ON DUPLICATE KEY UPDATE 
    title = VALUES(title),
    meta_description = VALUES(meta_description),
    icon = VALUES(icon);

-- VARIANTA 2: Galerie jako HLAVNÍ POLOŽKA v menu (odkomentujte pro použití)
-- UPDATE pages SET parent_slug = NULL, menu_order = 4 WHERE slug = 'fotky-okoli';

-- PRO ZMĚNU V ADMINISTRACI:
-- Přejděte do Admin → Stránky → najděte "Fotky okolí"
-- V poli "Nadřazená stránka":
--   - Vyberte prázdné = hlavní menu
--   - Vyberte nějakou stránku = bude podmenu
-- Změňte "Pořadí v menu" podle potřeby
