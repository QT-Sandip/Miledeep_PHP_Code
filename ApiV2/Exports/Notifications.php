<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/NotificationsController.php';

$database = new Database();
$restApi = new REST();
$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$data = validateData($authToken, $RequestBody);
if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new NotificationsController($db);

        $itemCount = $Controller->getNotificationsDetails($RequestBody);
        $responseData['Error'] = null;
        $responseData['Msg'] = 'Success';
        $responseData['ShipmentsData'] = $itemCount['ShipmentsData'];
        $responseData['PendingContractsData'] = $itemCount['PendingContractsData'];
        $responseData['revisedOrdersData'] = $itemCount['revisedOrdersData'];
        // $responseData['PendingContractsData']=  $Controller->getPendingContractsDetails($RequestBody);
        $responseData['NegotiationData'] = $itemCount['NegotiationData'];
        $responseData['PaymentsReceivedData'] = $itemCount['PaymentsReceivedData'];
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