-- get customer dublicates: getting of customers with identical NAME

SELECT c.ID, c.NAME, cc.TIMES
FROM (
		SELECT c.NAME, COUNT(*) TIMES
		FROM customers c
		GROUP BY NAME
		HAVING
			TIMES > 1
	) cc
INNER JOIN customers c ON
	c.NAME = cc.NAME;