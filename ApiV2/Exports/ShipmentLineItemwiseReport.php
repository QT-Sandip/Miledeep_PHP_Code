<?php

include_once '../Config/Config.php';
include_once 'Controller/ShipmentLineItemwiseReportController.php';

$database = new Database();
$restApi = new REST();
$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();

$data = validateData($authToken, $RequestBody, $RequestMethod);
if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {

        $Controller = new ShipmentLineItemwiseReportController($db);

        if ($RequestMethod == 'POST') {
            $itemCount = $Controller->ShipmentLineItemwiseReportData($RequestBody);
            $responseData['Error'] = null;
            $responseData['Msg'] = 'Success';
            $responseData['Data'] = $itemCount['ShipmentLineitemwiseData'];
            $responseData['RecordsAval'] = $itemCount['RecordsAval'];
            $responseData = json_encode($responseData);
            $restApi->response($responseData, 200);
        } else
            if ($RequestMethod == 'PUT') {
                $updateCount = $Controller->UpdateDDPPriceRemarks($RequestBody);
                $responseData['Error'] = null;
                $responseData['Msg'] = 'Updated Successfully';
                $responseData['Data'] = $updateCount;
                $responseData = json_encode($responseData);
                $restApi->response($responseData, 200);
            }



    }
} else {
    $responseData['Error'] = $data['Error'];
    $responseData['Msg'] = $data['Error'];
    $responseData = json_encode($responseData);
    $restApi->response($responseData, 200);
}

function validateData($authToken, $RequestBody, $RequestMethod)
{
    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    }
    if ($RequestMethod == 'PUT') {
        $isValid = true;

    } else if (isset($RequestBody['Year']) && !empty($RequestBody['Year'])) {
        $isValid = true;
    } else if (!isset($RequestBody['FromDate']) || empty($RequestBody['FromDate'])) {
        $error = 'Please Select From Date';
    } else if (!isset($RequestBody['ToDate']) || empty($RequestBody['ToDate'])) {
        $error = 'Please Select To Date ';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>