<?php

declare(strict_types=1);
date_default_timezone_set("Asia/Bangkok");
class BaseController
{
    protected $_httpStatusCode = [
        "200" => "OK",
        "400" => "Bad Request",
        "500" => "Internal Server Error",
    ];

    function __construct()
    {
    }
    /**
     * __call magic method.
     */
    public function __call($name, $arguments)
    {
        $this->sendOutput('', array('HTTP/1.1 404 Not Found'));
    }

    /**
     * Get URI elements.
     * 
     * @return array
     */
    protected function getUriSegments()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode('/', $uri);

        return $uri;
    }

    /**
     * Get querystring params.
     * 
     * @return array
     */
    protected function getQueryStringParams()
    {
        return parse_str($_SERVER['QUERY_STRING'], $query);
    }

    /**
     * Send API output.
     *
     * @param mixed  $data
     * @param string $httpHeader
     */
    protected function sendOutput($data, $httpHeaders = array())
    {
        header_remove('Set-Cookie');

        if (is_array($httpHeaders) && count($httpHeaders)) {
            foreach ($httpHeaders as $httpHeader) {
                header($httpHeader);
            }
        }

        echo $data;
        exit;
    }
    
    protected function badrequest(string $message)
    {
        $this->send(
            json_encode($this->result('400', $message)),
            array('Content-Type: application/json', $this->_httpStatusCode["400"])
        );
    }

    protected function send($response, $httpHeaders = array())
    {
        header_remove('Set-Cookie');

        if (is_array($httpHeaders) && count($httpHeaders)) {
            foreach ($httpHeaders as $httpHeader) {
                header($httpHeader);
            }
        }
        echo $response;
        exit;
    }

    protected function result(string $statusCode, string $message)
    {
        $result = new stdClass();
        $result->statusCode = $statusCode;
        $result->statusText = $this->_httpStatusCode[$statusCode];
        $result->message = $message;
        return $result;
    }
}
