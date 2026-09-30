<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/CommercialInvoiceController.php';

$database = new Database();
$restApi = new REST();
$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$data = validateData($authToken, $RequestBody);

if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    $payLoad=Token::Verify($authToken);
      
        $RequestBody['ClientID']=$payLoad['ClientID']; 
    if ($db != null) {
        $Controller = new CommercialInvoiceController($db);

        $itemCount = $Controller->GetCommercialListDetails($RequestBody);
        $responseData['Error'] = null;
        $responseData['Msg'] = 'Success';
        $responseData['EditLink'] = $itemCount['EditLink'];
        $responseData['EditAccess'] = true;
        $responseData['DeleteAccess'] = false;
        $responseData['TotalCount'] = $itemCount['TotalCount'];
        $responseData['RemainingCount'] = $itemCount['RemainingCount']; 

        $responseData['CommercialInvoicePrintLink'] = $itemCount['CommercialInvoicePrintLink'];
        $responseData['PackingInvoicePrintLink'] = $itemCount['PackingInvoicePrintLink'];
        $responseData['ViewReportsLink'] = $itemCount['ViewReportsLink'];
        $responseData['PageCount'] = $itemCount['PageCount'];
        $responseData['Data'] = $itemCount['Data'];
        $responseData['RecordsAval'] = $itemCount['RecordsAval'];
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
    }else if (!isset($RequestBody['Year']) || empty($RequestBody['Year'])) {
        $error = 'Please Select  Year';
    }
     else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>