-- MySQL dump 10.13  Distrib 8.0.40, for macos12.7 (arm64)
--
-- Host: 127.0.0.1    Database: moje_cms
-- ------------------------------------------------------
-- Server version	8.0.40

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES (2,'admin','$2y$10$haIOZkw1f1YW3jA7hHJq/eBz4sxHF8nbIrkXU79K6kCjYdQMZl9Qi','2025-08-17 20:45:36');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'admin','$2y$10$v0vNj8tRO0E9DiEkowMXfu75nxW3CIi/CD4ROvIeN6L2FnvsId0oG','admin@test.cz','2025-08-17 20:51:04');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_categories`
--

DROP TABLE IF EXISTS `event_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#007bff',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'calendar',
  `is_active` tinyint(1) DEFAULT '1',
  `position` int DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_categories`
--

LOCK TABLES `event_categories` WRITE;
/*!40000 ALTER TABLE `event_categories` DISABLE KEYS */;
INSERT INTO `event_categories` VALUES (2,'Sportovní akce','Sportovní aktivity a turnaje','#007bff','trophy',1,2),(3,'Kulturní akce','Koncerty, divadla, kulturní program','#6f42c1','music-note',1,3),(4,'Setkání','Společenská setkání a diskuze','#fd7e14','people',1,4),(5,'Relaxace','Wellness a relaxační aktivity','#20c997','heart',1,5);
/*!40000 ALTER TABLE `event_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_registrations`
--

DROP TABLE IF EXISTS `event_registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_registrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `participants` int DEFAULT '1',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','confirmed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  KEY `email` (`email`),
  CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_registrations`
--

LOCK TABLES `event_registrations` WRITE;
/*!40000 ALTER TABLE `event_registrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_registrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `start_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_all_day` tinyint(1) DEFAULT '0',
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `featured_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_participants` int DEFAULT NULL,
  `registration_required` tinyint(1) DEFAULT '0',
  `registration_deadline` date DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `start_date` (`start_date`),
  KEY `is_published` (`is_published`),
  KEY `category` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,'Letní slunovrat 2026','Oslava nejdelšího dne v roce s kulturním programem','<p>Tradiční oslava letního slunovratu s bohatým kulturním programem. Čeká vás živá hudba, workshopy, společné grilování a večerní táborák.</p><p>Program:<br>- 14:00 Otevření akce<br>- 15:00 Workshop jógy<br>- 17:00 Koncert folk skupiny<br>- 19:00 Společné grilování<br>- 21:00 Táborák a zpívání</p>','2026-06-21','14:00:00','2026-06-22','02:00:00','Hlavní areál',0,'Kulturní akce',NULL,NULL,0,NULL,NULL,1,'2025-11-23 06:31:00','2025-11-23 06:31:00','letni-slunovrat-2026'),(2,'Ranní jóga každé úterý','Pravidelná ranní jóga pod širým nebem','<p>Každé úterý ráno se můžete zúčastnit relaxační jógy v přírodním prostředí. Vhodné pro začátečníky i pokročilé.</p><p>Vezměte si:<br>- Podložku na jógu<br>- Pohodlné oblečení<br>- Pozitivní náladu</p>','2026-01-07','07:00:00','2026-12-31','08:30:00','Louka u jezera',0,'Relaxace',NULL,NULL,0,NULL,NULL,1,'2025-11-23 06:31:00','2025-11-23 06:31:00','ranní-joga'),(3,'Naturistický volleyball turnaj','Tradiční letní turnaj ve beach volejbale','<p>Největší naturistický sportovní event roku! Přihlašujte své týmy a bojujte o putovní pohár.</p><p>Pravidla:<br>- Týmy po 4 hráčích<br>- Registrace do 15.7.<br>- Startovné 200 Kč/tým<br>- Ceny pro první 3 místa</p>','2026-07-20','09:00:00','2026-07-20','18:00:00','Volejbalové hřiště',0,'Sportovní akce',NULL,NULL,0,NULL,NULL,1,'2025-11-23 06:31:00','2025-11-23 06:31:00','volleyball-turnaj-2026');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gallery_photos`
--

DROP TABLE IF EXISTS `gallery_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gallery_photos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int DEFAULT '0',
  `image_width` int DEFAULT '0',
  `image_height` int DEFAULT '0',
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `is_published` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `upload_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gallery_photos`
--

