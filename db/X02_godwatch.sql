-- godwatch old feature, absolete after X4.1 v3.0.2 

CREATE TABLE IF NOT EXISTS `gw_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `uid` varchar(18) COLLATE utf8_unicode_ci DEFAULT NULL,
  `note` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


--
-- device_type COMMON, CONV, LESSON, MONITOR 
--
CREATE TABLE IF NOT EXISTS `gw_device` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `uid` varchar(18) COLLATE utf8_unicode_ci NOT NULL,
  `group_id` int(11) DEFAULT NULL, 
  `device_type` varchar(8) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- TYPE_LESSON set as 0 or gw_deviceid 
--
CREATE TABLE IF NOT EXISTS `gw_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `pwd` varchar(255) COLLATE utf8_bin NOT NULL,
  `Mobile` varchar(16) DEFAULT NULL,
  `reg_email` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `Address` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `Note` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `TYPE_CONV` varchar(1) NOT NULL DEFAULT '0',
  `TYPE_LESSON_N` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `TYPE_LESSON` varchar(16) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
