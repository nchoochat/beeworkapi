SELECT
	ROW_NUMBER() OVER (ORDER BY j.JobSequence) AS RowNo,
	j.jobId AS ID,
	CONVERT(j.JobSequence, CHAR) AS JobId,
	c.Name AS CustomerName,
	jt.Name AS JobType,
	j.JobStatusId,
	js.Name AS JobStatusName,
	j.DayAppointment AS Appointment,
	b.BoxNo AS BoxNumber,
	cb.License,
	ec.ListOfActor,
	wsd.Detail AS Remark,
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
INNER JOIN job_status js ON js.JobStatusId = j.JobStatusId
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
LEFT JOIN work_sheet_detail wsd ON wsd.WorkSheetId = ws.WorkSheetId
LEFT JOIN box b ON b.BoxId = wsd.BoxId
LEFT JOIN customer_box cb ON cb.BoxId = b.BoxId
        and cb.Next = 0
WHERE   
	 j.Next = 0 AND ('{4}' LIKE CONCAT( '%', CONVERT(j.JobStatusId,CHAR(1)), '%'))  AND (ec.ListOfEmployeeId LIKE CONCAT('%[', '{0}', ']%') OR '{0}' = 'all')
    AND (c.CustomerId ='{1}' OR '{1}' = 'all')
    AND (j.DayAppointment BETWEEN '{2}' AND '{3}')
    AND(
        (CURRENT_TIMESTAMP < TIMESTAMP(CURRENT_DATE, '16:00:00') AND j.DayAppointment <= TIMESTAMP(CURRENT_DATE, '23:59:59')
        ) OR (
            CURRENT_TIMESTAMP >= TIMESTAMP(CURRENT_DATE, '16:00:00') AND j.DayAppointment <= TIMESTAMP(CURRENT_DATE, '23:59:59') + INTERVAL 1 DAY
        ) OR '{0}' = 'all'
    )
    AND IF(ISNULL(b.BoxNo) = 1, '', b.BoxNo) LIKE '%{5}%'
ORDER BY j.DayAppointment DESC;