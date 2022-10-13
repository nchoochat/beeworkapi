<?php

declare(strict_types=1);

class JobController extends BaseController
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

    public function list()
    {
        //http://localhost/hakao/index.php/job/jobList?eId=12345
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            try {
                $filename = $GLOBALS['dir_root'] . "/SQL/jobList.sql";

                $array = explode("\n", file_get_contents($filename));
                $sql = (implode(chr(10), $array));
                $database = new DatabaseController();
                $jobList = $database->execut(sprintf($sql, $_GET["eId"]));

                for ($i = 0; $i < count($jobList); $i++) {
                    $element = $jobList[$i];
                    $numOfAttachment = 0;
                    if (file_exists(WEB_PATH_ATTACH_FILE . "/" . $element["JobId"])) {
                        $files = scandir(WEB_PATH_ATTACH_FILE . "/" . $element["JobId"]);
                        foreach ($files as $fineIndex => $fileName) {
                            if (strpos($fileName, $_GET["eId"] . '_') !== false) {
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
                $filename = $GLOBALS['dir_root'] . "/SQL/jobWork.sql";

                $array = explode("\n", file_get_contents($filename));
                $sql = (implode(chr(10), $array));
                $database = new DatabaseController();
                $execList = $database->execut($sql);

                for ($i = 0; $i < count($execList); $i++) {
                    $el = $execList[$i];
                    $eId = $el["EmployeeId"];

                    $pendingWork = 0;
                    $pendingClose = 0;
                    $listOfJob = explode(',', $el['ListOfJob']);

                    for ($j = 0; $j < count($listOfJob); $j++) {
                        $jobId = $listOfJob[$j];
                        $foud = false;
                        if (file_exists(WEB_PATH_ATTACH_FILE . "/" . $jobId)) {
                            $files = scandir(WEB_PATH_ATTACH_FILE . "/" . $jobId);
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

                    $execList[$i]['PendingWork'] = $pendingWork;
                    $execList[$i]['PendingClose'] = $pendingClose;

                    $execList[0]['PendingWork'] += $pendingWork;
                    $execList[0]['PendingClose'] += $pendingClose;
                }
                $this->send(
                    json_encode(array_values($execList), JSON_UNESCAPED_UNICODE),
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
                $filename = $GLOBALS['dir_root'] . "/SQL/jobList.sql";
                $array = explode("\n", file_get_contents($filename));
                $sql = (implode(chr(10), $array));
                $database = new DatabaseController();
                $jobList = $database->execut(sprintf($sql, $_GET["eId"]));
                $date = new DateTime();
                for ($i = 0; $i < count($jobList); $i++) {
                    $el = $jobList[$i];
                    $this->prepareInfo($_GET["eId"], $el["JobId"], $el['UpdateDate']);
                    if (file_exists(WEB_PATH_ATTACH_FILE . "/" . $el["JobId"] . "/" . $_GET["eId"] . ".txt")) {
                        $filePath = WEB_PATH_ATTACH_FILE . "/" . $el["JobId"];
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

                // $jobList = array_filter($jobList, static function ($element) {
                //     $is_notify = false;
                //     if (file_exists(WEB_PATH_ATTACH_FILE . "/" . $element["JobId"] . "/" . $_GET["eId"] . ".txt")) {
                //         $filePath = WEB_PATH_ATTACH_FILE . "/" . $element["JobId"];
                //         $fileName = sprintf('%s.txt', $_GET["eId"]);
                //         $contentArray = file("${filePath}/${fileName}", FILE_IGNORE_NEW_LINES);
                //         if (count($contentArray) >= 2) {
                //             $notifyArray = explode("=", $contentArray[0]);
                //             if (count($notifyArray) >= 2) {
                //                 if ($notifyArray[0] == "notify_date" && $notifyArray[1] != "") {
                //                     $is_notify = true;
                //                 } else {
                //                     $date = new DateTime();
                //                     $notify = "notify_date=" . $date->format('Y-m-d H:i:s');
                //                     $filehandler = fopen("${filePath}/${fileName}", 'r+');
                //                     $contents = $notify . PHP_EOL . $contentArray[1];
                //                     fwrite($filehandler, $contents);
                //                     fclose($filehandler);
                //                 }
                //             }
                //         }
                //         return !$is_notify;
                //     } else {
                //         return false;
                //     }
                // });
                $this->send(
                    json_encode(array_values($jobList), JSON_UNESCAPED_UNICODE),
                    array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
                );
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }

    public function newList()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            try {
                $filename = $GLOBALS['dir_root'] . "/SQL/jobList.sql";
                $array = explode("\n", file_get_contents($filename));
                $sql = (implode(chr(10), $array));
                $database = new DatabaseController();
                $jobList = $database->execut(sprintf($sql, $_GET["eId"]));
                $date = new DateTime();
                for ($i = 0; $i < count($jobList); $i++) {
                    $el = $jobList[$i];
                    $this->prepareInfo($_GET["eId"], $el["JobId"], $el['UpdateDate']);
                    if (file_exists(WEB_PATH_ATTACH_FILE . "/" . $el["JobId"] . "/" . $_GET["eId"] . ".txt")) {
                        $filePath = WEB_PATH_ATTACH_FILE . "/" . $el["JobId"];
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
        $jId = $_GET["jId"];
        //$eId = $_GET["eId"];

        if (!file_exists(WEB_PATH_ATTACH_FILE . "/" . $jId)) {
            mkdir(WEB_PATH_ATTACH_FILE . "/" . $jId);
        }
        $date = new DateTime();
        if (file_exists(WEB_PATH_ATTACH_FILE . "/" . $_GET["jId"]) . "/" . $_GET["eId"] . ".txt") {
            $filePath = WEB_PATH_ATTACH_FILE . "/" . $_GET["jId"];
            $fileName = sprintf('%s.txt', $_GET["eId"]);
            $contentArray = file("${filePath}/${fileName}", FILE_IGNORE_NEW_LINES);
            if (count($contentArray) >= 2) {
                $notify = $contentArray[0];
                $accept = "accept_date=" . $date->format('Y-m-d H:i:s');
                $filePath = WEB_PATH_ATTACH_FILE . "/" . $_GET["jId"];
                $filehandler = fopen("${filePath}/${fileName}", 'r+');
                $contents = $notify . PHP_EOL . $accept;
                fwrite($filehandler, $contents);
                fclose($filehandler);
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
                $filename = $GLOBALS['dir_root'] . "/SQL/jobList.sql";
                $array = explode("\n", file_get_contents($filename));
                $sql = (implode(chr(10), $array));

                $database = new DatabaseController();
                $jobList = $database->execut(sprintf($sql, $_GET["eId"]));
                //$numOfTotal = 0;
                $numOfPendingWork = 0;
                $numOfPendingClose = 0;
                foreach ($jobList as $key => $element) {
                    $foud = false;
                    if (file_exists(WEB_PATH_ATTACH_FILE . "/" . $element["JobId"])) {
                        $files = scandir(WEB_PATH_ATTACH_FILE . "/" . $element["JobId"]);
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

                $filename = $GLOBALS['dir_root'] . "\SQL\jobDetail.sql";

                $array = explode("\n", file_get_contents($filename));
                $sql = (implode(chr(10), $array));
                $database = new DatabaseController();
                $jobDetail = $database->execut(sprintf($sql, $_GET["jId"]));
                $this->send(
                    json_encode(array_values($jobDetail), JSON_UNESCAPED_UNICODE),
                    array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
                );
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }

    public function listOfAttachFile()
    {
        $jId = $_GET["jId"];
        $eId = $_GET["eId"];
        $litOfAttachFile = array();
        if (file_exists(WEB_PATH_ATTACH_FILE . "/" . $jId)) {
            $files = scandir(WEB_PATH_ATTACH_FILE . "/" . $jId);
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

    public function uploadFile()
    {
        $jId = $_GET["jId"];
        $eId = $_GET["eId"];

        if (isset($_POST["image"])) {
            if (!file_exists(WEB_PATH_ATTACH_FILE . "/" . $jId)) {
                mkdir(WEB_PATH_ATTACH_FILE . "/" . $jId);
            }
            $date = new DateTime();
            $filePath = WEB_PATH_ATTACH_FILE . "/" . $jId;
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

    public function removeFile()
    {
        $jId = $_GET["jId"];
        $fId = $_GET["fId"];
        try {
            if (file_exists(WEB_PATH_ATTACH_FILE . "/${jId}/${fId}")) {
                unlink(WEB_PATH_ATTACH_FILE . "/${jId}/${fId}");
            }
            $this->send(
                json_encode((object) ['status' => 'success', 'fileName' => $fId], JSON_UNESCAPED_UNICODE),
                array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
            );
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function noImage()
    {
        $jId = $_POST["jId"];
        $eId = $_POST["eId"];
        $msg = $_POST["msg"];

        if (!file_exists(WEB_PATH_ATTACH_FILE . "/" . $jId)) {
            mkdir(WEB_PATH_ATTACH_FILE . "/" . $jId);
        }
        $date = new DateTime();
        $filePath = WEB_PATH_ATTACH_FILE . "/" . $jId;
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
        $font = dirname(__FILE__) . '\tahoma.ttf'; //'tahoma.ttf';

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
