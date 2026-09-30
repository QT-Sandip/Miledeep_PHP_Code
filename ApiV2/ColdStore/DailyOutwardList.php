<?php
include_once '../Config/Config.php';
include_once 'Controller/DailyOutwardListController.php';

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
        $Controller = new DailyOutwardListController($db);
        if ($RequestMethod == 'GET') {
            $itemCount = $Controller->getDailyOutwardDetails($RequestBody);
        }

        $responseData['Error'] = $itemCount['Error'];
        $responseData['Msg'] = $itemCount['Msg'];
        $responseData['DailyOutwardData'] = isset($itemCount['DailyOutwardData']) ? $itemCount['DailyOutwardData'] : null;
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