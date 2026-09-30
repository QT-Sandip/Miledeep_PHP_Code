<?php
//ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Config/Config.php';
include_once 'Controller/RealizationDoneController.php';

$database = new Database();
$restApi = new REST();
$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$data = validateData($authToken, $RequestBody);

if ($data['IsValid']) {

    $db = $database->getConnection($authToken);
  
    if ($db != null) {
        $Controller = new RealizationDoneController($db);
     
        $itemCount = $Controller->GetRealizationDoneDetails($RequestBody);
        $responseData['Error'] = null;
        $responseData['Msg'] = 'Success';
       
        $responseData['Data'] = $itemCount['RealizationDoneData'];
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
    }else if (!isset($RequestBody['Year']) || empty($RequestBody['Year'])) {
        if($RequestBody['DashBoard'] == 1){
            if((!isset($RequestBody['Year']) || empty($RequestBody['Year'])) && empty($RequestBody['ToDate']) && empty($RequestBody['FromDate'])){
                $error = 'Select a year or date range.';
            }else{
                 $isValid = true;
            }
        }else{
            $error = 'Please Select From Year';
        }
    }
   else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>