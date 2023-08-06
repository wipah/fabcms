CREATE TABLE fabcms_cronjobs
(
    ID              mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    module          varchar(255)                  DEFAULT NULL,
    operation       varchar(255)                  DEFAULT NULL,
    additional_data text                          DEFAULT NULL,
    `interval`      smallint(5) UNSIGNED ZEROFILL DEFAULT NULL COMMENT 'Expressed in minutes',
    latest_check    datetime                      DEFAULT NULL,
    next_run        datetime                      DEFAULT NULL,
    latest_status   tinyint(3) UNSIGNED           DEFAULT NULL COMMENT '0 = no entry; 1 = completed; 2 = error',
    log             text                          DEFAULT NULL,
    enabled         tinyint(3) UNSIGNED           DEFAULT NULL COMMENT '0 = disabled; 1 = enabled',
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    AVG_ROW_LENGTH = 8192,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;