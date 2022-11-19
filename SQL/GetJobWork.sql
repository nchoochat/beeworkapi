SELECT 
	'all' AS EmployeeId,
    'ทั้งหมด' AS FullName,
    '' AS ListOfJob,
    0 AS PendingWork, 0 AS PendingClose
UNION ALL
SELECT
	e.EmployeeId,
	e.Name AS FullName,
	j.ListOfJob,
    0 AS PendingWork, 0 AS PendingClose
FROM employee e
INNER JOIN (
    SELECT
        jm.EmployeeId,
        GROUP_CONCAT(IF(ISNULL(j.JobSequence) = 1, '', j.JobSequence) SEPARATOR ',') AS ListOfJob
    FROM (
        SELECT j.JobID, MAX(j.TimeStamp) AS LastUpdate
        FROM job j
        WHERE j.Next = 0
        GROUP BY JobSequence
    )jx
    INNER JOIN Job j ON J.JobId = jx.JobID AND j.TimeStamp = jx.LastUpdate
    INNER JOIN job_emp jm ON j.JobId = jm.JobId
    WHERE j.Next =0 AND j.JobStatusId = 1
    AND j.DayAppointment <=
	CASE
    	WHEN CURRENT_TIMESTAMP < TIMESTAMP(CURRENT_DATE, '16:00:00') THEN TIMESTAMP(CURRENT_DATE, '23:59:59')
        ELSE  TIMESTAMP(CURRENT_DATE, '23:59:59') + INTERVAL 1 DAY 
	END
    GROUP By jm.EmployeeId
) j ON j.EmployeeId = e.EmployeeId