<?php



include_once '../Config/Config.php';
include_once 'Controller/ProformaPrintController.php';
include_once 'Controller/UtilController.php';
include_once 'Controller/AppUtilController.php';

$database = new Database();
$restApi = new REST();

 $authToken = $restApi->getRequestHeader();
 $RequestBody = $restApi->getRequestBody();
$data=validateData($authToken,$RequestBody);
if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if($db!=null){
    $Controller = new ProformaPrintController($db);
    $db=null;
    $itemCount = $Controller->getProformaPrint($RequestBody);
    $responseData['Error']= null;
    $responseData['Data']= $itemCount;
    $responseData= json_encode($responseData);
    $restApi-> response($responseData,200);
}
} else {
    $responseData['Error']=$data['Error'];
    $responseData['Msg']=$data['Error'];
    $responseData=json_encode($responseData);
    $restApi-> response($responseData,200);
}

function validateData($authToken ,$RequestBody)
{
    $isValid = false;
    $error=null;

    
    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    }
    else if (!isset($RequestBody['ProformaID']) && empty($RequestBody['ProformaID'])) {
        $error = 'Proforma Id Required';
    } else if (!isset($RequestBody['DocType']) && empty($RequestBody['DocType'])) {
        $error = 'DocType Required';
    }
    
    else {
        $isValid = true;
    } 
    
    /*else if (!isset($RequestBody['LoginID']) && empty($RequestBody['LoginID'])) {
        $error = 'User Name Required';
    } else if (!isset($RequestBody['Password']) && empty($RequestBody['Password'])) {
        $error = 'Password Required';
    } else if (!isset($RequestBody['IpAddress'])  && empty($RequestBody['IpAddress'])) {
        $error = 'UnAuthrised Access';
    }*/ 
    
    

    $Data['Error']=$error;
    $Data['IsValid']=$isValid;
   
    return $Data;

}

?>