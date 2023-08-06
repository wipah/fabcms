--
-- Create table `fabcms_shop_vat`
--
CREATE TABLE fabcms_shop_vat
(
    ID          mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    code        varchar(10)           NOT NULL DEFAULT '0',
    value       tinyint(4) UNSIGNED   NOT NULL DEFAULT 0,
    description varchar(255)          NOT NULL DEFAULT '0',
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    AVG_ROW_LENGTH = 16384,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

--
-- Create table `fabcms_shop_variants_master`
--
CREATE TABLE fabcms_shop_variants_master
(
    ID mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    CHARACTER SET utf8,
    COLLATE utf8_general_ci,
    ROW_FORMAT = COMPACT;

--
-- Create table `fabcms_shop_variants`
--
CREATE TABLE fabcms_shop_variants
(
    ID                  mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    lang                varchar(2)            NOT NULL,
    variant_description varchar(255)          NOT NULL,
    varinat_note        text                  NOT NULL,
    variant_name        varchar(255)          NOT NULL,
    variant_enabled     tinyint(4) UNSIGNED   NOT NULL,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

--
-- Create table `fabcms_shop_user_discounts`
--
CREATE TABLE fabcms_shop_user_discounts
(
    ID       mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_ID  mediumint(8) UNSIGNED  DEFAULT NULL,
    discount decimal(4, 2) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

--
-- Create table `fabcms_shop_user_address`
--
CREATE TABLE `fabcms_shop_user_address`
(
    `ID`            MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_ID`       MEDIUMINT(8) UNSIGNED NOT NULL,
    `street`        VARCHAR(255)          NOT NULL,
    `zip`           VARCHAR(255)          NOT NULL,
    `city`          VARCHAR(255)          NOT NULL,
    `state`         VARCHAR(255)          NOT NULL,
    `nation`        VARCHAR(255)          NOT NULL,
    `internal_note` TEXT                  NOT NULL,
    `is_default`    TINYINT(4) UNSIGNED   NOT NULL,
    PRIMARY KEY (`ID`),
    INDEX `user_ID` (`user_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;


--
-- Create table `fabcms_shop_users_customizations`
--
CREATE TABLE `fabcms_shop_users_customizations`
(
    `ID`               MEDIUMINT(8) UNSIGNED  NOT NULL AUTO_INCREMENT,
    `user_ID`          MEDIUMINT(8) UNSIGNED  NULL DEFAULT NULL,
    `global_discount`  DECIMAL(5, 2) UNSIGNED NULL DEFAULT NULL,
    `custom_header`    TEXT                   NULL DEFAULT NULL,
    `custom_footer`    TEXT                   NULL DEFAULT NULL,
    `instant_checkout` TINYINT(1) UNSIGNED    NULL DEFAULT 0,
    PRIMARY KEY (`ID`),
    INDEX `user_ID` (`user_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = ARIA
;

--
-- Create table `fabcms_shop_triggers`
--
CREATE TABLE `fabcms_shop_triggers`
(
    `ID`           MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `item_ID`      MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
    `name`         VARCHAR(255)          NOT NULL DEFAULT '0',
    `description`  TEXT                  NOT NULL,
    `order`        TINYINT(3) UNSIGNED   NOT NULL DEFAULT 0,
    `trigger_data` VARCHAR(1024)         NULL     DEFAULT NULL,
    `enabled`      TINYINT(3) UNSIGNED   NOT NULL DEFAULT 0,
    PRIMARY KEY (`ID`),
    INDEX `item_ID` (`item_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;


--
-- Create table `fabcms_shop_item_masters`
--
CREATE TABLE fabcms_shop_item_masters
(
    ID mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    AVG_ROW_LENGTH = 8192,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

--
-- Create table `fabcms_shop_item_files`
--
CREATE TABLE `fabcms_shop_item_files`
(
    `ID`                MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `item_ID`           MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `file`              VARCHAR(255)          NULL DEFAULT NULL,
    `name`              VARCHAR(225)          NULL DEFAULT NULL,
    `type`              TINYINT(4) UNSIGNED   NULL DEFAULT NULL COMMENT '0 = public, 1 = after_purchase',
    `short_description` VARCHAR(255)          NULL DEFAULT NULL,
    `ordering`          TINYINT(3) UNSIGNED   NOT NULL,
    `enabled`           TINYINT(4) UNSIGNED   NULL DEFAULT NULL COMMENT '0 = disabled; 1 = enabled',
    PRIMARY KEY (`ID`),
    INDEX `item_ID` (`item_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 16384
;

--
-- Create table `fabcms_shop_item_additional_info_master`
--
CREATE TABLE fabcms_shop_item_additional_info_master
(
    ID mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

--
-- Create table `fabcms_shop_item_additional_info`
--
CREATE TABLE `fabcms_shop_item_additional_info`
(
    `ID`                MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `master_ID`         MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `type`              TINYINT(3) UNSIGNED   NULL DEFAULT NULL COMMENT '1 = boolean; 2 = float; 3 = select;',
    `lang`              VARCHAR(2)            NULL DEFAULT NULL,
    `name`              VARCHAR(2)            NULL DEFAULT NULL,
    `avaliable_options` TEXT                  NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `master_ID` (`master_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;


--
-- Create table `fabcms_shop_items_customizations_options`
--
CREATE TABLE `fabcms_shop_items_customizations_options`
(
    `ID`               MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `customization_ID` MEDIUMINT(8) UNSIGNED NOT NULL,
    `value`            VARCHAR(255)          NOT NULL,
    PRIMARY KEY (`ID`),
    INDEX `ID` (`ID`)
)
    COLLATE = 'utf16_general_ci'
    ENGINE = Aria
;


--
-- Create table `fabcms_shop_items_customizations_master`
--
CREATE TABLE fabcms_shop_items_customizations_master
(
    ID mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    AVG_ROW_LENGTH = 8192,
    CHARACTER SET utf16,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf16_general_ci;

--
-- Create table `fabcms_shop_items_customizations_link`
--
CREATE TABLE `fabcms_shop_items_customizations_link`
(
    `ID`               MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `item_ID`          MEDIUMINT(8) UNSIGNED NOT NULL,
    `customization_ID` MEDIUMINT(8) UNSIGNED NOT NULL,
    `order`            TINYINT(3) UNSIGNED   NOT NULL,
    PRIMARY KEY (`ID`),
    INDEX `item_ID` (`item_ID`),
    INDEX `customization_ID` (`customization_ID`)
)
    COLLATE = 'utf16_general_ci'
    ENGINE = Aria
;


--
-- Create table `fabcms_shop_items_customizations`
--
CREATE TABLE `fabcms_shop_items_customizations`
(
    `ID`           MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `master_ID`    MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `item_ID`      MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `lang`         VARCHAR(2)            NULL DEFAULT NULL,
    `name`         VARCHAR(255)          NULL DEFAULT NULL,
    `type`         TINYINT(3) UNSIGNED   NULL DEFAULT 1 COMMENT '1 = custom text; 2 = yes or no; 3 = single select: 4 = multiple select',
    `hint`         VARCHAR(255)          NULL DEFAULT '1',
    `help`         TEXT                  NULL DEFAULT NULL,
    `is_mandatory` TINYINT(3) UNSIGNED   NULL DEFAULT 1,
    `order`        TINYINT(4)            NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `master_ID` (`master_ID`),
    INDEX `item_ID` (`item_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;

CREATE TABLE `fabcms_shop_items`
(
    `ID`                MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `master_ID`         MEDIUMINT(9) UNSIGNED NULL     DEFAULT NULL,
    `category_ID`       TINYINT(4) UNSIGNED   NULL     DEFAULT NULL,
    `vat_ID`            MEDIUMINT(8) UNSIGNED NULL     DEFAULT NULL,
    `lang`              VARCHAR(2)            NOT NULL DEFAULT '0',
    `cod_art`           VARCHAR(255)          NULL     DEFAULT NULL,
    `title`             VARCHAR(255)          NULL     DEFAULT NULL,
    `tags`              VARCHAR(255)          NULL     DEFAULT NULL,
    `trackback`         VARCHAR(255)          NULL     DEFAULT NULL,
    `public_price`      DECIMAL(6, 2)         NULL     DEFAULT NULL,
    `discount_1`        TINYINT(3)            NULL     DEFAULT NULL,
    `discount_2`        TINYINT(3)            NULL     DEFAULT NULL,
    `discount_3`        TINYINT(3)            NULL     DEFAULT NULL,
    `final_price`       DECIMAL(6, 2)         NULL     DEFAULT NULL,
    `net_price`         DECIMAL(6, 2)         NOT NULL,
    `vat_amount`        DECIMAL(6, 2)         NULL     DEFAULT NULL,
    `cost_price`        DECIMAL(6, 2)         NULL     DEFAULT NULL,
    `short_description` VARCHAR(255)          NULL     DEFAULT NULL,
    `description`       TEXT                  NULL     DEFAULT NULL,
    `product_image`     VARCHAR(255)          NULL     DEFAULT NULL,
    `dismissed`         TINYINT(1) UNSIGNED   NULL     DEFAULT NULL,
    `is_promo`          TINYINT(4) UNSIGNED   NULL     DEFAULT NULL,
    `is_variant_master` TINYINT(4)            NULL     DEFAULT NULL,
    `is_stackable`      TINYINT(4) UNSIGNED   NULL     DEFAULT 1 COMMENT '0 = no; 1 = yes',
    `enabled`           TINYINT(4) UNSIGNED   NULL     DEFAULT NULL,
    `allow_note`        TINYINT(4) UNSIGNED   NULL     DEFAULT NULL COMMENT 'Can user post its own notes?',
    `mandatory_note`    TINYINT(4) UNSIGNED   NULL     DEFAULT NULL COMMENT 'User must fill own notes?',
    PRIMARY KEY (`ID`),
    INDEX `master_ID` (`master_ID`),
    INDEX `category_ID` (`category_ID`),
    INDEX `vat_ID` (`vat_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 8192
;

CREATE TABLE `fabcms_shop_downloads`
(
    `ID`            MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_ID`       MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `cart_item_ID`  SMALLINT(5) UNSIGNED  NULL DEFAULT NULL,
    `file`          VARCHAR(255)          NULL DEFAULT NULL COLLATE 'utf8_general_ci',
    `ip`            VARCHAR(15)           NULL DEFAULT NULL COLLATE 'utf8_general_ci',
    `download_date` DATETIME              NULL DEFAULT NULL,
    PRIMARY KEY (`ID`) USING BTREE,
    INDEX `user_ID` (`user_ID`) USING BTREE,
    INDEX `cart_item_ID` (`cart_item_ID`) USING BTREE
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 16384
;


CREATE TABLE fabcms_shop_couriers
(
    ID           int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    name         varchar(255)        DEFAULT NULL,
    tracking_URI varchar(1024)       DEFAULT NULL,
    method       tinyint(3) UNSIGNED DEFAULT 0 COMMENT '0 = POST; 1 = GET',
    codify       tinyint(3) UNSIGNED DEFAULT 0 COMMENT '0 = plain; 1 = XML; 2 = JSON',
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci,
    COMMENT = 'This tables stores informations about couriers.';

CREATE TABLE `fabcms_shop_coupons`
(
    `ID`                    MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_ID`               MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT 0,
    `appliable_to_group_ID` MEDIUMINT(9)          NOT NULL DEFAULT 0,
    `excluded_to_group_ID`  MEDIUMINT(9)          NOT NULL DEFAULT 0,
    `code`                  VARCHAR(50)           NOT NULL DEFAULT '0',
    `type`                  SMALLINT(6)           NOT NULL DEFAULT 0 COMMENT '1 = percentual, 2 net, 3 = free  shipping, 4 = 100% discount',
    `amount`                DECIMAL(5, 3)         NOT NULL DEFAULT 0.000,
    PRIMARY KEY (`ID`),
    INDEX `user_ID` (`user_ID`),
    INDEX `appliable_to_group_ID` (`appliable_to_group_ID`),
    INDEX `excluded_to_group_ID` (`excluded_to_group_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;

CREATE TABLE fabcms_shop_config
(
    ID    mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    lang  varchar(2)   DEFAULT NULL,
    param varchar(255) DEFAULT NULL,
    value varchar(255) DEFAULT NULL,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    AVG_ROW_LENGTH = 3276,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

CREATE TABLE fabcms_shop_categories_master
(
    ID mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    AVG_ROW_LENGTH = 5461,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

CREATE TABLE `fabcms_shop_categories`
(
    `ID`                 MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_master_ID` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `lang`               VARCHAR(2)            NULL DEFAULT NULL,
    `trackback`          VARCHAR(255)          NULL DEFAULT NULL,
    `title`              VARCHAR(255)          NULL DEFAULT NULL,
    `description`        TEXT                  NULL DEFAULT NULL,
    `thumb`              VARCHAR(255)          NULL DEFAULT NULL,
    `enabled`            TINYINT(1) UNSIGNED   NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `category_master_ID` (`category_master_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 4096
;

CREATE TABLE `fabcms_shop_cart_items_additional_info`
(
    `ID`                 MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `cart_item_ID`       MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `item_additional_ID` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `value`              TEXT                  NULL DEFAULT NULL,
    INDEX `ID` (`ID`),
    INDEX `cart_item_ID` (`cart_item_ID`),
    INDEX `item_additional_ID` (`item_additional_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;

CREATE TABLE `fabcms_shop_cart_items`
(
    `ID`              MEDIUMINT(8) UNSIGNED   NOT NULL AUTO_INCREMENT,
    `cart_ID`         MEDIUMINT(8) UNSIGNED   NULL DEFAULT NULL,
    `item_ID`         MEDIUMINT(8) UNSIGNED   NULL DEFAULT NULL,
    `item_qty`        MEDIUMINT(8) UNSIGNED   NULL DEFAULT NULL,
    `public_price`    DECIMAL(8, 2) UNSIGNED  NULL DEFAULT NULL,
    `discount_1`      DECIMAL(5, 2) UNSIGNED  NULL DEFAULT NULL,
    `discount_2`      DECIMAL(5, 2) UNSIGNED  NULL DEFAULT NULL,
    `discount_3`      DECIMAL(5, 2) UNSIGNED  NULL DEFAULT NULL,
    `final_price`     DECIMAL(10, 2) UNSIGNED NULL DEFAULT NULL,
    `net_price`       DECIMAL(10, 2) UNSIGNED NULL DEFAULT NULL,
    `cost_price`      DECIMAL(10, 2) UNSIGNED NULL DEFAULT NULL,
    `global_discount` DECIMAL(5, 2) UNSIGNED  NULL DEFAULT NULL,
    `vat_ID`          MEDIUMINT(8) UNSIGNED   NULL DEFAULT NULL,
    `vat_amount`      DECIMAL(10, 2) UNSIGNED NULL DEFAULT NULL,
    `add_date`        DATE                    NULL DEFAULT NULL,
    `is_promo`        TINYINT(3) UNSIGNED     NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `cart_ID` (`cart_ID`),
    INDEX `item_ID` (`item_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria;

CREATE TABLE `fabcms_shop_carts`
(
    `ID`                  MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_ID`             MEDIUMINT(8) UNSIGNED NULL DEFAULT 0,
    `payment_ID`          SMALLINT(5) UNSIGNED  NULL DEFAULT NULL,
    `anonymous_hash`      VARCHAR(32)           NULL DEFAULT NULL COMMENT '// MS5 salted hash',
    `start_date`          DATE                  NULL DEFAULT NULL,
    `status`              TINYINT(4) UNSIGNED   NULL DEFAULT 0,
    `latest_update`       DATETIME              NULL DEFAULT current_timestamp(),
    `has_global_discount` TINYINT(3) UNSIGNED   NULL DEFAULT 0,
    PRIMARY KEY (`ID`),
    INDEX `payment_ID` (`payment_ID`),
    INDEX `user_ID` (`user_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;



CREATE TABLE fabcms_shop_articles_group_translation
(
    ID          mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    lang        varchar(2)            NOT NULL,
    group_name  varchar(255)          NOT NULL,
    description text                  NOT NULL,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

CREATE TABLE fabcms_shop_articles_group_master
(
    ID   mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    code varchar(50)           NOT NULL DEFAULT '0',
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

--
-- Create table `fabcms_shop_articles_group_articles`
--
CREATE TABLE `fabcms_shop_articles_group_articles`
(
    `ID`         MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `article_ID` MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`ID`),
    INDEX `article_ID` (`article_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;
