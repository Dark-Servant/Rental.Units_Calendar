-- // create table for chosen technics
-- Migration SQL that makes the change goes here.

CREATE TABLE `chosen_technics` (
 `ID` int(11) NOT NULL AUTO_INCREMENT,
 `USER_ID` int(11) NOT NULL,
 `ENTITY_ID` int(11) NOT NULL,
 `IS_PARTNER` int(11) NOT NULL DEFAULT '0',
 `IS_ACTIVE` int(11) NOT NULL DEFAULT '1',
 `CREATED_AT` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci

-- @UNDO
-- SQL to undo the change goes here.

DROP TABLE IF EXISTS `chosen_technics`;