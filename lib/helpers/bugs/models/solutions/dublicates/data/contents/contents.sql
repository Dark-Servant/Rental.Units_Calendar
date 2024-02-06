-- get all dublicates: getting of contents with identical technic ID, URL and specification code

SELECT
    c.ID, c.SORT, c.SPECIFICATION_ID, c.BEGIN_DATE, c.FINISH_DATE, (c.FINISH_DATE - c.BEGIN_DATE) DAYSCOUNT, cs.COPY_COUNT, cp.PART_COUNT
FROM contents c
INNER JOIN (
		SELECT
            c.SPECIFICATION_ID, COUNT(*) COPY_COUNT
		FROM contents c
		WHERE
			c.ID = c.SORT
		GROUP BY c.SPECIFICATION_ID
    	HAVING
    		COPY_COUNT > 1
    ) cs ON
    c.SPECIFICATION_ID = cs.SPECIFICATION_ID
INNER JOIN (
        SELECT
            SORT, COUNT(*) PART_COUNT
		FROM contents
		GROUP BY SORT
	) cp ON
    c.SORT = cp.SORT  
ORDER BY
    SORT DESC,
	ID ASC;