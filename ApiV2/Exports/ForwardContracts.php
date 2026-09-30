<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once '../Config/Config.php';
include_once 'Controller/ForwardContractController.php';
include_once 'Controller/UtilController.php';


$data = validateData($authToken, $RequestBody);
if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new ForwardContractController($db);
        $db = null;
        $itemCount = $Controller->getForwardContractList($RequestBody);
        $responseData['Error'] = null;
        $responseData['Data'] = $itemCount;
        $responseData = json_encode($responseData);
        $restApi->response($responseData, 200);
    }
} else {
    $responseData['Error'] = $data['Error'];
    $responseData['Msg'] = $data['Error'];
    $responseData = json_encode($responseData);
    $restApi->response($responseData, 200);
}

function validateData($authToken, $RequestBody)
{
    $isValid = false;
    $error = null;

    //{ "Exporter" : "974","Consignee" : "","Country":"","Status":"","":"" }


    if (!isset($authToken) || empty($authToken)) {
        $error = 'Token Not Found';
    } else if (!isset($RequestBody['Year']) || empty($RequestBody['Year'])) {
        $error = 'Year Required';
    } else {
        $isValid = true;
    }

    /*else if (!isset($RequestBody['LoginID']) && empty($RequestBody['LoginID'])) {
        $error = 'User Name Required';
    } else if (!isset($RequestBody['Password']) && empty($RequestBody['Password'])) {
        $error = 'Password Required';
    } else if (!isset($RequestBody['IpAddress'])  && empty($RequestBody['IpAddress'])) {
        $error = 'UnAuthrised Access';
    }*/



    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>