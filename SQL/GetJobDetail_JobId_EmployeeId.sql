SELECT
	CONVERT(j.JobSequence, CHAR) AS JobId,
	c.CustomerId,
    c.Name AS CustomerName,
    e.EmployeeId,
    e.Name AS Fullname
FROM (
    SELECT j.JobID, MAX(j.TimeStamp) AS LastUpdate
    FROM job j
    WHERE j.Next = 0
    GROUP BY JobSequence
)jx
INNER JOIN job j ON j.JobId = jx.JobID AND j.TimeStamp = jx.LastUpdate
INNER JOIN customer c ON c.CustomerId = j.CustomerId
INNER JOIN job_emp je ON je.JobId = j.JobId
INNER JOIN employee e ON e.EmployeeId = je.EmployeeId
WHERE j.JobSequence = {0}  AND e.EmployeeId = {1}