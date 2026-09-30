<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/DeheadingListController.php';

$database = new Database();
$restApi = new REST();
$isValid = false;

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();

if ($RequestMethod == 'GET') {
    $data = validateGetData($authToken, $RequestBody);
}else if ($RequestMethod == 'POST') {
    $data = validatePostData($authToken, $RequestBody);
}else if ($RequestMethod == 'DELETE') {
        $data = validateGetData($authToken, $RequestBody);
        }

if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new DeheadingListController($db);
        if ($RequestMethod == 'GET') {
            $responseData = $Controller->getDeheadingListData($RequestBody);
        } 
        else if ($RequestMethod == 'POST') {
            $responseData = $Controller->postDeheadingListData($RequestBody);
        } 
        else if ($RequestMethod == 'DELETE') {
              
       $responseData = $Controller->deleteDeheadingListData($RequestBody);
        }
        $responseData = json_encode($responseData);
        $restApi->response($responseData, 200);
    }
} else {
    $responseData['Error'] = $data['Error'];
    $responseData['Msg'] = $data['Msg'];
    $responseData = json_encode($responseData);
    $restApi->response($responseData, 200);
}

function validateGetData($authToken,$RequestBody)
{

    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}

function validatePostData($authToken,$RequestBody)
{

    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}