<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/EmployeeBankDetailsCreateController.php';

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
}

// else if($RequestMethod=='DELETE'){
//     $data = validateDeleteData($authToken, $RequestBody);
// }
else{

}



if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new EmployeeBankDetailsCreateController($db);
        if($RequestMethod=='GET'){
            $itemCount = $Controller->getEmployeeBankDetails($RequestBody);
        }else if($RequestMethod=='POST'){
            $itemCount = $Controller->postEmployeeBankDetails($RequestBody);
        }
        // else if($RequestMethod=='DELETE'){
        //     $itemCount = $Controller->postDeleteEmployeeBankDetails($RequestBody);
        // }
       
        $responseData['Error']=$itemCount['Error'];
        $responseData['Msg']= $itemCount['Msg'];
        $responseData['EditLink'] = '#';
        $responseData['EditAccess'] = true;
        $responseData['DeleteAccess'] = true;
        //$responseData['BreakUpData'] = isset($itemCount['BreakUpData'])  ? $itemCount['BreakUpData']: null;
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
//     } else  if (!isset($RequestBody['ArrivalID']) || empty($RequestBody['ArrivalID'])) {
//         $error = 'Arrival ID not Found';
//     } else  if (!isset($RequestBody['BreakUpID']) || empty($RequestBody['BreakUpID'])) {
//         $error = 'BreakUp ID not Found';
//     }else {
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
    } else  if (!isset($RequestBody['EmployeeID']) || empty($RequestBody['EmployeeID'])) {
        $error = 'EmployeeID not Found';
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
    } else  if (!isset($RequestBody['EmployeeID']) && empty($RequestBody['EmployeeID'])) {
        $error = 'EmployeeID not Found';
    } else  if (!isset($RequestBody['BankID']) && empty($RequestBody['BankID'])) {
        $error = 'BankID not Found';
    } else  if (!isset($RequestBody['AccountNumber']) && empty($RequestBody['AccountNumber'])) {
        $error = 'AccountNumber not Found';
    } else  if (!isset($RequestBody['Ifsc']) && empty($RequestBody['Ifsc'])) {
        $error = 'Ifsc not Found';
    } 
    else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>

