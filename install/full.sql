-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versione server:              10.7.3-MariaDB - mariadb.org binary distribution
-- S.O. server:                  Win64
-- HeidiSQL Versione:            12.6.0.6765
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dump della struttura di tabella fabcms.fabcms_config
CREATE TABLE IF NOT EXISTS `fabcms_config` (
                                               `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                               `lang` varchar(2) DEFAULT NULL,
                                               `module` varchar(255) DEFAULT NULL,
                                               `param` varchar(255) DEFAULT NULL,
                                               `value` varchar(255) DEFAULT NULL,
                                               `extended_value` text DEFAULT NULL,
                                               PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_connectors
CREATE TABLE IF NOT EXISTS `fabcms_connectors` (
                                                   `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                   `handler` varchar(255) NOT NULL,
                                                   `connector` varchar(50) NOT NULL,
                                                   `action` varchar(255) NOT NULL,
                                                   `additional_data` text NOT NULL,
                                                   `order` tinyint(3) unsigned NOT NULL,
                                                   `enabled` tinyint(3) unsigned NOT NULL,
                                                   PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_core_modules
CREATE TABLE IF NOT EXISTS `fabcms_core_modules` (
                                                     `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                     `module` varchar(255) DEFAULT NULL,
                                                     `schema` smallint(5) unsigned DEFAULT NULL,
                                                     `config` text DEFAULT NULL,
                                                     `enabled` tinyint(4) DEFAULT NULL,
                                                     PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_cronjobs
CREATE TABLE IF NOT EXISTS `fabcms_cronjobs` (
                                                 `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                 `module` varchar(255) DEFAULT NULL,
                                                 `operation` varchar(255) DEFAULT NULL,
                                                 `additional_data` text DEFAULT NULL,
                                                 `interval` smallint(5) unsigned zerofill DEFAULT NULL COMMENT 'Expressed in minutes',
                                                 `latest_check` datetime DEFAULT NULL,
                                                 `next_run` datetime DEFAULT NULL,
                                                 `latest_status` tinyint(3) unsigned DEFAULT NULL COMMENT '0 = no entry; 1 = completed; 2 = error',
                                                 `log` text DEFAULT NULL,
                                                 `enabled` tinyint(3) unsigned DEFAULT NULL COMMENT '0 = disabled; 1 = enabled',
                                                 PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_fabmedia
CREATE TABLE IF NOT EXISTS `fabcms_fabmedia` (
                                                 `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                 `master_ID` mediumint(9) unsigned DEFAULT NULL,
                                                 `license_ID` smallint(5) unsigned DEFAULT NULL,
                                                 `user_ID` mediumint(9) unsigned NOT NULL,
                                                 `lang` varchar(2) DEFAULT NULL,
                                                 `filename` varchar(255) DEFAULT NULL,
                                                 `extension` varchar(32) DEFAULT NULL,
                                                 `type` varchar(64) DEFAULT NULL,
                                                 `subtype` varchar(64) DEFAULT NULL,
                                                 `modified` tinyint(4) unsigned DEFAULT 0,
                                                 `upload_date` datetime DEFAULT current_timestamp(),
                                                 `size` int(10) unsigned DEFAULT NULL,
                                                 `modify_date` date DEFAULT NULL,
                                                 `title` varchar(255) DEFAULT NULL,
                                                 `trackback` varchar(255) DEFAULT NULL,
                                                 `author` varchar(255) DEFAULT NULL,
                                                 `link` varchar(255) DEFAULT NULL,
                                                 `tags` varchar(255) DEFAULT NULL,
                                                 `description` text DEFAULT NULL,
                                                 `indexable` tinyint(4) unsigned DEFAULT NULL,
                                                 `global_available` tinyint(4) unsigned DEFAULT NULL,
                                                 `allow_download` tinyint(4) unsigned DEFAULT NULL,
                                                 `guest_downlod` tinyint(4) unsigned DEFAULT NULL,
                                                 `notes` text DEFAULT NULL COMMENT 'Internal notes',
                                                 `enabled` tinyint(4) unsigned DEFAULT NULL,
                                                 PRIMARY KEY (`ID`),
                                                 KEY `license_ID` (`license_ID`),
                                                 KEY `master_ID` (`master_ID`),
                                                 KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 AVG_ROW_LENGTH=235 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_fabmedia_custom_filetypes
CREATE TABLE IF NOT EXISTS `fabcms_fabmedia_custom_filetypes` (
                                                                  `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                                  `extension` varchar(32) NOT NULL,
                                                                  `import_plugins` varchar(1024) NOT NULL COMMENT 'Comma separated',
                                                                  `export_plugins` varchar(1024) NOT NULL COMMENT 'Comma separated',
                                                                  `notes` text NOT NULL,
                                                                  `module` varchar(50) DEFAULT NULL,
                                                                  `allowed_group` varchar(1024) DEFAULT NULL COMMENT 'Comma separated values',
                                                                  `enabled` tinyint(4) unsigned DEFAULT NULL,
                                                                  PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 AVG_ROW_LENGTH=3276 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_fabmedia_downloads
CREATE TABLE IF NOT EXISTS `fabcms_fabmedia_downloads` (
                                                           `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                           `user_ID` mediumint(9) unsigned NOT NULL,
                                                           `media_ID` mediumint(9) unsigned NOT NULL,
                                                           `date` datetime NOT NULL DEFAULT current_timestamp(),
                                                           `is_anonymous` tinyint(4) unsigned DEFAULT NULL,
                                                           `is_bot` tinyint(4) unsigned DEFAULT NULL,
                                                           `IP` varchar(15) NOT NULL,
                                                           PRIMARY KEY (`ID`),
                                                           KEY `media_ID` (`media_ID`),
                                                           KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_fabmedia_galleries_galleries
CREATE TABLE IF NOT EXISTS `fabcms_fabmedia_galleries_galleries` (
                                                                     `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                                     `master_ID` mediumint(9) unsigned DEFAULT NULL,
                                                                     `user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                                     `cover_ID` mediumint(9) unsigned DEFAULT NULL,
                                                                     `lang` varchar(2) DEFAULT NULL,
                                                                     `title` varchar(255) DEFAULT NULL,
                                                                     `trackback` varchar(255) DEFAULT NULL,
                                                                     `meta_description` varchar(255) DEFAULT NULL,
                                                                     `description` text DEFAULT NULL,
                                                                     `order` mediumint(9) unsigned DEFAULT NULL,
                                                                     `visible` tinyint(4) unsigned DEFAULT NULL,
                                                                     PRIMARY KEY (`ID`),
                                                                     KEY `cover_ID` (`cover_ID`),
                                                                     KEY `master_ID` (`master_ID`),
                                                                     KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_fabmedia_galleries_items
CREATE TABLE IF NOT EXISTS `fabcms_fabmedia_galleries_items` (
                                                                 `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                                 `user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                                 `image_ID` mediumint(9) unsigned DEFAULT NULL,
                                                                 `gallery_ID` mediumint(9) unsigned DEFAULT NULL,
                                                                 `order` smallint(5) unsigned DEFAULT NULL,
                                                                 `visible` tinyint(4) unsigned DEFAULT NULL,
                                                                 PRIMARY KEY (`ID`),
                                                                 KEY `gallery_ID` (`gallery_ID`),
                                                                 KEY `image_ID` (`image_ID`),
                                                                 KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_fabmedia_galleries_masters
CREATE TABLE IF NOT EXISTS `fabcms_fabmedia_galleries_masters` (
                                                                   `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                                   PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_fabmedia_images
CREATE TABLE IF NOT EXISTS `fabcms_fabmedia_images` (
                                                        `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                        `file_ID` mediumint(9) unsigned NOT NULL,
                                                        `width` mediumint(9) unsigned NOT NULL,
                                                        `height` mediumint(9) unsigned NOT NULL,
                                                        `gps` geometry NOT NULL,
                                                        `metadata` text DEFAULT NULL,
                                                        `brightness` tinyint(255) unsigned NOT NULL DEFAULT 255,
                                                        PRIMARY KEY (`ID`),
                                                        KEY `file_ID` (`file_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 AVG_ROW_LENGTH=68 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_fabmedia_masters
CREATE TABLE IF NOT EXISTS `fabcms_fabmedia_masters` (
                                                         `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                         `user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                         PRIMARY KEY (`ID`),
                                                         KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 AVG_ROW_LENGTH=1489 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_fabmedia_videos
CREATE TABLE IF NOT EXISTS `fabcms_fabmedia_videos` (
                                                        `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                        `fabmedia_ID` mediumint(9) unsigned NOT NULL,
                                                        `provider` varchar(50) NOT NULL COMMENT 'internal, youtube, vimeo',
                                                        `provider_ID` varchar(255) DEFAULT NULL,
                                                        `length` time DEFAULT NULL,
                                                        `allow_download` tinyint(4) unsigned DEFAULT NULL,
                                                        `allow_share` tinyint(4) unsigned DEFAULT NULL,
                                                        `allow_embedding` tinyint(4) unsigned DEFAULT NULL,
                                                        `visible` tinyint(4) unsigned DEFAULT NULL,
                                                        PRIMARY KEY (`ID`),
                                                        KEY `fabmedia_ID` (`fabmedia_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 AVG_ROW_LENGTH=2048 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_fabmenu
CREATE TABLE IF NOT EXISTS `fabcms_fabmenu` (
                                                `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                `parent_ID` mediumint(9) unsigned DEFAULT NULL,
                                                `lang` varchar(2) DEFAULT NULL,
                                                `type` tinyint(4) unsigned DEFAULT NULL COMMENT '1 = custom url, 2 = generator',
                                                `url` varchar(255) DEFAULT NULL,
                                                `name` varchar(128) DEFAULT NULL,
                                                `icon` varchar(32) DEFAULT NULL,
                                                `module` varchar(48) DEFAULT NULL,
                                                `generator` varchar(255) DEFAULT NULL,
                                                `generator_options` varchar(255) DEFAULT NULL,
                                                `order` mediumint(9) unsigned DEFAULT NULL,
                                                PRIMARY KEY (`ID`),
                                                KEY `parent_ID` (`parent_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_formazione_courses
CREATE TABLE IF NOT EXISTS `fabcms_formazione_courses` (
                                                           `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                           `shop_item_ID` mediumint(8) unsigned DEFAULT NULL,
                                                           `name` varchar(255) CHARACTER SET utf8mb3 DEFAULT NULL,
                                                           `name_trackback` varchar(255) CHARACTER SET utf8mb3 DEFAULT NULL,
                                                           `tags` varchar(255) CHARACTER SET utf8mb3 DEFAULT NULL,
                                                           `avaliable_date` date DEFAULT NULL,
                                                           `short_description` text CHARACTER SET utf8mb3 NOT NULL,
                                                           `SEO_description` varchar(512) CHARACTER SET utf8mb3 NOT NULL DEFAULT '0',
                                                           `thumb_image` varchar(255) CHARACTER SET utf8mb3 DEFAULT NULL,
                                                           `description` text CHARACTER SET utf8mb3 NOT NULL,
                                                           `subscription_link` varchar(255) CHARACTER SET utf8mb3 NOT NULL,
                                                           `avaliable` tinyint(3) unsigned DEFAULT NULL,
                                                           `visible` tinyint(3) unsigned DEFAULT NULL,
                                                           PRIMARY KEY (`ID`),
                                                           KEY `ID_shop_item` (`shop_item_ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_formazione_courses_media
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

-- Dump della struttura di tabella fabcms.fabcms_formazione_courses_subscriptions
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

-- Dump della struttura di tabella fabcms.fabcms_formazione_media
CREATE TABLE IF NOT EXISTS `fabcms_formazione_media` (
                                                         `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                         `youtube_ID` varchar(255) CHARACTER SET utf8mb3 DEFAULT NULL,
                                                         `full_ID` varchar(50) DEFAULT NULL COMMENT 'ID of the full content',
                                                         `name` varchar(255) CHARACTER SET utf8mb3 DEFAULT NULL,
                                                         `name_trackback` varchar(255) CHARACTER SET utf8mb3 DEFAULT NULL,
                                                         `access_level` tinyint(4) unsigned DEFAULT NULL,
                                                         `type` int(11) DEFAULT NULL,
                                                         `subtype` int(11) DEFAULT NULL,
                                                         `filename` varchar(255) CHARACTER SET utf8mb3 DEFAULT NULL,
                                                         `URI` varchar(255) CHARACTER SET utf8mb3 NOT NULL,
                                                         `description_short` tinytext DEFAULT NULL,
                                                         `description` mediumtext DEFAULT NULL,
                                                         `description_seo` mediumtext DEFAULT NULL,
                                                         `keywords` varchar(255) CHARACTER SET utf8mb3 DEFAULT NULL,
                                                         `plugin_full` varchar(50) DEFAULT NULL,
                                                         `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                                                         PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_bans
CREATE TABLE IF NOT EXISTS `fabcms_forum_bans` (
                                                   `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                   `user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                   `banned_by_user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                   `ban_reason` varchar(1024) DEFAULT NULL,
                                                   `ban_note` text DEFAULT NULL,
                                                   `ban_start_date` datetime DEFAULT NULL,
                                                   `ban_end_date` datetime DEFAULT NULL,
                                                   PRIMARY KEY (`ID`),
                                                   KEY `banned_by_user_ID` (`banned_by_user_ID`),
                                                   KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_categories
CREATE TABLE IF NOT EXISTS `fabcms_forum_categories` (
                                                         `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                         `category_name` varchar(255) NOT NULL DEFAULT '0',
                                                         `language` varchar(2) NOT NULL DEFAULT '0',
                                                         `order` smallint(5) unsigned DEFAULT NULL,
                                                         `enabled` tinyint(4) NOT NULL DEFAULT 0,
                                                         PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_category_visibility
CREATE TABLE IF NOT EXISTS `fabcms_forum_category_visibility` (
                                                                  `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                                  `category_ID` mediumint(9) unsigned DEFAULT NULL,
                                                                  `group_ID` mediumint(9) unsigned DEFAULT NULL,
                                                                  `status` mediumint(9) unsigned DEFAULT NULL COMMENT '0 = not visible; 1 = visible;',
                                                                  PRIMARY KEY (`ID`),
                                                                  KEY `category_ID` (`category_ID`),
                                                                  KEY `group_ID` (`group_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_config
CREATE TABLE IF NOT EXISTS `fabcms_forum_config` (
                                                     `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                     `param` varchar(255) NOT NULL,
                                                     `value` varchar(2056) NOT NULL,
                                                     `extended_valute` text DEFAULT NULL,
                                                     PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_edits
CREATE TABLE IF NOT EXISTS `fabcms_forum_edits` (
                                                    `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                    `reply_user_ID` mediumint(9) unsigned NOT NULL DEFAULT 0,
                                                    `type` tinyint(4) unsigned DEFAULT NULL COMMENT '0 = topic; 1 = post',
                                                    `reason` varchar(255) NOT NULL,
                                                    `reply_approved` tinyint(4) unsigned DEFAULT NULL,
                                                    `reply_approved_by_user_ID` tinyint(4) unsigned DEFAULT NULL,
                                                    `reply_approved_date` datetime DEFAULT NULL,
                                                    PRIMARY KEY (`ID`),
                                                    KEY `reply_user_ID` (`reply_user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_forums
CREATE TABLE IF NOT EXISTS `fabcms_forum_forums` (
                                                     `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                     `category_ID` mediumint(9) unsigned NOT NULL,
                                                     `latest_thread_user_ID` mediumint(11) unsigned NOT NULL,
                                                     `latest_thread_ID` mediumint(8) unsigned NOT NULL,
                                                     `lang` varchar(2) NOT NULL,
                                                     `forum_name` varchar(255) NOT NULL,
                                                     `forum_trackback` varchar(255) DEFAULT NULL,
                                                     `forum_description` text DEFAULT NULL,
                                                     `threads_count` mediumint(8) unsigned NOT NULL DEFAULT 0,
                                                     `forum_password` varchar(255) DEFAULT NULL,
                                                     `order` smallint(5) unsigned NOT NULL,
                                                     `visible` tinyint(4) unsigned NOT NULL DEFAULT 0,
                                                     PRIMARY KEY (`ID`),
                                                     KEY `category_ID` (`category_ID`),
                                                     KEY `latest_thread_ID` (`latest_thread_ID`),
                                                     KEY `latest_thread_user_ID` (`latest_thread_user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_groups
CREATE TABLE IF NOT EXISTS `fabcms_forum_groups` (
                                                     `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                     `group_name` varchar(255) DEFAULT NULL,
                                                     `group_description` text DEFAULT NULL,
                                                     `is_guest` tinyint(4) unsigned DEFAULT NULL,
                                                     `is_registered` tinyint(4) unsigned DEFAULT NULL,
                                                     `is_moderator` tinyint(4) unsigned DEFAULT NULL,
                                                     `is_global_moderator` tinyint(4) unsigned DEFAULT NULL,
                                                     `is_admin` tinyint(4) unsigned DEFAULT NULL,
                                                     `group_html_color` varchar(6) DEFAULT NULL,
                                                     PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_groups_permissions
CREATE TABLE IF NOT EXISTS `fabcms_forum_groups_permissions` (
                                                                 `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                                 `group_ID` mediumint(9) unsigned DEFAULT NULL,
                                                                 `can_update_config` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_warn_users` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_ban_users` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_edit_replies` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_delete_replies` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_hide_replies` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_post_replies` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_delete_own_replies` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_edit_own_replies` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_edit_topics` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_lock_topics` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_hide_topics` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_delete_topics` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_approve_topics` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_stick_topics` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_delete_own_topics` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_view_other_topics` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_edit_own_topics` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_set_topic_importance` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_merge_topics` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_post_topics` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_unapprove_signatures` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_edit_signatures` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_move_topics` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_manage_forums` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_manage_categories` tinyint(4) unsigned DEFAULT NULL,
                                                                 `can_manage_threads` tinyint(4) unsigned DEFAULT NULL,
                                                                 PRIMARY KEY (`ID`),
                                                                 KEY `group_ID` (`group_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_groups_users
CREATE TABLE IF NOT EXISTS `fabcms_forum_groups_users` (
                                                           `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                           `group_ID` mediumint(9) unsigned DEFAULT NULL,
                                                           `user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                           `note` text DEFAULT NULL,
                                                           `added_date` date DEFAULT NULL,
                                                           PRIMARY KEY (`ID`),
                                                           KEY `group_ID` (`group_ID`),
                                                           KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_logs
CREATE TABLE IF NOT EXISTS `fabcms_forum_logs` (
                                                   `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                                   `user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                   `IDX` mediumint(9) unsigned DEFAULT NULL,
                                                   `event` varchar(255) DEFAULT NULL,
                                                   `log` text DEFAULT NULL,
                                                   PRIMARY KEY (`ID`),
                                                   KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_parsers
CREATE TABLE IF NOT EXISTS `fabcms_forum_parsers` (
                                                      `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                      `parser` varchar(128) DEFAULT NULL,
                                                      `order` tinyint(3) unsigned DEFAULT NULL,
                                                      `enabled` tinyint(4) unsigned DEFAULT NULL,
                                                      PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_pm
CREATE TABLE IF NOT EXISTS `fabcms_forum_pm` (
                                                 `ID` tinyint(4) unsigned NOT NULL AUTO_INCREMENT,
                                                 `user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                 `user_destination_ID` mediumint(9) unsigned DEFAULT NULL,
                                                 `moderation_requested_user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                 `moderation_approved_user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                 `start_date` datetime DEFAULT NULL,
                                                 `last_message_date` datetime DEFAULT NULL,
                                                 `moderation_request_date` datetime DEFAULT NULL,
                                                 `moderation_requested` tinyint(4) unsigned DEFAULT NULL,
                                                 `moderation_approved` tinyint(4) unsigned DEFAULT NULL,
                                                 `is_bot` tinyint(4) unsigned DEFAULT NULL,
                                                 `is_unread` tinyint(4) unsigned DEFAULT NULL,
                                                 `visible` tinyint(4) unsigned DEFAULT NULL,
                                                 PRIMARY KEY (`ID`),
                                                 KEY `moderation_approved_user_ID` (`moderation_approved_user_ID`),
                                                 KEY `moderation_requested_user_ID` (`moderation_requested_user_ID`),
                                                 KEY `user_destination_ID` (`user_destination_ID`),
                                                 KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_pm_messages
CREATE TABLE IF NOT EXISTS `fabcms_forum_pm_messages` (
                                                          `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                          `user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                          `pm_ID` mediumint(9) unsigned DEFAULT NULL,
                                                          `moderation_requested_user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                          `moderation_approved_by_user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                          `message` text DEFAULT NULL,
                                                          `message_date` datetime DEFAULT NULL,
                                                          `moderation_request_date` datetime DEFAULT NULL,
                                                          `moderation_requested` tinyint(4) unsigned DEFAULT NULL,
                                                          `moderation_approved` tinyint(4) unsigned DEFAULT NULL,
                                                          `is_bot` tinyint(4) DEFAULT NULL,
                                                          PRIMARY KEY (`ID`),
                                                          KEY `moderation_approved_by_user_ID` (`moderation_approved_by_user_ID`),
                                                          KEY `moderation_requested_user_ID` (`moderation_requested_user_ID`),
                                                          KEY `pm_ID` (`pm_ID`),
                                                          KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_ranks
CREATE TABLE IF NOT EXISTS `fabcms_forum_ranks` (
                                                    `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                    `name` varchar(255) CHARACTER SET utf16 NOT NULL DEFAULT '0',
                                                    `post_required` mediumint(8) unsigned NOT NULL DEFAULT 0,
                                                    `stars` tinyint(3) unsigned NOT NULL DEFAULT 0,
                                                    `icon` varchar(255) DEFAULT NULL,
                                                    PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1 ROW_FORMAT=PAGE;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_reactions
CREATE TABLE IF NOT EXISTS `fabcms_forum_reactions` (
                                                        `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                        `user_ID` mediumint(8) unsigned DEFAULT NULL,
                                                        `topic_ID` mediumint(9) unsigned DEFAULT NULL,
                                                        `reply_id` mediumint(9) unsigned DEFAULT NULL,
                                                        `type` tinyint(3) unsigned DEFAULT NULL COMMENT '1 = topic; 2 = reply',
                                                        `date` datetime DEFAULT NULL COMMENT '0 = topic; 1 = reply;',
                                                        `reaction_type` tinyint(4) unsigned DEFAULT NULL COMMENT '1 = approve; 2 = angry; 3 = love; 4 = wow; 5 = shocked',
                                                        PRIMARY KEY (`ID`),
                                                        KEY `reply_id` (`reply_id`),
                                                        KEY `topic_ID` (`topic_ID`),
                                                        KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_replies
CREATE TABLE IF NOT EXISTS `fabcms_forum_replies` (
                                                      `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                      `user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                      `topic_ID` mediumint(9) unsigned DEFAULT NULL,
                                                      `date` datetime DEFAULT NULL,
                                                      `reply` text DEFAULT NULL,
                                                      `IP` varchar(15) DEFAULT NULL,
                                                      `is_edited` tinyint(4) unsigned DEFAULT NULL,
                                                      `is_best_answer` tinyint(4) unsigned DEFAULT NULL,
                                                      `approved` tinyint(4) unsigned DEFAULT NULL,
                                                      `approved_date` tinyint(4) unsigned DEFAULT NULL,
                                                      `visible` tinyint(3) unsigned DEFAULT NULL,
                                                      PRIMARY KEY (`ID`),
                                                      KEY `topic_ID` (`topic_ID`),
                                                      KEY `user_ID` (`user_ID`),
                                                      FULLTEXT KEY `reply` (`reply`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_signatures
CREATE TABLE IF NOT EXISTS `fabcms_forum_signatures` (
                                                         `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                         `user_ID` mediumint(9) unsigned NOT NULL,
                                                         `signature` varchar(2048) NOT NULL,
                                                         `signature_unapproved` tinyint(4) unsigned NOT NULL DEFAULT 0,
                                                         PRIMARY KEY (`ID`),
                                                         KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_subscriptions
CREATE TABLE IF NOT EXISTS `fabcms_forum_subscriptions` (
                                                            `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                            `user_ID` mediumint(9) unsigned NOT NULL,
                                                            `topic_ID` mediumint(9) unsigned NOT NULL,
                                                            `latest_check_date` datetime NOT NULL,
                                                            `notify_sent` tinyint(3) unsigned NOT NULL,
                                                            `status` tinyint(3) unsigned NOT NULL DEFAULT 1 COMMENT '0 = disabled, 1 = enabled, 2 = removed;',
                                                            PRIMARY KEY (`ID`),
                                                            KEY `topic_ID` (`topic_ID`),
                                                            KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_threads
CREATE TABLE IF NOT EXISTS `fabcms_forum_threads` (
                                                      `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                      `category_ID` mediumint(9) unsigned NOT NULL,
                                                      `latest_topic_ID` mediumint(8) unsigned NOT NULL,
                                                      `latest_topic_user_ID` smallint(5) unsigned NOT NULL,
                                                      `is_sub_threads_of_thread_ID` mediumint(9) unsigned DEFAULT NULL,
                                                      `lang` varchar(2) NOT NULL,
                                                      `thread_name` varchar(255) NOT NULL,
                                                      `thread_trackback` varchar(255) DEFAULT NULL,
                                                      `thread_description` text DEFAULT NULL,
                                                      `topics_count` mediumint(8) unsigned NOT NULL DEFAULT 0,
                                                      `order` smallint(5) unsigned NOT NULL,
                                                      `visible` tinyint(4) unsigned NOT NULL DEFAULT 0,
                                                      `has_subthreads` tinyint(4) unsigned DEFAULT NULL,
                                                      `topic_approvation` tinyint(4) unsigned DEFAULT NULL COMMENT 'Specify if the threads contains topics that requires moderation/approvation',
                                                      PRIMARY KEY (`ID`),
                                                      KEY `category_ID` (`category_ID`),
                                                      KEY `is_sub_threads_of_thread_ID` (`is_sub_threads_of_thread_ID`),
                                                      KEY `latest_topic_ID` (`latest_topic_ID`),
                                                      KEY `latest_topic_user_ID` (`latest_topic_user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_threads_groups
CREATE TABLE IF NOT EXISTS `fabcms_forum_threads_groups` (
                                                             `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                             `group_ID` mediumint(9) unsigned DEFAULT NULL,
                                                             PRIMARY KEY (`ID`),
                                                             KEY `group_ID` (`group_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1 COMMENT='Groups that can control threads';

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_thread_visibility
CREATE TABLE IF NOT EXISTS `fabcms_forum_thread_visibility` (
                                                                `ID` mediumint(9) unsigned DEFAULT NULL,
                                                                `thred_ID` mediumint(9) unsigned DEFAULT NULL,
                                                                `group_ID` mediumint(9) unsigned DEFAULT NULL,
                                                                `status` tinyint(4) unsigned DEFAULT 1 COMMENT '0 = not visible; 1 = visible;',
                                                                KEY `group_ID` (`group_ID`),
                                                                KEY `thred_ID` (`thred_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_topics
CREATE TABLE IF NOT EXISTS `fabcms_forum_topics` (
                                                     `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                     `thread_ID` mediumint(9) unsigned DEFAULT NULL,
                                                     `user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                     `latest_reply_user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                     `moved_topic_ID` mediumint(9) unsigned DEFAULT NULL,
                                                     `moved_thread_ID` mediumint(9) unsigned DEFAULT NULL,
                                                     `best_reply_ID` mediumint(9) unsigned DEFAULT NULL,
                                                     `duplicate_of_topic_ID` mediumint(9) unsigned DEFAULT NULL,
                                                     `approved_by_user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                     `topic_trackback` varchar(255) DEFAULT NULL,
                                                     `reply_count` smallint(5) unsigned DEFAULT NULL,
                                                     `moved_topic_trackback` varchar(255) DEFAULT NULL,
                                                     `moved_thread_trackback` varchar(255) DEFAULT NULL,
                                                     `topic_title` varchar(255) DEFAULT NULL,
                                                     `tags` varchar(255) DEFAULT NULL,
                                                     `date_created` datetime DEFAULT NULL,
                                                     `date_latest_update` datetime DEFAULT NULL,
                                                     `replies` smallint(5) unsigned DEFAULT NULL,
                                                     `topic_message` text DEFAULT NULL,
                                                     `pinned` tinyint(3) unsigned DEFAULT NULL,
                                                     `approved` tinyint(4) unsigned DEFAULT NULL,
                                                     `approved_date` date DEFAULT NULL,
                                                     `locked` tinyint(4) unsigned DEFAULT NULL,
                                                     `IP` varchar(15) DEFAULT NULL,
                                                     `minimum_post_required` tinyint(4) unsigned DEFAULT 0,
                                                     `visible` tinyint(4) unsigned DEFAULT NULL,
                                                     PRIMARY KEY (`ID`),
                                                     KEY `approved_by_user_ID` (`approved_by_user_ID`),
                                                     KEY `best_reply_ID` (`best_reply_ID`),
                                                     KEY `duplicate_of_topic_ID` (`duplicate_of_topic_ID`),
                                                     KEY `latest_reply_user_ID` (`latest_reply_user_ID`),
                                                     KEY `moved_thread_ID` (`moved_thread_ID`),
                                                     KEY `moved_topic_ID` (`moved_topic_ID`),
                                                     KEY `thread_ID` (`thread_ID`),
                                                     KEY `user_ID` (`user_ID`),
                                                     FULLTEXT KEY `topic_message` (`topic_message`),
                                                     FULLTEXT KEY `topic_title` (`topic_title`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_triggers
CREATE TABLE IF NOT EXISTS `fabcms_forum_triggers` (
                                                       `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                       `event` varchar(255) DEFAULT NULL,
                                                       `trigger` varchar(255) DEFAULT NULL,
                                                       `trigger_data` text DEFAULT NULL,
                                                       `enabled` tinyint(4) unsigned DEFAULT 0,
                                                       PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_user_config
CREATE TABLE IF NOT EXISTS `fabcms_forum_user_config` (
                                                          `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                          `user_ID` mediumint(9) unsigned NOT NULL DEFAULT 0,
                                                          `user_avatar` varchar(255) DEFAULT NULL,
                                                          `user_avatar_type` tinyint(4) unsigned DEFAULT NULL COMMENT ' 0 = internal, 1 = custom',
                                                          `email_notify` tinyint(4) unsigned NOT NULL DEFAULT 1 COMMENT '0 = false (no notifications), 1 = true;',
                                                          PRIMARY KEY (`ID`),
                                                          KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_user_stats
CREATE TABLE IF NOT EXISTS `fabcms_forum_user_stats` (
                                                         `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                         `user_ID` mediumint(8) unsigned NOT NULL,
                                                         `reply_count` mediumint(8) unsigned NOT NULL,
                                                         `topic_count` mediumint(8) unsigned NOT NULL,
                                                         `latest_post` datetime NOT NULL,
                                                         `latest_reply` datetime NOT NULL,
                                                         PRIMARY KEY (`ID`),
                                                         KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_forum_warns
CREATE TABLE IF NOT EXISTS `fabcms_forum_warns` (
                                                    `ID` mediumint(9) unsigned DEFAULT NULL,
                                                    `user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                    `warned_by_user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                    `warn_resource_ID` mediumint(9) unsigned DEFAULT NULL,
                                                    `warn_date` date DEFAULT NULL,
                                                    `warn_reason` text DEFAULT NULL,
                                                    `warn_type` smallint(6) unsigned DEFAULT NULL COMMENT '0 = topic, 1 = post; 3 = PM, 4 = hack',
                                                    KEY `user_ID` (`user_ID`),
                                                    KEY `warn_resource_ID` (`warn_resource_ID`),
                                                    KEY `warned_by_user_ID` (`warned_by_user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_hooks
CREATE TABLE IF NOT EXISTS `fabcms_hooks` (
                                              `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                              `lang` tinyint(3) unsigned DEFAULT NULL,
                                              `valid_from` datetime DEFAULT NULL,
                                              `valid_to` datetime DEFAULT NULL,
                                              `name` varchar(255) CHARACTER SET utf8mb3 NOT NULL,
                                              `html` text CHARACTER SET utf8mb3 NOT NULL,
                                              `order` tinyint(4) DEFAULT NULL,
                                              `enabled` tinyint(4) unsigned NOT NULL,
                                              PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf16 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_licenses_licenses
CREATE TABLE IF NOT EXISTS `fabcms_licenses_licenses` (
                                                          `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                          `master_ID` mediumint(9) unsigned DEFAULT NULL,
                                                          `lang` varchar(2) DEFAULT NULL,
                                                          `name` varchar(255) DEFAULT NULL,
                                                          `description` text DEFAULT NULL,
                                                          `allow_derivate_works` tinyint(3) unsigned DEFAULT NULL,
                                                          `allow_share` tinyint(3) unsigned DEFAULT NULL,
                                                          `mandatory_credits` tinyint(3) unsigned DEFAULT NULL,
                                                          PRIMARY KEY (`ID`),
                                                          KEY `master_ID` (`master_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_licenses_master
CREATE TABLE IF NOT EXISTS `fabcms_licenses_master` (
                                                        `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                        PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_logs
CREATE TABLE IF NOT EXISTS `fabcms_logs` (
                                             `ID` int(11) NOT NULL AUTO_INCREMENT,
                                             `IP` varchar(32) NOT NULL,
                                             `date` datetime NOT NULL,
                                             `userID` int(11) NOT NULL,
                                             `type` varchar(255) NOT NULL,
                                             `module` varchar(255) NOT NULL,
                                             `log` longtext NOT NULL,
                                             PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_notifications
CREATE TABLE IF NOT EXISTS `fabcms_notifications` (
                                                      `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                      `user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                      `date` datetime DEFAULT NULL,
                                                      `module` varchar(255) DEFAULT NULL,
                                                      `title` varchar(255) DEFAULT NULL,
                                                      `text` varchar(512) DEFAULT NULL,
                                                      `is_read` tinyint(4) unsigned DEFAULT NULL,
                                                      `is_bot` tinyint(4) unsigned DEFAULT NULL COMMENT '0 = user generated; 1 = bot generated',
                                                      PRIMARY KEY (`ID`),
                                                      KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_orders
CREATE TABLE IF NOT EXISTS `fabcms_orders` (
                                               `ID` int(11) DEFAULT NULL,
                                               `user_ID` int(11) DEFAULT NULL,
                                               `cart_ID` int(11) DEFAULT NULL,
                                               `order_date` datetime DEFAULT NULL,
                                               `total` decimal(10,2) DEFAULT NULL,
                                               `discount` int(11) DEFAULT NULL,
                                               `status` tinyint(4) DEFAULT NULL COMMENT '0 = open order; 1 = closed partially; 2 = closed',
                                               `refunded` tinyint(4) DEFAULT NULL COMMENT '1 = yes'
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_plugins_chain
CREATE TABLE IF NOT EXISTS `fabcms_plugins_chain` (
                                                      `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                      `spot` varchar(255) NOT NULL,
                                                      `type` varchar(255) NOT NULL,
                                                      `target` varchar(255) NOT NULL,
                                                      `data` text NOT NULL,
                                                      `visible` tinyint(4) unsigned NOT NULL,
                                                      PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_quiz_categories
CREATE TABLE IF NOT EXISTS `fabcms_quiz_categories` (
                                                        `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                        `nome` varchar(50) NOT NULL,
                                                        `title` varchar(255) NOT NULL DEFAULT '0',
                                                        `meta_description` varchar(512) NOT NULL DEFAULT '0',
                                                        `icon` varchar(255) NOT NULL,
                                                        `schede_fatte` mediumint(8) unsigned NOT NULL DEFAULT 0,
                                                        `visibile` mediumint(8) unsigned NOT NULL DEFAULT 0,
                                                        `lang` varchar(2) NOT NULL,
                                                        `short_description` varchar(255) NOT NULL,
                                                        `long_description` text NOT NULL,
                                                        PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_quiz_logs
CREATE TABLE IF NOT EXISTS `fabcms_quiz_logs` (
                                                  `ID` int(11) NOT NULL AUTO_INCREMENT,
                                                  `user_ID` int(11) NOT NULL,
                                                  `date` datetime NOT NULL,
                                                  `type` varchar(255) NOT NULL,
                                                  `subtype` int(8) NOT NULL,
                                                  `ok` varchar(255) NOT NULL,
                                                  `ko` varchar(255) NOT NULL,
                                                  `blank` varchar(255) NOT NULL,
                                                  PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_quiz_questions
CREATE TABLE IF NOT EXISTS `fabcms_quiz_questions` (
                                                       `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                       `domanda` mediumtext DEFAULT NULL,
                                                       `categorie` varchar(50) DEFAULT NULL,
                                                       `risposta_1` mediumtext DEFAULT NULL,
                                                       `risposta_2` mediumtext DEFAULT NULL,
                                                       `risposta_3` mediumtext DEFAULT NULL,
                                                       `risposta_4` mediumtext DEFAULT NULL,
                                                       `argomenti` varchar(255) DEFAULT NULL,
                                                       `appunti` varchar(255) DEFAULT NULL,
                                                       `pagine` varchar(255) DEFAULT NULL,
                                                       `views` mediumint(8) unsigned DEFAULT 0,
                                                       `ok` int(11) DEFAULT NULL,
                                                       `ko` int(11) DEFAULT NULL,
                                                       `blank` int(11) DEFAULT NULL,
                                                       PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_relog
CREATE TABLE IF NOT EXISTS `fabcms_relog` (
                                              `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                              `user_ID` mediumint(8) unsigned DEFAULT NULL,
                                              `date` datetime DEFAULT NULL,
                                              `module` varchar(255) DEFAULT NULL,
                                              `type` tinyint(4) unsigned DEFAULT NULL,
                                              `operation` varchar(255) DEFAULT NULL,
                                              `details` text DEFAULT NULL,
                                              `IP` varchar(15) DEFAULT NULL,
                                              `page` varchar(255) DEFAULT NULL,
                                              `referer` varchar(255) DEFAULT NULL,
                                              PRIMARY KEY (`ID`),
                                              KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_search_logs
CREATE TABLE IF NOT EXISTS `fabcms_search_logs` (
                                                    `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                                    `user_ID` mediumint(8) unsigned NOT NULL,
                                                    `date` datetime NOT NULL,
                                                    `phrase` varchar(255) CHARACTER SET utf16 NOT NULL,
                                                    `var_char` int(255) NOT NULL,
                                                    `method` enum('get','post','api') CHARACTER SET utf16 NOT NULL,
                                                    `IP` varchar(45) CHARACTER SET utf16 NOT NULL,
                                                    `from_page` varchar(255) CHARACTER SET utf16 NOT NULL,
                                                    `results` mediumint(8) unsigned NOT NULL,
                                                    `interface` varchar(255) NOT NULL,
                                                    PRIMARY KEY (`ID`),
                                                    KEY `ID` (`ID`),
                                                    KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_sense_banner
CREATE TABLE IF NOT EXISTS `fabcms_sense_banner` (
                                                     `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                     `hook_ID` mediumint(8) unsigned DEFAULT NULL,
                                                     `code` text DEFAULT NULL,
                                                     `probability` tinyint(3) unsigned DEFAULT NULL,
                                                     `probability_progression_start` smallint(5) unsigned DEFAULT NULL,
                                                     `probability_progression_end` smallint(5) unsigned DEFAULT NULL,
                                                     `hits` mediumint(8) unsigned DEFAULT NULL,
                                                     PRIMARY KEY (`ID`),
                                                     KEY `hook_ID` (`hook_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_sense_hooks
CREATE TABLE IF NOT EXISTS `fabcms_sense_hooks` (
                                                    `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                                    `lang` varchar(2) DEFAULT NULL,
                                                    `hook` varchar(50) DEFAULT NULL,
                                                    `enabled` tinyint(3) unsigned DEFAULT NULL,
                                                    PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_sessions
CREATE TABLE IF NOT EXISTS `fabcms_sessions` (
                                                 `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                                 `user_ID` mediumint(9) unsigned NOT NULL,
                                                 `start` date NOT NULL,
                                                 `end` date NOT NULL,
                                                 `hash` varchar(255) NOT NULL,
                                                 PRIMARY KEY (`ID`),
                                                 KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_articles_group_articles
CREATE TABLE IF NOT EXISTS `fabcms_shop_articles_group_articles` (
                                                                     `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                                     `article_ID` mediumint(9) unsigned NOT NULL DEFAULT 0,
                                                                     PRIMARY KEY (`ID`),
                                                                     KEY `article_ID` (`article_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_articles_group_master
CREATE TABLE IF NOT EXISTS `fabcms_shop_articles_group_master` (
                                                                   `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                                   `code` varchar(50) NOT NULL DEFAULT '0',
                                                                   PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_articles_group_translation
CREATE TABLE IF NOT EXISTS `fabcms_shop_articles_group_translation` (
                                                                        `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                                        `lang` varchar(2) NOT NULL,
                                                                        `group_name` varchar(255) NOT NULL,
                                                                        `description` text NOT NULL,
                                                                        PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_carts
CREATE TABLE IF NOT EXISTS `fabcms_shop_carts` (
                                                   `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                   `user_ID` mediumint(8) unsigned DEFAULT 0,
                                                   `payment_ID` smallint(5) unsigned DEFAULT NULL,
                                                   `anonymous_hash` varchar(32) DEFAULT NULL COMMENT '// MS5 salted hash',
                                                   `start_date` date DEFAULT NULL,
                                                   `status` tinyint(4) unsigned DEFAULT 0,
                                                   `latest_update` datetime DEFAULT current_timestamp(),
                                                   `has_global_discount` tinyint(3) unsigned DEFAULT 0,
                                                   PRIMARY KEY (`ID`),
                                                   KEY `payment_ID` (`payment_ID`),
                                                   KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_cart_items
CREATE TABLE IF NOT EXISTS `fabcms_shop_cart_items` (
                                                        `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                        `cart_ID` mediumint(8) unsigned DEFAULT NULL,
                                                        `item_ID` mediumint(8) unsigned DEFAULT NULL,
                                                        `item_qty` mediumint(8) unsigned DEFAULT NULL,
                                                        `public_price` decimal(8,2) unsigned DEFAULT NULL,
                                                        `discount_1` decimal(5,2) unsigned DEFAULT NULL,
                                                        `discount_2` decimal(5,2) unsigned DEFAULT NULL,
                                                        `discount_3` decimal(5,2) unsigned DEFAULT NULL,
                                                        `final_price` decimal(10,2) unsigned DEFAULT NULL,
                                                        `net_price` decimal(10,2) unsigned DEFAULT NULL,
                                                        `cost_price` decimal(10,2) unsigned DEFAULT NULL,
                                                        `global_discount` decimal(5,2) unsigned DEFAULT NULL,
                                                        `vat_ID` mediumint(8) unsigned DEFAULT NULL,
                                                        `vat_amount` decimal(10,2) unsigned DEFAULT NULL,
                                                        `add_date` date DEFAULT NULL,
                                                        `is_promo` tinyint(3) unsigned DEFAULT NULL,
                                                        PRIMARY KEY (`ID`),
                                                        KEY `cart_ID` (`cart_ID`),
                                                        KEY `item_ID` (`item_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_cart_items_additional_info
CREATE TABLE IF NOT EXISTS `fabcms_shop_cart_items_additional_info` (
                                                                        `ID` mediumint(8) unsigned NOT NULL,
                                                                        `cart_item_ID` mediumint(8) unsigned DEFAULT NULL,
                                                                        `item_additional_ID` mediumint(8) unsigned DEFAULT NULL,
                                                                        `value` text DEFAULT NULL,
                                                                        KEY `ID` (`ID`),
                                                                        KEY `cart_item_ID` (`cart_item_ID`),
                                                                        KEY `item_additional_ID` (`item_additional_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_categories
CREATE TABLE IF NOT EXISTS `fabcms_shop_categories` (
                                                        `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                        `category_master_ID` mediumint(8) unsigned DEFAULT NULL,
                                                        `lang` varchar(2) DEFAULT NULL,
                                                        `trackback` varchar(255) DEFAULT NULL,
                                                        `title` varchar(255) DEFAULT NULL,
                                                        `description` text DEFAULT NULL,
                                                        `thumb` varchar(255) DEFAULT NULL,
                                                        `enabled` tinyint(1) unsigned DEFAULT NULL,
                                                        PRIMARY KEY (`ID`),
                                                        KEY `category_master_ID` (`category_master_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_categories_master
CREATE TABLE IF NOT EXISTS `fabcms_shop_categories_master` (
                                                               `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                               PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_config
CREATE TABLE IF NOT EXISTS `fabcms_shop_config` (
                                                    `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                    `lang` varchar(2) DEFAULT NULL,
                                                    `param` varchar(255) DEFAULT NULL,
                                                    `value` varchar(255) DEFAULT NULL,
                                                    PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_coupons
CREATE TABLE IF NOT EXISTS `fabcms_shop_coupons` (
                                                     `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                     `user_ID` mediumint(9) unsigned NOT NULL DEFAULT 0,
                                                     `appliable_to_group_ID` mediumint(9) NOT NULL DEFAULT 0,
                                                     `excluded_to_group_ID` mediumint(9) NOT NULL DEFAULT 0,
                                                     `code` varchar(50) NOT NULL DEFAULT '0',
                                                     `type` smallint(6) NOT NULL DEFAULT 0 COMMENT '1 = percentual, 2 net, 3 = free  shipping, 4 = 100% discount',
                                                     `amount` decimal(5,3) NOT NULL DEFAULT 0.000,
                                                     PRIMARY KEY (`ID`),
                                                     KEY `appliable_to_group_ID` (`appliable_to_group_ID`),
                                                     KEY `excluded_to_group_ID` (`excluded_to_group_ID`),
                                                     KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_couriers
CREATE TABLE IF NOT EXISTS `fabcms_shop_couriers` (
                                                      `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                                      `name` varchar(255) DEFAULT NULL,
                                                      `tracking_URI` varchar(1024) DEFAULT NULL,
                                                      `method` tinyint(3) unsigned DEFAULT 0 COMMENT '0 = POST; 1 = GET',
                                                      `codify` tinyint(3) unsigned DEFAULT 0 COMMENT '0 = plain; 1 = XML; 2 = JSON',
                                                      PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1 COMMENT='This tables stores informations about couriers.';

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_downloads
CREATE TABLE IF NOT EXISTS `fabcms_shop_downloads` (
                                                       `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                       `user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                       `cart_item_ID` smallint(5) unsigned DEFAULT NULL,
                                                       `file` varchar(255) DEFAULT NULL,
                                                       `ip` varchar(15) DEFAULT NULL,
                                                       `download_date` datetime DEFAULT NULL,
                                                       PRIMARY KEY (`ID`),
                                                       KEY `user_ID` (`user_ID`),
                                                       KEY `cart_item_ID` (`cart_item_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_items
CREATE TABLE IF NOT EXISTS `fabcms_shop_items` (
                                                   `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                   `master_ID` mediumint(9) unsigned DEFAULT NULL,
                                                   `category_ID` tinyint(4) unsigned DEFAULT NULL,
                                                   `vat_ID` mediumint(8) unsigned DEFAULT NULL,
                                                   `lang` varchar(2) NOT NULL DEFAULT '0',
                                                   `cod_art` varchar(255) DEFAULT NULL,
                                                   `title` varchar(255) DEFAULT NULL,
                                                   `tags` varchar(255) DEFAULT NULL,
                                                   `trackback` varchar(255) DEFAULT NULL,
                                                   `public_price` decimal(6,2) DEFAULT NULL,
                                                   `discount_1` tinyint(3) DEFAULT NULL,
                                                   `discount_2` tinyint(3) DEFAULT NULL,
                                                   `discount_3` tinyint(3) DEFAULT NULL,
                                                   `final_price` decimal(6,2) DEFAULT NULL,
                                                   `net_price` decimal(6,2) NOT NULL,
                                                   `vat_amount` decimal(6,2) DEFAULT NULL,
                                                   `cost_price` decimal(6,2) DEFAULT NULL,
                                                   `short_description` varchar(255) DEFAULT NULL,
                                                   `description` text DEFAULT NULL,
                                                   `product_image` varchar(255) DEFAULT NULL,
                                                   `dismissed` tinyint(1) unsigned DEFAULT NULL,
                                                   `is_promo` tinyint(4) unsigned DEFAULT NULL,
                                                   `is_variant_master` tinyint(4) DEFAULT NULL,
                                                   `is_stackable` tinyint(4) unsigned DEFAULT 1 COMMENT '0 = no; 1 = yes',
                                                   `enabled` tinyint(4) unsigned DEFAULT NULL,
                                                   `allow_note` tinyint(4) unsigned DEFAULT NULL COMMENT 'Can user post its own notes?',
                                                   `mandatory_note` tinyint(4) unsigned DEFAULT NULL COMMENT 'User must fill own notes?',
                                                   PRIMARY KEY (`ID`),
                                                   KEY `category_ID` (`category_ID`),
                                                   KEY `master_ID` (`master_ID`),
                                                   KEY `vat_ID` (`vat_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_items_customizations
CREATE TABLE IF NOT EXISTS `fabcms_shop_items_customizations` (
                                                                  `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                                  `master_ID` mediumint(9) unsigned DEFAULT NULL,
                                                                  `item_ID` mediumint(9) unsigned DEFAULT NULL,
                                                                  `lang` varchar(2) DEFAULT NULL,
                                                                  `name` varchar(255) DEFAULT NULL,
                                                                  `type` tinyint(3) unsigned DEFAULT 1 COMMENT '1 = custom text; 2 = yes or no; 3 = single select: 4 = multiple select',
                                                                  `hint` varchar(255) DEFAULT '1',
                                                                  `help` text DEFAULT NULL,
                                                                  `is_mandatory` tinyint(3) unsigned DEFAULT 1,
                                                                  `order` tinyint(4) DEFAULT NULL,
                                                                  PRIMARY KEY (`ID`),
                                                                  KEY `item_ID` (`item_ID`),
                                                                  KEY `master_ID` (`master_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_items_customizations_link
CREATE TABLE IF NOT EXISTS `fabcms_shop_items_customizations_link` (
                                                                       `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                                       `item_ID` mediumint(8) unsigned NOT NULL,
                                                                       `customization_ID` mediumint(8) unsigned NOT NULL,
                                                                       `order` tinyint(3) unsigned NOT NULL,
                                                                       PRIMARY KEY (`ID`),
                                                                       KEY `customization_ID` (`customization_ID`),
                                                                       KEY `item_ID` (`item_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf16 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_items_customizations_master
CREATE TABLE IF NOT EXISTS `fabcms_shop_items_customizations_master` (
                                                                         `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                                         PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf16 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_items_customizations_options
CREATE TABLE IF NOT EXISTS `fabcms_shop_items_customizations_options` (
                                                                          `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                                          `customization_ID` mediumint(8) unsigned NOT NULL,
                                                                          `value` varchar(255) NOT NULL,
                                                                          PRIMARY KEY (`ID`),
                                                                          KEY `ID` (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf16 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_item_additional_info
CREATE TABLE IF NOT EXISTS `fabcms_shop_item_additional_info` (
                                                                  `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                                  `master_ID` mediumint(8) unsigned DEFAULT NULL,
                                                                  `type` tinyint(3) unsigned DEFAULT NULL COMMENT '1 = boolean; 2 = float; 3 = select;',
                                                                  `lang` varchar(2) DEFAULT NULL,
                                                                  `name` varchar(2) DEFAULT NULL,
                                                                  `avaliable_options` text DEFAULT NULL,
                                                                  PRIMARY KEY (`ID`),
                                                                  KEY `master_ID` (`master_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_item_additional_info_master
CREATE TABLE IF NOT EXISTS `fabcms_shop_item_additional_info_master` (
                                                                         `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                                         PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_item_files
CREATE TABLE IF NOT EXISTS `fabcms_shop_item_files` (
                                                        `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                        `item_ID` mediumint(9) unsigned DEFAULT NULL,
                                                        `file` varchar(255) DEFAULT NULL,
                                                        `name` varchar(225) DEFAULT NULL,
                                                        `type` tinyint(4) unsigned DEFAULT NULL COMMENT '0 = public, 1 = after_purchase',
                                                        `short_description` varchar(255) DEFAULT NULL,
                                                        `ordering` tinyint(3) unsigned NOT NULL,
                                                        `enabled` tinyint(4) unsigned DEFAULT NULL COMMENT '0 = disabled; 1 = enabled',
                                                        PRIMARY KEY (`ID`),
                                                        KEY `item_ID` (`item_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_item_masters
CREATE TABLE IF NOT EXISTS `fabcms_shop_item_masters` (
                                                          `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                          PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_triggers
CREATE TABLE IF NOT EXISTS `fabcms_shop_triggers` (
                                                      `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                      `item_ID` mediumint(8) unsigned NOT NULL DEFAULT 0,
                                                      `name` varchar(255) NOT NULL DEFAULT '0',
                                                      `description` text NOT NULL,
                                                      `order` tinyint(3) unsigned NOT NULL DEFAULT 0,
                                                      `trigger_data` varchar(1024) DEFAULT NULL,
                                                      `enabled` tinyint(3) unsigned NOT NULL DEFAULT 0,
                                                      PRIMARY KEY (`ID`),
                                                      KEY `item_ID` (`item_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1 ROW_FORMAT=PAGE;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_users_customizations
CREATE TABLE IF NOT EXISTS `fabcms_shop_users_customizations` (
                                                                  `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                                  `user_ID` mediumint(8) unsigned DEFAULT NULL,
                                                                  `global_discount` decimal(5,2) unsigned DEFAULT NULL,
                                                                  `custom_header` text DEFAULT NULL,
                                                                  `custom_footer` text DEFAULT NULL,
                                                                  `instant_checkout` tinyint(1) unsigned DEFAULT 0,
                                                                  PRIMARY KEY (`ID`),
                                                                  KEY `user_ID` (`user_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_user_address
CREATE TABLE IF NOT EXISTS `fabcms_shop_user_address` (
                                                          `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                          `user_ID` mediumint(8) unsigned NOT NULL,
                                                          `street` varchar(255) NOT NULL,
                                                          `zip` varchar(255) NOT NULL,
                                                          `city` varchar(255) NOT NULL,
                                                          `state` varchar(255) NOT NULL,
                                                          `nation` varchar(255) NOT NULL,
                                                          `internal_note` text NOT NULL,
                                                          `is_default` tinyint(4) unsigned NOT NULL,
                                                          PRIMARY KEY (`ID`),
                                                          KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_user_discounts
CREATE TABLE IF NOT EXISTS `fabcms_shop_user_discounts` (
                                                            `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                            `user_ID` mediumint(8) unsigned DEFAULT NULL,
                                                            `discount` decimal(4,2) unsigned DEFAULT NULL,
                                                            PRIMARY KEY (`ID`),
                                                            KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_variants
CREATE TABLE IF NOT EXISTS `fabcms_shop_variants` (
                                                      `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                      `lang` varchar(2) NOT NULL,
                                                      `variant_description` varchar(255) NOT NULL,
                                                      `varinat_note` text NOT NULL,
                                                      `variant_name` varchar(255) NOT NULL,
                                                      `variant_enabled` tinyint(4) unsigned NOT NULL,
                                                      PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_variants_master
CREATE TABLE IF NOT EXISTS `fabcms_shop_variants_master` (
                                                             `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                             PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_shop_vat
CREATE TABLE IF NOT EXISTS `fabcms_shop_vat` (
                                                 `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                 `code` varchar(10) NOT NULL DEFAULT '0',
                                                 `value` tinyint(4) unsigned NOT NULL DEFAULT 0,
                                                 `description` varchar(255) NOT NULL DEFAULT '0',
                                                 PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_stats
CREATE TABLE IF NOT EXISTS `fabcms_stats` (
                                              `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                              `user_ID` mediumint(8) unsigned DEFAULT NULL,
                                              `IDX` mediumint(8) unsigned NOT NULL,
                                              `date` datetime NOT NULL,
                                              `module` varchar(50) NOT NULL,
                                              `submodule` varchar(255) NOT NULL,
                                              `IP` varchar(15) NOT NULL,
                                              `uri` varchar(255) DEFAULT NULL,
                                              `agent` varchar(255) DEFAULT NULL,
                                              `refer` varchar(255) NOT NULL,
                                              `is_bot` tinyint(3) unsigned DEFAULT NULL,
                                              `bot` varchar(128) DEFAULT NULL,
                                              `is_mobile` tinyint(3) unsigned DEFAULT NULL,
                                              PRIMARY KEY (`ID`),
                                              KEY `IDX` (`IDX`),
                                              KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 AVG_ROW_LENGTH=8192 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_stats_daily
CREATE TABLE IF NOT EXISTS `fabcms_stats_daily` (
                                                    `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                                    `IDX` mediumint(8) unsigned NOT NULL,
                                                    `date` date NOT NULL,
                                                    `module` varchar(255) NOT NULL,
                                                    `submodule` varchar(50) DEFAULT NULL,
                                                    `is_bot` tinyint(4) NOT NULL DEFAULT 0,
                                                    `hits` mediumint(8) unsigned NOT NULL,
                                                    PRIMARY KEY (`ID`),
                                                    KEY `IDX` (`IDX`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_stats_groups
CREATE TABLE IF NOT EXISTS `fabcms_stats_groups` (
                                                     `ID` mediumint(9) NOT NULL AUTO_INCREMENT,
                                                     `group_name` varchar(255) DEFAULT NULL,
                                                     `module` varchar(255) DEFAULT NULL,
                                                     PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_stats_group_items
CREATE TABLE IF NOT EXISTS `fabcms_stats_group_items` (
                                                          `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                          `group_ID` mediumint(8) unsigned DEFAULT NULL,
                                                          `IDX` int(11) unsigned DEFAULT NULL,
                                                          `module` varchar(255) DEFAULT NULL,
                                                          `submodule` varchar(255) DEFAULT NULL,
                                                          PRIMARY KEY (`ID`),
                                                          KEY `group_ID` (`group_ID`),
                                                          KEY `IDX` (`IDX`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_users
CREATE TABLE IF NOT EXISTS `fabcms_users` (
                                              `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                              `group_ID` mediumint(8) unsigned NOT NULL,
                                              `admin` tinyint(4) unsigned DEFAULT 0,
                                              `username` varchar(255) NOT NULL,
                                              `name` varchar(255) DEFAULT NULL,
                                              `article_signature` varchar(32) DEFAULT NULL,
                                              `surname` varchar(255) DEFAULT NULL,
                                              `birthdate` date DEFAULT NULL,
                                              `short_biography` text DEFAULT NULL,
                                              `biography` text DEFAULT NULL,
                                              `reset_email_hash` varchar(32) DEFAULT NULL,
                                              `privacy_profile_level` varchar(32) DEFAULT NULL,
                                              `password` varchar(255) NOT NULL,
                                              `email` varchar(255) NOT NULL,
                                              `email_pec` varchar(255) DEFAULT NULL,
                                              `tax_id` varchar(32) DEFAULT NULL,
                                              `fiscal_code` varchar(32) DEFAULT NULL,
                                              `electronic_invoice_code` varchar(7) DEFAULT NULL,
                                              `registration_IP` varchar(32) DEFAULT NULL,
                                              `optin_IP_confirm` varchar(32) DEFAULT NULL,
                                              `newsletter` tinyint(1) unsigned DEFAULT NULL,
                                              `registration_date` date DEFAULT NULL,
                                              `optin_hash` varchar(32) DEFAULT NULL,
                                              `address` varchar(255) DEFAULT NULL,
                                              `address_2` varchar(255) DEFAULT NULL,
                                              `city` varchar(255) DEFAULT NULL,
                                              `state` varchar(255) DEFAULT NULL,
                                              `google_plus` varchar(255) DEFAULT NULL,
                                              `latest_optin` date DEFAULT NULL,
                                              `zip` varchar(32) DEFAULT NULL,
                                              `country` varchar(64) DEFAULT NULL,
                                              `enabled` tinyint(4) unsigned DEFAULT NULL,
                                              PRIMARY KEY (`ID`),
                                              KEY `group_ID` (`group_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_users_groups
CREATE TABLE IF NOT EXISTS `fabcms_users_groups` (
                                                     `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                     `group_name` varchar(255) DEFAULT NULL,
                                                     `group_type` tinyint(4) unsigned DEFAULT NULL COMMENT '1 = admin, 2 = registered, 3 = guest',
                                                     `restrictions` tinyint(4) unsigned DEFAULT NULL COMMENT '0 = no restriction; 1 = banned',
                                                     `group_order` tinyint(4) unsigned DEFAULT NULL,
                                                     PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_users_tags
CREATE TABLE IF NOT EXISTS `fabcms_users_tags` (
                                                   `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                   `user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                   `tag` varchar(255) DEFAULT NULL,
                                                   PRIMARY KEY (`ID`),
                                                   KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_wiki_categories_details
CREATE TABLE IF NOT EXISTS `fabcms_wiki_categories_details` (
                                                                `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                                `master_ID` mediumint(8) unsigned DEFAULT NULL,
                                                                `lang` varchar(2) NOT NULL DEFAULT '0',
                                                                `name` varchar(255) DEFAULT NULL,
                                                                `description` text DEFAULT NULL,
                                                                PRIMARY KEY (`ID`),
                                                                KEY `master_ID` (`master_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_wiki_categories_masters
CREATE TABLE IF NOT EXISTS `fabcms_wiki_categories_masters` (
                                                                `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                                `type` tinyint(4) unsigned DEFAULT NULL,
                                                                PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_wiki_comments
CREATE TABLE IF NOT EXISTS `fabcms_wiki_comments` (
                                                      `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                      `page_ID` mediumint(8) unsigned DEFAULT NULL,
                                                      `author_ID` mediumint(8) unsigned DEFAULT NULL,
                                                      `author` varchar(255) DEFAULT NULL,
                                                      `comment` text DEFAULT NULL,
                                                      `date` datetime DEFAULT NULL,
                                                      `IP` varchar(15) DEFAULT NULL,
                                                      `visible` tinyint(1) unsigned DEFAULT NULL,
                                                      PRIMARY KEY (`ID`),
                                                      KEY `author_ID` (`author_ID`),
                                                      KEY `page_ID` (`page_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_wiki_config
CREATE TABLE IF NOT EXISTS `fabcms_wiki_config` (
                                                    `param` varchar(255) DEFAULT NULL,
                                                    `lang` varchar(2) DEFAULT NULL,
                                                    `value` text DEFAULT NULL
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_wiki_custom_title_rules
CREATE TABLE IF NOT EXISTS `fabcms_wiki_custom_title_rules` (
                                                                `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                                `type` tinyint(4) unsigned DEFAULT NULL,
                                                                `first_tag` varchar(50) DEFAULT NULL,
                                                                `rule` varchar(255) DEFAULT NULL,
                                                                PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_wiki_masters
CREATE TABLE IF NOT EXISTS `fabcms_wiki_masters` (
                                                     `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                     `creation_date` date DEFAULT NULL,
                                                     PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_wiki_outbound_trackback
CREATE TABLE IF NOT EXISTS `fabcms_wiki_outbound_trackback` (
                                                                `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                                `master_ID` mediumint(8) unsigned NOT NULL,
                                                                `page_ID` mediumint(8) unsigned NOT NULL,
                                                                `trackback_page_ID` varchar(256) NOT NULL,
                                                                `link_name` varchar(255) DEFAULT NULL,
                                                                PRIMARY KEY (`ID`),
                                                                KEY `master_ID` (`master_ID`),
                                                                KEY `page_ID` (`page_ID`),
                                                                KEY `trackback_page_ID` (`trackback_page_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_wiki_pages
CREATE TABLE IF NOT EXISTS `fabcms_wiki_pages` (
                                                   `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                   `master_ID` mediumint(8) unsigned DEFAULT NULL,
                                                   `type_ID` tinyint(3) unsigned DEFAULT NULL COMMENT '1 = Article, 2 = Blog post',
                                                   `category_ID` tinyint(3) unsigned DEFAULT NULL,
                                                   `status_ID` smallint(5) unsigned DEFAULT 0,
                                                   `license_ID` mediumint(9) unsigned DEFAULT NULL,
                                                   `creation_user_ID` smallint(5) unsigned DEFAULT NULL,
                                                   `latest_update_user_ID` smallint(5) unsigned DEFAULT NULL,
                                                   `image_ID` mediumint(8) unsigned DEFAULT NULL,
                                                   `first_tag_ID` mediumint(8) unsigned DEFAULT NULL,
                                                   `first_internal_tag_ID` mediumint(8) unsigned DEFAULT NULL,
                                                   `featured_video_ID` mediumint(8) unsigned DEFAULT NULL,
                                                   `language` varchar(2) DEFAULT NULL,
                                                   `title` varchar(255) DEFAULT NULL,
                                                   `title_alternative` varchar(255) DEFAULT NULL,
                                                   `trackback` varchar(255) DEFAULT NULL,
                                                   `short_description` text DEFAULT NULL,
                                                   `metadata_description` varchar(512) DEFAULT NULL,
                                                   `creation_date` date DEFAULT NULL,
                                                   `last_update` datetime DEFAULT NULL,
                                                   `visible_from_date` datetime DEFAULT NULL,
                                                   `visible_to_date` date DEFAULT NULL,
                                                   `content` mediumtext DEFAULT NULL,
                                                   `keywords` varchar(512) DEFAULT NULL,
                                                   `tags` tinytext DEFAULT NULL,
                                                   `tags_trackback` tinytext DEFAULT NULL,
                                                   `additional_data` text DEFAULT NULL,
                                                   `internal_redirect` varchar(255) DEFAULT NULL,
                                                   `service_page` tinyint(3) unsigned DEFAULT NULL,
                                                   `no_index` tinyint(4) unsigned DEFAULT NULL,
                                                   `no_linking_pages` tinyint(4) unsigned DEFAULT NULL,
                                                   `no_info` tinyint(4) unsigned DEFAULT NULL,
                                                   `no_search` tinyint(4) unsigned DEFAULT NULL,
                                                   `no_editor` tinyint(3) unsigned DEFAULT NULL,
                                                   `full_page` tinyint(4) unsigned DEFAULT NULL,
                                                   `no_banner` tinyint(4) unsigned DEFAULT NULL,
                                                   `no_similar_pages` tinyint(4) unsigned DEFAULT NULL,
                                                   `no_title` tinyint(4) unsigned DEFAULT NULL,
                                                   `no_comment` tinyint(3) unsigned DEFAULT NULL,
                                                   `no_toc` tinyint(3) unsigned DEFAULT NULL,
                                                   `image` varchar(255) DEFAULT NULL,
                                                   `use_file` varchar(255) DEFAULT NULL,
                                                   `hits` mediumint(8) unsigned DEFAULT NULL,
                                                   `revision` smallint(5) unsigned NOT NULL DEFAULT 1,
                                                   `notes` text DEFAULT NULL,
                                                   `stats_last_refresh` date DEFAULT NULL,
                                                   `template` varchar(255) DEFAULT NULL,
                                                   `template_variant` varchar(255) DEFAULT NULL,
                                                   `parser` varchar(255) DEFAULT NULL,
                                                   `seo_score` tinyint(10) unsigned DEFAULT NULL,
                                                   `cached` text DEFAULT NULL,
                                                   `cache_expiration` datetime DEFAULT NULL,
                                                   `visible` tinyint(4) unsigned DEFAULT NULL,
                                                   PRIMARY KEY (`ID`),
                                                   KEY `ID` (`ID`),
                                                   KEY `category_ID` (`category_ID`),
                                                   KEY `creation_user_ID` (`creation_user_ID`),
                                                   KEY `image_ID` (`image_ID`),
                                                   KEY `latest_update_user_ID` (`latest_update_user_ID`),
                                                   KEY `license_ID` (`license_ID`),
                                                   KEY `master_ID` (`master_ID`),
                                                   KEY `status_ID` (`status_ID`),
                                                   KEY `type_ID` (`type_ID`),
                                                   KEY `first_tag_ID` (`first_tag_ID`),
                                                   KEY `first_internal_tag_ID` (`first_internal_tag_ID`),
                                                   KEY `featured_video_ID` (`featured_video_ID`),
                                                   FULLTEXT KEY `content` (`content`),
                                                   FULLTEXT KEY `title` (`title`),
                                                   FULLTEXT KEY `title_alternative` (`title_alternative`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_wiki_pages_files
CREATE TABLE IF NOT EXISTS `fabcms_wiki_pages_files` (
                                                         `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                         `page_ID` mediumint(8) unsigned NOT NULL,
                                                         `fabmedia_ID` mediumint(8) unsigned NOT NULL,
                                                         `type` varchar(255) DEFAULT NULL,
                                                         `subtype` varchar(255) DEFAULT NULL,
                                                         `filename` varchar(255) DEFAULT NULL,
                                                         `title` varchar(255) DEFAULT NULL,
                                                         `description` text DEFAULT NULL,
                                                         PRIMARY KEY (`ID`),
                                                         KEY `fabmedia_ID` (`fabmedia_ID`),
                                                         KEY `page_ID` (`page_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_wiki_pages_groups
CREATE TABLE IF NOT EXISTS `fabcms_wiki_pages_groups` (
                                                          `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                                          `page_ID` mediumint(9) unsigned DEFAULT NULL,
                                                          `group_ID` mediumint(9) unsigned DEFAULT NULL,
                                                          `visibile` tinyint(4) unsigned DEFAULT 1 COMMENT '0 = hidden; 1 = visible',
                                                          PRIMARY KEY (`ID`),
                                                          KEY `group_ID` (`group_ID`),
                                                          KEY `page_ID` (`page_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_wiki_pages_internal_tags
CREATE TABLE IF NOT EXISTS `fabcms_wiki_pages_internal_tags` (
                                                                 `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                                 `page_ID` mediumint(9) unsigned DEFAULT NULL,
                                                                 `tag` varchar(255) DEFAULT NULL,
                                                                 PRIMARY KEY (`ID`),
                                                                 KEY `page_ID` (`page_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_wiki_pages_keywords
CREATE TABLE IF NOT EXISTS `fabcms_wiki_pages_keywords` (
                                                            `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                            `page_ID` mediumint(8) unsigned DEFAULT NULL,
                                                            `keyword` varchar(50) DEFAULT NULL,
                                                            PRIMARY KEY (`ID`),
                                                            KEY `page_ID` (`page_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_wiki_pages_seo
CREATE TABLE IF NOT EXISTS `fabcms_wiki_pages_seo` (
                                                       `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                       `page_ID` mediumint(8) unsigned NOT NULL,
                                                       `keyword` varchar(255) DEFAULT NULL,
                                                       `results` text DEFAULT NULL,
                                                       `score` tinyint(3) unsigned DEFAULT NULL,
                                                       `order` tinyint(3) unsigned DEFAULT NULL,
                                                       PRIMARY KEY (`ID`),
                                                       KEY `page_ID` (`page_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_wiki_pages_statistics
CREATE TABLE IF NOT EXISTS `fabcms_wiki_pages_statistics` (
                                                              `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                              `page_ID` mediumint(8) unsigned DEFAULT NULL,
                                                              `characters` mediumint(8) unsigned DEFAULT NULL,
                                                              `words` mediumint(8) unsigned DEFAULT NULL,
                                                              `tables` mediumint(8) unsigned DEFAULT NULL,
                                                              `images` mediumint(8) unsigned DEFAULT NULL,
                                                              `links` mediumint(8) unsigned DEFAULT NULL,
                                                              `headings` smallint(3) unsigned DEFAULT NULL,
                                                              PRIMARY KEY (`ID`),
                                                              KEY `page_ID` (`page_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_wiki_pages_status
CREATE TABLE IF NOT EXISTS `fabcms_wiki_pages_status` (
                                                          `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                          `status` varchar(50) DEFAULT NULL,
                                                          `description` text DEFAULT NULL,
                                                          PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_wiki_pages_tags
CREATE TABLE IF NOT EXISTS `fabcms_wiki_pages_tags` (
                                                        `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                        `page_ID` mediumint(9) unsigned DEFAULT NULL,
                                                        `tag` varchar(255) DEFAULT NULL,
                                                        `tag_trackback` varchar(255) DEFAULT NULL,
                                                        PRIMARY KEY (`ID`),
                                                        KEY `page_ID` (`page_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_wiki_parsers
CREATE TABLE IF NOT EXISTS `fabcms_wiki_parsers` (
                                                     `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                     `parser` varchar(255) NOT NULL,
                                                     `order` smallint(5) unsigned NOT NULL,
                                                     `enabled` tinyint(2) unsigned NOT NULL,
                                                     PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_wiki_planner
CREATE TABLE IF NOT EXISTS `fabcms_wiki_planner` (
                                                     `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                     `title` varchar(255) CHARACTER SET utf8mb3 NOT NULL DEFAULT '0',
                                                     `description` text CHARACTER SET utf8mb3 NOT NULL DEFAULT '0',
                                                     `notes` text CHARACTER SET utf8mb3 NOT NULL DEFAULT '0',
                                                     `sql` text CHARACTER SET utf8mb3 NOT NULL DEFAULT '0',
                                                     `enabled` tinyint(3) unsigned NOT NULL DEFAULT 1,
                                                     PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb4 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_wiki_revisions
CREATE TABLE IF NOT EXISTS `fabcms_wiki_revisions` (
                                                       `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                       `page_ID` mediumint(8) unsigned DEFAULT NULL,
                                                       `user_ID` mediumint(8) unsigned DEFAULT NULL,
                                                       `revision` smallint(6) unsigned DEFAULT NULL,
                                                       `content` text DEFAULT NULL,
                                                       `update_date` datetime DEFAULT NULL,
                                                       PRIMARY KEY (`ID`),
                                                       KEY `page_ID` (`page_ID`),
                                                       KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 AVG_ROW_LENGTH=2048 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_wiki_tags_menu
CREATE TABLE IF NOT EXISTS `fabcms_wiki_tags_menu` (
                                                       `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                       `language` varchar(2) NOT NULL,
                                                       `depth` tinyint(4) unsigned NOT NULL,
                                                       `tag` varchar(255) NOT NULL,
                                                       `URI` tinytext NOT NULL,
                                                       `name` varchar(255) DEFAULT NULL,
                                                       PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabcms_wiki_triggers
CREATE TABLE IF NOT EXISTS `fabcms_wiki_triggers` (
                                                      `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                      `parser` varchar(255) DEFAULT NULL,
                                                      `event` varchar(255) DEFAULT NULL,
                                                      `configuration` varchar(2048) DEFAULT NULL,
                                                      `order` tinyint(4) unsigned DEFAULT NULL,
                                                      `enabled` tinyint(4) unsigned DEFAULT NULL,
                                                      PRIMARY KEY (`ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabmcs_permission_matrix
CREATE TABLE IF NOT EXISTS `fabmcs_permission_matrix` (
                                                          `ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                                                          `group_ID` mediumint(9) unsigned DEFAULT NULL,
                                                          `user_ID` mediumint(9) unsigned DEFAULT NULL,
                                                          `module` varchar(255) DEFAULT NULL,
                                                          `op` varchar(255) DEFAULT NULL,
                                                          `permission` tinyint(4) unsigned DEFAULT NULL COMMENT '1 = group; 2 = tag; 3 = user',
                                                          `tag` varchar(255) DEFAULT NULL,
                                                          PRIMARY KEY (`ID`),
                                                          KEY `group_ID` (`group_ID`),
                                                          KEY `user_ID` (`user_ID`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabmedia_importer
CREATE TABLE IF NOT EXISTS `fabmedia_importer` (
                                                   `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                   `name` varchar(50) NOT NULL DEFAULT '0',
                                                   `import_method` varchar(255) NOT NULL DEFAULT '0',
                                                   `view_plugin` varchar(255) NOT NULL DEFAULT '0',
                                                   `trackback` varchar(50) NOT NULL DEFAULT 'showitem',
                                                   `enable_view` tinyint(3) unsigned NOT NULL DEFAULT 0,
                                                   `enable_import` tinyint(3) unsigned NOT NULL DEFAULT 0,
                                                   PRIMARY KEY (`ID`) USING BTREE
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella fabcms.fabmedia_importer_extension
CREATE TABLE IF NOT EXISTS `fabmedia_importer_extension` (
                                                             `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                                             `importer_ID` mediumint(8) unsigned DEFAULT NULL,
                                                             `extension` varchar(50) DEFAULT NULL,
                                                             `enabled` tinyint(4) unsigned DEFAULT NULL,
                                                             PRIMARY KEY (`ID`) USING BTREE,
                                                             KEY `importer_ID` (`importer_ID`) USING BTREE
) ENGINE=Aria DEFAULT CHARSET=utf8mb3 PAGE_CHECKSUM=1;

-- L’esportazione dei dati non era selezionata.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
