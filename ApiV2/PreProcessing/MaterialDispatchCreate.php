<?php


include_once '../Config/Config.php';
include_once 'Controller/MaterialDispatchCreateController.php';

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
} else if ($RequestMethod == 'DELETE') {
    $data = validateDeleteData($authToken, $RequestBody);
} else {
}



if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new MaterialDispatchCreateController($db);
        if ($RequestMethod == 'GET') {
            $responseData = $Controller->getMaterialDispatch($RequestBody);
        } else if ($RequestMethod == 'POST') {
            $responseData = $Controller->postMaterialDispatch($RequestBody);
        } else if ($RequestMethod == 'DELETE') {
            $responseData = $Controller->deleteMaterialDispatch($RequestBody);
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
    } else if (!isset($RequestBody['MaterialDispatchID']) || empty($RequestBody['MaterialDispatchID'])) {
        $error = 'MaterialDispatchID  not Found';
    } 
    
   
    else {
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
    } 
  
           else if (!isset($RequestBody['DataType']) || empty($RequestBody['DataType'])) {
            $error = ' DataType  not Found';
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
    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } 
        
    else if (!isset($RequestBody['ArrivalID']) || empty($RequestBody['ArrivalID'])) {
        $error = 'ArrivalID not Found';
           }                   
        
        else if (!isset($RequestBody['FromStationID']) || empty($RequestBody['FromStationID'])) {
                            $error = ' FromStationID  not Found';
      } 
     else if (!isset($RequestBody['ToStationID']) || empty($RequestBody['ToStationID'])) {
                $error = ' ToStationID  not Found';
 } 

 else if (!isset($RequestBody['TransferredDate']) || empty($RequestBody['TransferredDate'])) {
    $error = ' TransferredDate  not Found';
}  else if (!isset($RequestBody['Grade']) || empty($RequestBody['Grade'])) {
    $error = ' Grade  not Found';
}  else if (!isset($RequestBody['VarietyID']) || empty($RequestBody['VarietyID'])) {
    $error = ' VarietyID  not Found';
}  else if (!isset($RequestBody['Quantity']) || empty($RequestBody['Quantity'])) {
    $error = ' Quantity   not Found';
}  else if (!isset($RequestBody['DispatchedCrates']) || empty($RequestBody['DispatchedCrates'])) {
    $error = ' DispatchedCrates  not Found';
}  
else if (!isset($RequestBody['Status']) || $RequestBody['Status'] == ''){
    $error = ' Status  not Found';
}  



    else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}


