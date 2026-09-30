<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/SalaryStructureMasterController.php';

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
        $Controller = new SalaryStructureMasterController($db);
        if($RequestMethod=='GET'){
            $itemCount = $Controller->getSalaryStructureMasterDetails($RequestBody);
        }else if($RequestMethod=='POST'){
            $itemCount = $Controller->postSalaryStructureMasterDetails($RequestBody);
        }
        // else if($RequestMethod=='DELETE'){
        //     $itemCount = $Controller->postDeleteSalaryElementsMasterDetails($RequestBody);
        // }
       
        $responseData['Error']=$itemCount['Error'];
        $responseData['Msg']= $itemCount['Msg'];
        $responseData['EditLink'] = '#';
        $responseData['EditAccess'] = true;
        $responseData['DeleteAccess'] = true;      
        $responseData['Data'] = isset($itemCount['Data'])  ? $itemCount['Data']: null;       
        $responseData = json_encode($responseData);
        $restApi->response($responseData, 200);
       
    }
} else {
    $responseData['Error'] = $data['Error'];
    $responseData['Msg'] = $data['Error'];
    $responseData = json_encode($responseData);
    $restApi->response($responseData, 200);
}


// function validateDeleteData($authToken, $RequestBody)
// {
//     $isValid = false;
//     $error = null;
//     if (!isset($authToken) && empty($authToken)) {
//         $error = 'Token Not Found';
//     } 
//     else  if (!isset($RequestBody['ArrivalID']) || empty($RequestBody['ArrivalID'])) {
//         $error = 'Arrival ID not Found';
//     } 
//     else {
//         $isValid = true;
//     }
//     $Data['Error'] = $error;
//     $Data['IsValid'] = $isValid;
//     return $Data;
// }

function validateGetData($authToken, $RequestBody)
{

    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    }
    //  else  if (!isset($RequestBody['ArrivalID']) || empty($RequestBody['ArrivalID'])) {
    //     $error = 'Arrival ID not Found';
    // }   
    
    
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
    }
    //  else  if (!isset($RequestBody['ArrivalID']) && empty($RequestBody['ArrivalID'])) {
    //     $error = 'ArrivalID not Found';
    // } 
   
    
    else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>

