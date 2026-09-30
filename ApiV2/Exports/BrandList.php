<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once '../Config/Config.php';
include_once 'Controller/BrandController.php';

$database=new Database();
$Data=validateData($authToken,$RequestBody);
if($Data['IsValid']){
    $db = $database->getconnection($authToken);
    if($db!=null){
        $Controller=new BrandListController($db);
        $ResponseData['Error']=null;
		$itemCount=$Controller->getBrandDetails();
        $ResponseData['Data']=$itemCount;
        $ResponseData=json_encode($ResponseData);
		$restApi->response($ResponseData,200);
    }
    else{
        $ResponseData['Error']=json_encode($data['Error']);
        $restApi->response($responseData, 200);
    }
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