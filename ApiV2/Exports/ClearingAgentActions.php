<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Config/Config.php';
include_once 'Controller/ClearingAgentActionsController.php';

$database = new Database();
$restApi = new REST(); 
$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();

 if($RequestMethod=='DELETE'){
    $data = validateDeleteData($authToken, $RequestBody);
}




if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new ClearingAgentActionsController($db);
         if($RequestMethod=='DELETE'){
            $itemCount = $Controller->deleteClearingAgent($RequestBody);
            $responseData['Error'] = $itemCount['Error'];
            $responseData['Msg'] = $itemCount['Msg']; 
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



function validateDeleteData($authToken, $RequestBody)
{
    $isValid = false; 
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    }else if (!isset($RequestBody['OrderID']) || empty($RequestBody['OrderID'])) {
        $error = 'OrderID Not Found';
    }  else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}


?>