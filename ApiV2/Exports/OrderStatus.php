<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once '../Config/Config.php';
include_once 'Controller/OrderStatusController.php';

$database = new Database();
$restApi = new REST(); 
$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();

if($RequestMethod=='GET'){
    $data = validateGetData($authToken, $RequestBody);
}else if($RequestMethod=='PUT'){
    $data = validatePUTData($authToken, $RequestBody);
}else if($RequestMethod=='DELETE'){
    $data = validateDeleteData($authToken, $RequestBody);
}else if($RequestMethod=='POST'){
    $data = validateDeleteData($authToken, $RequestBody);
}



if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new OrderStatusController($db);
        if($RequestMethod=='GET'){
            $itemCount = $Controller->getOrderList($RequestBody);
            $responseData['Error'] = null;
            $responseData['Msg'] = 'Success';
            $responseData['ApprovalAccess'] = true;    
            $responseData['Orders'] = $itemCount['Orders'];
            $responseData['Status'] = $itemCount['Status'];
        }else if($RequestMethod=='PUT'){
            $itemCount = $Controller->updateOrderList($RequestBody);
            $responseData['Error'] = $itemCount['Error'];
            $responseData['Msg'] = $itemCount['Msg'];  
        }else if($RequestMethod=='DELETE'){
            $itemCount = $Controller->deletePI($RequestBody);
            $responseData['Error'] = $itemCount['Error'];
            $responseData['Msg'] = $itemCount['Msg']; 
        } else if($RequestMethod=='POST'){
            $itemCount = $Controller->duplicatePI($RequestBody);
            $responseData['Error'] = $itemCount['Error'];
            $responseData['Msg'] = 'Success';
            //$responseData['Msg'] = $itemCount['Msg']; 
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

function validateGetData($authToken, $RequestBody)
{
    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    }else if (!isset($RequestBody['ApprovalType']) || empty($RequestBody['ApprovalType'])) {
        $error = 'Approval Type Not Found';
    }  else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

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
function validatePUTData($authToken, $RequestBody)
{
    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    }else if (!isset($RequestBody['Status']) || empty($RequestBody['Status'])) {
        $error = 'Status Not Found';
    }else if (!isset($RequestBody['OrderIDs']) || empty($RequestBody['OrderIDs'])) {
        $error = 'OrderID  Not Found';
    }else if (!isset($RequestBody['ApprovalType']) || empty($RequestBody['ApprovalType'])) {
        $error = 'Approval Type  Not Found';
    }  else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>