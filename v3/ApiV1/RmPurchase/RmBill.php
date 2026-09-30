<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once '../Config/Config.php';
include_once 'Controller/RmBillController.php';

$database=new Database();
$data=validateData($authToken,$RequestBody);
if($data['IsValid']){
    $db = $database->getconnection($authToken);
    if($db!=null){
        $Controller=new RmBillController($db);

        $ResponseData['Error']=null;
		$itemCount=$Controller->getRmBillDetails($RequestBody);
        $responseData['Error'] = null;
        $responseData['Msg'] = 'Success';
        $responseData['EditLink'] = '';
        $responseData['EditAccess'] = True;
        $responseData['DeleteAccess'] = false;
        $responseData['Data'] = $itemCount;
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



function validateData($authToken, $RequestBody)
{
    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    }
    else  if (!isset($RequestBody['RmDate']) || empty($RequestBody['RmDate'])) {
        $error = 'RmDate Details not Found';
    } 
    else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}
?>