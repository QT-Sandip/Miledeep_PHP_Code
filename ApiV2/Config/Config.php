<?php

    /*ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
*/
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    
    include_once 'Database.php';
    include_once 'Rest.php';

    $database = new Database();
    $restApi = new REST();

    $authToken = $restApi->getRequestHeader();
    $RequestBody = $restApi->getRequestBody();
?>