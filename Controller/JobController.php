<?php

declare(strict_types=1);
require_once ROOT_PATH . "/Model/Job.php";
class JobController extends BaseController
{
    protected $_httpStatusCode = [
        "500" => "HTTP/1.1 500 Internal Server Error",
        "200" => "HTTP/1.1 200 OK",
    ];

    function __construct()
    {
        if (!file_exists(WEB_PATH_PHOTO)) {
            mkdir(WEB_PATH_PHOTO, 0777);
        }
        chmod(WEB_PATH_PHOTO, 0777);
    }

    static function prepareInfo($eId, $jId)
    {
        // -- Make Job Folder If Not Exist
        if (!file_exists(WEB_PATH_PHOTO . "/" . $jId)) {
            mkdir(WEB_PATH_PHOTO . "/" . $jId, 0777);
        }
        chmod(WEB_PATH_PHOTO . "/" . $jId, 0777);

        //-- Meak File Information If Not Exist
        $filePath = WEB_PATH_PHOTO . "/" . $jId;
        $fileName = sprintf('%s.txt', $eId);
        if (!file_exists(WEB_PATH_PHOTO . "/" . $jId . "/" . $eId . ".txt")) {
            $jobInfo = new stdClass();
            $jobInfo->notify_date = "";
            $jobInfo->accept_date = "";
            $jobInfo->update_date = "";
            file_put_contents("${filePath}/${fileName}", json_encode($jobInfo, JSON_PRETTY_PRINT));
        }
        chmod("${filePath}/${fileName}", 0777);
    }

    public function list()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            try {

                $jobList = (new Job)->get_job_list($_GET["eId"]);
                for ($i = 0; $i < count($jobList); $i++) {
                    $element = $jobList[$i];
                    $numOfAttachment = 0;
                    if (file_exists(WEB_PATH_PHOTO . "/" . $element["JobId"])) {
                        $files = scandir(WEB_PATH_PHOTO . "/" . $element["JobId"]);
                        foreach ($files as $fileName) {
                            if (strpos($fileName, $element["EmployeeId"] . '_') !== false) {
                                $numOfAttachment += 1;
                            }
                        }
                    }
                    $jobList[$i]['NumOfAttachment'] = $numOfAttachment;
                }

                $this->send(
                    json_encode(array_values($jobList), JSON_UNESCAPED_UNICODE),
                    array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
                );
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }

    public function work()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            try {

                $jobList = (new Job)->get_job_work();
                for ($i = 0; $i < count($jobList); $i++) {
                    $el = $jobList[$i];
                    $eId = $el["EmployeeId"];

                    $pendingWork = 0;
                    $pendingClose = 0;
                    $listOfJob = explode(',', $el['ListOfJob']);

                    for ($j = 0; $j < count($listOfJob); $j++) {
                        $jobId = $listOfJob[$j];
                        $foud = false;
                        if (file_exists(WEB_PATH_PHOTO . "/" . $jobId)) {
                            $files = scandir(WEB_PATH_PHOTO . "/" . $jobId);
                            foreach ($files as $fineIndex => $fileName) {
                                if (strpos($fileName, $eId . '_') !== false) {
                                    $foud = true;
                                }
                            }
                        }
                        if ($foud)
                            $pendingClose += 1;
                        else
                            $pendingWork += 1;
                    }

                    $jobList[$i]['PendingWork'] = $pendingWork;
                    $jobList[$i]['PendingClose'] = $pendingClose;

                    $jobList[0]['PendingWork'] += $pendingWork;
                    $jobList[0]['PendingClose'] += $pendingClose;
                }
                $this->send(
                    json_encode(array_values($jobList), JSON_UNESCAPED_UNICODE),
                    array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
                );
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }
    public function notifyList()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            try {
                $date = new DateTime();
                $jobList = (new Job)->get_job_list($_GET["eId"]);
                for ($i = 0; $i < count($jobList); $i++) {
                    $el = $jobList[$i];
                    $this->prepareInfo($_GET["eId"], $el["JobId"]);
                    if (file_exists(WEB_PATH_PHOTO . "/" . $el["JobId"] . "/" . $_GET["eId"] . ".txt")) {
                        $filePath = WEB_PATH_PHOTO . "/" . $el["JobId"];
                        $fileName = sprintf('%s.txt', $_GET["eId"]);
                        $contentArray = file("${filePath}/${fileName}", FILE_IGNORE_NEW_LINES);
                        if (count($contentArray) >= 2) {
                            $notify = explode("=", $contentArray[0]);
                            $accept = explode("=", $contentArray[1]);
                            $jobList[$i]['NotifyDate'] = $notify[1];
                            $jobList[$i]['AcceptDate'] = $accept[1];
                        }
                        if ($jobList[$i]['NotifyDate'] == '') {
                            $date = new DateTime();
                            $notify = "notify_date=" . $date->format('Y-m-d H:i:s');
                            $filehandler = fopen("${filePath}/${fileName}", 'r+');
                            $contents = $notify . PHP_EOL . $contentArray[1];
                            fwrite($filehandler, $contents);
                            fclose($filehandler);
                        }
                    }
                }

                $jobList = array_filter($jobList, function ($element, $index) {
                    return $element['NotifyDate'] == '';
                }, ARRAY_FILTER_USE_BOTH);

                $this->send(
                    json_encode(array_values($jobList), JSON_UNESCAPED_UNICODE),
                    array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
                );
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }

