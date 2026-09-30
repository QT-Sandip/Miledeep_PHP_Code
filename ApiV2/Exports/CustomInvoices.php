<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/CustomInvoicesController.php';

$database = new Database();
$restApi = new REST();

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();


if ($RequestMethod == 'GET') {
    $data = validateData($authToken, $RequestBody);
} else if ($RequestMethod == 'POST') {
    $data = validatePostData($authToken, $RequestBody);
} else {
}


// $data = validateData($authToken, $RequestBody);

if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new CustomInvoicesController($db);

        if ($RequestMethod == 'GET') {
            $itemCount = $Controller->GetCustomsListDetails($RequestBody);
        } else if ($RequestMethod == 'POST') {
            $itemCount = $Controller->postCancelCustoms($RequestBody);
        }

        // $itemCount = $Controller->GetCustomsListDetails($RequestBody);
        $responseData['Error'] = $itemCount['Error'];
        $responseData['Msg'] = $itemCount['Msg'];
        $responseData['EditLink'] = $itemCount['EditLink'];
        $responseData['DownloadExcel'] = $itemCount['DownloadExcel'];
        $responseData['EditAccess'] = true;
        $responseData['DeleteAccess'] = false;
        $responseData['TotalCount'] = $itemCount['TotalCount'];
        $responseData['RemainingCount'] = $itemCount['RemainingCount'];
        $responseData['RecordsAval'] = $itemCount['RecordsAval'];
        $responseData['PageCount'] = $itemCount['PageCount'];
        $responseData['Data'] = $itemCount['Data'];
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
    } else if (!isset($RequestBody['Year']) || empty($RequestBody['Year'])) {
        $error = 'Please Select From Year';
    } else if (!isset($RequestBody['PageNo']) || empty($RequestBody['PageNo'])) {
        $error = 'Please Select From PageNo';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}

function validatePostData($authToken, $RequestBody)
{

    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else if (!isset($RequestBody['PiID']) || empty($RequestBody['PiID'])) {
        $error = 'PiID is not Found';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;
}
