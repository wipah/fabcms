CREATE TABLE fabcms_connectors
(
    ID              mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    handler         varchar(255)          NOT NULL,
    connector       varchar(50)           NOT NULL,
    action          varchar(255)          NOT NULL,
    additional_data text                  NOT NULL,
    `order`         tinyint(3) UNSIGNED   NOT NULL,
    enabled         tinyint(3) UNSIGNED   NOT NULL,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    AVG_ROW_LENGTH = 16384,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;