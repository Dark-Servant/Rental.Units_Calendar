-- // add field for chosen by user date at calendar
-- Migration SQL that makes the change goes here.

ALTER TABLE `responsibles` ADD `CALENDAR_DATE` DATETIME NOT NULL AFTER `NAME`;

-- @UNDO
-- SQL to undo the change goes here.

ALTER TABLE `responsibles` DROP `CALENDAR_DATE`;