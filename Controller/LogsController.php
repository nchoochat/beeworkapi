<?php

declare(strict_types=1);

class LogsController extends BaseController
{
    protected $_httpStatusCode = [
        "500" => "HTTP/1.1 500 Internal Server Error",
        "200" => "HTTP/1.1 200 OK",
    ];

    function __construct()
    {
        //echo $_SERVER["REQUEST_METHOD"];
    }
    function error()
    {
    }
    function save()
    {
        if (!file_exists(WEB_PATH_ATTACH_FILE)) {
            mkdir(WEB_PATH_ATTACH_FILE);
        }
        $date = new DateTime();
        $filePath = WEB_PATH_ATTACH_FILE . "\\";
        $fileName = 'log-' . $date->format('Y-m-d') . '.txt';
        $filehandler = fopen("${filePath}\\${fileName}", 'a+');
        fwrite($filehandler, str_replace(array('{0}', '{1}', '{2}'), array($date->format('Y-m-d H:i:s'), $_POST['title'], $_POST['detail']), "[{0}]{1}:{2}" . PHP_EOL));
        fclose($filehandler);
    }

    // function save()
    // {
    //     $msg = $_POST["msg"];

    //     // -- Make Root Folder If Not Exist
    //     if (!file_exists(WEB_PATH_ATTACH_FILE)) {
    //         mkdir(WEB_PATH_ATTACH_FILE);
    //     }

    //     //-- Meak File Information If Not Exist
    //     //if (!file_exists(WEB_PATH_ATTACH_FILE . "\\errors.txt")) {
    //     $filePath = WEB_PATH_ATTACH_FILE . "\\";
    //     $fileName = 'errors.txt';
    //     $filehandler = fopen("${filePath}\\${fileName}", 'w');
    //     //$contents = "notify_date=" . PHP_EOL . "accept_date=";
    //     fwrite($filehandler, $msg);
    //     fclose($filehandler);
    //     //}
    // }
}
