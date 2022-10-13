<?php
$users = array('admin' => 'mypass', 'guest' => 'guest');
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    //echo 'Text to send if user hits Cancel button';
    exit;
} 
else {
    if(!isset($users[$_SERVER['PHP_AUTH_USER']])){
        exit;
    }
    if($users[$_SERVER['PHP_AUTH_USER']] != $_SERVER['PHP_AUTH_PW']){
        exit;
    }
   // echo 'success';
}
?>