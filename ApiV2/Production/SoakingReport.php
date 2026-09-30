<?php
include_once '../Config/Config.php';
include_once 'Controller/SoakingReportController.php';

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
        $Controller = new SoakingReportController($db);
        if ($RequestMethod == 'GET') {
             if ($RequestBody['DataType'] == "getArrivalsByFY") {
                $responseData = $Controller->getArrivalsByFinancialYear($RequestBody);
            } else if (isset($RequestBody['DataType']) && $RequestBody['DataType'] === "getArrivalsByDate") {
                $responseData = $Controller->getArrivalsByFinancialYear($RequestBody);
            }else {
            $responseData = $Controller->getSoakingReportDetails($RequestBody);
            }
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


// function validateGetData($authToken, $RequestBody)
// {

//     $isValid = false;
//     $error = null;

//     if (!isset($authToken) && empty($authToken)) {
//         $error = 'Token Not Found';
//     } else if (!isset($RequestBody['FromDate']) || empty($RequestBody['FromDate'])) {
//         $error = 'FromDate not Found';
//     } else if (!isset($RequestBody['ToDate']) || empty($RequestBody['ToDate'])) {
//         $error = 'ToDate not Found';
//     } else if (isset($RequestBody['IsPlantFilter']) && $RequestBody['IsPlantFilter'] == 1 && (!isset($RequestBody['PlantID']) || empty($RequestBody['PlantID']))) {
//         $error = 'PlantID not Found';
//     } else {
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

    if (!isset($authToken) || empty($authToken)) {
        $error = 'Token Not Found';
    }
    
    else if ((
            (!isset($RequestBody['SoakingDate']) || empty($RequestBody['SoakingDate'])) &&
            (!isset($RequestBody['FromDate']) || empty($RequestBody['FromDate']))
        ) && $RequestBody['methodType'] != 'getLinersByPlant'
    ) {
        $error = 'FromDate not Found';
    }
    else if ((
            (!isset($RequestBody['SoakingDate']) || empty($RequestBody['SoakingDate'])) &&
            (!isset($RequestBody['ToDate']) || empty($RequestBody['ToDate']))
        ) && $RequestBody['methodType'] != 'getLinersByPlant'
    ) {
        $error = 'ToDate not Found';
    }
    else if ((
            isset($RequestBody['IsPlantFilter']) && $RequestBody['IsPlantFilter'] == 1 &&
            (!isset($RequestBody['PlantID']) || empty($RequestBody['PlantID']))
        ) && $RequestBody['methodType'] != 'getLinersByPlant'
    ) {
        $error = 'PlantID not Found';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}
