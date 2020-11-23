-- // bug with comment for contents but now with partners
-- Migration SQL that makes the change goes here.

UPDATE `comments` `cm`
    SET
        `cm`.`CONTENT_ID` = (
                SELECT MIN(`c`.`ID`)
                FROM `contents` `c`
                WHERE
                    (
                        (`c`.`TECHNIC_ID` = `cm`.`TECHNIC_ID`) OR
                        (`c`.`TECHNIC_ID` IN (
                                SELECT `t`.`ID`
                                FROM `technics` `t`
                                WHERE
                                    (`t`.`PARTNER_ID` IS NOT NULL) AND
                                    (`t`.`PARTNER_ID` <> 0) AND
                                    (
                                        `t`.`PARTNER_ID` = (
                                            SELECT `t2`.`PARTNER_ID`
                                            FROM `technics` `t2`
                                            WHERE
                                                `t2`.`ID` = `cm`.`TECHNIC_ID`
                                        )
                                    )
                            )
                        )
                    ) AND
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
                    (
                        (`c`.`TECHNIC_ID` = `cm`.`TECHNIC_ID`) OR
                        (`c`.`TECHNIC_ID` IN (
                                SELECT `t`.`ID`
                                FROM `technics` `t`
                                WHERE
                                    (`t`.`PARTNER_ID` IS NOT NULL) AND
                                    (`t`.`PARTNER_ID` <> 0) AND
                                    (
                                        `t`.`PARTNER_ID` = (
                                            SELECT `t2`.`PARTNER_ID`
                                            FROM `technics` `t2`
                                            WHERE
                                                `t2`.`ID` = `cm`.`TECHNIC_ID`
                                        )
                                    )
                            )
                        )
                    ) AND
                    (`c`.`BEGIN_DATE` <= `cm`.`CONTENT_DATE`) AND
                    (`c`.`FINISH_DATE` >= `cm`.`CONTENT_DATE`)
            ) > 0
        );

-- @UNDO
-- SQL to undo the change goes here.
