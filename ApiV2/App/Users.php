<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/UsersController.php';
include_once '../Config/Token.php';

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
    $db = $database->getAuthConnection();//$authToken
    if ($db != null) {
        $payLoad=Token::Verify($authToken);
        $RequestBody['UserID']=$payLoad['UserID'];
        $RequestBody['ClientID']=$payLoad['ClientID'];

          $rawDataJson = file_get_contents("php://input");
        $rawData = json_decode( $rawDataJson ,true);
//echo '<pre>'; print_r($rawData);die;
        // Pass Status if exists
        $RequestBody['Status'] = isset($rawData['Status']) ? $rawData['Status'] : "0";


        $Controller = new UsersController($db);
        if($RequestMethod=='GET'){
            $itemCount = $Controller->getUsers($RequestBody);
        }else if($RequestMethod=='POST'){
            $itemCount = $Controller->updateUser($RequestBody);
        }
        // else if($RequestMethod=='DELETE'){
        //     $itemCount = $Controller->postDeleteSalaryElementsMasterDetails($RequestBody);
        // }

        //var_dump($itemCount);
       
        $responseData['Error']=$itemCount['Error'];
        $responseData['Msg']= $itemCount['Msg'];
        $responseData['EditLink'] = '#';
        $responseData['EditAccess'] = $itemCount['Data']['EditAccess'];
        $responseData['DeleteAccess'] = $itemCount['Data']['DeleteAccess'];   
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

