<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Config/Config.php';
include_once 'Controller/FreezingTrackIDDetailsController.php';

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
        $Controller = new FreezingTrackIDDetailsController($db);
        if ($RequestMethod == 'GET') {
            $responseData = $Controller->getFreezingDetails($RequestBody);
            } else if ($RequestMethod == 'POST') {
                $responseData = $Controller->postDailyFreezingDetails($RequestBody);
            } else if ($RequestMethod == 'DELETE') {
                $responseData = $Controller->deleteDailyFreezing($RequestBody);
        } else {
            $responseData['Error'] = "No Data Found";
        }

        // $responseData['Error'] = $itemCount['Error'];
        // $responseData['Msg'] = $itemCount['Msg'];
        // $responseData['DailyProductionData'] = isset($itemCount['DailyProductionData']) ? $itemCount['DailyProductionData'] : null;
        $responseData = json_encode($responseData);
        $restApi->response($responseData, 200);
    }
} else {
    $responseData['Error'] = $data['Error'];
    $responseData['Msg'] = $data['Msg'];
    $responseData = json_encode($responseData);
    $restApi->response($responseData, 200);
}

function validateGetData($authToken, $RequestBody)
{

    $isValid = false;
    $error = null;
    if($RequestBody['MixedBatchCodeStatus']){
        $condition = (empty($RequestBody['TrackID']) && empty($RequestBody['TrackID']) && empty($RequestBody['MixedBatchCode']));
        $errorMsg = 'TrackID / MixedBatchCode not found ';
    }else{
        $condition = (empty($RequestBody['TrackID']) && empty($RequestBody['TrackID']));
        $errorMsg = 'TrackID not found ';
    }
    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
        }
        // else if (empty($RequestBody['ProductionDate']) && empty($RequestBody['ProductionID'])) {
        //     $error = 'ProductionDate or ProductionID Mandatory';
        // } 

        else if ($condition) {
            $error = $errorMsg;
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
    } else if (!isset($RequestBody['ProductionID']) && empty($RequestBody['ProductionID'])) {
        $error = 'ProductionID not Found';
    } else {
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
    } else if (!isset($RequestBody['ProductionID']) || empty($RequestBody['ProductionID'])) {
        $error = 'ProductionID not Found';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}