    public function sendnotify()
    {
        try {

            $jobList = (new Job)->get_notify_list($_GET["eId"]);

            $date = new DateTime();
            for ($i = 0; $i < count($jobList); $i++) {
                $el = $jobList[$i];
                // Get notify token
                try {
                    // $eId = $_POST["eId"];
                    // $token = $_POST["token"];
                    $files = scandir(WEB_PATH_USER);
                    $fileName = "";
                    $found = false;
                    foreach ($files as  $fileName) {
                        if (pathinfo($fileName, PATHINFO_EXTENSION) == 'json') {
                            $b = "a" . $_GET["eId"] . '-';
                            if (strpos($fileName, $b) !== false) {
                                $found = true;
                            }
                        }
                    }
                    if ($found) {
                        $objContent = json_decode(file_get_contents(WEB_PATH_USER . "/" . $fileName));
                        $el["NotifyToken"] = $objContent->notify_token;
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }

                $this->prepareInfo($el["EmployeeId"], $el["JobId"]);
                if (file_exists(WEB_PATH_PHOTO . "/" . $el["JobId"] . "/" . $el["EmployeeId"] . ".txt")) {
                    $filePath = WEB_PATH_PHOTO . "/" . $el["JobId"];
                    $fileName = sprintf('%s.txt', $el["EmployeeId"],);
                    //$content = file("${filePath}/${fileName}", FILE_IGNORE_NEW_LINES);
                    $content = file_get_contents("${filePath}/${fileName}");

                    if ($content) {
                        $jobInfo = json_decode($content);
                        if ($jobInfo->notify_date == "") {
                            //-- New Job
                            $el["NotifyType"] = "New Job";
                        } else if ($jobInfo->notify_date <= $el["UpdateDate"]) {
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
                            if ($result) {
                                $jobInfo->notify_date = $date->format('Y-m-d H:i:s');
                            }
                            print_r($result . "</br >");
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

    public function new()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            try {
                $jobList = (new Job)->get_job_list($_GET["eId"]);
                for ($i = 0; $i < count($jobList); $i++) {
                    $el = $jobList[$i];
                    $this->prepareInfo($_GET["eId"], $el["JobId"]);
                    if (file_exists(WEB_PATH_PHOTO . "/" . $el["JobId"] . "/" . $_GET["eId"] . ".txt")) {
                        $filePath = WEB_PATH_PHOTO . "/" . $el["JobId"];
                        $fileName = sprintf('%s.txt', $_GET["eId"]);
                        $content = file_get_contents("${filePath}/${fileName}");
                        if ($content) {
                            $jsonObj = json_decode($content);
                            $jobList[$i]['NotifyDate'] = $jsonObj->notify_date;
                            $jobList[$i]['AcceptDate'] = $jsonObj->accept_date;
                            $jobList[$i]['UpdateDate'] = $jsonObj->update_date;
                        }
                    }
                }
                $jobList = array_filter($jobList, function ($element, $index) {
                    return $element['AcceptDate'] == '';
                }, ARRAY_FILTER_USE_BOTH);
                $this->send(
                    json_encode(array_values($jobList), JSON_UNESCAPED_UNICODE),
                    array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
                );
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }

    public function accept()
    {
        // if (!file_exists(WEB_PATH_PHOTO . "/" . $_GET["jId"])) {
        //     mkdir(WEB_PATH_PHOTO . "/" . $_GET["jId"]);
        // }
        $this->prepareInfo($_GET["eId"], $_GET["jId"]);
        $date = new DateTime();
        if (file_exists(WEB_PATH_PHOTO . "/" . $_GET["jId"]) . "/" . $_GET["eId"] . ".txt") {
            $filePath = WEB_PATH_PHOTO . "/" . $_GET["jId"];
            $fileName = sprintf('%s.txt', $_GET["eId"]);
            $content = file_get_contents("${filePath}/${fileName}");
            if ($content) {
                $jobInfo = json_decode($content);
                $jobInfo->accept_date = $date->format('Y-m-d H:i:s');
                //$filehandler = fopen("${filePath}\\${fileName}", 'w');
                $contents = json_encode($jobInfo, JSON_PRETTY_PRINT);
                //print_r($contents);
                file_put_contents($filePath . "/" . $fileName, $contents);
                //fwrite($filehandler, $contents);
                //fclose($filehandler);
            }
        }
        $this->send(
            json_encode((object) ['status' => 'success', 'fileName' => $fileName], JSON_UNESCAPED_UNICODE),
            array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
        );
    }

    public function pending()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            try {

                $numOfPendingWork = 0;
                $numOfPendingClose = 0;

                $jobList = (new Job)->get_job_list($_GET["eId"]);
                foreach ($jobList as $key => $element) {
                    $foud = false;
                    if (file_exists(WEB_PATH_PHOTO . "/" . $element["JobId"])) {
                        $files = scandir(WEB_PATH_PHOTO . "/" . $element["JobId"]);
                        foreach ($files as $fineIndex => $fileName) {
                            if (strpos($fileName, $_GET["eId"] . '_') !== false) {
                                $foud = true;
                            }
                        }
                    }
                    if ($foud)
                        $numOfPendingClose =  $numOfPendingClose + 1;
                    else
                        $numOfPendingWork = $numOfPendingWork + 1;
                }
                $responst = (object) [
                    'numOfPendingWork' => $numOfPendingWork,
                    'numOfPendingClose' => $numOfPendingClose,
                    'numOfTotal' => $numOfPendingWork + $numOfPendingClose
                ];
                $this->send(
                    json_encode($responst, JSON_UNESCAPED_UNICODE),
                    array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
                );
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }

    public function detail()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            try {
                $jobDetail = (new Job)->get_job_list($_GET["jId"]);
                $this->send(
                    json_encode(array_values($jobDetail), JSON_UNESCAPED_UNICODE),
                    array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
                );
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }

    public function attachfilelist()
    {
        $jId = $_GET["jId"];
        $eId = $_GET["eId"];
        $litOfAttachFile = array();
        if (file_exists(WEB_PATH_PHOTO . "/" . $jId)) {
            $files = scandir(WEB_PATH_PHOTO . "/" . $jId);
            foreach ($files as $fineIndex => $fileName) {
                if (pathinfo($fileName, PATHINFO_EXTENSION) != 'txt') {
                    if (strpos($fileName, $eId . '_') !== false) {
                        array_push($litOfAttachFile, $fileName);
                    }
                }
            }
        }
        $this->send(
            json_encode($litOfAttachFile, JSON_UNESCAPED_UNICODE),
            array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
        );
    }

    public function uploadfile()
    {
        $jId = $_GET["jId"];
        $eId = $_GET["eId"];

        if (isset($_POST["image"])) {
            if (!file_exists(WEB_PATH_PHOTO . "/" . $jId)) {
                mkdir(WEB_PATH_PHOTO . "/" . $jId);
            }
            $date = new DateTime();
            $filePath = WEB_PATH_PHOTO . "/" . $jId;
            $fileName = sprintf('%s_%s', $eId, $date->format('YmdHis'));
            $base64_string = $_POST["image"];
            $filehandler = fopen("${filePath}/${fileName}", 'wb');
            fwrite($filehandler, base64_decode($base64_string));
            fclose($filehandler);
            $mimeFile = (mime_content_type("${filePath}/${fileName}"));
            switch ($mimeFile) {
                case 'image/png':
                    rename("${filePath}/${fileName}", "${filePath}/${fileName}.png");
                    break;
                case 'image/jpeg':
                    rename("${filePath}/${fileName}", "${filePath}/${fileName}.jpg");
                    break;
                default:
                    rename("${filePath}/${fileName}", "${filePath}/${fileName}.tmp");
            }

            $this->send(
                json_encode((object) ['status' => 'success', 'fileName' => $fileName], JSON_UNESCAPED_UNICODE),
                array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
            );
        } else {
            $this->send(
                json_encode((object) ['status' => 'failed', 'fileName' => ""], JSON_UNESCAPED_UNICODE),
                array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
            );
        }
    }

    public function removefile()
    {
        $jId = $_GET["jId"];
        $fId = $_GET["fId"];
        try {
            if (file_exists(WEB_PATH_PHOTO . "/${jId}/${fId}")) {
                unlink(WEB_PATH_PHOTO . "/${jId}/${fId}");
            }
            $this->send(
                json_encode((object) ['status' => 'success', 'fileName' => $fId], JSON_UNESCAPED_UNICODE),
                array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
            );
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function noimage()
    {
        //print_r('xxx');
        $jId = $_POST["jId"];
        $eId = $_POST["eId"];
        $msg = $_POST["msg"];


        if (!file_exists(WEB_PATH_PHOTO . "/" . $jId)) {
            mkdir(WEB_PATH_PHOTO . "/" . $jId, 0777);
            chmod(WEB_PATH_PHOTO . "/" . $jId, 0777);
        }

        $date = new DateTime();
        $filePath = WEB_PATH_PHOTO . "/" . $jId;
        $fileName = sprintf('%s_noimage_%s.png', $eId, $date->format('YmdHis'));
        // Set the content-type
        header('Content-Type: image/png');

        // Create the image
        $height = 512;
        $width = 512;
        $font_size = 15;
        $im = imagecreatetruecolor($height, $width);

        // Create some colors
        $white = imagecolorallocate($im, 255, 255, 255);
        //$grey = imagecolorallocate($im, 128, 128, 128);
        $black = imagecolorallocate($im, 0, 0, 0);
        imagefilledrectangle($im, 0, 0, $height - 1, $width - 1, $white);

        // The text to draw

        // Replace path by your own font path
        $font = dirname(__FILE__) . '/tahoma.ttf'; //'tahoma.ttf';

        $text_bbox = imagettfbbox($font_size, 0, $font, $msg);
        $image_centerx = $width / 2;
        $image_centery = $height / 2;
        $text_x = $image_centerx - round(($text_bbox[4] / 2));
        $text_y = $image_centery;

        // Add the text
        imagettftext($im, $font_size, 0, intval($text_x), intval($text_y), $black, $font, $msg);

        // Using imagepng() results in clearer text compared with imagejpeg()
        imagepng($im, "${filePath}/${fileName}");
        imagedestroy($im);

        header_remove('Content-Type');
        $this->send(
            json_encode((object) ['status' => 'success', 'fileName' => $fileName], JSON_UNESCAPED_UNICODE),
            array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
        );
    }
}
