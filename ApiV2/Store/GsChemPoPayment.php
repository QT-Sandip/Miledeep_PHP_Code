<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/GsChemPoPaymentController.php';

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
        $Controller = new GsChemPoPaymentController($db);
        if ($RequestMethod == 'GET') {
            $itemCount = $Controller->getChemPoPaymentDetails($RequestBody);
        } else if ($RequestMethod == 'POST') {
            $itemCount = $Controller->postChemPoPaymentDetails($RequestBody);
        } else if ($RequestMethod == 'DELETE') {
            $itemCount = $Controller->deleteChemPaymentDetails($RequestBody);
        }

        $responseData['Error'] = $itemCount['Error'];
        $responseData['Msg'] = $itemCount['Msg'];
        $responseData['EditLink'] = $itemCount['EditLink'];
        $responseData['EditAccess'] = true;
        $responseData['DeleteAccess'] = true;
        $responseData['Data'] = $itemCount['Data'];
        $responseData['Total'] = $itemCount['Total'];

        $responseData = json_encode($responseData);
        $restApi->response($responseData, 200);
    }
} else {
    $responseData['Error'] = $data['Error'];
    $responseData['Msg'] = $data['Error'];
    $responseData = json_encode($responseData);
    $restApi->response($responseData, 200);
}

function validateGetData($authToken)
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
    } else  if (!isset($RequestBody['InvoiceNo']) || empty($RequestBody['InvoiceNo'])) {
        $error = 'InvoiceNo not Found';
    } /* else  if (!isset($RequestBody['InvoiceAmount']) || empty($RequestBody['InvoiceAmount'])) {
        $error = 'InvoiceAmount not Found';
    }  */else {
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
    } else if (!isset($RequestBody['ChemPaymentID']) || empty($RequestBody['ChemPaymentID'])) {
        $error = 'ChemPaymentID Required';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}
