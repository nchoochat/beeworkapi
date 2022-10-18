SELECT 
	e.Name AS FullName,
    dp.Position AS PositionName,
    dp.DepPositionId AS RoleId,
    CASE
    	WHEN dp.DepPositionId = '1' THEN UPPER(dp.Position)
        WHEN dp.DepPositionId = '2' THEN UPPER(dp.Position)
        WHEN dp.DepPositionId = '4' THEN UPPER(dp.Position)
        ELSE NULL
    END AS Role
FROM employee e
LEFT JOIN dep_position dp ON dp.DepPositionId = e.DepPositionId
WHERE e.EmployeeId = '%s'
