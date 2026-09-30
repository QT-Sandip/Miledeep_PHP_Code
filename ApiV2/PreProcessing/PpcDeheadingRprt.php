<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/DeheadingDetailsController.php';

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
        $Controller = new DeheadingDetailsController($db);
        if($RequestMethod=='GET'){
            $itemCount = $Controller->getDeheadingDetails($RequestBody);
        }else if($RequestMethod=='POST'){
            $itemCount = $Controller->postDeheadingDetails($RequestBody);
        }else if($RequestMethod=='DELETE'){
            $itemCount = $Controller->postDeleteDeheading($RequestBody);
        }
       
        $responseData['Error']=$itemCount['Error'];
        $responseData['Msg']= $itemCount['Msg'];
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
    } else  if (!isset($RequestBody['startDate']) || empty($RequestBody['startDate'])) {
        $error = 'From Date not Found';
    } else  if (!isset($RequestBody['endDate']) || empty($RequestBody['endDate'])) {
        $error = 'To Date  not Found';
    }else {
        $isValid = true;
    }
    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;
    return $Data;
}


endDate

startDate
function validateGetData($authToken, $RequestBody)
{

    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else  if (!isset($RequestBody['startDate']) || empty($RequestBody['startDate'])) {
        $error = 'From Date not Found';
    }  else  if (!isset($RequestBody['endDate']) || empty($RequestBody['endDate'])) {
        $error = 'To Date not Found';
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

 //   var_dump($RequestBody);

 //echo $RequestBody['GraderID'];
    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else  if (!isset($RequestBody['ArrivalID']) && empty($RequestBody['ArrivalID'])) {
        $error = 'ArrivalID not Found';
    } else  if (!isset($RequestBody['Plant']) && empty($RequestBody['Plant'])) {
        $error = 'Plant Details not Found';
    }else  if (!isset($RequestBody['Center']) && empty($RequestBody['Center'])) {
        $error = 'Center Details not Found';
    }
    
    else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>

