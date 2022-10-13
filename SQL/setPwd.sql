UPDATE employee SET
    Pwd = '%s', UpdateDate = CURRENT_TIMESTAMP()
WHERE EmployeeId = '%s';
