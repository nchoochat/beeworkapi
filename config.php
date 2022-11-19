<?php
define("ROOT_PATH", __DIR__ . "/..");

// DB Configuration
define("DB_HOST", "localhost");
define("DB_USERNAME", "utica");
define("DB_PASSWORD", "tzsve9nRIj/z06HN");
define("DB_DATABASE_NAME", "navtech_internal");

// WEB Configuration
define('WEB_ROOT', 'hakaoqa');
define("WEB_PATH_USER", "/mnt/raid1/Data/User");
define("WEB_PATH_PHOTO", "/mnt/raid1//Data/JobPhoto");
define("WEB_PATH_PHOTO_COMPLETE", "/mnt/raid1//Data/JobPhoto");

// include the base controller file
require_once ROOT_PATH . "/Controller/BaseController.php";
require_once ROOT_PATH . "/Controller/DatabaseController.php";
