-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.6.16 - MySQL Community Server (GPL)
-- Server OS:                    Win32
-- HeidiSQL version:             7.0.0.4053
-- Date/time:                    2014-08-21 16:07:04
-- --------------------------------------------------------

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
  PRIMARY KEY (`id`)
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
