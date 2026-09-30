<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

include_once '../Config/Config.php';
include_once 'Controller/RmArrivalReportController.php';

$database = new Database();
$restApi = new REST();
$isValid=false;

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();

if($RequestMethod=='GET'){
    $data = validateGetData($authToken, $RequestBody);
}else if($RequestMethod=='POST'){
    $data = validatePostData($authToken, $RequestBody);
}else if($RequestMethod=='DELETE'){
    $data = validateDeleteData($authToken, $RequestBody);
}else{

}



if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new RmAllArrivalReportController($db);
        if($RequestMethod=='GET'){
            $itemCount = $Controller->getArrivalDetails($RequestBody);
        }else if($RequestMethod=='POST'){
            $itemCount = $Controller->postArrivalDetails($RequestBody);
        }else if($RequestMethod=='DELETE'){
            $itemCount = $Controller->postDeleteArrivalBrkUP($RequestBody);
        }
       
        $responseData['Error']=$itemCount['Error'];
        $responseData['Msg']= $itemCount['Msg'];
        $responseData['EditLink'] = '#';
        $responseData['CompanyName'] =  $itemCount['CompanyName'];
        $responseData['EditAccess'] = true;
        $responseData['DeleteAccess'] = true;
        $responseData['BreakUpData'] = isset($itemCount['BreakUpData'])  ? $itemCount['BreakUpData']: null;
        $responseData['ArrivalData'] = isset($itemCount['ArrivalData'])  ? $itemCount['ArrivalData']: null;
        $responseData['Headers'] = isset($itemCount['Headers'])  ? $itemCount['Headers']: null;
        $responseData['SubTotal'] = isset($itemCount['SubTotal'])  ? $itemCount['SubTotal']: null;
        $responseData['GrandTotal'] = isset($itemCount['GrandTotal'])  ? $itemCount['GrandTotal']: null;
        $responseData['Summary'] = isset($itemCount['Summary'])  ? $itemCount['Summary']: null;
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
    } else  if (!isset($RequestBody['ArrivalID']) || empty($RequestBody['ArrivalID'])) {
        $error = 'Arrival ID not Found';
    }else {
        $isValid = true;
    }
    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;
    return $Data;
}

function validateGetData($authToken, $RequestBody)
{

    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else  if (!isset($RequestBody['ArrivalDate']) || empty($RequestBody['ArrivalDate'])) {
        $error = 'Arrival Date not Found';
    }   
    
    
    else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}
function validatePostData($authToken, $RequestBody)
{

   // var_dump($RequestBody);
    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    }   if (!isset($RequestBody['ArrivalDate']) || empty($RequestBody['ArrivalDate'])) {
        $error = 'Arrival Date Details not Found';
    }    else  if (!isset($RequestBody['PlantID']) || empty($RequestBody['PlantID'])) {
        $error = 'Plant Details not Found';
    }    else  if (!isset($RequestBody['CenterID']) || empty($RequestBody['CenterID'])) {
        $error = 'Center Details not Found';
    }    else  if (!isset($RequestBody['CompanyID']) || empty($RequestBody['CompanyID'])) {
        $error = 'Company Details not Found';
    }    else  if (!isset($RequestBody['Species']) || empty($RequestBody['Species'])) {
        $error = 'Species Details not Found';
    }    else  if (!isset($RequestBody['TotalWeight']) || empty($RequestBody['TotalWeight'])) {
        $error = 'TotalWeight Details not Found';
    }        else  if (!isset($RequestBody['TotalAmount']) || empty($RequestBody['TotalAmount'])) {
        $error = 'AvgRate Details not Found';
    }else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>

