<?php

include_once '../Config/Config.php';
include_once 'Controller/RmPaymentSummaryReportController.php';

$database = new Database();
$restApi = new REST();

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();
$payload = Token::Verify($authToken);
if (is_array($payload)) {
    $RequestBody['AuthPayload'] = $payload;
    $RequestBody['AuthUserID'] = isset($payload['UserID']) ? $payload['UserID'] : null;
    $RequestBody['ClientID'] = isset($payload['ClientID']) ? $payload['ClientID'] : null;
}

if($RequestMethod=='GET'){
    $data = validateGetData($authToken, $RequestBody);
}else if($RequestMethod=='DELETE'){
    $data = validateDeleteData($authToken, $RequestBody);
}else{

}

//$data = validateData($authToken, $RequestBody);

if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new RmPaymentSummaryReportController($db);

        if($RequestMethod=='GET'){
            $itemCount = $Controller->GetRmPaymentSummaryReportDetails($RequestBody);
        }
        else if($RequestMethod=='DELETE'){
            $itemCount = $Controller->postDeletePayment($RequestBody);
        }
//echo "hii";
       // $itemCount = $Controller->GetRmPaymentSummaryReportDetails($RequestBody);
        // $responseData['Error'] = null;
        // $responseData['Msg'] = 'Success';        
        // $responseData['Data'] = isset($itemCount['Data'])  ? $itemCount['Data']: null;
        // $responseData = json_encode($responseData);
        // $restApi->response($responseData, 200);

        $responseData['Error']=$itemCount['Error'];
        $responseData['Msg']= $itemCount['Msg'];
        
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

function validateGetData($authToken, $RequestBody)
{
    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    }
    else  if (!isset($RequestBody['BillerType']) || empty($RequestBody['BillerType'])) {
        $error = 'Biller Type not Found';
    
    } 
    else  if (!isset($RequestBody['StartDate']) || empty($RequestBody['StartDate'])) {
        $error = 'Start Date not Found';
    
    } 
    
    else  if (!isset($RequestBody['EndDate']) || empty($RequestBody['EndDate'])) {
        $error = 'End Date not Found';
    
    }
    else {
        $isValid = true;
    }
     
    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}


function validateDeleteData($authToken, $RequestBody)
{

   // var_dump($RequestBody);
    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } 
    
    else  if (!isset($RequestBody['PaymentID']) && empty($RequestBody['PaymentID'])) {
        $error = 'Payment ID  not Found';
    } 
    
    else  if (!isset($RequestBody['BillerType']) && empty($RequestBody['BillerType'])) {
        $error = 'Biller Type  not Found';
    }  
    else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>
