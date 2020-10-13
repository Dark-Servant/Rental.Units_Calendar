-- // Bug with comment for contents, when comment can to display with other dates at calendar
-- Migration SQL that makes the change goes here.

UPDATE `comments`
    SET
        `CONTENT_ID` = 0
    WHERE
        (`TECHNIC_ID` <> (SELECT `c`.`TECHNIC_ID` FROM `contents` `c` WHERE `c`.`ID` = `CONTENT_ID`)) OR
        (`CONTENT_DATE` < (SELECT `c`.`BEGIN_DATE` FROM `contents` `c` WHERE `c`.`ID` = `CONTENT_ID`)) OR
        (`CONTENT_DATE` > (SELECT `c`.`FINISH_DATE` FROM `contents` `c` WHERE `c`.`ID` = `CONTENT_ID`));

UPDATE `comments` `cm`
    SET
        `cm`.`CONTENT_ID` = (
                SELECT MIN(`c`.`ID`)
                FROM `contents` `c`
                WHERE
                    (`c`.`TECHNIC_ID` = `cm`.`TECHNIC_ID`) AND
                    (`c`.`BEGIN_DATE` <= `cm`.`CONTENT_DATE`) AND
                    (`c`.`FINISH_DATE` >= `cm`.`CONTENT_DATE`)
            )
    WHERE
        (`CONTENT_ID` = 0) AND
        (
            (
                SELECT COUNT(`c`.`ID`)
                FROM `contents` `c`
                WHERE
                    (`c`.`TECHNIC_ID` = `cm`.`TECHNIC_ID`) AND
                    (`c`.`BEGIN_DATE` <= `cm`.`CONTENT_DATE`) AND
                    (`c`.`FINISH_DATE` >= `cm`.`CONTENT_DATE`)
            ) > 0
        );

-- @UNDO
-- SQL to undo the change goes here.