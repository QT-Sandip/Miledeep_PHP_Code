<?php
include_once '../Config/Config.php';
include_once 'Controller/RmDeductionsCreateController.php';

$database = new Database();
$restApi = new REST();
$isValid = false;

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();
$payload = Token::Verify($authToken);
if (is_array($payload)) {
    $RequestBody['AuthPayload'] = $payload;
    $RequestBody['AuthUserID'] = isset($payload['UserID']) ? $payload['UserID'] : null;
}

if ($RequestMethod == 'GET') {
    $data = validateGetData($authToken,$RequestBody);
} else if ($RequestMethod == 'POST') {
    $data = validatePostData($authToken, $RequestBody);
}


if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new RmDeductionsCreateController($db);
        if ($RequestMethod == 'GET') {
            $responseData = $Controller->getRmDeductions($RequestBody);          
        } else if ($RequestMethod == 'POST') {
            $responseData = $Controller->postRmDeductionRates($RequestBody);
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

function validateGetData($authToken,$RequestBody)
{

    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } 
    else if (!isset($RequestBody['RateID']) || empty($RequestBody['RateID'])) {
        $error = 'RateID not Found';
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
    } else if (!isset($RequestBody['PurchaseType']) || empty($RequestBody['PurchaseType'])) {
        $error = 'PurchaseType not Found';
    } else if (!isset($RequestBody['CenterId']) && empty($RequestBody['CenterId'])) {
        $error = 'CenterId not Found';
    } else if (!isset($RequestBody['Species']) || empty($RequestBody['Species'])) {
        $error = 'Species not Found';
    } else if (!isset($RequestBody['EffectedDate']) || empty($RequestBody['EffectedDate'])) {
        $error = 'EffectedDate not Found';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}
