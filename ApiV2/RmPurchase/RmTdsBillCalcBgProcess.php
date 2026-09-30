<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);*/

include_once '../Config/Config.php';
include_once '../Global/Util.php';
include_once 'Controller/RmTdsBillCalcBgProcessController.php';

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

}else{

}
if($data['IsValid']){
    $db = $database->getconnection($authToken);
    if($db!=null){
        $Controller=new RmTdsBillCalcBgProcessController($db,$RequestBody['ClientID'] );

        $ResponseData['Error']=null;
        if($RequestMethod=='GET'){
            $itemCount = $Controller->updateAutoOtherBills($RequestBody);
        }else if($RequestMethod=='POST'){
            $itemCount = $Controller->updateAutoOtherBills($RequestBody);
        }

        $responseData['Error'] = null;
        $responseData['Msg'] = 'Success';
        $responseData['EditLink'] = '';
        $responseData['EditAccess'] = True;
        $responseData['DeleteAccess'] = false;
        $responseData['Data'] = $itemCount;
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
    else  if (!isset($RequestBody['ArrivalID']) || empty($RequestBody['ArrivalID'])) {
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
?>
