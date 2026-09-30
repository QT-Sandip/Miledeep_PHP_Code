<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/SoakingTrackIDDetailsController.php';

$database = new Database();
$restApi = new REST();
$isValid = false;

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();

if ($RequestMethod == 'GET') {
    if (isset($RequestBody['type']) && !empty($RequestBody['type']) && $RequestBody['type'] == 'getAutoTub') {
        $data = validateGetAutoData($authToken, $RequestBody);
    } else {
        $data = validateGetData($authToken, $RequestBody);
    }
} else if ($RequestMethod == 'POST') {
    $data = validatePostData($authToken, $RequestBody);
} else if ($RequestMethod == 'DELETE') {
    $data = validateDeleteData($authToken, $RequestBody);
} else {
}



if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new SoakingTrackIDDetailsController($db);
        if ($RequestMethod == 'GET') {
            if (isset($RequestBody['type']) && !empty($RequestBody['type']) && $RequestBody['type'] == 'getAutoTub') {
                $responseData = $Controller->getAutoTub($RequestBody);
            } else {
                $responseData = $Controller->getSoakingDetails($RequestBody);
            }
        } else if ($RequestMethod == 'POST') {
            $responseData = $Controller->postSoakingDetails($RequestBody);
        } else if ($RequestMethod == 'DELETE') {
            $responseData = $Controller->deleteSoaking($RequestBody);
        }

        // $responseData['Error']=$itemCount['Error'];
        // $responseData['Msg']= $itemCount['Msg'];
        // $responseData['EditLink'] = '#';
        // $responseData['EditAccess'] = true;
        // $responseData['DeleteAccess'] = true;
        // $responseData['Data'] = isset($itemCount['Data'])  ? $itemCount['Data']: null;

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
    }
    // else  if (!isset($RequestBody['ArrivalID']) || empty($RequestBody['ArrivalID'])) {
    //     $error = 'Arrival ID not Found';
    // } 
    else if (!isset($RequestBody['BreakUpID']) || empty($RequestBody['BreakUpID'])) {
        $error = 'BreakUp ID not Found';
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
    } else if (!isset($RequestBody['TrackID']) || empty($RequestBody['TrackID'])) {
        $error = 'TrackID not Found';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}
function validateGetAutoData($authToken, $RequestBody)
{

    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else if ($RequestBody['IsPlantFilter'] == 1 && (!isset($RequestBody['PlantID']) || empty($RequestBody['PlantID']))) {
        $error = 'PlantID not Found';
    } else if (!isset($RequestBody['ArrivalDate']) || empty($RequestBody['ArrivalDate'])) {
        $error = 'ArrivalDate not Found';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}
function validatePostData($authToken, $RequestBody)
{

    //   var_dump($RequestBody);

    //echo $RequestBody['GraderID'];
    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else if (!isset($RequestBody['TrackID']) && empty($RequestBody['TrackID'])) {
        $error = 'TrackID  not Found';
    } else if (!isset($RequestBody['PlantID']) && empty($RequestBody['PlantID'])) {
        $error = 'Plant Details not Found';
    }

    // else  if (!isset($RequestBody['SourceStation']) && empty($RequestBody['SourceStation'])) {
    //     $error = 'Source Station Details not Found';
    // }
    else if (!isset($RequestBody['SoakingDate']) && empty($RequestBody['SoakingDate'])) {
        $error = 'Soaking Date not Found';
    }

    // else  if (!isset($RequestBody['Shift']) && empty($RequestBody['Shift'])) {
    //     $error = 'Shift Details not Found';
    // }
    else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}
