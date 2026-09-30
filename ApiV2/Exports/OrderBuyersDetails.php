<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/OrdersBuyerDetailsController.php';

$database = new Database();
$restApi = new REST();
// $isValid = false;

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$data = validateData($authToken, $RequestBody);

if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new OrdersBuyerDetailsController($db);
        
        $itemCount = $Controller->getOrderBuyersDetails($RequestBody);
        $responseData['Error'] = $itemCount['Error'];
        $responseData['Msg'] = 'Success';
        $responseData['TotalCount'] = $itemCount['TotalCount'];
        $responseData['Data'] = $itemCount['Data'];
        // $responseData['BreakUpData'] = $itemCount['BreakUpData'];

        $responseData = json_encode($responseData);
        $restApi->response($responseData, 200);
    }
} else {
    $responseData['Error'] = $data['Error'];
    $responseData['Msg'] = $data['Error'];
    $responseData = json_encode($responseData);
    $restApi->response($responseData, 200);
}

function validateData($authToken)
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
