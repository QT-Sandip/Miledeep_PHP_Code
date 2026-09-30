<?php
include_once '../Config/Config.php';
include_once 'Controller/PlacePalletController.php';

$database = new Database();
$restApi = new REST();
$isValid = false;

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();

if ($RequestMethod == 'GET') {
    $data = validateGetData($authToken, $RequestBody);
} else if ($RequestMethod == 'POST') {
    $data = validatePostData($authToken, $RequestBody);
} else {

}



if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new PlacePalletController($db);
        // if ($RequestMethod == 'GET') {
        //     $itemCount = $Controller->getStockDetails($RequestBody);
        // } 
        if ($RequestMethod == 'POST') {
            $itemCount = $Controller->postPlacePalletDetails($RequestBody);
        }


        $responseData['Error'] = $itemCount['Error'];
        $responseData['Msg'] = $itemCount['Msg'];

        // $responseData['StockData'] = isset($itemCount['StockData']) ? $itemCount['StockData'] : null;

        $responseData = json_encode($responseData);
        $restApi->response($responseData, 200);

    }
} else {
    $responseData['Error'] = $data['Error'];
    $responseData['Msg'] = $data['Error'];
    $responseData = json_encode($responseData);
    $restApi->response($responseData, 200);
}


function validatePostData($authToken, $RequestBody)
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