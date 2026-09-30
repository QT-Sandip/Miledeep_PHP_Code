<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/DailyInwardDetailsController.php';

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
} else if ($RequestMethod == 'DELETE') {
    $data = validateDeleteData($authToken, $RequestBody);
} else {
}



if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new DailyInwardDetailsController($db);
        if ($RequestMethod == 'GET') {
            if($RequestBody['method'] == "PoNumber"){
                $responseData = $Controller->getPoNumbers($RequestBody);
                if($responseData == []){
                    $responseData['msg'] = "Success";
                }
            }else  if($RequestBody['method'] == "PoBalance"){
                $responseData = $Controller->getPoWeightBalance($RequestBody);
            }else{
                $responseData = $Controller->getStockDetails($RequestBody);
            }
        } else if ($RequestMethod == 'POST') {
            $responseData = $Controller->postStockDetails($RequestBody);
        } else if ($RequestMethod == 'DELETE') {
            $responseData = $Controller->deleteStockData($RequestBody);
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
    }


    // else if (!isset($RequestBody['ProductionDate']) || empty($RequestBody['ProductionDate'])) {
    //     $error = 'ProductionDate not Found';
    // } 

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
function validateDeleteData($authToken, $RequestBody)
{


    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else if (!isset($RequestBody['StockID']) && empty($RequestBody['StockID'])) {
        $error = 'StockID not Found';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}
