SELECT
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
    ws.WorkSheetNo
FROM (
    SELECT j.JobID, MAX(j.TimeStamp) AS LastUpdate
    FROM job j
    WHERE j.Next = 0
    GROUP BY JobSequence
)jx
INNER JOIN job j ON j.JobId = jx.JobID AND j.TimeStamp = jx.LastUpdate
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
    	GROUP_CONCAT(CONCAT(b.BoxNo, '  [', IF(ISNULL(cb.License) = 1, '', cb.License) , ']') SEPARATOR '\n') AS BoxNo
    FROM box b
    INNER JOIN job_box jb ON jb.BoxId = b.BoxId
    LEFT JOIN customer_box cb ON cb.BoxId = b.BoxId
        and cb.Next = 0
    GROUP BY jb.JobId
 )jb ON jb.JobId = j.JobId
 LEFT JOIN (
    SELECT
        jm.JobId,
     	MAX(e.EmployeeId) AS EmployeeID,
     	MAX(e.Name) AS Name,
    	CONCAT('[', GROUP_CONCAT(IF(ISNULL(e.EmployeeId) = 1, '', e.EmployeeId) SEPARATOR '][') , ']') AS ListOfEmployeeId,
        GROUP_CONCAT(IF(ISNULL(e.Name) = 1, '', e.Name) SEPARATOR '\n') AS ListOfActor
    FROM job_emp jm
    INNER JOIN employee e ON jm.EmployeeId = e.EmployeeId
    GROUP By jm.JobId
 )ec ON ec.JobId = j.JobId
LEFT JOIN job_work_sheet jws ON jws.JobId = j.JobId
 LEFT JOIN work_sheet ws ON ws.WorkSheetId = jws.WorkSheetId
WHERE j.Next = 0 AND j.JobSequence = '{0}';