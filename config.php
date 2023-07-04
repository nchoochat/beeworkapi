<?php
define("ROOT_PATH", __DIR__);
define("SEVER_IS_UNIX", true);

// DB Configuration
define("DB_HOST", "localhost");
define("DB_USERNAME", "alpha");
define("DB_PASSWORD", "t9ll0pXic*ExFuBK");
define("DB_DATABASE_NAME", "navtech_internal");

// WEB Configuration
define('WEB_ROOT', 'beeworkapi');
//define("WEB_PATH_ATTACH_FILE", "Z:\\\\JobAttachment");
define("WEB_PATH_USER", ROOT_PATH . "/Data/User");
define("WEB_PATH_PHOTO", ROOT_PATH . "/Data/JobPhoto");
define("WEB_PATH_LOGS", ROOT_PATH . "/Data/Logs");
define("WEB_PATH_PHOTO_COMPLETE", ROOT_PATH . "/Data/JobPhoto");

define("APP_LINE_TOKEN", "EWHWv5bHh7MOMuH5QywssYpGmEiDGkhJUxmBbETuhyF");


// include the base controller file
require_once ROOT_PATH . "/Controller/BaseController.php";
require_once ROOT_PATH . "/Controller/DatabaseController.php";
