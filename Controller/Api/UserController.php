<?php
// include the use model file

require_once PROJECT_ROOT_PATH . "/Model/UserModel.php";

class UserController extends BaseController
{
    protected $_httpStatusCode = [
        "500" => "HTTP/1.1 500 Internal Server Error",
        "200" => "HTTP/1.1 200 OK",
    ];

    function __construct()
    {
        //echo $_SERVER["REQUEST_METHOD"];
    }
    /**
     * "/user/list" Endpoint - Get list of users
     */
    public function list()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $arrQueryStringParams = $this->getQueryStringParams();

        if (strtoupper($requestMethod) == 'GET') {
            try {
                $userModel = new UserModel("", "");

                $intLimit = 10;
                if (isset($arrQueryStringParams['limit']) && $arrQueryStringParams['limit']) {
                    $intLimit = $arrQueryStringParams['limit'];
                }

                $arrUsers = $userModel->getUsers($intLimit);
                $responseData = json_encode($arrUsers);
            } catch (Error $e) {
                $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
        } else {
            $strErrorDesc = 'Method not supported';
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
        }

        // send output
        if (!$strErrorDesc) {
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {
            $this->sendOutput(
                json_encode(array('error' => $strErrorDesc)),
                array('Content-Type: application/json', $strErrorHeader)
            );
        }
    }

    public function authUser()
    {
        //http://localhost/hakao/index.php/user/authUser?eyJ1IjoiTmpZek1ERTFNekk9IiwicCI6ImNHRnpjM2R2Y21RPSJ9
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $base64AuthUser = $_SERVER['QUERY_STRING'];
            try {
                $authUser = json_decode(base64_decode($base64AuthUser)); // {"u":"","p":""}
                if ($authUser != null) {
                    $database = new DatabaseController();

                    $filename = $GLOBALS['dir_root'] . "/SQL/authUser.sql";
                    $array = explode("\n", file_get_contents($filename));
                    $sql = (implode(chr(10), $array));

                    $users = $database->execut(sprintf($sql, base64_decode($authUser->u), md5(base64_decode($authUser->p))));
                    //$users = $database->execut("SELECT * FROM employee WHERE Username = '" . base64_decode($authUser->u) . "' AND Pwd = '" . md5(base64_decode($authUser->p)) . "'");

                    $userModel = new UserModel(base64_decode($authUser->u), base64_decode($authUser->p));
                    if (count($users) > 0) {
                        $userModel->username = base64_decode($authUser->u);
                        $userModel->pwd = base64_decode($authUser->p);
                        $userModel->employeeId = (string) $users[0]["EmployeeId"];
                        $userModel->status = "success";
                    } else {
                        $userModel->status = "failed";
                    }
                    $this->send(
                        json_encode($userModel),
                        array('Content-Type: application/json', $this->_httpStatusCode["200"])
                    );
                }
            } catch (Exception $e) {
                $this->send(
                    $e,
                    array('Content-Type: application/json', $this->_httpStatusCode["500"])
                );
            }
        }
    }

    public function saveNotifyToken()
    {
        $result = new stdClass();
        try {
            //code...
            $method = $_SERVER['REQUEST_METHOD'];
            if ('PUT' === $method) {
                parse_str(file_get_contents('php://input'), $_PUT);
                // print_r($_PUT); //$_PUT contains put fields 
                $eId = $_PUT["EmployeeId"];
                $notifyToken = $_PUT["NotifyToken"];

                $filename = $GLOBALS['dir_root'] . "/SQL/saveNotifyToken.sql";
                $array = explode("\n", file_get_contents($filename));
                $sql = (implode(chr(10), $array));

                $database = new DatabaseController();
                //UPDATE employee SET Pwd = '%s' WHERE EmployeeId = '%s'
                if ($database->executeNonQuery(sprintf($sql, $notifyToken, $eId)) > 0) {
                    $result->status = 'success';
                    $result->error = '';
                } else {
                    $result->status = 'failed';
                    $result->error = '';
                }
            }
        } catch (Exception $th) {
            //throw $th;
            $result->status = 'error';
            $result->error = $th->getMessage();
        }
        $this->send(
            json_encode($result, JSON_UNESCAPED_UNICODE),
            array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
        );
    }