LOCK TABLES `gallery_photos` WRITE;
/*!40000 ALTER TABLE `gallery_photos` DISABLE KEYS */;
/*!40000 ALTER TABLE `gallery_photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `meta_description` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `featured_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('published','draft') COLLATE utf8mb4_unicode_ci DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_published` tinyint(1) DEFAULT '1',
  `menu_order` int DEFAULT '0',
  `has_sidebar_menu` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (20,'o Organizaci','o-organizaci','<h1>o organizaci</h1><p>Poslání Spolku naturistů Ostrava<br><br>Šíření myšlenek naturismu.<br>Provozování naturistické lokality Pohoda v Ostravě Antošovicích.<br>Pořádání naturistických akcí, zejména naturistických srazů, naturistické rekreace, sportovních a kulturních akcí.<br>Prosazování ochrany těch zájmů naturistů, které nejsou v rozporu s morálními a naturistickými principy, platnými právními předpisy.<br>Usilování o zpřístupnění některých rekreačních zařízení a kempů pro účely naturistické rekreace.<br>Udržování kontaktů s naturisty v České republice i v jiných zemích.<br>Napomáhání ochraně životního prostředí a přírody.<br>&nbsp;</p>','',NULL,'',NULL,'published','2025-11-22 11:39:29','2025-11-23 15:37:12',1,2,0),(21,'Provozní řád','provozni-rad','<h2>Provozní řád kempu</h2>\n        <p>Vítejte v sekci provozního řádu našeho kempu. Zde najdete všechny důležité informace o pravidlech a provozu.</p>\n        \n        <h3>Navigace v této sekci</h3>\n        <p>V levém menu najdete jednotlivé části provozního řádu:</p>\n        <ul>\n            <li><strong>Základní pravidla</strong> - Obecná pravidla pro všechny hosty</li>\n            <li><strong>Bezpečnostní předpisy</strong> - Důležité bezpečnostní informace</li>\n            <li><strong>Ubytování</strong> - Pravidla pro ubytování a stravování</li>\n            <li><strong>Noční klid</strong> - Pravidla pro večerní a noční hodiny</li>\n        </ul>\n        \n        <div class=\"alert alert-info\">\n            <i class=\"fas fa-info-circle me-2\"></i>\n            <strong>Tip:</strong> Použijte boční menu pro rychlou navigaci mezi jednotlivými sekcemi provozního řádu.\n        </div>','Kompletní provozní řád kempu s pravidly a předpisy',NULL,NULL,NULL,'published','2025-11-22 16:05:16','2025-11-23 15:37:12',1,6,1),(22,'Základní pravidla','zakladni-pravidla','<h2>Základní pravidla kempu</h2>\n            <p>Pro zajištění příjemného pobytu všech hostů prosíme o dodržování těchto základních pravidel:</p>\n            \n            <h3>Obecná pravidla</h3>\n            <ul>\n                <li>Respektujte ostatní hosty a jejich soukromí</li>\n                <li>Udržujte pořádek ve svém okolí</li>\n                <li>Dodržujte noční klid od 22:00 do 7:00</li>\n                <li>Parkování pouze na vyhrazených místech</li>\n            </ul>\n            \n            <h3>Environmentální pravidla</h3>\n            <ul>\n                <li>Třiďte odpad podle označených kontejnerů</li>\n                <li>Šetřete vodou a energiemi</li>\n                <li>Neničte rostlinstvo a nekazíte přírodu</li>\n            </ul>','Základní pravidla pro pobyt v kempu',NULL,NULL,'provozni-rad','published','2025-11-22 16:05:16','2025-11-22 16:05:16',1,0,0),(23,'Bezpečnostní předpisy','bezpecnostni-predpisy','<h2>Bezpečnostní předpisy</h2>\n            <p>Bezpečnost našich hostů je naší prioritou. Prosíme o dodržování následujících předpisů:</p>\n            \n            <h3>Požární bezpečnost</h3>\n            <ul>\n                <li>Grilování pouze na vyhrazených místech</li>\n                <li>Zákaz rozdělávání ohně mimo vyhrazené plochy</li>\n                <li>Hasicí přístroje jsou umístěny u recepcí a v sanitárních objektech</li>\n            </ul>\n            \n            <h3>Bezpečnost dětí</h3>\n            <ul>\n                <li>Děti do 12 let jsou vždy pod dohledem rodičů</li>\n                <li>Dětské hřiště je určeno pouze pro děti do 12 let</li>\n                <li>Bazén - děti pouze pod dohledem dospělých</li>\n            </ul>\n            \n            <h3>Nouzové kontakty</h3>\n            <p><strong>Recepce:</strong> +420 123 456 789<br>\n            <strong>Lékařská pohotovost:</strong> 155<br>\n            <strong>Hasiči:</strong> 150<br>\n            <strong>Policie:</strong> 158</p>','Bezpečnostní předpisy a nouzové kontakty',NULL,NULL,'provozni-rad','published','2025-11-22 16:05:16','2025-11-22 16:05:16',1,0,0),(24,'Ubytování a stravování','ubytovani-stravovani','<h2>Ubytování a stravování</h2>\n            \n            <h3>Check-in a check-out</h3>\n            <ul>\n                <li><strong>Check-in:</strong> od 14:00</li>\n                <li><strong>Check-out:</strong> do 11:00</li>\n                <li>Předčasný check-in nebo pozdní check-out po domluvě s recepcí</li>\n            </ul>\n            \n            <h3>Ubytování</h3>\n            <ul>\n                <li>Každé místo má svou maximální kapacitu - dodržujte ji</li>\n                <li>Hosté navíc pouze se souhlasem recepce</li>\n                <li>Domácí mazlíčci povoleni po ohlášení na recepci</li>\n            </ul>\n            \n            <h3>Stravování</h3>\n            <ul>\n                <li>Restaurace otevřena: 8:00 - 22:00</li>\n                <li>Snídaně: 8:00 - 10:30</li>\n                <li>Oběd: 12:00 - 15:00</li>\n                <li>Večeře: 18:00 - 21:00</li>\n            </ul>','Pravidla ubytování a provozní doba stravování',NULL,NULL,'provozni-rad','published','2025-11-22 16:05:16','2025-11-22 16:05:16',1,0,0),(25,'Noční klid','nocni-klid','<h2>Noční klid</h2>\n            <p>Pro zajištění odpočinku všech hostů je stanoven noční klid.</p>\n            \n            <h3>Časové vymezení</h3>\n            <ul>\n                <li><strong>Noční klid:</strong> 22:00 - 7:00</li>\n                <li><strong>Polední klid:</strong> 12:30 - 14:00</li>\n            </ul>\n            \n            <h3>Během nočního klidu</h3>\n            <ul>\n                <li>Minimalizujte hluk ve svém okolí</li>\n                <li>Žádná hlasitá hudba nebo TV</li>\n                <li>Rozhovory tlumeným hlasem</li>\n                <li>Automobily - vypnuté motory, pomalá jízda</li>\n            </ul>\n            \n            <h3>Sankce za porušení</h3>\n            <p>Při opakovaném porušování nočního klidu si vyhrazujeme právo požádat hosty o opuštění kempu.</p>\n            \n            <div class=\"alert alert-warning\">\n                <i class=\"fas fa-moon me-2\"></i>\n                <strong>Upozornění:</strong> Respektujte potřebu odpočinku ostatních hostů. Děkujeme za pochopení.\n            </div>','Pravidla nočního klidu a pokojného soužití',NULL,NULL,'provozni-rad','published','2025-11-22 16:05:16','2025-11-22 16:05:16',1,0,0),(46,'Kontakt','kontakt','<h2>Kontaktní informace</h2>\n\n<h3>Adresa kempu</h3>\n<p>\n<strong>Kemp Pohoda Antošovice</strong><br>\nAntošovice 123<br>\n739 53 Antošovice<br>\nČeská republika\n</p>\n\n<h3>Kontakt</h3>\n<p>\n<strong>Telefon:</strong> <a href=\"tel:+420123456789\">+420 123 456 789</a><br>\n<strong>Email:</strong> <a href=\"mailto:info@pohoda-antosovice.cz\">info@pohoda-antosovice.cz</a>\n</p>\n\n<h3>Otevírací doba</h3>\n<h4>Hlavní sezóna (květen - září)</h4>\n<p>\n<strong>Pondělí - Neděle:</strong> 8:00 - 22:00<br>\n<strong>Recepce:</strong> 8:00 - 20:00\n</p>\n\n<h4>Mimosezóna (říjen - duben)</h4>\n<p>\n<strong>Otevřeno po domluvě</strong><br>\nVolejte prosím předem pro domluvení termínu návštěvy.\n</p>','Kontaktní informace kempu Pohoda Antošovice - telefon, email, adresa a otevírací doba',NULL,'fas fa-phone',NULL,'published','2025-11-23 07:31:32','2025-11-23 15:37:12',1,3,0),(47,'Více informací','vice-informaci','<h2>Více informací o kempu Pohoda Antošovice</h2>\n<p>Náš naturistický kemp se nachází v překrásném prostředí moravských kopců a nabízí jedinečný zážitek v souladu s přírodou.</p>\n\n<h3>O našem kempu</h3>\n<p>Kemp Pohoda Antošovice je určen pro všechny věkové kategorie a poskytuje bezpečné prostředí pro naturistickou rekreaci. Nacházíme se v klidné lokalitě, kde si můžete odpočinout od shonu každodenního života.</p>\n\n<h3>Nabízíme:</h3>\n<ul>\n<li><strong>Ubytování:</strong> Prostorné parcely pro stany, karavany a komfortní chaty</li>\n<li><strong>Zázemí:</strong> Moderní sanitární zařízení s teplou vodou</li>\n<li><strong>Relaxaci:</strong> Bazén, sauna, odpočinkové zóny</li>\n<li><strong>Aktivní odpočinek:</strong> Sportovní hřiště, dětský koutek</li>\n<li><strong>Společenské prostory:</strong> Restaurace, společenská místnost</li>\n</ul>\n\n<h3>Proč si vybrat náš kemp?</h3>\n<ul>\n<li>✅ Oplocený a hlídaný areál</li>\n<li>✅ Přátelská a respektující atmosféra</li>\n<li>✅ Krásné přírodní prostředí</li>\n<li>✅ Kompletní služby pro pohodlný pobyt</li>\n<li>✅ Dlouholeté zkušenosti s naturistickou rekreací</li>\n</ul>','Podrobné informace o naturistickém kempu Pohoda Antošovice - ubytování, služby a aktivity',NULL,'fas fa-info-circle',NULL,'published','2025-11-23 07:31:32','2025-11-23 15:37:12',1,7,0),(48,'Poslání','poslani','<h2>Poslání</h2><p>Obsah stránky Poslání...</p>',NULL,NULL,'fas fa-bullseye',NULL,'published','2025-11-23 15:37:12','2025-11-23 15:37:12',1,1,0);
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `featured_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('published','draft') COLLATE utf8mb4_unicode_ci DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_published` tinyint(1) DEFAULT '1',
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `event_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (1,'Vítejte na našich stránkách','vitejte-na-nasich-strankach','<p>Jsme rádi, že jste navštívili naše stránky. Náš naturistický kemp Pohoda Antošovice nabízí jedinečný zážitek v souladu s přírodou.</p><p>Nachází se v malebném prostředí moravských kopců, kde si můžete užít klid a relaxaci daleko od shonu města. Kemp je určen pro všechny věkové kategorie a nabízí bezpečné prostředí pro naturistickou rekreaci.</p><p><img src=\"uploads/2025-08-19_19-41-03_68a4d34f1993b.jpg\" alt=\"pic01.jpg\"></p>','Úvodní článek o kempu Pohoda Antošovice',NULL,'published','2025-08-17 17:51:13','2025-08-19 19:41:09',1,NULL,NULL),(2,'Nová sezóna 2025','nova-sezona-2025','<p>Těšíme se na novou sezónu 2025! Připravili jsme pro vás mnoho novinek a vylepšení:</p><ul><li>Nově zrekonstruované chaty s moderním vybavením</li><li>Modernizované sanitární zařízení</li><li>Nové sportoviště pro volejbal a badminton</li><li>Zlepšené WiFi pokrytí v celém areálu</li></ul><p>Rezervace na sezónu 2025 již přijímáme!</p>','Novinky a vylepšení pro sezónu 2025',NULL,'published','2025-08-17 17:51:13','2025-08-17 17:51:13',1,NULL,NULL),(3,'Jarní přípravy kempu','jarni-pripravy-kempu','<p>Březen je měsíc, kdy začínáme s přípravami kempu na novou sezónu. Naši pracovníci se starají o údržbu všech zařízení:</p><ul><li>Kontrola a oprava chat</li><li>Údržba sanitárních zařízení</li><li>Příprava zahradních úprav</li><li>Kontrola bezpečnostních systémů</li></ul><p>Vše bude připraveno k zahájení sezóny v květnu!</p>','Přípravy kempu na novou sezónu',NULL,'published','2025-08-17 17:51:13','2025-08-17 17:51:13',1,NULL,NULL),(4,'Letní aktivity a programy','letni-aktivity-programy','<p>Během letní sezóny pořádáme různé aktivity pro naše hosty:</p><h3>Sportovní aktivity</h3><ul><li>Aqua aerobic v bazénu</li><li>Volejbalové turnaje</li><li>Ranní cvičení</li></ul><h3>Společenské akce</h3><ul><li>Hudební večery</li><li>Tematické party</li><li>Grilování pod hvězdami</li></ul><p>Program aktivit najdete vždy na nástěnce u recepce.</p>','Letní aktivity a společenské akce v kempu',NULL,'published','2025-08-17 17:51:13','2025-08-17 17:51:13',1,NULL,NULL),(5,'Bezpečnost a pravidla','bezpecnost-pravidla','<p>Bezpečnost našich hostů je pro nás priorita. Proto dodržujte prosím základní pravidla:</p><h3>Bezpečnostní opatření</h3><ul><li>Kemp je oplocený a hlídaný</li><li>Vstup pouze pro registrované hosty</li><li>Kamerový systém ve společných prostorách</li></ul><h3>Děti v kempu</h3><p>Děti do 16 let musí být neustále pod dohledem rodičů. V kempu máme dětské hřiště a bezpečné prostory pro hry.</p>','Informace o bezpečnosti a pravidlech kempu',NULL,'published','2025-08-17 17:51:13','2025-08-17 17:51:13',1,NULL,NULL),(6,'Podzimní speciální nabídky','podzimni-specialni-nabidky','<p>I v podzimních měsících nabízíme speciální pobyty:</p><h3>Podzimní víkendy</h3><ul><li>Sleva 30% na ubytování v říjnu</li><li>Speciální wellness programy</li><li>Saunování pod hvězdami</li></ul><p>Podzim v kempu má své kouzlo - barevné listí, teplé dny a chladnější večery ideální pro relaxaci.</p>','Podzimní nabídky a slevy na ubytování',NULL,'published','2025-08-17 17:51:13','2025-08-17 17:51:13',1,NULL,NULL),(7,'test','test_test','<p>to je testovaci stranka zalozena v adminu</p>','to je test',NULL,'published','2025-11-22 05:38:44','2025-11-22 05:38:44',1,NULL,NULL);
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checkin_date` date NOT NULL,
  `checkout_date` date NOT NULL,
  `guests` int DEFAULT '1',
  `accommodation_type` enum('tent','caravan','cabin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','confirmed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'site_title','Pohoda Antošovice','2025-08-17 17:51:13'),(2,'site_description','relaxace v harmonii s přírodou','2025-08-17 17:51:13'),(3,'contact_phone','+420 123 456 789','2025-08-17 17:51:13'),(4,'contact_email','info@pohoda-antosovice.cz','2025-08-17 17:51:13'),(5,'contact_address','Antošovice 123, 739 53 Antošovice','2025-08-17 17:51:13'),(6,'facebook_url','https://www.facebook.com/profile.php?id=100013126296205','2025-08-17 17:51:13'),(7,'instagram_url','','2025-08-17 17:51:13'),(10,'camp_capacity','50','2025-08-17 17:51:13'),(11,'price_adult','150','2025-08-17 17:51:13'),(12,'price_child','100','2025-08-17 17:51:13'),(13,'price_tent','80','2025-08-17 17:51:13'),(14,'price_caravan','120','2025-08-17 17:51:13'),(15,'price_cabin_2','800','2025-08-17 17:51:13'),(16,'price_cabin_4','1200','2025-08-17 17:51:13'),(17,'price_electricity','50','2025-08-17 17:51:13'),(18,'season_start','2025-05-01','2025-08-17 17:51:13'),(19,'season_end','2025-09-30','2025-08-17 17:51:13'),(20,'operating_hours_reception','8:00 - 20:00','2025-08-17 17:51:13'),(21,'operating_hours_restaurant','12:00 - 22:00','2025-08-17 17:51:13'),(22,'operating_hours_pool','9:00 - 21:00','2025-08-17 17:51:13'),(23,'operating_hours_sauna','16:00 - 22:00','2025-08-17 17:51:13'),(28,'address','','2025-08-19 05:21:53'),(29,'opening_hours','','2025-08-19 05:21:53');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-23 21:18:13
