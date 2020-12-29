-- // add column for comments with duty status
-- Migration SQL that makes the change goes here.

ALTER TABLE `comments` ADD `DUTY_STATUS` INT NOT NULL DEFAULT '0' AFTER `VALUE`;

-- @UNDO
-- SQL to undo the change goes here.

ALTER TABLE `comments` DROP `DUTY_STATUS`;