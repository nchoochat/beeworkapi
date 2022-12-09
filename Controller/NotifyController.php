<?php

declare(strict_types=1);
require_once ROOT_PATH . "/Model/Job.php";

class NotifyController extends BaseController
{

    function __construct()
    {
        //echo $_SERVER["REQUEST_METHOD"];
    }

    static function prepareInfo($eId, $jId, $lastUpdte)
    {
        // -- Make Root Folder If Not Exist
        if (!file_exists(WEB_PATH_PHOTO)) {
            mkdir(WEB_PATH_PHOTO);
        }

        // -- Make Job Folder If Not Exist
        if (!file_exists(WEB_PATH_PHOTO . "/" . $jId)) {
            mkdir(WEB_PATH_PHOTO . "/" . $jId, 0777);
            chmod(WEB_PATH_PHOTO . "/" . $jId, 0777);
        }

        //-- Meak File Information If Not Exist
        if (!file_exists(WEB_PATH_PHOTO . "/" . $jId . "/" . $eId . ".txt")) {
            $jobInfo = new stdClass();
            $jobInfo->notify_date = "";
            $jobInfo->accept_date = "";
            $jobInfo->update_date = $lastUpdte;

            $filePath = WEB_PATH_PHOTO . "/" . $jId;
            $fileName = sprintf('%s.txt', $eId);
            file_put_contents($filePath . "/" . $fileName, json_encode($jobInfo, JSON_PRETTY_PRINT));
        }
    }

    function job()
    {
        try {

            $jobList = (new Job)->get_notify_list('all');
            $date = new DateTime();
            print_r("Notify Start >>>>>" . PHP_EOL);
            //print_r($jobList);
            for ($i = 0; $i < count($jobList); $i++) {
                $el = $jobList[$i];
                try {
                    $files = scandir(WEB_PATH_USER);
                    $fileName = "";
                    $found = false;
                    foreach ($files as  $fileName) {
                        if (pathinfo($fileName, PATHINFO_EXTENSION) == 'json') {
                            $b = "a" . $el["EmployeeId"] . '-';
                            if (strpos($fileName, $b) !== false) {
                                $found = true;
                                break;
                            }
                        }
                    }
                    if ($found) {
                        $objContent = json_decode(file_get_contents(WEB_PATH_USER . "/" . $fileName));
                        $el["NotifyToken"] = $objContent->notify_token;
                    }
                    if ($el["NotifyToken"] != "") {
                        $this->prepareInfo($el["EmployeeId"], $el["JobId"], $el['UpdateDate']);
                        if (file_exists(WEB_PATH_PHOTO . "/" . $el["JobId"] . "/" . $el["EmployeeId"] . ".txt")) {
                            $filePath = WEB_PATH_PHOTO . "/" . $el["JobId"];
                            $fileName = sprintf('%s.txt', $el["EmployeeId"],);
                            //$content = file("${filePath}/${fileName}", FILE_IGNORE_NEW_LINES);
                            $content = file_get_contents("${filePath}/${fileName}");
                            $notifyType = "";
                            if ($content) {
                                $jobInfo = json_decode($content);
                                if ($jobInfo->notify_date == "") {
                                    //-- New Job
                                    $notifyType = "งานใหม่";
                                } else if (strtotime($jobInfo->notify_date) <= strtotime($el["UpdateDate"])) {
                                    // -- Update Job
                                    $notifyType = "งานอัพเดท";
                                }
                                if ($notifyType  != "") {
                                    $url = 'https://fcm.googleapis.com/fcm/send';

                                    $header = "";
                                    $header = $header . "Content-Type: application/json\r\n";
                                    $header = $header . "Authorization: key=AAAAI4Uphw4:APA91bGKLfMRuJZvbdNCWjVxsnDzLjSAJi93_KYsyhAGZ5VlumSyJFT3ooQZWoJD4sHdu7up_YbKEGkpE0suS3fp35Sqn07U77IiVS1D4s4n9HwzNWO6NedhIq_zaveTesN0zUxxO9LJ";

                                    $fcm = new stdClass();
                                    $notification = new stdClass();
                                    $notification->title = $notifyType;
                                    $notification->body = $el["CustomerName"] . ": \n" . $el["Description"];
                                    $fcm->to = $el["NotifyToken"];
                                    $fcm->notification = $notification;
                                    $options = array(
                                        'http' => array(
                                            'header'  => $header,
                                            'method'  => 'POST',
                                            'content' => json_encode($fcm, JSON_UNESCAPED_UNICODE)
                                        )
                                    );
                                    $context  = stream_context_create($options);
                                    $result = file_get_contents($url, false, $context);
                                    if ($result) {
                                        $jobInfo->notify_date = date_format($date, 'Y-m-d H:i:s');
                                    }
                                    sleep(1); // Wait 1 second 
                                    $jobInfo->update_date = $el["UpdateDate"];
                                    file_put_contents($filePath . "/" . $fileName, json_encode($jobInfo, JSON_PRETTY_PRINT));
                                    print_r($el["JobId"] . ',' . $el["EmployeeId"] . " => " . $result . PHP_EOL);
                                }
                            } else {
                                print_r($el["JobId"] . ',' . $el["EmployeeId"] . " => " . "Notify tokey not found" . PHP_EOL);
                            }
                        } else {
                            print_r($el["JobId"] . ',' . $el["EmployeeId"] . " => " . "Notify tokey not found" . PHP_EOL);
                        }
                    } else {
                        print_r($el["JobId"] . ',' . $el["EmployeeId"] . " => " . "Notify tokey not found" . PHP_EOL);
                    }
                } catch (\Throwable $th) {
                    throw $th;
                }
            }
            print_r("Notify END <<<<<<" . PHP_EOL);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
