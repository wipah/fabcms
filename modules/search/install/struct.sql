CREATE TABLE `fabcms_search_logs` (
                                      `ID` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                                      `user_ID` MEDIUMINT(8) UNSIGNED NOT NULL,
                                      `date` DATETIME NOT NULL,
                                      `phrase` VARCHAR(255) NOT NULL,
                                      `var_char` INT(255) NOT NULL,
                                      `method` ENUM('get','post','api') NOT NULL,
                                      `IP` VARCHAR(45) NOT NULL,
                                      `from_page` VARCHAR(255) NOT NULL,
                                      `results` MEDIUMINT(8) UNSIGNED NOT NULL,
                                      `interface` VARCHAR(255) NOT NULL COLLATE 'utf8_general_ci',
                                      PRIMARY KEY (`ID`),
                                      INDEX `ID` (`ID`),
                                      INDEX `user_ID` (`user_ID`)
)
    COLLATE='utf8_general_ci'
    ENGINE=Aria
    AVG_ROW_LENGTH=204
;

