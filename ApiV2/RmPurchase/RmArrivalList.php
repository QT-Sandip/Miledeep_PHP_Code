<?php

include_once '../Config/Config.php';
include_once '../Global/Util.php';
include_once 'Controller/RmArrivalListController.php';

$database = new Database();
$restApi = new REST();

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();

$RequestMethod = $restApi->get_request_method();
$payload = Token::Verify($authToken);
if (is_array($payload)) {
    $RequestBody['AuthPayload'] = $payload;
    $RequestBody['AuthUserID'] = isset($payload['UserID']) ? $payload['UserID'] : null;
    $RequestBody['ClientID'] = isset($payload['ClientID']) ? $payload['ClientID'] : null;
}

if ($RequestMethod == 'GET' || $RequestMethod == 'POST') {
    $data = validateData($authToken, $RequestBody);
} else if ($RequestMethod == 'DELETE') {
    $data = validateDeleteData($authToken, $RequestBody);
} else {
}


if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new RmArrivalListController($db);
        if ($RequestMethod == 'GET' || $RequestMethod == 'POST') {
            $itemCount = $Controller->GetRmArrivalListDetails($RequestBody);
        } else if ($RequestMethod == 'DELETE') {
            $itemCount = $Controller->deleteArrivalDetails($RequestBody);
        }

        // $itemCount = $Controller->GetRmArrivalListDetails($RequestBody);
        $responseData['Error'] = null;
        $responseData['Msg'] = 'Success';
        $responseData['EditLink'] = 'create-arrival.php';
        $responseData['EditAccess'] = True;
        // $responseData['DeleteAccess'] = 'DeleteAccess';
        $responseData['Data'] = $itemCount;
        $responseData = json_encode($responseData);
        $restApi->response($responseData, 200);
    }
} else {
    $responseData['Error'] = $data['Error'];
    $responseData['Msg'] = $data['Error'];
    $responseData = json_encode($responseData);
    $restApi->response($responseData, 200);
}

function validateData($authToken, $RequestBody)
{
    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else  if (!isset($RequestBody['ArrivalDate']) || empty($RequestBody['ArrivalDate'])) {
        $error = 'ArrivalDate Details not Found';
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
    } else if (!isset($RequestBody['RmArrivalID']) || empty($RequestBody['RmArrivalID'])) {
        $error = 'RmArrivalID Required';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}
