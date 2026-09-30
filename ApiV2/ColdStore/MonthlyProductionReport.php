<?php
//   ini_set('display_errors', 1);
//  ini_set('display_startup_errors', 1);
//  error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/MonthlyProductionReportController.php';

$database = new Database();
$restApi = new REST();
$isValid = false;

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();

if ($RequestMethod == 'GET') {
    $data = validateGetData($authToken, $RequestBody);
} 
else {
}


if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new MonthlyProductionReportController($db);
        if ($RequestMethod == 'GET') {
            $responseData = $Controller->getMonthlyProductionReport($RequestBody);
        }

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

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } elseif (!isset($RequestBody['FromDate']) || empty($RequestBody['FromDate'])) {
        $error = 'Please select from date';
    }elseif (!isset($RequestBody['ToDate']) || empty($RequestBody['ToDate'])) {
        $error = 'Please select closing date';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}
