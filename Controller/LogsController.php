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
        if (!file_exists(ROOT_PATH . '/Data/Logs')) {
            mkdir(ROOT_PATH . '/Data/Logs', 0777);
            chmod(ROOT_PATH . '/Data/Logs', 0777);
        }
        $date = new DateTime();
        $filePath = ROOT_PATH . '/Data/Logs';
        $fileName = 'log-' . $date->format('Y-m-d') . '.txt';
        $filehandler = fopen("{$filePath}/{$fileName}", 'a+');
        fwrite($filehandler, str_replace(array('{0}', '{1}', '{2}'), array($date->format('Y-m-d H:i:s.ms'), $_POST['title'], $_POST['detail']), "[{0}]{1}: {2}" . PHP_EOL));
        fclose($filehandler);
    }

    function write($type, $msg)
    {
        if (!file_exists(ROOT_PATH . '/Data/Logs')) {
            mkdir(ROOT_PATH . '/Data/Logs', 0777);
            chmod(ROOT_PATH . '/Data/Logs', 0777);
        }

        $date = new DateTime();
        $filePath = ROOT_PATH . '/Data/Logs';
        $fileName = 'log-' . $date->format('Y-m-d') . '.txt';
        $filehandler = fopen("{$filePath}/{$fileName}", 'a+');
        fwrite($filehandler, str_replace(array('{0}', '{1}', '{2}'), array($date->format('Y-m-d H:i:s.ms'), $type, $msg), "[{0}]{1}: {2}" . PHP_EOL));
        fclose($filehandler);
    }
}
