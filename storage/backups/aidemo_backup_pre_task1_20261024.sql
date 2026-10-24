-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: aidemo
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'sahaj2026mkt@gmail.com','$2y$10$UFx1bnf0zMsYMz6M2W9cEetWKMAUkYIh.IrTEiu57e3CbR0EDlviy','2022-08-14 05:34:56','2026-08-29 11:23:27'),(2,'pro@cpanel.com','$2y$10$UFx1bnf0zMsYMz6M2W9cEetWKMAUkYIh.IrTEiu57e3CbR0EDlviy','2024-02-03 14:10:42','2024-02-03 14:10:42');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_us`
--

DROP TABLE IF EXISTS `contact_us`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_us` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(55) NOT NULL,
  `message` varchar(1000) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_us`
--

LOCK TABLES `contact_us` WRITE;
/*!40000 ALTER TABLE `contact_us` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_us` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `iso` char(2) NOT NULL,
  `name` varchar(80) NOT NULL,
  `nicename` varchar(80) NOT NULL,
  `iso3` char(3) DEFAULT NULL,
  `numcode` smallint(6) DEFAULT NULL,
  `phonecode` int(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=240 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `countries`
--

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;
INSERT INTO `countries` VALUES (1,'AF','AFGHANISTAN','Afghanistan','AFG',4,93),(2,'AL','ALBANIA','Albania','ALB',8,355),(3,'DZ','ALGERIA','Algeria','DZA',12,213),(4,'AS','AMERICAN SAMOA','American Samoa','ASM',16,1684),(5,'AD','ANDORRA','Andorra','AND',20,376),(6,'AO','ANGOLA','Angola','AGO',24,244),(7,'AI','ANGUILLA','Anguilla','AIA',660,1264),(8,'AQ','ANTARCTICA','Antarctica',NULL,NULL,0),(9,'AG','ANTIGUA AND BARBUDA','Antigua and Barbuda','ATG',28,1268),(10,'AR','ARGENTINA','Argentina','ARG',32,54),(11,'AM','ARMENIA','Armenia','ARM',51,374),(12,'AW','ARUBA','Aruba','ABW',533,297),(13,'AU','AUSTRALIA','Australia','AUS',36,61),(14,'AT','AUSTRIA','Austria','AUT',40,43),(15,'AZ','AZERBAIJAN','Azerbaijan','AZE',31,994),(16,'BS','BAHAMAS','Bahamas','BHS',44,1242),(17,'BH','BAHRAIN','Bahrain','BHR',48,973),(18,'BD','BANGLADESH','Bangladesh','BGD',50,880),(19,'BB','BARBADOS','Barbados','BRB',52,1246),(20,'BY','BELARUS','Belarus','BLR',112,375),(21,'BE','BELGIUM','Belgium','BEL',56,32),(22,'BZ','BELIZE','Belize','BLZ',84,501),(23,'BJ','BENIN','Benin','BEN',204,229),(24,'BM','BERMUDA','Bermuda','BMU',60,1441),(25,'BT','BHUTAN','Bhutan','BTN',64,975),(26,'BO','BOLIVIA','Bolivia','BOL',68,591),(27,'BA','BOSNIA AND HERZEGOVINA','Bosnia and Herzegovina','BIH',70,387),(28,'BW','BOTSWANA','Botswana','BWA',72,267),(29,'BV','BOUVET ISLAND','Bouvet Island',NULL,NULL,0),(30,'BR','BRAZIL','Brazil','BRA',76,55),(31,'IO','BRITISH INDIAN OCEAN TERRITORY','British Indian Ocean Territory',NULL,NULL,246),(32,'BN','BRUNEI DARUSSALAM','Brunei Darussalam','BRN',96,673),(33,'BG','BULGARIA','Bulgaria','BGR',100,359),(34,'BF','BURKINA FASO','Burkina Faso','BFA',854,226),(35,'BI','BURUNDI','Burundi','BDI',108,257),(36,'KH','CAMBODIA','Cambodia','KHM',116,855),(37,'CM','CAMEROON','Cameroon','CMR',120,237),(38,'CA','CANADA','Canada','CAN',124,1),(39,'CV','CAPE VERDE','Cape Verde','CPV',132,238),(40,'KY','CAYMAN ISLANDS','Cayman Islands','CYM',136,1345),(41,'CF','CENTRAL AFRICAN REPUBLIC','Central African Republic','CAF',140,236),(42,'TD','CHAD','Chad','TCD',148,235),(43,'CL','CHILE','Chile','CHL',152,56),(44,'CN','CHINA','China','CHN',156,86),(45,'CX','CHRISTMAS ISLAND','Christmas Island',NULL,NULL,61),(46,'CC','COCOS (KEELING) ISLANDS','Cocos (Keeling) Islands',NULL,NULL,672),(47,'CO','COLOMBIA','Colombia','COL',170,57),(48,'KM','COMOROS','Comoros','COM',174,269),(49,'CG','CONGO','Congo','COG',178,242),(50,'CD','CONGO, THE DEMOCRATIC REPUBLIC OF THE','Congo, the Democratic Republic of the','COD',180,242),(51,'CK','COOK ISLANDS','Cook Islands','COK',184,682),(52,'CR','COSTA RICA','Costa Rica','CRI',188,506),(53,'CI','COTE D\'IVOIRE','Cote D\'Ivoire','CIV',384,225),(54,'HR','CROATIA','Croatia','HRV',191,385),(55,'CU','CUBA','Cuba','CUB',192,53),(56,'CY','CYPRUS','Cyprus','CYP',196,357),(57,'CZ','CZECH REPUBLIC','Czech Republic','CZE',203,420),(58,'DK','DENMARK','Denmark','DNK',208,45),(59,'DJ','DJIBOUTI','Djibouti','DJI',262,253),(60,'DM','DOMINICA','Dominica','DMA',212,1767),(61,'DO','DOMINICAN REPUBLIC','Dominican Republic','DOM',214,1809),(62,'EC','ECUADOR','Ecuador','ECU',218,593),(63,'EG','EGYPT','Egypt','EGY',818,20),(64,'SV','EL SALVADOR','El Salvador','SLV',222,503),(65,'GQ','EQUATORIAL GUINEA','Equatorial Guinea','GNQ',226,240),(66,'ER','ERITREA','Eritrea','ERI',232,291),(67,'EE','ESTONIA','Estonia','EST',233,372),(68,'ET','ETHIOPIA','Ethiopia','ETH',231,251),(69,'FK','FALKLAND ISLANDS (MALVINAS)','Falkland Islands (Malvinas)','FLK',238,500),(70,'FO','FAROE ISLANDS','Faroe Islands','FRO',234,298),(71,'FJ','FIJI','Fiji','FJI',242,679),(72,'FI','FINLAND','Finland','FIN',246,358),(73,'FR','FRANCE','France','FRA',250,33),(74,'GF','FRENCH GUIANA','French Guiana','GUF',254,594),(75,'PF','FRENCH POLYNESIA','French Polynesia','PYF',258,689),(76,'TF','FRENCH SOUTHERN TERRITORIES','French Southern Territories',NULL,NULL,0),(77,'GA','GABON','Gabon','GAB',266,241),(78,'GM','GAMBIA','Gambia','GMB',270,220),(79,'GE','GEORGIA','Georgia','GEO',268,995),(80,'DE','GERMANY','Germany','DEU',276,49),(81,'GH','GHANA','Ghana','GHA',288,233),(82,'GI','GIBRALTAR','Gibraltar','GIB',292,350),(83,'GR','GREECE','Greece','GRC',300,30),(84,'GL','GREENLAND','Greenland','GRL',304,299),(85,'GD','GRENADA','Grenada','GRD',308,1473),(86,'GP','GUADELOUPE','Guadeloupe','GLP',312,590),(87,'GU','GUAM','Guam','GUM',316,1671),(88,'GT','GUATEMALA','Guatemala','GTM',320,502),(89,'GN','GUINEA','Guinea','GIN',324,224),(90,'GW','GUINEA-BISSAU','Guinea-Bissau','GNB',624,245),(91,'GY','GUYANA','Guyana','GUY',328,592),(92,'HT','HAITI','Haiti','HTI',332,509),(93,'HM','HEARD ISLAND AND MCDONALD ISLANDS','Heard Island and Mcdonald Islands',NULL,NULL,0),(94,'VA','HOLY SEE (VATICAN CITY STATE)','Holy See (Vatican City State)','VAT',336,39),(95,'HN','HONDURAS','Honduras','HND',340,504),(96,'HK','HONG KONG','Hong Kong','HKG',344,852),(97,'HU','HUNGARY','Hungary','HUN',348,36),(98,'IS','ICELAND','Iceland','ISL',352,354),(99,'IN','INDIA','India','IND',356,91),(100,'ID','INDONESIA','Indonesia','IDN',360,62),(101,'IR','IRAN, ISLAMIC REPUBLIC OF','Iran, Islamic Republic of','IRN',364,98),(102,'IQ','IRAQ','Iraq','IRQ',368,964),(103,'IE','IRELAND','Ireland','IRL',372,353),(104,'IL','ISRAEL','Israel','ISR',376,972),(105,'IT','ITALY','Italy','ITA',380,39),(106,'JM','JAMAICA','Jamaica','JAM',388,1876),(107,'JP','JAPAN','Japan','JPN',392,81),(108,'JO','JORDAN','Jordan','JOR',400,962),(109,'KZ','KAZAKHSTAN','Kazakhstan','KAZ',398,7),(110,'KE','KENYA','Kenya','KEN',404,254),(111,'KI','KIRIBATI','Kiribati','KIR',296,686),(112,'KP','KOREA, DEMOCRATIC PEOPLE\'S REPUBLIC OF','Korea, Democratic People\'s Republic of','PRK',408,850),(113,'KR','KOREA, REPUBLIC OF','Korea, Republic of','KOR',410,82),(114,'KW','KUWAIT','Kuwait','KWT',414,965),(115,'KG','KYRGYZSTAN','Kyrgyzstan','KGZ',417,996),(116,'LA','LAO PEOPLE\'S DEMOCRATIC REPUBLIC','Lao People\'s Democratic Republic','LAO',418,856),(117,'LV','LATVIA','Latvia','LVA',428,371),(118,'LB','LEBANON','Lebanon','LBN',422,961),(119,'LS','LESOTHO','Lesotho','LSO',426,266),(120,'LR','LIBERIA','Liberia','LBR',430,231),(121,'LY','LIBYAN ARAB JAMAHIRIYA','Libyan Arab Jamahiriya','LBY',434,218),(122,'LI','LIECHTENSTEIN','Liechtenstein','LIE',438,423),(123,'LT','LITHUANIA','Lithuania','LTU',440,370),(124,'LU','LUXEMBOURG','Luxembourg','LUX',442,352),(125,'MO','MACAO','Macao','MAC',446,853),(126,'MK','MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF','Macedonia, the Former Yugoslav Republic of','MKD',807,389),(127,'MG','MADAGASCAR','Madagascar','MDG',450,261),(128,'MW','MALAWI','Malawi','MWI',454,265),(129,'MY','MALAYSIA','Malaysia','MYS',458,60),(130,'MV','MALDIVES','Maldives','MDV',462,960),(131,'ML','MALI','Mali','MLI',466,223),(132,'MT','MALTA','Malta','MLT',470,356),(133,'MH','MARSHALL ISLANDS','Marshall Islands','MHL',584,692),(134,'MQ','MARTINIQUE','Martinique','MTQ',474,596),(135,'MR','MAURITANIA','Mauritania','MRT',478,222),(136,'MU','MAURITIUS','Mauritius','MUS',480,230),(137,'YT','MAYOTTE','Mayotte',NULL,NULL,269),(138,'MX','MEXICO','Mexico','MEX',484,52),(139,'FM','MICRONESIA, FEDERATED STATES OF','Micronesia, Federated States of','FSM',583,691),(140,'MD','MOLDOVA, REPUBLIC OF','Moldova, Republic of','MDA',498,373),(141,'MC','MONACO','Monaco','MCO',492,377),(142,'MN','MONGOLIA','Mongolia','MNG',496,976),(143,'MS','MONTSERRAT','Montserrat','MSR',500,1664),(144,'MA','MOROCCO','Morocco','MAR',504,212),(145,'MZ','MOZAMBIQUE','Mozambique','MOZ',508,258),(146,'MM','MYANMAR','Myanmar','MMR',104,95),(147,'NA','NAMIBIA','Namibia','NAM',516,264),(148,'NR','NAURU','Nauru','NRU',520,674),(149,'NP','NEPAL','Nepal','NPL',524,977),(150,'NL','NETHERLANDS','Netherlands','NLD',528,31),(151,'AN','NETHERLANDS ANTILLES','Netherlands Antilles','ANT',530,599),(152,'NC','NEW CALEDONIA','New Caledonia','NCL',540,687),(153,'NZ','NEW ZEALAND','New Zealand','NZL',554,64),(154,'NI','NICARAGUA','Nicaragua','NIC',558,505),(155,'NE','NIGER','Niger','NER',562,227),(156,'NG','NIGERIA','Nigeria','NGA',566,234),(157,'NU','NIUE','Niue','NIU',570,683),(158,'NF','NORFOLK ISLAND','Norfolk Island','NFK',574,672),(159,'MP','NORTHERN MARIANA ISLANDS','Northern Mariana Islands','MNP',580,1670),(160,'NO','NORWAY','Norway','NOR',578,47),(161,'OM','OMAN','Oman','OMN',512,968),(162,'PK','PAKISTAN','Pakistan','PAK',586,92),(163,'PW','PALAU','Palau','PLW',585,680),(164,'PS','PALESTINIAN TERRITORY, OCCUPIED','Palestinian Territory, Occupied',NULL,NULL,970),(165,'PA','PANAMA','Panama','PAN',591,507),(166,'PG','PAPUA NEW GUINEA','Papua New Guinea','PNG',598,675),(167,'PY','PARAGUAY','Paraguay','PRY',600,595),(168,'PE','PERU','Peru','PER',604,51),(169,'PH','PHILIPPINES','Philippines','PHL',608,63),(170,'PN','PITCAIRN','Pitcairn','PCN',612,0),(171,'PL','POLAND','Poland','POL',616,48),(172,'PT','PORTUGAL','Portugal','PRT',620,351),(173,'PR','PUERTO RICO','Puerto Rico','PRI',630,1787),(174,'QA','QATAR','Qatar','QAT',634,974),(175,'RE','REUNION','Reunion','REU',638,262),(176,'RO','ROMANIA','Romania','ROM',642,40),(177,'RU','RUSSIAN FEDERATION','Russian Federation','RUS',643,70),(178,'RW','RWANDA','Rwanda','RWA',646,250),(179,'SH','SAINT HELENA','Saint Helena','SHN',654,290),(180,'KN','SAINT KITTS AND NEVIS','Saint Kitts and Nevis','KNA',659,1869),(181,'LC','SAINT LUCIA','Saint Lucia','LCA',662,1758),(182,'PM','SAINT PIERRE AND MIQUELON','Saint Pierre and Miquelon','SPM',666,508),(183,'VC','SAINT VINCENT AND THE GRENADINES','Saint Vincent and the Grenadines','VCT',670,1784),(184,'WS','SAMOA','Samoa','WSM',882,684),(185,'SM','SAN MARINO','San Marino','SMR',674,378),(186,'ST','SAO TOME AND PRINCIPE','Sao Tome and Principe','STP',678,239),(187,'SA','SAUDI ARABIA','Saudi Arabia','SAU',682,966),(188,'SN','SENEGAL','Senegal','SEN',686,221),(189,'CS','SERBIA AND MONTENEGRO','Serbia and Montenegro',NULL,NULL,381),(190,'SC','SEYCHELLES','Seychelles','SYC',690,248),(191,'SL','SIERRA LEONE','Sierra Leone','SLE',694,232),(192,'SG','SINGAPORE','Singapore','SGP',702,65),(193,'SK','SLOVAKIA','Slovakia','SVK',703,421),(194,'SI','SLOVENIA','Slovenia','SVN',705,386),(195,'SB','SOLOMON ISLANDS','Solomon Islands','SLB',90,677),(196,'SO','SOMALIA','Somalia','SOM',706,252),(197,'ZA','SOUTH AFRICA','South Africa','ZAF',710,27),(198,'GS','SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS','South Georgia and the South Sandwich Islands',NULL,NULL,0),(199,'ES','SPAIN','Spain','ESP',724,34),(200,'LK','SRI LANKA','Sri Lanka','LKA',144,94),(201,'SD','SUDAN','Sudan','SDN',736,249),(202,'SR','SURINAME','Suriname','SUR',740,597),(203,'SJ','SVALBARD AND JAN MAYEN','Svalbard and Jan Mayen','SJM',744,47),(204,'SZ','SWAZILAND','Swaziland','SWZ',748,268),(205,'SE','SWEDEN','Sweden','SWE',752,46),(206,'CH','SWITZERLAND','Switzerland','CHE',756,41),(207,'SY','SYRIAN ARAB REPUBLIC','Syrian Arab Republic','SYR',760,963),(208,'TW','TAIWAN, PROVINCE OF CHINA','Taiwan, Province of China','TWN',158,886),(209,'TJ','TAJIKISTAN','Tajikistan','TJK',762,992),(210,'TZ','TANZANIA, UNITED REPUBLIC OF','Tanzania, United Republic of','TZA',834,255),(211,'TH','THAILAND','Thailand','THA',764,66),(212,'TL','TIMOR-LESTE','Timor-Leste',NULL,NULL,670),(213,'TG','TOGO','Togo','TGO',768,228),(214,'TK','TOKELAU','Tokelau','TKL',772,690),(215,'TO','TONGA','Tonga','TON',776,676),(216,'TT','TRINIDAD AND TOBAGO','Trinidad and Tobago','TTO',780,1868),(217,'TN','TUNISIA','Tunisia','TUN',788,216),(218,'TR','TURKEY','Turkey','TUR',792,90),(219,'TM','TURKMENISTAN','Turkmenistan','TKM',795,7370),(220,'TC','TURKS AND CAICOS ISLANDS','Turks and Caicos Islands','TCA',796,1649),(221,'TV','TUVALU','Tuvalu','TUV',798,688),(222,'UG','UGANDA','Uganda','UGA',800,256),(223,'UA','UKRAINE','Ukraine','UKR',804,380),(224,'AE','UNITED ARAB EMIRATES','United Arab Emirates','ARE',784,971),(225,'GB','UNITED KINGDOM','United Kingdom','GBR',826,44),(226,'US','UNITED STATES','United States','USA',840,1),(227,'UM','UNITED STATES MINOR OUTLYING ISLANDS','United States Minor Outlying Islands',NULL,NULL,1),(228,'UY','URUGUAY','Uruguay','URY',858,598),(229,'UZ','UZBEKISTAN','Uzbekistan','UZB',860,998),(230,'VU','VANUATU','Vanuatu','VUT',548,678),(231,'VE','VENEZUELA','Venezuela','VEN',862,58),(232,'VN','VIET NAM','Viet Nam','VNM',704,84),(233,'VG','VIRGIN ISLANDS, BRITISH','Virgin Islands, British','VGB',92,1284),(234,'VI','VIRGIN ISLANDS, U.S.','Virgin Islands, U.s.','VIR',850,1340),(235,'WF','WALLIS AND FUTUNA','Wallis and Futuna','WLF',876,681),(236,'EH','WESTERN SAHARA','Western Sahara','ESH',732,212),(237,'YE','YEMEN','Yemen','YEM',887,967),(238,'ZM','ZAMBIA','Zambia','ZMB',894,260),(239,'ZW','ZIMBABWE','Zimbabwe','ZWE',716,263);
/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `daily_incomes`
--

DROP TABLE IF EXISTS `daily_incomes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daily_incomes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `income_id` int(10) DEFAULT NULL,
  `member_id` varchar(255) NOT NULL,
  `amount` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `package` int(5) NOT NULL,
  `installment` int(5) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_incomes`
--

LOCK TABLES `daily_incomes` WRITE;
/*!40000 ALTER TABLE `daily_incomes` DISABLE KEYS */;
/*!40000 ALTER TABLE `daily_incomes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dash_messages`
--

DROP TABLE IF EXISTS `dash_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dash_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rank` int(11) NOT NULL,
  `message` varchar(1000) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dash_messages`
--

LOCK TABLES `dash_messages` WRITE;
/*!40000 ALTER TABLE `dash_messages` DISABLE KEYS */;
INSERT INTO `dash_messages` VALUES (19,1,'Welcome to all users','2026-06-25 12:51:49','2026-06-25 12:51:49');
/*!40000 ALTER TABLE `dash_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `direct_incomes`
--

DROP TABLE IF EXISTS `direct_incomes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `direct_incomes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `memberid` varchar(255) NOT NULL,
  `directid` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `package` decimal(12,2) DEFAULT 0.00,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sponsor_package` varchar(255) NOT NULL DEFAULT '',
  `source_type` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Paid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `direct_incomes_reference_unique` (`reference`),
  KEY `direct_incomes_memberid_index` (`memberid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `direct_incomes`
--

LOCK TABLES `direct_incomes` WRITE;
/*!40000 ALTER TABLE `direct_incomes` DISABLE KEYS */;
INSERT INTO `direct_incomes` VALUES (1,'QRB123456','QRB942901','Sahaj',100.00,5.00,'100',NULL,'Investment',NULL,NULL,'Paid','2026-09-07 15:12:14','2026-09-07 15:12:14');
/*!40000 ALTER TABLE `direct_incomes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fund_requests`
--

DROP TABLE IF EXISTS `fund_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fund_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `memberid` varchar(15) NOT NULL,
  `date` date NOT NULL,
  `amount` decimal(10,4) NOT NULL,
  `tran_id` varchar(100) NOT NULL,
  `type` varchar(255) NOT NULL,
  `inr_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `image` text NOT NULL,
  `status` enum('Requested','Accepted','Cancelled') NOT NULL DEFAULT 'Requested',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fund_requests`
--

LOCK TABLES `fund_requests` WRITE;
/*!40000 ALTER TABLE `fund_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `fund_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import_funds`
--

DROP TABLE IF EXISTS `import_funds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `import_funds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `memberid` varchar(20) NOT NULL,
  `txnid` varchar(100) DEFAULT NULL,
  `orderid` varchar(255) DEFAULT NULL,
  `usdg_amount` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `amount` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `image` text DEFAULT NULL,
  `type` enum('Add','Deduct') NOT NULL DEFAULT 'Add',
  `wallet_type` varchar(255) DEFAULT NULL,
  `added_by` varchar(255) DEFAULT NULL,
  `status` enum('Approved','Pending','Cancelled') NOT NULL DEFAULT 'Approved',
  `mode` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `import_funds`
--

LOCK TABLES `import_funds` WRITE;
/*!40000 ALTER TABLE `import_funds` DISABLE KEYS */;
INSERT INTO `import_funds` VALUES (1,'QRB942901','NA','48676',0.0000,500.0000,NULL,'Add','Fund Wallet','Admin','Approved','Manual','2026-09-07 15:10:34','2026-09-07 15:10:34'),(2,'QRB886154','NA','19241',0.0000,1000.0000,NULL,'Add','Fund Wallet','Admin','Approved','Manual','2026-09-07 15:14:25','2026-09-07 15:14:25');
/*!40000 ALTER TABLE `import_funds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `level_incomes`
--

DROP TABLE IF EXISTS `level_incomes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `level_incomes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `memberid` varchar(255) NOT NULL,
  `level_id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `level` varchar(50) NOT NULL,
  `downline` int(10) DEFAULT NULL,
  `staking_income` decimal(10,4) NOT NULL,
  `amount` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `type` varchar(255) DEFAULT NULL,
  `rate` decimal(10,2) NOT NULL,
  `package` int(10) NOT NULL DEFAULT 0,
  `status` enum('Paid','Unpaid','Flushed') NOT NULL DEFAULT 'Unpaid',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `level_incomes`
--

LOCK TABLES `level_incomes` WRITE;
/*!40000 ALTER TABLE `level_incomes` DISABLE KEYS */;
INSERT INTO `level_incomes` VALUES (1,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-08 00:00:07','2026-09-08 00:00:07'),(2,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-09 00:00:08','2026-09-09 00:00:08'),(3,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-10 00:00:07','2026-09-10 00:00:07'),(4,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-11 00:00:07','2026-09-11 00:00:07'),(5,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-12 00:00:07','2026-09-12 00:00:07'),(6,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-13 00:00:06','2026-09-13 00:00:06'),(7,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-14 00:00:06','2026-09-14 00:00:06'),(8,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-15 00:00:07','2026-09-15 00:00:07'),(9,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-16 00:00:07','2026-09-16 00:00:07'),(10,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-17 00:00:07','2026-09-17 00:00:07'),(11,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-18 00:00:07','2026-09-18 00:00:07'),(12,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-19 00:00:07','2026-09-19 00:00:07'),(13,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-20 00:00:07','2026-09-20 00:00:07'),(14,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-21 00:00:07','2026-09-21 00:00:07'),(15,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-22 00:00:08','2026-09-22 00:00:08'),(16,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-23 00:00:07','2026-09-23 00:00:07'),(17,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-24 00:00:08','2026-09-24 00:00:08'),(18,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-25 00:00:07','2026-09-25 00:00:07'),(19,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-26 00:00:07','2026-09-26 00:00:07'),(20,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-27 00:00:08','2026-09-27 00:00:08'),(21,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-28 00:00:07','2026-09-28 00:00:07'),(22,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-29 00:00:07','2026-09-29 00:00:07'),(23,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-09-30 00:00:08','2026-09-30 00:00:08'),(24,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-01 00:00:07','2026-10-01 00:00:07'),(25,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-02 00:00:07','2026-10-02 00:00:07'),(26,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-03 00:00:07','2026-10-03 00:00:07'),(27,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-04 00:00:08','2026-10-04 00:00:08'),(28,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-05 00:00:08','2026-10-05 00:00:08'),(29,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-06 00:00:06','2026-10-06 00:00:06'),(30,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-07 00:00:07','2026-10-07 00:00:07'),(31,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-08 00:00:08','2026-10-08 00:00:08'),(32,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-09 00:00:07','2026-10-09 00:00:07'),(33,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-10 00:00:08','2026-10-10 00:00:08'),(34,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-11 00:00:07','2026-10-11 00:00:07'),(35,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-12 00:00:08','2026-10-12 00:00:08'),(36,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-13 00:00:07','2026-10-13 00:00:07'),(37,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-14 00:00:07','2026-10-14 00:00:07'),(38,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-15 00:00:07','2026-10-15 00:00:07'),(39,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-16 00:00:07','2026-10-16 00:00:07'),(40,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-17 00:00:09','2026-10-17 00:00:09'),(41,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-18 00:00:07','2026-10-18 00:00:07'),(42,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-19 00:00:08','2026-10-19 00:00:08'),(43,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-20 00:00:08','2026-10-20 00:00:08'),(44,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-21 00:00:08','2026-10-21 00:00:08'),(45,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-22 00:00:08','2026-10-22 00:00:08'),(46,'QRB123456','QRB942901','Sahaj','1',4,0.3333,0.0500,'Staking',15.00,0,'Paid','2026-10-23 00:00:08','2026-10-23 00:00:08');
/*!40000 ALTER TABLE `level_incomes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_details`
--

DROP TABLE IF EXISTS `member_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `memberid` varchar(255) DEFAULT NULL,
  `sponsorid` varchar(255) NOT NULL DEFAULT '',
  `placementid` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `mobile` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `txn_password` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `occupation` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `bank` varchar(100) DEFAULT NULL,
  `branch` varchar(100) DEFAULT NULL,
  `account_no` int(10) DEFAULT NULL,
  `ifsc` varchar(50) DEFAULT NULL,
  `holder` varchar(100) DEFAULT NULL,
  `wallet_address` varchar(255) DEFAULT NULL,
  `profile_image` text DEFAULT NULL,
  `position` enum('Left','Right') DEFAULT NULL,
  `left_count` int(10) NOT NULL DEFAULT 0,
  `right_count` int(10) DEFAULT 0,
  `left_amount` int(10) NOT NULL DEFAULT 0,
  `right_amount` int(10) NOT NULL DEFAULT 0,
  `left_balance` int(10) NOT NULL DEFAULT 0,
  `right_balance` int(10) NOT NULL DEFAULT 0,
  `left_id` varchar(20) DEFAULT NULL,
  `right_id` varchar(20) DEFAULT NULL,
  `package` int(12) DEFAULT 0,
  `package_name` varchar(255) DEFAULT NULL,
  `capped_income_earned` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `income_cap_limit` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `downline` int(20) NOT NULL DEFAULT 0,
  `rank` varchar(100) DEFAULT 'Basic',
  `status` enum('Active','Deactive','Temp','Blocked') NOT NULL DEFAULT 'Temp',
  `wallet` decimal(10,2) NOT NULL DEFAULT 0.00,
  `p2p_wallet` decimal(10,2) NOT NULL DEFAULT 0.00,
  `coin_wallet` decimal(10,2) NOT NULL DEFAULT 0.00,
  `self_biz` int(11) NOT NULL DEFAULT 0,
  `team_biz` int(11) NOT NULL DEFAULT 0,
  `rank_status` tinyint(3) DEFAULT 0,
  `kyc_status` enum('Verified','Submitted','Unverified') NOT NULL DEFAULT 'Unverified',
  `reward_status` tinyint(3) NOT NULL DEFAULT 0,
  `level_status` tinyint(3) NOT NULL DEFAULT 1,
  `package_activated_at` datetime DEFAULT NULL,
  `activated_at` datetime DEFAULT NULL,
  `activated_by` varchar(20) NOT NULL DEFAULT 'User',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_details`
--

LOCK TABLES `member_details` WRITE;
/*!40000 ALTER TABLE `member_details` DISABLE KEYS */;
INSERT INTO `member_details` VALUES (1,'QRB123456','Root','Root','Admin','8979708991','admin@gmail.com','$2y$12$uBMtUtHiO0HlbeYKA02S9uID/IzYk4mwWawNCLL2FXJ9FFEhHKk1a','$2y$12$eMYvOKDJZj3EQ7hDbGfx/.O2H/XNo4nwPxcGSN7RqtOZ1eTGd0ZOa','ABC','ABC','Male','ABC','1994-07-15','ABC','ABC','India','HDFC','khurja',2147483647,'HDFC0000738','abcd','cxbn2h3vc10n23cvcn201v32n10vc32',NULL,NULL,0,0,0,0,0,0,NULL,'',100,'Silver',0.00000000,0.00000000,4,'Basic','Active',23.47,0.00,0.00,100,200,0,'Submitted',0,0,'2025-05-30 12:51:36','2025-05-30 11:42:09','User','2024-11-01 11:29:37','2026-10-23 00:00:08'),(2,'QRB839609','QRB123456',NULL,'ARB','1234567890','business2022zoom@gmail.com','$2y$12$YrexQ8vewZsi8kk8SlsUmeU.tCv9qAMHN8RbuF/t3BZrXSQgC1gJi','$2y$12$tNe6e8stXa9tE7fMXp.BkuOy8.cWvIlQ3jUR.5KcArhLNarN1rXa6',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'GERMANY',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,NULL,NULL,1000,'Silver',0.00000000,0.00000000,1,'Basic','Active',0.00,0.00,0.00,1000,0,0,'Unverified',0,1,NULL,'2026-09-05 13:33:33','Admin','2026-09-03 16:13:40','2026-09-05 13:52:57'),(3,'QRB996056','QRB123456',NULL,'ARB2','1234567890','business2022zoom@gmail.com','$2y$12$MDg3OiuNfh5H4.w7aUx7N.0KxsxG4IgZE28c/KWnfIoTpzHigh7Fe','$2y$12$xVXJz2JsYzUl0Jhk/cgEpu2JOGvmk3lJHBLC1wD3p0tMfvL4kqc0u',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'GERMANY',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,NULL,NULL,100,'Silver',0.00000000,0.00000000,1,'Basic','Active',0.00,0.00,0.00,100,0,0,'Unverified',0,1,NULL,'2026-09-05 13:55:12','Admin','2026-09-04 14:02:00','2026-09-05 13:55:12'),(4,'QRB743327','QRB996056',NULL,'ARB3','1234567890','business2022zoom@gmail.com','$2y$12$oipnI/2wGBQ3puZlclJHs.ASjm0X4Qp5IrCnKMO/oh9bnPU9cfMOe','$2y$12$a7zjUpUZ7SeLG0Pid1YIOO9uHXc53jv0frc1r7xdsA60t44BCrXte',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'GERMANY',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,NULL,NULL,100,'Silver',0.00000000,0.00000000,1,'Basic','Active',0.00,0.00,0.00,100,0,0,'Unverified',0,1,NULL,'2026-09-05 13:55:00','Admin','2026-09-04 14:04:36','2026-09-05 13:55:00'),(5,'QRB886154','QRB839609',NULL,'Shaikh','8287362193','amanshaikhmlm@gmail.com','$2y$12$cqB2wm76/YuQ1YxPJlBKAOA86kbKIPmYvQyYPFQjtyaOk2idDAb0S','$2y$12$RA1GD31.xIdHZuLTDJMLjevsMQX65fpOAn1yFxfv3NS8eMJwgPmUe',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'INDIA',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,NULL,NULL,1000,'Silver',0.00000000,0.00000000,3,'Basic','Active',0.00,1000.00,0.00,1000,0,0,'Unverified',0,1,NULL,'2026-09-05 13:52:57','Admin','2026-09-04 14:57:48','2026-09-07 15:14:25'),(6,'QRB477931','QRB743327',NULL,'ARB4','1234567890','business2022zoom@gmail.com','$2y$12$oyrImzFLSdBYRIBehfyQdOd.zvW6/5DOqyRv5Y2CSfeyh3yxil5Nq','$2y$12$D38zlhP0/xIPnl4NEovM5.7WNvpJZnDIHiGc2WDqE9AXEuMcBdfpq',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'GERMANY',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,NULL,NULL,100,'Silver',0.00000000,0.00000000,1,'Basic','Active',0.00,0.00,0.00,100,0,0,'Unverified',0,1,NULL,'2026-09-05 13:54:49','Admin','2026-09-04 16:00:54','2026-09-05 16:10:02'),(7,'QRB826017','QRB886154',NULL,'Mohammad','9773972042','nightofking189@gmail.com','$2y$12$xhHU5yNRPDOE/VUeWu3I/ucJavnSsMmMGfEwnnbW.NM7Bw.Q7wvFG','$2y$12$Mbtqtb98m/IY/QbFCQ2PoetZ7MLxBt657JVEzQGQ6v2i/xwUK7i/a',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'INDIA',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,NULL,NULL,1000,'Silver',0.00000000,0.00000000,0,'Basic','Active',0.00,0.00,0.00,1000,0,0,'Unverified',0,1,NULL,'2026-09-05 13:53:38','Admin','2026-09-04 18:07:45','2026-09-05 13:53:38'),(8,'QRB357134','QRB886154',NULL,'Winner','9560193286','mahikhan02007@gmail.com','$2y$12$Re7IkpFergKoXq2wZY93DOf4u77NDDap9sbTBsp8Q3Qz.T1OfCnze','$2y$12$6mmv.p0sIzStbkhVidrhSuP5qSbki7O9w8PT5zJok6f8pGo5jUUQ6',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'INDIA',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,NULL,NULL,1000,'Silver',0.00000000,0.00000000,0,'Basic','Active',0.00,0.00,0.00,1000,0,0,'Unverified',0,1,NULL,'2026-09-05 13:54:04','Admin','2026-09-04 19:42:30','2026-09-05 13:54:04'),(9,'QRB809597','QRB886154',NULL,'Pratima Yadav','8471092120','gprabhat258@gmail.com','$2y$12$0lk8zW7qp6/oSpGLZGVJ/..rUMTPVWTE1iol2dLAsUo2bOZplMts2','$2y$12$PRtxXB.Kr1iHTXoVZMBKu.fIEz5k5eIjwwCVry95XEVK24KzX6qMe',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'INDIA',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,NULL,NULL,0,NULL,0.00000000,0.00000000,0,'Basic','Temp',0.00,0.00,0.00,0,0,0,'Unverified',0,1,NULL,NULL,'User','2026-09-04 20:52:49','2026-09-04 20:52:49'),(10,'QRB383651','QRB886154',NULL,'Mahendra','9586997001','alwayshighthink777@gmail.com','$2y$12$B3SJWk4UU.cAYij8I8FO1uSG.HSj/u3lY8THLuAn/XUKIrmxw5FYi','$2y$12$DU1xfIi0yDfjwqhOyZR.2.igMPFNpWFwAypksjD6X1aBkMTqqxhc.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'INDIA',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,NULL,NULL,1000,'Silver',0.00000000,0.00000000,0,'Basic','Active',0.00,0.00,0.00,1000,0,0,'Unverified',0,1,NULL,'2026-09-05 13:54:29','Admin','2026-09-04 23:05:47','2026-09-05 13:54:29'),(11,'QRB993502','QRB123456',NULL,'Abhi','1234567890','abhijot2024mkt@gmail.com','$2y$12$0ThvEnCzp1OMkKknHbRJwuGPTKowBVIMeF7CzjybaN3peV2KQSjQu','$2y$12$vckxFQSiTgEPl55cYjdU1uiR12Psq58bHyYgfgZ2FCWseV6ArOnNO',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'CANADA',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,NULL,NULL,100,'Silver',0.00000000,0.00000000,0,'Basic','Active',0.00,0.00,0.00,100,0,0,'Unverified',0,1,NULL,'2026-09-05 16:11:32','Admin','2026-09-05 14:08:07','2026-09-05 16:11:32'),(12,'QRB511909','QRB993502',NULL,'Abhi2','1234567890','abhijot2024mkt@gmail.com','$2y$12$zKV23R0bkjHAXuomiXmKjOQU0VT6f45i2W8ENfXMX2JOdQv2oTkem','$2y$12$t532/AzLHb0HL26REgbSO.vXsmHNduYn7VqFloAU1jSlxs58LibSa',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'INDIA',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,NULL,NULL,0,NULL,0.00000000,0.00000000,0,'Basic','Temp',0.00,0.00,0.00,0,0,0,'Unverified',0,1,NULL,NULL,'User','2026-09-05 14:14:08','2026-09-05 14:14:08'),(13,'QRB942901','QRB123456',NULL,'Sahaj','1234567890','abhijot2024mkt@gmail.com','$2y$12$UjmyHAHj5au/3z4/EN2BRevYyVLXmmRB94DxuPW8rzrdxs4jHWEee','$2y$12$2fqZvJnc63SQbINeJz.XROFe0sahu26C72Q.t5zMhNLWd7ybQ4l2e',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'THAILAND',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,NULL,NULL,100,'Silver',0.00000000,0.00000000,1,'Basic','Active',15.18,400.00,0.00,100,100,0,'Unverified',0,1,NULL,'2026-09-07 15:12:14','User','2026-09-05 14:22:30','2026-10-23 00:00:06'),(14,'QRB828613','QRB942901',NULL,'Lakhvinder','1234567890','sahajpreet2023@gmail.com','$2y$12$mnfTN9hRS9pi8vwZtf0AEuQUfK41txcH/ZIwO2tZVMk5Y70z1HuIi','$2y$12$7dkRVSGsGRYmxvTfaoGYkeT0UqCTI0.2MIDH7jAzFLO100HfdC3oy',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'GERMANY',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,NULL,NULL,1000,'Silver',0.00000000,0.00000000,1,'Basic','Active',0.00,0.00,0.00,1000,0,0,'Unverified',0,1,NULL,'2026-09-05 16:09:15','Admin','2026-09-05 14:26:46','2026-09-05 16:10:26'),(15,'QRB765627','QRB828613',NULL,'Mushtak Diwan','9624019444','mushtak.diwan@gmail.com','$2y$12$BduycRhbVCriQXnEz4S7tuDu18u9tcOwhOceDi9w4nPaxDwT3eTFO','$2y$12$/yxow/2giTuzvqJJbsF5Yunu9AS1tj2TRbdVZumpa6gxJUQWKkdXm',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'INDIA',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,NULL,NULL,1000,'Silver',0.00000000,0.00000000,0,'Basic','Active',0.00,0.00,0.00,1000,0,0,'Unverified',0,1,NULL,'2026-09-05 16:10:26','Admin','2026-09-05 14:32:06','2026-09-05 16:10:26'),(16,'QRB225986','QRB477931',NULL,'Manish Raghav','9599210050','onemanishraghav@gmail.com','$2y$12$lTW3yBOu9ogzGC1xYrUgj.7SXuPK3szZFLu03kgTr6NnYJcEsJ1BK','$2y$12$snUTAibI7tQ0Z4CKlOoVG.DdRgh0rwKiX9xiSf7IzuAzlrLyfmG76',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'INDIA',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,NULL,NULL,1000,'Silver',0.00000000,0.00000000,0,'Basic','Active',0.00,0.00,0.00,1000,0,0,'Unverified',0,1,NULL,'2026-09-05 16:10:02','Admin','2026-09-05 14:59:09','2026-09-05 16:10:02'),(17,'QRB847047','QRB225986',NULL,'Nikhil Sharma','9910394008','nikhilsharma604@gmail.com','$2y$12$lJRxcFdXNkeYsz.c2iyvOu3vmsEl8s.ZJB.czNNpD9o3kAzLVueR.','$2y$12$obIR1YikLuiDaJ85Q7MMhOJGqwXKavLwgUGqybtkfZY6SHGeIpWgG',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'INDIA',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,NULL,NULL,0,NULL,0.00000000,0.00000000,0,'Basic','Temp',0.00,0.00,0.00,0,0,0,'Unverified',0,1,NULL,NULL,'User','2026-09-05 15:52:22','2026-09-05 15:52:22'),(18,'QRB874606','QRB886154',NULL,'Harish Kumar','9324606249','kkumarharish91@gmail.com','$2y$12$mpdKr5P7r2TzHDbq0BFFFedaQ3MVXKtfqkkzxzK/8Uij.n19rTLJy','$2y$12$T2hylPnjsVsA.aJKyWE8xutl5c/KG7G1WbxufJphps9WOw0bv6XyO',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'INDIA',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,NULL,NULL,0,NULL,0.00000000,0.00000000,0,'Basic','Temp',0.00,0.00,0.00,0,0,0,'Unverified',0,1,NULL,NULL,'User','2026-09-07 19:55:07','2026-09-07 19:55:07'),(19,'QRB829734','QRB886154',NULL,'Deepak Kr','7866826354','dkumar8687@gmail.com','$2y$12$kaSY5sJtAeRt//xe8e6Gk./6POSAEzWIN4/J5yaJNG9TJ/.qQxcPu','$2y$12$XvzlatEOD.PZkzKmDxCxVuuLjF0XebF6SeaoZCbQbTSEkT.97uOcO',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'INDIA',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,NULL,NULL,0,NULL,0.00000000,0.00000000,0,'Basic','Temp',0.00,0.00,0.00,0,0,0,'Unverified',0,1,NULL,NULL,'User','2026-09-07 19:58:00','2026-09-07 19:58:00'),(20,'QRB163211','QRB511909',NULL,'Narendra singh','9728939456','singhnarender750@gmail.com','$2y$12$qANwWRpptxW6NHlIiR6hPO0hanWc/WjieOng8SeYz5PNGmivNqwcy','$2y$12$Rwm4Jdpn8sNKTyMjbysFzOfTHHGUkJ1h0x8dBfLBskUE54UtV/vcm',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'INDIA',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,NULL,NULL,0,NULL,0.00000000,0.00000000,0,'Basic','Temp',0.00,0.00,0.00,0,0,0,'Unverified',0,1,NULL,NULL,'User','2026-09-11 20:49:26','2026-09-11 20:49:26');
/*!40000 ALTER TABLE `member_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nowpayment_payments`
--

DROP TABLE IF EXISTS `nowpayment_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nowpayment_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_id` varchar(255) NOT NULL,
  `order_id` varchar(255) DEFAULT NULL,
  `memberid` varchar(255) NOT NULL,
  `amount` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nowpayment_payments`
--

LOCK TABLES `nowpayment_payments` WRITE;
/*!40000 ALTER TABLE `nowpayment_payments` DISABLE KEYS */;
INSERT INTO `nowpayment_payments` VALUES (1,'4385189526','Yu1IvYJT2Dp1CfKpwqEN2667','QRB996056','100','WAITING','2026-09-05 13:59:53','2026-09-05 13:59:53'),(2,'4977370525','LIE2C0vcvoEDtmfcLHyt2023','QRB123456','1500','WAITING','2026-10-13 21:21:51','2026-10-13 21:21:51');
/*!40000 ALTER TABLE `nowpayment_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `p2p_fund_transfers`
--

DROP TABLE IF EXISTS `p2p_fund_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `p2p_fund_transfers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `senderid` varchar(30) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `deduction` decimal(10,2) NOT NULL,
  `net_amount` decimal(10,2) NOT NULL,
  `receiverid` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `p2p_fund_transfers`
--

LOCK TABLES `p2p_fund_transfers` WRITE;
/*!40000 ALTER TABLE `p2p_fund_transfers` DISABLE KEYS */;
/*!40000 ALTER TABLE `p2p_fund_transfers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `package_details`
--

DROP TABLE IF EXISTS `package_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `package_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `txn_date` datetime NOT NULL,
  `order_id` varchar(255) NOT NULL,
  `memberid` varchar(255) NOT NULL,
  `activated_by` varchar(255) NOT NULL DEFAULT 'User',
  `package` varchar(50) DEFAULT NULL,
  `package_type` varchar(255) DEFAULT NULL,
  `package_value` int(5) NOT NULL,
  `txnid` varchar(255) DEFAULT NULL,
  `payment_mode` varchar(255) NOT NULL,
  `status` varchar(255) DEFAULT 'Active',
  `activated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `package_details`
--

LOCK TABLES `package_details` WRITE;
/*!40000 ALTER TABLE `package_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `package_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `packages`
--

DROP TABLE IF EXISTS `packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `amount` decimal(20,8) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `packages`
--

LOCK TABLES `packages` WRITE;
/*!40000 ALTER TABLE `packages` DISABLE KEYS */;
/*!40000 ALTER TABLE `packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_datas`
--

DROP TABLE IF EXISTS `payment_datas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_datas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_id` varchar(255) NOT NULL,
  `payment_status` varchar(255) NOT NULL,
  `price_amount` decimal(10,4) NOT NULL,
  `pay_amount` decimal(10,4) NOT NULL,
  `actually_paid` decimal(10,4) NOT NULL,
  `order_id` varchar(255) NOT NULL,
  `purchase_id` varchar(255) NOT NULL,
  `outcome_amount` decimal(10,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_datas`
--

LOCK TABLES `payment_datas` WRITE;
/*!40000 ALTER TABLE `payment_datas` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_datas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_details`
--

DROP TABLE IF EXISTS `payment_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank` varchar(255) DEFAULT NULL,
  `ifsc` varchar(255) DEFAULT NULL,
  `account` varchar(255) DEFAULT NULL,
  `upi` varchar(255) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `qrcode` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Not Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_details`
--

LOCK TABLES `payment_details` WRITE;
/*!40000 ALTER TABLE `payment_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reward_incomes`
--

DROP TABLE IF EXISTS `reward_incomes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reward_incomes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `memberid` varchar(30) NOT NULL,
  `rank` varchar(100) NOT NULL,
  `reward` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `team_biz` decimal(18,2) NOT NULL DEFAULT 0.00,
  `type` varchar(50) NOT NULL DEFAULT '0.0000',
  `status` varchar(50) NOT NULL,
  `reward_status` tinyint(3) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reward_incomes`
--

LOCK TABLES `reward_incomes` WRITE;
/*!40000 ALTER TABLE `reward_incomes` DISABLE KEYS */;
/*!40000 ALTER TABLE `reward_incomes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `set_rates`
--

DROP TABLE IF EXISTS `set_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `set_rates` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `roi_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `set_rates`
--

LOCK TABLES `set_rates` WRITE;
/*!40000 ALTER TABLE `set_rates` DISABLE KEYS */;
INSERT INTO `set_rates` VALUES (1,1.00,'2024-10-05 04:56:44','2026-06-25 12:25:50');
/*!40000 ALTER TABLE `set_rates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'dashboard_popup_image','','2026-08-22 18:00:44','2026-09-01 21:59:43'),(2,'dashboard_popup_title','wifhjsdvns','2026-08-22 18:00:44','2026-08-22 18:02:49'),(3,'dashboard_popup_link','https://qunatumarb.world/member/staking','2026-08-22 18:00:44','2026-08-22 18:02:49'),(4,'dashboard_popup_status','0','2026-08-22 18:00:44','2026-09-01 21:59:43');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staking_details`
--

DROP TABLE IF EXISTS `staking_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staking_details` (
  `id` int(50) NOT NULL AUTO_INCREMENT,
  `memberid` varchar(255) NOT NULL,
  `invest_date` datetime NOT NULL,
  `break_date` datetime DEFAULT NULL,
  `invest_amount` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `package` varchar(255) NOT NULL,
  `package_name` varchar(255) NOT NULL,
  `is_upgrade` tinyint(1) NOT NULL DEFAULT 0,
  `type` varchar(255) DEFAULT NULL,
  `installments` int(5) NOT NULL DEFAULT 0,
  `referral_installments` int(5) DEFAULT 0,
  `break_amount` decimal(10,4) DEFAULT 0.0000,
  `deduction` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `rate` decimal(10,2) DEFAULT 0.00,
  `activated_by` varchar(255) DEFAULT 'User',
  `status` varchar(255) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staking_details`
--

LOCK TABLES `staking_details` WRITE;
/*!40000 ALTER TABLE `staking_details` DISABLE KEYS */;
INSERT INTO `staking_details` VALUES (1,'QRB123456','2026-08-26 16:25:26',NULL,100.0000,'100','Silver',0,'Staking Income',49,0,0.0000,0.0000,10.00,'User','Active','2026-08-26 16:25:26','2026-10-23 00:00:06'),(2,'QRB839609','2026-09-05 13:33:33',NULL,1000.0000,'Silver','Silver',0,NULL,0,0,0.0000,0.0000,0.00,'Admin','Deactive','2026-09-05 13:33:33','2026-09-05 13:33:33'),(3,'QRB886154','2026-09-05 13:52:57',NULL,1000.0000,'Silver','Silver',0,NULL,0,0,0.0000,0.0000,0.00,'Admin','Deactive','2026-09-05 13:52:57','2026-09-05 13:52:57'),(4,'QRB826017','2026-09-05 13:53:38',NULL,1000.0000,'Silver','Silver',0,NULL,0,0,0.0000,0.0000,0.00,'Admin','Deactive','2026-09-05 13:53:38','2026-09-05 13:53:38'),(5,'QRB357134','2026-09-05 13:54:04',NULL,1000.0000,'Silver','Silver',0,NULL,0,0,0.0000,0.0000,0.00,'Admin','Deactive','2026-09-05 13:54:04','2026-09-05 13:54:04'),(6,'QRB383651','2026-09-05 13:54:29',NULL,1000.0000,'Silver','Silver',0,NULL,0,0,0.0000,0.0000,0.00,'Admin','Deactive','2026-09-05 13:54:29','2026-09-05 13:54:29'),(7,'QRB477931','2026-09-05 13:54:49',NULL,100.0000,'Silver','Silver',0,NULL,0,0,0.0000,0.0000,0.00,'Admin','Deactive','2026-09-05 13:54:49','2026-09-05 13:54:49'),(8,'QRB743327','2026-09-05 13:55:00',NULL,100.0000,'Silver','Silver',0,NULL,0,0,0.0000,0.0000,0.00,'Admin','Deactive','2026-09-05 13:55:00','2026-09-05 13:55:00'),(9,'QRB996056','2026-09-05 13:55:12',NULL,100.0000,'Silver','Silver',0,NULL,0,0,0.0000,0.0000,0.00,'Admin','Deactive','2026-09-05 13:55:12','2026-09-05 13:55:12'),(10,'QRB828613','2026-09-05 16:09:15',NULL,1000.0000,'Silver','Silver',0,NULL,0,0,0.0000,0.0000,0.00,'Admin','Deactive','2026-09-05 16:09:15','2026-09-05 16:09:15'),(11,'QRB225986','2026-09-05 16:10:02',NULL,1000.0000,'Silver','Silver',0,NULL,0,0,0.0000,0.0000,0.00,'Admin','Deactive','2026-09-05 16:10:02','2026-09-05 16:10:02'),(12,'QRB765627','2026-09-05 16:10:26',NULL,1000.0000,'Silver','Silver',0,NULL,0,0,0.0000,0.0000,0.00,'Admin','Deactive','2026-09-05 16:10:26','2026-09-05 16:10:26'),(13,'QRB993502','2026-09-05 16:11:32',NULL,100.0000,'Silver','Silver',0,NULL,0,0,0.0000,0.0000,0.00,'Admin','Deactive','2026-09-05 16:11:32','2026-09-05 16:11:32'),(14,'QRB942901','2026-09-07 15:12:14',NULL,100.0000,'100','Silver',0,'Staking Income',46,0,0.0000,0.0000,10.00,'User','Active','2026-09-07 15:12:14','2026-10-23 00:00:06');
/*!40000 ALTER TABLE `staking_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staking_incomes`
--

DROP TABLE IF EXISTS `staking_incomes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staking_incomes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `memberid` varchar(20) NOT NULL,
  `total_investment` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `type` varchar(255) DEFAULT NULL,
  `installment` int(10) NOT NULL DEFAULT 0,
  `level_status` tinyint(3) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'Paid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staking_incomes`
--

LOCK TABLES `staking_incomes` WRITE;
/*!40000 ALTER TABLE `staking_incomes` DISABLE KEYS */;
INSERT INTO `staking_incomes` VALUES (1,'2026-09-05','QRB123456',100.0000,0.33,0.3333,NULL,1,1,'Paid','2026-09-05 00:00:05','2026-09-05 00:00:06'),(2,'2026-09-06','QRB123456',100.0000,0.33,0.3333,NULL,2,1,'Paid','2026-09-06 00:00:05','2026-09-06 00:00:07'),(3,'2026-09-07','QRB123456',100.0000,0.33,0.3333,NULL,3,1,'Paid','2026-09-07 00:00:05','2026-09-07 00:00:07'),(4,'2026-09-08','QRB123456',100.0000,0.33,0.3333,NULL,4,1,'Paid','2026-09-08 00:00:05','2026-09-08 00:00:07'),(5,'2026-09-08','QRB942901',100.0000,0.33,0.3333,NULL,1,1,'Paid','2026-09-08 00:00:05','2026-09-08 00:00:07'),(6,'2026-09-09','QRB123456',100.0000,0.33,0.3333,NULL,5,1,'Paid','2026-09-09 00:00:06','2026-09-09 00:00:07'),(7,'2026-09-09','QRB942901',100.0000,0.33,0.3333,NULL,2,1,'Paid','2026-09-09 00:00:06','2026-09-09 00:00:08'),(8,'2026-09-10','QRB123456',100.0000,0.33,0.3333,NULL,6,1,'Paid','2026-09-10 00:00:05','2026-09-10 00:00:07'),(9,'2026-09-10','QRB942901',100.0000,0.33,0.3333,NULL,3,1,'Paid','2026-09-10 00:00:05','2026-09-10 00:00:07'),(10,'2026-09-11','QRB123456',100.0000,0.33,0.3333,NULL,7,1,'Paid','2026-09-11 00:00:05','2026-09-11 00:00:07'),(11,'2026-09-11','QRB942901',100.0000,0.33,0.3333,NULL,4,1,'Paid','2026-09-11 00:00:05','2026-09-11 00:00:07'),(12,'2026-09-12','QRB123456',100.0000,0.33,0.3333,NULL,8,1,'Paid','2026-09-12 00:00:06','2026-09-12 00:00:07'),(13,'2026-09-12','QRB942901',100.0000,0.33,0.3333,NULL,5,1,'Paid','2026-09-12 00:00:06','2026-09-12 00:00:07'),(14,'2026-09-13','QRB123456',100.0000,0.33,0.3333,NULL,9,1,'Paid','2026-09-13 00:00:05','2026-09-13 00:00:06'),(15,'2026-09-13','QRB942901',100.0000,0.33,0.3333,NULL,6,1,'Paid','2026-09-13 00:00:05','2026-09-13 00:00:06'),(16,'2026-09-14','QRB123456',100.0000,0.33,0.3333,NULL,10,1,'Paid','2026-09-14 00:00:05','2026-09-14 00:00:06'),(17,'2026-09-14','QRB942901',100.0000,0.33,0.3333,NULL,7,1,'Paid','2026-09-14 00:00:05','2026-09-14 00:00:06'),(18,'2026-09-15','QRB123456',100.0000,0.33,0.3333,NULL,11,1,'Paid','2026-09-15 00:00:05','2026-09-15 00:00:06'),(19,'2026-09-15','QRB942901',100.0000,0.33,0.3333,NULL,8,1,'Paid','2026-09-15 00:00:05','2026-09-15 00:00:07'),(20,'2026-09-16','QRB123456',100.0000,0.33,0.3333,NULL,12,1,'Paid','2026-09-16 00:00:05','2026-09-16 00:00:07'),(21,'2026-09-16','QRB942901',100.0000,0.33,0.3333,NULL,9,1,'Paid','2026-09-16 00:00:05','2026-09-16 00:00:07'),(22,'2026-09-17','QRB123456',100.0000,0.33,0.3333,NULL,13,1,'Paid','2026-09-17 00:00:06','2026-09-17 00:00:07'),(23,'2026-09-17','QRB942901',100.0000,0.33,0.3333,NULL,10,1,'Paid','2026-09-17 00:00:06','2026-09-17 00:00:07'),(24,'2026-09-18','QRB123456',100.0000,0.33,0.3333,NULL,14,1,'Paid','2026-09-18 00:00:05','2026-09-18 00:00:07'),(25,'2026-09-18','QRB942901',100.0000,0.33,0.3333,NULL,11,1,'Paid','2026-09-18 00:00:05','2026-09-18 00:00:07'),(26,'2026-09-19','QRB123456',100.0000,0.33,0.3333,NULL,15,1,'Paid','2026-09-19 00:00:05','2026-09-19 00:00:07'),(27,'2026-09-19','QRB942901',100.0000,0.33,0.3333,NULL,12,1,'Paid','2026-09-19 00:00:05','2026-09-19 00:00:07'),(28,'2026-09-20','QRB123456',100.0000,0.33,0.3333,NULL,16,1,'Paid','2026-09-20 00:00:05','2026-09-20 00:00:07'),(29,'2026-09-20','QRB942901',100.0000,0.33,0.3333,NULL,13,1,'Paid','2026-09-20 00:00:05','2026-09-20 00:00:07'),(30,'2026-09-21','QRB123456',100.0000,0.33,0.3333,NULL,17,1,'Paid','2026-09-21 00:00:05','2026-09-21 00:00:07'),(31,'2026-09-21','QRB942901',100.0000,0.33,0.3333,NULL,14,1,'Paid','2026-09-21 00:00:05','2026-09-21 00:00:07'),(32,'2026-09-22','QRB123456',100.0000,0.33,0.3333,NULL,18,1,'Paid','2026-09-22 00:00:06','2026-09-22 00:00:08'),(33,'2026-09-22','QRB942901',100.0000,0.33,0.3333,NULL,15,1,'Paid','2026-09-22 00:00:06','2026-09-22 00:00:08'),(34,'2026-09-23','QRB123456',100.0000,0.33,0.3333,NULL,19,1,'Paid','2026-09-23 00:00:05','2026-09-23 00:00:07'),(35,'2026-09-23','QRB942901',100.0000,0.33,0.3333,NULL,16,1,'Paid','2026-09-23 00:00:05','2026-09-23 00:00:07'),(36,'2026-09-24','QRB123456',100.0000,0.33,0.3333,NULL,20,1,'Paid','2026-09-24 00:00:06','2026-09-24 00:00:08'),(37,'2026-09-24','QRB942901',100.0000,0.33,0.3333,NULL,17,1,'Paid','2026-09-24 00:00:06','2026-09-24 00:00:08'),(38,'2026-09-25','QRB123456',100.0000,0.33,0.3333,NULL,21,1,'Paid','2026-09-25 00:00:05','2026-09-25 00:00:07'),(39,'2026-09-25','QRB942901',100.0000,0.33,0.3333,NULL,18,1,'Paid','2026-09-25 00:00:06','2026-09-25 00:00:07'),(40,'2026-09-26','QRB123456',100.0000,0.33,0.3333,NULL,22,1,'Paid','2026-09-26 00:00:05','2026-09-26 00:00:07'),(41,'2026-09-26','QRB942901',100.0000,0.33,0.3333,NULL,19,1,'Paid','2026-09-26 00:00:05','2026-09-26 00:00:07'),(42,'2026-09-27','QRB123456',100.0000,0.33,0.3333,NULL,23,1,'Paid','2026-09-27 00:00:06','2026-09-27 00:00:08'),(43,'2026-09-27','QRB942901',100.0000,0.33,0.3333,NULL,20,1,'Paid','2026-09-27 00:00:06','2026-09-27 00:00:08'),(44,'2026-09-28','QRB123456',100.0000,0.33,0.3333,NULL,24,1,'Paid','2026-09-28 00:00:06','2026-09-28 00:00:07'),(45,'2026-09-28','QRB942901',100.0000,0.33,0.3333,NULL,21,1,'Paid','2026-09-28 00:00:06','2026-09-28 00:00:07'),(46,'2026-09-29','QRB123456',100.0000,0.33,0.3333,NULL,25,1,'Paid','2026-09-29 00:00:05','2026-09-29 00:00:07'),(47,'2026-09-29','QRB942901',100.0000,0.33,0.3333,NULL,22,1,'Paid','2026-09-29 00:00:06','2026-09-29 00:00:07'),(48,'2026-09-30','QRB123456',100.0000,0.33,0.3333,NULL,26,1,'Paid','2026-09-30 00:00:06','2026-09-30 00:00:08'),(49,'2026-09-30','QRB942901',100.0000,0.33,0.3333,NULL,23,1,'Paid','2026-09-30 00:00:06','2026-09-30 00:00:08'),(50,'2026-10-01','QRB123456',100.0000,0.33,0.3333,NULL,27,1,'Paid','2026-10-01 00:00:05','2026-10-01 00:00:07'),(51,'2026-10-01','QRB942901',100.0000,0.33,0.3333,NULL,24,1,'Paid','2026-10-01 00:00:05','2026-10-01 00:00:07'),(52,'2026-10-02','QRB123456',100.0000,0.33,0.3333,NULL,28,1,'Paid','2026-10-02 00:00:05','2026-10-02 00:00:07'),(53,'2026-10-02','QRB942901',100.0000,0.33,0.3333,NULL,25,1,'Paid','2026-10-02 00:00:05','2026-10-02 00:00:07'),(54,'2026-10-03','QRB123456',100.0000,0.33,0.3333,NULL,29,1,'Paid','2026-10-03 00:00:06','2026-10-03 00:00:07'),(55,'2026-10-03','QRB942901',100.0000,0.33,0.3333,NULL,26,1,'Paid','2026-10-03 00:00:06','2026-10-03 00:00:07'),(56,'2026-10-04','QRB123456',100.0000,0.33,0.3333,NULL,30,1,'Paid','2026-10-04 00:00:06','2026-10-04 00:00:08'),(57,'2026-10-04','QRB942901',100.0000,0.33,0.3333,NULL,27,1,'Paid','2026-10-04 00:00:06','2026-10-04 00:00:08'),(58,'2026-10-05','QRB123456',100.0000,0.33,0.3333,NULL,31,1,'Paid','2026-10-05 00:00:06','2026-10-05 00:00:08'),(59,'2026-10-05','QRB942901',100.0000,0.33,0.3333,NULL,28,1,'Paid','2026-10-05 00:00:06','2026-10-05 00:00:08'),(60,'2026-10-06','QRB123456',100.0000,0.33,0.3333,NULL,32,1,'Paid','2026-10-06 00:00:03','2026-10-06 00:00:06'),(61,'2026-10-06','QRB942901',100.0000,0.33,0.3333,NULL,29,1,'Paid','2026-10-06 00:00:03','2026-10-06 00:00:06'),(62,'2026-10-07','QRB123456',100.0000,0.33,0.3333,NULL,33,1,'Paid','2026-10-07 00:00:06','2026-10-07 00:00:07'),(63,'2026-10-07','QRB942901',100.0000,0.33,0.3333,NULL,30,1,'Paid','2026-10-07 00:00:06','2026-10-07 00:00:07'),(64,'2026-10-08','QRB123456',100.0000,0.33,0.3333,NULL,34,1,'Paid','2026-10-08 00:00:06','2026-10-08 00:00:08'),(65,'2026-10-08','QRB942901',100.0000,0.33,0.3333,NULL,31,1,'Paid','2026-10-08 00:00:06','2026-10-08 00:00:08'),(66,'2026-10-09','QRB123456',100.0000,0.33,0.3333,NULL,35,1,'Paid','2026-10-09 00:00:05','2026-10-09 00:00:06'),(67,'2026-10-09','QRB942901',100.0000,0.33,0.3333,NULL,32,1,'Paid','2026-10-09 00:00:05','2026-10-09 00:00:07'),(68,'2026-10-10','QRB123456',100.0000,0.33,0.3333,NULL,36,1,'Paid','2026-10-10 00:00:06','2026-10-10 00:00:08'),(69,'2026-10-10','QRB942901',100.0000,0.33,0.3333,NULL,33,1,'Paid','2026-10-10 00:00:06','2026-10-10 00:00:08'),(70,'2026-10-11','QRB123456',100.0000,0.33,0.3333,NULL,37,1,'Paid','2026-10-11 00:00:06','2026-10-11 00:00:07'),(71,'2026-10-11','QRB942901',100.0000,0.33,0.3333,NULL,34,1,'Paid','2026-10-11 00:00:06','2026-10-11 00:00:07'),(72,'2026-10-12','QRB123456',100.0000,0.33,0.3333,NULL,38,1,'Paid','2026-10-12 00:00:06','2026-10-12 00:00:08'),(73,'2026-10-12','QRB942901',100.0000,0.33,0.3333,NULL,35,1,'Paid','2026-10-12 00:00:06','2026-10-12 00:00:08'),(74,'2026-10-13','QRB123456',100.0000,0.33,0.3333,NULL,39,1,'Paid','2026-10-13 00:00:06','2026-10-13 00:00:07'),(75,'2026-10-13','QRB942901',100.0000,0.33,0.3333,NULL,36,1,'Paid','2026-10-13 00:00:06','2026-10-13 00:00:07'),(76,'2026-10-14','QRB123456',100.0000,0.33,0.3333,NULL,40,1,'Paid','2026-10-14 00:00:05','2026-10-14 00:00:07'),(77,'2026-10-14','QRB942901',100.0000,0.33,0.3333,NULL,37,1,'Paid','2026-10-14 00:00:05','2026-10-14 00:00:07'),(78,'2026-10-15','QRB123456',100.0000,0.33,0.3333,NULL,41,1,'Paid','2026-10-15 00:00:06','2026-10-15 00:00:07'),(79,'2026-10-15','QRB942901',100.0000,0.33,0.3333,NULL,38,1,'Paid','2026-10-15 00:00:06','2026-10-15 00:00:07'),(80,'2026-10-16','QRB123456',100.0000,0.33,0.3333,NULL,42,1,'Paid','2026-10-16 00:00:05','2026-10-16 00:00:07'),(81,'2026-10-16','QRB942901',100.0000,0.33,0.3333,NULL,39,1,'Paid','2026-10-16 00:00:05','2026-10-16 00:00:07'),(82,'2026-10-17','QRB123456',100.0000,0.33,0.3333,NULL,43,1,'Paid','2026-10-17 00:00:07','2026-10-17 00:00:09'),(83,'2026-10-17','QRB942901',100.0000,0.33,0.3333,NULL,40,1,'Paid','2026-10-17 00:00:07','2026-10-17 00:00:09'),(84,'2026-10-18','QRB123456',100.0000,0.33,0.3333,NULL,44,1,'Paid','2026-10-18 00:00:05','2026-10-18 00:00:07'),(85,'2026-10-18','QRB942901',100.0000,0.33,0.3333,NULL,41,1,'Paid','2026-10-18 00:00:05','2026-10-18 00:00:07'),(86,'2026-10-19','QRB123456',100.0000,0.33,0.3333,NULL,45,1,'Paid','2026-10-19 00:00:06','2026-10-19 00:00:08'),(87,'2026-10-19','QRB942901',100.0000,0.33,0.3333,NULL,42,1,'Paid','2026-10-19 00:00:06','2026-10-19 00:00:08'),(88,'2026-10-20','QRB123456',100.0000,0.33,0.3333,NULL,46,1,'Paid','2026-10-20 00:00:06','2026-10-20 00:00:08'),(89,'2026-10-20','QRB942901',100.0000,0.33,0.3333,NULL,43,1,'Paid','2026-10-20 00:00:06','2026-10-20 00:00:08'),(90,'2026-10-21','QRB123456',100.0000,0.33,0.3333,NULL,47,1,'Paid','2026-10-21 00:00:06','2026-10-21 00:00:08'),(91,'2026-10-21','QRB942901',100.0000,0.33,0.3333,NULL,44,1,'Paid','2026-10-21 00:00:06','2026-10-21 00:00:08'),(92,'2026-10-22','QRB123456',100.0000,0.33,0.3333,NULL,48,1,'Paid','2026-10-22 00:00:06','2026-10-22 00:00:08'),(93,'2026-10-22','QRB942901',100.0000,0.33,0.3333,NULL,45,1,'Paid','2026-10-22 00:00:06','2026-10-22 00:00:08'),(94,'2026-10-23','QRB123456',100.0000,0.33,0.3333,NULL,49,1,'Paid','2026-10-23 00:00:06','2026-10-23 00:00:08'),(95,'2026-10-23','QRB942901',100.0000,0.33,0.3333,NULL,46,1,'Paid','2026-10-23 00:00:06','2026-10-23 00:00:08');
/*!40000 ALTER TABLE `staking_incomes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_tickets`
--

DROP TABLE IF EXISTS `support_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` varchar(50) NOT NULL,
  `memberid` varchar(50) NOT NULL,
  `masterid` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `support_status` enum('New','Replied') NOT NULL DEFAULT 'New',
  `status` enum('Open','Closed') NOT NULL DEFAULT 'Open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_tickets`
--

LOCK TABLES `support_tickets` WRITE;
/*!40000 ALTER TABLE `support_tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_texts`
--

DROP TABLE IF EXISTS `ticket_texts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_texts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` varchar(50) NOT NULL,
  `written_by` enum('Admin','Member') DEFAULT NULL,
  `text` varchar(1000) NOT NULL,
  `status` enum('Unread','Read') NOT NULL DEFAULT 'Unread',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_texts`
--

LOCK TABLES `ticket_texts` WRITE;
/*!40000 ALTER TABLE `ticket_texts` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_texts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `upline_members`
--

DROP TABLE IF EXISTS `upline_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `upline_members` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `memberid` varchar(255) NOT NULL,
  `upline_1` varchar(255) DEFAULT NULL,
  `upline_2` varchar(255) DEFAULT NULL,
  `upline_3` varchar(255) DEFAULT NULL,
  `upline_4` varchar(255) DEFAULT NULL,
  `upline_5` varchar(255) DEFAULT NULL,
  `upline_6` varchar(255) DEFAULT NULL,
  `upline_7` varchar(255) DEFAULT NULL,
  `upline_8` varchar(255) DEFAULT NULL,
  `upline_9` varchar(255) DEFAULT NULL,
  `upline_10` varchar(255) DEFAULT NULL,
  `upline_11` varchar(255) DEFAULT NULL,
  `upline_12` varchar(255) DEFAULT NULL,
  `upline_13` varchar(255) DEFAULT NULL,
  `upline_14` varchar(255) DEFAULT NULL,
  `upline_15` varchar(255) DEFAULT NULL,
  `upline_16` varchar(255) DEFAULT NULL,
  `upline_17` varchar(255) DEFAULT NULL,
  `upline_18` varchar(255) DEFAULT NULL,
  `upline_19` varchar(255) DEFAULT NULL,
  `upline_20` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `upline_members`
--

LOCK TABLES `upline_members` WRITE;
/*!40000 ALTER TABLE `upline_members` DISABLE KEYS */;
INSERT INTO `upline_members` VALUES (1,'QRB123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-07-03 20:11:11','2025-07-03 20:11:11'),(2,'QRB996056','QRB123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-04 14:02:00','2026-09-04 14:02:00'),(3,'QRB743327','QRB996056','QRB123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-04 14:04:36','2026-09-04 14:04:36'),(4,'QRB886154',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-04 14:57:48','2026-09-04 14:57:48'),(5,'QRB477931','QRB743327','QRB996056','QRB123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-04 16:00:54','2026-09-04 16:00:54'),(6,'QRB826017','QRB886154',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-04 18:07:45','2026-09-04 18:07:45'),(7,'QRB357134','QRB886154',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-04 19:42:30','2026-09-04 19:42:30'),(8,'QRB809597','QRB886154',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-04 20:52:49','2026-09-04 20:52:49'),(9,'QRB383651','QRB886154',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-04 23:05:47','2026-09-04 23:05:47'),(10,'QRB839609','QRB123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-05 13:33:33','2026-09-05 13:33:33'),(11,'QRB886154','QRB839609','QRB123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-05 13:52:57','2026-09-05 13:52:57'),(12,'QRB826017','QRB886154',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-05 13:53:38','2026-09-05 13:53:38'),(13,'QRB357134','QRB886154',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-05 13:54:04','2026-09-05 13:54:04'),(14,'QRB383651','QRB886154',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-05 13:54:29','2026-09-05 13:54:29'),(15,'QRB477931','QRB743327','QRB996056','QRB123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-05 13:54:49','2026-09-05 13:54:49'),(16,'QRB743327','QRB996056','QRB123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-05 13:55:00','2026-09-05 13:55:00'),(17,'QRB996056','QRB123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-05 13:55:12','2026-09-05 13:55:12'),(18,'QRB993502','QRB123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-05 14:08:07','2026-09-05 14:08:07'),(19,'QRB511909','QRB993502','QRB123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-05 14:14:08','2026-09-05 14:14:08'),(20,'QRB942901','QRB123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-05 14:22:30','2026-09-05 14:22:30'),(21,'QRB828613','QRB942901','QRB123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-05 14:26:46','2026-09-05 14:26:46'),(22,'QRB765627','QRB828613','QRB942901','QRB123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-05 14:32:06','2026-09-05 14:32:06'),(23,'QRB225986','QRB477931','QRB743327','QRB996056','QRB123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-05 14:59:09','2026-09-05 14:59:09'),(24,'QRB847047','QRB225986','QRB477931','QRB743327','QRB996056','QRB123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-05 15:52:22','2026-09-05 15:52:22'),(25,'QRB828613','QRB942901','QRB123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-05 16:09:15','2026-09-05 16:09:15'),(26,'QRB225986','QRB477931','QRB743327','QRB996056','QRB123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-05 16:10:02','2026-09-05 16:10:02'),(27,'QRB765627','QRB828613','QRB942901','QRB123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-05 16:10:26','2026-09-05 16:10:26'),(28,'QRB993502','QRB123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-05 16:11:32','2026-09-05 16:11:32'),(29,'QRB874606','QRB886154',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-07 19:55:07','2026-09-07 19:55:07'),(30,'QRB829734','QRB886154',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-07 19:58:00','2026-09-07 19:58:00'),(31,'QRB163211','QRB511909','QRB993502','QRB123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-11 20:49:26','2026-09-11 20:49:26');
/*!40000 ALTER TABLE `upline_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wallet_transfers`
--

DROP TABLE IF EXISTS `wallet_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallet_transfers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `memberid` varchar(100) NOT NULL,
  `masterid` varchar(255) DEFAULT NULL,
  `walletType` varchar(255) NOT NULL,
  `debit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL,
  `balance_before` decimal(20,8) DEFAULT NULL,
  `particular` text NOT NULL,
  `status` varchar(255) DEFAULT 'Completed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wallet_transfers_masterid_index` (`masterid`)
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wallet_transfers`
--

LOCK TABLES `wallet_transfers` WRITE;
/*!40000 ALTER TABLE `wallet_transfers` DISABLE KEYS */;
INSERT INTO `wallet_transfers` VALUES (1,'QRB123456',NULL,'Investment',0.00,100.00,0.00,100.00000000,'Member QRB123456 Investment 100 $ deducted from Fund wallet','Completed','2026-08-26 16:25:26','2026-08-26 16:25:26'),(2,'QRB123456',NULL,'ROI Income',0.33,0.00,0.33,0.00000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-05 00:00:05','2026-09-05 00:00:05'),(3,'QRB123456',NULL,'ROI Income',0.33,0.00,0.66,0.33000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-06 00:00:05','2026-09-06 00:00:05'),(4,'QRB123456',NULL,'ROI Income',0.33,0.00,0.99,0.66000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-07 00:00:05','2026-09-07 00:00:05'),(5,'QRB942901',NULL,'Fund Transfer',500.00,0.00,500.00,0.00000000,'$ 500 has been transferred to your wallet by Admin','Completed','2026-09-07 15:10:34','2026-09-07 15:10:34'),(6,'QRB123456',NULL,'Direct Income',5.00,0.00,5.99,0.99000000,'Direct Income Amount Added into wallet. Payment Id-1','Completed','2026-09-07 15:12:14','2026-09-07 15:12:14'),(7,'QRB942901',NULL,'Investment',0.00,100.00,400.00,500.00000000,'Member QRB942901 Investment 100 $ deducted from Fund wallet','Completed','2026-09-07 15:12:14','2026-09-07 15:12:14'),(8,'QRB886154',NULL,'Fund Transfer',1000.00,0.00,1000.00,0.00000000,'$ 1000 has been transferred to your wallet by Admin','Completed','2026-09-07 15:14:25','2026-09-07 15:14:25'),(9,'QRB123456',NULL,'ROI Income',0.33,0.00,6.32,5.99000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-08 00:00:05','2026-09-08 00:00:05'),(10,'QRB942901',NULL,'ROI Income',0.33,0.00,0.33,0.00000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-08 00:00:05','2026-09-08 00:00:05'),(11,'QRB123456',NULL,'Level Income',0.05,0.00,6.37,6.32000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-08 00:00:07','2026-09-08 00:00:07'),(12,'QRB123456',NULL,'ROI Income',0.33,0.00,6.70,6.37000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-09 00:00:06','2026-09-09 00:00:06'),(13,'QRB942901',NULL,'ROI Income',0.33,0.00,0.66,0.33000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-09 00:00:06','2026-09-09 00:00:06'),(14,'QRB123456',NULL,'Level Income',0.05,0.00,6.75,6.70000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-09 00:00:08','2026-09-09 00:00:08'),(15,'QRB123456',NULL,'ROI Income',0.33,0.00,7.08,6.75000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-10 00:00:05','2026-09-10 00:00:05'),(16,'QRB942901',NULL,'ROI Income',0.33,0.00,0.99,0.66000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-10 00:00:05','2026-09-10 00:00:05'),(17,'QRB123456',NULL,'Level Income',0.05,0.00,7.13,7.08000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-10 00:00:07','2026-09-10 00:00:07'),(18,'QRB123456',NULL,'ROI Income',0.33,0.00,7.46,7.13000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-11 00:00:05','2026-09-11 00:00:05'),(19,'QRB942901',NULL,'ROI Income',0.33,0.00,1.32,0.99000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-11 00:00:05','2026-09-11 00:00:05'),(20,'QRB123456',NULL,'Level Income',0.05,0.00,7.51,7.46000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-11 00:00:07','2026-09-11 00:00:07'),(21,'QRB123456',NULL,'ROI Income',0.33,0.00,7.84,7.51000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-12 00:00:06','2026-09-12 00:00:06'),(22,'QRB942901',NULL,'ROI Income',0.33,0.00,1.65,1.32000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-12 00:00:06','2026-09-12 00:00:06'),(23,'QRB123456',NULL,'Level Income',0.05,0.00,7.89,7.84000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-12 00:00:07','2026-09-12 00:00:07'),(24,'QRB123456',NULL,'ROI Income',0.33,0.00,8.22,7.89000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-13 00:00:05','2026-09-13 00:00:05'),(25,'QRB942901',NULL,'ROI Income',0.33,0.00,1.98,1.65000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-13 00:00:05','2026-09-13 00:00:05'),(26,'QRB123456',NULL,'Level Income',0.05,0.00,8.27,8.22000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-13 00:00:06','2026-09-13 00:00:06'),(27,'QRB123456',NULL,'ROI Income',0.33,0.00,8.60,8.27000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-14 00:00:05','2026-09-14 00:00:05'),(28,'QRB942901',NULL,'ROI Income',0.33,0.00,2.31,1.98000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-14 00:00:05','2026-09-14 00:00:05'),(29,'QRB123456',NULL,'Level Income',0.05,0.00,8.65,8.60000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-14 00:00:06','2026-09-14 00:00:06'),(30,'QRB123456',NULL,'ROI Income',0.33,0.00,8.98,8.65000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-15 00:00:05','2026-09-15 00:00:05'),(31,'QRB942901',NULL,'ROI Income',0.33,0.00,2.64,2.31000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-15 00:00:05','2026-09-15 00:00:05'),(32,'QRB123456',NULL,'Level Income',0.05,0.00,9.03,8.98000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-15 00:00:07','2026-09-15 00:00:07'),(33,'QRB123456',NULL,'ROI Income',0.33,0.00,9.36,9.03000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-16 00:00:05','2026-09-16 00:00:05'),(34,'QRB942901',NULL,'ROI Income',0.33,0.00,2.97,2.64000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-16 00:00:05','2026-09-16 00:00:05'),(35,'QRB123456',NULL,'Level Income',0.05,0.00,9.41,9.36000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-16 00:00:07','2026-09-16 00:00:07'),(36,'QRB123456',NULL,'ROI Income',0.33,0.00,9.74,9.41000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-17 00:00:06','2026-09-17 00:00:06'),(37,'QRB942901',NULL,'ROI Income',0.33,0.00,3.30,2.97000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-17 00:00:06','2026-09-17 00:00:06'),(38,'QRB123456',NULL,'Level Income',0.05,0.00,9.79,9.74000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-17 00:00:07','2026-09-17 00:00:07'),(39,'QRB123456',NULL,'ROI Income',0.33,0.00,10.12,9.79000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-18 00:00:05','2026-09-18 00:00:05'),(40,'QRB942901',NULL,'ROI Income',0.33,0.00,3.63,3.30000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-18 00:00:05','2026-09-18 00:00:05'),(41,'QRB123456',NULL,'Level Income',0.05,0.00,10.17,10.12000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-18 00:00:07','2026-09-18 00:00:07'),(42,'QRB123456',NULL,'ROI Income',0.33,0.00,10.50,10.17000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-19 00:00:05','2026-09-19 00:00:05'),(43,'QRB942901',NULL,'ROI Income',0.33,0.00,3.96,3.63000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-19 00:00:05','2026-09-19 00:00:05'),(44,'QRB123456',NULL,'Level Income',0.05,0.00,10.55,10.50000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-19 00:00:07','2026-09-19 00:00:07'),(45,'QRB123456',NULL,'ROI Income',0.33,0.00,10.88,10.55000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-20 00:00:05','2026-09-20 00:00:05'),(46,'QRB942901',NULL,'ROI Income',0.33,0.00,4.29,3.96000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-20 00:00:05','2026-09-20 00:00:05'),(47,'QRB123456',NULL,'Level Income',0.05,0.00,10.93,10.88000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-20 00:00:07','2026-09-20 00:00:07'),(48,'QRB123456',NULL,'ROI Income',0.33,0.00,11.26,10.93000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-21 00:00:05','2026-09-21 00:00:05'),(49,'QRB942901',NULL,'ROI Income',0.33,0.00,4.62,4.29000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-21 00:00:05','2026-09-21 00:00:05'),(50,'QRB123456',NULL,'Level Income',0.05,0.00,11.31,11.26000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-21 00:00:07','2026-09-21 00:00:07'),(51,'QRB123456',NULL,'ROI Income',0.33,0.00,11.64,11.31000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-22 00:00:06','2026-09-22 00:00:06'),(52,'QRB942901',NULL,'ROI Income',0.33,0.00,4.95,4.62000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-22 00:00:06','2026-09-22 00:00:06'),(53,'QRB123456',NULL,'Level Income',0.05,0.00,11.69,11.64000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-22 00:00:08','2026-09-22 00:00:08'),(54,'QRB123456',NULL,'ROI Income',0.33,0.00,12.02,11.69000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-23 00:00:05','2026-09-23 00:00:05'),(55,'QRB942901',NULL,'ROI Income',0.33,0.00,5.28,4.95000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-23 00:00:05','2026-09-23 00:00:05'),(56,'QRB123456',NULL,'Level Income',0.05,0.00,12.07,12.02000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-23 00:00:07','2026-09-23 00:00:07'),(57,'QRB123456',NULL,'ROI Income',0.33,0.00,12.40,12.07000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-24 00:00:06','2026-09-24 00:00:06'),(58,'QRB942901',NULL,'ROI Income',0.33,0.00,5.61,5.28000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-24 00:00:06','2026-09-24 00:00:06'),(59,'QRB123456',NULL,'Level Income',0.05,0.00,12.45,12.40000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-24 00:00:08','2026-09-24 00:00:08'),(60,'QRB123456',NULL,'ROI Income',0.33,0.00,12.78,12.45000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-25 00:00:06','2026-09-25 00:00:06'),(61,'QRB942901',NULL,'ROI Income',0.33,0.00,5.94,5.61000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-25 00:00:06','2026-09-25 00:00:06'),(62,'QRB123456',NULL,'Level Income',0.05,0.00,12.83,12.78000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-25 00:00:07','2026-09-25 00:00:07'),(63,'QRB123456',NULL,'ROI Income',0.33,0.00,13.16,12.83000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-26 00:00:05','2026-09-26 00:00:05'),(64,'QRB942901',NULL,'ROI Income',0.33,0.00,6.27,5.94000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-26 00:00:05','2026-09-26 00:00:05'),(65,'QRB123456',NULL,'Level Income',0.05,0.00,13.21,13.16000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-26 00:00:07','2026-09-26 00:00:07'),(66,'QRB123456',NULL,'ROI Income',0.33,0.00,13.54,13.21000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-27 00:00:06','2026-09-27 00:00:06'),(67,'QRB942901',NULL,'ROI Income',0.33,0.00,6.60,6.27000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-27 00:00:06','2026-09-27 00:00:06'),(68,'QRB123456',NULL,'Level Income',0.05,0.00,13.59,13.54000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-27 00:00:08','2026-09-27 00:00:08'),(69,'QRB123456',NULL,'ROI Income',0.33,0.00,13.92,13.59000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-28 00:00:06','2026-09-28 00:00:06'),(70,'QRB942901',NULL,'ROI Income',0.33,0.00,6.93,6.60000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-28 00:00:06','2026-09-28 00:00:06'),(71,'QRB123456',NULL,'Level Income',0.05,0.00,13.97,13.92000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-28 00:00:07','2026-09-28 00:00:07'),(72,'QRB123456',NULL,'ROI Income',0.33,0.00,14.30,13.97000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-29 00:00:06','2026-09-29 00:00:06'),(73,'QRB942901',NULL,'ROI Income',0.33,0.00,7.26,6.93000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-29 00:00:06','2026-09-29 00:00:06'),(74,'QRB123456',NULL,'Level Income',0.05,0.00,14.35,14.30000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-29 00:00:07','2026-09-29 00:00:07'),(75,'QRB123456',NULL,'ROI Income',0.33,0.00,14.68,14.35000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-30 00:00:06','2026-09-30 00:00:06'),(76,'QRB942901',NULL,'ROI Income',0.33,0.00,7.59,7.26000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-09-30 00:00:06','2026-09-30 00:00:06'),(77,'QRB123456',NULL,'Level Income',0.05,0.00,14.73,14.68000000,'1 Level Income Amount Added into wallet.','Completed','2026-09-30 00:00:08','2026-09-30 00:00:08'),(78,'QRB123456',NULL,'ROI Income',0.33,0.00,15.06,14.73000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-01 00:00:05','2026-10-01 00:00:05'),(79,'QRB942901',NULL,'ROI Income',0.33,0.00,7.92,7.59000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-01 00:00:05','2026-10-01 00:00:05'),(80,'QRB123456',NULL,'Level Income',0.05,0.00,15.11,15.06000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-01 00:00:07','2026-10-01 00:00:07'),(81,'QRB123456',NULL,'ROI Income',0.33,0.00,15.44,15.11000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-02 00:00:05','2026-10-02 00:00:05'),(82,'QRB942901',NULL,'ROI Income',0.33,0.00,8.25,7.92000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-02 00:00:05','2026-10-02 00:00:05'),(83,'QRB123456',NULL,'Level Income',0.05,0.00,15.49,15.44000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-02 00:00:07','2026-10-02 00:00:07'),(84,'QRB123456',NULL,'ROI Income',0.33,0.00,15.82,15.49000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-03 00:00:06','2026-10-03 00:00:06'),(85,'QRB942901',NULL,'ROI Income',0.33,0.00,8.58,8.25000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-03 00:00:06','2026-10-03 00:00:06'),(86,'QRB123456',NULL,'Level Income',0.05,0.00,15.87,15.82000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-03 00:00:07','2026-10-03 00:00:07'),(87,'QRB123456',NULL,'ROI Income',0.33,0.00,16.20,15.87000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-04 00:00:06','2026-10-04 00:00:06'),(88,'QRB942901',NULL,'ROI Income',0.33,0.00,8.91,8.58000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-04 00:00:06','2026-10-04 00:00:06'),(89,'QRB123456',NULL,'Level Income',0.05,0.00,16.25,16.20000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-04 00:00:08','2026-10-04 00:00:08'),(90,'QRB123456',NULL,'ROI Income',0.33,0.00,16.58,16.25000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-05 00:00:06','2026-10-05 00:00:06'),(91,'QRB942901',NULL,'ROI Income',0.33,0.00,9.24,8.91000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-05 00:00:06','2026-10-05 00:00:06'),(92,'QRB123456',NULL,'Level Income',0.05,0.00,16.63,16.58000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-05 00:00:08','2026-10-05 00:00:08'),(93,'QRB123456',NULL,'ROI Income',0.33,0.00,16.96,16.63000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-06 00:00:03','2026-10-06 00:00:03'),(94,'QRB942901',NULL,'ROI Income',0.33,0.00,9.57,9.24000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-06 00:00:03','2026-10-06 00:00:03'),(95,'QRB123456',NULL,'Level Income',0.05,0.00,17.01,16.96000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-06 00:00:06','2026-10-06 00:00:06'),(96,'QRB123456',NULL,'ROI Income',0.33,0.00,17.34,17.01000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-07 00:00:06','2026-10-07 00:00:06'),(97,'QRB942901',NULL,'ROI Income',0.33,0.00,9.90,9.57000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-07 00:00:06','2026-10-07 00:00:06'),(98,'QRB123456',NULL,'Level Income',0.05,0.00,17.39,17.34000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-07 00:00:07','2026-10-07 00:00:07'),(99,'QRB123456',NULL,'ROI Income',0.33,0.00,17.72,17.39000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-08 00:00:06','2026-10-08 00:00:06'),(100,'QRB942901',NULL,'ROI Income',0.33,0.00,10.23,9.90000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-08 00:00:06','2026-10-08 00:00:06'),(101,'QRB123456',NULL,'Level Income',0.05,0.00,17.77,17.72000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-08 00:00:08','2026-10-08 00:00:08'),(102,'QRB123456',NULL,'ROI Income',0.33,0.00,18.10,17.77000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-09 00:00:05','2026-10-09 00:00:05'),(103,'QRB942901',NULL,'ROI Income',0.33,0.00,10.56,10.23000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-09 00:00:05','2026-10-09 00:00:05'),(104,'QRB123456',NULL,'Level Income',0.05,0.00,18.15,18.10000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-09 00:00:07','2026-10-09 00:00:07'),(105,'QRB123456',NULL,'ROI Income',0.33,0.00,18.48,18.15000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-10 00:00:06','2026-10-10 00:00:06'),(106,'QRB942901',NULL,'ROI Income',0.33,0.00,10.89,10.56000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-10 00:00:06','2026-10-10 00:00:06'),(107,'QRB123456',NULL,'Level Income',0.05,0.00,18.53,18.48000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-10 00:00:08','2026-10-10 00:00:08'),(108,'QRB123456',NULL,'ROI Income',0.33,0.00,18.86,18.53000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-11 00:00:06','2026-10-11 00:00:06'),(109,'QRB942901',NULL,'ROI Income',0.33,0.00,11.22,10.89000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-11 00:00:06','2026-10-11 00:00:06'),(110,'QRB123456',NULL,'Level Income',0.05,0.00,18.91,18.86000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-11 00:00:07','2026-10-11 00:00:07'),(111,'QRB123456',NULL,'ROI Income',0.33,0.00,19.24,18.91000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-12 00:00:06','2026-10-12 00:00:06'),(112,'QRB942901',NULL,'ROI Income',0.33,0.00,11.55,11.22000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-12 00:00:06','2026-10-12 00:00:06'),(113,'QRB123456',NULL,'Level Income',0.05,0.00,19.29,19.24000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-12 00:00:08','2026-10-12 00:00:08'),(114,'QRB123456',NULL,'ROI Income',0.33,0.00,19.62,19.29000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-13 00:00:06','2026-10-13 00:00:06'),(115,'QRB942901',NULL,'ROI Income',0.33,0.00,11.88,11.55000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-13 00:00:06','2026-10-13 00:00:06'),(116,'QRB123456',NULL,'Level Income',0.05,0.00,19.67,19.62000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-13 00:00:07','2026-10-13 00:00:07'),(117,'QRB123456',NULL,'ROI Income',0.33,0.00,20.00,19.67000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-14 00:00:05','2026-10-14 00:00:05'),(118,'QRB942901',NULL,'ROI Income',0.33,0.00,12.21,11.88000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-14 00:00:05','2026-10-14 00:00:05'),(119,'QRB123456',NULL,'Level Income',0.05,0.00,20.05,20.00000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-14 00:00:07','2026-10-14 00:00:07'),(120,'QRB123456',NULL,'ROI Income',0.33,0.00,20.38,20.05000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-15 00:00:06','2026-10-15 00:00:06'),(121,'QRB942901',NULL,'ROI Income',0.33,0.00,12.54,12.21000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-15 00:00:06','2026-10-15 00:00:06'),(122,'QRB123456',NULL,'Level Income',0.05,0.00,20.43,20.38000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-15 00:00:07','2026-10-15 00:00:07'),(123,'QRB123456',NULL,'ROI Income',0.33,0.00,20.76,20.43000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-16 00:00:05','2026-10-16 00:00:05'),(124,'QRB942901',NULL,'ROI Income',0.33,0.00,12.87,12.54000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-16 00:00:05','2026-10-16 00:00:05'),(125,'QRB123456',NULL,'Level Income',0.05,0.00,20.81,20.76000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-16 00:00:07','2026-10-16 00:00:07'),(126,'QRB123456',NULL,'ROI Income',0.33,0.00,21.14,20.81000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-17 00:00:07','2026-10-17 00:00:07'),(127,'QRB942901',NULL,'ROI Income',0.33,0.00,13.20,12.87000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-17 00:00:07','2026-10-17 00:00:07'),(128,'QRB123456',NULL,'Level Income',0.05,0.00,21.19,21.14000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-17 00:00:09','2026-10-17 00:00:09'),(129,'QRB123456',NULL,'ROI Income',0.33,0.00,21.52,21.19000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-18 00:00:05','2026-10-18 00:00:05'),(130,'QRB942901',NULL,'ROI Income',0.33,0.00,13.53,13.20000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-18 00:00:05','2026-10-18 00:00:05'),(131,'QRB123456',NULL,'Level Income',0.05,0.00,21.57,21.52000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-18 00:00:07','2026-10-18 00:00:07'),(132,'QRB123456',NULL,'ROI Income',0.33,0.00,21.90,21.57000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-19 00:00:06','2026-10-19 00:00:06'),(133,'QRB942901',NULL,'ROI Income',0.33,0.00,13.86,13.53000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-19 00:00:06','2026-10-19 00:00:06'),(134,'QRB123456',NULL,'Level Income',0.05,0.00,21.95,21.90000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-19 00:00:08','2026-10-19 00:00:08'),(135,'QRB123456',NULL,'ROI Income',0.33,0.00,22.28,21.95000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-20 00:00:06','2026-10-20 00:00:06'),(136,'QRB942901',NULL,'ROI Income',0.33,0.00,14.19,13.86000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-20 00:00:06','2026-10-20 00:00:06'),(137,'QRB123456',NULL,'Level Income',0.05,0.00,22.33,22.28000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-20 00:00:08','2026-10-20 00:00:08'),(138,'QRB123456',NULL,'ROI Income',0.33,0.00,22.66,22.33000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-21 00:00:06','2026-10-21 00:00:06'),(139,'QRB942901',NULL,'ROI Income',0.33,0.00,14.52,14.19000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-21 00:00:06','2026-10-21 00:00:06'),(140,'QRB123456',NULL,'Level Income',0.05,0.00,22.71,22.66000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-21 00:00:08','2026-10-21 00:00:08'),(141,'QRB123456',NULL,'ROI Income',0.33,0.00,23.04,22.71000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-22 00:00:06','2026-10-22 00:00:06'),(142,'QRB942901',NULL,'ROI Income',0.33,0.00,14.85,14.52000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-22 00:00:06','2026-10-22 00:00:06'),(143,'QRB123456',NULL,'Level Income',0.05,0.00,23.09,23.04000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-22 00:00:08','2026-10-22 00:00:08'),(144,'QRB123456',NULL,'ROI Income',0.33,0.00,23.42,23.09000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-23 00:00:06','2026-10-23 00:00:06'),(145,'QRB942901',NULL,'ROI Income',0.33,0.00,15.18,14.85000000,'ROI Income amount has been transfered into Main Wallet.','Completed','2026-10-23 00:00:06','2026-10-23 00:00:06'),(146,'QRB123456',NULL,'Level Income',0.05,0.00,23.47,23.42000000,'1 Level Income Amount Added into wallet.','Completed','2026-10-23 00:00:08','2026-10-23 00:00:08');
/*!40000 ALTER TABLE `wallet_transfers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `welcome_images`
--

DROP TABLE IF EXISTS `welcome_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `welcome_images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rank` int(11) NOT NULL,
  `image` text NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `welcome_images`
--

LOCK TABLES `welcome_images` WRITE;
/*!40000 ALTER TABLE `welcome_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `welcome_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `withdrawal_requests`
--

DROP TABLE IF EXISTS `withdrawal_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `withdrawal_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `request_date` datetime NOT NULL,
  `payment_date` datetime DEFAULT NULL,
  `request_id` varchar(255) NOT NULL,
  `txnid` varchar(100) DEFAULT NULL,
  `memberid` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `wallet_address` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `usdt_amount` decimal(12,4) DEFAULT NULL,
  `gross_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `service_charge` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `net_amount` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `admin_comment` varchar(255) DEFAULT NULL,
  `status` varchar(100) DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `withdrawal_requests_member_status_index` (`memberid`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `withdrawal_requests`
--

LOCK TABLES `withdrawal_requests` WRITE;
/*!40000 ALTER TABLE `withdrawal_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `withdrawal_requests` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-24 16:32:37
