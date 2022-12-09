<?php
//require_once PROJECT_ROOT_PATH . "/Controller/DatabaseController.php";

class Job
{

    function __construct()
    {
    }

    function get_job_list($eId)
    {
        $filename = ROOT_PATH . "/Sql/GetJobList.sql";
        $array = explode("\n", file_get_contents($filename));
        $sql = str_replace(array('{0}'), array($eId), implode(chr(10), $array));
        
        $database = new DatabaseController();
        return $database->execute($sql);
    }

    function get_job_work()
    {
        $filename = ROOT_PATH . "/Sql/GetJobWork.sql";
        $array = explode("\n", file_get_contents($filename));
        $sql = (implode(chr(10), $array));

        $database = new DatabaseController();
        return $database->execute($sql);
    }

    function get_job_detail($jId)
    {
        $filename = ROOT_PATH . "/Sql/GetJobDetail.sql";
        $array = explode("\n", file_get_contents($filename));
        $sql = (implode(chr(10), $array));

        $database = new DatabaseController();
        return $database->execute(sprintf($sql, $$jId));
    }
    function get_job_detail_by_jobid_and_employeeid(String $jId, String $eId){
        $filename = ROOT_PATH . "/Sql/GetJobDetail_JobId_EmployeeId.sql";
        $array = explode("\n", file_get_contents($filename));
        $sql = str_replace(array('{0}', '{1}'), array($jId, $eId), implode(chr(10), $array));

        $database = new DatabaseController();
        return $database->execute($sql);
    }

    function get_notify_list($eId)
    {
        $filename = ROOT_PATH . "/Sql/GetNotifyList.sql";
        $array = explode("\n", file_get_contents($filename));
        $sql = str_replace(array('{0}'), array($eId), implode(chr(10), $array));

        $database = new DatabaseController();
        return $database->execute($sql);
    }
}
