<?php

declare(strict_types=1);

class LogsController extends BaseController
{
    function __construct()
    {
        //echo $_SERVER["REQUEST_METHOD"];
    }
    function error()
    {
        
    }
    function save()
    {
        if (!file_exists(WEB_PATH_PHOTO)) {
            mkdir(WEB_PATH_PHOTO);
        }
        $date = new DateTime();
        $filePath = WEB_PATH_PHOTO . "\\";
        $fileName = 'log-' . $date->format('Y-m-d') . '.txt';
        $filehandler = fopen("${filePath}\\${fileName}", 'a+');
        fwrite($filehandler, str_replace(array('{0}', '{1}', '{2}'), array($date->format('Y-m-d H:i:s'), $_POST['title'], $_POST['detail']), "[{0}]{1}:{2}" . PHP_EOL));
        fclose($filehandler);
    }

    function write($msg)
    {
        if (!file_exists(WEB_PATH_USER)) {
            mkdir(WEB_PATH_USER);
        }
        $date = new DateTime();
        $filePath = WEB_PATH_USER . "\\";
        $fileName = 'log-' . $date->format('Y-m-d') . '.txt';
        $filehandler = fopen("${filePath}\\${fileName}", 'a+');
        fwrite($filehandler, str_replace(array('{0}', '{1}', '{2}'), array($date->format('Y-m-d H:i:s'), 'internal error', $msg), "[{0}]{1}:{2}" . PHP_EOL));
        fclose($filehandler);
    }
}
