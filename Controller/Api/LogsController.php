<?php

declare(strict_types=1);

class ImageController extends BaseController
{
    protected $_httpStatusCode = [
        "500" => "HTTP/1.1 500 Internal Server Error",
        "200" => "HTTP/1.1 200 OK",
    ];

    function __construct()
    {
        //echo $_SERVER["REQUEST_METHOD"];
    }

    function save(){
        $msg = $_POST["msg"];

         // -- Make Root Folder If Not Exist
         if (!file_exists(WEB_PATH_ATTACH_FILE)) {
            mkdir(WEB_PATH_ATTACH_FILE);
        }       

        //-- Meak File Information If Not Exist
        //if (!file_exists(WEB_PATH_ATTACH_FILE . "\\errors.txt")) {
            $filePath = WEB_PATH_ATTACH_FILE . "\\";
            $fileName = 'errors.txt';
            $filehandler = fopen("${filePath}\\${fileName}", 'w');
            //$contents = "notify_date=" . PHP_EOL . "accept_date=";
            fwrite($filehandler, $msg);
            fclose($filehandler);
        //}
    }
    
}
