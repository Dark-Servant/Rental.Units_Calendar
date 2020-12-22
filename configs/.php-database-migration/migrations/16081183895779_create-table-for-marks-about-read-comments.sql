-- // create table for marks about read comments
-- Migration SQL that makes the change goes here.

CREATE TABLE IF NOT EXISTS `read_comment_marks` (
    `ID` INT NOT NULL AUTO_INCREMENT,
    `COMMENT_ID` INT NOT NULL,
    `USER_ID` INT NOT NULL,
    `CREATED_AT` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ID`),
    INDEX(`COMMENT_ID`),
    INDEX(`USER_ID`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- @UNDO
-- SQL to undo the change goes here.

DROP TABLE IF EXISTS `read_comment_marks`;