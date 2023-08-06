-- Dump della struttura di tabella ftest.fabcms_formazione_courses
CREATE TABLE IF NOT EXISTS `fabcms_formazione_courses` (
                                                           `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                           `name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
                                                           `name_trackback` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
                                                           `tags` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
                                                           `avaliable_date` date DEFAULT NULL,
                                                           `short_description` text CHARACTER SET utf8 NOT NULL,
                                                           `SEO_description` varchar(512) CHARACTER SET utf8 NOT NULL DEFAULT '0',
                                                           `thumb_image` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
                                                           `description` text CHARACTER SET utf8 NOT NULL,
                                                           `prestashop_ID` int(11) DEFAULT NULL,
                                                           `subscription_link` varchar(255) CHARACTER SET utf8 NOT NULL,
                                                           `avaliable` bit(1) DEFAULT b'0',
                                                           `visible` bit(1) DEFAULT b'0',
                                                           PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf16;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella ftest.fabcms_formazione_courses_media
CREATE TABLE IF NOT EXISTS `fabcms_formazione_courses_media` (
                                                                 `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                                 `course_ID` mediumint(8) unsigned DEFAULT NULL,
                                                                 `media_ID` mediumint(8) unsigned DEFAULT NULL,
                                                                 `access_level` tinyint(4) unsigned NOT NULL DEFAULT 0,
                                                                 `order` smallint(6) unsigned NOT NULL DEFAULT 0,
                                                                 PRIMARY KEY (`ID`),
                                                                 KEY `course_ID` (`course_ID`),
                                                                 KEY `media_ID` (`media_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella ftest.fabcms_formazione_courses_subscriptions
CREATE TABLE IF NOT EXISTS `fabcms_formazione_courses_subscriptions` (
                                                                         `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                                         `user_ID` mediumint(8) unsigned DEFAULT NULL,
                                                                         `course_ID` mediumint(8) unsigned DEFAULT NULL,
                                                                         `purchase_date` date DEFAULT NULL,
                                                                         `expiring_date` date DEFAULT NULL,
                                                                         PRIMARY KEY (`ID`),
                                                                         KEY `user_ID` (`user_ID`),
                                                                         KEY `course_ID` (`course_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella ftest.fabcms_formazione_media
CREATE TABLE IF NOT EXISTS `fabcms_formazione_media` (
                                                         `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                         `youtube_ID` varchar(255) CHARACTER SET utf8 NOT NULL,
                                                         `full_ID` varchar(50) DEFAULT NULL COMMENT 'ID of the full content',
                                                         `name` varchar(255) CHARACTER SET utf8 NOT NULL,
                                                         `name_trackback` varchar(255) CHARACTER SET utf8 NOT NULL,
                                                         `access_level` tinyint(4) unsigned NOT NULL,
                                                         `type` int(11) NOT NULL,
                                                         `subtype` int(11) NOT NULL,
                                                         `filename` varchar(255) CHARACTER SET utf8 NOT NULL,
                                                         `URI` varchar(255) CHARACTER SET utf8 NOT NULL,
                                                         `description_short` tinytext DEFAULT NULL,
                                                         `description` text DEFAULT NULL,
                                                         `description_seo` text DEFAULT NULL,
                                                         `keywords` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
                                                         `plugin_full` varchar(50) DEFAULT NULL,
                                                         `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
                                                         PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;
