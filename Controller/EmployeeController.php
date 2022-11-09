<?php
// include the use model file

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
                                json_encode($result),
                                array('Content-Type: application/json', $this->_httpStatusCode["200"])
                            );
                        } else {
                            $this->send(
                                json_encode($this->result('400', 'Invalid username or password')),
                                array('Content-Type: application/json', $this->_httpStatusCode["400"])
                            );
                        }
                    } else {
                        $this->send(
                            json_encode($this->result('400', 'Invalid username or password')),
                            array('Content-Type: application/json', $this->_httpStatusCode["400"])
                        );
                    }
                }
            } catch (Exception $e) {
                $this->send(
                    $e,
                    array('Content-Type: application/json', $this->_httpStatusCode["500"])
                );
            }
        }

        // if ($_SERVER["REQUEST_METHOD"] == "GET") {
        //     $base64AuthUser = $_SERVER['QUERY_STRING'];
        //     try {
        //         $authUser = json_decode(base64_decode($base64AuthUser)); // {"u":"","p":""}
        //         if ($authUser != null) {
        //             $eId = (new EmployeeModel())->get_employee_id(base64_decode($authUser->u), md5(base64_decode($authUser->p)));
        //             if ($eId != null) {
        //                 $result = new stdClass();
        //                 $result->employeeId = (string)$eId;
        //                 $this->send(
        //                     json_encode($result),
        //                     array('Content-Type: application/json', $this->_httpStatusCode["200"])
        //                 );
        //             } else {
        //                 $this->send(
        //                     json_encode($this->result('400', 'Invalid username or password')),
        //                     array('Content-Type: application/json', $this->_httpStatusCode["400"])
        //                 );
        //             }
        //         }
        //     } catch (Exception $e) {
        //         $this->send(
        //             $e,
        //             array('Content-Type: application/json', $this->_httpStatusCode["500"])
        //         );
        //     }
        // }
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
                        json_encode($obj, JSON_UNESCAPED_UNICODE),
                        array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
                    );
                } else {
                    $this->send(
                        json_encode($this->badrequest('Invalid profile for employee id ' . $_GET["eId"])),
                        array('Content-Type: application/json', $this->_httpStatusCode["400"])
                    );
                }
            } else {
                $this->send(
                    json_encode($this->badrequest($_SERVER["REQUEST_METHOD"] . ' Method not impllent')),
                    array('Content-Type: application/json', $this->_httpStatusCode["400"])
                );
            }
        } catch (\Throwable $th) {
            //throw $th;
            $this->send(
                $th->getMessage(),
                array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["500"])
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
                $found = false;
                foreach ($files as  $fileName) {
                    if (pathinfo($fileName, PATHINFO_EXTENSION) == 'json') {
                        $b = "a" . $eId . '-';
                        if (strpos($fileName, $b) !== false) {
                            $found = true;
                        }
                    }
                }

                if ($found) {
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
                        json_encode($output, JSON_UNESCAPED_UNICODE),
                        array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
                    );
                } else {
                    $this->send(
                        json_encode($this->badrequest('Notify token not save')),
                        array('Content-Type: application/json', $this->_httpStatusCode["400"])
                    );
                }
            } else {
                $this->send(
                    json_encode($this->badrequest($_SERVER["REQUEST_METHOD"] . ' Method not impllent')),
                    array('Content-Type: application/json', $this->_httpStatusCode["400"])
                );
            }
        } catch (Exception $th) {
            $this->send(
                $th->getMessage(),
                array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["500"])
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
            $fileName = "";
            //$found = false;
            foreach ($files as  $fileName) {
                if (pathinfo($fileName, PATHINFO_EXTENSION) == 'json') {
                    $b = "a" . $eId . '-';
                    if (strpos($fileName, $b) !== false) {
                        $found = true;
                        break;
                    } else {
                        $fileName = "";
                    }
                }
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
                    json_encode($output, JSON_UNESCAPED_UNICODE),
                    array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
                );
            } else {
                $output = new stdClass();
                $output->result = false;
                $output->message = "Employee data file not found";
                $this->send(
                    json_encode($output, JSON_UNESCAPED_UNICODE),
                    array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
                );
            }
        } catch (\Throwable $th) {
            $this->send(
                $th->getMessage(),
                array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["500"])
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
                            json_encode($objUser),
                            array('Content-Type: application/json', $this->_httpStatusCode["200"])
                        );
                    } else {
                        $this->send(
                            json_encode($this->badrequest('Exist username or employee id')),
                            array('Content-Type: application/json', $this->_httpStatusCode["400"])
                        );
                    }
                } else {
                    $this->send(
                        json_encode($this->badrequest('Invalid profile for employee id ' . $_POST["eId"])),
                        array('Content-Type: application/json', $this->_httpStatusCode["400"])
                    );
                }
            } else {
                $this->send(
                    json_encode($this->badrequest($_SERVER["REQUEST_METHOD"] . ' Method not impllent')),
                    array('Content-Type: application/json', $this->_httpStatusCode["400"])
                );
            }
        } catch (\Throwable $th) {
            $this->send(
                $th->getMessage(),
                array('Content-Type: application/json', $this->_httpStatusCode["500"])
            );
        }
    }
}
