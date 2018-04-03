/****
 * Admin showGIS, installGIS
ALTER TABLE customerservice.workeyegis ADD COLUMN URL varchar(255) DEFAULT NULL;
ALTER TABLE customerservice.workeyegis ADD COLUMN is_installer  tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE customerservice.workeyegis ADD COLUMN share_account varchar(660) DEFAULT NULL;
ALTER TABLE customerservice.workeyegis MODIFY COLUMN share_account varchar(660); 
 ****/
CREATE TABLE IF NOT EXISTS `workeyegis` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `OEM_ID` char(3) COLLATE utf8_unicode_ci NOT NULL,
  `ACNO` varchar(64) NOT NULL,
  `PURP` varchar(64) COLLATE utf8_bin NOT NULL,
  `APNAME` varchar(64) COLLATE utf8_bin NOT NULL,
  `DIGADD` varchar(255) COLLATE utf8_bin NOT NULL,
  `TCNAME` varchar(64) COLLATE utf8_bin NOT NULL,
  `TC_TEL` varchar(64) DEFAULT NULL,
  `LAT` DECIMAL(10, 8) NOT NULL,
  `LNG` DECIMAL(11, 8) NOT NULL,
  `APPMODE` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `URL` varchar(255) DEFAULT NULL,
	`bind_account` varchar(64) DEFAULT NULL,
  `user_name` varchar(64) DEFAULT NULL,
  `user_pwd` varchar(32) DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL, 
  `note` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `share_account` varchar(660) DEFAULT NULL,
  `is_installer` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ACNO` (`ACNO`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/***
ALTER TABLE customerservice.gis_log ADD COLUMN ACNO varchar(64) DEFAULT NULL;
 ***/
CREATE TABLE IF NOT EXISTS `gis_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bind_account` varchar(64) DEFAULT NULL,
  `ACNO` varchar(64) DEFAULT NULL,
  `DIGADD` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `LAT` DECIMAL(10, 8) DEFAULT NULL,
  `LNG` DECIMAL(11, 8) DEFAULT NULL,
  `mac` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `action` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `result` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip_addr` varchar(63) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL, 
  PRIMARY KEY (`id`)
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*******
 *	RPIC Web manage_share log
 *******/
CREATE TABLE IF NOT EXISTS `share_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mac` varchar(20) DEFAULT NULL,
  `owner_id` varchar(11) COLLATE utf8_bin DEFAULT NULL,
  `owner_name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `visitor_id` varchar(11) COLLATE utf8_bin NOT NULL,
  `visitor_name` varchar(255) COLLATE utf8_bin NOT NULL,  
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `action` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `result` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip_addr` varchar(63) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL, 
  PRIMARY KEY (`id`)
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*
-- vendor info was used to test vendor self-input
CREATE TABLE IF NOT EXISTS `vendor_info` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cloudprefix` varchar(10) DEFAULT NULL,
  `Email` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cloudprefix` (`cloudprefix`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
*/

/*
ALTER TABLE customerservice.maintain ADD COLUMN submiter varchar(32) DEFAULT NULL
*/
CREATE TABLE IF NOT EXISTS `maintain` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `oem_id` char(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mac` varchar(20) DEFAULT NULL,
  `account` varchar(64) DEFAULT NULL,
  `camera_type` varchar(8) NOT NULL DEFAULT 'RMA',
  `note` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `update_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `submiter` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
SHOW VARIABLES LIKE "%version%";
TINYTEXT          255 (+1 byte  overhead)
BLOB/TEXT          64K - 1 (+2 bytes overhead)
MEDIUMBLOB/MEDIUMTEXT    16M - 1 (+3 bytes overhead)
LONGTEXT      4G  - 1 (+4 bytes overhead)
ALTER TABLE customerservice.urlshort ADD CONSTRAINT `shorturl` UNIQUE (`shorturl`)
*/
CREATE TABLE IF NOT EXISTS `urlshort` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `url` BLOB DEFAULT NULL,
  `shorturl` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shorturl` (`shorturl`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;