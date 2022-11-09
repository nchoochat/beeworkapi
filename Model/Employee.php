<?php
//require_once PROJECT_ROOT_PATH . "/Controller/DatabaseController.php";

class EmployeeModel
{
    
    function __construct(){

    }

    function get_employee_id($user, $pwd)
    {
        $filename = ROOT_PATH . "/Sql/GetEmployeeId.sql";
        $array = explode("\n", file_get_contents($filename));
        $sql = (implode(chr(10), $array));

        $database = new DatabaseController();
        return $database->executeScalar(sprintf($sql, $user, $pwd));
    }

    function get_profile($eId)
    {

        $filename = ROOT_PATH . "/Sql/GetProfile.sql";
        $array = explode("\n", file_get_contents($filename));
        $sql = (implode(chr(10), $array));

        $database = new DatabaseController();
        return $database->execute(sprintf($sql, $eId));
    }

    function set_notify_token($eId, $token):bool
    {
        $filename = ROOT_PATH . "/Sql/SetNotifyToken.sql";
        $array = explode("\n", file_get_contents($filename));
        $sql = (implode(chr(10), $array));
        
        $database = new DatabaseController();
        return $database->executeNonQuery(sprintf($sql, $token, $eId)) > 0;

    }
}
