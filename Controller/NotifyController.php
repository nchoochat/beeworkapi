<?php

class NotifyController extends BaseController
{
    protected $_httpStatusCode = [
        "500" => "HTTP/1.1 500 Internal Server Error",
        "200" => "HTTP/1.1 200 OK",
    ];

    function __construct()
    {
        //echo $_SERVER["REQUEST_METHOD"];
    }

    static function prepareInfo($eId, $jId, $lastUpdte)
    {
        // -- Make Root Folder If Not Exist
        if (!file_exists(WEB_PATH_ATTACH_FILE)) {
            mkdir(WEB_PATH_ATTACH_FILE);
        }

        // -- Make Job Folder If Not Exist
        if (!file_exists(WEB_PATH_ATTACH_FILE . "\\" . $jId)) {
            mkdir(WEB_PATH_ATTACH_FILE . "\\" . $jId);
        }

        //-- Meak File Information If Not Exist
        if (!file_exists(WEB_PATH_ATTACH_FILE . "\\" . $jId . "\\" . $eId . ".txt")) {
            $jobInfo = new stdClass();
            $jobInfo->notify_date = "";
            $jobInfo->accept_date = "";
            $jobInfo->update_date = $lastUpdte;

            $filePath = WEB_PATH_ATTACH_FILE . "\\" . $jId;
            $fileName = sprintf('%s.txt', $eId);
            $filehandler = fopen("${filePath}\\${fileName}", 'w');
            $contents = json_encode($jobInfo);
            fwrite($filehandler, $contents);
            fclose($filehandler);
        }
    }

    function job()
    {
        try {
            $filename = $GLOBALS['dir_root'] . "/SQL/NotifyList.sql";
            $array = explode("\n", file_get_contents($filename));
            $sql = (implode(chr(10), $array));
            $database = new DatabaseController();
            $jobList = $database->execut($sql);
            $date = new DateTime();
            //print_r($date);
            for ($i = 0; $i < count($jobList); $i++) {
                $el = $jobList[$i];
                $this->prepareInfo($el["EmployeeId"], $el["JobId"], $el['UpdateDate']);
                if (file_exists(WEB_PATH_ATTACH_FILE . "/" . $el["JobId"] . "/" . $el["EmployeeId"] . ".txt")) {
                    $filePath = WEB_PATH_ATTACH_FILE . "/" . $el["JobId"];
                    $fileName = sprintf('%s.txt', $el["EmployeeId"],);
                    //$content = file("${filePath}/${fileName}", FILE_IGNORE_NEW_LINES);
                    $content = file_get_contents("${filePath}/${fileName}");
                    
                    if ($content) {
                        $jobInfo = json_decode($content);
                        if ($jobInfo->notify_date == "") {
                            //-- New Job
                            $el["NotifyType"] = "New Job";
                        } else if (strtotime($jobInfo->notify_date) <= strtotime($el["UpdateDate"])) {                            
                            // -- Update Job
                            $el["NotifyType"] = "Update Job";
                        }
                        if ($el["NotifyType"] != "") {
                            $url = 'https://fcm.googleapis.com/fcm/send';

                            $header = "";
                            $header = $header . "Content-Type: application/json\r\n";
                            $header = $header . "Authorization: key=AAAAI4Uphw4:APA91bGKLfMRuJZvbdNCWjVxsnDzLjSAJi93_KYsyhAGZ5VlumSyJFT3ooQZWoJD4sHdu7up_YbKEGkpE0suS3fp35Sqn07U77IiVS1D4s4n9HwzNWO6NedhIq_zaveTesN0zUxxO9LJ";

                            //$data = array('key1' => 'value1', 'key2' => 'value2');
                            $fcm = new stdClass();
                            $notification = new stdClass();
                            $notification->title = $el["NotifyType"];
                            $notification->body = $el["CustomerName"] . "\n" . $el["Description"];
                            $fcm->to = $el["NotifyToken"];
                            $fcm->notification = $notification;
                            $options = array(
                                'http' => array(
                                    'header'  => $header,
                                    'method'  => 'POST',
                                    'content' => json_encode($fcm)
                                )
                            );
                            $context  = stream_context_create($options);
                            $result = file_get_contents($url, false, $context);
                            if($result){
                                $jobInfo->notify_date = date_format($date, 'Y-m-d H:i:s');
                            }
                            print_r($result . PHP_EOL);
                            sleep(1); // Wait 1 second 
                        }
                        $jobInfo->update_date = $el["UpdateDate"];
                        $filehandler = fopen("${filePath}\\${fileName}", 'w');
                        $contents = json_encode($jobInfo);
                        fwrite($filehandler, $contents);
                        fclose($filehandler);                        
                    }
                }
            }

            // $jobList = array_filter($jobList, function ($element, $index) {
            //     return $element['NotifyDate'] == '';
            // }, ARRAY_FILTER_USE_BOTH);

            // $this->send(
            //     json_encode(array_values($jobList), JSON_UNESCAPED_UNICODE),
            //     array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
            // );

        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
