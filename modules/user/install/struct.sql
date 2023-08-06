--
-- Create table `fabcms_users_tags`
--
CREATE TABLE `fabcms_users_tags`
(
    `ID`      MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_ID` MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `tag`     VARCHAR(255)          NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `user_ID` (`user_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 2048
;

--
-- Create table `fabcms_users_groups`
--
CREATE TABLE fabcms_users_groups
(
    ID           mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    group_name   varchar(255)        DEFAULT NULL,
    group_type   tinyint(4) UNSIGNED DEFAULT NULL COMMENT '1 = admin, 2 = registered, 3 = guest',
    restrictions tinyint(4) UNSIGNED DEFAULT NULL COMMENT '0 = no restriction; 1 = banned',
    group_order  tinyint(4) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    AVG_ROW_LENGTH = 5461,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

CREATE TABLE `fabcms_users`
(
    `ID`                      MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `group_ID`                MEDIUMINT(8) UNSIGNED NOT NULL,
    `admin`                   TINYINT(4) UNSIGNED   NULL DEFAULT 0,
    `username`                VARCHAR(255)          NOT NULL,
    `name`                    VARCHAR(255)          NULL DEFAULT NULL,
    `article_signature`       VARCHAR(32)           NULL DEFAULT NULL,
    `surname`                 VARCHAR(255)          NULL DEFAULT NULL,
    `birthdate`               DATE                  NULL DEFAULT NULL,
    `short_biography`         TEXT                  NULL DEFAULT NULL,
    `biography`               TEXT                  NULL DEFAULT NULL,
    `reset_email_hash`        VARCHAR(32)           NULL DEFAULT NULL,
    `privacy_profile_level`   VARCHAR(32)           NULL DEFAULT NULL,
    `password`                VARCHAR(255)          NOT NULL,
    `email`                   VARCHAR(255)          NOT NULL,
    `email_pec`               VARCHAR(255)          NULL DEFAULT NULL,
    `tax_id`                  VARCHAR(32)           NULL DEFAULT NULL,
    `fiscal_code`             VARCHAR(32)           NULL DEFAULT NULL,
    `electronic_invoice_code` VARCHAR(7)            NULL DEFAULT NULL,
    `registration_IP`         VARCHAR(32)           NULL DEFAULT NULL,
    `optin_IP_confirm`        VARCHAR(32)           NULL DEFAULT NULL,
    `newsletter`              TINYINT(1) UNSIGNED   NULL DEFAULT NULL,
    `registration_date`       DATE                  NULL DEFAULT NULL,
    `optin_hash`              VARCHAR(32)           NULL DEFAULT NULL,
    `address`                 VARCHAR(255)          NULL DEFAULT NULL,
    `address_2`               VARCHAR(255)          NULL DEFAULT NULL,
    `city`                    VARCHAR(255)          NULL DEFAULT NULL,
    `state`                   VARCHAR(255)          NULL DEFAULT NULL,
    `google_plus`             VARCHAR(255)          NULL DEFAULT NULL,
    `latest_optin`            DATE                  NULL DEFAULT NULL,
    `zip`                     VARCHAR(32)           NULL DEFAULT NULL,
    `country`                 VARCHAR(64)           NULL DEFAULT NULL,
    `enabled`                 TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `group_ID` (`group_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 4096
;


CREATE TABLE `fabcms_notifications`
(
    `ID`      MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_ID` MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `date`    DATETIME              NULL DEFAULT NULL,
    `module`  VARCHAR(255)          NULL DEFAULT NULL,
    `title`   VARCHAR(255)          NULL DEFAULT NULL,
    `text`    VARCHAR(512)          NULL DEFAULT NULL,
    `is_read` TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `is_bot`  TINYINT(4) UNSIGNED   NULL DEFAULT NULL COMMENT '0 = user generated; 1 = bot generated',
    PRIMARY KEY (`ID`),
    INDEX `user_ID` (`user_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;

CREATE TABLE `fabcms_users_social`
(
    `ID`      MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_ID` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `social`  VARCHAR(128)          NULL DEFAULT NULL COLLATE 'utf8_general_ci',
    `profile` VARCHAR(255)          NULL DEFAULT NULL COLLATE 'utf8_general_ci',
    `notes`   VARCHAR(512)          NULL DEFAULT NULL COLLATE 'utf8_general_ci',
    PRIMARY KEY (`ID`) USING BTREE,
    INDEX `user_ID` (`user_ID`) USING BTREE
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;
