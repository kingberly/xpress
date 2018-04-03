--
-- Table structure for table `event_log`
--

CREATE TABLE IF NOT EXISTS `event_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` varchar(63) COLLATE utf8_unicode_ci NOT NULL,
  `mac` char(12) COLLATE utf8_unicode_ci NOT NULL,
  `get_parameter` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `result` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
