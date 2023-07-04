SELECT DISTINCT
    c.CustomerId,
    c.Name
FROM (
    SELECT j.JobID, MAX(j.TimeStamp) AS LastUpdate
    FROM job j
    WHERE j.Next = 0
    GROUP BY JobSequence
)jx
INNER JOIN job j ON j.JobId = jx.JobID AND j.TimeStamp = jx.LastUpdate
INNER JOIN customer c ON c.CustomerId = j.CustomerId
ORDER BY c.Name ASC, c.CustomerId