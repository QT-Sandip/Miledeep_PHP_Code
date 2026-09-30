<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include_once '../Config/Config.php';
include_once 'Controller/DropdownController.php';
include_once '../Config/Token.php';

$data=validateData($authToken,$RequestBody);

if($data['IsValid']){
    $db = $database->getconnection($authToken);
    if($db!=null){
        $payLoad=Token::Verify($authToken);
        $RequestBody['UserID']=$payLoad['UserID'];
        $RequestBody['ClientID']=$payLoad['ClientID'];
        $Controller=new DropdownController($db);
        $ResponseData['Error']=null;
		$itemCount=$Controller->getDropdowns($RequestBody);
        $ResponseData['Data']=$itemCount;
        $ResponseData=json_encode($ResponseData);
		$restApi->response($ResponseData,200);
    }
    
}else{
    $responseData['Error'] = $data['Error'];
    $responseData['Msg'] = $data['Error'];
    $responseData = json_encode($responseData);
    $restApi->response($responseData, 200);
}


function validateData($authToken, $RequestBody)
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
?>