-- // create table for partners
-- Migration SQL that makes the change goes here.

CREATE TABLE IF NOT EXISTS `partners` (
 `ID` int(11) NOT NULL AUTO_INCREMENT,
 `EXTERNAL_ID` int(11) NOT NULL DEFAULT '0',
 `NAME` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
 `CREATED_AT` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `partners`(`EXTERNAL_ID`, `NAME`)
    SELECT DISTINCT `PARTNER_ID`, `PARTNER_NAME` FROM `technics` WHERE `PARTNER_ID` <> 0;

UPDATE `technics` `t`
    SET
        `PARTNER_ID` = (
            SELECT `p`.`ID`
                FROM `partners` `p`
                WHERE
                    (`p`.`EXTERNAL_ID` = `t`.`PARTNER_ID`)
        )
    WHERE
        `PARTNER_ID` <> 0;

ALTER TABLE `technics` DROP `PARTNER_NAME`;

-- @UNDO
-- SQL to undo the change goes here.

ALTER TABLE `technics` ADD `PARTNER_NAME` VARCHAR(250) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER `PARTNER_ID`;

UPDATE `technics` `t`
    SET
        `PARTNER_NAME` = (
            SELECT `p`.`NAME`
                FROM `partners` `p`
                WHERE
                    (`p`.`ID` = `t`.`PARTNER_ID`)
        ),
        `PARTNER_ID` = (
            SELECT `p`.`EXTERNAL_ID`
                FROM `partners` `p`
                WHERE
                    (`p`.`ID` = `t`.`PARTNER_ID`)
        )
    WHERE
        `PARTNER_ID` <> 0;

DROP TABLE IF EXISTS `partners`;