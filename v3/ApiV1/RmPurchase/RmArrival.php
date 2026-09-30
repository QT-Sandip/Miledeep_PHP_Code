<?php

include_once '../Config/Config.php';
include_once 'Controller/RmArrivalController.php';

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

}else{

}



if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new RmArrivalController($db);
        if($RequestMethod=='GET'){
            $itemCount = $Controller->getArrivalDetails($RequestBody);
        }else if($RequestMethod=='POST'){
            $itemCount = $Controller->postArrivalDetails($RequestBody);
        }
        
        $responseData['Error']=$itemCount['Error'];
        $responseData['Msg']= $itemCount['Msg'];
        $responseData['EditLink'] = '#';
        $responseData['EditAccess'] = false;
        $responseData['DeleteAccess'] = false;
        $responseData['BreakUpData'] = $itemCount['BreakUpData'];
        $responseData['ArrivalData'] = $itemCount['ArrivalData'];
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
    } else  if (!isset($RequestBody['ArrivalID']) || empty($RequestBody['ArrivalID'])) {
        $error = 'Arrival ID not Found';
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
    } else  if (!isset($RequestBody['GraderID']) || empty($RequestBody['GraderID'])) {
        $error = 'Grader Details not Found';
    } else  if (!isset($RequestBody['AgentID']) || empty($RequestBody['AgentID'])) {
        $error = 'Agent Details not Found';
    }else  if (!isset($RequestBody['ArrivalNumber']) || empty($RequestBody['ArrivalNumber'])) {
        $error = 'Arrival Number not Found';
    }  else  if (!isset($RequestBody['DriverID']) || empty($RequestBody['DriverID'])) {
        $error = 'Driver Details not Found';
    } else  if (!isset($RequestBody['VehicalID']) || empty($RequestBody['VehicalID'])) {
        $error = 'Vehicle Details not Found';
    } else  if (!isset($RequestBody['FarmAddress']) || empty($RequestBody['FarmAddress'])) {
        $error = 'Farm Details not Found';
    }   else  if (!isset($RequestBody['PurchaseCategoryID']) || empty($RequestBody['PurchaseCategoryID'])) {
        $error = 'Purchase  not Found';
    }   else  if (!isset($RequestBody['ArrivalDate']) || empty($RequestBody['ArrivalDate'])) {
        $error = 'Arrival Date Details not Found';
    }    else  if (!isset($RequestBody['ArrivalStationID']) || empty($RequestBody['ArrivalStationID'])) {
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

