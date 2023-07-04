SELECT DISTINCT
    e.EmployeeId, CONCAT(e.Name, '(', e.EmployeeId, ')')  AS FullName
FROM (
    SELECT j.JobID, MAX(j.TimeStamp) AS LastUpdate
    FROM job j
    WHERE j.Next = 0
    GROUP BY JobSequence
)jx
INNER JOIN job_emp je ON je.JobId = jx.JobID
INNER JOIN employee e ON e.EmployeeId = je.EmployeeId
ORDER BY e.Name ASC, e.EmployeeId ASC