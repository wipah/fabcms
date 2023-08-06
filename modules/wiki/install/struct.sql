--
-- Create table `fabcms_wiki_tags_menu`
--
CREATE TABLE fabcms_wiki_tags_menu
(
    ID       mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    language varchar(2)            NOT NULL,
    depth    tinyint(4) UNSIGNED   NOT NULL,
    tag      varchar(255)          NOT NULL,
    URI      tinytext              NOT NULL,
    name     varchar(255) DEFAULT NULL,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    AVG_ROW_LENGTH = 8192,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

--
-- Create table `fabcms_wiki_revisions`
--
CREATE TABLE `fabcms_wiki_revisions`
(
    `ID`          MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `page_ID`     MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `user_ID`     MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `revision`    SMALLINT(6) UNSIGNED  NULL DEFAULT NULL,
    `content`     TEXT                  NULL DEFAULT NULL,
    `update_date` DATETIME              NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `page_ID` (`page_ID`),
    INDEX `user_ID` (`user_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 455
;

--
-- Create table `fabcms_wiki_parsers`
--
CREATE TABLE fabcms_wiki_parsers
(
    ID      mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    parser  varchar(255)          NOT NULL,
    `order` smallint(5) UNSIGNED  NOT NULL,
    enabled tinyint(2) UNSIGNED   NOT NULL,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    AVG_ROW_LENGTH = 1820,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

--
-- Create table `fabcms_wiki_pages_tags`
--
CREATE TABLE `fabcms_wiki_pages_tags`
(
    `ID`            MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `page_ID`       MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `tag`           VARCHAR(255)          NULL DEFAULT NULL,
    `tag_trackback` VARCHAR(255)          NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `page_ID` (`page_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 1638
;


--
-- Create table `fabcms_wiki_pages_status`
--
CREATE TABLE fabcms_wiki_pages_status
(
    ID          mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    status      varchar(50) DEFAULT NULL,
    description text        DEFAULT NULL,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    AVG_ROW_LENGTH = 8192,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

--
-- Create table `fabcms_wiki_pages_statistics`
--
CREATE TABLE `fabcms_wiki_pages_statistics` (
                                                `ID` MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
                                                `page_ID` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
                                                `characters` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
                                                `words` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
                                                `tables` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
                                                `images` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
                                                `links` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
                                                `headings` SMALLINT(3) UNSIGNED NULL DEFAULT NULL,
                                                PRIMARY KEY (`ID`) USING BTREE,
                                                INDEX `page_ID` (`page_ID`) USING BTREE
)
    COLLATE='utf8_general_ci'
    ENGINE=Aria
    AVG_ROW_LENGTH=3276
;


--
-- Create table `fabcms_wiki_pages_keywords`
--
CREATE TABLE `fabcms_wiki_pages_keywords`
(
    `ID`      MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `page_ID` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `keyword` VARCHAR(50)           NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `page_ID` (`page_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 1365
;

--
-- Create table `fabcms_wiki_pages_internal_tags`
--
CREATE TABLE `fabcms_wiki_pages_internal_tags`
(
    `ID`      MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `page_ID` MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `tag`     VARCHAR(255)          NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `page_ID` (`page_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;


--
-- Create table `fabcms_wiki_pages_files`
--
CREATE TABLE `fabcms_wiki_pages_files`
(
    `ID`          MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `page_ID`     MEDIUMINT(8) UNSIGNED NOT NULL,
    `fabmedia_ID` MEDIUMINT(8) UNSIGNED NOT NULL,
    `type`        VARCHAR(255)          NOT NULL,
    `subtype`     VARCHAR(255)          NOT NULL,
    `filename`    VARCHAR(255)          NOT NULL,
    `title`       VARCHAR(255)          NOT NULL,
    `description` TEXT                  NOT NULL,
    PRIMARY KEY (`ID`),
    INDEX `page_ID` (`page_ID`),
    INDEX `fabmedia_ID` (`fabmedia_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;


CREATE TABLE `fabcms_wiki_pages` (
                                     `ID` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
                                     `master_ID` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
                                     `type_ID` TINYINT(3) UNSIGNED NULL DEFAULT NULL COMMENT '1 = Article, 2 = Blog post',
                                     `category_ID` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                                     `status_ID` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
                                     `license_ID` MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
                                     `creation_user_ID` SMALLINT(5) UNSIGNED NULL DEFAULT NULL,
                                     `latest_update_user_ID` SMALLINT(5) UNSIGNED NULL DEFAULT NULL,
                                     `image_ID` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
                                     `first_tag_ID` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
                                     `first_internal_tag_ID` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
                                     `language` VARCHAR(2) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
                                     `title` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
                                     `title_alternative` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
                                     `trackback` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
                                     `short_description` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
                                     `metadata_description` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
                                     `creation_date` DATE NULL DEFAULT NULL,
                                     `last_update` DATETIME NULL DEFAULT NULL,
                                     `visible_from_date` DATETIME NULL DEFAULT NULL,
                                     `visible_to_date` DATE NULL DEFAULT NULL,
                                     `content` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
                                     `keywords` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
                                     `tags` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
                                     `tags_trackback` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
                                     `additional_data` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
                                     `internal_redirect` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
                                     `service_page` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                                     `no_index` TINYINT(4) UNSIGNED NULL DEFAULT NULL,
                                     `no_linking_pages` TINYINT(4) UNSIGNED NULL DEFAULT NULL,
                                     `no_info` TINYINT(4) UNSIGNED NULL DEFAULT NULL,
                                     `no_search` TINYINT(4) UNSIGNED NULL DEFAULT NULL,
                                     `no_editor` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                                     `full_page` TINYINT(4) UNSIGNED NULL DEFAULT NULL,
                                     `no_banner` TINYINT(4) UNSIGNED NULL DEFAULT NULL,
                                     `no_similar_pages` TINYINT(4) UNSIGNED NULL DEFAULT NULL,
                                     `no_title` TINYINT(4) UNSIGNED NULL DEFAULT NULL,
                                     `no_comment` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                                     `image` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
                                     `hits` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
                                     `revision` SMALLINT(5) UNSIGNED NULL DEFAULT '1',
                                     `notes` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
                                     `stats_last_refresh` DATE NULL DEFAULT NULL,
                                     `template` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
                                     `template_variant` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
                                     `parser` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
                                     `visible` TINYINT(4) UNSIGNED NULL DEFAULT NULL,
                                     PRIMARY KEY (`ID`) USING BTREE,
                                     INDEX `ID` (`ID`) USING BTREE,
                                     INDEX `master_ID` (`master_ID`) USING BTREE,
                                     INDEX `type_ID` (`type_ID`) USING BTREE,
                                     INDEX `category_ID` (`category_ID`) USING BTREE,
                                     INDEX `status_ID` (`status_ID`) USING BTREE,
                                     INDEX `license_ID` (`license_ID`) USING BTREE,
                                     INDEX `creation_user_ID` (`creation_user_ID`) USING BTREE,
                                     INDEX `image_ID` (`image_ID`) USING BTREE,
                                     INDEX `latest_update_user_ID` (`latest_update_user_ID`) USING BTREE,
                                     INDEX `first_tag_ID` (`first_tag_ID`) USING BTREE,
                                     INDEX `first_internal_tag_ID` (`first_internal_tag_ID`) USING BTREE,
                                     FULLTEXT INDEX `content` (`content`),
                                     FULLTEXT INDEX `title` (`title`),
                                     FULLTEXT INDEX `title_alternative` (`title_alternative`)
)
    COLLATE='utf8_general_ci'
    ENGINE=Aria
    AVG_ROW_LENGTH=4915
;


--
-- Create table `fabcms_wiki_outbound_trackback`
--
CREATE TABLE `fabcms_wiki_outbound_trackback`
(
    `ID`                MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `master_ID`         MEDIUMINT(8) UNSIGNED NOT NULL,
    `page_ID`           MEDIUMINT(8) UNSIGNED NOT NULL,
    `trackback_page_ID` VARCHAR(256)          NOT NULL,
    `link_name`         VARCHAR(255)          NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `master_ID` (`master_ID`),
    INDEX `page_ID` (`page_ID`),
    INDEX `trackback_page_ID` (`trackback_page_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 2730
;

--
-- Create table `fabcms_wiki_masters`
--
CREATE TABLE fabcms_wiki_masters
(
    ID            mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    creation_date date DEFAULT NULL,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    AVG_ROW_LENGTH = 4096,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

--
-- Create table `fabcms_wiki_custom_title_rules`
--
CREATE TABLE fabcms_wiki_custom_title_rules
(
    ID        mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    type      tinyint(4) UNSIGNED DEFAULT NULL,
    first_tag varchar(50)         DEFAULT NULL,
    rule      varchar(255)        DEFAULT NULL,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

--
-- Create table `fabcms_wiki_config`
--
CREATE TABLE fabcms_wiki_config
(
    param varchar(255) DEFAULT NULL,
    lang  varchar(2)   DEFAULT NULL,
    value text         DEFAULT NULL
)
    ENGINE = ARIA,
    AVG_ROW_LENGTH = 390,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

--
-- Create table `fabcms_wiki_comments`
--
CREATE TABLE `fabcms_wiki_comments`
(
    `ID`        MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `page_ID`   MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `author_ID` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `author`    VARCHAR(255)          NULL DEFAULT NULL,
    `comment`   TEXT                  NULL DEFAULT NULL,
    `date`      DATETIME              NULL DEFAULT NULL,
    `IP`        VARCHAR(15)           NULL DEFAULT NULL,
    `visible`   TINYINT(1) UNSIGNED   NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `page_ID` (`page_ID`),
    INDEX `author_ID` (`author_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 862
;

--
-- Create table `fabcms_wiki_categories_masters`
--
CREATE TABLE fabcms_wiki_categories_masters
(
    ID   mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    type tinyint(4) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    AVG_ROW_LENGTH = 16384,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

--
-- Create table `fabcms_wiki_categories_details`
--
CREATE TABLE `fabcms_wiki_categories_details`
(
    `ID`          MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `master_ID`   MEDIUMINT(8) UNSIGNED NULL     DEFAULT NULL,
    `lang`        VARCHAR(2)            NOT NULL DEFAULT '0',
    `name`        VARCHAR(255)          NULL     DEFAULT NULL,
    `description` TEXT                  NULL     DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `master_ID` (`master_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 8192
;

CREATE TABLE `fabcms_wiki_pages_groups`
(
    `ID`       INT(11) UNSIGNED      NOT NULL AUTO_INCREMENT,
    `page_ID`  MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `group_ID` MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `visibile` TINYINT(4) UNSIGNED   NULL DEFAULT 1 COMMENT '0 = hidden; 1 = visible',
    PRIMARY KEY (`ID`),
    INDEX `page_ID` (`page_ID`),
    INDEX `group_ID` (`group_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;

CREATE TABLE fabcms_wiki_triggers
(
    ID            mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    parser        varchar(255)        DEFAULT NULL,
    event         varchar(255)        DEFAULT NULL,
    configuration varchar(2048)       DEFAULT NULL,
    `order`       tinyint(4) UNSIGNED DEFAULT NULL,
    enabled       tinyint(4) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

CREATE TABLE `fabcms_wiki_pages_seo`
(
    `ID`      MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `page_ID` MEDIUMINT(8) UNSIGNED NOT NULL,
    `keyword` VARCHAR(255)          NOT NULL,
    PRIMARY KEY (`ID`),
    INDEX `page_ID` (`page_ID`)
)
    ENGINE = Aria
;

CREATE TABLE `fabcms_wiki_projects`
(
    `ID`          MEDIUMINT UNSIGNED NOT NULL,
    `lang`        VARCHAR(2)         NOT NULL,
    `title`       VARCHAR(2255)      NOT NULL COLLATE 'utf8_general_ci',
    `description` TEXT               NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
    `status`      TINYINT UNSIGNED   NOT NULL COMMENT '0 = open; 1 = closed',
    `start_date`  DATE               NOT NULL,
    `end_date`    DATE               NOT NULL,
    PRIMARY KEY (`ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;

CREATE TABLE `fabcms_wiki_project_pages`
(
    `ID`         MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_ID` MEDIUMINT UNSIGNED NULL DEFAULT NULL,
    `page_ID`    MEDIUMINT UNSIGNED NULL DEFAULT NULL,
    `title`      VARCHAR(255)       NULL DEFAULT NULL COLLATE 'utf8_general_ci',
    `notes`      TEXT               NULL DEFAULT NULL COLLATE 'utf8_general_ci',
    `status`     TINYINT UNSIGNED   NULL DEFAULT NULL COMMENT '0 = not started, 1 = stared, 2 = finished',
    PRIMARY KEY (`ID`),
    INDEX `project_ID` (`project_ID`),
    INDEX `page_ID` (`page_ID`)
)
    COLLATE = 'utf8_general_ci'
;
