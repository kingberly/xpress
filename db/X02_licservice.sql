/* MyISAM to InnoDB:  ALTER TABLE qlicense ENGINE=InnoDB
	 change Filaname value after v3.0.3 
*/
CREATE TABLE IF NOT EXISTS `qlicense` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Filename` varchar(64) NOT NULL,
  `Date` varchar(12) NOT NULL,
  `Apply_name` varchar(8) NOT NULL,
  `Apply_num` varchar(8) NOT NULL,
  `PID` varchar(4) NOT NULL,
  `CID` varchar(4) NOT NULL,
  `Payer_ID` varchar(4) NOT NULL,
  `Hw` varchar(16) NOT NULL,
  `Customer` varchar(16) NOT NULL,
  `Sample` varchar(4) NOT NULL DEFAULT '0',
  `Paid` varchar(4) NOT NULL DEFAULT '0',
  `Not_paid` varchar(4) NOT NULL DEFAULT '1',
  `DCC` varchar(8) NOT NULL,
  `Order_num` varchar(16) DEFAULT NULL,
  `Mac` varchar(12) NOT NULL,
  `Code` varchar(12) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8; 

CREATE TABLE IF NOT EXISTS `other_license` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `begin` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expire` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `oem_id` char(3) COLLATE utf8_bin DEFAULT NULL,
  `note` text COLLATE utf8_bin,
  PRIMARY KEY (`id`),
  UNIQUE KEY `license_key` (`license_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin; 

CREATE TABLE IF NOT EXISTS `stream_server_license` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `VERSION` tinyint(4) DEFAULT NULL,
  `LICENSE_ID` varchar(32) COLLATE utf8_bin NOT NULL,
  `KEY1` text COLLATE utf8_bin,
  `KEY2` text COLLATE utf8_bin,
  `KEY3` text COLLATE utf8_bin,
  `ISSUE_DATE` varchar(20) COLLATE utf8_bin DEFAULT '0000-00-00 00:00:00',
  `PURPOSE` text COLLATE utf8_bin,
  `COMPANY` text COLLATE utf8_bin,
  `CONTACT_PERSON_NAME` text COLLATE utf8_bin,
  `CONTACT_PERSON_EMAIL` text COLLATE utf8_bin,
  `SIGNATURE` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `LICENSE_ID` (`LICENSE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 
CREATE TABLE IF NOT EXISTS `tunnel_server_license` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `begin` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expire` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `license_key` varchar(32) COLLATE utf8_bin NOT NULL,
  `signature` text COLLATE utf8_bin,
  `version` smallint(6) NOT NULL DEFAULT '0',
  `cid` char(3) COLLATE utf8_bin NOT NULL,
  `oem_id` char(3) COLLATE utf8_bin DEFAULT NULL,
  `channels` int(11) NOT NULL DEFAULT '0',
  `note` text COLLATE utf8_bin,
  PRIMARY KEY (`id`),
  UNIQUE KEY `license_key` (`license_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin; 

/* MyISAM to InnoDB after
   MyISAM compatible with xpress 2.3.9
*/
CREATE TABLE IF NOT EXISTS `account` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Email` varchar(64) NOT NULL,
  `Password` varchar(32) DEFAULT NULL,
  `Company_english` varchar(32) NOT NULL,
  `Company_chinese` varchar(32) NOT NULL,
  `Contact` varchar(32) NOT NULL,
  `Mobile` varchar(16) NOT NULL,
  `Phone` varchar(16) NOT NULL,
  `Address` varchar(64) NOT NULL,
  `CID` varchar(8) NOT NULL,
  `Level` varchar(8) NOT NULL DEFAULT '0' COMMENT '0-for normal partner  9-for superuser',
  `Status` varchar(8) NOT NULL DEFAULT '0' COMMENT '0-for just apply  1-for approved with CID add by PM ',
  `Company_nickname` varchar(16) NOT NULL,
  `ID_admin` varchar(1) NOT NULL DEFAULT '0',
  `ID_admin_oem` varchar(1) NOT NULL DEFAULT '0',
  `ID_webmaster` varchar(1) NOT NULL DEFAULT '0',
  `ID_qlync_pm` varchar(1) DEFAULT '0',
  `ID_qlync_rd` varchar(4) DEFAULT '0',
  `ID_pm_oem` varchar(4) DEFAULT '0',
  `ID_sales` varchar(4) DEFAULT '0',
  `ID_qlync_fae` varchar(1) DEFAULT '0',
  `ID_qlync_qa` varchar(4) DEFAULT '0',
  `ID_qlync_admin` varchar(4) DEFAULT '0',
  `ICID` varchar(8) DEFAULT NULL,
  `ID_fae` varchar(4) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account`
--

LOCK TABLES `account` WRITE;
/*!40000 ALTER TABLE `account` DISABLE KEYS */;
INSERT IGNORE INTO `account` VALUES (1,'admin@localhost.com',ENCODE('1qaz2wsx', 'admin'),'Administrator','Administrator','','','','','N99','0','1','','1','0','1','0','0','0','0','0','0','0','','0');
/*!40000 ALTER TABLE `account` ENABLE KEYS */;
UNLOCK TABLES;