<?php
require_once PROJECT_ROOT_PATH . "/Model/Database.php";

class UserModel extends Database
{
    public  $username;
    public  $pwd;
    public  $employeeId;
    public  $status;

    function __construct($username,  $pwd)
    {
        $this->username = $username;
        $this->pwd = $pwd;
        $this->employeeId = "";
        $this->status = "";
    }
    public function getUsers($limit)
    {
        //return $this->select("SELECT * FROM users ORDER BY user_id ASC LIMIT ?", ["i", $limit]);
        return $this->select("SELECT * FROM employee");
    }
}
