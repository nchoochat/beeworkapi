SELECT j.jobId AS ID,
    CONVERT(j.JobSequence, CHAR) AS JobId,
    j.DayAppointment,
    e.EmployeeId,
    e.Name AS FullName,
    '' AS NotifyToken,
    c.Name AS CustomerName,
    j.TimeStamp AS UpdateDate,
    j.Description,
    '' AS NotifyType
FROM (
        SELECT j.JobID,
            MAX(j.TimeStamp) AS LastUpdate
        FROM job j
        WHERE j.Next = 0
        GROUP BY JobSequence
    ) jx
    INNER JOIN Job j ON J.JobId = jx.JobID
    AND j.TimeStamp = jx.LastUpdate
    INNER JOIN job_emp jm ON j.JobId = jm.JobId
    INNER JOIN employee e ON e.EmployeeId = jm.EmployeeId
    INNER JOIN customer c ON c.CustomerId = j.CustomerId
WHERE j.Next = 0 AND (e.EmployeeId = '{0}' OR '{0}' = 'All')
    AND j.JobStatusId = 1
    AND e.NotifyToken IS NOT NULL
   AND(
        (
            CURRENT_TIMESTAMP < TIMESTAMP(CURRENT_DATE, '16:00:00') AND j.DayAppointment <= TIMESTAMP(CURRENT_DATE, '23:59:59')
        ) OR (
            CURRENT_TIMESTAMP >= TIMESTAMP(CURRENT_DATE, '16:00:00') AND j.DayAppointment <= TIMESTAMP(CURRENT_DATE, '23:59:59') + INTERVAL 1 DAY
        )
    )