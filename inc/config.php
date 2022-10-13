<?php
define("PROJECT_ROOT_PATH", __DIR__ . "/../");



// DB Configuration
define("DB_HOST", "localhost");
define("DB_USERNAME", "alpha");
define("DB_PASSWORD", "t9ll0pXic*ExFuBK");
define("DB_DATABASE_NAME", "navtech_internal");

// WEB Configuration
define('WEB_ROOT','beeworkapi');
define("WEB_PATH_ATTACH_FILE", "\\\\AZENK\\Public$\\JobAttachment");

// include the base controller file
require_once PROJECT_ROOT_PATH . "/Controller/DatabaseController.php";
require_once PROJECT_ROOT_PATH . "/Controller/Api/BaseController.php";