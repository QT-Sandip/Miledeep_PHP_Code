<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 
*/

include_once 'Config/Config.php';
include_once 'ApiController.php';

$database = new Database();
$restApi = new REST();



 $authToken = $restApi->getRequestHeader();
 
$RequestBody = $restApi->getRequestBody();

$data = validateData($authToken, $RequestBody);
$db = $database->getConnectionV1($authToken);
$func = strtolower(trim(str_replace("/", "", $_REQUEST['f'])));


$Controller = new ApiController($db);

if((int)method_exists($Controller,$func) > 0){
$Controller->$func();
}else{
	$restApi->response('',404);
}



function validateData($authToken, $RequestBody)
{
    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}
?>