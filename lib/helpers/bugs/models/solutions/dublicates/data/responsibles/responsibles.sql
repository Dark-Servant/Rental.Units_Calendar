-- get responsible dublicates: getting of responsibles with identical NAME

SELECT rr.TIMES, r.EXTERNAL_ID, r.NAME, r.ID
FROM (
		SELECT r.NAME, COUNT(*) TIMES
		FROM responsibles r
		GROUP BY NAME
		HAVING
			TIMES > 1
	) rr
INNER JOIN responsibles r ON
	r.NAME = rr.NAME
ORDER BY EXTERNAL_ID ASC;