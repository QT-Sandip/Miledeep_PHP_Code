<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Config/Config.php';
include_once 'Controller/AddPackingDataController.php';

$database = new Database();
$restApi = new REST(); 
$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();

 if($RequestMethod=='POST'){
    $data = validatePackingData($authToken, $RequestBody);
}




if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new AddPackingDataController($db);
         if($RequestMethod=='POST'){
            $itemCount = $Controller->addPackingData($RequestBody);
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



function validatePackingData($authToken, $RequestBody)
{
    $isValid = false; 
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    }else if (!isset($RequestBody['OrderID']) || empty($RequestBody['OrderID'])) {
        $error = 'OrderID Required';
    } 
    // else if (!isset($RequestBody['POGradeId']) || empty($RequestBody['POGradeId'])) {
    //     $error = 'BreakUp ID Required';
    // }  
    else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}


?>