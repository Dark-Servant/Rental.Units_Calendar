-- // Add columns (technic_id and date) for comment table
-- Migration SQL that makes the change goes here.

ALTER TABLE `comments` ADD `TECHNIC_ID` INT NOT NULL DEFAULT '0' AFTER `ID`, ADD `CONTENT_DATE` DATE  NOT NULL AFTER `TECHNIC_ID`;
ALTER TABLE `comments` CHANGE `USER_ID` `USER_ID` INT NOT NULL;

-- @UNDO
-- SQL to undo the change goes here.

ALTER TABLE `comments`
  DROP `TECHNIC_ID`,
  DROP `CONTENT_DATE`;

ALTER TABLE `comments` CHANGE `USER_ID` `USER_ID` varchar(250) COLLATE utf8_unicode_ci NOT NULL;