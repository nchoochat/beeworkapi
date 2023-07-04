<?php
//require_once PROJECT_ROOT_PATH . "/Controller/DatabaseController.php";

class Customer
{
    
    function __construct(){

    }
   
    function get_list(){
        $filename = ROOT_PATH . "/Sql/GetCustomerList.sql";
        $array = explode("\n", file_get_contents($filename));
        $sql = implode(chr(10), $array);
        
        $database = new DatabaseController();
        return $database->execute($sql);
    }
}
