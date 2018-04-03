-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.6.16 - MySQL Community Server (GPL)
-- Server OS:                    Win32
-- HeidiSQL version:             7.0.0.4053
-- Date/time:                    2014-08-21 16:07:04
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;

-- Dumping structure for table customerservice.service_cameras
CREATE TABLE IF NOT EXISTS `service_cameras` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cloudid` varchar(50) DEFAULT NULL,
  `mac` varchar(20) DEFAULT NULL,
  `serviceid` varchar(50) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `firmware` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mac` (`mac`),
  KEY `cloudid` (`cloudid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- Dumping data for table customerservice.service_cameras: ~0 rows (approximately)
/*!40000 ALTER TABLE `service_cameras` DISABLE KEYS */;
INSERT IGNORE INTO `service_cameras` (`id`, `cloudid`, `mac`, `serviceid`, `model`, `firmware`) VALUES
	(1, 'FGFD897979', '001BFE54SDO3', '1234567890ABC', 'Simple', 'M2.1.6 05C029_P04'),
	(3, 'FGFD897979', 'FDSFSD7G9D89', 'G976HGD8H6FG', 'Night', 'M2.1.6 05C029_P04');
/*!40000 ALTER TABLE `service_cameras` ENABLE KEYS */;


-- Dumping structure for table customerservice.service_users
CREATE TABLE IF NOT EXISTS `service_users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cloudid` varchar(50) DEFAULT NULL,
  `account` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cloudid` (`cloudid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- Dumping data for table test.service_users: ~0 rows (approximately)
/*!40000 ALTER TABLE `service_users` DISABLE KEYS */;
INSERT IGNORE INTO `service_users` (`id`, `cloudid`, `account`, `email`) VALUES
	(1, 'FGFD897979', 'Charlie Store Corp.', 'char@charliestore.com');
/*!40000 ALTER TABLE `service_users` ENABLE KEYS */;
/*!40014 SET FOREIGN_KEY_CHECKS=1 */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
