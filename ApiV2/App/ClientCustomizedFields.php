<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/ClientCustomizedFieldsController.php';

$database = new Database();
$restApi = new REST();
 $authToken = $restApi->getRequestHeader();
$db = $database->getAuthConnection();
$CustomizedFieldsController = new ClientCustomizedFieldsController($db);

 //$RequestBody = $restApi->getRequestBody();
 if($db!=null){
 $data = validateData($authToken,$RequestBody);
 }
 

if ($data['IsValid']) {
    $reqMethod = $restApi->get_request_method();
  
    $itemCount = $CustomizedFieldsController->getClientCustomizedFieldsList($RequestBody);
    $responseData['Error']= null;
    $responseData['Data']= $itemCount['Data'];
    $responseData= json_encode($responseData);
    $restApi-> response($responseData,200);
} else {
    $responseData['Error']=$data['Error'];
    $responseData['Msg']=$data['Error'];
    $responseData=json_encode($responseData);
    $restApi-> response($responseData,200);
}

function validateData($authToken,$RequestBody)
{
    $isValid = false;
    $error=null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else {
        $isValid = true;
    }

    $Data['Error']=$error;
    $Data['IsValid']=$isValid;
   
    return $Data;

}



?>