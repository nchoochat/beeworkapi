<?php

class Job
{

    function __construct()
    {
    }

    function get_list($eId)
    {
        $filename = ROOT_PATH . "/Sql/GetJobList.sql";
        $array = explode("\n", file_get_contents($filename));
        $sql = str_replace(array('{0}'), array($eId), implode(chr(10), $array));

        $database = new DatabaseController();
        return $database->execute($sql);
    }

    function get_history($eId, $cId, $sd, $ed, $js, $bNo)
    {
        $filename = ROOT_PATH . "/Sql/GetJobHistory.sql";
        $array = explode("\n", file_get_contents($filename));
        $sql = str_replace(array('{0}', '{1}', '{2}', '{3}', '{4}', '{5}'), array($eId, $cId, $sd, $ed, $js, $bNo), implode(chr(10), $array));

        $database = new DatabaseController();
        return $database->execute($sql);
    }

    function get_work()
    {
        $filename = ROOT_PATH . "/Sql/GetJobWork.sql";
        $array = explode("\n", file_get_contents($filename));
        $sql = (implode(chr(10), $array));

        $database = new DatabaseController();
        return $database->execute($sql);
    }

    function get_detail(String $jId)
    {
        $filename = ROOT_PATH . "/Sql/GetJobDetail.sql";
        $array = explode("\n", file_get_contents($filename));
        $sql = str_replace(array('{0}'), array($jId), implode(chr(10), $array));

        $database = new DatabaseController();
        return $database->execute($sql);
    }

    function get_detail_by_eId(String $jId, String $eId)
    {
        $filename = ROOT_PATH . "/Sql/GetJobDetailByEId.sql";
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
