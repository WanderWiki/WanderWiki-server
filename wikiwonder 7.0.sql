CREATE DATABASE  IF NOT EXISTS `p50380g50790__wanderwiki` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `p50380g50790__wanderwiki`;
-- MySQL dump 10.13  Distrib 5.6.13, for Win32 (x86)
--
-- Host: localhost    Database: p50380g50790__wanderwiki
-- ------------------------------------------------------
-- Server version	5.5.34-MariaDB-1~precise-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(40) NOT NULL,
  `length` double NOT NULL,
  `description` text,
  `articles` text,
  `startLat` double DEFAULT NULL,
  `startLng` double DEFAULT NULL,
  `endLat` double DEFAULT NULL,
  `endLng` double DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `dl` int(11) unsigned DEFAULT '0',
  `idAuthor` int(10) unsigned NOT NULL,
  `votePos` int(11) NOT NULL DEFAULT '0',
  `voteNeg` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_idAuteur_traj` (`idAuthor`),
  CONSTRAINT `fk_idAuteur_traj` FOREIGN KEY (`idAuthor`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `account` varchar(60) NOT NULL,
  `pseudo` varchar(30) DEFAULT NULL,
  `nbrTraces` int(11) DEFAULT '0',
  `security` int(11) DEFAULT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nbrDl` int(11) NOT NULL DEFAULT '0',
  `nbrVotePos` int(11) NOT NULL DEFAULT '0',
  `nbrVoteNeg` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_UNIQUE` (`account`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vote`
--

DROP TABLE IF EXISTS `vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vote` (
  `value` int(10) DEFAULT NULL,
  `idTrace` int(10) unsigned NOT NULL,
  `idUser` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idTrace`,`idUser`),
  KEY `fk_idAuthor_vote` (`idUser`),
  CONSTRAINT `fk_idAuteur_vote` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_idTrajet_vote` FOREIGN KEY (`idTrace`) REFERENCES `files` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'p50380g50790__wanderwiki'
--
/*!50003 DROP PROCEDURE IF EXISTS `new_trace` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`p50380g50790`@`%` PROCEDURE `new_trace`(IN `ti` VARCHAR(40), IN `lgth` INT, IN `des` TEXT, IN `art` TEXT, IN `slat` DOUBLE, IN `slng` DOUBLE, IN `elat` DOUBLE, IN `elng` DOUBLE, IN `time` INT, IN `author` INT)
    NO SQL
BEGIN
INSERT INTO files (title, length, description, articles, startLat, startLng, endLat, endLng, time,idAuthor) VALUES
(ti,lgth,des,art,slat,slng,elat,elng,time,author);
 UPDATE users SET nbrTraces=nbrTraces+1 WHERE id=author;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `new_users` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`p50380g50790`@`%` PROCEDURE `new_users`( IN  `acc` VARCHAR( 60 ) , IN  `name` VARCHAR( 30 ) , IN  `sec` INT )
    NO SQL
INSERT INTO users( account, pseudo, security ) 
VALUES (
acc, name, sec
) ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `new_vote_neg` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`p50380g50790`@`%` PROCEDURE `new_vote_neg`(IN `trace` INT, IN `author` INT)
    NO SQL
BEGIN
INSERT INTO vote (value,idTrace,idUser) VALUES
(-1,author,trace);
UPDATE files SET voteNeg=voteNeg WHERE id=trace;

UPDATE users, files SET nbrVoteNeg=nbrVoteNeg+1  WHERE files.id=trace AND users.id=files.idAuthor;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `new_vote_plus` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`p50380g50790`@`%` PROCEDURE `new_vote_plus`(IN `author` INT, IN `trace` INT)
    NO SQL
BEGIN
INSERT INTO vote (value,idTrace,idUser) VALUES (1,author,trace);
UPDATE files SET votePos=votePos+1 WHERE id=trace;
UPDATE users, files SET nbrVotePos=nbrVotePos+1  WHERE files.id=trace AND users.id=files.idAuthor;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-01-12 18:18:44
