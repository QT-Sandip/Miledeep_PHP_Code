<?php

include_once '../Config/Config.php';
include_once 'Controller/ShippingLinesChargesController.php';

$database = new Database();
$restApi = new REST();

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();



$data = validateData($authToken, $RequestBody);

if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new ShippingLinesChargesController($db);
        if ($RequestMethod == 'GET') {
            if (isset($RequestBody['GetType']) && !empty($RequestBody['GetType']) && $RequestBody['GetType'] == 'LoadShippingLinerDetails') {
                $itemCount = $Controller->GetShippingLinerChargesDetails($RequestBody);
                $responseData['Error'] = null;
                $responseData['Msg'] = 'Success';
                $responseData['Data'] = $itemCount['Data'];
            } else {
                $itemCount = $Controller->GetShippingLinerChargesList($RequestBody);
                $responseData['Error'] = null;
                $responseData['Msg'] = 'Success';
                $responseData['EditLink'] = $itemCount['EditLink'];
                $responseData['EditAccess'] = false;
                $responseData['DeleteAccess'] = True;
                $responseData['TotalCount'] = $itemCount['TotalCount'];
                $responseData['RemainingCount'] = $itemCount['RemainingCount'];
                $responseData['RecordsAval'] = $itemCount['RecordsAval'];
                $responseData['PageCount'] = $itemCount['PageCount'];
                $responseData['Data'] = $itemCount['Data'];
            }

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

function validateData($authToken, $RequestBody)
{
    $isValid = true;
    $error = null;

    if (isset($RequestBody['GetType']) && !empty($RequestBody['GetType']) && $RequestBody['GetType'] != 'LoadShippingLinerDetails') {
        if (!isset($authToken) && empty($authToken)) {
            $error = 'Token Not Found';
        } else if (!isset($RequestBody['FromDate']) || empty($RequestBody['FromDate'])) {
            $error = 'Please Select From FromDate';
        } else if (!isset($RequestBody['ToDate']) || empty($RequestBody['ToDate'])) {
            $error = 'Please Select From ToDate';
        } else {
            $isValid = true;
        }
    }



    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>