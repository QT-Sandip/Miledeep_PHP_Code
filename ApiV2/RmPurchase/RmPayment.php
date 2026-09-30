<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include_once '../Config/Config.php';
include_once 'Controller/RmPaymentController.php';

$database=new Database();

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
}else if($RequestMethod=='POST'){
    $data = validatePostData($authToken, $RequestBody);
}else if($RequestMethod=='DELETE'){
    $data = validateDeleteData($authToken, $RequestBody);
}else{

}
if($data['IsValid']){
    $db = $database->getconnection($authToken);
    if($db!=null){
        $Controller=new RmPaymentController($db);

        $ResponseData['Error']=null;
        if($RequestMethod=='GET'){
            $itemCount = $Controller->getRmBillDetails($RequestBody);
        }
        else if($RequestMethod=='POST'){
            $itemCount = $Controller->postRmPaymentDetails($RequestBody);
        }
		//  else if($RequestMethod=='DELETE'){
        //     $itemCount = $Controller->postDeletePaymentBreakup($RequestBody);
        // }
		
        $responseData['Error'] = $itemCount['Error'];
        $responseData['Msg'] = $itemCount['Msg'];
        $responseData['EditLink'] = '';
        $responseData['EditAccess'] = True;
        $responseData['DeleteAccess'] = false;
        $responseData['VoucherData'] = isset($itemCount['Data'])  ? $itemCount['Data']: null;
		/*$responseData['BreakUpData'] = isset($itemCount['BreakUpData'])  ? $itemCount['BreakUpData']: null;
        $responseData['ArrivalData'] = isset($itemCount['ArrivalData'])  ? $itemCount['ArrivalData']: null;
        $responseData['GrandTotal'] = isset($itemCount['GrandTotal'])  ? $itemCount['GrandTotal']: null;
        */
        $responseData = json_encode($responseData);
        $restApi->response($responseData, 200);

    }
}
    else{
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
    else  if (!isset($RequestBody['PaymentMode']) || empty($RequestBody['PaymentMode'])) {
        $error = 'Payment Mode not Found';
    } else  if (!isset($RequestBody['CenterID']) || empty($RequestBody['CenterID'])) {
        $error = 'Center ID not Found';
    } else  if (!isset($RequestBody['PaymentDate']) || empty($RequestBody['PaymentDate'])) {
        $error = 'Payment Date not Found';
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
    } else  if (!isset($RequestBody['PaymentMode']) && empty($RequestBody['PaymentMode'])) {
        $error = 'Payment Mode not Found';
    } else  if (!isset($RequestBody['Amount']) || empty($RequestBody['Amount'])) {
        $error = 'Amount not Found';
    }  else {
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
