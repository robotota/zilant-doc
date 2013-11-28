-- MySQL dump 10.13  Distrib 5.1.61, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: autoexp
-- ------------------------------------------------------
-- Server version	5.1.61-0ubuntu0.10.10.1


--
-- Table structure for table `__display_names`
--

DROP TABLE IF EXISTS `__display_names`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `__display_names` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(50) NOT NULL,
  `display_name` varchar(200)  NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `__display_names`
--

LOCK TABLES `__display_names` WRITE;
/*!40000 ALTER TABLE `__display_names` DISABLE KEYS */;
INSERT INTO `__display_names` VALUES (1,'users','Пользователи'),(8,'__field_config','Настройка полей'),(7,'__display_names','Таблицы'),(19,'tickets','Замечания');
/*!40000 ALTER TABLE `__display_names` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `__field_config`
--

DROP TABLE IF EXISTS `__field_config`;
CREATE TABLE `__field_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  `type` varchar(200) NOT NULL,
  `display_name` varchar(200) NOT NULL,
  `display` int(11) NOT NULL,
  `editable` int(11) NOT NULL,
  `foreign_table_name` varchar(100) NOT NULL,
  `foreign_name` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=154 ;

--
-- Dumping data for table `__field_config`
--

LOCK TABLES `__field_config` WRITE;
/*!40000 ALTER TABLE `__field_config` DISABLE KEYS */;
INSERT INTO `__field_config` VALUES (26,'__field_config','id','int','id',1,1,'',NULL),(114,'tickets','description','memo','Текст замечания',1,1,'',''),(115,'tickets','solution','memo','Решение',1,1,'',''),(116,'tickets','user_id','reference','Пользователь',1,1,'users','name');
/*!40000 ALTER TABLE `__field_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `description` varchar(500)  NOT NULL,
  `solution` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=22;
\


--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `password` varchar(100) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=35 ;


--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (23,'admin','5f4dcc3b5aa765d61d8327deb882cf99',1,'Админ');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

