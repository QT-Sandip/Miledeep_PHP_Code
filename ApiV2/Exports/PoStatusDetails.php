<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

include_once '../Config/Config.php';
include_once 'Controller/PoStatusDetailsController.php';

$database = new Database();
$restApi = new REST();

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();

$POID = $RequestBody['POID'];

$data = validateData($authToken, $RequestBody);

if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new PoStatusDetailsController($db);
        $itemCount = $Controller->getPoStatusDetails($POID);
        $responseData['Error'] = null;
        $responseData['Msg'] = 'Success';
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
    }else if (!isset($RequestBody['POID']) || empty($RequestBody['POID'])) {
        $error = 'Please Select To POID ';
    }
     else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>