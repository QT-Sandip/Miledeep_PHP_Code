<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/DailyInwardListController.php';

$database = new Database();
$restApi = new REST();
$isValid = false;

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();

if ($RequestMethod == 'GET') {
    $data = validateGetData($authToken, $RequestBody);
} else {

}


if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new DailyInwardListController($db);
        if ($RequestMethod == 'GET') {
            $itemCount = $Controller->getDailyInwardDetails($RequestBody);
        }

        $responseData['Error'] = $itemCount['Error'];
        $responseData['Msg'] = $itemCount['Msg'];
        $responseData['DailyInwardData'] = isset($itemCount['DailyInwardData']) ? $itemCount['DailyInwardData'] : null;
        $responseData['Totals'] = $itemCount['Totals'];
        $responseData = json_encode($responseData);
        $restApi->response($responseData, 200);

    }
} else {
    $responseData['Error'] = $data['Error'];
    $responseData['Msg'] = $data['Error'];
    $responseData = json_encode($responseData);
    $restApi->response($responseData, 200);
}

function validateGetData($authToken, $RequestBody)
{

    $isValid = false;
    $error = null;
 // echo  $RequestBody['TransactionDate']; exit;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    }
    else if (!isset($RequestBody['TransactionDate']) || empty($RequestBody['TransactionDate'])) {
        $error = 'TransactionDate not Found';
    }
    
    else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}


?>