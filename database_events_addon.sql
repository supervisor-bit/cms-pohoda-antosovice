-- Kalendář akcí - rozšíření databáze
-- Spusťte v MySQL po instalaci základní databáze

-- Tabulka pro akce/události
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `content` longtext,
  `start_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `is_all_day` tinyint(1) DEFAULT 0,
  `category` varchar(100) DEFAULT 'general',
  `featured_image` varchar(255) DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `registration_required` tinyint(1) DEFAULT 0,
  `registration_deadline` date DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `slug` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `start_date` (`start_date`),
  KEY `is_published` (`is_published`),
  KEY `category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kategorie akcí
CREATE TABLE `event_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `color` varchar(7) DEFAULT '#007bff',
  `icon` varchar(50) DEFAULT 'calendar',
  `is_active` tinyint(1) DEFAULT 1,
  `position` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registrace na akce (volitelné)
CREATE TABLE `event_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `participants` int(11) DEFAULT 1,
  `notes` text,
  `registration_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  KEY `email` (`email`),
  CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Přidání event_id do posts tabulky pro propojení
ALTER TABLE `posts` ADD COLUMN `event_id` int(11) DEFAULT NULL;
ALTER TABLE `posts` ADD KEY `event_id` (`event_id`);
ALTER TABLE `posts` ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL;

-- Základní kategorie akcí
INSERT INTO `event_categories` (`name`, `description`, `color`, `icon`, `position`) VALUES
('Workshopy', 'Vzdělávací akce a workshopy', '#28a745', 'book', 1),
('Sportovní akce', 'Sportovní aktivity a turnaje', '#007bff', 'trophy', 2),
('Kulturní akce', 'Koncerty, divadla, kulturní program', '#6f42c1', 'music-note', 3),
('Setkání', 'Společenská setkání a diskuze', '#fd7e14', 'people', 4),
('Relaxace', 'Wellness a relaxační aktivity', '#20c997', 'heart', 5),
('Speciální akce', 'Výjimečné a tématické akce', '#dc3545', 'star', 6);

-- Ukázkové akce
INSERT INTO `events` (`title`, `description`, `content`, `start_date`, `start_time`, `end_date`, `end_time`, `location`, `category`, `is_all_day`, `slug`) VALUES
('Letní slunovrat 2026', 'Oslava nejdelšího dne v roce s kulturním programem', '<p>Tradiční oslava letního slunovratu s bohatým kulturním programem. Čeká vás živá hudba, workshopy, společné grilování a večerní táborák.</p><p>Program:<br>- 14:00 Otevření akce<br>- 15:00 Workshop jógy<br>- 17:00 Koncert folk skupiny<br>- 19:00 Společné grilování<br>- 21:00 Táborák a zpívání</p>', '2026-06-21', '14:00:00', '2026-06-22', '02:00:00', 'Hlavní areál', 'Kulturní akce', 0, 'letni-slunovrat-2026'),

('Ranní jóga každé úterý', 'Pravidelná ranní jóga pod širým nebem', '<p>Každé úterý ráno se můžete zúčastnit relaxační jógy v přírodním prostředí. Vhodné pro začátečníky i pokročilé.</p><p>Vezměte si:<br>- Podložku na jógu<br>- Pohodlné oblečení<br>- Pozitivní náladu</p>', '2026-01-07', '07:00:00', '2026-12-31', '08:30:00', 'Louka u jezera', 'Relaxace', 0, 'ranní-joga'),

('Naturistický volleyball turnaj', 'Tradiční letní turnaj ve beach volejbale', '<p>Největší naturistický sportovní event roku! Přihlašujte své týmy a bojujte o putovní pohár.</p><p>Pravidla:<br>- Týmy po 4 hráčích<br>- Registrace do 15.7.<br>- Startovné 200 Kč/tým<br>- Ceny pro první 3 místa</p>', '2026-07-20', '09:00:00', '2026-07-20', '18:00:00', 'Volejbalové hřiště', 'Sportovní akce', 0, 'volleyball-turnaj-2026');