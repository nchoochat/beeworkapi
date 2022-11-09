<?php
//error_reporting(E_ERROR | E_PARSE);
//declare(strict_types=1);
require __DIR__ . "/Inc/config.php";

$uri_root = "/" . WEB_ROOT;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', str_replace($uri_root, "", $uri));

if (!isset($uri[2])) {
    require "version.txt";
    exit();
}

$realm = 'Restricted area';
switch (strtolower($uri[2])) {
    case 'employee':
        if (!empty($_SERVER['PHP_AUTH_DIGEST'])) {
            require ROOT_PATH . "/Controller/EmployeeController.php";
            $objFeedController = new UserController();
            if (method_exists($objFeedController, $uri[3]))
                $objFeedController->{$uri[3]}();
            else
                $objFeedController->badrequest("Invalid API caller");
        } else {
            header('HTTP/1.1 401 Unauthorized');
        }
        break;
    case 'job':
        if (!empty($_SERVER['PHP_AUTH_DIGEST'])) {
            require ROOT_PATH . "/Controller/JobController.php";
            $objFeedController = new JobController();
            $objFeedController->{$uri[3]}();
        } else {
            header('HTTP/1.1 401 Unauthorized');
        }
        break;
    case 'notify':
        require ROOT_PATH . "/Controller/NotifyController.php";
        $objFeedController = new NotifyController();
        $objFeedController->{$uri[3]}();
        break;
    case 'image':
        require ROOT_PATH . "/Controller/ImageController.php";
        $objFeedController = new ImageController();
        $objFeedController->{$uri[3]}();
        break;
    case 'logs':
        require ROOT_PATH . "/Controller/LogsController.php";
        $objFeedController = new LogsController();
        $objFeedController->{$uri[3]}();
        break;
    case "authen":
        require ROOT_PATH . "/AuthDigest.php";
        break;
    default:
        header("HTTP/1.1 404 Not Found");
        exit();
        break;
}
