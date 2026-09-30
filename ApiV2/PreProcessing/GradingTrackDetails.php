<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/GradingTrackDetailsController.php';

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
        $Controller = new GradingTrackDetailsController($db);
        if ($RequestMethod == 'GET') {
            $responseData = $Controller->getGradingDetails($RequestBody);
        } else if ($RequestMethod == 'POST') {
            $responseData = $Controller->postGradingDetails($RequestBody);
        } else if ($RequestMethod == 'DELETE') {
            $responseData = $Controller->deleteGradingBreakUP($RequestBody);
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


function validateDeleteData($authToken, $RequestBody)
{
    $isValid = false;
    $error = null;
    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else if ($RequestBody['Type'] !="DeleteTotalGradingDetails" && empty($RequestBody['DataBreakUpID'])) {
        $error = 'DataBreakUpID not Found';
    } else {
        $isValid = true;
    }
    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;
    return $Data;
}

function validateGetData($authToken, $RequestBody)
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
function validatePostData($authToken, $RequestBody)
{

    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    }else if (isset($RequestBody['ArrivalID']) && empty($RequestBody['ArrivalID'])) {
        $error = 'Receiving Count not Found'; 
    } else if (isset($RequestBody['Type']) && $RequestBody['Type'] == "BreakUpData"  && empty($RequestBody['GradingID'])) {
        $error = 'GradingID not Found';
    } else if (isset($RequestBody['Type']) && $RequestBody['Type'] == "BreakUpData" && $RequestBody['BreakUpID'] != "" && empty($RequestBody['VarietyID'])) {
        $error = 'Variety not Found';
    } else if (isset($RequestBody['Type']) && $RequestBody['Type'] == "BreakUpData" && $RequestBody['BreakUpID'] != "" && empty($RequestBody['HLCount'])) {
        $error = 'HL Count not Found';
    } else {
        $isValid = true;
    }


    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}
