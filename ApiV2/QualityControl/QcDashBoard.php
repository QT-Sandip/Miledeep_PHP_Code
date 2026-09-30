<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/QcDashBoardController.php';

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
    $data['IsValid'] = false;
}



if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new QcDashBoardController($db);
        $payload = Token::Verify($authToken);
        $RequestBody['ClientID'] = $payload['ClientID'];

        if ($RequestMethod == 'POST') {
            $responseData = $Controller->getDashBoardData($RequestBody);
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


function validatePostData($authToken, $RequestBody)
{

    //   var_dump($RequestBody);

    //echo $RequestBody['GraderID'];
    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    }

    // else  if (!isset($RequestBody['GraderID']) && empty($RequestBody['GraderID'])) {
    //     $error = 'Grader Details not Found';
    // } else  if (!isset($RequestBody['AgentID']) && empty($RequestBody['AgentID'])) {
    //     $error = 'Agent Details not Found';
    // }else  if (!isset($RequestBody['ArrivalNumber']) && empty($RequestBody['ArrivalNumber'])) {
    //     $error = 'Arrival Number not Found';
    // }  else  if (!isset($RequestBody['DriverID']) && empty($RequestBody['DriverID'])) {
    //     $error = 'Driver Details not Found';
    // } else  if (!isset($RequestBody['VehicalID']) && empty($RequestBody['VehicalID'])) {
    //     $error = 'Vehicle Details not Found';
    // } else  if (!isset($RequestBody['FarmAddress']) && empty($RequestBody['FarmAddress'])) {
    //     $error = 'Farm Details not Found';
    // }   else  if (!isset($RequestBody['PurchaseCategoryID']) && empty($RequestBody['PurchaseCategoryID'])) {
    //     $error = 'Purchase  not Found';
    // }   else  if (!isset($RequestBody['ArrivalDate']) && empty($RequestBody['ArrivalDate'])) {
    //     $error = 'Arrival Date Details not Found';
    // }    else  if (!isset($RequestBody['ArrivalStationID']) && empty($RequestBody['ArrivalStationID'])) {
    //     $error = 'Center Details not Found';
    // }   
    else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>