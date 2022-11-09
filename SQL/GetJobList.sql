SELECT
	e.EmployeeId,
    e.Name as FullName,
    j.jobId AS ID,
	CONVERT(j.JobSequence, CHAR) AS JobId,
    c.Name AS CustomerName,
    c.Address,
    REPLACE(j.Location, SUBSTRING(j.Location, POSITION('{' IN j.Location), POSITION('}' IN j.Location)), '') AS 'Location',
    REPLACE(REPLACE(SUBSTRING(j.Location, POSITION('{' IN j.Location), POSITION('}' IN j.Location)), '{', ''), '}', '') AS Map,
    jc.ContractName,
    jc.ContractPhone,
    jt.Name AS JobType,
    j.DayAppointment AS Appointment,
    jb.BoxNo AS BoxNumber,
    ec.ListOfActor,
    j.DescriptiON AS Remark,
    j.TimeStamp AS UpdateDate,
    CASE WHEN DATE(j.DayAppointment) < DATE(CURRENT_TIMESTAMP) THEN '1' ELSE '0' END AS IsPastAppointment,
    null AS AcceptDate,
    null AS NotifyDate,
    0 AS NumOfAttachment    
FROM (
    SELECT j.JobID, MAX(j.TimeStamp) AS LastUpdate
    FROM job j
    WHERE j.Next = 0
    GROUP BY JobSequence
)jx
INNER JOIN job j ON j.JobId = jx.JobID AND j.TimeStamp = jx.LastUpdate
INNER JOIN job_emp jm ON j.JobId = jm.JobId
INNER JOIN employee e ON e.EmployeeId = jm.EmployeeId
INNER JOIN customer c ON c.CustomerId = j.CustomerId
INNER JOIN job_type jt ON jt.JobTypeId = j.JobTypeId
LEFT JOIN (
    SELECT
    	jc.JobId,
    	jc.Name AS ContractName,
    	jc.Phone AS ContractPhone
    FROM job_contact jc
    GROUP BY jc.JobId
) jc ON jc.JobId = j.JobId
LEFT JOIN (
	SELECT
    	jb.JobId,
    	GROUP_CONCAT(b.BoxNo SEPARATOR ', ') AS BoxNo
    FROM box b
    INNER JOIN job_box jb ON jb.BoxId = b.BoxId
    GROUP BY jb.JobId
 )jb ON jb.JobId = j.JobId
 LEFT JOIN (
    SELECT
        jm.JobId,
        GROUP_CONCAT(IF(ISNULL(e.Name) = 1, '', e.Name) SEPARATOR '\n') AS ListOfActor
    FROM job_emp jm
    INNER JOIN employee e ON jm.EmployeeId = e.EmployeeId
    GROUP By jm.JobId
 )ec ON ec.JobId = j.JobId
WHERE
    j.Next = 0 AND j.JobStatusId = 1 AND (e.EmployeeId = '{0}' OR '{0}' = 'All')
    AND(
        (
            CURRENT_TIMESTAMP < TIMESTAMP(CURRENT_DATE, '16:00:00') AND j.DayAppointment <= TIMESTAMP(CURRENT_DATE, '23:59:59')
        ) OR (
            CURRENT_TIMESTAMP >= TIMESTAMP(CURRENT_DATE, '16:00:00') AND j.DayAppointment <= TIMESTAMP(CURRENT_DATE, '23:59:59') + INTERVAL 1 DAY
        )
    )
ORDER BY j.DayAppointment ASC;