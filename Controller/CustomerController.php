<?php
// include the use model file
// set_error_handler([$logger, 'onSilencedError']);
// dns_get_record("php.net", DNS_ANY, $authns, $addtl);
// restore_error_handler();
require_once ROOT_PATH . "/Model/Customer.php";

class CustomerController extends BaseController
{
    function __construct()
    {
        // if (!file_exists(WEB_PATH_USER)) {
        //     mkdir(WEB_PATH_USER, 0777);
        // }
        // chmod(WEB_PATH_USER, 0777);
    }

    public function list(){
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            try {
                $jobList = (new Customer())->get_list();                
                $this->send(
                    $this::OK,
                    json_encode(array_values($jobList), JSON_UNESCAPED_UNICODE),
                    array('Content-Type: application/json; charset=utf-8')
                );
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }
}