    public function getProfile()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            //try {
            $filename = $GLOBALS['dir_root'] . "/SQL/getProfile.sql";
            $array = explode("\n", file_get_contents($filename));
            $sql = (implode(chr(10), $array));
            //print_r(sprintf($sql, $_GET["eId"]));
            $database = new DatabaseController();
            $profile = $database->execut(sprintf($sql, $_GET["eId"]));

            if (count($profile) > 0) {
                //print_r($profile[0]);
                $obj = new Profile;
                $obj->fullName =  $profile[0]['FullName'];
                $obj->positionName =  $profile[0]['PositionName'];
                $obj->role = ($profile[0]['Role'] == null) ? '' : $profile[0]['Role'];

                $this->send(
                    json_encode($obj, JSON_UNESCAPED_UNICODE),
                    array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
                );
            } else {
                $this->send(
                    "",
                    JSON_UNESCAPED_UNICODE,
                    array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["500"])
                );
            }
        }
    }

    public function changePwd()
    {
        $result = new stdClass();
        try {
            //code...
            $eId = $_POST["eId"];
            $pwd = $_POST["pwd"];

            $filename = $GLOBALS['dir_root'] . "/SQL/setPwd.sql";
            $array = explode("\n", file_get_contents($filename));
            $sql = (implode(chr(10), $array));

            $database = new DatabaseController();
            //UPDATE employee SET Pwd = '%s' WHERE EmployeeId = '%s'
            if ($database->executeNonQuery(sprintf($sql, md5($pwd), $eId)) > 0) {
                $result->status = 'success';
            } else {
                $result->status = 'failed';
            }
        } catch (\Throwable $th) {
            //throw $th;
            $result->status = 'failed';
        }
        $this->send(
            json_encode($result, JSON_UNESCAPED_UNICODE),
            array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
        );
    }
    public function pwd()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            // Do nothing
        } else if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $result = new stdClass();
            try {
                //code...
                $eId = $_POST["eId"];
                $pwd = $_POST["pwd"];

                $filename = $GLOBALS['dir_root'] . "/SQL/setPwd.sql";
                $array = explode("\n", file_get_contents($filename));
                $sql = (implode(chr(10), $array));

                $database = new DatabaseController();
                if ($database->executeNonQuery(sprintf($sql, md5($pwd), $eId)) > 0) {
                    $result->status = 'success';
                } else {
                    $result->status = 'failed';
                }
            } catch (\Throwable $th) {
                //throw $th;
                $result->status = 'failed';
            }
            $this->send(
                json_encode($result, JSON_UNESCAPED_UNICODE),
                array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
            );
        }
    }
    public function login()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            // Do nothing
        } else if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $result = new stdClass();
            try {
                //code...
                $eId = $_POST["eId"];
                $username = $_POST["username"];
                $pwd = $_POST["pwd"];

                $filename = $GLOBALS['dir_root'] . "/SQL/setLogin.sql";
                $array = explode("\n", file_get_contents($filename));
                $sql = str_replace(array('{0}', '{1}', '{2}'), array($eId, $username, md5($pwd)), implode(chr(10), $array));
                $database = new DatabaseController();
                if ($database->executeNonQuery($sql) > 0) {
                    $result->status = 'success';
                } else {
                    $result->status = 'failed';
                }
            } catch (\Throwable $th) {
                //throw $th;
                $result->status = 'failed';
            }
            $this->send(
                json_encode($result, JSON_UNESCAPED_UNICODE),
                array('Content-Type: application/json; charset=utf-8', $this->_httpStatusCode["200"])
            );
        }
    }
}

class Profile
{
    public  $fullName;
    public  $positionName;
    public  $role;
}
