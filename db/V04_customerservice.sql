-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.6.16 - MySQL Community Server (GPL)
-- Server OS:                    Win32
-- HeidiSQL version:             7.0.0.4053
-- Date/time:                    2014-08-21 16:07:04
-- ?? ALTER TABLE customerservice.api_log CHANGE COLUMN `action` `action` VARCHAR(64);
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `api_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `api` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `action` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `result` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip_addr` varchar(63) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL, 
  PRIMARY KEY (`id`)
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
