SELECT 
	e.Name AS FullName,
    dp.Position AS PositionName,
    r.RoleId,
    UPPER(r.RoleName) AS Role
FROM employee e
LEFT JOIN dep_position dp ON dp.DepPositionId = e.DepPositionId
LEFT JOIN (
    SELECT u.EmployeeId, r.RoleId, r.RoleName
    FROM user u 
    INNER JOIN role r ON r.RoleId = u.RoleId
    GROUP BY u.EmployeeId
) r ON r.EmployeeId = e.EmployeeId
WHERE e.EmployeeId = '%s'
