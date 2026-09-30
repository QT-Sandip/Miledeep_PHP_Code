<?php

include_once '../Config/Config.php';
include_once 'Controller/RmPurchaseMonthWiseReportController.php';

$database = new Database();
$restApi = new REST();

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();

$data = validateData($authToken, $RequestBody);

if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new RmPurchaseMonthWiseReportController($db);
//echo "hii";
        $itemCount = $Controller->GetRmPurchaseMonthWiseReportDetails($RequestBody);
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
    // else  if (!isset($RequestBody['CenterID']) || empty($RequestBody['CenterID'])) {
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
