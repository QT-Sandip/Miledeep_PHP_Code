<?php

include_once '../Config/Config.php';
include_once 'Controller/PaymentTermsDetailsController.php';

$database = new Database();
$restApi = new REST();

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$data = validateData($authToken, $RequestBody);

if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new PaymentTermsDetailsController($db);

        $itemCount = $Controller->getPaymentTermsDetails();
        $responseData['Error'] = null;
        $responseData['Msg'] = 'Success';
        $responseData['EditLink'] = 'https://serp.silverlights.in/Clients/DFL/Sales/AddPymntTrms.php';
        $responseData['EditAccess'] = true;
        $responseData['DeleteAccess'] = false;
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
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>