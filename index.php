<?php
//error_reporting(E_ERROR | E_PARSE);
//declare(strict_types=1);
require __DIR__ . "/inc/config.php";

$uri_root = "/" . WEB_ROOT ;
$dir_root = __DIR__;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', str_replace($uri_root, "", $uri));

if (!isset($uri[2]) || !isset($uri[3])) {
    //header("HTTP/1.1 404 Not Found");
    require "version.txt";
    exit();
}

$realm = 'Restricted area';
switch (strtolower($uri[2])) {
    case 'user':        
        if (!empty($_SERVER['PHP_AUTH_DIGEST'])) {
            require PROJECT_ROOT_PATH . "/Controller/Api/UserController.php";
            $objFeedController = new UserController();
            $objFeedController->{$uri[3]}();
        } else {
            header('HTTP/1.1 401 Unauthorized');
        }
        break;
    case 'job':
        if (!empty($_SERVER['PHP_AUTH_DIGEST'])) {
            require PROJECT_ROOT_PATH . "/Controller/Api/JobController.php";
            $objFeedController = new JobController();
            $objFeedController->{$uri[3]}();
        } else {
            header('HTTP/1.1 401 Unauthorized');
        }
        break;
    case 'image':
        require PROJECT_ROOT_PATH . "/Controller/Api/ImageController.php";
        $objFeedController = new ImageController();
        $objFeedController->{$uri[3]}();
        break;
    case 'logs':
        require PROJECT_ROOT_PATH . "/Controller/Api/LogsController.php";
        $objFeedController = new ImageController();
        $objFeedController->{$uri[3]}();
        break;
    default:
        header("HTTP/1.1 404 Not Found");
        exit();
        break;
}
