SELECT
    j.jobId AS ID,
	CONVERT(j.JobSequence, CHAR) AS JobId,
    c.Name AS CustomerName,
    c.Address,
    REPLACE(j.Location, SUBSTRING(j.Location, POSITION('{' IN j.Location), POSITION('}' IN j.Location)), '') AS 'Location',
    REPLACE(REPLACE(SUBSTRING(j.Location, POSITION('{' IN j.Location), POSITION('}' IN j.Location)), '{', ''), '}', '') AS Map,
    jc.Name AS ContractName,
    jt.Name AS JobType,
    j.DayAppointment AS Appointment,
    jb.BoxNo AS BoxNumber,
    ec.ListOfActor,
    j.DescriptiON AS Remark,
    j.TimeStamp AS UpdateDate,
    null AS AcceptDate,
    null AS NotifyDate,
    0 AS NumOfAttachment
FROM (
    SELECT j.JobID, MAX(j.TimeStamp) AS LastUpdate
    FROM job j
    WHERE j.Next = 0
    GROUP BY JobSequence
)jx
INNER JOIN Job j ON J.JobId = jx.JobID AND j.TimeStamp = jx.LastUpdate
INNER JOIN job_emp jm ON j.JobId = jm.JobId
INNER JOIN employee e ON e.EmployeeId = jm.EmployeeId
INNER JOIN customer c ON c.CustomerId = j.CustomerId
INNER JOIN job_type jt ON jt.JobTypeId = j.JobTypeId
LEFT JOIN job_contact jc ON jc.JobId = j.JobId
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
        GROUP_CONCAT(IF(ISNULL(e.Name)=1, '', e.Name) SEPARATOR '\n') AS ListOfActor
    FROM job_emp jm
    INNER JOIN employee e ON jm.EmployeeId = e.EmployeeId
    GROUP By jm.JobId
 )ec ON ec.JobId = j.JobId
WHERE j.Next =0 AND j.JobStatusId = 1 AND e.EmployeeId = '%s'
AND j.DayAppointment <=
	CASE
    	WHEN CURRENT_TIMESTAMP < TIMESTAMP(CURRENT_DATE, '16:00:00') THEN TIMESTAMP(CURRENT_DATE, '23:59:59')
        ELSE  TIMESTAMP(CURRENT_DATE, '23:59:59') + INTERVAL 1 DAY 
	END
ORDER BY j.DayAppointment ASC