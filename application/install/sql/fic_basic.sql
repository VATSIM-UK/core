CREATE TABLE IF NOT EXISTS `fic_airfield` (
  `id` bigint(12) NOT NULL AUTO_INCREMENT,
  `icao` varchar(4) CHARACTER SET utf8 NOT NULL,
  `iata` varchar(3) CHARACTER SET utf8 NOT NULL,
  `name` varchar(50) CHARACTER SET utf8 NOT NULL,
  `country` varchar(2) CHARACTER SET utf8 NOT NULL,
  `type` SMALLINT(2) UNSIGNED NOT NULL,
  `elevation` smallint(4) UNSIGNED NOT NULL DEFAULT '0',
  `latitude` decimal(8,6) NOT NULL,
  `longitude` decimal(8,6) NOT NULL,
  `intro` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iata` (`iata`),
  UNIQUE KEY `icao` (`icao`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=145 ;

ALTER TABLE `fic_airfield` ADD `updated` TIMESTAMP NULL DEFAULT NULL AFTER `intro`;

CREATE TABLE IF NOT EXISTS `fic_airfield_procedures` (
  `id` int(10) unsigned NOT NULL,
  `airfield_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `fic_navaid` (
  `id` bigint(20) unsigned NOT NULL,
  `identifier` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `fic_airfield_runway` (
  `id` bigint(20) unsigned NOT NULL,
  `airfield_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
