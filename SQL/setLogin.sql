UPDATE employee e
SET Username = '{1}', Pwd = '{2}'
WHERE e.EmployeeId = '{0}'
AND 0 = (
    SELECT COUNT(1)
    FROM employee AS e 
    WHERE e.Username = '{1}' AND e.EmployeeId <> '{0}'
);