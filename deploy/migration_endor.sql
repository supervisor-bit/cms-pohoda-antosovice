-- Migrace pro Endora: Homepage cards + Custom URL
-- Datum: 1. prosince 2025
-- Spusť v phpMyAdmin na Endoře

-- 1. Vytvoření tabulky homepage_cards
CREATE TABLE IF NOT EXISTS `homepage_cards` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `button_text` VARCHAR(100) NOT NULL,
    `button_link` VARCHAR(255) NOT NULL,
    `icon` VARCHAR(50) DEFAULT 'fa-arrow-right',
    `position` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Vložení výchozích karet pro homepage
INSERT INTO `homepage_cards` (`title`, `description`, `button_text`, `button_link`, `icon`, `position`) VALUES
('Naturistická lokalita', 'Objevte klidné místo v srdci přírody, kde můžete relaxovat v harmonii s přírodou.', 'Více informací', 'page_new.php?slug=plaz-pohoda', 'fa-leaf', 1),
('Kontaktujte nás', 'Máte dotazy? Rádi vám poskytneme všechny potřebné informace o našem kempu.', 'Kontakt', 'page_new.php?slug=kontakt', 'fa-envelope', 2),
('Sledujte nás', 'Buďte v obraze o našich akcích a novinkách na sociálních sítích.', 'Sociální sítě', '#', 'fa-share-alt', 3);

-- 3. Přidání sloupce custom_url do tabulky pages
ALTER TABLE `pages` ADD COLUMN `custom_url` VARCHAR(255) DEFAULT NULL AFTER `slug`;

-- 4. Vytvoření stránky Administrace v menu Organizace
INSERT INTO `pages` (`title`, `slug`, `custom_url`, `content`, `parent_slug`, `icon`, `menu_order`, `is_published`, `status`) 
VALUES ('Administrace', 'administrace', 'admin/', '', 'spolek', 'fas fa-cog', 999, 1, 'published')
ON DUPLICATE KEY UPDATE 
    `custom_url` = 'admin/', 
    `icon` = 'fas fa-cog', 
    `parent_slug` = 'spolek',
    `menu_order` = 999;

-- Hotovo! Zkontroluj výsledek:
-- SELECT * FROM homepage_cards;
-- SELECT id, title, slug, custom_url, parent_slug FROM pages WHERE slug = 'administrace';
