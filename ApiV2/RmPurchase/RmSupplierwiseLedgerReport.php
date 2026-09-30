<?php

include_once '../Config/Config.php';
include_once 'Controller/RmSupplierwiseLedgerReportController.php';

$database = new Database();
$restApi = new REST();

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();

$data = validateData($authToken, $RequestBody);

if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new RmSupplierwiseLedgerReportController($db);
//echo "hii";
        $itemCount = $Controller->GetRmSupplierWiseledgerReportDetails($RequestBody);
        $responseData['Error'] = null;
        $responseData['Msg'] = 'Success';
        // $responseData['EditLink'] = 'rm-bill';
        // $responseData['EditAccess'] = True;
        // $responseData['DeleteAccess'] = false;
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
    }
    else  if (!isset($RequestBody['BillerType']) && empty($RequestBody['BillerType'])) {
        $error = 'BillerType not Found';
    }
    // Durga
    else  if (!isset($RequestBody['StartDate']) || empty($RequestBody['StartDate'])) {
        $error = 'Start Date not Found';
    } 
    else  if (!isset($RequestBody['EndDate']) || empty($RequestBody['EndDate'])) {
        $error = 'End Date not Found';
    }
    // Durga
    else {
        $isValid = true;
    }
     
    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>
