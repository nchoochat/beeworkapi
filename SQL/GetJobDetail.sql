SELECT
	j.JobId, j.CustomerName, j.Location, j.Map, j.ContractName, j.JobType, j.Appointment, j.BoxNumber, j.Remark
FROM job j
WHERE j.JobId = '%s'