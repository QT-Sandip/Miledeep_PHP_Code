<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/CreateStockController.php';

$database = new Database();
$restApi = new REST();
$isValid = false;

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();

if ($RequestMethod == 'GET') {
    $data = validateGetData($authToken, $RequestBody);
} else if ($RequestMethod == 'POST') {
    $data = validatePostData($authToken, $RequestBody);
}
// else if($RequestMethod=='DELETE'){
//     $data = validateDeleteData($authToken, $RequestBody);
// }
else {

}



if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new CreateStockController($db);
        if ($RequestMethod == 'GET') {
            $itemCount = $Controller->getStockDetails($RequestBody);
        } else if ($RequestMethod == 'POST') {
            $itemCount = $Controller->postStockDetails($RequestBody);
        }
        // else if($RequestMethod=='DELETE'){
        //     $itemCount = $Controller->postDeleteArrivalBrkUP($RequestBody);
        // }

        $responseData['Error'] = $itemCount['Error'];
        $responseData['Msg'] = $itemCount['Msg'];
        // $responseData['EditLink'] = '#';
        // $responseData['EditAccess'] = true;
        // $responseData['DeleteAccess'] = true;
        // $responseData['BreakUpData'] = isset($itemCount['BreakUpData'])  ? $itemCount['BreakUpData']: null;
        $responseData['StockData'] = isset($itemCount['StockData']) ? $itemCount['StockData'] : null;
        // $responseData['GrandTotal'] = isset($itemCount['GrandTotal'])  ? $itemCount['GrandTotal']: null;
        // $responseData['ArrivalNumberData'] = isset($itemCount['ArrivalNumberData'])  ? $itemCount['ArrivalNumberData']: null;
        $responseData = json_encode($responseData);
        $restApi->response($responseData, 200);

    }
} else {
    $responseData['Error'] = $data['Error'];
    $responseData['Msg'] = $data['Error'];
    $responseData = json_encode($responseData);
    $restApi->response($responseData, 200);
}


// function validateDeleteData($authToken, $RequestBody)
// {
//     $isValid = false;
//     $error = null;
//     if (!isset($authToken) && empty($authToken)) {
//         $error = 'Token Not Found';
//     } else  if (!isset($RequestBody['ArrivalID']) || empty($RequestBody['ArrivalID'])) {
//         $error = 'Arrival ID not Found';
//     } else  if (!isset($RequestBody['BreakUpID']) || empty($RequestBody['BreakUpID'])) {
//         $error = 'BreakUp ID not Found';
//     }else {
//         $isValid = true;
//     }
//     $Data['Error'] = $error;
//     $Data['IsValid'] = $isValid;
//     return $Data;
// }

function validateGetData($authToken, $RequestBody)
{

    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    }  else if (!isset($RequestBody['BarCodeID']) && empty($RequestBody['BarCodeID'])) {
            $error = 'BarCodeID not Found';
        }
     else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}
function validatePostData($authToken, $RequestBody)
{


    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } 
    // else if (!isset($RequestBody['GraderID']) && empty($RequestBody['GraderID'])) {
    //     $error = 'Grader Details not Found';
    // }
    else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>