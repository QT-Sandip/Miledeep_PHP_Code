<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/AntiDumpingShipmentReportController.php';

$database = new Database();
$restApi = new REST();
$isValid = false;

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();

if ($RequestMethod == 'GET') {
    if ($RequestBody['type'] == "packingStyle") {
        $data = validateGetPackingData($authToken, $RequestBody);
    } else if ($RequestBody['type'] == "Grades") {
        $data = validateGetGradeData($authToken, $RequestBody);
    } else{
        $data = validateGetData($authToken, $RequestBody);
    }
} else {
}


if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new AntiDumpingShipmentReportController($db);
        if ($RequestMethod == 'GET') {
            if ($RequestBody['type'] == "packingStyle") {
                $responseData = $Controller->getPackingStyleDetails($RequestBody);
            } else if ($RequestBody['type'] == "getPoTotal"){
                $responseData = $Controller->getPoTotal($RequestBody);
            } else if ($RequestBody['type'] == "Grades") {
                $responseData = $Controller->getAllGrades($RequestBody);
            } else if ($RequestBody['type'] == "allPoNumbers") {
                $responseData = $Controller->getAllPoNumbers($RequestBody);
            } else {
                $responseData = $Controller->getDailyProductionDetails($RequestBody);
            }
        }  else {
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
    // else if (empty($RequestBody['ProductionDate']) && empty($RequestBody['ProductionID'])) {
    //     $error = 'ProductionDate or ProductionID Mandatory';
    // } 
    else if (empty($RequestBody['ProductionDate']) && empty($RequestBody['ProductionID'])) {
        $error = 'Please select Production Date Or Production Id Missing  ';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}

function validateGetPackingData($authToken, $RequestBody)
{

    $isValid = false;
    $error = null;
    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    }
    // else if (empty($RequestBody['ProductionDate']) && empty($RequestBody['ProductionID'])) {
    //     $error = 'ProductionDate or ProductionID Mandatory';
    // } 
    else if (empty($RequestBody['PackingStyle']) && empty($RequestBody['PackingStyle'])) {
        $error = 'Please select Packing Style  ';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}

function validateGetGradeData($authToken, $RequestBody)
{

    $isValid = false;
    $error = null;
    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    }
    // else if (empty($RequestBody['ProductionDate']) && empty($RequestBody['ProductionID'])) {
    //     $error = 'ProductionDate or ProductionID Mandatory';
    // } 
    else if (empty($RequestBody['VarietyID']) && empty($RequestBody['VarietyID'])) {
        $error = 'Please select Variety';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}