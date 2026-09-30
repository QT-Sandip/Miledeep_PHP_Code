<?php
//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
include_once '../Config/Config.php';
include_once 'Controller/HlVaBatchListController.php';

$database = new Database();
$restApi = new REST();

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();


if($RequestMethod=='GET'){
    $data = validateData($authToken, $RequestBody);
}else  if($RequestMethod=='DELETE'){
    $data = validateDeleteData($authToken, $RequestBody);
}else{

}



if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new HlVaBatchListController($db);

        if($RequestMethod=='GET'){
            $itemCount = $Controller->getRmHlVaDetails($RequestBody);
        }else if($RequestMethod=='DELETE'){          
            $itemCount = $Controller->postDeleteRmHlVaDetails($RequestBody);
        }

        
        $responseData['Error'] = null;
        $responseData['Msg'] = 'Success';
        $responseData['EditLink'] = 'hl-va-bills';
        $responseData['EditAccess'] = True;
        $responseData['DeleteAccess'] = false;
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

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    }else  if (!isset($RequestBody['BatchDate']) || empty($RequestBody['BatchDate'])) {
        $error = 'Batch Date not Found';
    }  else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

function validateDeleteData($authToken, $RequestBody)
{
    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    }else  if (!isset($RequestBody['BillID']) || empty($RequestBody['BillID'])) {
        $error = 'BillID ID  not Found';
    }  else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>