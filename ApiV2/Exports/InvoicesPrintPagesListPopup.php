<?php

include_once '../Config/Config.php';
include_once 'Controller/InvoicesPrintPagesListPopupController.php';

$database = new Database();
$restApi = new REST();
$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$data = validateData($authToken, $RequestBody);
if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new InvoicesPrintPagesListPopupController($db);

        $itemCount = $Controller->getInvoicesPrintPagesList($RequestBody);
        $responseData['Error'] = null;
        $responseData['Msg'] = 'Success';        
        $responseData['Data'] = $itemCount['AvailableReports'];
        $responseData['RecordsAval'] = $itemCount['RecordsAval'];
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
    } else if (!isset($RequestBody['PoID']) || empty($RequestBody['PoID'])) {
        $error = 'Po Id Required';
    }else if (!isset($RequestBody['ReportCat']) || empty($RequestBody['ReportCat'])) {
        $error = 'Invalid Report Category';
    }else if (!isset($RequestBody['DocType']) || empty($RequestBody['DocType'])) {
        $error = 'Invalid Document Type';
    }else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>