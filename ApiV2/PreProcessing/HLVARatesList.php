<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Config/Config.php';
include_once 'Controller/HLVARatesListController.php';

$database = new Database();
$restApi = new REST();
$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$data = validateData($authToken, $RequestBody);
if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new HLVARatesListController($db);

        $itemCount = $Controller->GetHLVARatesDetails($RequestBody);
        $responseData['Error'] = null;
        $responseData['Msg'] = 'Success';
        $responseData['PrintLink'] = $itemCount['PrintLink'];
        $responseData['EditLink'] = $itemCount['EditLink'];
        $responseData['Data'] = $itemCount['Data'];
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
    $Month = $RequestBody['Month'];
    $Year = $RequestBody['Year'];
    $StationID = $RequestBody['StationID'];
    $ContractorType = $RequestBody['ContractorType'];


    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    }
    else if (!isset($Month) || empty($Month)) {
        $error = 'Month Not Found';
    }
    else if (!isset($Year) || empty($Year)) {
        $error = 'Year Not Found';
    }
    else if (!isset($StationID) || empty($StationID)) {
        $error = 'StationID Not Found';
    }
    else if (!isset($ContractorType) || empty($ContractorType)) {
        $error = 'ContractorType Not Found';
    }
    else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>