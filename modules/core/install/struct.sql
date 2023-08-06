CREATE TABLE `fabmcs_permission_matrix`
(
    `ID`         MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `group_ID`   MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `user_ID`    MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `module`     VARCHAR(255)          NULL DEFAULT NULL,
    `op`         VARCHAR(255)          NULL DEFAULT NULL,
    `permission` TINYINT(4) UNSIGNED   NULL DEFAULT NULL COMMENT '1 = group; 2 = tag; 3 = user',
    `tag`        VARCHAR(255)          NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `group_ID` (`group_ID`),
    INDEX `user_ID` (`user_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;


--
-- Create table `fabcms_stats_group_items`
--
CREATE TABLE `fabcms_stats_group_items`
(
    `ID`        MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `group_ID`  MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `IDX`       INT(11) UNSIGNED      NULL DEFAULT NULL,
    `module`    VARCHAR(255)          NULL DEFAULT NULL,
    `submodule` VARCHAR(255)          NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `group_ID` (`group_ID`),
    INDEX `IDX` (`IDX`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;


--
-- Create table `fabcms_stats_groups`
--
CREATE TABLE fabcms_stats_groups
(
    ID         mediumint(9) NOT NULL AUTO_INCREMENT,
    group_name varchar(255) DEFAULT NULL,
    module     varchar(255) DEFAULT NULL,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    AVG_ROW_LENGTH = 16384,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

--
-- Create table `fabcms_stats_daily`
--
CREATE TABLE `fabcms_stats_daily`
(
    `ID`        INT(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
    `IDX`       MEDIUMINT(8) UNSIGNED NOT NULL,
    `date`      DATE                  NOT NULL,
    `module`    VARCHAR(255)          NOT NULL,
    `submodule` VARCHAR(50)           NULL     DEFAULT NULL,
    `is_bot`    TINYINT(4)            NOT NULL DEFAULT 0,
    `hits`      MEDIUMINT(8) UNSIGNED NOT NULL,
    PRIMARY KEY (`ID`),
    INDEX `IDX` (`IDX`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;

--
-- Create table `fabcms_stats`
--
CREATE TABLE `fabcms_stats`
(
    `ID`        INT(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
    `user_ID`   MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `IDX`       MEDIUMINT(8) UNSIGNED NOT NULL,
    `date`      DATETIME              NOT NULL,
    `module`    VARCHAR(50)           NOT NULL,
    `submodule` VARCHAR(255)          NOT NULL,
    `IP`        VARCHAR(15)           NOT NULL,
    `uri`       VARCHAR(255)          NULL DEFAULT NULL,
    `agent`     VARCHAR(255)          NULL DEFAULT NULL,
    `refer`     VARCHAR(255)          NOT NULL,
    `is_bot`    TINYINT(3) UNSIGNED   NULL DEFAULT NULL,
    `bot`       VARCHAR(128)          NULL DEFAULT NULL,
    `is_mobile` TINYINT(3) UNSIGNED   NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `user_ID` (`user_ID`),
    INDEX `IDX` (`IDX`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 264
;


--
-- Create table `fabcms_sessions`
--
CREATE TABLE `fabcms_sessions`
(
    `ID`      INT(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
    `user_ID` MEDIUMINT(9) UNSIGNED NOT NULL,
    `start`   DATE                  NOT NULL,
    `end`     DATE                  NOT NULL,
    `hash`    VARCHAR(255)          NOT NULL,
    PRIMARY KEY (`ID`),
    INDEX `user_ID` (`user_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 356
;


--
-- Create table `fabcms_sense_hooks`
--
CREATE TABLE `fabcms_hooks`
(
    `ID`         MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `lang`       TINYINT(3) UNSIGNED   NULL DEFAULT NULL,
    `valid_from` DATETIME              NULL DEFAULT NULL,
    `valid_to`   DATETIME              NULL DEFAULT NULL,
    `name`       VARCHAR(255)          NOT NULL COLLATE 'utf8_general_ci',
    `html`       TEXT                  NOT NULL COLLATE 'utf8_general_ci',
    `order`      TINYINT(4)            NULL DEFAULT NULL,
    `enabled`    TINYINT(4) UNSIGNED   NOT NULL,
    PRIMARY KEY (`ID`)
)
    COLLATE = 'utf16_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 4096
;


CREATE TABLE `fabcms_sense_banner`
(
    `ID`                            MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `hook_ID`                       MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `code`                          TEXT                  NULL DEFAULT NULL,
    `probability`                   TINYINT(3) UNSIGNED   NULL DEFAULT NULL,
    `probability_progression_start` SMALLINT(5) UNSIGNED  NULL DEFAULT NULL,
    `probability_progression_end`   SMALLINT(5) UNSIGNED  NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `hook_ID` (`hook_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 963
;


CREATE TABLE `fabcms_relog`
(
    `ID`        INT(11) UNSIGNED      NOT NULL AUTO_INCREMENT,
    `user_ID`   MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `date`      DATETIME              NULL DEFAULT NULL,
    `module`    VARCHAR(255)          NULL DEFAULT NULL,
    `type`      TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `operation` VARCHAR(255)          NULL DEFAULT NULL,
    `details`   TEXT                  NULL DEFAULT NULL,
    `IP`        VARCHAR(15)           NULL DEFAULT NULL,
    `page`      VARCHAR(255)          NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `user_ID` (`user_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 953
;


--
-- Create table `fabcms_plugins_chain`
--
CREATE TABLE fabcms_plugins_chain
(
    ID      mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    spot    varchar(255)          NOT NULL,
    type    varchar(255)          NOT NULL,
    target  varchar(255)          NOT NULL,
    data    text                  NOT NULL,
    visible tinyint(4) UNSIGNED   NOT NULL,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

--
-- Create table `fabcms_logs`
--
CREATE TABLE fabcms_logs
(
    ID     int(11)      NOT NULL AUTO_INCREMENT,
    IP     varchar(32)  NOT NULL,
    date   datetime     NOT NULL,
    userID int(11)      NOT NULL,
    type   varchar(255) NOT NULL,
    module varchar(255) NOT NULL,
    log    longtext     NOT NULL,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    AVG_ROW_LENGTH = 161,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;


--
-- Create table `fabcms_hooks`
--
CREATE TABLE fabcms_hooks
(
    ID      mediumint(9) UNSIGNED                                   NOT NULL AUTO_INCREMENT,
    name    varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    html    text CHARACTER SET utf8 COLLATE utf8_general_ci         NOT NULL,
    enabled tinyint(4) UNSIGNED                                     NOT NULL,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    AVG_ROW_LENGTH = 4096,
    CHARACTER SET utf16,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf16_general_ci;

--
-- Create table `fabcms_fabmenu`
--
CREATE TABLE `fabcms_fabmenu`
(
    `ID`                MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_ID`         MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `lang`              VARCHAR(2)            NULL DEFAULT NULL,
    `type`              TINYINT(4) UNSIGNED   NULL DEFAULT NULL COMMENT '1 = custom url, 2 = generator',
    `url`               VARCHAR(255)          NULL DEFAULT NULL,
    `name`              VARCHAR(128)          NULL DEFAULT NULL,
    `icon`              VARCHAR(32)           NULL DEFAULT NULL,
    `module`            VARCHAR(48)           NULL DEFAULT NULL,
    `generator`         VARCHAR(255)          NULL DEFAULT NULL,
    `generator_options` VARCHAR(255)          NULL DEFAULT NULL,
    `order`             MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `parent_ID` (`parent_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 2048
;

-- Create table `fabcms_core_modules`
--
CREATE TABLE fabcms_core_modules
(
    ID       mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    module   varchar(255)         DEFAULT NULL,
    `schema` smallint(5) UNSIGNED DEFAULT NULL,
    config   text                 DEFAULT NULL,
    enabled  tinyint(4)           DEFAULT NULL,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;



--
-- Create table `fabcms_config`
--
CREATE TABLE fabcms_config
(
    lang           varchar(2)   DEFAULT NULL,
    module         varchar(255) DEFAULT NULL,
    param          varchar(255) DEFAULT NULL,
    value          varchar(255) DEFAULT NULL,
    extended_value text         DEFAULT NULL
)
    ENGINE = ARIA,
    AVG_ROW_LENGTH = 682,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

