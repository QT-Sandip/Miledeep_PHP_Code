<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

*/
include_once '../Config/Config.php';
include_once 'Controller/RmSupplierController.php';

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
    if (!isset($RequestBody['ClientId']) && isset($payload['ClientID'])) {
        $RequestBody['ClientId'] = $payload['ClientID'];
    }
}

if ($RequestMethod == 'POST') {
    $data = validatePostData($authToken, $RequestBody);
}




if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new RmSupplierController($db);
        if ($RequestMethod == 'POST') {
            $itemCount = $Controller->postSupplierDetails($RequestBody);
        }

        $responseData['Error'] = $itemCount['Error'];
        $responseData['Msg'] = $itemCount['Msg'];
        $responseData['EditLink'] = '#';
        $responseData['EditAccess'] = true;
        $responseData['DeleteAccess'] = true;
        $responseData['Data'] = $itemCount['Data'];
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

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else if (!isset($RequestBody['SupplierID']) || empty($RequestBody['SupplierID'])) {
        $error = 'SupplierID  ID not Found';
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
    } else if (!isset($RequestBody['SupplierID']) || empty($RequestBody['SupplierID'])) {
        $error = 'Supplier ID not Found';
    }
    //  else  if (!isset($RequestBody['BreakUpID']) || empty($RequestBody['BreakUpID'])) {
    //     $error = 'BreakUp ID not Found';
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
    } else if (!isset($RequestBody['SupplierType']) || empty($RequestBody['SupplierType'])) {
        $error = 'Supplier Type not Found';
    } else if (!isset($RequestBody['SupplierName']) || empty($RequestBody['SupplierName'])) {
        $error = 'Supplier Name not Found';

    } else if (!isset($RequestBody['PANNumber']) || empty($RequestBody['PANNumber'])) {
        $error = 'Supplier PAN Number not Found';
    }    
    else {
        $isValid = true;
    }
    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;
    return $Data;

}

?>
