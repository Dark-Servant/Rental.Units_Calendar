-- // add column sort to content
-- Migration SQL that makes the change goes here.

ALTER TABLE `contents` ADD `SORT` INT(11) NOT NULL DEFAULT '0' AFTER `ID`;
UPDATE `contents` SET `SORT`=`ID`;

-- @UNDO
-- SQL to undo the change goes here.

ALTER TABLE `contents` DROP `SORT`;