CREATE TABLE `fabcms_fabmedia_videos`
(
    `ID`              MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `fabmedia_ID`     MEDIUMINT(9) UNSIGNED NOT NULL,
    `is_external`     TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `provider`        VARCHAR(50)           NOT NULL COMMENT '0 = internal, 1 = youtube, 2 = vimeo',
    `provider_ID`     VARCHAR(255)          NOT NULL,
    `lenght`          TIME                  NULL DEFAULT NULL,
    `allow_download`  TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `allow_share`     TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `allow_embedding` TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `visible`         TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `fabmedia_ID` (`fabmedia_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 5461
;

CREATE TABLE `fabcms_fabmedia_masters`
(
    `ID`      MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_ID` MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `user_ID` (`user_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 16384
;

CREATE TABLE `fabcms_fabmedia_images`
(
    `ID`       MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `file_ID`  MEDIUMINT(9) UNSIGNED NOT NULL,
    `width`    MEDIUMINT(9) UNSIGNED NOT NULL,
    `height`   MEDIUMINT(9) UNSIGNED NOT NULL,
    `gps`      GEOMETRY              NOT NULL,
    `metadata` TEXT                  NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `file_ID` (`file_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 16384
;

CREATE TABLE `fabcms_fabmedia_galleries_items`
(
    `ID`         MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_ID`    MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `image_ID`   MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `gallery_ID` MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `order`      SMALLINT(5) UNSIGNED  NULL DEFAULT NULL,
    `visible`    TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `user_ID` (`user_ID`),
    INDEX `image_ID` (`image_ID`),
    INDEX `gallery_ID` (`gallery_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 3276
;

CREATE TABLE `fabcms_fabmedia_galleries_galleries`
(
    `ID`               MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `master_ID`        MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `user_ID`          MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `cover_ID`         MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `lang`             VARCHAR(2)            NULL DEFAULT NULL,
    `title`            VARCHAR(255)          NULL DEFAULT NULL,
    `trackback`        VARCHAR(255)          NULL DEFAULT NULL,
    `meta_description` VARCHAR(255)          NULL DEFAULT NULL,
    `description`      TEXT                  NULL DEFAULT NULL,
    `order`            MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `visible`          TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `master_ID` (`master_ID`),
    INDEX `user_ID` (`user_ID`),
    INDEX `cover_ID` (`cover_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 3276
;

CREATE TABLE `fabcms_fabmedia_downloads`
(
    `ID`           MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_ID`      MEDIUMINT(9) UNSIGNED NOT NULL,
    `media_ID`     MEDIUMINT(9) UNSIGNED NOT NULL,
    `date`         DATETIME              NOT NULL DEFAULT current_timestamp(),
    `is_anonymous` TINYINT(4) UNSIGNED   NULL     DEFAULT NULL,
    `is_bot`       TINYINT(4) UNSIGNED   NULL     DEFAULT NULL,
    `IP`           VARCHAR(15)           NOT NULL,
    PRIMARY KEY (`ID`),
    INDEX `user_ID` (`user_ID`),
    INDEX `media_ID` (`media_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 16384
;

CREATE TABLE `fabcms_fabmedia_custom_filetypes`
(
    `ID`             MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `extension`      VARCHAR(32)           NOT NULL,
    `import_plugins` VARCHAR(1024)         NOT NULL COMMENT 'Comma separated',
    `export_plugins` VARCHAR(1024)         NOT NULL COMMENT 'Comma separated',
    `notes`          TEXT                  NOT NULL,
    `module`         VARCHAR(50)           NULL DEFAULT NULL,
    `allowed_group`  VARCHAR(1024)         NULL DEFAULT NULL COMMENT 'Comma separated values',
    `enabled`        TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    PRIMARY KEY (`ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 4096
;

CREATE TABLE `fabcms_fabmedia`
(
    `ID`               MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `master_ID`        MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `license_ID`       SMALLINT(5) UNSIGNED  NULL DEFAULT NULL,
    `user_ID`          MEDIUMINT(9) UNSIGNED NOT NULL,
    `lang`             VARCHAR(2)            NULL DEFAULT NULL,
    `filename`         VARCHAR(255)          NULL DEFAULT NULL,
    `extension`        VARCHAR(32)           NULL DEFAULT NULL,
    `type`             VARCHAR(64)           NULL DEFAULT NULL,
    `subtype`          VARCHAR(64)           NULL DEFAULT NULL,
    `modified`         TINYINT(4) UNSIGNED   NULL DEFAULT 0,
    `upload_date`      DATETIME              NULL DEFAULT '0000-00-00 00:00:00',
    `size`             MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `modify_date`      DATE                  NULL DEFAULT NULL,
    `title`            VARCHAR(255)          NULL DEFAULT NULL,
    `trackback`        VARCHAR(255)          NULL DEFAULT NULL,
    `author`           VARCHAR(255)          NULL DEFAULT NULL,
    `link`             VARCHAR(255)          NULL DEFAULT NULL,
    `tags`             VARCHAR(255)          NULL DEFAULT NULL,
    `description`      TEXT                  NULL DEFAULT NULL,
    `indexable`        TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `global_available` TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `allow_download`   TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `guest_downlod`    TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `notes`            TEXT                  NULL DEFAULT NULL COMMENT 'Internal notes',
    `enabled`          TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `master_ID` (`master_ID`),
    INDEX `license_ID` (`license_ID`),
    INDEX `user_ID` (`user_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 16384
;

CREATE TABLE `fabmedia_importer`
(
    `ID`            MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(50)           NOT NULL DEFAULT '0' COLLATE 'utf8_general_ci',
    `import_method` VARCHAR(255)          NOT NULL DEFAULT '0' COLLATE 'utf8_general_ci',
    `view_plugin`   VARCHAR(255)          NOT NULL DEFAULT '0' COLLATE 'utf8_general_ci',
    `trackback`     VARCHAR(50)           NOT NULL DEFAULT 'showitem' COLLATE 'utf8_general_ci',
    `enable_view`   TINYINT(3) UNSIGNED   NOT NULL DEFAULT '0',
    `enable_import` TINYINT(3) UNSIGNED   NOT NULL DEFAULT '0',
    PRIMARY KEY (`ID`) USING BTREE
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;

CREATE TABLE `fabmedia_importer_extension`
(
    `ID`          MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `importer_ID` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `extension`   VARCHAR(50)           NULL DEFAULT NULL COLLATE 'utf8_general_ci',
    `enabled`     TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    PRIMARY KEY (`ID`) USING BTREE,
    INDEX `importer_ID` (`importer_ID`) USING BTREE
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;
