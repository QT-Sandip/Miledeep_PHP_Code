<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/EmployeeListController.php';

$database = new Database();
$restApi = new REST();
 $authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$data = validateData($authToken, $RequestBody);

if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new EmployeeListController($db);

        $itemCount = $Controller->GetEmployeeListDetails($RequestBody);
        $responseData['Error'] = null;
        $responseData['Msg'] = 'Success';
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
    } else if (!isset($RequestBody['EmployeeStatus']) || empty($RequestBody['EmployeeStatus'])) {
        $error = 'Please Select Employee Status';
    }else if (!isset($RequestBody['PageNo']) || empty($RequestBody['PageNo'])) {
        $error = 'Please Select From PageNo';
    }
    else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>