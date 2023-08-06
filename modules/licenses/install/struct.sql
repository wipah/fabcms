CREATE TABLE `fabcms_licenses_licenses` (
                                            `ID` MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
                                            `master_ID` MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
                                            `lang` VARCHAR(2) NULL DEFAULT NULL,
                                            `name` VARCHAR(255) NULL DEFAULT NULL,
                                            `description` TEXT NULL DEFAULT NULL,
                                            `allow_derivate_works` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                                            `allow_share` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                                            `mandatory_credits` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                                            PRIMARY KEY (`ID`),
                                            INDEX `master_ID` (`master_ID`)
)
    COLLATE='utf8_general_ci'
    ENGINE=Aria
;

CREATE TABLE `fabcms_licenses_master` (
                                          `ID` MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
                                          PRIMARY KEY (`ID`)
)
    COLLATE='utf8_general_ci'
    ENGINE=Aria;

INSERT INTO fabcms_licenses_master (`ID`)
VALUES (1);