-- Content bad interval: Comments with exists content, but content has bad interval

SELECT cm.*
FROM `comments` cm
INNER JOIN contents c ON
    c.ID = cm.CONTENT_ID
WHERE
    (cm.CONTENT_DATE < c.BEGIN_DATE)
    OR (cm.CONTENT_DATE > c.FINISH_DATE);

-- My techics: getting of comments without content for my techics

SELECT cm.*, c.ID CID, c.SORT
FROM comments cm
INNER JOIN contents c ON
    cm.TECHNIC_ID = c.TECHNIC_ID
INNER JOIN technics t ON
    t.ID = cm.TECHNIC_ID
WHERE
    (
        (cm.CONTENT_ID = 0) OR
        (cm.CONTENT_ID IS NULL) OR
        (cm.ID IN (:IDS:))
    ) AND
    (t.IS_MY = 1) AND
    (cm.CONTENT_DATE >= c.BEGIN_DATE) AND
    (cm.CONTENT_DATE <= c.FINISH_DATE)
ORDER BY
    c.SORT ASC;

-- Partner technics: getting of comments without content for partner technics

SELECT cm.ID, cm.CONTENT_DATE, t.PARTNER_ID
FROM comments cm
INNER JOIN technics t ON
    t.ID = cm.TECHNIC_ID
WHERE
    (
        (cm.CONTENT_ID = 0) OR
        (cm.CONTENT_ID IS NULL) OR
        (cm.ID IN (:IDS:))
    ) AND
    (t.IS_MY = 0) AND
    (
        (
            SELECT COUNT(*)
            FROM contents cn
            INNER JOIN technics tt ON
                tt.ID = cn.TECHNIC_ID
            WHERE
                (tt.IS_MY = 0) AND
                (tt.PARTNER_ID = t.PARTNER_ID) AND
                (cn.BEGIN_DATE <= cm.CONTENT_DATE) AND
                (cn.FINISH_DATE >= cm.CONTENT_DATE)
        ) > 0
    );

-- Partner Techinc contents: getting content for technic partners, what was got with not hosted comments

SELECT c.ID, c.SORT, c.BEGIN_DATE, c.FINISH_DATE, c.TECHNIC_ID
FROM contents c
INNER JOIN technics t ON
    t.ID = c.TECHNIC_ID
WHERE
    (t.PARTNER_ID = :PARTNER_ID:) AND
    (t.IS_MY = 0)
ORDER BY
    c.SORT ASC

-- Update zero content comment: update comments, which not has contents with good interval

UPDATE comments
SET
    CONTENT_ID = 0
WHERE
    ID IN (:IDS:)

-- Update my technic comment: update comments, for which exists contents with good interval

UPDATE comments
SET
    CONTENT_ID = :CONTENT_ID:
WHERE
    ID IN (:IDS:)

-- Update partner comment: update comments, for which exists contents with good interval and exists technic of same partner

UPDATE comments
SET
    TECHNIC_ID = :TECHNIC_ID:,
    CONTENT_ID = :CONTENT_ID:
WHERE
    ID IN (:IDS:)
