-- Tabulka pro správu karet na hlavní stránce
CREATE TABLE IF NOT EXISTS `homepage_cards` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `button_text` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `button_link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'fa-arrow-right',
  `position` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vložení stávajících karet
INSERT INTO `homepage_cards` (`title`, `description`, `button_text`, `button_link`, `icon`, `position`, `is_active`) VALUES
('Naturistická lokalita Pohoda v Antošovicích', 'Jsme rádi, že jste navštívili naše stránky. Naši lokalitu v Antošovicích jste již také navštívili? Ta totiž nabízí jedinečný zážitek v souladu s přírodou.', 'Více informací', 'page_new.php?slug=vice-informaci', 'fa-arrow-right', 1, 1),
('Kontaktujte nás', 'Máte otázky nebo chcete rezervovat pobyt? Neváhejte nás kontaktovat telefonicky nebo emailem.', 'Kontakt', 'page_new.php?slug=kontakt', 'fa-envelope', 2, 1),
('Sledujte nás', 'Zůstaňte v kontaktu a sledujte naše nejnovější příspěvky a fotky z kempu na sociálních sítích.', 'Sociální sítě', '#', 'fa-share-alt', 3, 1);
