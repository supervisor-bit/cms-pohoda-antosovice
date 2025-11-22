-- MySQL dump 10.13  Distrib 8.0.40, for macos12.7 (arm64)
--
-- Host: localhost    Database: moje_cms
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
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (20,'o Organizaci','o-organizaci','<h1>o organizaci</h1><p>Poslání Spolku naturistů Ostrava<br><br>Šíření myšlenek naturismu.<br>Provozování naturistické lokality Pohoda v Ostravě Antošovicích.<br>Pořádání naturistických akcí, zejména naturistických srazů, naturistické rekreace, sportovních a kulturních akcí.<br>Prosazování ochrany těch zájmů naturistů, které nejsou v rozporu s morálními a naturistickými principy, platnými právními předpisy.<br>Usilování o zpřístupnění některých rekreačních zařízení a kempů pro účely naturistické rekreace.<br>Udržování kontaktů s naturisty v České republice i v jiných zemích.<br>Napomáhání ochraně životního prostředí a přírody.<br>&nbsp;</p>','',NULL,'',NULL,'published','2025-11-22 11:39:29','2025-11-22 11:39:29',1,0,0),(21,'Provozní řád','provozni-rad','<h2>Provozní řád kempu</h2>\n        <p>Vítejte v sekci provozního řádu našeho kempu. Zde najdete všechny důležité informace o pravidlech a provozu.</p>\n        \n        <h3>Navigace v této sekci</h3>\n        <p>V levém menu najdete jednotlivé části provozního řádu:</p>\n        <ul>\n            <li><strong>Základní pravidla</strong> - Obecná pravidla pro všechny hosty</li>\n            <li><strong>Bezpečnostní předpisy</strong> - Důležité bezpečnostní informace</li>\n            <li><strong>Ubytování</strong> - Pravidla pro ubytování a stravování</li>\n            <li><strong>Noční klid</strong> - Pravidla pro večerní a noční hodiny</li>\n        </ul>\n        \n        <div class=\"alert alert-info\">\n            <i class=\"fas fa-info-circle me-2\"></i>\n            <strong>Tip:</strong> Použijte boční menu pro rychlou navigaci mezi jednotlivými sekcemi provozního řádu.\n        </div>','Kompletní provozní řád kempu s pravidly a předpisy',NULL,NULL,NULL,'published','2025-11-22 16:05:16','2025-11-22 16:05:16',1,0,1),(22,'Základní pravidla','zakladni-pravidla','<h2>Základní pravidla kempu</h2>\n            <p>Pro zajištění příjemného pobytu všech hostů prosíme o dodržování těchto základních pravidel:</p>\n            \n            <h3>Obecná pravidla</h3>\n            <ul>\n                <li>Respektujte ostatní hosty a jejich soukromí</li>\n                <li>Udržujte pořádek ve svém okolí</li>\n                <li>Dodržujte noční klid od 22:00 do 7:00</li>\n                <li>Parkování pouze na vyhrazených místech</li>\n            </ul>\n            \n            <h3>Environmentální pravidla</h3>\n            <ul>\n                <li>Třiďte odpad podle označených kontejnerů</li>\n                <li>Šetřete vodou a energiemi</li>\n                <li>Neničte rostlinstvo a nekazíte přírodu</li>\n            </ul>','Základní pravidla pro pobyt v kempu',NULL,NULL,'provozni-rad','published','2025-11-22 16:05:16','2025-11-22 16:05:16',1,0,0),(23,'Bezpečnostní předpisy','bezpecnostni-predpisy','<h2>Bezpečnostní předpisy</h2>\n            <p>Bezpečnost našich hostů je naší prioritou. Prosíme o dodržování následujících předpisů:</p>\n            \n            <h3>Požární bezpečnost</h3>\n            <ul>\n                <li>Grilování pouze na vyhrazených místech</li>\n                <li>Zákaz rozdělávání ohně mimo vyhrazené plochy</li>\n                <li>Hasicí přístroje jsou umístěny u recepcí a v sanitárních objektech</li>\n            </ul>\n            \n            <h3>Bezpečnost dětí</h3>\n            <ul>\n                <li>Děti do 12 let jsou vždy pod dohledem rodičů</li>\n                <li>Dětské hřiště je určeno pouze pro děti do 12 let</li>\n                <li>Bazén - děti pouze pod dohledem dospělých</li>\n            </ul>\n            \n            <h3>Nouzové kontakty</h3>\n            <p><strong>Recepce:</strong> +420 123 456 789<br>\n            <strong>Lékařská pohotovost:</strong> 155<br>\n            <strong>Hasiči:</strong> 150<br>\n            <strong>Policie:</strong> 158</p>','Bezpečnostní předpisy a nouzové kontakty',NULL,NULL,'provozni-rad','published','2025-11-22 16:05:16','2025-11-22 16:05:16',1,0,0),(24,'Ubytování a stravování','ubytovani-stravovani','<h2>Ubytování a stravování</h2>\n            \n            <h3>Check-in a check-out</h3>\n            <ul>\n                <li><strong>Check-in:</strong> od 14:00</li>\n                <li><strong>Check-out:</strong> do 11:00</li>\n                <li>Předčasný check-in nebo pozdní check-out po domluvě s recepcí</li>\n            </ul>\n            \n            <h3>Ubytování</h3>\n            <ul>\n                <li>Každé místo má svou maximální kapacitu - dodržujte ji</li>\n                <li>Hosté navíc pouze se souhlasem recepce</li>\n                <li>Domácí mazlíčci povoleni po ohlášení na recepci</li>\n            </ul>\n            \n            <h3>Stravování</h3>\n            <ul>\n                <li>Restaurace otevřena: 8:00 - 22:00</li>\n                <li>Snídaně: 8:00 - 10:30</li>\n                <li>Oběd: 12:00 - 15:00</li>\n                <li>Večeře: 18:00 - 21:00</li>\n            </ul>','Pravidla ubytování a provozní doba stravování',NULL,NULL,'provozni-rad','published','2025-11-22 16:05:16','2025-11-22 16:05:16',1,0,0),(25,'Noční klid','nocni-klid','<h2>Noční klid</h2>\n            <p>Pro zajištění odpočinku všech hostů je stanoven noční klid.</p>\n            \n            <h3>Časové vymezení</h3>\n            <ul>\n                <li><strong>Noční klid:</strong> 22:00 - 7:00</li>\n                <li><strong>Polední klid:</strong> 12:30 - 14:00</li>\n            </ul>\n            \n            <h3>Během nočního klidu</h3>\n            <ul>\n                <li>Minimalizujte hluk ve svém okolí</li>\n                <li>Žádná hlasitá hudba nebo TV</li>\n                <li>Rozhovory tlumeným hlasem</li>\n                <li>Automobily - vypnuté motory, pomalá jízda</li>\n            </ul>\n            \n            <h3>Sankce za porušení</h3>\n            <p>Při opakovaném porušování nočního klidu si vyhrazujeme právo požádat hosty o opuštění kempu.</p>\n            \n            <div class=\"alert alert-warning\">\n                <i class=\"fas fa-moon me-2\"></i>\n                <strong>Upozornění:</strong> Respektujte potřebu odpočinku ostatních hostů. Děkujeme za pochopení.\n            </div>','Pravidla nočního klidu a pokojného soužití',NULL,NULL,'provozni-rad','published','2025-11-22 16:05:16','2025-11-22 16:05:16',1,0,0);
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (1,'Vítejte na našich stránkách','vitejte-na-nasich-strankach','<p>Jsme rádi, že jste navštívili naše stránky. Náš naturistický kemp Pohoda Antošovice nabízí jedinečný zážitek v souladu s přírodou.</p><p>Nachází se v malebném prostředí moravských kopců, kde si můžete užít klid a relaxaci daleko od shonu města. Kemp je určen pro všechny věkové kategorie a nabízí bezpečné prostředí pro naturistickou rekreaci.</p><p><img src=\"uploads/2025-08-19_19-41-03_68a4d34f1993b.jpg\" alt=\"pic01.jpg\"></p>','Úvodní článek o kempu Pohoda Antošovice',NULL,'published','2025-08-17 17:51:13','2025-08-19 19:41:09',1,NULL),(2,'Nová sezóna 2025','nova-sezona-2025','<p>Těšíme se na novou sezónu 2025! Připravili jsme pro vás mnoho novinek a vylepšení:</p><ul><li>Nově zrekonstruované chaty s moderním vybavením</li><li>Modernizované sanitární zařízení</li><li>Nové sportoviště pro volejbal a badminton</li><li>Zlepšené WiFi pokrytí v celém areálu</li></ul><p>Rezervace na sezónu 2025 již přijímáme!</p>','Novinky a vylepšení pro sezónu 2025',NULL,'published','2025-08-17 17:51:13','2025-08-17 17:51:13',1,NULL),(3,'Jarní přípravy kempu','jarni-pripravy-kempu','<p>Březen je měsíc, kdy začínáme s přípravami kempu na novou sezónu. Naši pracovníci se starají o údržbu všech zařízení:</p><ul><li>Kontrola a oprava chat</li><li>Údržba sanitárních zařízení</li><li>Příprava zahradních úprav</li><li>Kontrola bezpečnostních systémů</li></ul><p>Vše bude připraveno k zahájení sezóny v květnu!</p>','Přípravy kempu na novou sezónu',NULL,'published','2025-08-17 17:51:13','2025-08-17 17:51:13',1,NULL),(4,'Letní aktivity a programy','letni-aktivity-programy','<p>Během letní sezóny pořádáme různé aktivity pro naše hosty:</p><h3>Sportovní aktivity</h3><ul><li>Aqua aerobic v bazénu</li><li>Volejbalové turnaje</li><li>Ranní cvičení</li></ul><h3>Společenské akce</h3><ul><li>Hudební večery</li><li>Tematické party</li><li>Grilování pod hvězdami</li></ul><p>Program aktivit najdete vždy na nástěnce u recepce.</p>','Letní aktivity a společenské akce v kempu',NULL,'published','2025-08-17 17:51:13','2025-08-17 17:51:13',1,NULL),(5,'Bezpečnost a pravidla','bezpecnost-pravidla','<p>Bezpečnost našich hostů je pro nás priorita. Proto dodržujte prosím základní pravidla:</p><h3>Bezpečnostní opatření</h3><ul><li>Kemp je oplocený a hlídaný</li><li>Vstup pouze pro registrované hosty</li><li>Kamerový systém ve společných prostorách</li></ul><h3>Děti v kempu</h3><p>Děti do 16 let musí být neustále pod dohledem rodičů. V kempu máme dětské hřiště a bezpečné prostory pro hry.</p>','Informace o bezpečnosti a pravidlech kempu',NULL,'published','2025-08-17 17:51:13','2025-08-17 17:51:13',1,NULL),(6,'Podzimní speciální nabídky','podzimni-specialni-nabidky','<p>I v podzimních měsících nabízíme speciální pobyty:</p><h3>Podzimní víkendy</h3><ul><li>Sleva 30% na ubytování v říjnu</li><li>Speciální wellness programy</li><li>Saunování pod hvězdami</li></ul><p>Podzim v kempu má své kouzlo - barevné listí, teplé dny a chladnější večery ideální pro relaxaci.</p>','Podzimní nabídky a slevy na ubytování',NULL,'published','2025-08-17 17:51:13','2025-08-17 17:51:13',1,NULL),(7,'test','test_test','<p>to je testovaci stranka zalozena v adminu</p>','to je test',NULL,'published','2025-11-22 05:38:44','2025-11-22 05:38:44',1,NULL);
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quick_links`
--

DROP TABLE IF EXISTS `quick_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quick_links` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `position` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quick_links`
--

