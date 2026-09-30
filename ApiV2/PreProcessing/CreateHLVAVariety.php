<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/CreateHLVAVarietyController.php';

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
        $Controller = new CreateHLVAVarietyController($db);
        if($RequestMethod=='GET'){
            $itemCount = $Controller->getHLVAVarietyDetails($RequestBody);
        }else if($RequestMethod=='POST'){
            $itemCount = $Controller->postHLVAVarietyDetails($RequestBody);
        }
        // else if($RequestMethod=='DELETE'){
        //     $itemCount = $Controller->postDeleteEmployeeBankDetails($RequestBody);
        // }
       
        $responseData['Error']=$itemCount['Error'];
        $responseData['Msg']= $itemCount['Msg'];
        $responseData['EditLink'] = '#';
        $responseData['EditAccess'] = true;
        $responseData['Data'] = $itemCount['Data'];
       
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
    } else  if (!isset($RequestBody['Species']) || empty($RequestBody['Species'])) {
        $error = 'Species not Found';
    } else  if (!isset($RequestBody['TypeProduct']) || empty($RequestBody['TypeProduct'])) {
        $error = 'Type Product not Found';
    } else  if (!isset($RequestBody['ShortName']) || empty($RequestBody['ShortName'])) {
        $error = 'Short Name not Found';
    } else  if (!isset($RequestBody['FullName']) || empty($RequestBody['FullName'])) {
        $error = 'Full Name not Found';
    } else  if (!isset($RequestBody['Category']) || empty($RequestBody['Category'])) {
        $error = 'Category not Found';
    } else  if (!isset($RequestBody['ProductID']) || empty($RequestBody['ProductID'])) {
        $error = 'ProductID not Found';
    } 
    else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>

