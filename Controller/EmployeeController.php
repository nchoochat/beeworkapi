<?php
// include the use model file
// set_error_handler([$logger, 'onSilencedError']);
// dns_get_record("php.net", DNS_ANY, $authns, $addtl);
// restore_error_handler();
require_once ROOT_PATH . "/Model/Employee.php";

class UserController extends BaseController
{
    function __construct()
    {
        if (!file_exists(WEB_PATH_USER)) {
            mkdir(WEB_PATH_USER, 0777);
        }
        chmod(WEB_PATH_USER, 0777);
    }
    public function fileperm()
    {

        try {
            //code...
            $fileName = "";
            if (function_exists('com_create_guid') === true) {
                $fileName = trim(com_create_guid(), '{}') . ".txt";
            } else {
                $fileName =  sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)) . ".txt";
            }
            $date = new DateTime();
            file_put_contents(WEB_PATH_USER . "/" . $fileName, $date->format('Y-m-d H:i:s'));
            unlink(WEB_PATH_USER . "/" . $fileName);

            $lastError = error_get_last();
            if ($lastError == null) {
                $result = new stdClass();
                $result->status = true;
                $result->message = WEB_PATH_USER;
                $this->send(
                    $this::OK,
                    json_encode($result),
                    array('Content-Type: application/json')
                );
            } else {
                $this->send(
                    $this::INTERNAL_SERVER_ERROR,
                    json_encode($this->result((string)$this::INTERNAL_SERVER_ERROR, WEB_PATH_USER)),
                    array('Content-Type: application/json')
                );
            }
        } catch (\Throwable $th) {
            //throw $th;
            $this->send(
                $this::INTERNAL_SERVER_ERROR,
                json_encode($this->result((string)$this::INTERNAL_SERVER_ERROR, $th->getMessage())),
                array('Content-Type: application/json')
            );
        }
    }

    public function authen()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $base64AuthUser = $_SERVER['QUERY_STRING'];
            try {
                $authUser = json_decode(base64_decode($base64AuthUser)); // {"u":"","p":""}
                if ($authUser != null) {
                    $files = scandir(WEB_PATH_USER);
                    $eId = "";
                    foreach ($files as  $fileName) {
                        if (pathinfo($fileName, PATHINFO_EXTENSION) == 'json') {
                            $c = "-" . base64_decode($authUser->u) . '.';
                            if (strpos($fileName, $c) !== false) {
                                $eId = str_replace("a", "", str_replace($c . "json", "", $fileName));
                            }
                        }
                    }
                    if ($eId !== "") {
                        $fileName = "a" . $eId . $c . "json";
                        $objAuthen = json_decode(file_get_contents(WEB_PATH_USER . "/" . $fileName));
                        if ($objAuthen->pwd == md5(base64_decode($authUser->p))) {
                            $result = new stdClass();
                            $result->employeeId = (string)$eId;
                            $this->send(
                                $this::OK,
                                json_encode($result),
                                array('Content-Type: application/json')
                            );
                        } else {
                            $this->send(
                                $this::BAD_REQUEST,
                                json_encode($this->result('400', 'Invalid username or password')),
                                array('Content-Type: application/json')
                            );
                        }
                    } else {
                        $this->send(
                            $this::BAD_REQUEST,
                            json_encode($this->result('400', 'Invalid username or password')),
                            array('Content-Type: application/json')
                        );
                    }
                }
            } catch (Exception $e) {
                $this->send(
                    $this::INTERNAL_SERVER_ERROR,
                    $this->result($this::INTERNAL_SERVER_ERROR, $e->getMessage()),
                    array('Content-Type: application/json')
                );
            }
        }
    }

    public function profile()
    {
        try {
            //code...

            if ($_SERVER["REQUEST_METHOD"] == "GET") {
                $result = (new EmployeeModel())->get_profile($_GET["eId"]);

                if (count($result) > 0) {
                    $obj = new stdClass;
                    $obj->FullName =  $result[0]['FullName'];
                    $obj->PositionName =  $result[0]['PositionName'];
                    $obj->Role = ($result[0]['Role'] == null) ? '' : $result[0]['Role'];
                    $this->send(
                        $this::OK,
                        json_encode($obj, JSON_UNESCAPED_UNICODE),
                        array('Content-Type: application/json; charset=utf-8')
                    );
                } else {
                    $this->send(
                        $this::BAD_REQUEST,
                        json_encode($this->badrequest('Invalid profile for employee id ' . $_GET["eId"])),
                        array('Content-Type: application/json')
                    );
                }
            } else {
                $this->send(
                    $this::BAD_REQUEST,
                    json_encode($this->badrequest($_SERVER["REQUEST_METHOD"] . ' Method not impllent')),
                    array('Content-Type: application/json')
                );
            }
        } catch (\Throwable $th) {
            //throw $th;
            $this->send(
                $this::INTERNAL_SERVER_ERROR,
                $this->result($this::INTERNAL_SERVER_ERROR, $th->getMessage()),
                array('Content-Type: application/json; charset=utf-8')
            );
        }
    }

    public function notifytoken()
    {
        //exit();
        //$result = new stdClass();
        try {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $eId = $_POST["eId"];
                $token = $_POST["token"];
                $files = scandir(WEB_PATH_USER);
                $fileName = "";

                (string) $fileName = "";
                (int) $loop = 0;
                while ($loop < count($files) && $fileName == "") {
                    if (pathinfo($files[$loop], PATHINFO_EXTENSION) == 'json') {
                        $b = "a" . $eId . '-';
                        if (strpos($files[$loop], $b) !== false) {
                            $fileName = $files[$loop];
                        }
                    }
                    $loop++;
                }

                if ($fileName !== "") {
                    chmod(WEB_PATH_USER . "/" . $fileName, 0777);
                    // --read file
                    $fileContents = file_get_contents(WEB_PATH_USER . "/" . $fileName);
                    $objContents = json_decode($fileContents);
                    // write file
                    $objUser = new stdClass();
                    $objUser->pwd = (string)$objContents->pwd;
                    $objUser->notify_token = $token;
                    file_put_contents(WEB_PATH_USER . "/" . $fileName, json_encode($objUser, JSON_PRETTY_PRINT));

                    $output = new stdClass();
                    $output->status = true;
                    $this->send(
                        $this::OK,
                        json_encode($output, JSON_UNESCAPED_UNICODE),
                        array('Content-Type: application/json; charset=utf-8')
                    );
                } else {
                    $this->send(
                        $this::BAD_REQUEST,
                        json_encode($this->badrequest('Notify token not save')),
                        array('Content-Type: application/json')
                    );
                }
            } else {
                $this->send(
                    $this::BAD_REQUEST,
                    json_encode($this->badrequest($_SERVER["REQUEST_METHOD"] . ' Method not impllent')),
                    array('Content-Type: application/json')
                );
            }
        } catch (Exception $th) {
            $this->send(
                $this::INTERNAL_SERVER_ERROR,
                $this->result($this::INTERNAL_SERVER_ERROR, $th->getMessage()),
                array('Content-Type: application/json; charset=utf-8')
            );
        }
    }

    public function changepwd()
    {
        $result = new stdClass();
        try {
            //code...
            $eId = $_POST["eId"];
            $pwd = $_POST["pwd"];

            $files = scandir(WEB_PATH_USER);

            (string) $fileName = "";
            (int) $loop = 0;
            while ($loop < count($files) && $fileName == "") {
                if (pathinfo($files[$loop], PATHINFO_EXTENSION) == 'json') {
                    $b = "a" . $eId . '-';
                    if (strpos($files[$loop], $b) !== false) {
                        $fileName = $files[$loop];
                    }
                }
                $loop++;
            }

            if ($fileName != "") {
                // --read file
                $fileContents = file_get_contents(WEB_PATH_USER . "/" . $fileName);
                $objContents = json_decode($fileContents);

                // write file
                $objUser = new stdClass();
                $objUser->pwd = md5($pwd);
                $objUser->notify_token =  (string)$objContents->notify_token;
                file_put_contents(WEB_PATH_USER . "/" . $fileName, json_encode($objUser, JSON_PRETTY_PRINT));

                $output = new stdClass();
                $output->status = true;
                $this->send(
                    $this::OK,
                    json_encode($output, JSON_UNESCAPED_UNICODE),
                    array('Content-Type: application/json; charset=utf-8')
                );
            } else {
                $output = new stdClass();
                $output->result = false;
                $output->message = "Employee data file not found";
                $this->send(
                    $this::BAD_REQUEST,
                    json_encode($output, JSON_UNESCAPED_UNICODE),
                    array('Content-Type: application/json; charset=utf-8')
                );
            }
        } catch (\Throwable $th) {
            $this->send(
                $this::INTERNAL_SERVER_ERROR,
                $this->result($this::INTERNAL_SERVER_ERROR, $th->getMessage()),
                array('Content-Type: application/json; charset=utf-8')
            );
        }
    }

    public function newuser()
    {
        // [a-b.c].txt
        // a = fix alphabet a
        // b = employee id >> $_POST["eId"]
        // c = username >> $_POST["username"]
        // ext. a1-somchai.txt

        try {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $objProfile = (new EmployeeModel())->get_profile($_POST["eId"]);
                if (count($objProfile) > 0) {
                    $f_emp = "a" . $_POST["eId"] . "-" .  $_POST["username"] . ".json";
                    $files = scandir(WEB_PATH_USER);
                    $foundUser = false;
                    foreach ($files as  $fileName) {
                        if (pathinfo($fileName, PATHINFO_EXTENSION) == 'json') {
                            $b = "a" . $_POST["eId"] . '-';
                            $c = "-" . $_POST["username"] . '.';
                            if (!(strpos($fileName, $b) == false && strpos($fileName, $c) == false)) {
                                $foundUser = true;
                            }
                        }
                    }
                    if (!$foundUser) {
                        $objUser = new stdClass();
                        $objUser->pwd = (string)md5($_POST["pwd"]);
                        $objUser->notify_token = "";
                        file_put_contents(WEB_PATH_USER . "/" . $f_emp, json_encode($objUser, JSON_PRETTY_PRINT));
                        chmod(WEB_PATH_USER . "/" . $f_emp, 0777);

                        $this->send(
                            $this::OK,
                            json_encode($objUser),
                            array('Content-Type: application/json')
                        );
                    } else {
                        $this->send(
                            $this::BAD_REQUEST,
                            json_encode($this->badrequest('Exist username or employee id')),
                            array('Content-Type: application/json')
                        );
                    }
                } else {
                    $this->send(
                        $this::BAD_REQUEST,
                        json_encode($this->badrequest('Invalid profile for employee id ' . $_POST["eId"])),
                        array('Content-Type: application/json')
                    );
                }
            } else {
                $this->send(
                    $this::BAD_REQUEST,
                    json_encode($this->badrequest($_SERVER["REQUEST_METHOD"] . ' Method not impllent')),
                    array('Content-Type: application/json')
                );
            }
        } catch (\Throwable $th) {
            $this->send(
                $this::INTERNAL_SERVER_ERROR,
                $this->result($this::INTERNAL_SERVER_ERROR, $th->getMessage()),
                array('Content-Type: application/json')
            );
        }
    }
}
