<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/StoreOnFloorStockController.php';

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
        $Controller = new StoreOnFloorStockController($db);
        if ($RequestMethod == 'GET') {
            if( isset($RequestBody['methodType']) && $RequestBody['methodType'] == 'getAnalytics' ){
                   $itemCount = $Controller->getAnalytics($RequestBody);
                   $responseData = $itemCount;
            }else{
                 $itemCount = $Controller->getStoreOnFloorDetails($RequestBody);
            // $responseData['EditLink'] = $itemCount['EditLink'];
            // $responseData['EditAccess'] = true;
            $responseData['TotalCount'] = $itemCount['TotalCount'];
            $responseData['RemainingCount'] = $itemCount['RemainingCount'];
            $responseData['RecordsAval'] = $itemCount['RecordsAval'];
            $responseData['PageCount'] = $itemCount['PageCount'];
            $responseData['Data'] = $itemCount['Data'];
           }
    }

        $responseData['Error'] = $itemCount['Error'];
        $responseData['Msg'] = $itemCount['Msg'];

        $responseData = json_encode($responseData);
        $restApi->response($responseData, 200);
    }
} else {
    $responseData['Error'] = $data['Error'];
    $responseData['Msg'] = $data['Error'];
    $responseData = json_encode($responseData);
    $restApi->response($responseData, 200);
}

function validateGetData($authToken)
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