LOCK TABLES `quick_links` WRITE;
/*!40000 ALTER TABLE `quick_links` DISABLE KEYS */;
INSERT INTO `quick_links` VALUES (1,'O organizaci','page_new.php?slug=o-organizaci','Informace o naší organizaci',1,1,'2025-11-22 16:40:11','2025-11-22 16:40:11'),(2,'Provozní řád','page_new.php?slug=provozni-rad','Pravidla a předpisy',2,1,'2025-11-22 16:40:11','2025-11-22 16:40:11'),(3,'Ubytování','page_new.php?slug=ubytovani-stravovani','Informace o ubytování',3,1,'2025-11-22 16:40:11','2025-11-22 16:40:11');
/*!40000 ALTER TABLE `quick_links` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'site_title','Pohoda Antošovice','2025-08-17 17:51:13'),(2,'site_description','relaxace v harmonii s přírodou','2025-08-17 17:51:13'),(3,'contact_phone','+420 123 456 789','2025-08-17 17:51:13'),(4,'contact_email','info@pohoda-antosovice.cz','2025-08-17 17:51:13'),(5,'contact_address','Antošovice 123, 739 53 Antošovice','2025-08-17 17:51:13'),(6,'facebook_url','https://www.facebook.com/pohoda.antosovice','2025-08-17 17:51:13'),(7,'instagram_url','','2025-08-17 17:51:13'),(10,'camp_capacity','50','2025-08-17 17:51:13'),(11,'price_adult','150','2025-08-17 17:51:13'),(12,'price_child','100','2025-08-17 17:51:13'),(13,'price_tent','80','2025-08-17 17:51:13'),(14,'price_caravan','120','2025-08-17 17:51:13'),(15,'price_cabin_2','800','2025-08-17 17:51:13'),(16,'price_cabin_4','1200','2025-08-17 17:51:13'),(17,'price_electricity','50','2025-08-17 17:51:13'),(18,'season_start','2025-05-01','2025-08-17 17:51:13'),(19,'season_end','2025-09-30','2025-08-17 17:51:13'),(20,'operating_hours_reception','8:00 - 20:00','2025-08-17 17:51:13'),(21,'operating_hours_restaurant','12:00 - 22:00','2025-08-17 17:51:13'),(22,'operating_hours_pool','9:00 - 21:00','2025-08-17 17:51:13'),(23,'operating_hours_sauna','16:00 - 22:00','2025-08-17 17:51:13'),(28,'address','','2025-08-19 05:21:53'),(29,'opening_hours','','2025-08-19 05:21:53');
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

-- Dump completed on 2025-11-22 19:35:39
